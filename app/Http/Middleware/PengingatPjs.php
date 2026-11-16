<?php

namespace App\Http\Middleware;

use App\Services\ReminderPjsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * Pengingat PJS yang muncul sekali tiap kali login.
 *
 * Hanya muncul kalau ADA yang syaratnya sudah lengkap — MDG dan assessment
 * terpenuhi, sehingga bisa langsung diangkat tetap. Yang masih menunggu MDG
 * atau assessment tidak memunculkan apa-apa: tidak ada yang bisa dikerjakan
 * hari itu, dan pengingat yang muncul tiap login tanpa bisa ditindaklanjuti
 * cuma jadi gangguan sampai akhirnya diabaikan.
 *
 * Dihitung SEKALI per sesi, bukan tiap halaman: penandanya disimpan di session,
 * dan session dimulai bersih tiap login (logout memanggil invalidate()).
 */
class PengingatPjs
{
    /** Penanda bahwa pengingat sudah ditampilkan pada sesi ini. */
    private const KUNCI = 'pengingat_pjs.sudah';

    /** Berapa nama yang ditampilkan di kartu sebelum diringkas "dan N lainnya". */
    private const MAKS_NAMA = 4;

    public function handle(Request $request, Closure $next)
    {
        if ($this->perluDihitung($request)) {
            // Ditandai lebih dulu: kalaupun perhitungannya gagal, pengingat
            // tidak lantas muncul berulang di setiap halaman.
            $request->session()->put(self::KUNCI, true);

            try {
                if ($data = $this->kumpulkan()) {
                    View::share('pengingatPjs', $data);
                }
            } catch (\Throwable $e) {
                // Pengingat bukan hal kritis — jangan sampai menggagalkan halaman.
                Log::warning('Pengingat PJS gagal dihitung: ' . $e->getMessage());
            }
        }

        return $next($request);
    }

    private function perluDihitung(Request $request): bool
    {
        $user = Auth::user();

        return $user !== null
            && ! $request->session()->has(self::KUNCI)
            // Hanya halaman biasa: bukan AJAX (mis. polling notifikasi),
            // bukan unduhan, bukan POST/PATCH/DELETE.
            && $request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->expectsJson()
            && ! $user->isUser()
            && $user->canAccessMenu('pgs_pjs');
    }

    /** Hasil assessment yang membuat orang ini lolos, siap dicetak. */
    private function labelAssessment(array $i): string
    {
        if ($i['assessment_sumber'] === 'kompetensi' && $i['kompetensi']) {
            return ucwords(strtolower($i['kompetensi']->kesimpulan));
        }

        if ($i['rekomendasi']) {
            return match ($i['rekomendasi']->rekomendasi_final) {
                'ready'                  => 'Ready',
                'ready_with_development' => 'Ready with Development',
                default                  => $i['rekomendasi']->rekomendasi_final,
            };
        }

        return '-';
    }

    /** @return array<string,mixed>|null null bila tidak ada yang siap diangkat */
    private function kumpulkan(): ?array
    {
        $d = app(ReminderPjsService::class)->build();

        // Hanya yang syaratnya sudah lengkap. Tanpa ini, pengingat muncul tiap
        // login untuk orang yang baru bisa diproses berbulan-bulan lagi.
        $siap = array_values(array_filter(
            $d['items'],
            fn ($i) => $i['keadaan'] === ReminderPjsService::SIAP
        ));

        if (! $siap) {
            return null;
        }

        $orang = [];
        foreach (array_slice($siap, 0, self::MAKS_NAMA) as $i) {
            $orang[] = [
                'nama'    => $i['karyawan']->nama,
                'posisi'  => $i['posisi_pjs'] ?: '-',
                'lama'    => $i['pjs']->tanggal_mulai
                    ? \App\Models\Karyawan::formatMdgLengkap($i['pjs']->tanggal_mulai)
                    : null,
                'terlalu_lama' => (bool) $i['terlalu_lama'],
                // Dasar penilaiannya ikut ditampilkan supaya pengingat ini
                // bisa dipercaya tanpa perlu membuka halaman Reminder PJS.
                'mdg_jg'     => $i['mdg_jg_bulan'] . '/' . $i['min_mdg_jg'] . ' bln',
                'mdg_pg'     => $i['mdg_pg_bulan'] . '/' . $i['min_mdg_pg'] . ' bln',
                'assessment' => $this->labelAssessment($i),
            ];
        }

        return [
            'total'            => $d['total'],
            'siap'             => $d['siap'],
            'menunggu_mdg'     => $d['menunggu_mdg'],
            'perlu_assessment' => $d['perlu_assessment'],
            'lama'             => $d['lama'],
            'orang'            => $orang,
            'sisa'             => max(0, count($siap) - count($orang)),
        ];
    }
}
