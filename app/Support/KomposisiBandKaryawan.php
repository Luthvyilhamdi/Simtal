<?php

namespace App\Support;

use App\Models\Karyawan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Komposisi karyawan HOME per Band: berapa yang ditempatkan di struktur induk
 * (dipecah Core / Non Core) dan berapa yang sedang ditugaskan keluar.
 *
 * Berbeda dari KeterisianHomeHost yang menghitung KURSI (formasi vs terisi),
 * tabel ini menghitung ORANG, dan band-nya band KARYAWAN (dari Job Grade
 * pribadinya), bukan band posisi. Petanya memakai Karyawan::bandConfig(),
 * satu-satunya peta band di aplikasi — JG 10 ada di Band 5.
 *
 * Istilahnya mengikuti laporan HC:
 *   Home                  — karyawan milik PIM sendiri. Karyawan ber-status
 *                           kepegawaian "Penugasan" adalah orang luar yang
 *                           ditugaskan MASUK ke PIM, jadi tidak ikut dihitung.
 *   Penempatan di Induk   — bekerja di PIM, dipecah Core / Non Core menurut
 *                           penanda pada baris Struktur Organisasi-nya.
 *   Penugasan · PI Group  — ditugaskan ke PT Pupuk Indonesia (holding),
 *                           ditandai "Non Mutasi" pada jabatan saat ini.
 *   Penugasan · Anper/    — ditugaskan ke anak/cucu perusahaan atau yayasan,
 *   Cuper/Yayasan           mis. "Penugasan Sebagai Direktur PT PIM Prima Medika".
 *
 * Penugasan internal ("DKU Penugasan Bidang …", Tugas Belajar) TETAP dihitung
 * sebagai penempatan di induk — orangnya masih bekerja untuk PIM.
 */
class KomposisiBandKaryawan
{
    /** Status kepegawaian untuk orang luar yang ditugaskan MASUK ke PIM. */
    public const STATUS_MASUK = 'Penugasan';

    public const PENANDA_PI_GROUP = 'non mutasi';
    public const PENANDA_KELUAR   = 'penugasan sebagai';

