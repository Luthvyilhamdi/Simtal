<?php

namespace App\Services;

use App\Models\JobFamily;
use App\Models\KompetensiTeknis;
use App\Models\UnitKompetensiTeknis;
use Illuminate\Support\Facades\DB;

/**
 * Insert baris tidy Kompetensi Teknis (hasil KompetensiTeknisParser::parse()) ke
 * kompetensi_teknis & unit_kompetensi_teknis, dgn resolve/create master kompetensi,
 * deteksi konflik job_family, & skip duplikat by unique constraint (unit_organisasi_id,
 * struktur_organisasi_versi_id, jenjang_jabatan, kompetensi_teknis_id).
 *
 * Diekstrak dari ImportKompetensiTeknis::handle() (logic apa adanya, bukan logic baru)
 * supaya bisa dipakai bareng oleh command CLI komtek:import (dictionary unit hardcode
 * per rumpun) DAN controller web (unit_mapping dinamis hasil Step 2 alur upload
 * self-service) — tanpa duplikasi logic insert/validasi/dedup.
 *
 * Baris tidy dgn job_family_id NULL (rumpun jabatan generic yg tidak dikenali di master
 * Job Family saat parsing, lihat KompetensiTeknisParser) DIANGGAP ERROR di sini — bukan
 * di-skip diam2 — supaya halaman Review otomatis nge-block tombol commit (blocker sama
 * spt error lain, cukup lewat count($errors) > 0, TIDAK perlu pengecekan terpisah).
 *
 * DEFAULT DRY-RUN ($commit = false): transaksi tetap dijalankan penuh (termasuk create
 * kompetensi_teknis baru & insert unit_kompetensi_teknis) supaya SEMUA pengecekan
 * (unique constraint, duplikat, kompetensi baru) akurat 100% seperti eksekusi sungguhan,
 * lalu di-ROLLBACK di akhir — bukan simulasi/dry logic terpisah yg bisa meleset dari
 * kondisi race/duplikat sungguhan di database.
 *
 * prioritas (primary/secondary) TIDAK LAGI dibaca dari tidy row apa adanya — nilai
 * 'prioritas' bawaan tidy row SELALU 'secondary' (default aman dari Parser, lihat
 * KompetensiTeknisParser). Baris jadi 'primary' HANYA kalau row_id-nya ada di parameter
 * $primaryRowIds (hasil pilihan manual user di step "Pilih Primary", alur import web) —
 * default [] (kosong) utk caller yg belum/tidak punya konsep ini (mis. CLI komtek:import),
 * semua baris otomatis 'secondary'. asal (native/generic) TIDAK terpengaruh sama sekali
 * oleh pemilihan primary — tetap murni dari hasil parsing.
 */
