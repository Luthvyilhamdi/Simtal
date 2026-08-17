@extends('layouts.app')
@section('title', 'Perbandingan Versi Struktur Organisasi')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Perbandingan Versi')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { margin-bottom:8px; }
    .page-title { font-size:20px;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .page-title .arrow { color:#9ca3af;font-weight:400; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:6px; }

    .hop-trail { font-size:11.5px;color:#9ca3af;margin-bottom:20px;display:flex;align-items:center;gap:6px;flex-wrap:wrap; }
    .hop-chip { background:#f3f4f6;border-radius:20px;padding:3px 10px;color:#374151;font-weight:600; }

    .ringkasan-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
    .ringkasan-item { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px 16px;text-align:center; }
    .ringkasan-num { font-size:22px;font-weight:800;color:#111827; }
    .ringkasan-label { font-size:11px;color:#6b7280;margin-top:2px; }
    .ringkasan-item.anomali .ringkasan-num { color:#d97706; }

    .unchanged-note { background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;margin-bottom:24px;display:flex;align-items:center;gap:8px; }

    .section-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:22px 26px;margin-bottom:16px; }
    .section-title { display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#111827;margin-bottom:14px; }
    .section-badge { font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:#f3f4f6;color:#374151; }

    .diff-list { display:flex;flex-direction:column;gap:8px; }
    .diff-row { display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fafafa;border-radius:9px;font-size:13px;flex-wrap:wrap; }
    .diff-name { font-weight:600;color:#111827; }
    .diff-arrow { color:#9ca3af; }
    .diff-old { color:#9ca3af;text-decoration:line-through; }
    .diff-new { color:#15803d;font-weight:600; }
    .diff-list-multi { display:flex;flex-wrap:wrap;gap:6px; }
    .diff-pill { background:white;border:1px solid #e5e7eb;border-radius:7px;padding:3px 10px;font-size:12px;color:#374151; }

    .anomali-row { padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px; }
    .anomali-name { font-weight:700;color:#92400e;font-size:13px;margin-bottom:6px; }
    .anomali-detail { font-size:12px;color:#78350f;margin-left:16px;list-style:disc; }

    .section-card.section-baru .section-badge { background:#eff6ff;color:#1d4ed8; }
    .section-card.section-bubar .section-badge { background:#fef2f2;color:#dc2626; }
    .section-card.section-anomali .section-badge { background:#fffbeb;color:#92400e; }

    @media (max-width:900px) {
        .ringkasan-grid { grid-template-columns:repeat(2,1fr); }
    }

    /* ===== Tab toggle (pola SAMA PERSIS dgn Tampilan Diagram/List di Timeline Unit) ===== */
    .view-tabs { display:flex;gap:8px;margin-bottom:16px; }
    .view-tab { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12.5px;font-weight:600;border:1px solid #e5e7eb;background:white;color:#6b7280;cursor:pointer;font-family:inherit; }
    .view-tab svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }
    .view-tab:hover { background:#f9fafb; }
    .view-tab.active { background:#15803d;border-color:#15803d;color:white; }
    [x-cloak] { display:none !important; }

    /* ===== Komponen tree-node — DIDUPLIKASI dari tree.blade.php (komponennya sendiri
       tidak bawa style, bergantung penuh pada CSS halaman yg meng-include-nya, sama
       spt tree.blade.php melakukannya) — TIDAK ada perubahan apapun pada komponennya. ===== */
    .tree-scroll-wrap { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow-x:auto;overflow-y:hidden;padding:36px 24px; }
    .tree-scroll-inner { display:inline-flex;justify-content:center;min-width:100%; }
    .org-node { display:flex;flex-direction:column;align-items:center; }
    /* height:240px TETAP (bukan min-height) — lihat catatan panjang di tree.blade.php
       soal kenapa ini WAJIB utk perataan tier lintas cabang (Masalah A). Angka di
       halaman INI beda dari tree.blade.php/import-lanjutan-preview.blade.php (160px)
       krn CUMA di Compare ada narasi diff ringkas yg di-inject ::after ke
       .org-box-stats di bawah (maks ~78 karakter mentah, s.d. ~96 stlh escape/3 baris,
       lihat $truncatePlain) —
       budget 160px tidak akan cukup nampung itu tanpa overflow ke baris berikutnya.
       WAJIB tetap sinkron dgn prop box-height="240" di 2 pemanggil komponen tree-node
       di bawah (kolom lama & baru). */
    .org-box { width:190px;height:240px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 12px;box-shadow:0 1px 2px rgba(16,24,40,.04);position:relative;flex-shrink:0; }
    .org-box-leaf { border-radius:22px;border-style:dashed;border-color:#c4b5fd;background:#faf8ff; }
    /* Level Fungsional = cylinder/drum — lihat catatan sama di tree.blade.php. */
    .org-box-fungsional { border-radius:50%/24px; padding:26px 12px 10px; }
    .org-box-highlight { border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.25);background:#fffbeb; }
    .org-box-top { display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:4px; }
    .org-box-level { font-size:9.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px; }
    .org-box-top-actions { display:flex;align-items:center;gap:5px;flex-shrink:0; }
    .org-toggle { width:18px;height:18px;border-radius:5px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:12px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:inherit;flex-shrink:0; }
    .org-toggle:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .org-history-btn { width:18px;height:18px;border-radius:5px;border:1px solid #e5e7eb;background:#f9fafb;color:#6b7280;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none; }
    .org-history-btn:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .org-history-btn svg { width:11px;height:11px; }
    /* line-clamp 3 baris — pasangan dari height:240px tetap di .org-box. Lihat catatan
       sama di tree.blade.php. */
    .org-box-name { font-size:12.5px;font-weight:700;color:#111827;line-height:1.3;margin-bottom:8px;min-height:32px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;line-clamp:3;overflow:hidden; }
    .org-box-stats { display:flex;flex-direction:column;gap:3px;border-top:1px solid #f3f4f6;padding-top:6px; }
    .org-stat { display:flex;align-items:center;justify-content:space-between;font-size:11px; }
    .org-stat-label { color:#9ca3af; }
    .org-stat-val { font-weight:700;color:#111827; }
    .org-child-count { position:absolute;bottom:-9px;left:50%;transform:translateX(-50%);background:#111827;color:white;font-size:9.5px;font-weight:600;padding:2px 8px;border-radius:20px;white-space:nowrap; }
    .org-children { padding-top:28px;position:relative; }
    .org-children::before { content:'';position:absolute;top:0;left:50%;width:0;border-left:1.5px solid #d1d5db;height:28px; }
    .org-children-inner { display:flex;justify-content:center; }
    .org-child-branch { position:relative;padding:28px 16px 0 16px; }
    .org-child-branch::before,
    .org-child-branch::after { content:'';position:absolute;top:0;right:50%;border-top:1.5px solid #d1d5db;width:50%;height:28px; }
    .org-child-branch::after { right:auto;left:50%;border-left:1.5px solid #d1d5db; }
    .org-child-branch:first-child::before { border:0 none; }
    /* FIX (garis buntu di node paling kanan) — lihat catatan sama di tree.blade.php. */
    .org-child-branch:last-child::after { border-top:0 none; }
    .org-child-branch:only-child { padding-top:0; }
    .org-child-branch:only-child::before,
    .org-child-branch:only-child::after { display:none; }

    /* Tier-spacer (perataan by level) — lihat catatan sama di tree.blade.php &
       org-tree-node.blade.php. */
    .org-tier-spacer { position:relative; }
    .org-tier-spacer::before { content:'';position:absolute;top:0;left:50%;width:0;border-left:1.5px solid #d1d5db;height:100%; }

    /* ===== Tampilan Visual — 2 kolom + legend + popover ===== */
    .compare-visual-header { display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px; }
    .compare-legend { display:flex;align-items:center;gap:14px;flex-wrap:wrap;font-size:11.5px;color:#6b7280; }
    .compare-legend-item { display:flex;align-items:center;gap:6px; }
    .compare-legend-clickable { cursor:pointer;user-select:none; }
    .compare-legend-clickable input[type="checkbox"] { width:13px;height:13px;margin:0;cursor:pointer;accent-color:#15803d; }
    .compare-legend-swatch { width:14px;height:14px;border-radius:4px;border-width:1.5px;border-style:solid;flex-shrink:0; }
    .compare-level-select { flex-shrink:0;background:white;border:1.5px solid #e5e7eb;border-radius:8px;padding:7px 12px;font-size:12.5px;font-family:inherit;color:#374151;cursor:pointer; }
    .compare-level-select:focus { outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .btn-toolbar { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12.5px;font-weight:600;border:1px solid #e5e7eb;background:white;color:#374151;cursor:pointer;font-family:inherit; }
    .btn-toolbar:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }

    .compare-columns { display:grid;grid-template-columns:1fr 1fr;gap:20px; }
    .compare-col { min-width:0; }
    .compare-col-header { background:white;border-radius:var(--radius) var(--radius) 0 0;border:1px solid var(--card-border);border-bottom:none;padding:14px 20px; }
    .compare-col-sk { font-size:14px;font-weight:700;color:#111827; }
    .compare-col-tanggal { font-size:12px;color:#6b7280;margin-top:2px; }
    .compare-col-tree { border-radius:0 0 var(--radius) var(--radius); }

    .compare-popover { position:fixed;z-index:1200;background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:0 12px 40px rgba(0,0,0,.15);padding:14px 16px;width:280px;font-size:12.5px;color:#374151; }
    .compare-popover-text { line-height:1.5;margin-bottom:4px; }
    .compare-popover-text strong { color:#111827; }
    .compare-popover-ket { font-size:11px;color:#9ca3af;font-style:italic;margin-bottom:10px; }
    .compare-popover-link { display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#15803d;background:none;border:none;cursor:pointer;padding:0;font-family:inherit; }
    .compare-popover-link:hover { text-decoration:underline; }
    .compare-popover-close { position:absolute;top:8px;right:8px;border:none;background:none;color:#9ca3af;cursor:pointer;width:20px;height:20px;display:flex;align-items:center;justify-content:center; }

    /* ===== Filter fokus unit — search box DIDUPLIKASI dari tree.blade.php (pola in-page
       live-filter yg sudah ada, bukan pola search.blade.php yg full-reload — lihat catatan
       di respons task ini) ===== */
    .compare-search { position:relative;flex-shrink:0; }
    .search-box { display:flex;align-items:center;gap:8px;background:white;border:1.5px solid #e5e7eb;border-radius:8px;padding:7px 12px;min-width:220px; }
    .search-box:focus-within { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .search-box svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-box input { border:none;outline:none;font-size:12.5px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-box input::placeholder { color:#9ca3af; }
    .compare-search-dropdown { position:absolute;top:calc(100% + 4px);left:0;right:0;background:white;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 12px 32px rgba(0,0,0,.12);max-height:280px;overflow-y:auto;z-index:50; }
    .compare-search-item { display:flex;align-items:baseline;gap:8px;width:100%;text-align:left;padding:9px 14px;border:none;background:none;font-size:12.5px;color:#374151;cursor:pointer;font-family:inherit;border-bottom:1px solid #f3f4f6; }
    .compare-search-item:last-child { border-bottom:none; }
    .compare-search-item:hover { background:#f0fdf4;color:#15803d; }
    .compare-search-item-level { font-size:9.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;flex-shrink:0; }

    .compare-focus-banner { display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:10px 16px;font-size:12.5px;font-weight:600;margin-bottom:16px; }
    .compare-focus-reset { margin-left:auto;background:white;border:1px solid #bbf7d0;color:#15803d;border-radius:7px;padding:5px 12px;font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit; }
    .compare-focus-reset:hover { background:#dcfce7; }
    .compare-focus-missing { background:white;border-radius:0 0 var(--radius) var(--radius);border:1px solid var(--card-border);padding:40px 20px;text-align:center;color:#9ca3af;font-size:13px; }
    .compare-focus-missing strong { color:#374151; }

    @media (max-width:1000px) {
        .compare-columns { grid-template-columns:1fr; }
    }
    @media (max-width:640px) {
        .compare-visual-header { flex-direction:column;align-items:stretch; }
        .search-box { min-width:0; }
    }
</style>
@endpush

@php
    // Palet REUSE dari token badge yg sudah ada di project (search.blade.php, karyawan/
    // index.blade.php, usulan_promosi/talent_pool, dst — lihat riset sebelum revisi ini):
    // fill = latar muda punya pasangan text = versi lebih gelap/saturasi dari hue yg sama
    // (pola badge-aktif/.badge-draft/.badge-final yg sudah dipakai berulang), BUKAN palet baru.
    $colorHex = [
        'rename'       => ['fill' => '#f5f3ff', 'border' => '#7c3aed', 'text' => '#6d28d9'], // ungu — dipertahankan
        'pindah_induk' => ['fill' => '#dbeafe', 'border' => '#1d4ed8', 'text' => '#1d4ed8'], // biru — persis pasangan badge-longlist
        'ganti_level'  => ['fill' => '#fffbeb', 'border' => '#b45309', 'text' => '#92400e'], // amber — persis pasangan badge-draft
        'pecah'        => ['fill' => '#ecfeff', 'border' => '#0891b2', 'text' => '#0e7490'], // teal — dipertahankan
        'gabung'       => ['fill' => '#fff5f4', 'border' => '#f97066', 'text' => '#c2410c'], // coral — dipertahankan
        'bubar'        => ['fill' => '#fef2f2', 'border' => '#dc2626', 'text' => '#dc2626'], // merah — persis pasangan status-bubar
        'baru'         => ['fill' => '#f0fdf4', 'border' => '#16a34a', 'text' => '#15803d'], // hijau — persis pasangan badge-aktif
    ];
    // 'category' = null utk "Tidak berubah" (bukan kategori sungguhan yg bisa di-match ke
    // unit manapun — unit tanpa perubahan justru TIDAK PERNAH masuk statusByUnitId sama
    // sekali, jadi item ini murni display/tidak jadi checkbox fungsional).
    $legendItems = [
        ['label' => 'Tidak berubah', 'category' => null, 'fill' => '#ffffff', 'border' => '#e5e7eb'],
        ['label' => 'Rename', 'category' => 'rename', 'fill' => $colorHex['rename']['fill'], 'border' => $colorHex['rename']['border']],
        ['label' => 'Pindah Induk', 'category' => 'pindah_induk', 'fill' => $colorHex['pindah_induk']['fill'], 'border' => $colorHex['pindah_induk']['border']],
        ['label' => 'Ganti Level', 'category' => 'ganti_level', 'fill' => $colorHex['ganti_level']['fill'], 'border' => $colorHex['ganti_level']['border']],
        ['label' => 'Pecah', 'category' => 'pecah', 'fill' => $colorHex['pecah']['fill'], 'border' => $colorHex['pecah']['border']],
        ['label' => 'Gabung', 'category' => 'gabung', 'fill' => $colorHex['gabung']['fill'], 'border' => $colorHex['gabung']['border']],
        ['label' => 'Bubar', 'category' => 'bubar', 'fill' => $colorHex['bubar']['fill'], 'border' => $colorHex['bubar']['border']],
        ['label' => 'Baru', 'category' => 'baru', 'fill' => $colorHex['baru']['fill'], 'border' => $colorHex['baru']['border']],
    ];
    $levelOptions = ['direktorat' => 'Direktorat', 'kompartemen' => 'Kompartemen', 'departemen' => 'Departemen',
        'bagian' => 'Bagian', 'seksi' => 'Seksi', 'foreman' => 'Foreman', 'fungsional' => 'Fungsional'];
    $allTreeIds = $treeLama['byParent']->flatten(1)->pluck('unit_organisasi_id')
        ->merge($treeBaru['byParent']->flatten(1)->pluck('unit_organisasi_id'))
        ->unique()->values();

    // Escape teks utk dipakai sbg literal string di CSS content: (dipakai narasi ringkas
    // langsung di kotak node lewat ::after — lihat blok <style> di bawah).
    $escCss = function (string $s): string {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('"', '\\"', $s);
        return str_replace(["\r", "\n"], ' ', $s);
    };
    // Max dinaikkan dari 66 -> 78 (BUKAN 88, lihat catatan) krn narasi ini sekarang
    // sumbernya LeveledTransitionNarrator (nama unit dapat prefix level, mis.
    // "Direktorat "/"Kompartemen ", ~11-12 karakter/nama) — budget lama jadi kepotong
    // lebih awal drpd sebelum prefix ada. TAPI: panjang di sini dihitung SEBELUM Blade
    // auto-escape ('&' -> '&amp;', +4 karakter per kemunculan) yg terjadi lewat {{ }} di
    // <style> block — diverifikasi empiris thd 462 baris narasi asli, ketemu kasus
    // rename dgn 2x '&' dlm 88 karakter pertama jadi 96 karakter TERRENDER, mepet ke
    // batas 3 baris. 78 dipilih supaya kasus 2x'&' terburuk (78+8=86) masih ada slack
    // yg cukup di bawah 88 karakter/3-baris.
    $truncatePlain = function (string $s, int $max = 78): string {
        return mb_strlen($s) > $max ? mb_substr($s, 0, $max - 1) . '…' : $s;
    };
@endphp

@section('content')
<a href="{{ route('organisasi.struktur.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Riwayat Struktur Organisasi
</a>

<div class="page-header">
    <div class="page-title">
        <a href="{{ route('organisasi.struktur.show', $lama) }}" style="color:inherit;text-decoration:none;">{{ $lama->nomor_sk }}</a>
        <span class="arrow">→</span>
        <a href="{{ route('organisasi.struktur.show', $baru) }}" style="color:inherit;text-decoration:none;">{{ $baru->nomor_sk }}</a>
    </div>
    <div class="page-sub">
        {{ $lama->tanggal_mulai_berlaku->translatedFormat('d F Y') }} dibandingkan dengan {{ $baru->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
    </div>
</div>

<div x-data="compareVisual()">
    <div class="view-tabs">
        <button type="button" class="view-tab" :class="{ 'active': tab === 'visual' }" @click="tab = 'visual'">
            <svg viewBox="0 0 24 24"><rect x="8" y="2" width="8" height="4" rx="1"/><rect x="1" y="14" width="6" height="4" rx="1"/><rect x="9" y="14" width="6" height="4" rx="1"/><rect x="17" y="14" width="6" height="4" rx="1"/><path d="M4 14v-3h16v3"/><path d="M12 6v5"/></svg>
            Tampilan Visual
        </button>
        <button type="button" class="view-tab" :class="{ 'active': tab === 'tabel' }" @click="tab = 'tabel'">
            <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Tampilan Tabel
        </button>
    </div>

    {{-- ===== TAB BARU: Tampilan Visual (default) ===== --}}
    <div x-show="tab === 'visual'" x-cloak>
        <style id="compare-highlight-style"></style>

        <div class="compare-visual-header">
            <div class="compare-legend">
                @foreach($legendItems as $item)
                @if($item['category'])
                <label class="compare-legend-item compare-legend-clickable">
                    <input type="checkbox" value="{{ $item['category'] }}" x-model="categoryFilter" @change="applyAllFilters()">
                    <span class="compare-legend-swatch" style="background:{{ $item['fill'] }};border-color:{{ $item['border'] }};"></span>
                    {{ $item['label'] }}
                </label>
                @else
                <div class="compare-legend-item">
                    <span class="compare-legend-swatch" style="background:{{ $item['fill'] }};border-color:{{ $item['border'] }};"></span>
                    {{ $item['label'] }}
                </div>
                @endif
                @endforeach
            </div>

            <select class="compare-level-select" x-model="levelFilter" @change="applyAllFilters()">
                <option value="">Semua Level</option>
                @foreach($levelOptions as $key => $label)
                <option value="{{ $key }}">Sampai {{ $label }}</option>
                @endforeach
            </select>

            <div class="compare-search" @click.outside="searchResults = []">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" x-model="searchQuery" @input.debounce.200ms="runSearch()" @focus="runSearch()" placeholder="Fokus ke 1 unit...">
                </div>
                <div class="compare-search-dropdown" x-show="searchResults.length > 0" x-cloak>
                    <template x-for="r in searchResults" :key="r.id">
                        <button type="button" class="compare-search-item" @click="selectFocusUnit(r.id, r.label)">
                            <span class="compare-search-item-level" x-text="r.level"></span>
                            <span x-text="r.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            <button type="button" class="btn-toolbar" @click="$store.tree.toggleShowAll()">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                <span x-text="$store.tree.showingAll ? 'Sembunyikan yang Tidak Berubah' : 'Tampilkan Semua'"></span>
            </button>
        </div>

        <template x-if="focusUnitId || categoryFilter.length > 0 || levelFilter">
            <div class="compare-focus-banner">
                🔎 Filter aktif:
                <template x-if="focusUnitId"><span>fokus <strong x-text="focusUnitName"></strong></span></template>
                <template x-if="categoryFilter.length > 0"><span x-text="categoryFilter.length + ' jenis transisi dicentang'"></span></template>
                <template x-if="levelFilter"><span x-text="'sampai level ' + levelFilter"></span></template>
                <button type="button" class="compare-focus-reset" @click="resetAllFilters()">Reset Semua Filter</button>
            </div>
        </template>

        @if(!empty($visual['statusByUnitId']))
        <style>
            @foreach($visual['statusByUnitId'] as $unitId => $categories)
                @php
                    // >1 kategori sekaligus (mis. rename + pindah_induk bersamaan) -> latar
                    // gradient terbagi rata per kategori (kiri = prioritas tertinggi), border
                    // & warna teks ikut kategori PALING prioritas. Narasi (baris terpisah, lihat
                    // ::after di bawah) tetap menggabungkan SEMUA kategori, bukan cuma yg tampil
                    // di border/teks.
                    $fills = array_map(fn ($c) => $colorHex[$c]['fill'], $categories);
                    $primary = $colorHex[$categories[0]];
                    if (count($fills) === 1) {
                        $bg = $fills[0];
                    } else {
                        $stops = [];
                        $step = 100 / count($fills);
                        foreach ($fills as $i => $fill) {
                            $stops[] = "{$fill} " . ($i * $step) . '%';
                            $stops[] = "{$fill} " . (($i + 1) * $step) . '%';
                        }
                        $bg = 'linear-gradient(135deg, ' . implode(', ', $stops) . ')';
                    }
                    $narasiPlain = $visual['narrativeByUnitId'][$unitId]['plain'] ?? '';
                @endphp
            #org-node-{{ $unitId }} { border-color: {{ $primary['border'] }}; background: {{ $bg }}; cursor: pointer; }
            #org-node-{{ $unitId }} .org-box-name,
            #org-node-{{ $unitId }} .org-stat-val { color: {{ $primary['text'] }}; }
                @if(in_array('bubar', $categories, true))
            #org-node-{{ $unitId }} .org-box-name { text-decoration: line-through; opacity: .7; }
                @endif
                @if($narasiPlain !== '')
            #org-node-{{ $unitId }} .org-box-stats::after { content: "{{ $escCss($truncatePlain($narasiPlain)) }}"; display: block; margin-top: 6px; padding-top: 6px; border-top: 1px dashed {{ $primary['border'] }}; font-size: 10px; line-height: 1.35; color: {{ $primary['text'] }}; }
                @endif
            @endforeach
        </style>
        @endif

        <div class="compare-columns">
            <div class="compare-col">
                <div class="compare-col-header">
                    <div class="compare-col-sk">SK {{ $lama->nomor_sk }}</div>
                    <div class="compare-col-tanggal">{{ $lama->tanggal_mulai_berlaku->translatedFormat('d F Y') }} &middot; versi lebih lama</div>
                </div>
                <div class="compare-focus-missing" x-show="focusMissing.lama" x-cloak>
                    <strong x-text="focusUnitName"></strong> belum ada di versi ini.
                </div>
                <div id="compare-tree-lama" class="tree-scroll-wrap compare-col-tree" x-show="!focusMissing.lama" @click="handleTreeClick($event)">
                    <div class="tree-scroll-inner">
                        @if($treeLama['roots']->isEmpty())
                            <div style="text-align:center;color:#9ca3af;padding:40px;font-size:13px;">Belum ada unit di versi ini.</div>
                        @else
                            @foreach($treeLama['roots'] as $root)
                                <x-org-tree-node :node="$root" :by-parent="$treeLama['byParent']" :totals="$treeLama['totals']" :box-height="240" :show-level-prefix="true" />
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="compare-col">
                <div class="compare-col-header">
                    <div class="compare-col-sk">SK {{ $baru->nomor_sk }}</div>
                    <div class="compare-col-tanggal">{{ $baru->tanggal_mulai_berlaku->translatedFormat('d F Y') }} &middot; versi lebih baru</div>
                </div>
                <div class="compare-focus-missing" x-show="focusMissing.baru" x-cloak>
                    <strong x-text="focusUnitName"></strong> sudah tidak ada di versi ini.
                </div>
                <div id="compare-tree-baru" class="tree-scroll-wrap compare-col-tree" x-show="!focusMissing.baru" @click="handleTreeClick($event)">
                    <div class="tree-scroll-inner">
                        @if($treeBaru['roots']->isEmpty())
                            <div style="text-align:center;color:#9ca3af;padding:40px;font-size:13px;">Belum ada unit di versi ini.</div>
                        @else
                            @foreach($treeBaru['roots'] as $root)
                                <x-org-tree-node :node="$root" :by-parent="$treeBaru['byParent']" :totals="$treeBaru['totals']" :box-height="240" :show-level-prefix="true" />
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="compare-popover" x-show="popover.open" x-cloak :style="`left:${popover.x}px; top:${popover.y}px;`" @click.outside="popover.open = false" @keydown.escape.window="popover.open = false">
            <button type="button" class="compare-popover-close" @click="popover.open = false" title="Tutup">
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div class="compare-popover-text" x-html="popover.html"></div>
            <template x-if="popover.keterangan">
                <div class="compare-popover-ket" x-text="'Catatan: ' + popover.keterangan"></div>
            </template>
            <button type="button" class="compare-popover-link" @click="$store.riwayatOverlay.openPanel(popover.unitId, popover.namaUnit); popover.open = false;">
                Lihat riwayat lengkap →
            </button>
        </div>
    </div>

    {{-- ===== TAB LAMA: Tampilan Tabel (SUDAH ADA, TIDAK diubah — cuma dibungkus toggle) ===== --}}
    <div x-show="tab === 'tabel'" x-cloak>

@if($hops->count() > 1)
<div class="hop-trail">
    Mencakup {{ $hops->count() }} versi dalam rentang ini:
    @foreach($hops as $hop)
        <span class="hop-chip">{{ $hop->nomor_sk }}</span>
        @if(!$loop->last) → @endif
    @endforeach
</div>
@endif

<div class="ringkasan-grid">
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['baru']) }}</div>
        <div class="ringkasan-label">Unit Baru</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['rename']) }}</div>
        <div class="ringkasan-label">Rename</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['pindah_induk']) }}</div>
        <div class="ringkasan-label">Pindah Induk</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['ganti_level']) }}</div>
        <div class="ringkasan-label">Ganti Level</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['pecah']) }}</div>
        <div class="ringkasan-label">Pecah</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['gabung']) }}</div>
        <div class="ringkasan-label">Gabung</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['bubar']) }}</div>
        <div class="ringkasan-label">Bubar</div>
    </div>
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ count($hasil['formasi_berubah']) }}</div>
        <div class="ringkasan-label">Formasi Berubah</div>
    </div>
    <div class="ringkasan-item anomali">
        <div class="ringkasan-num">{{ count($hasil['anomali']) }}</div>
        <div class="ringkasan-label">Anomali Data</div>
    </div>
</div>

<div class="unchanged-note">
    ✓ {{ $hasil['unchanged_count'] }} unit tidak mengalami perubahan berarti antara kedua versi ini.
</div>

@if(count($hasil['baru']))
<div class="section-card section-baru">
    <div class="section-title">🆕 Unit Baru <span class="section-badge">{{ count($hasil['baru']) }}</span></div>
    <div class="diff-list-multi">
        @foreach($hasil['baru'] as $u)
        <span class="diff-pill">{{ formatUnitLabel($u['nama'], $u['level']) }}</span>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['rename']))
<div class="section-card">
    <div class="section-title">✏️ Rename <span class="section-badge">{{ count($hasil['rename']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['rename'] as $r)
        <div class="diff-row">
            <span class="diff-old">{{ formatUnitLabel($r['dari'], $r['dari_level']) }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ formatUnitLabel($r['ke'], $r['ke_level']) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['pindah_induk']))
<div class="section-card">
    <div class="section-title">🔀 Pindah Induk <span class="section-badge">{{ count($hasil['pindah_induk']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['pindah_induk'] as $p)
        <div class="diff-row">
            <span class="diff-name">{{ formatUnitLabel($p['nama'], $p['nama_level']) }}</span>
            <span class="diff-arrow">·</span>
            <span class="diff-old">{{ formatUnitLabel($p['dari'], $p['dari_level']) }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ formatUnitLabel($p['ke'], $p['ke_level']) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['ganti_level']))
<div class="section-card">
    <div class="section-title">🔡 Ganti Level <span class="section-badge">{{ count($hasil['ganti_level']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['ganti_level'] as $g)
        <div class="diff-row">
            <span class="diff-name">{{ formatUnitLabel($g['nama'], $g['nama_level']) }}</span>
            <span class="diff-arrow">·</span>
            <span class="diff-old">{{ ucfirst($g['dari']) }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ ucfirst($g['ke']) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['pecah']))
<div class="section-card">
    <div class="section-title">✂️ Pecah <span class="section-badge">{{ count($hasil['pecah']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['pecah'] as $p)
        <div class="diff-row">
            <span class="diff-old">{{ formatUnitLabel($p['dari'], $p['dari_level']) }}</span>
            <span class="diff-arrow">→</span>
            <div class="diff-list-multi">
                @foreach($p['ke'] as $i => $nama)
                <span class="diff-pill">{{ formatUnitLabel($nama, $p['ke_levels'][$i] ?? null) }}</span>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['gabung']))
<div class="section-card">
    <div class="section-title">🔗 Gabung <span class="section-badge">{{ count($hasil['gabung']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['gabung'] as $g)
        <div class="diff-row">
            <div class="diff-list-multi">
                @foreach($g['dari'] as $i => $nama)
                <span class="diff-pill">{{ formatUnitLabel($nama, $g['dari_levels'][$i] ?? null) }}</span>
                @endforeach
            </div>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ formatUnitLabel($g['ke'], $g['ke_level']) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['bubar']))
<div class="section-card section-bubar">
    <div class="section-title">🗑️ Bubar <span class="section-badge">{{ count($hasil['bubar']) }}</span></div>
    <div class="diff-list-multi">
        @foreach($hasil['bubar'] as $u)
        <span class="diff-pill">{{ formatUnitLabel($u['nama'], $u['level']) }}</span>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['formasi_berubah']))
<div class="section-card">
    <div class="section-title">📊 Perubahan Formasi <span class="section-badge">{{ count($hasil['formasi_berubah']) }}</span></div>
    <div class="diff-list">
        @foreach($hasil['formasi_berubah'] as $f)
        <div class="diff-row">
            <span class="diff-name">{{ formatUnitLabel($f['nama'], $f['level']) }}</span>
            <span class="diff-arrow">·</span>
            <span class="diff-old">{{ $f['dari'] }}</span>
            <span class="diff-arrow">→</span>
            <span class="diff-new">{{ $f['ke'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['reorganisasi']))
<div class="section-card">
    <div class="section-title">🧩 Reorganisasi Kompleks <span class="section-badge">{{ count($hasil['reorganisasi']) }}</span></div>
    <div class="section-sub" style="font-size:11.5px;color:#9ca3af;margin-bottom:12px;">
        Sekelompok unit lama & baru saling terkait lewat beberapa pecah/gabung sekaligus dalam rentang ini — sistem tidak bisa memasangkan satu-satu secara otomatis, jadi ditampilkan sebagai satu kelompok.
    </div>
    <div class="diff-list">
        @foreach($hasil['reorganisasi'] as $r)
        <div class="diff-row">
            <div class="diff-list-multi">
                @foreach($r['dari'] as $i => $nama)<span class="diff-pill">{{ formatUnitLabel($nama, $r['dari_levels'][$i] ?? null) }}</span>@endforeach
            </div>
            <span class="diff-arrow">→</span>
            <div class="diff-list-multi">
                @foreach($r['ke'] as $i => $nama)<span class="diff-pill">{{ formatUnitLabel($nama, $r['ke_levels'][$i] ?? null) }}</span>@endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(count($hasil['anomali']))
<div class="section-card section-anomali">
    <div class="section-title">⚠️ Anomali / Belum Terdeklarasi <span class="section-badge">{{ count($hasil['anomali']) }}</span></div>
    <div class="section-sub" style="font-size:11.5px;color:#9ca3af;margin-bottom:12px;">
        Unit ini berubah tanpa pernah tercatat sebagai transisi resmi (rename/pindah induk/dst) di sepanjang rentang versi ini — kemungkinan luput didokumentasikan tim OD saat input. Perlu dicek ulang.
    </div>
    <div class="diff-list">
        @foreach($hasil['anomali'] as $a)
        <div class="anomali-row">
            <div class="anomali-name">{{ formatUnitLabel($a['nama'], $a['level']) }}</div>
            <ul class="anomali-detail">
                @foreach($a['detail'] as $d)
                <li>{{ $d }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</div>
@endif

    </div>
</div>

@include('organisasi.struktur.partials.riwayat-overlay-shell')

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('tree', {
            expanded: Object.fromEntries(@json($visual['defaultExpandedIds']).map(id => [id, true])),
            defaultExpandedIds: @json($visual['defaultExpandedIds']),
            allIds: @json($allTreeIds),
            showingAll: false,
            // Diisi oleh compareVisual() saat filter fokus-unit aktif (union id yg tampil
            // di kedua kolom) — null berarti tidak ada filter, "Tampilkan Semua" berlaku ke
            // SEMUA unit spt biasa. Saat filter aktif, "Tampilkan Semua" cuma pengaruh ke
            // node DALAM scope filter itu (keputusan: filter menyaring struktur duluan,
            // baru toggle ini expand/collapse di dalam hasil saringan itu).
            visibleIds: null,

            isExpanded(id) {
                return !!this.expanded[id];
            },
            toggle(id) {
                this.expanded[id] = !this.expanded[id];
            },
            matches(id) {
                return false; // tidak ada fitur cari-highlight di halaman Compare, tapi komponen tree-node selalu memanggil ini
            },
            toggleShowAll() {
                this.showingAll = !this.showingAll;
                const scope = this.visibleIds || this.allIds;
                if (this.showingAll) {
                    scope.forEach(id => { this.expanded[id] = true; });
                } else {
                    this.expanded = Object.fromEntries(this.defaultExpandedIds.map(id => [id, true]));
                    scope.forEach(id => { if (this.expanded[id] === undefined) this.expanded[id] = false; });
                }
            },
        });

        Alpine.data('compareVisual', () => ({
            tab: 'visual',
            popover: { open: false, x: 0, y: 0, html: '', keterangan: null, unitId: null, namaUnit: '' },
            narrativeMap: @json($visual['narrativeByUnitId']),
            namesMap: @json($visual['namesByUnitId']),

            // ===== Data statis (di-embed sekali saat load, 0 query tambahan utk SEMUA filter di bawah) =====
            parentMapLama: @json($visual['parentMapLama']),
            parentMapBaru: @json($visual['parentMapBaru']),
            levelMapLama: @json($visual['levelMapLama']),
            levelMapBaru: @json($visual['levelMapBaru']),
            categoriesByUnitId: @json($visual['statusByUnitId']),
            childrenMapLama: {},
            childrenMapBaru: {},
            LEVEL_ORDER: ['direktorat', 'kompartemen', 'departemen', 'bagian', 'seksi', 'foreman', 'fungsional'],

            // ===== Filter search 1-unit =====
            searchUnits: @json($visual['searchUnits']),
            searchQuery: '',
            searchResults: [],
            focusUnitId: null,
            focusUnitName: '',
            focusMissing: { lama: false, baru: false },

            // ===== Filter level (depth cutoff) & jenis transisi (checkbox legend) =====
            levelFilter: '',
            categoryFilter: [],

            init() {
                this.childrenMapLama = this.buildChildrenMap(this.parentMapLama);
                this.childrenMapBaru = this.buildChildrenMap(this.parentMapBaru);
            },

            handleTreeClick(event) {
                if (event.target.closest('.org-toggle') || event.target.closest('.org-history-btn')) {
                    return;
                }
                const box = event.target.closest('.org-box');
                if (!box) {
                    return;
                }
                const unitId = box.id.replace('org-node-', '');
                const narasi = this.narrativeMap[unitId];
                if (!narasi) {
                    this.popover.open = false;
                    return;
                }

                const rect = box.getBoundingClientRect();
                this.popover = {
                    open: true,
                    x: Math.max(8, Math.min(rect.left, window.innerWidth - 300)),
                    y: rect.bottom + 8,
                    html: narasi.html,
                    keterangan: narasi.keterangan,
                    unitId: unitId,
                    namaUnit: this.namesMap[unitId] || '',
                };
            },

            buildChildrenMap(parentMap) {
                const map = {};
                Object.keys(parentMap).forEach(id => {
                    const p = parentMap[id];
                    if (p === null || p === undefined) {
                        return;
                    }
                    if (!map[p]) {
                        map[p] = [];
                    }
                    map[p].push(id);
                });
                return map;
            },

            runSearch() {
                const q = this.searchQuery.trim().toLowerCase();
                if (!q) {
                    this.searchResults = [];
                    return;
                }
                this.searchResults = this.searchUnits
                    .filter(u => u.aliases.some(a => a.toLowerCase().includes(q)))
                    .slice(0, 8);
            },

            /** Jalur tunggal ke atas (parent->parent->...->root, TANPA saudara) + SEMUA descendant ke bawah — dipakai filter search 1-unit. */
            computeScopeIds(unitId, parentMap, childrenMap) {
                const visible = new Set();
                const key = String(unitId);
                if (!Object.prototype.hasOwnProperty.call(parentMap, key)) {
                    return visible; // unit ini tidak ada di versi ini
                }
                visible.add(key);

                let current = parentMap[key];
                while (current !== null && current !== undefined) {
                    visible.add(String(current));
                    current = parentMap[current];
                }

                const queue = [key];
                while (queue.length) {
                    const cur = queue.shift();
                    (childrenMap[cur] || []).forEach(child => {
                        if (!visible.has(String(child))) {
                            visible.add(String(child));
                            queue.push(String(child));
                        }
                    });
                }
                return visible;
            },

            /** Sama spt computeScopeIds tapi TANPA descendant — cuma unit yg match + jalur ke atas. Dipakai filter jenis transisi (bisa banyak unit match sekaligus). */
            computeAncestorOnlyScope(unitIds, parentMap) {
                const visible = new Set();
                unitIds.forEach(id => {
                    const key = String(id);
                    if (!Object.prototype.hasOwnProperty.call(parentMap, key)) {
                        return;
                    }
                    visible.add(key);
                    let current = parentMap[key];
                    while (current !== null && current !== undefined) {
                        visible.add(String(current));
                        current = parentMap[current];
                    }
                });
                return visible;
            },

            /** Cari elemen .org-node (box + sub-pohonnya) SCR TERSKOP ke 1 kolom — PENTING: unit_organisasi_id yg SAMA muncul di KEDUA kolom (id="org-node-X" dobel di seluruh dokumen), jadi document.getElementById() TIDAK BOLEH dipakai langsung (selalu balik ke kemunculan PERTAMA di dokumen = kolom lama, ini akar bug "cuma kolom baru/lama yg ke-filter" yg dilaporkan). querySelector yg di-scope ke container kolom yg benar menghindari itu. */
            getNodeWrap(containerId, unitId) {
                const container = document.getElementById(containerId);
                if (!container) {
                    return null;
                }
                const box = container.querySelector('#org-node-' + unitId);
                return box ? box.closest('.org-node') : null;
            },

            applyTreeFilter(containerId, parentMap, visibleIds) {
                Object.keys(parentMap).forEach(id => {
                    const wrap = this.getNodeWrap(containerId, id);
                    if (wrap) {
                        wrap.style.display = visibleIds.has(String(id)) ? '' : 'none';
                    }
                });
            },

            resetTreeFilter(containerId, parentMap) {
                Object.keys(parentMap).forEach(id => {
                    const wrap = this.getNodeWrap(containerId, id);
                    if (wrap) {
                        wrap.style.display = '';
                    }
                });
            },

            selectFocusUnit(id, label) {
                this.searchQuery = '';
                this.searchResults = [];
                this.focusUnitId = id;
                this.focusUnitName = label;
                this.applyAllFilters();
            },

            /**
             * Satu titik hitung ulang SEMUA filter (search 1-unit, jenis transisi, level) utk
             * KEDUA kolom sekaligus — dipanggil tiap kali salah satu filter berubah. Interaksi:
             * search-unit & jenis-transisi SAMA-SAMA membatasi node mana yg TAMPIL (irisan/
             * intersection kalau keduanya aktif bersamaan); level cutoff lapisan TERPISAH yg
             * cuma atur status expand/collapse (jadi otomatis "membatasi depth DI DALAM scope"
             * apapun yg sudah disaring 2 filter di atas, sesuai keputusan poin 2).
             */
            applyAllFilters() {
                const store = Alpine.store('tree');
                const unionAll = new Set();
                const anyScopeFilter = this.focusUnitId !== null || this.categoryFilter.length > 0;

                ['lama', 'baru'].forEach(side => {
                    const parentMap = side === 'lama' ? this.parentMapLama : this.parentMapBaru;
                    const childrenMap = side === 'lama' ? this.childrenMapLama : this.childrenMapBaru;
                    const levelMap = side === 'lama' ? this.levelMapLama : this.levelMapBaru;
                    const containerId = side === 'lama' ? 'compare-tree-lama' : 'compare-tree-baru';

                    let scope = null;

                    if (this.focusUnitId !== null) {
                        const key = String(this.focusUnitId);
                        const exists = Object.prototype.hasOwnProperty.call(parentMap, key);
                        this.focusMissing[side] = !exists;
                        scope = exists ? this.computeScopeIds(this.focusUnitId, parentMap, childrenMap) : new Set();
                    }

                    if (this.categoryFilter.length > 0) {
                        const matchedIds = Object.keys(this.categoriesByUnitId).filter(uid =>
                            this.categoriesByUnitId[uid].some(c => this.categoryFilter.includes(c))
                        );
                        const catScope = this.computeAncestorOnlyScope(matchedIds, parentMap);
                        scope = scope === null ? catScope : new Set([...scope].filter(x => catScope.has(x)));
                    }

                    if (scope === null) {
                        this.resetTreeFilter(containerId, parentMap);
                    } else {
                        this.applyTreeFilter(containerId, parentMap, scope);
                        scope.forEach(uid => { store.expanded[uid] = true; unionAll.add(uid); });
                    }

                    if (this.levelFilter) {
                        const cutoffRank = this.LEVEL_ORDER.indexOf(this.levelFilter);
                        Object.keys(parentMap).forEach(id => {
                            const rank = this.LEVEL_ORDER.indexOf(levelMap[id]);
                            if (rank === -1) {
                                return;
                            }
                            store.expanded[id] = rank < cutoffRank;
                        });
                    }
                });

                store.visibleIds = anyScopeFilter ? Array.from(unionAll) : null;
                store.showingAll = false;

                if (!anyScopeFilter && !this.levelFilter) {
                    store.expanded = Object.fromEntries(store.defaultExpandedIds.map(uid => [uid, true]));
                }

                this.updateHighlightPath();
            },

            resetAllFilters() {
                this.focusUnitId = null;
                this.focusUnitName = '';
                this.focusMissing = { lama: false, baru: false };
                this.categoryFilter = [];
                this.levelFilter = '';
                this.applyAllFilters();
            },

            /**
             * Highlight garis penghubung (bukan kotak node — sesuai keputusan yg sudah
             * dikonfirmasi) sepanjang jalur ancestor dari root sampai unit fokus, DI KEDUA
             * kolom. Garis penghubung di komponen ini murni CSS ::before/::after (bukan
             * elemen DOM terpisah), jadi di-highlight lewat <style> dinamis yg nge-target
             * selector CSS scr presisi (:has() utk cari .org-child-branch yg membungkus node
             * anak tertentu) — tetap TANPA menyentuh komponennya sendiri.
             */
            updateHighlightPath() {
                const styleEl = document.getElementById('compare-highlight-style');
                if (!styleEl) {
                    return;
                }
                if (this.focusUnitId === null) {
                    styleEl.textContent = '';
                    return;
                }

                const HILITE = '#f59e0b';
                const rules = [];
                ['lama', 'baru'].forEach(side => {
                    const parentMap = side === 'lama' ? this.parentMapLama : this.parentMapBaru;
                    const containerId = side === 'lama' ? 'compare-tree-lama' : 'compare-tree-baru';
                    const key = String(this.focusUnitId);
                    if (!Object.prototype.hasOwnProperty.call(parentMap, key)) {
                        return;
                    }

                    let child = key;
                    let parent = parentMap[key];
                    while (parent !== null && parent !== undefined) {
                        rules.push(`#${containerId} #org-node-${parent} + .org-children::before { border-left-color: ${HILITE}; border-left-width: 3px; }`);
                        rules.push(`#${containerId} .org-child-branch:has(#org-node-${child})::before, #${containerId} .org-child-branch:has(#org-node-${child})::after { border-color: ${HILITE}; }`);
                        child = parent;
                        parent = parentMap[parent];
                    }
                });
                styleEl.textContent = rules.join('\n');
            },
        }));
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
