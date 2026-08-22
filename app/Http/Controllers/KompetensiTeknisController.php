<?php

namespace App\Http\Controllers;

use App\Models\StrukturOrganisasiVersi;
use App\Models\UnitKompetensiTeknis;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;

// TODO: kalau nanti ada fitur CRUD manual dari UI (tambah/ubah/hapus mapping kompetensi
// teknis lewat halaman ini), tambahkan `use App\Traits\LogsActivity;` + `use LogsActivity;`
// di controller ini, ikuti pola JobProfileController — belum perlu selama masih read-only.
class KompetensiTeknisController extends Controller
{
    /**
     * Halaman list/search read-only — MASTER per POSISI (kombinasi unit + jenjang_jabatan
     * + struktur_organisasi_versi_id), bukan per-baris-kompetensi lagi. Query dasarnya
     * (join snapshot + kompetensi_teknis + versi) SAMA PERSIS spt sebelumnya, cuma sekarang
     * hasil flat-nya di-group DI SISI PHP (Collection::groupBy(), bukan SQL GROUP BY) krn
     * dataset masih ratusan baris — lebih simpel & gampang dibaca drpd raw GROUP_CONCAT/MIN()
     * utk menghindari isu ONLY_FULL_GROUP_BY. Tiap grup diringkas jadi 1 "posisi" dgn
     * jumlah_kompetensi (COUNT), komb_list (SET kombinasi "asal-prioritas" yg ada di grup
     * itu, mis. "native-primary" — gantikan tipe_list lama stlh kolom tipe dipecah jadi
     * asal+prioritas, dipakai filter tetap bisa narrow ke posisi yg PUNYA kombinasi itu),
     * & kompetensi_names (gabungan nama kompetensi, utk search tetap bisa nemuin posisi
     * lewat nama kompetensi meski tidak ditampilkan di baris master). Search & filter
     * tetap vanilla JS di sisi klien.
     */
    public function index(Request $request)
    {
        $flatRows = UnitKompetensiTeknis::query()
            ->join('unit_organisasi_snapshot as uos', function ($join) {
                $join->on('uos.unit_organisasi_id', '=', 'unit_kompetensi_teknis.unit_organisasi_id')
                    ->on('uos.struktur_organisasi_versi_id', '=', 'unit_kompetensi_teknis.struktur_organisasi_versi_id');
            })
            ->join('kompetensi_teknis as kt', 'kt.id', '=', 'unit_kompetensi_teknis.kompetensi_teknis_id')
            ->join('job_family as jf', 'jf.id', '=', 'kt.job_family_id')
            ->join('struktur_organisasi_versi as sov', 'sov.id', '=', 'unit_kompetensi_teknis.struktur_organisasi_versi_id')
            ->select([
                'unit_kompetensi_teknis.id',
                'unit_kompetensi_teknis.unit_organisasi_id',
                'unit_kompetensi_teknis.struktur_organisasi_versi_id',
                'unit_kompetensi_teknis.jenjang_jabatan',
                'unit_kompetensi_teknis.urutan_jenjang',
                'unit_kompetensi_teknis.grade',
                'unit_kompetensi_teknis.nama_jobs',
                'unit_kompetensi_teknis.managerial',
                'unit_kompetensi_teknis.level',
                'unit_kompetensi_teknis.asal',
                'unit_kompetensi_teknis.prioritas',
                'uos.nama_unit',
                'uos.level as unit_level',
                'kt.nama_kompetensi',
                'jf.nama as job_family_nama',
                'sov.nomor_sk',
            ])
            ->orderBy('uos.nama_unit')
            ->orderBy('unit_kompetensi_teknis.urutan_jenjang')
            ->orderBy('kt.nama_kompetensi')
            ->get();

        $positions = $flatRows
            ->groupBy(fn ($r) => $r->unit_organisasi_id . '|' . $r->jenjang_jabatan . '|' . $r->struktur_organisasi_versi_id)
            ->map(function ($group) {
                $first = $group->first();

                return (object) [
                    'unit_organisasi_id'           => $first->unit_organisasi_id,
                    'struktur_organisasi_versi_id' => $first->struktur_organisasi_versi_id,
                    'jenjang_jabatan'              => $first->jenjang_jabatan,
                    'urutan_jenjang'               => $first->urutan_jenjang,
                    'grade'                        => $first->grade,
                    'nama_jobs'                    => $first->nama_jobs,
                    'managerial'                   => $first->managerial,
                    'nama_unit'                    => $first->nama_unit,
                    'unit_level'                   => $first->unit_level,
                    'nomor_sk'                     => $first->nomor_sk,
                    'jumlah_kompetensi'            => $group->count(),
                    // "native-primary" dst — kombinasi asal+prioritas yg ADA di grup ini,
                    // dipakai filter (lihat index.blade.php), gantikan tipe_list lama.
                    'komb_list'                    => $group->map(fn ($r) => $r->asal . '-' . $r->prioritas)->unique()->values()->all(),
                    'kompetensi_names'             => $group->pluck('nama_kompetensi')->implode(' '),
                    // Dipakai search text (lihat index.blade.php) supaya link "?rumpun=..."
                    // dari hasil commit Step 3 bisa auto-filter ke posisi yg baru diimport,
                    // meski nama job_family sendiri tidak ditampilkan sbg kolom di baris master.
                    'rumpun_asal_list'             => $group->pluck('job_family_nama')->filter()->unique()->implode(' '),
                ];
            })
            ->sortBy([['nama_unit', 'asc'], ['urutan_jenjang', 'asc']])
            ->values();

        $versiList      = StrukturOrganisasiVersi::orderByDesc('tanggal_mulai_berlaku')->get();
        $unitOptions    = $flatRows->pluck('nama_unit')->unique()->sort()->values();
        $jenjangOptions = $flatRows->sortBy('urutan_jenjang')->pluck('jenjang_jabatan')->unique()->values();

        return view('kompetensi_teknis.index', compact('positions', 'versiList', 'unitOptions', 'jenjangOptions'));
    }