class KompetensiTeknisImporter
{
    /**
     * @param array<int, array<string, mixed>> $tidyRows baris tidy (field sama persis dgn output KompetensiTeknisParser::parse()['tidyRows'])
     * @param array<string, int> $unitMapping kandidat_nama_unit => unit_organisasi_id
     * @param array<int, string> $primaryRowIds row_id yg dipilih manual jadi prioritas='primary' — sisanya 'secondary'
     *
     * @return array{
     *     totalDiproses: int, totalInsert: int, totalDuplikat: int, errors: array<int, string>,
     *     stoppedEarly: bool,
     *     tallyAsal: array{native: int, generic: int}, tallyPrioritas: array{primary: int, secondary: int},
     *     perUnit: array<int, int>, kompetensiBaru: array<int, string>, konflikRumpun: array<int, string>,
     *     committed: bool
     * }
     */
    public static function import(array $tidyRows, array $unitMapping, int $versiId, bool $commit = false, array $primaryRowIds = []): array
    {
        $totalDiproses  = count($tidyRows);
        $totalInsert    = 0;
        $totalDuplikat  = 0;
        $errors         = [];
        $tallyAsal      = ['native' => 0, 'generic' => 0];
        $tallyPrioritas = ['primary' => 0, 'secondary' => 0];
        $perUnit        = [];
        $stoppedEarly   = false;
        $primaryRowIdSet = array_flip($primaryRowIds);

        // Nama job_family SEKALI di awal (bukan query per baris) — dipakai utk pesan
        // laporan yg manusiawi (nama, bukan id mentah) di kompetensiBaru/konflikRumpun.
        $jobFamilyNames = JobFamily::pluck('nama', 'id')->all();

        $kompetensiCache = []; // lower(nama_kompetensi) => id
        $kompetensiBaru  = []; // "nama || nama job family" unik
        $konflikRumpun   = []; // "nama || db=... || file=..." unik
        $insertedKeys    = []; // composite key yg sudah di-insert run ini

        DB::beginTransaction();

        try {
            foreach ($tidyRows as $r) {
                $baris          = $r['baris_asal_excel'];
                $kandidatUnit   = trim((string) $r['kandidat_nama_unit']);
                $jenjang        = trim((string) $r['jenjang_jabatan']);
                $urutanJenjang  = $r['urutan_jenjang'];
                $grade          = trim((string) $r['grade']);
                $namaJobs       = trim((string) $r['nama_jobs']);
                $managerialRaw  = $r['managerial'];
                $namaKompetensi = trim((string) $r['nama_kompetensi']);
                $jobFamilyId    = $r['job_family_id'] !== null ? (int) $r['job_family_id'] : null;
                $level          = $r['level'];
                $asalRow        = trim((string) $r['asal']);
                $rowId          = (string) ($r['row_id'] ?? '');
                // Default tidy row 'secondary' — jadi 'primary' HANYA kalau row_id-nya
                // dipilih manual di step "Pilih Primary" (lihat catatan class di atas).
                $prioritas      = isset($primaryRowIdSet[$rowId]) ? 'primary' : 'secondary';

                if (isset($tallyAsal[$asalRow])) {
                    $tallyAsal[$asalRow]++;
                }
                $tallyPrioritas[$prioritas]++;

                // Mapping miss = error konfigurasi (unit belum dipetakan), bukan error data
                // per-baris -> hentikan seluruh proses (sama spt dictionary miss di CLI).
                if (!array_key_exists($kandidatUnit, $unitMapping)) {
                    $errors[] = "Baris {$baris}: kandidat_nama_unit \"{$kandidatUnit}\" tidak ada di unit_mapping. Proses dihentikan.";
                    $stoppedEarly = true;
                    break;
                }
                $unitOrganisasiId = (int) $unitMapping[$kandidatUnit];
                $perUnit[$unitOrganisasiId] = ($perUnit[$unitOrganisasiId] ?? 0) + 1;

                if ($jenjang === '' || $urutanJenjang === '' || $urutanJenjang === null || $managerialRaw === '' || $managerialRaw === null) {
                    $errors[] = "Baris {$baris}: data tidak lengkap (jenjang_jabatan/urutan_jenjang/managerial kosong) — baris dilewati.";
                    continue;
                }
                $managerial = (bool) ((int) $managerialRaw);

                // job_family_id NULL = rumpun jabatan generic tidak dikenali di master saat
                // parsing (lihat KompetensiTeknisParser) — WAJIB direview manual, tidak boleh
                // ikut ke-commit begitu saja. Diperlakukan sbg error (bukan skip diam2) supaya
                // halaman Review otomatis nge-block tombol commit.
                if ($jobFamilyId === null) {
                    $errors[] = "Baris {$baris}: rumpun jabatan (job_family) untuk kompetensi \"{$namaKompetensi}\" tidak dikenali — perlu direview manual sebelum bisa commit.";
                    continue;
                }

                if ($namaKompetensi === '' || !is_numeric($level) || (int) $level < 1 || (int) $level > 5 || !in_array($asalRow, ['native', 'generic'], true)) {
                    $errors[] = "Baris {$baris}: data kompetensi tidak valid (nama_kompetensi/level/asal) — baris dilewati.";
                    continue;
                }

                $kompetensiId = self::resolveKompetensi($namaKompetensi, $jobFamilyId, $jobFamilyNames, $kompetensiCache, $kompetensiBaru, $konflikRumpun);

                $key = $unitOrganisasiId . '|' . $versiId . '|' . mb_strtolower($jenjang) . '|' . $kompetensiId;

                if (isset($insertedKeys[$key]) || self::existsInDb($unitOrganisasiId, $versiId, $jenjang, $kompetensiId)) {
                    $totalDuplikat++;
                    continue;
                }

                UnitKompetensiTeknis::create([
                    'unit_organisasi_id'           => $unitOrganisasiId,
                    'struktur_organisasi_versi_id' => $versiId,
                    'jenjang_jabatan'               => $jenjang,
                    'urutan_jenjang'                => (int) $urutanJenjang,
                    'grade'                         => $grade !== '' ? $grade : null,
                    'nama_jobs'                     => $namaJobs,
                    'managerial'                    => $managerial,
                    'kompetensi_teknis_id'          => $kompetensiId,
                    'level'                         => (int) $level,
                    'asal'                          => $asalRow,
                    'prioritas'                     => $prioritas,
                ]);

                $insertedKeys[$key] = true;
                $totalInsert++;
            }

            if ($stoppedEarly || count($errors) > 0) {
                DB::rollBack();
            } elseif ($commit) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[]     = 'Import gagal, transaksi di-rollback: ' . $e->getMessage();
            $stoppedEarly = true;
        }

        return [
            'totalDiproses'  => $totalDiproses,
            'totalInsert'    => $totalInsert,
            'totalDuplikat'  => $totalDuplikat,
            'errors'         => $errors,
            'stoppedEarly'   => $stoppedEarly,
            'tallyAsal'      => $tallyAsal,
            'tallyPrioritas' => $tallyPrioritas,
            'perUnit'        => $perUnit,
            'kompetensiBaru' => array_keys($kompetensiBaru),
            'konflikRumpun'  => array_keys($konflikRumpun),
            'committed'      => $commit && !$stoppedEarly && count($errors) === 0,
        ];
    }

