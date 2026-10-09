<?php

namespace App\Services;

use App\Models\HistoryAssessment;
use App\Models\HistoryAssessmentKompetensi;
use App\Models\Karyawan;
use App\Models\PgsPjs;
use Carbon\Carbon;

/**
 * Sumber tunggal perhitungan Reminder PJS.
 *
 * PJS diberikan ketika salah satu syarat pengangkatan tetap belum terpenuhi.
 * Halaman ini menjawab satu pertanyaan: dari pemegang PJS yang sedang berjalan,
 * siapa yang syaratnya SUDAH lengkap sehingga bisa diangkat definitif, dan
 * sisanya tertahan apa.
 *
 * Dua syarat yang dicek:
 *   1. MDG  — memakai Karyawan::statusKenaikan(), sama persis dengan Reminder
 *             Promosi, supaya perhitungan masa dinas tidak bercabang dua versi.
 *   2. Assessment — DUA sumber, salah satu memenuhi sudah cukup:
 *        a. Assessment Rekomendasi  — minimal "ready with development"
 *        b. Assessment Kompetensi   — kesimpulan "QUALIFIED"
 *      Keduanya dipakai karena tidak semua orang menempuh jenis yang sama;
 *      mis. Ariyo Wibowo Jinca tidak punya rekomendasi tapi QUALIFIED di
 *      assessment kompetensi.
 *
 * PGS TIDAK ikut dihitung: ketentuannya berbeda dan ambang assessment-nya pun
 * belum disepakati. Read-only, tidak mengubah data apa pun.
 */
class ReminderPjsService
{
    /** Rekomendasi assessment yang dianggap sudah memenuhi syarat. */
    public const ASSESSMENT_MEMENUHI = ['ready', 'ready_with_development'];

    /** Kesimpulan assessment kompetensi yang dianggap memenuhi syarat. */
    public const KOMPETENSI_MEMENUHI = 'qualified';

    /** Batas lama memangku PJS sebelum dianggap perlu ditinjau (bulan). */
    public const BATAS_LAMA_BULAN = 12;

    public const SIAP             = 'siap';
    public const MENUNGGU_MDG     = 'menunggu_mdg';
    public const PERLU_ASSESSMENT = 'perlu_assessment';

