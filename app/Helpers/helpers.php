<?php

if (! function_exists('initials')) {
    /**
     * Ambil inisial dari sebuah nama. Maksimal 3 huruf.
     *
     * - "Nida Ulfia"             => "NU"
     * - "Arief Budi Dharma"      => "ABD"
     * - "Muhammad Arief Budi D"  => "MAB"  (dibatasi 3 huruf)
     * - "Budi"                   => "BU"   (satu kata: 2 huruf pertama)
     * - ""                       => "?"
     *
     * @param  string|null  $nama
     * @param  int          $max  Batas maksimal jumlah huruf (default 3)
     */
    function initials(?string $nama, int $max = 3): string
    {
        $nama = trim((string) $nama);

        if ($nama === '') {
            return '?';
        }

        // Pecah jadi kata, buang spasi ganda / kosong
        $kata = preg_split('/\s+/', $nama, -1, PREG_SPLIT_NO_EMPTY);

        // Satu kata => ambil 2 huruf pertama
        if (count($kata) === 1) {
            return mb_strtoupper(mb_substr($kata[0], 0, 2));
        }

        // Banyak kata => huruf pertama tiap kata, lalu dibatasi $max
        $hasil = '';
        foreach ($kata as $k) {
            $hasil .= mb_substr($k, 0, 1);
        }

        return mb_strtoupper(mb_substr($hasil, 0, $max));
    }
}