    /**
     * @return array{
     *     bulan:int, tahun:int,
     *     band: array<string, array>,
     *     total: array
     * }
     */
    public static function hitung(int $bulan, int $tahun): array
    {
        $core     = self::coreTiapKaryawan($bulan, $tahun);
        $jgPosisi = self::jgPosisiTiapKaryawan($bulan, $tahun);
        $home     = self::karyawanHome();

        // Grade efektif dilekatkan sekali, supaya pengelompokan band tidak
        // menghitung ulang pencadangannya di tiap penyaringan.
        $home->each(function ($k) use ($jgPosisi) {
            $k->grade_efektif = self::gradeEfektif($k->job_grade, $jgPosisi[$k->id] ?? null);
            $k->pakai_jg_posisi = self::grade($k->job_grade) === null && $k->grade_efektif !== null;
        });

        $band = [];
        foreach (self::urutanBand() as $nama) {
            $band[$nama] = self::sel($home->filter(fn ($k) => self::band($k->grade_efektif) === $nama), $core);
        }
        $tanpa = $home->filter(fn ($k) => self::band($k->grade_efektif) === null);
        if ($tanpa->isNotEmpty()) {
            $band['Tanpa Band'] = self::sel($tanpa, $core);
        }

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'band'  => $band,
            'total' => self::sel($home, $core),
            // Orang luar yang ditugaskan MASUK ke PIM — tidak ikut dihitung,
            // disertakan supaya jumlahnya bisa disebut di keterangan tabel.
            'ditugaskan_masuk' => DB::table('karyawans')
                ->where('status', 'aktif')
                ->where('status_kepegawaian', self::STATUS_MASUK)
                ->count(),
            // Siapa saja yang band-nya terpaksa diambil dari JG posisi. Ini
            // penanda data Master yang belum lengkap, bukan keadaan normal.
            'grade_dari_posisi' => $home->where('pakai_jg_posisi', true)
                ->map(fn ($k) => ['nik' => $k->nik, 'nama' => $k->nama, 'jg' => $k->grade_efektif])
                ->values()->all(),
        ];
    }

    /**
     * Sisi HOST: orang yang bekerja di PIM.
     *
     * Yang dikeluarkan hanya PI Group — 34 orang ber-status "Non Mutasi" yang
     * ditugaskan ke holding. Penugasan ke anak/cucu perusahaan TETAP dihitung
     * di sini, jadi angkanya sejalan dengan kartu "Host — Keterisian".
     *
     * Susunannya mencerminkan sisi Home: kolom kiri karyawan PIM sendiri
     * (dipecah Core/Non Core), kolom kanan orang luar yang ditugaskan masuk.
     *
     * @return array{bulan:int, tahun:int, band: array<string, array>, total: array}
     */
    public static function hitungHost(int $bulan, int $tahun): array
    {
        $core     = self::coreTiapKaryawan($bulan, $tahun);
        $jgPosisi = self::jgPosisiTiapKaryawan($bulan, $tahun);

        $semua = self::karyawanAktif();
        $semua->each(function ($k) use ($jgPosisi) {
            $k->grade_efektif = self::gradeEfektif($k->job_grade, $jgPosisi[$k->id] ?? null);
        });

        // Yang ditugaskan ke holding tidak bekerja di PIM.
        $host = $semua->reject(fn ($k) => self::piGroup($k->jabatan_saat_ini));

        $band = [];
        foreach (self::urutanBand() as $nama) {
            $isi = $host->filter(fn ($k) => self::band($k->grade_efektif) === $nama);
            if ($isi->isNotEmpty()) {
                $band[$nama] = self::selHost($isi, $core);
            }
        }
        $tanpa = $host->filter(fn ($k) => self::band($k->grade_efektif) === null);
        if ($tanpa->isNotEmpty()) {
            $band['Tanpa Band'] = self::selHost($tanpa, $core);
        }

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'band'  => $band,
            'total' => self::selHost($host, $core),
        ];
    }

    /** Satu baris band sisi Host: karyawan PIM (Core/Non Core) + penugasan masuk. */
    protected static function selHost(Collection $orang, Collection $core): array
    {
        $masuk = $orang->filter(fn ($k) => $k->status_kepegawaian === self::STATUS_MASUK);
        $pim   = $orang->reject(fn ($k) => $k->status_kepegawaian === self::STATUS_MASUK);

        $jmlCore = $pim->filter(fn ($k) => ($core[$k->id] ?? null) === 'Core')->count();
        $jmlNon  = $pim->filter(fn ($k) => ($core[$k->id] ?? null) === 'Non Core')->count();
        $totPim  = $jmlCore + $jmlNon;

        return [
            'core'            => $jmlCore,
            'non_core'        => $jmlNon,
            'total_pim'       => $totPim,
            'persen_core'     => $totPim ? round($jmlCore / $totPim * 100, 2) : null,
            'persen_non_core' => $totPim ? round($jmlNon  / $totPim * 100, 2) : null,
            'masuk'           => $masuk->count(),
            'total_host'      => $totPim + $masuk->count(),
            // Karyawan PIM yang belum punya baris Struktur Organisasi sama sekali.
            'tanpa_penempatan' => $pim->count() - $totPim,
        ];
    }

    /**
     * Job Grade sebagai angka, atau null kalau belum diisi.
     * Master memakai "-" dan "0" sebagai penanda kosong, bukan hanya NULL.
     */
    public static function grade($nilai): ?int
    {
        $jg = (int) $nilai;

        return $jg > 0 ? $jg : null;
    }

    /**
     * Grade yang dipakai untuk menentukan band.
     *
     * Kalau Job Grade pribadi karyawan belum diisi, dipakai Job Grade POSISI
     * yang sedang didudukinya di Struktur Organisasi. Contohnya Rizal Pahlevi:
     * job_grade & person_grade-nya masih "-", tetapi posisinya "Vice President
     * Hukum" ber-JG 18 di semua periode — jadi Band 2, bukan tanpa band.
     *
     * Ini jaring pengaman, bukan pengganti data yang benar: grade karyawannya
     * tetap perlu diisi di Master, karena MDG dan Reminder Promosi membacanya
     * langsung dan tidak ikut mencadangkan seperti ini.
     */
    public static function gradeEfektif($jobGradeKaryawan, $jobGradePosisi): ?int
    {
        return self::grade($jobGradeKaryawan) ?? self::grade($jobGradePosisi);
    }

    /**
     * Band dari Job Grade, null kalau grade-nya belum diisi atau di luar peta.
     * Petanya satu untuk seluruh aplikasi — Karyawan::bandConfig().
     */
    public static function band($jobGrade): ?string
    {
        $jg = self::grade($jobGrade);
        if ($jg === null) {
            return null;
        }
        $band = Karyawan::getBandFromGrade($jg);

        return $band === '-' ? null : $band;
    }

    /** Urutan band untuk baris tabel, mengikuti peta tunggal aplikasi. */
    protected static function urutanBand(): array
    {
        return array_keys(Karyawan::bandConfig());
    }

    /** Seluruh karyawan aktif, termasuk yang ditugaskan masuk ke PIM. */
    protected static function karyawanAktif(): Collection
    {
        return DB::table('karyawans as k')
            ->leftJoin('job_grade as jg', 'jg.id', '=', 'k.job_grade_id')
            ->where('k.status', 'aktif')
            ->get(['k.id', 'k.nik', 'k.nama', 'k.jabatan_saat_ini', 'k.status_kepegawaian', 'jg.job_grade']);
    }

    /** Karyawan aktif milik PIM sendiri (yang ditugaskan masuk tidak ikut). */
    protected static function karyawanHome(): Collection
    {
        return self::karyawanAktif()
            ->reject(fn ($k) => $k->status_kepegawaian === self::STATUS_MASUK)
            ->values();
    }

    /**
     * Job Grade POSISI yang diduduki tiap karyawan pada periode ini — dipakai
     * sebagai cadangan kalau grade pribadinya belum diisi.
     *
     * @return Collection<int, string>  karyawan_id => job_grade posisi
     */
    protected static function jgPosisiTiapKaryawan(int $bulan, int $tahun): Collection
    {
        return DB::table('struktur_organisasi')
            ->where('bulan', $bulan)->where('tahun', $tahun)
            ->where('posisi', '!=', '-')
            ->whereNotNull('karyawan_id')
            ->whereNotNull('job_grade')
            ->pluck('job_grade', 'karyawan_id');
    }

    /**
     * Penanda Core / Non Core tiap karyawan, dari baris Struktur Organisasi
     * yang didudukinya pada periode ini.
     *
     * @return Collection<int, string>  karyawan_id => 'Core' | 'Non Core'
     */
    protected static function coreTiapKaryawan(int $bulan, int $tahun): Collection
    {
        return DB::table('struktur_organisasi')
            ->where('bulan', $bulan)->where('tahun', $tahun)
            ->where('posisi', '!=', '-')
            ->whereNotNull('karyawan_id')
            ->whereIn('core', ['Core', 'Non Core'])
            ->pluck('core', 'karyawan_id');
    }

    public static function ditugaskanKeluar(?string $jabatan): bool
    {
        return $jabatan !== null && stripos($jabatan, self::PENANDA_KELUAR) !== false;
    }

    public static function piGroup(?string $jabatan): bool
    {
        return $jabatan !== null && stripos($jabatan, self::PENANDA_PI_GROUP) !== false;
    }

    /** Satu baris band: pecahan induk Core/Non Core + pecahan penugasan. */
    protected static function sel(Collection $orang, Collection $core): array
    {
        $pi    = $orang->filter(fn ($k) => self::piGroup($k->jabatan_saat_ini));
        $anper = $orang->filter(fn ($k) => self::ditugaskanKeluar($k->jabatan_saat_ini)
                                        && !self::piGroup($k->jabatan_saat_ini));
        $induk = $orang->reject(fn ($k) => self::ditugaskanKeluar($k->jabatan_saat_ini));

        $jmlCore = $induk->filter(fn ($k) => ($core[$k->id] ?? null) === 'Core')->count();
        $jmlNon  = $induk->filter(fn ($k) => ($core[$k->id] ?? null) === 'Non Core')->count();
        $totInduk = $jmlCore + $jmlNon;

        return [
            'core'            => $jmlCore,
            'non_core'        => $jmlNon,
            'total_induk'     => $totInduk,
            // null kalau tidak ada orang di induk — '—' lebih jujur daripada 0%
            'persen_core'     => $totInduk ? round($jmlCore / $totInduk * 100, 2) : null,
            'persen_non_core' => $totInduk ? round($jmlNon  / $totInduk * 100, 2) : null,
            'anper'           => $anper->count(),
            'pi_group'        => $pi->count(),
            'total_penugasan' => $anper->count() + $pi->count(),
            // Orang yang tidak punya baris Struktur Organisasi sama sekali —
            // tidak masuk Core maupun Non Core, ditahan di sini agar totalnya utuh.
            'tanpa_penempatan' => $induk->count() - $totInduk,
            'total_orang'      => $orang->count(),
        ];
    }
}