    /**
     * Isi panel overlay Kompetensi Teknis (dipicu dari icon award di Tree View) — return
     * PARTIAL saja (bukan halaman penuh, tanpa <style>/<script>), di-fetch via vanilla JS
     * fetch() & di-inject lewat innerHTML (lihat kompetensi_teknis/partials/overlay-shell.
     * blade.php). Pola & alasannya SAMA PERSIS dgn unitRiwayatOverlay() di
     * StrukturOrganisasiVersiController — CSS overlay ini WAJIB sudah dideklarasikan di
     * shell (halaman induk), bukan di partial ini, krn partial ini tidak pernah render
     * lewat siklus @push('styles') Blade biasa saat dipanggil standalone via AJAX.
     */
    public function unitOverlay(UnitOrganisasi $unit, Request $request)
    {
        $versi = StrukturOrganisasiVersi::findOrFail((int) $request->query('versi'));

        $snapshot = $versi->unitOrganisasiSnapshots()
            ->where('unit_organisasi_id', $unit->id)
            ->first();

        $rows = UnitKompetensiTeknis::with('kompetensiTeknis')
            ->where('unit_organisasi_id', $unit->id)
            ->where('struktur_organisasi_versi_id', $versi->id)
            ->orderBy('urutan_jenjang')
            ->orderByRaw("FIELD(CONCAT(asal,'-',prioritas), 'native-primary','native-secondary','generic-primary','generic-secondary')")
            ->get();

        // Kelompokkan per jenjang_jabatan, urutan grup ikut urutan_jenjang ASC (bukan
        // urutan groupBy() yg berdasar kemunculan pertama tiap key di $rows — kebetulan
        // sama krn $rows sudah di-order duluan, tapi usort di bawah ini jaminan eksplisit).
        $groups = [];
        foreach ($rows as $row) {
            $key = $row->jenjang_jabatan;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'jenjang_jabatan' => $row->jenjang_jabatan,
                    'urutan_jenjang'  => $row->urutan_jenjang,
                    'grade'           => $row->grade,
                    'nama_jobs'       => $row->nama_jobs,
                    'managerial'      => $row->managerial,
                    'items'           => [],
                ];
            }
            $groups[$key]['items'][] = $row;
        }
        usort($groups, fn ($a, $b) => $a['urutan_jenjang'] <=> $b['urutan_jenjang']);

        return view('kompetensi_teknis.partials.unit-overlay', [
            'unit'     => $unit,
            'snapshot' => $snapshot,
            'versi'    => $versi,
            'groups'   => $groups,
        ]);
    }

    /**
     * Isi panel overlay Kompetensi Teknis PER POSISI (dipicu dari tombol "Detail" di
     * halaman list Kompetensi Teknis, bukan dari Tree View) — endpoint BARU, TERPISAH dari
     * unitOverlay() di atas. unitOverlay() SENGAJA tidak diubah/dipakai ulang krn itu masih
     * dipakai icon award di Tree View (org-tree-node.blade.php) yg behaviornya HARUS tetap
     * menampilkan SEMUA jenjang di 1 unit — beda kebutuhan dgn overlay ini yg difilter ke
     * 1 kombinasi unit+jenjang (1 posisi) saja lewat query string ?jenjang=.
     *
     * Return PARTIAL murni (tanpa <style>/<script>), di-fetch via openPosisiOverlay() &
     * di-inject innerHTML ke #komtekOverlayBody — REUSE shell & CSS yg sama dgn
     * unitOverlay() (kompetensi_teknis/partials/overlay-shell.blade.php), krn diminta
     * gaya visual SAMA PERSIS dgn overlay yg sudah ada.
     */
    public function posisiOverlay(UnitOrganisasi $unit, Request $request)
    {
        $versi   = StrukturOrganisasiVersi::findOrFail((int) $request->query('versi'));
        $jenjang = (string) $request->query('jenjang');

        $snapshot = $versi->unitOrganisasiSnapshots()
            ->where('unit_organisasi_id', $unit->id)
            ->first();

        $items = UnitKompetensiTeknis::with('kompetensiTeknis')
            ->where('unit_organisasi_id', $unit->id)
            ->where('struktur_organisasi_versi_id', $versi->id)
            ->where('jenjang_jabatan', $jenjang)
            ->orderByRaw("FIELD(CONCAT(asal,'-',prioritas), 'native-primary','native-secondary','generic-primary','generic-secondary')")
            ->get();

        $posisi = $items->isNotEmpty() ? [
            'jenjang_jabatan' => $jenjang,
            'grade'           => $items->first()->grade,
            'nama_jobs'       => $items->first()->nama_jobs,
            'managerial'      => $items->first()->managerial,
        ] : null;

        return view('kompetensi_teknis.partials.posisi-overlay', [
            'unit'     => $unit,
            'snapshot' => $snapshot,
            'versi'    => $versi,
            'posisi'   => $posisi,
            'items'    => $items,
        ]);
    }
}
