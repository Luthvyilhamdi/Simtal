<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/** Saringan rentang waktu untuk daftar usulan (promosi & rotasi/mutasi). */
class PeriodeUsulan
{
    /** nilai => label. Nilai berupa jumlah BULAN ke belakang; '' = semua. */
    public const OPSI = [
        ''   => 'Semua Periode',
        '1'  => '1 Bulan',
        '3'  => '3 Bulan',
        '6'  => '6 Bulan',
        '12' => '1 Tahun',
    ];

    /** Nilai yang sah saja; selain itu dianggap 'semua'. */
    public static function bersihkan($nilai): string
    {
        $nilai = (string) $nilai;

        return array_key_exists($nilai, self::OPSI) ? $nilai : '';
    }

    /**
     * Batasi query ke N bulan terakhir.
     *
     * Dihitung dari tanggal_usulan, bukan created_at: yang dicari orang adalah
     * kapan usulannya diajukan, bukan kapan barisnya kebetulan dibuat. Baris
     * tanpa tanggal_usulan jatuh kembali ke created_at supaya tidak hilang.
     */
    public static function terapkan(Builder $query, $periode): Builder
    {
        $bulan = self::bersihkan($periode);
        if ($bulan === '') {
            return $query;
        }

        $batas = now()->subMonths((int) $bulan)->startOfDay();

        return $query->where(fn ($q) => $q
            ->where('tanggal_usulan', '>=', $batas)
            ->orWhere(fn ($q2) => $q2->whereNull('tanggal_usulan')->where('created_at', '>=', $batas)));
    }
}
