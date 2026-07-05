<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\TalentPool;
use App\Models\UsulanPromosi;

/**
 * Sumber tunggal perhitungan Reminder Promosi (MDG), dipakai oleh
 * ReminderPromosiController dan kartu ringkasan di Dashboard.
 *
 * Read-only. Selaras dengan Data Talent: shortlist (periode TERBARU yang ada
 * datanya, <= tahun ini) memakai ambang longgar (Band 24 bln, JG 12 bln),
 * lainnya normal (Band 36, JG 24, PG 12).
 */
class ReminderPromosiService
{
    /** Batas "akan datang" dalam bulan. */
    public const WINDOW_BULAN = 3;

    /** Status usulan promosi yang dianggap "sedang berjalan / sudah lulus"
     *  → karyawannya disembunyikan dari reminder (tidak perlu diusulkan lagi).
     *  Status 'tidak_lulus' & 'ditolak' TIDAK termasuk (mungkin perlu diusulkan lagi). */
    public const USULAN_AKTIF = ['draft', 'verif_berkas', 'sidang', 'lulus'];

    /**
     * @return array{items: array<int,array>, hiddenItems: array<int,array>, shortlistPeriode: int|null, totalDinilai: int, disembunyikan: int}
     */
    public function build(): array
    {
        // Tahun Talent Pool TERBARU yang ada datanya (APA PUN klasifikasinya), <= tahun ini.
        // Patokan pada keberadaan data tahun itu — bukan pada ada-tidaknya shortlist —
        // supaya begitu penilaian tahun baru masuk (mis. 2026), acuan langsung pindah
        // ke tahun itu; karyawan yang shortlist hanya di tahun sebelumnya (2025) tidak
        // lagi dianggap shortlist walau tahun baru kebetulan tanpa shortlist.
        $latestPeriode = TalentPool::where('periode', '<=', now()->year)->max('periode');

        $shortlistSet = $latestPeriode
            ? array_flip(
                TalentPool::where('klasifikasi', 'shortlist')
                    ->where('periode', $latestPeriode)
                    ->pluck('karyawan_id')
                    ->all()
              )
            : [];

        // Karyawan yang sudah punya usulan promosi berjalan/lulus → disembunyikan.
        // Map karyawan_id => status usulan terbaru (untuk ditampilkan di panel "disembunyikan").
        $usulanStatusMap = [];
        foreach (UsulanPromosi::whereIn('status', self::USULAN_AKTIF)->orderByDesc('id')->get(['karyawan_id', 'status']) as $u) {
            $usulanStatusMap[$u->karyawan_id] ??= $u->status;
        }

        // Hanya karyawan aktif yang punya minimal satu TMT (kalau tidak, MDG = 0
        // dan sisa bulannya pasti > window → tidak masuk daftar).
        $karyawans = Karyawan::with(['jobGrade', 'personGrade', 'direktorat', 'jabatan'])
            ->where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNotNull('tanggal_mulai_pg')
                  ->orWhereNotNull('tanggal_mulai_jg');
            })
            ->orderBy('nama')
            ->get();

        $items = [];
        $hiddenItems = [];
        foreach ($karyawans as $k) {
            $isShortlist = isset($shortlistSet[$k->id]);

            $minBand = $isShortlist ? 24 : 36;
            $minJg   = $isShortlist ? 12 : 24;
            $minPg   = 12;

            $sk = $k->statusKenaikan($minPg, $minJg, $minBand);

            if (($sk['status'] ?? null) === 'puncak') {
                continue;
            }

            $eligibleNow = ($sk['eligible'] ?? false) === true;
            $sisa        = (int) ($sk['sisa_bulan'] ?? 0);

            if (!$eligibleNow && ($sisa < 1 || $sisa > self::WINDOW_BULAN)) {
                continue;
            }

            $row = [
                'karyawan'     => $k,
                'sk'           => $sk,
                'eligible_now' => $eligibleNow,
                'sisa'         => $eligibleNow ? 0 : $sisa,
                'is_shortlist' => $isShortlist,
            ];

            // Sudah diusulkan (berjalan/lulus) → masuk daftar tersembunyi, bukan daftar utama.
            if (isset($usulanStatusMap[$k->id])) {
                $row['usulan_status'] = $usulanStatusMap[$k->id];
                $hiddenItems[] = $row;
                continue;
            }

            $items[] = $row;
        }

        return [
            'items'            => $items,
            'hiddenItems'      => $hiddenItems,
            'shortlistPeriode' => $latestPeriode,
            'totalDinilai'     => $karyawans->count(),
            'disembunyikan'    => count($hiddenItems),
        ];
    }
}