    private static function resolveKompetensi(
        string $nama,
        int $jobFamilyId,
        array $jobFamilyNames,
        array &$kompetensiCache,
        array &$kompetensiBaru,
        array &$konflikRumpun
    ): int {
        $lower = mb_strtolower($nama);

        if (isset($kompetensiCache[$lower])) {
            $existing = KompetensiTeknis::find($kompetensiCache[$lower]);
            self::checkKonflikJobFamily($existing, $jobFamilyId, $jobFamilyNames, $konflikRumpun);

            return $kompetensiCache[$lower];
        }

        $existing = KompetensiTeknis::whereRaw('LOWER(nama_kompetensi) = ?', [$lower])->first();

        if ($existing) {
            $kompetensiCache[$lower] = $existing->id;
            self::checkKonflikJobFamily($existing, $jobFamilyId, $jobFamilyNames, $konflikRumpun);

            return $existing->id;
        }

        $baru = KompetensiTeknis::create([
            'nama_kompetensi' => $nama,
            'job_family_id'   => $jobFamilyId,
        ]);

        $kompetensiCache[$lower] = $baru->id;
        $kompetensiBaru[$nama . ' || ' . ($jobFamilyNames[$jobFamilyId] ?? "id={$jobFamilyId}")] = true;

        return $baru->id;
    }

    private static function checkKonflikJobFamily(KompetensiTeknis $existing, int $jobFamilyId, array $jobFamilyNames, array &$konflikRumpun): void
    {
        if ((int) $existing->job_family_id !== $jobFamilyId) {
            $dbNama   = $jobFamilyNames[$existing->job_family_id] ?? "id={$existing->job_family_id}";
            $fileNama = $jobFamilyNames[$jobFamilyId] ?? "id={$jobFamilyId}";

            $konflikRumpun[$existing->nama_kompetensi . ' || db="' . $dbNama . '" || file="' . $fileNama . '"'] = true;
        }
    }

    private static function existsInDb(int $unitOrganisasiId, int $versiId, string $jenjang, int $kompetensiId): bool
    {
        return UnitKompetensiTeknis::where('unit_organisasi_id', $unitOrganisasiId)
            ->where('struktur_organisasi_versi_id', $versiId)
            ->whereRaw('LOWER(jenjang_jabatan) = ?', [mb_strtolower($jenjang)])
            ->where('kompetensi_teknis_id', $kompetensiId)
            ->exists();
    }
}
