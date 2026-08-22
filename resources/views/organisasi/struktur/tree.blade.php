@extends('layouts.app')
@section('title', 'Pohon Organisasi')
@section('breadcrumb-parent', $versi->nomor_sk)
@section('breadcrumb', 'Pohon Organisasi')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .toolbar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px; }
    .btn-toolbar { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12.5px;font-weight:600;border:1px solid #e5e7eb;background:white;color:#374151;cursor:pointer;font-family:inherit; }
    .btn-toolbar:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .legend { display:flex;align-items:center;gap:16px;margin-left:auto;font-size:11.5px;color:#6b7280;flex-wrap:wrap; }
    .legend-item { display:flex;align-items:center;gap:6px; }
    .legend-swatch { width:14px;height:14px;border-radius:4px;border:1.5px solid #e5e7eb;background:white; }
    .legend-swatch.leaf { border-radius:9px;border-style:dashed;border-color:#a78bfa; }

    .search-box { display:flex;align-items:center;gap:8px;background:white;border:1.5px solid #e5e7eb;border-radius:8px;padding:7px 12px;min-width:220px; }
    .search-box:focus-within { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .search-box svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-box input { border:none;outline:none;font-size:12.5px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-box input::placeholder { color:#9ca3af; }
    .search-count { font-size:11px;color:#6b7280;white-space:nowrap; }

    /* ===== Container scroll ===== */
    .tree-scroll-wrap { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow-x:auto;overflow-y:hidden;padding:36px 24px; }
    .tree-scroll-inner { display:inline-flex;justify-content:center;min-width:100%; }

    /* ===== Org box ===== */
    .org-node { display:flex;flex-direction:column;align-items:center; }
    /* height:160px TETAP (bukan min-height) — SENGAJA, ini yg menjamin semua node level
       sama sejajar sempurna lintas cabang (Masalah A): tanpa tinggi box yg konstan, garis
       tier-spacer (org-tier-spacer, lihat bawah) tidak akan pernah bisa dijamin PERSIS
       sama tingginya dgn box asli, krn box asli tingginya ikut memanjang/memendek sesuai
       jumlah baris nama unit. 160px dibudget cukup utk 3 baris nama (line-clamp di
       .org-box-name) + padding-top terbesar (26px, dipakai varian fungsional). Kalau
       angka ini diubah, WAJIB update juga $tierRowHeight di org-tree-node.blade.php. */
    .org-box { width:190px;height:160px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 12px;box-shadow:0 1px 2px rgba(16,24,40,.04);position:relative;flex-shrink:0; }
    .org-box-leaf { border-radius:22px;border-style:dashed;border-color:#c4b5fd;background:#faf8ff; }
    /* Level Fungsional = cylinder/drum (meniru manning chart PDF PIM): oval di atas &
       bawah, sisi kiri-kanan lurus. Fungsional selalu leaf (tidak pernah punya anak) jadi
       TIDAK pakai org-box-leaf dashed spt level lain — cylinder-nya sendiri sudah jadi
       penanda visual "ujung cabang". Radius vertikal 24px (naik dari 16px) biar lengkungan
       lebih jelas ke-baca sbg oval/tabung, bukan kotak bersudut membulat dikit. SENGAJA
       cuma 1 lengkungan per sisi (dari border-radius box itu sendiri) — versi awal sempat
       nambah ::before sbg garis "seam" tutup drum, tapi itu bikin sisi atas kelihatan 2
       lengkungan menumpuk (dihapus). padding-top >= radius (26px) biar teks tidak
       kepotong lengkungan. */
    .org-box-fungsional { border-radius:50%/24px; padding:26px 12px 10px; }
    .org-box-highlight { border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.25);background:#fffbeb; }
    .org-box-top { display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:4px; }
    .org-box-level { font-size:9.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px; }
    .org-box-top-actions { display:flex;align-items:center;gap:5px;flex-shrink:0; }
    .org-toggle { width:18px;height:18px;border-radius:5px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:12px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:inherit;flex-shrink:0; }
    .org-toggle:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .org-history-btn { width:18px;height:18px;border-radius:5px;border:1px solid #e5e7eb;background:#f9fafb;color:#6b7280;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none; }
    .org-history-btn:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    {{-- 14px (naik dari 11px): tombol pembungkus TETAP 18x18 (interior 16x16 stlh border
         1px box-sizing:border-box) — 14px sisa 1px margin tiap sisi, cukup besar utk
         lebih mudah dilihat/diklik tapi tidak mepet ke border tombol. 15px sisa cuma
         0.5px, terlalu mepet. Lihat .org-jobprofile-btn svg (sama persis, biar konsisten). --}}
    .org-history-btn svg { width:14px;height:14px; }
    /* Badge indikator Job Profile — 2 state warna SAMA PERSIS dgn skema yg sudah dipakai
       di halaman Job Profile sendiri (.profile-count-badge has/none di organisasi/
       job-profile/show.blade.php): hijau = ada, abu-abu netral = belum ada (BUKAN
       merah/warning — kondisi data wajar, bukan error). */
    .org-jobprofile-btn { width:18px;height:18px;border-radius:5px;border:1px solid transparent;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none; }
    .org-jobprofile-btn svg { width:14px;height:14px; }
    .org-jobprofile-btn.has { background:#dcfce7;color:#15803d;border-color:#bbf7d0; }
    .org-jobprofile-btn.has:hover { background:#bbf7d0; }
    .org-jobprofile-btn.none { background:#f3f4f6;color:#9ca3af; }
    .org-jobprofile-btn.none:hover { background:#e5e7eb;color:#6b7280; }
    /* Icon Kompetensi Teknis — elemen BARU di sebelah icon Job Profile (fitur beda,
       tombol Job Profile di atas TIDAK disentuh). Ungu (SAMA dgn skema warna
       status-pecah/status-gabung yg sudah ada di project ini, mis. unit-timeline.blade.php)
       biar beda jelas dari hijau (Job Profile) & abu-abu (Riwayat), sengaja BUKAN amber
       krn amber sudah dipakai konsisten sbg warna highlight pencarian (.org-box-highlight). */
    .org-komtek-btn { width:18px;height:18px;border-radius:5px;border:1px solid #ddd6fe;background:#f5f3ff;color:#7c3aed;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none; }
    .org-komtek-btn:hover { background:#ede9fe; }
    .org-komtek-btn svg { width:14px;height:14px; }
    /* line-clamp 3 baris + ellipsis — pasangan dari height:160px tetap di .org-box di
       atas: nama unit TIDAK BOLEH lagi memanjangkan box (itu yg bikin tinggi box beda2
       per cabang), jadi dibatasi keras 3 baris. Data terpanjang saat ini (43 karakter)
       masih muat 2 baris, jadi tidak ada nama yg ke-potong hari ini — ini jaring pengaman
       utk nama yg jauh lebih panjang di masa depan. */
    .org-box-name { font-size:12.5px;font-weight:700;color:#111827;line-height:1.3;margin-bottom:8px;min-height:32px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;line-clamp:3;overflow:hidden; }
    .org-box-stats { display:flex;flex-direction:column;gap:3px;border-top:1px solid #f3f4f6;padding-top:6px; }
    .org-stat { display:flex;align-items:center;justify-content:space-between;font-size:11px; }
    .org-stat-label { color:#9ca3af; }
    .org-stat-val { font-weight:700;color:#111827; }
    .org-child-count { position:absolute;bottom:-9px;left:50%;transform:translateX(-50%);background:#111827;color:white;font-size:9.5px;font-weight:600;padding:2px 8px;border-radius:20px;white-space:nowrap; }

    /* ===== Connector lines (pola CSS org-chart klasik) ===== */
    .org-children { padding-top:28px;position:relative; }
    .org-children::before { content:'';position:absolute;top:0;left:50%;width:0;border-left:1.5px solid #d1d5db;height:28px; }
    .org-children-inner { display:flex;justify-content:center; }
    .org-child-branch { position:relative;padding:28px 16px 0 16px; }
    .org-child-branch::before,
    .org-child-branch::after { content:'';position:absolute;top:0;right:50%;border-top:1.5px solid #d1d5db;width:50%;height:28px; }
    .org-child-branch::after { right:auto;left:50%;border-left:1.5px solid #d1d5db; }
    .org-child-branch:first-child::before { border:0 none; }
    /* FIX (garis buntu di node paling kanan): dulu salah nge-null-kan border-left
       (drop vertikal ke box anak terakhir sendiri) alih-alih border-top (sisa spine
       horizontal yg menjorok ke padding kosong di kanan branch terakhir, krn tidak
       ada sibling lagi di situ). Akibatnya box paling kanan tiap baris kehilangan
       garis turun ke dirinya sendiri — persis gejala "garis buntu di tengah". */
    .org-child-branch:last-child::after { border-top:0 none; }
    .org-child-branch:only-child { padding-top:0; }
    .org-child-branch:only-child::before,
    .org-child-branch:only-child::after { display:none; }

    /* Tier-spacer: garis pass-through utk tier level yg dilompati (mis. Bagian ->
       Fungsional langsung, lewat Seksi & Foreman) — lihat org-tree-node.blade.php.
       Selalu 1 "anak" (spacer lain atau node asli), jadi tanpa spine horizontal spt
       .org-child-branch (yg bisa punya banyak sibling); cukup 1 garis vertikal lurus
       di tengah. height di-set inline per instance dari component = kelipatan PERSIS
       $tierRowHeight (160px box + 56px gap konektor ganda, lihat catatan di
       org-tree-node.blade.php) — bukan taksiran lagi, HARUS tetap sinkron dgn
       height:160px di .org-box supaya 1 spacer = 1 box asli persis. */
    .org-tier-spacer { position:relative; }
    .org-tier-spacer::before { content:'';position:absolute;top:0;left:50%;width:0;border-left:1.5px solid #d1d5db;height:100%; }

    [x-cloak] { display:none !important; }

    @media (max-width:640px) {
        .legend { display:none; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.show', $versi) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Detail Versi
</a>

<div class="page-header">
    <div>
        <div class="page-title">Pohon Organisasi — SK {{ $versi->nomor_sk }}</div>
        <div class="page-sub">{{ $units->count() }} unit · berlaku sejak {{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</div>
    </div>
</div>

<div x-data>
<div class="toolbar">
    <button type="button" class="btn-toolbar" @click="$store.tree.expandAll()">
        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
        Expand All
    </button>
    <button type="button" class="btn-toolbar" @click="$store.tree.collapseAll()">
        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M4 14h6v6M20 10h-6V4M14 10l7-7M3 21l7-7"/></svg>
        Collapse All
    </button>
    <div class="search-box">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Cari nama unit..." x-model="$store.tree.searchQuery" @input.debounce.300ms="$store.tree.runSearch()">
    </div>
    <template x-if="$store.tree.searchQuery">
        <span class="search-count" x-text="$store.tree.matchCount() + ' cocok'"></span>
    </template>
    <div class="legend">
        <div class="legend-item"><span class="legend-swatch"></span> Punya anak unit</div>
        <div class="legend-item"><span class="legend-swatch leaf"></span> Tanpa anak unit (ujung cabang)</div>
    </div>
</div>

<div class="tree-scroll-wrap">
    <div class="tree-scroll-inner">
        @if($roots->isEmpty())
            <div style="text-align:center;color:#9ca3af;padding:40px;font-size:13px;">Belum ada unit di versi ini.</div>
        @else
            @foreach($roots as $root)
                <x-org-tree-node :node="$root" :by-parent="$byParent" :totals="$totals" :job-profile-unit-ids="$jobProfileUnitIds" :job-profile-versi-id="$versi->id" :kompetensi-teknis-unit-ids="$kompetensiTeknisUnitIds" :kompetensi-teknis-versi-id="$versi->id" />
            @endforeach
        @endif
    </div>
</div>
</div>

@include('organisasi.struktur.partials.riwayat-overlay-shell')
@include('kompetensi_teknis.partials.overlay-shell')

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('tree', {
            expanded: Object.fromEntries(@json($defaultExpandedIds).map(id => [id, true])),
            allIds: @json($allIds),
            parentMap: @json($units->pluck('parent_unit_organisasi_id', 'unit_organisasi_id')),
            namesMap: @json($units->pluck('nama_unit', 'unit_organisasi_id')),
            searchQuery: '',

            isExpanded(id) {
                return !!this.expanded[id];
            },
            toggle(id) {
                this.expanded[id] = !this.expanded[id];
            },
            expandAll() {
                this.allIds.forEach(id => { this.expanded[id] = true; });
            },
            collapseAll() {
                this.expanded = {};
            },

            matches(id) {
                if (!this.searchQuery) return false;
                const name = this.namesMap[id] || '';
                return name.toLowerCase().includes(this.searchQuery.toLowerCase());
            },
            matchCount() {
                return this.allIds.filter(id => this.matches(id)).length;
            },
            runSearch() {
                if (!this.searchQuery) return;

                const matchedIds = this.allIds.filter(id => this.matches(id));
                matchedIds.forEach(id => {
                    let current = this.parentMap[id];
                    while (current) {
                        this.expanded[current] = true;
                        current = this.parentMap[current];
                    }
                });

                if (!matchedIds.length) return;
                setTimeout(() => {
                    const el = document.getElementById('org-node-' + matchedIds[0]);
                    el?.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
                }, 50);
            },
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
