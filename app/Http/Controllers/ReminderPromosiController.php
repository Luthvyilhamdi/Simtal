<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Direktorat;
use App\Models\TalentPool;
use Illuminate\Http\Request;

/**
 * Reminder Promosi — murni informasi (read-only).
 *
 * Menampilkan karyawan yang SUDAH memenuhi syarat MDG untuk naik grade,
 * atau AKAN memenuhi dalam 1–3 bulan ke depan, agar HR bisa menyiapkan
 * usulan promosi tepat waktu. Tidak mengubah data apa pun.
 *
 * SELARAS dengan Data Talent (shortlist): karyawan yang shortlist di Talent Pool
 * (periode tahun ini / tahun lalu) memakai ambang MDG lebih longgar
 * (Band 24 bln, JG 12 bln) — sama seperti halaman detail karyawan & Usulan Promosi.
 * Non-shortlist memakai ambang normal (Band 36, JG 24, PG 12).
 */
class ReminderPromosiController extends Controller
{
    /** Batas "akan datang" dalam bulan. */
    private const WINDOW_BULAN = 3;

    public function index(Request $request)
    {
        $direktoratFilter = $request->direktorat;
        $jenisFilter      = $request->jenis; // naik_pg | naik_jg | naik_band

        // Peta shortlist Talent Pool (tahun ini / tahun lalu) → keringanan ambang MDG.
        $tahun = now()->year;
        $shortlistSet = array_flip(
            TalentPool::where('klasifikasi', 'shortlist')
                ->whereIn('periode', [$tahun, $tahun - 1])
                ->pluck('karyawan_id')
                ->all()
        );

        // Hanya karyawan aktif yang punya minimal satu TMT (kalau tidak, MDG = 0
        // dan sisa bulannya pasti > window → tidak masuk daftar; saring lebih awal).
        $karyawans = Karyawan::with(['jobGrade', 'personGrade', 'direktorat', 'jabatan'])
            ->where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNotNull('tanggal_mulai_pg')
                  ->orWhereNotNull('tanggal_mulai_jg');
            })
            ->orderBy('nama')
            ->get();

        $items = [];
        foreach ($karyawans as $k) {
            $isShortlist = isset($shortlistSet[$k->id]);

            // Ambang MDG: shortlist lebih longgar (selaras Usulan Promosi & detail karyawan).
            $minBand = $isShortlist ? 24 : 36;
            $minJg   = $isShortlist ? 12 : 24;
            $minPg   = 12;

            $sk = $k->statusKenaikan($minPg, $minJg, $minBand);

            if (($sk['status'] ?? null) === 'puncak') {
                continue; // sudah di grade tertinggi
            }

            $eligibleNow = ($sk['eligible'] ?? false) === true;
            $sisa        = (int) ($sk['sisa_bulan'] ?? 0);

            // Window: sudah eligible sekarang, ATAU akan eligible dalam 1–3 bulan.
            if (!$eligibleNow && ($sisa < 1 || $sisa > self::WINDOW_BULAN)) {
                continue;
            }

            $items[] = [
                'karyawan'     => $k,
                'sk'           => $sk,
                'eligible_now' => $eligibleNow,
                'sisa'         => $eligibleNow ? 0 : $sisa,
                'is_shortlist' => $isShortlist,
            ];
        }

        // Filter struktural (setelah window) — search nama dilakukan di sisi klien.
        if ($direktoratFilter) {
            $items = array_filter($items, fn ($i) =>
                ($i['karyawan']->direktorat->nama_direktorat ?? '') === $direktoratFilter);
        }
        if ($jenisFilter) {
            $items = array_filter($items, fn ($i) => $i['sk']['status'] === $jenisFilter);
        }
        $items = array_values($items);

        // Urutkan: paling mendesak dulu (sisa terkecil), lalu nama
        usort($items, function ($a, $b) {
            return [$a['sisa'], $a['karyawan']->nama] <=> [$b['sisa'], $b['karyawan']->nama];
        });

        $countNow      = count(array_filter($items, fn ($i) => $i['eligible_now']));
        $countSoon     = count($items) - $countNow;
        $countShortlist = count(array_filter($items, fn ($i) => $i['is_shortlist']));

        $direktorats = Direktorat::orderBy('nama_direktorat')->get();

        return view('reminder_promosi.index', [
            'items'            => $items,
            'countNow'         => $countNow,
            'countSoon'        => $countSoon,
            'countShortlist'   => $countShortlist,
            'totalDinilai'     => $karyawans->count(),
            'direktorats'      => $direktorats,
            'direktoratFilter' => $direktoratFilter,
            'jenisFilter'      => $jenisFilter,
            'windowBulan'      => self::WINDOW_BULAN,
        ]);
    }
}
