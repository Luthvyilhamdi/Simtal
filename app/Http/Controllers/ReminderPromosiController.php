<?php

namespace App\Http\Controllers;

use App\Models\Direktorat;
use App\Services\ReminderPromosiService;
use Illuminate\Http\Request;

/**
 * Reminder Promosi — murni informasi (read-only).
 *
 * Menampilkan karyawan yang SUDAH memenuhi syarat MDG untuk naik grade,
 * atau AKAN memenuhi dalam 1–3 bulan ke depan, agar HR bisa menyiapkan
 * usulan promosi tepat waktu. Tidak mengubah data apa pun.
 *
 * Perhitungan inti (termasuk keringanan shortlist) ada di ReminderPromosiService
 * agar sama persis dengan kartu ringkasan di Dashboard.
 */
class ReminderPromosiController extends Controller
{
    public function index(Request $request, ReminderPromosiService $service)
    {
        $direktoratFilter = $request->direktorat;
        $jenisFilter      = $request->jenis; // naik_pg | naik_jg | naik_band

        $data          = $service->build();
        $items         = $data['items'];
        $latestPeriode = $data['shortlistPeriode'];

        // Filter struktural (search nama dilakukan di sisi klien).
        if ($direktoratFilter) {
            $items = array_filter($items, fn ($i) =>
                ($i['karyawan']->direktorat->nama_direktorat ?? '') === $direktoratFilter);
        }
        if ($jenisFilter) {
            $items = array_filter($items, fn ($i) => $i['sk']['status'] === $jenisFilter);
        }
        $items = array_values($items);

        // Urutkan: paling mendesak dulu (sisa terkecil), lalu nama.
        usort($items, function ($a, $b) {
            return [$a['sisa'], $a['karyawan']->nama] <=> [$b['sisa'], $b['karyawan']->nama];
        });

        $countNow  = count(array_filter($items, fn ($i) => $i['eligible_now']));
        $countSoon = count($items) - $countNow;

        return view('reminder_promosi.index', [
            'items'            => $items,
            'countNow'         => $countNow,
            'countSoon'        => $countSoon,
            'totalDinilai'     => $data['totalDinilai'],
            'direktorats'      => Direktorat::orderBy('nama_direktorat')->get(),
            'direktoratFilter' => $direktoratFilter,
            'jenisFilter'      => $jenisFilter,
            'shortlistPeriode' => $latestPeriode,
            'windowBulan'      => ReminderPromosiService::WINDOW_BULAN,
            'disembunyikan'    => $data['disembunyikan'] ?? 0,
        ]);
    }
}
