@extends('layouts.app')
@section('title', 'History Assessment')
@section('breadcrumb', 'History Assessment')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

    .stats-grid { display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:20px; }
    .stat-card { background:white;border-radius:10px;border:1px solid #e5e7eb;padding:14px;text-align:center; }
    .stat-num { font-size:24px;font-weight:800;color:#111827; }
    .stat-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:3px; }
    .stat-card.ready .stat-num { color:#15803d; }
    .stat-card.rwd .stat-num { color:#d97706; }
    .stat-card.nr .stat-num { color:#dc2626; }
    .stat-card.qualified .stat-num { color:#1d4ed8; }
    .stat-card.notqualified .stat-num { color:#dc2626; }

    .tab-wrap { display:flex;gap:4px;background:#f3f4f6;border-radius:10px;padding:4px;margin-bottom:16px; }
    .tab-btn { flex:1;padding:7px 12px;border-radius:7px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:inherit;color:#6b7280;background:transparent;transition:all 0.15s;text-align:center; }
    .tab-btn.active { background:white;color:#15803d;box-shadow:0 1px 4px rgba(0,0,0,0.08); }

    .search-mini { display:flex;align-items:center;gap:8px;background:white;border:1px solid #e5e7eb;border-radius:8px;padding:7px 12px;width:220px;transition:border-color 0.15s; }
    .search-mini:focus-within { border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,0.1); }
    .search-mini svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-mini input { border:none;outline:none;font-size:12px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-mini input::placeholder { color:#9ca3af; }
    .clear-btn { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:15px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .clear-btn.visible { display:block; }
    .search-spinner { display:none;width:12px;height:12px;border:2px solid #e5e7eb;border-top-color:#15803d;border-radius:50%;animation:spin 0.6s linear infinite;flex-shrink:0; }
    .search-spinner.show { display:block; }
    @keyframes spin { to{transform:rotate(360deg)} }

    .filter-row { display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center; }
    .filter-select { padding:7px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:12px;font-family:inherit;color:#374151;background:white;outline:none;cursor:pointer; }
    .btn-reset { display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:12px;font-weight:500;font-family:inherit;cursor:pointer;text-decoration:none;white-space:nowrap; }
    .btn-reset:hover { background:#f5f5f0; }

    .table-card { background:white;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:900px; }
    thead th { padding:10px 14px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:11px 14px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle;white-space:nowrap; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:9px; }
    .karyawan-avatar { width:32px;height:32px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:12px; }
    .karyawan-nik { font-size:11px;color:#9ca3af; }

    .badge-final { display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700; }
    .final-ready { background:#dcfce7;color:#15803d; }
    .final-rwd { background:#fef3c7;color:#d97706; }
    .final-nr { background:#fee2e2;color:#dc2626; }
    .final-none { background:#f3f4f6;color:#6b7280; }
    .final-qualified { background:#dbeafe;color:#1d4ed8; }
    .final-notqualified { background:#fee2e2;color:#dc2626; }

    .idp-badge { display:inline-flex;padding:2px 7px;border-radius:5px;font-size:11px;font-weight:600; }
    .idp-expired { background:#fee2e2;color:#dc2626; }
    .idp-aktif { background:#dcfce7;color:#15803d; }

    /* FIX: idp tanggal warna via class statis */
    .idp-tgl-expired { color:#ef4444;font-weight:700; }
    .idp-tgl-aktif   { color:#374151;font-weight:400; }

    /* FIX: under badge warna via class statis */
    .under-badge { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;font-size:16px;font-weight:800; }
    .under-ok   { background:#f0fdf4;color:#15803d;border:1.5px solid #86efac; }
    .under-fail { background:#fef2f2;color:#dc2626;border:1.5px solid #fca5a5; }

    .rekom-bar { display:flex;align-items:center;gap:5px;min-width:90px; }
    .rekom-track { flex:1;height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden; }
    .rekom-fill { height:100%;border-radius:20px; }
    .rekom-pct { font-size:11px;font-weight:700;color:#374151;min-width:28px; }

    .btn-del { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-del:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del svg { width:12px;height:12px;stroke:#ef4444;fill:none;stroke-width:2; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:11px 16px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px; }
    .pagination-wrap { display:flex;align-items:center;gap:3px; }
    .page-btn { width:27px;height:27px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:50px 20px;color:#9ca3af; }
    .empty-state svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:12px 14px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:260px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:20px;height:20px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:11px;height:11px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:16px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:24px;width:100%;max-width:380px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center; }
    .modal-icon-wrap { width:50px;height:50px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px; }
    .modal-icon-wrap svg { width:22px;height:22px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:20px; }
    .modal-actions { display:flex;gap:8px; }
    .modal-btn { flex:1;padding:10px;border-radius:9px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }

    mark { background:#fef08a;border-radius:2px;padding:0 1px;color:inherit;font-weight:600; }

    .side-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.35);z-index:2000;display:none;backdrop-filter:blur(2px); }
    .side-overlay.show { display:block; }
    .side-panel { position:fixed;right:0;top:0;bottom:0;width:420px;max-width:95vw;background:white;z-index:2001;box-shadow:-8px 0 40px rgba(0,0,0,0.14);display:flex;flex-direction:column;transform:translateX(100%);transition:transform 0.3s cubic-bezier(0.4,0,0.2,1); }
    .side-panel.show { transform:translateX(0); }
    .side-panel-header { display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #f3f4f6;flex-shrink:0; }
    .side-panel-title { font-size:15px;font-weight:700;color:#111827; }
    .side-close { width:30px;height:30px;border-radius:50%;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6b7280;font-size:18px;transition:all 0.12s; }
    .side-close:hover { background:#fef2f2;border-color:#fecaca;color:#ef4444; }
    .side-panel-body { flex:1;overflow-y:auto;padding:18px 20px; }

    .sp-karyawan { display:flex;align-items:center;gap:12px;padding:14px;background:#f9fafb;border-radius:10px;margin-bottom:16px; }
    .sp-avatar { width:40px;height:40px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0; }
    .sp-name { font-size:14px;font-weight:700;color:#111827; }
    .sp-sub { font-size:12px;color:#9ca3af;margin-top:2px; }

    .sp-kesimpulan { display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-radius:10px;margin-bottom:16px; }
    .sp-kesimpulan.qualified { background:#dcfce7;border:1px solid #86efac; }
    .sp-kesimpulan.notqualified { background:#fee2e2;border:1px solid #fca5a5; }
    .sp-kesimpulan-label { font-size:12px;font-weight:600;color:#6b7280; }
    .sp-kesimpulan-val { font-size:14px;font-weight:800; }

    .sp-section-title { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;margin-top:16px;display:flex;align-items:center;gap:6px; }
    .sp-section-title::after { content:'';flex:1;height:1px;background:#f3f4f6; }
    .sp-score-item { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid #f9fafb; }
    .sp-score-item:last-child { border-bottom:none; }
    .sp-score-label { font-size:12px;color:#374151;font-weight:500;flex:1;line-height:1.4; }
    .sp-score-val { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;flex-shrink:0; }
    .sp-score-1 { background:#fef2f2;color:#dc2626;border:1.5px solid #fca5a5; }
    .sp-score-2 { background:#fffbeb;color:#d97706;border:1.5px solid #fcd34d; }
    .sp-score-3 { background:#f0fdf4;color:#16a34a;border:1.5px solid #86efac; }
    .sp-score-4 { background:#eff6ff;color:#1d4ed8;border:1.5px solid #93c5fd; }
    .sp-score-na { background:#f3f4f6;color:#9ca3af;border:1.5px solid #e5e7eb; }

    .sp-summary { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px; }
    .sp-stat { background:#f9fafb;border-radius:8px;padding:10px;text-align:center; }
    .sp-stat-num { font-size:20px;font-weight:800;margin-bottom:2px; }
    .sp-stat-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase; }
    /* FIX: sp-stat warna via class statis */
    .sp-stat-ok   { color:#15803d; }
    .sp-stat-fail { color:#dc2626; }

    .btn-detail { width:28px;height:28px;border-radius:7px;border:1px solid #dbeafe;background:#eff6ff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-detail:hover { background:#dbeafe;border-color:#93c5fd; }
    .btn-detail svg { width:12px;height:12px;stroke:#2563eb;fill:none;stroke-width:2; }

    /* FIX: rekom-fill warna via class statis */
    .rekom-fill-green  { background:#15803d; }
    .rekom-fill-blue   { background:#3b82f6; }
    .rekom-fill-amber  { background:#f59e0b; }

    @media (max-width:900px) { .stats-grid { grid-template-columns:repeat(3,1fr); } }
    @media (max-width:480px) { .stats-grid { grid-template-columns:repeat(2,1fr); } }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus Assessment?</div>
        <div class="modal-desc" id="modalDesc">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">History Assessment</div>
        <div class="page-sub">Riwayat assessment seluruh karyawan</div>
    </div>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-num">{{ $stats['total'] + $stats['total_kompetensi'] }}</div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card ready">
        <div class="stat-num">{{ $stats['ready'] }}</div>
        <div class="stat-label">Ready</div>
    </div>
    <div class="stat-card rwd">
        <div class="stat-num">{{ $stats['rwd'] }}</div>
        <div class="stat-label">Ready w/ Dev</div>
    </div>
    <div class="stat-card nr">
        <div class="stat-num">{{ $stats['nr'] }}</div>
        <div class="stat-label">Not Ready</div>
    </div>
    <div class="stat-card qualified">
        <div class="stat-num">{{ $stats['qualified'] }}</div>
        <div class="stat-label">Qualified</div>
    </div>
    <div class="stat-card notqualified">
        <div class="stat-num">{{ $stats['not_qualified'] ?? 0 }}</div>
        <div class="stat-label">Not Qualified</div>
    </div>
</div>

{{-- TAB --}}
<div class="tab-wrap">
    {{-- FIX: onclick switchTab pakai data-tab --}}
    <button class="tab-btn active" id="tab-rekom" data-tab="rekom" onclick="switchTab(this.dataset.tab)">
        📋 Rekomendasi ({{ $stats['total'] }})
    </button>
    <button class="tab-btn" id="tab-komp" data-tab="komp" onclick="switchTab(this.dataset.tab)">
        ⭐ Kompetensi ({{ $stats['total_kompetensi'] }})
    </button>
</div>

{{-- ===== PANEL REKOMENDASI ===== --}}
<div id="panel-rekom">
    <div class="filter-row">
        <div class="search-mini" id="searchWrapRekom">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInputRekom" placeholder="Cari nama / NIK..." value="{{ request('search') }}" autocomplete="off">
            <div class="search-spinner" id="spinnerRekom"></div>
            <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearRekom" onclick="clearSearchRekom()">×</button>
        </div>
        <form method="GET" id="filterForm" style="display:contents">
            <input type="hidden" name="tab" value="rekom">
            <input type="hidden" name="search" id="hiddenSearch" value="{{ request('search') }}">
            <select name="rekomendasi" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Rekomendasi</option>
                <option value="ready" {{ request('rekomendasi')=='ready' ? 'selected' : '' }}>Ready</option>
                <option value="ready_with_development" {{ request('rekomendasi')=='ready_with_development' ? 'selected' : '' }}>Ready with Development</option>
                <option value="not_ready" {{ request('rekomendasi')=='not_ready' ? 'selected' : '' }}>Not Ready</option>
            </select>
            <select name="tahun" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                @foreach($tahuns as $t)
                    <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            @if(request()->hasAny(['search','rekomendasi','tahun']))
                <a href="{{ route('history_assessment_all.index') }}" class="btn-reset">× Reset</a>
            @endif
        </form>

        <div style="display:flex;gap:8px;align-items:center;margin-left:auto;">
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('history_assessment_all.import') }}"
               style="display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #e5e7eb;white-space:nowrap;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import
            </a>
            @endif
            <div style="position:relative;" id="exportWrap">
                <button onclick="toggleExportMenu()" id="exportBtn"
                    style="display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;white-space:nowrap;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" style="width:11px;height:11px;" id="exportChevron"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="exportMenu" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:white;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);overflow:hidden;min-width:210px;z-index:100;">
                    <a href="{{ route('history_assessment_all.export', request()->query()) }}"
                       style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:12px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f3f4f6;transition:background 0.12s;"
                       onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='white'">
                        <span style="width:26px;height:26px;border-radius:7px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:14px;">📋</span>
                        Export Rekomendasi
                    </a>
                    <a href="{{ route('history_assessment_all.export.kompetensi', request()->query()) }}"
                       style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:12px;font-weight:600;color:#374151;text-decoration:none;transition:background 0.12s;"
                       onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='white'">
                        <span style="width:26px;height:26px;border-radius:7px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:14px;">⭐</span>
                        Export Kompetensi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jabatan Saat Ini</th>
                        <th>JG</th>
                        <th>PG</th>
                        <th>Tgl Pelaksanaan</th>
                        <th>Rek. Inti</th>
                        <th>Rek. Primer</th>
                        <th>Rek. Sekunder</th>
                        <th>Rekomendasi Final</th>
                        <th>Tgl Exp IDP</th>
                        <th>Status IDP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBodyRekom">
                    @forelse($assessments as $a)
                    @php $idpPast = $a->tanggal_exp_idp && \Carbon\Carbon::parse($a->tanggal_exp_idp)->isPast(); @endphp
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">
                                    @if($a->karyawan->foto)
                                        <img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                                    @else
                                        {{ initials($a->karyawan->nama) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="karyawan-name">{{ $a->karyawan->nama }}</div>
                                    <div class="karyawan-nik">NIK {{ $a->karyawan->nik }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;">{{ $a->jabatan_saat_ini ?? '-' }}</td>
                        <td>{{ $a->job_grade ?? '-' }}</td>
                        <td>{{ $a->person_grade ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}</td>
                        <td>
                            @if($a->rekomendasi_inti)
                            {{-- FIX: width Blade diganti data-pct + apply via JS --}}
                            <div class="rekom-bar">
                                <div class="rekom-track"><div class="rekom-fill rekom-fill-green" data-pct="{{ $a->rekomendasi_inti }}"></div></div>
                                <span class="rekom-pct">{{ $a->rekomendasi_inti }}%</span>
                            </div>
                            @else -
                            @endif
                        </td>
                        <td>
                            @if($a->rekomendasi_primer)
                            <div class="rekom-bar">
                                <div class="rekom-track"><div class="rekom-fill rekom-fill-blue" data-pct="{{ $a->rekomendasi_primer }}"></div></div>
                                <span class="rekom-pct">{{ $a->rekomendasi_primer }}%</span>
                            </div>
                            @else -
                            @endif
                        </td>
                        <td>
                            @if($a->rekomendasi_skunder)
                            <div class="rekom-bar">
                                <div class="rekom-track"><div class="rekom-fill rekom-fill-amber" data-pct="{{ $a->rekomendasi_skunder }}"></div></div>
                                <span class="rekom-pct">{{ $a->rekomendasi_skunder }}%</span>
                            </div>
                            @else -
                            @endif
                        </td>
                        <td>
                            @if($a->rekomendasi_final === 'ready')
                                <span class="badge-final final-ready">● Ready</span>
                            @elseif($a->rekomendasi_final === 'ready_with_development')
                                <span class="badge-final final-rwd">● Ready w/ Dev</span>
                            @elseif($a->rekomendasi_final === 'not_ready')
                                <span class="badge-final final-nr">● Not Ready</span>
                            @else
                                <span class="badge-final final-none">-</span>
                            @endif
                        </td>
                        <td>
                            @if($a->tanggal_exp_idp)
                                {{-- FIX: color Blade diganti class statis --}}
                                <span class="{{ $idpPast ? 'idp-tgl-expired' : 'idp-tgl-aktif' }}">
                                    {{ \Carbon\Carbon::parse($a->tanggal_exp_idp)->format('d M Y') }}
                                </span>
                            @else -
                            @endif
                        </td>
                        <td>
                            @if($a->tanggal_exp_idp)
                                @if($idpPast)
                                    <span class="idp-badge idp-expired">⚠ Expired</span>
                                @else
                                    <span class="idp-badge idp-aktif">✓ Aktif</span>
                                @endif
                            @else
                                <span style="color:#9ca3af;font-size:12px;">-</span>
                            @endif
                        </td>
                        <td>
                            {{-- FIX: onclick dengan dataset --}}
                            <button type="button" class="btn-del"
                                data-url="{{ route('history_assessment_all.destroy', $a) }}"
                                data-nama="{{ $a->karyawan->nama }}"
                                data-tgl="{{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}"
                                onclick="openModal(this.dataset.url, this.dataset.nama, this.dataset.tgl)">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                <p style="font-size:13px;font-weight:600;color:#6b7280;margin-bottom:3px;">Belum ada data assessment rekomendasi</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assessments->hasPages())
        <div class="table-footer" id="footerRekom">
            <span>Menampilkan <strong>{{ $assessments->firstItem() }}</strong>–<strong>{{ $assessments->lastItem() }}</strong> dari <strong>{{ $assessments->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($assessments->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $assessments->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif
                @php $cur=$assessments->currentPage();$last=$assessments->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s > 1)
                    <a href="{{ $assessments->url(1) }}" class="page-btn">1</a>
                    @if($s > 2)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                @endif
                @for($i = $s; $i <= $e; $i++)
                    <a href="{{ $assessments->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                @if($e < $last)
                    @if($e < $last - 1)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                    <a href="{{ $assessments->url($last) }}" class="page-btn">{{ $last }}</a>
                @endif
                @if($assessments->hasMorePages())
                    <a href="{{ $assessments->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
                @else
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ===== PANEL KOMPETENSI ===== --}}
<div id="panel-komp" style="display:none;">
    <div class="filter-row">
        <div class="search-mini">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInputKomp" placeholder="Cari nama / NIK..." autocomplete="off">
            <div class="search-spinner" id="spinnerKomp"></div>
            <button class="clear-btn" id="clearKomp" onclick="clearSearchKomp()">×</button>
        </div>
    </div>
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Tgl Assessment</th>
                        <th>Periode</th>
                        <th style="text-align:center;">Under Competency</th>
                        <th style="text-align:center;">Under Qualification</th>
                        <th>Kesimpulan</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBodyKomp">
                    @forelse($assessmentKompetensi as $ak)
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">
                                    @if($ak->karyawan->foto)
                                        <img src="{{ Storage::url($ak->karyawan->foto) }}" alt="">
                                    @else
                                        {{ initials($ak->karyawan->nama) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="karyawan-name">{{ $ak->karyawan->nama }}</div>
                                    <div class="karyawan-nik">NIK {{ $ak->karyawan->nik }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $ak->tanggal_assessment->format('d M Y') }}</td>
                        <td>{{ $ak->periode ?? '-' }}</td>
                        <td style="text-align:center;">
                            {{-- FIX: style Blade diganti class statis under-ok/under-fail --}}
                            <span class="under-badge {{ $ak->total_competency_under > 0 ? 'under-fail' : 'under-ok' }}">
                                {{ $ak->total_competency_under }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <span class="under-badge {{ $ak->total_qualification_under > 0 ? 'under-fail' : 'under-ok' }}">
                                {{ $ak->total_qualification_under }}
                            </span>
                        </td>
                        <td>
                            @if($ak->kesimpulan === 'QUALIFIED')
                                <span class="badge-final final-qualified">● QUALIFIED</span>
                            @elseif($ak->kesimpulan === 'NOT QUALIFIED')
                                <span class="badge-final final-notqualified">● NOT QUALIFIED</span>
                            @else
                                <span class="badge-final final-none">-</span>
                            @endif
                        </td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;color:#6b7280;font-style:italic;">{{ $ak->keterangan ?? '-' }}</td>
                        <td>
                            @php
                            $kompLabels = \App\Models\HistoryAssessmentKompetensi::competencies();
                            $qualLabels = \App\Models\HistoryAssessmentKompetensi::qualifications();
                            $compR1det = 0; $compR2det = 0;
                            foreach (array_keys($kompLabels) as $kKey) {
                                $vDet = (int)($ak->$kKey ?? 0);
                                if ($vDet === 1) $compR1det++;
                                if ($vDet === 2) $compR2det++;
                            }
                            $detail = [
                                'nama'       => $ak->karyawan->nama,
                                'nik'        => $ak->karyawan->nik,
                                'tgl'        => $ak->tanggal_assessment->format('d M Y'),
                                'periode'    => $ak->periode ?? '-',
                                'comp_r1'    => $compR1det,
                                'comp_r2'    => $compR2det,
                                'qual_under' => $ak->total_qualification_under,
                                'kesimpulan' => $ak->kesimpulan ?? '-',
                                'kompetensi' => collect($kompLabels)->mapWithKeys(fn($label, $key) => [$label => $ak->$key ?? null])->toArray(),
                                'kualifikasi'=> collect($qualLabels)->mapWithKeys(fn($label, $key) => [$label => $ak->$key ?? null])->toArray(),
                            ];
                            @endphp
                            <div style="display:flex;gap:4px;">
                                {{-- FIX: onclick showDetail pakai this.dataset.detail --}}
                                <button type="button" class="btn-detail"
                                    data-detail='@json($detail)'
                                    onclick="showDetail(this.dataset.detail)"
                                    title="Lihat Detail">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button type="button" class="btn-del"
                                    data-url="{{ route('assessment_kompetensi_all.destroy', $ak) }}"
                                    data-nama="{{ $ak->karyawan->nama }}"
                                    data-tgl="{{ $ak->tanggal_assessment->format('d M Y') }}"
                                    onclick="openModal(this.dataset.url, this.dataset.nama, this.dataset.tgl)">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                <p style="font-size:13px;font-weight:600;color:#6b7280;margin-bottom:3px;">Belum ada data assessment kompetensi</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assessmentKompetensi->hasPages())
        <div class="table-footer" id="footerKomp">
            <span>Menampilkan <strong>{{ $assessmentKompetensi->firstItem() }}</strong>–<strong>{{ $assessmentKompetensi->lastItem() }}</strong> dari <strong>{{ $assessmentKompetensi->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($assessmentKompetensi->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $assessmentKompetensi->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif
                @php $curK=$assessmentKompetensi->currentPage();$lastK=$assessmentKompetensi->lastPage();$sK=max(1,$curK-2);$eK=min($lastK,$curK+2); @endphp
                @if($sK > 1)
                    <a href="{{ $assessmentKompetensi->url(1) }}" class="page-btn">1</a>
                    @if($sK > 2)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                @endif
                @for($i = $sK; $i <= $eK; $i++)
                    <a href="{{ $assessmentKompetensi->url($i) }}" class="page-btn {{ $i == $curK ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                @if($eK < $lastK)
                    @if($eK < $lastK - 1)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                    <a href="{{ $assessmentKompetensi->url($lastK) }}" class="page-btn">{{ $lastK }}</a>
                @endif
                @if($assessmentKompetensi->hasMorePages())
                    <a href="{{ $assessmentKompetensi->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
                @else
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ===== SIDE PANEL ===== --}}
<div class="side-overlay" id="sideOverlay" onclick="closeSidePanel()"></div>
<div class="side-panel" id="sidePanel">
    <div class="side-panel-header">
        <div class="side-panel-title">📊 Detail Assessment Kompetensi</div>
        <button class="side-close" onclick="closeSidePanel()">×</button>
    </div>
    <div class="side-panel-body" id="sidePanelBody">
        <div style="text-align:center;padding:40px 0;color:#9ca3af;font-size:13px;">Memuat data...</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Toast
function closeToast() {
    var t=document.getElementById('toast');if(!t)return;
    t.classList.add('hiding');setTimeout(function(){var w=document.getElementById('toastWrap');if(w)w.remove();},300);
}
window.addEventListener('DOMContentLoaded',function(){
    if(document.getElementById('toast'))setTimeout(function(){closeToast();},3000);
    if(new URLSearchParams(window.location.search).get('tab')==='komp')switchTab('komp');

    // Apply rekom-fill width dari data-pct
    document.querySelectorAll('.rekom-fill[data-pct]').forEach(function(el){
        el.style.width = el.dataset.pct + '%';
    });
});

// Tab
function switchTab(tab){
    document.getElementById('panel-rekom').style.display=tab==='rekom'?'block':'none';
    document.getElementById('panel-komp').style.display=tab==='komp'?'block':'none';
    document.getElementById('tab-rekom').classList.toggle('active',tab==='rekom');
    document.getElementById('tab-komp').classList.toggle('active',tab==='komp');
}

// Modal
var deleteUrl='';
function openModal(url,nama,tgl){
    deleteUrl=url;
    document.getElementById('modalDesc').innerHTML='Hapus assessment <strong>'+nama+'</strong> tanggal <strong>'+tgl+'</strong>.<br>Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('modalHapus').classList.add('show');
    document.body.style.overflow='hidden';
}
function closeModal(){document.getElementById('modalHapus').classList.remove('show');document.body.style.overflow='';}
function submitHapus(){document.getElementById('formHapus').action=deleteUrl;document.getElementById('formHapus').submit();}
document.getElementById('modalHapus').addEventListener('click',function(e){if(e.target===this)closeModal();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeModal();});

// Real-time search (panel rekomendasi)
var searchTimer=null;
var searchInput=document.getElementById('searchInputRekom');
var clearBtn=document.getElementById('clearRekom');
var spinner=document.getElementById('spinnerRekom');
var tableBody=document.getElementById('tableBodyRekom');

searchInput.addEventListener('input',function(){
    var val=this.value.trim();
    clearBtn.classList.toggle('visible',val.length>0);
    document.getElementById('hiddenSearch').value=val;
    clearTimeout(searchTimer);
    searchTimer=setTimeout(function(){doSearchRekom(val);},300);
});
searchInput.addEventListener('keydown',function(e){
    if(e.key==='Enter'){clearTimeout(searchTimer);doSearchRekom(this.value.trim());}
});

function clearSearchRekom(){
    searchInput.value='';
    clearBtn.classList.remove('visible');
    document.getElementById('hiddenSearch').value='';
    doSearchRekom('');
    searchInput.focus();
}

function doSearchRekom(keyword){
    var url=new URL(window.location.href);
    if(keyword)url.searchParams.set('search',keyword);
    else url.searchParams.delete('search');
    url.searchParams.delete('page');
    url.searchParams.set('tab','rekom');
    window.history.pushState({},'',url.toString());
    spinner.classList.add('show');
    tableBody.style.opacity='0.5';
    fetch(url.toString(),{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.text();})
        .then(function(html){
            var doc=new DOMParser().parseFromString(html,'text/html');
            var nb=doc.getElementById('tableBodyRekom');
            if(nb)tableBody.innerHTML=nb.innerHTML;
            var nf=doc.getElementById('footerRekom');
            if(nf&&document.getElementById('footerRekom'))document.getElementById('footerRekom').innerHTML=nf.innerHTML;
            tableBody.style.opacity='1';
            spinner.classList.remove('show');
            if(keyword)highlightText(tableBody,keyword);
            // Re-apply rekom-fill width setelah AJAX
            tableBody.querySelectorAll('.rekom-fill[data-pct]').forEach(function(el){
                el.style.width = el.dataset.pct + '%';
            });
        })
        .catch(function(){tableBody.style.opacity='1';spinner.classList.remove('show');});
}

function highlightText(el,keyword){
    var regex=new RegExp('('+keyword.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi');
    el.querySelectorAll('.karyawan-name,.karyawan-nik').forEach(function(node){
        node.innerHTML=node.textContent.replace(regex,'<mark>$1</mark>');
    });
}

// Real-time search panel kompetensi
var searchTimerKomp=null;
var searchInputKomp=document.getElementById('searchInputKomp');
var clearBtnKomp=document.getElementById('clearKomp');
var spinnerKomp=document.getElementById('spinnerKomp');
var tableBodyKomp=document.getElementById('tableBodyKomp');

searchInputKomp.addEventListener('input',function(){
    var val=this.value.trim();
    clearBtnKomp.classList.toggle('visible',val.length>0);
    clearTimeout(searchTimerKomp);
    searchTimerKomp=setTimeout(function(){doSearchKomp(val);},300);
});
searchInputKomp.addEventListener('keydown',function(e){
    if(e.key==='Enter'){clearTimeout(searchTimerKomp);doSearchKomp(this.value.trim());}
});
function clearSearchKomp(){
    searchInputKomp.value='';clearBtnKomp.classList.remove('visible');
    doSearchKomp('');searchInputKomp.focus();
}
function doSearchKomp(keyword){
    var url=new URL(window.location.href);
    if(keyword)url.searchParams.set('search_komp',keyword);
    else url.searchParams.delete('search_komp');
    url.searchParams.set('tab','komp');
    window.history.pushState({},'',url.toString());
    spinnerKomp.classList.add('show');
    tableBodyKomp.style.opacity='0.5';
    fetch(url.toString(),{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.text();})
        .then(function(html){
            var doc=new DOMParser().parseFromString(html,'text/html');
            var nb=doc.getElementById('tableBodyKomp');
            if(nb)tableBodyKomp.innerHTML=nb.innerHTML;
            tableBodyKomp.style.opacity='1';spinnerKomp.classList.remove('show');
            if(keyword)highlightText(tableBodyKomp,keyword);
        })
        .catch(function(){tableBodyKomp.style.opacity='1';spinnerKomp.classList.remove('show');});
}

// ===== SIDE PANEL DETAIL =====
function showDetail(detailJson) {
    var d = typeof detailJson === 'string' ? JSON.parse(detailJson) : detailJson;
    var panel = document.getElementById('sidePanel');
    var overlay = document.getElementById('sideOverlay');
    var body = document.getElementById('sidePanelBody');

    var isQualified = d.kesimpulan === 'QUALIFIED';
    var kesClass = isQualified ? 'qualified' : 'notqualified';
    var kesColor = isQualified ? '#15803d' : '#dc2626';

    var scoreClass = function(v) {
        if (!v) return 'sp-score-na';
        return 'sp-score-' + Math.min(Math.max(parseInt(v), 1), 4);
    };

    var kompRows = '';
    for (var label in d.kompetensi) {
        var val = d.kompetensi[label];
        var cls = scoreClass(val);
        var warning = (val == 1) ? ' <span style="font-size:10px;color:#dc2626;font-weight:700;">⚠ Kritis</span>' : (val == 2 ? ' <span style="font-size:10px;color:#d97706;">Under</span>' : '');
        kompRows += '<div class="sp-score-item">'
            + '<span class="sp-score-label">' + label + warning + '</span>'
            + '<span class="sp-score-val ' + cls + '">' + (val ?? '—') + '</span>'
            + '</div>';
    }

    var qualRows = '';
    for (var qlabel in d.kualifikasi) {
        var qval = d.kualifikasi[qlabel];
        var qcls = scoreClass(qval);
        var warning2 = (qval < 2 && qval !== null) ? ' <span style="font-size:10px;color:#dc2626;font-weight:700;">⚠ Under</span>' : '';
        qualRows += '<div class="sp-score-item">'
            + '<span class="sp-score-label">' + qlabel + warning2 + '</span>'
            + '<span class="sp-score-val ' + qcls + '">' + (qval ?? '—') + '</span>'
            + '</div>';
    }

    var compUnder = (d.comp_r1 ?? 0) + (d.comp_r2 ?? 0);
    var qualUnder = d.qual_under;

    body.innerHTML =
        '<div class="sp-karyawan">'
        + '<div class="sp-avatar">' + d.nama.substring(0,2).toUpperCase() + '</div>'
        + '<div><div class="sp-name">' + d.nama + '</div>'
        + '<div class="sp-sub">NIK ' + d.nik + ' · ' + d.tgl + (d.periode !== '-' ? ' · ' + d.periode : '') + '</div></div>'
        + '</div>'

        + '<div class="sp-kesimpulan ' + kesClass + '">'
        + '<span class="sp-kesimpulan-label">Kesimpulan</span>'
        + '<span class="sp-kesimpulan-val" style="color:' + kesColor + ';">' + (isQualified ? '✅ QUALIFIED' : '❌ NOT QUALIFIED') + '</span>'
        + '</div>'

        + '<div class="sp-summary">'
        + '<div class="sp-stat"><div class="sp-stat-num ' + (compUnder > 0 ? 'sp-stat-fail' : 'sp-stat-ok') + '">' + compUnder + '</div><div class="sp-stat-label">Under Competency</div></div>'
        + '<div class="sp-stat"><div class="sp-stat-num ' + (qualUnder > 0 ? 'sp-stat-fail' : 'sp-stat-ok') + '">' + qualUnder + '</div><div class="sp-stat-label">Under Qualification</div></div>'
        + '</div>'

        + '<div class="sp-section-title">⭐ Kompetensi Perilaku</div>'
        + '<div style="background:white;border:1px solid #f3f4f6;border-radius:8px;padding:4px 12px;">' + kompRows + '</div>'

        + '<div class="sp-section-title">🏆 Professional Qualification</div>'
        + '<div style="background:white;border:1px solid #f3f4f6;border-radius:8px;padding:4px 12px;">' + qualRows + '</div>'

        + '<div style="margin-top:16px;padding:10px 12px;background:#f9fafb;border-radius:8px;font-size:11px;color:#6b7280;">'
        + '<span style="font-weight:700;color:#374151;">Keterangan warna:</span><br>'
        + '<span style="color:#dc2626;">■</span> Nilai 1 (Kritis) &nbsp; '
        + '<span style="color:#d97706;">■</span> Nilai 2 (Under) &nbsp; '
        + '<span style="color:#16a34a;">■</span> Nilai 3 (Cukup) &nbsp; '
        + '<span style="color:#1d4ed8;">■</span> Nilai 4 (Baik)'
        + '</div>';

    overlay.classList.add('show');
    setTimeout(function() { panel.classList.add('show'); }, 10);
    document.body.style.overflow = 'hidden';
}

function closeSidePanel() {
    document.getElementById('sidePanel').classList.remove('show');
    document.getElementById('sideOverlay').classList.remove('show');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeSidePanel(); closeExportMenu(); }
});

// Export dropdown
function toggleExportMenu() {
    var menu = document.getElementById('exportMenu');
    var open = menu.style.display === 'block';
    menu.style.display = open ? 'none' : 'block';
    document.getElementById('exportChevron').style.transform = open ? '' : 'rotate(180deg)';
}
function closeExportMenu() {
    document.getElementById('exportMenu').style.display = 'none';
    document.getElementById('exportChevron').style.transform = '';
}
document.addEventListener('click', function(e) {
    var wrap = document.getElementById('exportWrap');
    if (wrap && !wrap.contains(e.target)) closeExportMenu();
});
</script>
@endpush