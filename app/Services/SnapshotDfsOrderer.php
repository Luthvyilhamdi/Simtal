<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Urutkan snapshot units scr DFS top-down mengikuti struktur parent-child ASLI
 * (root -> semua descendant-nya PENUH dulu, baru lanjut ke root/sibling berikutnya)
 * — BUKAN flat alfabetis/level spt query default. Sibling (baik root maupun anak)
 * diurutkan alfabetis by nama_unit di tiap tingkat.
 *
 * Diekstrak dari StrukturOrganisasiVersiController::dfsOrderSnapshots() (logic apa
 * adanya, bukan logic baru) supaya bisa dipakai bareng lintas controller
 * (StrukturOrganisasiVersiController::show()/exportPdf() dan JobProfileController::show())
 * tanpa duplikasi.
 *
 * PENGECUALIAN PENTING: anak yg levelnya SENDIRI = 'direktorat' TIDAK PERNAH
 * di-descend sbg bagian DFS parent-nya, walau scr data parent_unit_organisasi_id dia
 * memang child (satu2nya kasus ini terjadi: SEMUA direktorat lain adalah child dari
 * "Utama", satu2nya node dgn parent NULL sungguhan). Kalau didescend spt anak biasa,
 * begitu direktorat pertama ketemu (mis. "Keuangan & Umum") DFS langsung nyemplung ke
 * seluruh subtree-nya SEBELUM anak non-direktorat Utama yg LAIN (Kompartemen SPI,
 * Sekretaris Perusahaan, dst) sempat tampil — salah. Jadi tiap kali walk() nemu anak
 * level='direktorat', ditunda (masuk antrian $direktoratChildren) & BARU diproses
 * (masing2 sbg root top-level baru, depth=0, urutan sesama direktorat ttp alfabetis)
 * SETELAH semua anak non-direktorat node saat ini tuntas total.
 */
class SnapshotDfsOrderer
{
    /**
     * @return array<int, array{node: \App\Models\UnitOrganisasiSnapshot, depth: int}>
     */
    public static function order(Collection $units): array
    {
        $byParent = $units->groupBy('parent_unit_organisasi_id');
        $roots    = $byParent->get(null, collect())->values()->sortBy('nama_unit');

        $ordered = [];
        $walk = function ($node, $depth) use (&$walk, &$ordered, $byParent) {
            $ordered[] = ['node' => $node, 'depth' => $depth];

            $children = $byParent->get($node->unit_organisasi_id, collect())->sortBy('nama_unit');
            $direktoratChildren = $children->filter(fn ($c) => $c->level === 'direktorat')->values();
            $lainnya            = $children->reject(fn ($c) => $c->level === 'direktorat')->values();

            foreach ($lainnya as $child) {
                $walk($child, $depth + 1);
            }
            foreach ($direktoratChildren as $child) {
                $walk($child, 0);
            }
        };
        foreach ($roots as $root) {
            $walk($root, 0);
        }

        return $ordered;
    }
}
