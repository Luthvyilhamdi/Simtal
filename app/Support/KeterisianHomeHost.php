<?php

namespace App\Support;

use App\Models\Karyawan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Keterisian posisi Struktur Organisasi dalam dua sudut pandang:
 *
 *   HOME — seluruh karyawan PIM, TERMASUK yang sedang ditugaskan ke holding.
 *          Menjawab "berapa orang yang jadi tanggungan PIM".
 *   HOST — hanya yang benar-benar bekerja di PIM; yang ditugaskan DIKELUARKAN.
 *          Menjawab "berapa kursi di PIM yang benar-benar ada orangnya".
 *
 * Karyawan penugasan dikenali dari teks "Non Mutasi" (status penugasan DKU ke
 * PT Pupuk Indonesia). Baris mereka ber-mc_tko 0 — memang tidak memakan formasi
 * PIM — tapi pengisian-nya 1, sehingga ikut menaikkan angka keterisian kalau
 * tidak dipisahkan.
 *
 * CATATAN BAND: band di sini adalah band POSISI (dari job_grade posisi), bukan
 * band orangnya. Beda dengan kartu "Distribusi per Band" di dashboard yang
 * menghitung band KARYAWAN (dari job_grade karyawan) — keduanya memang tidak
 * akan sama. Contoh: posisi "Senior Operator Shift Amoniak" ber-JG 12 (Band 5)
 * diisi karyawan ber-JG 8 (Band 6). Dihitung di Band 5, karena formasinya di situ.
 */
class KeterisianHomeHost
{
    /** Penanda status penugasan pada posisi / jabatan saat ini. */
    public const PENANDA_PENUGASAN = 'non mutasi';

    /**
     * @return array{
     *     bulan:int, tahun:int, jumlah_penugasan:int,
     *     home: array{core:array,non_core:array,total:array,band:array},
     *     host: array{core:array,non_core:array,total:array,band:array}
     * }
     */
    public static function hitung(int $bulan, int $tahun): array
    {
        $baris = self::ambilBaris($bulan, $tahun);

        return [
            'bulan'            => $bulan,
            'tahun'            => $tahun,
            'jumlah_penugasan' => $baris->where('penugasan', true)->count(),
            'home'             => self::sisi($baris),
            'host'             => self::sisi($baris->where('penugasan', false)),
        ];
    }

    /** Periode Struktur Organisasi terbaru yang sudah ada datanya. */
    public static function periodeTerbaru(): array
    {
        $p = DB::table('struktur_organisasi')
            ->orderByDesc('tahun')->orderByDesc('bulan')
            ->first(['bulan', 'tahun']);

        return [(int) ($p->bulan ?? now()->month), (int) ($p->tahun ?? now()->year)];
    }

    /**
     * Baris posisi satu periode, sudah dilengkapi band & penanda penugasan.
     * Posisi '-' dibuang supaya sejalan dengan kartu statistik yang sudah ada.
     */
    protected static function ambilBaris(int $bulan, int $tahun): Collection
    {
        return DB::table('struktur_organisasi as so')
            ->leftJoin('karyawans as k', 'k.id', '=', 'so.karyawan_id')
            ->where('so.bulan', $bulan)
            ->where('so.tahun', $tahun)
            ->where('so.posisi', '!=', '-')
            ->get([
                'so.core', 'so.mc_tko', 'so.pengisian', 'so.posisi',
                'so.job_grade as jg_posisi',
                'k.jabatan_saat_ini',
            ])
            ->map(function ($r) {
                // Band SELALU dari JG posisi, tidak pernah dari JG orangnya.
                // MC/TKO melekat pada posisi, jadi pembilang & penyebut harus
                // memakai ukuran yang sama. Memakai JG orang sebagai cadangan
                // pernah dicoba dan membuat terisi masuk ke band yang MC-nya
                // ada di band lain — Band 2 jadi 115%, Band 3 106%.
                $jg = (int) ($r->jg_posisi ?: 0);

                return (object) [
                    'core'      => $r->core ?: '',
                    'mc'        => (int) $r->mc_tko,
                    'terisi'    => (int) $r->pengisian,
                    'band'      => $jg ? Karyawan::getBandFromGrade($jg) : '-',
                    'penugasan' => self::adalahPenugasan($r->posisi)
                                || self::adalahPenugasan($r->jabatan_saat_ini),
                ];
            });
    }

    /** Apakah teks ini menandakan karyawan sedang ditugaskan (bukan di PIM)? */
    public static function adalahPenugasan(?string $teks): bool
    {
        return $teks !== null && stripos($teks, self::PENANDA_PENUGASAN) !== false;
    }

    /** Rekap satu sisi (Home atau Host): per Core, per Band, dan totalnya. */
    protected static function sisi(Collection $baris): array
    {
        $urutanBand = array_keys(Karyawan::bandConfig());

        $band = [];
        foreach ($urutanBand as $nama) {
            $isi = $baris->where('band', $nama);
            if ($isi->isNotEmpty()) {
                $band[$nama] = self::sel($isi);
            }
        }
        $tanpaBand = $baris->where('band', '-');
        if ($tanpaBand->isNotEmpty()) {
            $band['Tanpa Band'] = self::sel($tanpaBand);
        }

        return [
            'core'     => self::sel($baris->where('core', 'Core')),
            'non_core' => self::sel($baris->where('core', 'Non Core')),
            'total'    => self::sel($baris),
            'band'     => $band,
        ];
    }

    /**
     * Satu sel angka. 'persen' null kalau tidak ada formasi (mc_tko 0) —
     * ditampilkan sebagai '—', bukan 0%, karena 0% menyesatkan: posisinya
     * memang tidak punya formasi untuk dibandingkan.
     */
    protected static function sel(Collection $baris): array
    {
        $mc     = (int) $baris->sum('mc');
        $terisi = (int) $baris->sum('terisi');

        return [
            'posisi'  => $baris->count(),
            'mc'      => $mc,
            'terisi'  => $terisi,
            'deviasi' => $terisi - $mc,
            'persen'  => $mc > 0 ? round($terisi / $mc * 100, 1) : null,
        ];
    }
}
