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

if (! function_exists('formatUnitLabel')) {
    /**
     * Tempel prefix level di depan nama unit utk tampilan (TIDAK mengubah data
     * tersimpan) — dipakai HANYA di halaman "Cari Unit" & tab "List" Timeline 1 Unit.
     *
     * - formatUnitLabel('Acara & Protokoler', 'fungsional') => "Fungsional Acara & Protokoler"
     * - formatUnitLabel('Humas', 'bagian')                  => "Bagian Humas"
     * - formatUnitLabel('Humas', null)                      => "Humas" (level tak dikenal, nama apa adanya)
     */
    function formatUnitLabel(?string $namaUnit, ?string $level): string
    {
        $prefixes = [
            'direktorat' => 'Direktorat',
            'kompartemen' => 'Kompartemen',
            'departemen' => 'Departemen',
            'bagian' => 'Bagian',
            'seksi' => 'Seksi',
            'foreman' => 'Foreman',
            'fungsional' => 'Fungsional',
        ];

        $prefix = $level !== null ? ($prefixes[$level] ?? null) : null;

        return $prefix ? "{$prefix} {$namaUnit}" : (string) $namaUnit;
    }
}

if (! function_exists('transisiCategoryColor')) {
    /**
     * Warna skema 8-kategori transisi unit organisasi (baseline/lanjut, rename,
     * pindah_induk, ganti_level, pecah, gabung, bubar, baru) — NILAINYA DISALIN PERSIS
     * dari $colorHex di resources/views/organisasi/struktur/compare.blade.php (Fitur B),
     * sumber kebenaran skema warna ini di project. Disalin (bukan diekstrak jadi 1
     * pemanggilan bersama) krn compare.blade.php sengaja tidak disentuh utk task manapun
     * yg memakai helper ini — kalau skema warna Fitur B pernah berubah, update juga
     * array ini secara manual supaya tetap sinkron.
     *
     * $category null (atau nilai apapun di luar 7 key yg dikenal) -> abu-abu, dipakai
     * utk "Baseline/Lanjut" (versi tanpa perubahan berarti) — reuse token gray netral
     * yg sudah ada di project (#9ca3af/#6b7280/#f9fafb, lihat mis. .badge-lampau &
     * .org-box-level di halaman lain), BUKAN warna baru.
     */
    function transisiCategoryColor(?string $category): array
    {
        $colors = [
            'rename'       => ['fill' => '#f5f3ff', 'border' => '#7c3aed', 'text' => '#6d28d9'],
            'pindah_induk' => ['fill' => '#dbeafe', 'border' => '#1d4ed8', 'text' => '#1d4ed8'],
            'ganti_level'  => ['fill' => '#fffbeb', 'border' => '#b45309', 'text' => '#92400e'],
            'pecah'        => ['fill' => '#ecfeff', 'border' => '#0891b2', 'text' => '#0e7490'],
            'gabung'       => ['fill' => '#fff5f4', 'border' => '#f97066', 'text' => '#c2410c'],
            'bubar'        => ['fill' => '#fef2f2', 'border' => '#dc2626', 'text' => '#dc2626'],
            'baru'         => ['fill' => '#f0fdf4', 'border' => '#16a34a', 'text' => '#15803d'],
        ];

        return $category !== null && isset($colors[$category])
            ? $colors[$category]
            : ['fill' => '#f9fafb', 'border' => '#9ca3af', 'text' => '#6b7280'];
    }
}

if (! function_exists('transisiCategoryLabel')) {
    /** Label tampil utk 1 kategori transisi (dipakai bareng transisiCategoryColor()). */
    function transisiCategoryLabel(?string $category): string
    {
        $labels = [
            'rename'       => 'Rename',
            'pindah_induk' => 'Pindah Induk',
            'ganti_level'  => 'Ganti Level',
            'pecah'        => 'Pecah',
            'gabung'       => 'Gabung',
            'bubar'        => 'Bubar',
            'baru'         => 'Baru',
        ];

        return $category !== null && isset($labels[$category])
            ? $labels[$category]
            : 'Baseline / Lanjut';
    }
}