    /**
     * @return array{
     *     items: array<int,array>,
     *     siap:int, menunggu_mdg:int, perlu_assessment:int,
     *     total:int, lama:int
     * }
     */
    public function build(): array
    {
        $pjs = PgsPjs::with(['karyawan.jobGrade', 'karyawan.personGrade', 'karyawan.direktorat'])
            ->where('is_active', true)
            ->where('tipe', 'pjs')
            ->get()
            ->filter(fn ($p) => $p->karyawan && $p->karyawan->status === 'aktif');

        // Assessment terakhir tiap karyawan diambil sekali untuk seluruh daftar,
        // bukan per baris, supaya jumlah query tidak tumbuh bersama daftar PJS.
        $ids        = $pjs->pluck('karyawan_id')->all();
        $rekomendasi = $this->rekomendasiTerakhir($ids);
        $kompetensi  = $this->kompetensiTerakhir($ids);

        $items = [];
        foreach ($pjs as $p) {
            $k  = $p->karyawan;
            $sk = $k->statusKenaikan();

            $mdgOk   = ($sk['eligible'] ?? false) === true;
            $sisaMdg = (int) ($sk['sisa_bulan'] ?? 0);

            $rek  = $rekomendasi[$k->id] ?? null;
            $komp = $kompetensi[$k->id] ?? null;

            $rekOk  = $rek !== null && in_array($rek->rekomendasi_final, self::ASSESSMENT_MEMENUHI, true);
            $kompOk = $komp !== null
                && strcasecmp(trim((string) $komp->kesimpulan), self::KOMPETENSI_MEMENUHI) === 0;
            $asOk   = $rekOk || $kompOk;

            // Yang ditampilkan: sumber yang MEMENUHI lebih dulu; kalau tidak ada
            // yang memenuhi, tampilkan apa pun yang dimiliki supaya tetap jelas
            // apa yang sudah pernah ditempuh.
            if ($rekOk)          { $sumber = 'rekomendasi'; $tanggal = $rek->tanggal_pelaksanaan; }
            elseif ($kompOk)     { $sumber = 'kompetensi';  $tanggal = $komp->tanggal_assessment; }
            elseif ($rek)        { $sumber = 'rekomendasi'; $tanggal = $rek->tanggal_pelaksanaan; }
            elseif ($komp)       { $sumber = 'kompetensi';  $tanggal = $komp->tanggal_assessment; }
            else                 { $sumber = null;          $tanggal = null; }

            $umurAs = $tanggal ? (int) Carbon::parse($tanggal)->diffInMonths(now()) : null;

            $lama = $p->tanggal_mulai
                ? (int) Carbon::parse($p->tanggal_mulai)->diffInMonths(now())
                : null;

            // Assessment diperiksa lebih dulu: tanpa rekomendasi yang memenuhi,
            // MDG yang sudah cukup pun belum bisa ditindaklanjuti.
            if (!$asOk) {
                $keadaan = self::PERLU_ASSESSMENT;
            } elseif (!$mdgOk) {
                $keadaan = self::MENUNGGU_MDG;
            } else {
                $keadaan = self::SIAP;
            }

            $items[] = [
                'karyawan'        => $k,
                'pjs'             => $p,
                'posisi_pjs'      => Karyawan::posisiDari($p->jabatan_pgs_pjs),
                'lama_bulan'      => $lama,
                'terlalu_lama'    => $lama !== null && $lama > self::BATAS_LAMA_BULAN,
                'sk'                 => $sk,
                'mdg_ok'             => $mdgOk,
                'sisa_mdg'           => $mdgOk ? 0 : $sisaMdg,
                'rekomendasi'        => $rek,
                'kompetensi'         => $komp,
                'assessment_ok'      => $asOk,
                'assessment_sumber'  => $sumber,
                'assessment_umur'    => $umurAs,
                'keadaan'            => $keadaan,
            ];
        }

        // Siap dulu, lalu yang tinggal menunggu MDG (sisa terkecil di atas),
        // terakhir yang masih butuh assessment. Nama sebagai pemutus.
        $urutan = [self::SIAP => 0, self::MENUNGGU_MDG => 1, self::PERLU_ASSESSMENT => 2];
        usort($items, fn ($a, $b) => [$urutan[$a['keadaan']], $a['sisa_mdg'], $a['karyawan']->nama]
                                <=> [$urutan[$b['keadaan']], $b['sisa_mdg'], $b['karyawan']->nama]);

        $hitung = fn (string $keadaan) => count(array_filter($items, fn ($i) => $i['keadaan'] === $keadaan));

        return [
            'items'            => $items,
            'siap'             => $hitung(self::SIAP),
            'menunggu_mdg'     => $hitung(self::MENUNGGU_MDG),
            'perlu_assessment' => $hitung(self::PERLU_ASSESSMENT),
            'total'            => count($items),
            'lama'             => count(array_filter($items, fn ($i) => $i['terlalu_lama'])),
        ];
    }

    /**
     * Assessment Rekomendasi terakhir per karyawan.
     *
     * @param  array<int,int>  $karyawanIds
     * @return array<int,HistoryAssessment>
     */
    protected function rekomendasiTerakhir(array $karyawanIds): array
    {
        if (!$karyawanIds) {
            return [];
        }

        $peta = [];
        $rows = HistoryAssessment::whereIn('karyawan_id', $karyawanIds)
            ->orderByDesc('tanggal_pelaksanaan')
            ->orderByDesc('id')
            ->get(['id', 'karyawan_id', 'rekomendasi_final', 'tanggal_pelaksanaan', 'tanggal_exp_idp', 'lembaga']);

        foreach ($rows as $r) {
            $peta[$r->karyawan_id] ??= $r;   // yang pertama ditemui = paling baru
        }

        return $peta;
    }

    /**
     * Assessment Kompetensi terakhir per karyawan.
     *
     * @param  array<int,int>  $karyawanIds
     * @return array<int,HistoryAssessmentKompetensi>
     */
    protected function kompetensiTerakhir(array $karyawanIds): array
    {
        if (!$karyawanIds) {
            return [];
        }

        $peta = [];
        $rows = HistoryAssessmentKompetensi::whereIn('karyawan_id', $karyawanIds)
            ->orderByDesc('tanggal_assessment')
            ->orderByDesc('id')
            ->get(['id', 'karyawan_id', 'kesimpulan', 'tanggal_assessment', 'periode', 'lembaga']);

        foreach ($rows as $r) {
            $peta[$r->karyawan_id] ??= $r;
        }

        return $peta;
    }
}
