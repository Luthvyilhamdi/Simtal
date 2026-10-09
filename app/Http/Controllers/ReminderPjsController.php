<?php

namespace App\Http\Controllers;

use App\Models\Direktorat;
use App\Services\ReminderPjsService;
use Illuminate\Http\Request;

/**
 * Reminder PJS — murni informasi (read-only).
 *
 * Menampilkan pemegang PJS yang sedang berjalan beserta status dua syarat
 * pengangkatan tetap: MDG dan hasil assessment. Tidak mengubah data apa pun.
 *
 * Perhitungannya ada di ReminderPjsService agar bisa dipakai bersama kartu
 * ringkasan di Dashboard tanpa menggandakan logika.
 */
class ReminderPjsController extends Controller
{
    public function index(Request $request, ReminderPjsService $service)
    {
        $data  = $service->build();
        $items = $data['items'];

        $direktoratFilter = $request->direktorat;
        $keadaanFilter    = $request->keadaan;

        if ($direktoratFilter) {
            $items = array_filter(
                $items,
                fn ($i) => ($i['karyawan']->direktorat->nama_direktorat ?? '') === $direktoratFilter
            );
        }
        if ($keadaanFilter) {
            $items = array_filter($items, fn ($i) => $i['keadaan'] === $keadaanFilter);
        }

        return view('reminder_pjs.index', [
            'items'            => array_values($items),
            'siap'             => $data['siap'],
            'menungguMdg'      => $data['menunggu_mdg'],
            'perluAssessment'  => $data['perlu_assessment'],
            'total'            => $data['total'],
            'lama'             => $data['lama'],
            'batasLama'        => ReminderPjsService::BATAS_LAMA_BULAN,
            'direktorats'      => Direktorat::orderBy('nama_direktorat')->get(),
            'direktoratFilter' => $direktoratFilter,
            'keadaanFilter'    => $keadaanFilter,
        ]);
    }
}
