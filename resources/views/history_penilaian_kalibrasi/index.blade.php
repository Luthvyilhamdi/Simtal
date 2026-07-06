@extends('layouts.app')
@section('title', 'History Penilaian & Kalibrasi')
@section('breadcrumb', 'History Penilaian & Kalibrasi')

@php $isSA = auth()->user()->isSuperAdmin(); @endphp

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

    .stats-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px; }
    .stat-card { background:white;border-radius:var(--radius-sm);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px;text-align:center; }
    .stat-num { font-size:24px;font-weight:800;color:#111827; }
    .stat-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:3px; }
    .stat-card.pen .stat-num { color:#15803d; }
    .stat-card.kal .stat-num { color:#7c3aed; }

    .tab-wrap { display:flex;gap:4px;background:#f3f4f6;border-radius:10px;padding:4px;margin-bottom:16px; }
    .tab-btn { flex:1;padding:7px 12px;border-radius:7px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:inherit;color:#6b7280;background:transparent;transition:all 0.15s;text-align:center; }
    .tab-btn.active { background:white;color:#15803d;box-shadow:0 1px 4px rgba(0,0,0,0.08); }

    .filter-row { display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center; }
    .search-mini { display:flex;align-items:center;gap:8px;background:white;border:1px solid #e5e7eb;border-radius:8px;padding:7px 12px;width:220px;transition:border-color 0.15s; }
    .search-mini:focus-within { border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,0.1); }
    .search-mini svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-mini input { border:none;outline:none;font-size:12px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-mini input::placeholder { color:#9ca3af; }
    .clear-btn { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:15px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .clear-btn.visible { display:block; }
    .search-spinner { display:none;width:12px;height:12px;border:2px solid #e5e7eb;border-top-color:#15803d;border-radius:50%;animation:hpkspin 0.6s linear infinite;flex-shrink:0; }
    .search-spinner.show { display:block; }
    @keyframes hpkspin { to{transform:rotate(360deg)} }
    #panel-penilaian, #panel-kalibrasi { transition:opacity .15s ease; }
    .filter-select { padding:7px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:12px;font-family:inherit;color:#374151;background:white;outline:none;cursor:pointer; }
    .btn-reset { display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:12px;font-weight:500;cursor:pointer;text-decoration:none;white-space:nowrap; }
    .btn-reset:hover { background:#f5f5f0; }
    .spacer { flex:1; }
    .btn-act { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;border:none;cursor:pointer;font-family:inherit; }
    .btn-act svg { width:13px;height:13px;fill:none;stroke-width:2; }
    .btn-import { background:white;color:#374151;border:1px solid #e5e7eb; }
    .btn-import:hover { background:#f9fafb; }
    .btn-import svg { stroke:currentColor; }
    .btn-export { background:#15803d;color:white; }
    .btn-export svg { stroke:white; }

    .table-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:680px; }
    thead th { padding:10px 14px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:11px 14px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:9px; }
    .karyawan-avatar { width:32px;height:32px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:12px; }
    .karyawan-nik { font-size:11px;color:#9ca3af; }

    .count-pill { display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:24px;padding:0 8px;border-radius:20px;background:#f0fdf4;color:#15803d;font-size:12px;font-weight:800;border:1px solid #bbf7d0; }
    .count-pill.kal { background:#f5f3ff;color:#7c3aed;border-color:#ddd6fe; }
    .year-range { font-size:12px;color:#6b7280;font-weight:600; }
    .nilai-badge { display:inline-flex;align-items:center;padding:3px 11px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:0.3px; }
    .periode-chip { display:inline-flex;padding:2px 8px;border-radius:5px;background:#f3f4f6;font-size:11px;color:#6b7280;font-weight:600; }
    .tipe-chip { display:inline-flex;padding:2px 8px;border-radius:5px;font-size:11px;font-weight:700; }
    .tipe-kpi { background:#dcfce7;color:#15803d; }
    .tipe-360 { background:#dbeafe;color:#1d4ed8; }
    .latest-wrap { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
    .latest-nilai { font-weight:800;color:#111827; }

    .btn-detail { display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:7px;border:1px solid #dbeafe;background:#eff6ff;color:#2563eb;cursor:pointer;transition:all 0.12s;font-size:12px;font-weight:600;font-family:inherit; }
    .btn-detail:hover { background:#dbeafe;border-color:#93c5fd; }
    .btn-detail svg { width:13px;height:13px;stroke:#2563eb;fill:none;stroke-width:2; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:11px 16px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px; }
    .pagination-wrap { display:flex;align-items:center;gap:3px; }
    .page-btn { width:27px;height:27px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:50px 20px;color:#9ca3af; }
    .empty-state svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:13px;font-weight:600;color:#6b7280; }

    /* Toast */
    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border-radius:12px;padding:12px 14px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;font-weight:500;min-width:260px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s forwards; }
    .toast.ok { border:1px solid #bbf7d0;border-left:4px solid #16a34a;color:#15803d; }
    .toast.err { border:1px solid #fecaca;border-left:4px solid #ef4444;color:#dc2626; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:16px;padding:0;margin-left:auto; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }

    /* Modal */
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
    .modal-btn.green { background:#15803d;color:white; }
    .modal-btn.green:hover { background:#166534; }

    /* Import modal */
    .imp-file { display:block;width:100%;border:2px dashed #d1d5db;border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:#fafafa;font-size:12px;color:#6b7280;margin-bottom:12px;transition:all .15s; }
    .imp-file:hover { border-color:#15803d;background:#f0fdf4;color:#15803d; }
    .imp-file input[type="file"] { position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0; }
    .imp-cols { font-size:11px;color:#6b7280;background:#f9fafb;border:1px solid #f3f4f6;border-radius:8px;padding:10px 12px;line-height:1.6;margin-bottom:12px;text-align:left; }
    .imp-cols code { background:#eef2ff;color:#4338ca;padding:1px 5px;border-radius:4px;font-size:11px; }
    .tmpl-link { display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#15803d;text-decoration:none;margin-bottom:14px; }

    mark { background:#fef08a;border-radius:2px;padding:0 1px;color:inherit;font-weight:600; }

    /* Side panel */
    .side-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.35);z-index:2000;display:none;backdrop-filter:blur(2px); }
    .side-overlay.show { display:block; }
    .side-panel { position:fixed;right:0;top:0;bottom:0;width:430px;max-width:95vw;background:white;z-index:2001;box-shadow:-8px 0 40px rgba(0,0,0,0.14);display:flex;flex-direction:column;transform:translateX(100%);transition:transform 0.3s cubic-bezier(0.4,0,0.2,1); }
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
    .sp-section-title { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;margin:16px 0 10px;display:flex;align-items:center;gap:6px; }
    .sp-section-title::after { content:'';flex:1;height:1px;background:#f3f4f6; }
    .sp-item { border:1px solid #f3f4f6;border-radius:10px;padding:12px 14px;margin-bottom:8px;position:relative; }
    .sp-item-top { display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:4px; }
    .sp-year { font-size:14px;font-weight:800;color:#111827; }
    .sp-meta { font-size:11px;color:#9ca3af; }
    .sp-judul { font-size:12px;color:#374151;font-weight:500; }
    .sp-ket { font-size:11px;color:#9ca3af;font-style:italic;margin-top:4px; }
    .sp-del { position:absolute;top:10px;right:10px;width:24px;height:24px;border-radius:6px;border:1px solid #fee2e2;background:#fff;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center; }
    .sp-del:hover { background:#fef2f2; }
    .sp-del svg { width:11px;height:11px;stroke:#ef4444;fill:none;stroke-width:2; }

    @media (max-width:768px) { .stats-grid { grid-template-columns:repeat(2,1fr); } }
</style>
@endpush

@section('content')

{{-- TOAST --}}
@if(session('success'))
<div class="toast-wrap" id="toastWrap"><div class="toast ok" id="toast">
    <svg viewBox="0 0 24 24" width="16" height="16" stroke="#16a34a" fill="none" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <div>{{ session('success') }}</div><button class="toast-close" onclick="closeToast()">×</button>
</div></div>
@endif
@if(session('error'))
<div class="toast-wrap" id="toastWrap"><div class="toast err" id="toast">
    <svg viewBox="0 0 24 24" width="16" height="16" stroke="#dc2626" fill="none" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>{{ session('error') }}</div><button class="toast-close" onclick="closeToast()">×</button>
</div></div>
@endif

{{-- DELETE MODAL --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></div>
        <div class="modal-title">Hapus Data?</div>
        <div class="modal-desc" id="modalDesc">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

@if($isSA)
{{-- IMPORT MODAL: PENILAIAN --}}
<div class="modal-backdrop" id="modalImportPen">
    <div class="modal-box" style="max-width:440px;text-align:left">
        <div class="modal-title">📥 Import Penilaian</div>
        <div class="imp-cols">Kolom: <code>NIK</code> <code>Nama</code> <code>Tahun</code> <code>Periode</code> <code>Tipe</code> <code>Judul</code> <code>Nilai</code> <code>Keterangan</code><br>Periode: Triwulan I–IV / Tahunan · Tipe: KPI / 360 · Unik per (karyawan, tahun, periode, tipe) — yang sudah ada akan diperbarui</div>
        <a href="{{ route('history_penilaian_kalibrasi.template.penilaian') }}" class="tmpl-link">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#15803d" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Template</a>
        <form method="POST" action="{{ route('history_penilaian_kalibrasi.import.penilaian') }}" enctype="multipart/form-data">
            @csrf
            <label class="imp-file">Pilih file Excel/CSV<input type="file" name="file" accept=".xlsx,.xls,.csv" required onchange="updateImpFileLabel(this)"></label>
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" onclick="closeImport('Pen')">Batal</button>
                <button type="submit" class="modal-btn green">Import</button>
            </div>
        </form>
    </div>
</div>
{{-- IMPORT MODAL: KALIBRASI --}}
<div class="modal-backdrop" id="modalImportKal">
    <div class="modal-box" style="max-width:440px;text-align:left">
        <div class="modal-title">📥 Import Kalibrasi</div>
        <div class="imp-cols">Kolom: <code>NIK</code> <code>Nama</code> <code>Tahun</code> <code>Nilai</code> <code>Keterangan</code><br>Nilai: FEE / EXE / PEE / MEE / ME / SME / PME / BEE / NME / FBE · Unik per (karyawan, tahun) — yang sudah ada akan diperbarui</div>
        <a href="{{ route('history_penilaian_kalibrasi.template.kalibrasi') }}" class="tmpl-link">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#15803d" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Template</a>
        <form method="POST" action="{{ route('history_penilaian_kalibrasi.import.kalibrasi') }}" enctype="multipart/form-data">
            @csrf
            <label class="imp-file">Pilih file Excel/CSV<input type="file" name="file" accept=".xlsx,.xls,.csv" required onchange="updateImpFileLabel(this)"></label>
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" onclick="closeImport('Kal')">Batal</button>
                <button type="submit" class="modal-btn green">Import</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">History Penilaian &amp; Kalibrasi</div>
        <div class="page-sub">Dikelompokkan per karyawan — klik Riwayat untuk lihat semua tahun. Import &amp; export massal.</div>
    </div>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-card pen"><div class="stat-num">{{ $stats['total_penilaian'] }}</div><div class="stat-label">Total Penilaian</div></div>
    <div class="stat-card"><div class="stat-num">{{ $stats['karyawan_penilaian'] }}</div><div class="stat-label">Karyawan Dinilai</div></div>
    <div class="stat-card kal"><div class="stat-num">{{ $stats['total_kalibrasi'] }}</div><div class="stat-label">Total Kalibrasi</div></div>
    <div class="stat-card"><div class="stat-num">{{ $stats['karyawan_kalibrasi'] }}</div><div class="stat-label">Karyawan Dikalibrasi</div></div>
</div>

{{-- TAB --}}
<div class="tab-wrap">
    <button class="tab-btn active" id="tab-penilaian" data-tab="penilaian" onclick="switchTab('penilaian')">📊 Penilaian</button>
    <button class="tab-btn" id="tab-kalibrasi" data-tab="kalibrasi" onclick="switchTab('kalibrasi')">🎯 Kalibrasi</button>
</div>

{{-- ===== PANEL PENILAIAN ===== --}}
<div id="panel-penilaian">
    <div class="filter-row">
        <div class="search-mini">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchPenilaian" placeholder="Cari nama / NIK..." value="{{ request('search') }}" autocomplete="off">
            <div class="search-spinner" id="spinnerPenilaian"></div>
            <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearPenilaian" type="button" onclick="clearSearchPenilaian()">×</button>
        </div>
        <select id="tahunPenilaian" class="filter-select" onchange="applyPenilaian()">
            <option value="">Semua Tahun</option>
            @foreach($tahunsPenilaian as $t)<option value="{{ $t }}" {{ request('tahun')==$t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
        </select>
        @if(request('search') || request('tahun'))<a href="{{ route('history_penilaian_kalibrasi.index', ['tab'=>'penilaian']) }}" class="btn-reset">× Reset</a>@endif
        <div class="spacer"></div>
        @if($isSA)<button class="btn-act btn-import" onclick="openImport('Pen')"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>Import</button>@endif
        <a href="{{ route('history_penilaian_kalibrasi.export.penilaian', request()->only('search','tahun')) }}" class="btn-act btn-export"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Download Penilaian</a>
    </div>

    <div class="table-card" id="cardPenilaian">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Karyawan</th><th>Jumlah</th><th>Rentang Tahun</th><th>Penilaian Terbaru</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($penilaianKaryawans as $kar)
                    @php
                        $items = $kar->penilaians;
                        $latest = $items->first();
                        $thMax = $items->max('tahun'); $thMin = $items->min('tahun');
                        $detailP = [
                            'tipe_panel' => 'penilaian',
                            'nama' => $kar->nama, 'nik' => $kar->nik,
                            'items' => $items->map(fn($x) => [
                                'id' => $x->id, 'tahun' => $x->tahun, 'periode' => $x->periode_label, 'tipe' => $x->tipe,
                                'judul' => $x->judul, 'nilai' => $x->nilai_format, 'keterangan' => $x->keterangan,
                                'del' => route('history_penilaian_kalibrasi.penilaian.destroy', $x->id),
                                'info'=> $x->judul.' ('.$x->tahun.' '.$x->periode_label.')',
                            ])->values()->toArray(),
                        ];
                    @endphp
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">@if($kar->foto)<img src="{{ Storage::url($kar->foto) }}" alt="">@else{{ initials($kar->nama) }}@endif</div>
                                <div><div class="karyawan-name">{{ $kar->nama }}</div><div class="karyawan-nik">NIK {{ $kar->nik }}</div></div>
                            </div>
                        </td>
                        <td><span class="count-pill">{{ $items->count() }}</span></td>
                        <td><span class="year-range">{{ $thMin==$thMax ? $thMin : ($thMin.' – '.$thMax) }}</span></td>
                        <td>
                            @if($latest)
                            <div class="latest-wrap">
                                <span class="latest-nilai">{{ $latest->nilai_format }}</span>
                                <span class="tipe-chip {{ $latest->tipe==='KPI' ? 'tipe-kpi' : 'tipe-360' }}">{{ $latest->tipe }}</span>
                                <span class="periode-chip">{{ $latest->tahun }} · {{ $latest->periode_label }}</span>
                            </div>
                            @else - @endif
                        </td>
                        <td>
                            <button type="button" class="btn-detail" data-detail='@json($detailP)' onclick="openSide(this.dataset.detail)">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Riwayat
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty-state"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><p>Belum ada data penilaian</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($penilaianKaryawans->hasPages())
        <div class="table-footer">
            <span>Menampilkan <strong>{{ $penilaianKaryawans->firstItem() }}</strong>–<strong>{{ $penilaianKaryawans->lastItem() }}</strong> dari <strong>{{ $penilaianKaryawans->total() }}</strong> karyawan</span>
            <div class="pagination-wrap">
                @if($penilaianKaryawans->onFirstPage())<span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>@else<a href="{{ $penilaianKaryawans->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>@endif
                @php $cur=$penilaianKaryawans->currentPage();$last=$penilaianKaryawans->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s>1)<a href="{{ $penilaianKaryawans->url(1) }}" class="page-btn">1</a>@if($s>2)<span class="page-btn disabled" style="border:none;background:transparent">…</span>@endif @endif
                @for($i=$s;$i<=$e;$i++)<a href="{{ $penilaianKaryawans->url($i) }}" class="page-btn {{ $i==$cur?'active':'' }}">{{ $i }}</a>@endfor
                @if($e<$last)@if($e<$last-1)<span class="page-btn disabled" style="border:none;background:transparent">…</span>@endif<a href="{{ $penilaianKaryawans->url($last) }}" class="page-btn">{{ $last }}</a>@endif
                @if($penilaianKaryawans->hasMorePages())<a href="{{ $penilaianKaryawans->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>@else<span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>@endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ===== PANEL KALIBRASI ===== --}}
<div id="panel-kalibrasi" style="display:none">
    <div class="filter-row">
        <div class="search-mini">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchKalibrasi" placeholder="Cari nama / NIK..." value="{{ request('search_kalibrasi') }}" autocomplete="off">
            <div class="search-spinner" id="spinnerKalibrasi"></div>
            <button class="clear-btn {{ request('search_kalibrasi') ? 'visible' : '' }}" id="clearKalibrasi" type="button" onclick="clearSearchKalibrasi()">×</button>
        </div>
        <select id="tahunKalibrasi" class="filter-select" onchange="applyKalibrasi()">
            <option value="">Semua Tahun</option>
            @foreach($tahunsKalibrasi as $t)<option value="{{ $t }}" {{ request('tahun_kalibrasi')==$t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
        </select>
        @if(request('search_kalibrasi') || request('tahun_kalibrasi'))<a href="{{ route('history_penilaian_kalibrasi.index', ['tab'=>'kalibrasi']) }}" class="btn-reset">× Reset</a>@endif
        <div class="spacer"></div>
        @if($isSA)<button class="btn-act btn-import" onclick="openImport('Kal')"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>Import</button>@endif
        <a href="{{ route('history_penilaian_kalibrasi.export.kalibrasi', request()->only('search_kalibrasi','tahun_kalibrasi')) }}" class="btn-act btn-export"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Download Kalibrasi</a>
    </div>

    <div class="table-card" id="cardKalibrasi">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Karyawan</th><th>Jumlah</th><th>Rentang Tahun</th><th>Kalibrasi Terbaru</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($kalibrasiKaryawans as $kar)
                    @php
                        $items = $kar->kalibrasis;
                        $latest = $items->first();
                        $thMax = $items->max('tahun'); $thMin = $items->min('tahun');
                        $detailK = [
                            'tipe_panel' => 'kalibrasi',
                            'nama' => $kar->nama, 'nik' => $kar->nik,
                            'items' => $items->map(fn($x) => [
                                'id' => $x->id, 'tahun' => $x->tahun, 'nilai' => $x->nilai, 'label' => $x->nilai_label,
                                'color' => $x->nilai_badge_color, 'keterangan' => $x->keterangan,
                                'del' => route('history_penilaian_kalibrasi.kalibrasi.destroy', $x->id),
                                'info'=> 'Kalibrasi '.$x->tahun.' ('.$x->nilai.')',
                            ])->values()->toArray(),
                        ];
                        $lc = $latest ? $latest->nilai_badge_color : ['bg'=>'#f3f4f6','text'=>'#374151'];
                    @endphp
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">@if($kar->foto)<img src="{{ Storage::url($kar->foto) }}" alt="">@else{{ initials($kar->nama) }}@endif</div>
                                <div><div class="karyawan-name">{{ $kar->nama }}</div><div class="karyawan-nik">NIK {{ $kar->nik }}</div></div>
                            </div>
                        </td>
                        <td><span class="count-pill kal">{{ $items->count() }}</span></td>
                        <td><span class="year-range">{{ $thMin==$thMax ? $thMin : ($thMin.' – '.$thMax) }}</span></td>
                        <td>
                            @if($latest)
                            <div class="latest-wrap">
                                <span class="nilai-badge" style="background:{{ $lc['bg'] }};color:{{ $lc['text'] }}" title="{{ $latest->nilai_label }}">{{ $latest->nilai }}</span>
                                <span class="periode-chip">{{ $latest->tahun }}</span>
                            </div>
                            @else - @endif
                        </td>
                        <td>
                            <button type="button" class="btn-detail" data-detail='@json($detailK)' onclick="openSide(this.dataset.detail)">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Riwayat
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty-state"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg><p>Belum ada data kalibrasi</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kalibrasiKaryawans->hasPages())
        <div class="table-footer">
            <span>Menampilkan <strong>{{ $kalibrasiKaryawans->firstItem() }}</strong>–<strong>{{ $kalibrasiKaryawans->lastItem() }}</strong> dari <strong>{{ $kalibrasiKaryawans->total() }}</strong> karyawan</span>
            <div class="pagination-wrap">
                @if($kalibrasiKaryawans->onFirstPage())<span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>@else<a href="{{ $kalibrasiKaryawans->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>@endif
                @php $curK=$kalibrasiKaryawans->currentPage();$lastK=$kalibrasiKaryawans->lastPage();$sK=max(1,$curK-2);$eK=min($lastK,$curK+2); @endphp
                @if($sK>1)<a href="{{ $kalibrasiKaryawans->url(1) }}" class="page-btn">1</a>@if($sK>2)<span class="page-btn disabled" style="border:none;background:transparent">…</span>@endif @endif
                @for($i=$sK;$i<=$eK;$i++)<a href="{{ $kalibrasiKaryawans->url($i) }}" class="page-btn {{ $i==$curK?'active':'' }}">{{ $i }}</a>@endfor
                @if($eK<$lastK)@if($eK<$lastK-1)<span class="page-btn disabled" style="border:none;background:transparent">…</span>@endif<a href="{{ $kalibrasiKaryawans->url($lastK) }}" class="page-btn">{{ $lastK }}</a>@endif
                @if($kalibrasiKaryawans->hasMorePages())<a href="{{ $kalibrasiKaryawans->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>@else<span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>@endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- SIDE PANEL --}}
<div class="side-overlay" id="sideOverlay" onclick="closeSide()"></div>
<div class="side-panel" id="sidePanel">
    <div class="side-panel-header">
        <div class="side-panel-title" id="sideTitle">Detail</div>
        <button class="side-close" onclick="closeSide()">×</button>
    </div>
    <div class="side-panel-body" id="sideBody"></div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tab) {
    document.getElementById('panel-penilaian').style.display = tab === 'penilaian' ? 'block' : 'none';
    document.getElementById('panel-kalibrasi').style.display = tab === 'kalibrasi' ? 'block' : 'none';
    document.getElementById('tab-penilaian').classList.toggle('active', tab === 'penilaian');
    document.getElementById('tab-kalibrasi').classList.toggle('active', tab === 'kalibrasi');
    var url = new URL(window.location.href); url.searchParams.set('tab', tab); window.history.replaceState({}, '', url.toString());
}
document.addEventListener('DOMContentLoaded', function() {
    if (new URLSearchParams(window.location.search).get('tab') === 'kalibrasi') switchTab('kalibrasi');
});

function closeToast() { var t=document.getElementById('toast'); if(!t)return; t.classList.add('hiding'); setTimeout(function(){var w=document.getElementById('toastWrap'); if(w)w.remove();},300); }
window.addEventListener('DOMContentLoaded', function(){ if(document.getElementById('toast')) setTimeout(closeToast, 3500); });

// ===== REAL-TIME SEARCH (AJAX) =====
function hpkDebounce(fn, ms){ var t; return function(){ var a=arguments, c=this; clearTimeout(t); t=setTimeout(function(){ fn.apply(c,a); }, ms); }; }
function hpkHighlight(root, kw){
    if(!kw) return;
    var rx = new RegExp('(' + kw.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')','gi');
    root.querySelectorAll('.karyawan-name, .karyawan-nik').forEach(function(n){ n.innerHTML = n.textContent.replace(rx,'<mark>$1</mark>'); });
}
function ajaxReplace(url, spinnerId, cardId, kw){
    var spin = document.getElementById(spinnerId);
    var card = document.getElementById(cardId);
    if (spin) spin.classList.add('show');
    card.style.opacity = '0.5';
    fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } })
        .then(function(r){ return r.text(); })
        .then(function(html){
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var fresh = doc.getElementById(cardId);
            if (fresh) card.innerHTML = fresh.innerHTML;
            if (kw) hpkHighlight(card, kw);
            card.style.opacity = '1';
            if (spin) spin.classList.remove('show');
        })
        .catch(function(){ card.style.opacity='1'; if(spin) spin.classList.remove('show'); });
}

function applyPenilaian(){
    var kw = document.getElementById('searchPenilaian').value.trim();
    var th = document.getElementById('tahunPenilaian').value;
    document.getElementById('clearPenilaian').classList.toggle('visible', kw.length>0);
    var url = new URL(window.location.href);
    if (kw) url.searchParams.set('search', kw); else url.searchParams.delete('search');
    if (th) url.searchParams.set('tahun', th); else url.searchParams.delete('tahun');
    url.searchParams.delete('page_penilaian');
    url.searchParams.set('tab', 'penilaian');
    window.history.replaceState({}, '', url.toString());
    ajaxReplace(url.toString(), 'spinnerPenilaian', 'cardPenilaian', kw);
}
function clearSearchPenilaian(){ var i=document.getElementById('searchPenilaian'); i.value=''; applyPenilaian(); i.focus(); }

function applyKalibrasi(){
    var kw = document.getElementById('searchKalibrasi').value.trim();
    var th = document.getElementById('tahunKalibrasi').value;
    document.getElementById('clearKalibrasi').classList.toggle('visible', kw.length>0);
    var url = new URL(window.location.href);
    if (kw) url.searchParams.set('search_kalibrasi', kw); else url.searchParams.delete('search_kalibrasi');
    if (th) url.searchParams.set('tahun_kalibrasi', th); else url.searchParams.delete('tahun_kalibrasi');
    url.searchParams.delete('page_kalibrasi');
    url.searchParams.set('tab', 'kalibrasi');
    window.history.replaceState({}, '', url.toString());
    ajaxReplace(url.toString(), 'spinnerKalibrasi', 'cardKalibrasi', kw);
}
function clearSearchKalibrasi(){ var i=document.getElementById('searchKalibrasi'); i.value=''; applyKalibrasi(); i.focus(); }

document.addEventListener('DOMContentLoaded', function(){
    var sp = document.getElementById('searchPenilaian');
    if (sp) sp.addEventListener('input', hpkDebounce(applyPenilaian, 300));
    var sk = document.getElementById('searchKalibrasi');
    if (sk) sk.addEventListener('input', hpkDebounce(applyKalibrasi, 300));
});

// ===== DELETE MODAL =====
var deleteUrl = '';
function openModal(url, info) {
    deleteUrl = url;
    document.getElementById('modalDesc').innerHTML = 'Hapus <strong>' + (info || 'data ini') + '</strong>?<br>Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('modalHapus').classList.add('show'); document.body.style.overflow = 'hidden';
}
function closeModal() { document.getElementById('modalHapus').classList.remove('show'); document.body.style.overflow=''; }
function submitHapus() { var f=document.getElementById('formHapus'); f.action=deleteUrl; f.submit(); }
document.getElementById('modalHapus').addEventListener('click', function(e){ if(e.target===this) closeModal(); });

// ===== IMPORT MODAL =====
function openImport(w){ var m=document.getElementById('modalImport'+w); if(m){ m.classList.add('show'); document.body.style.overflow='hidden'; } }
function closeImport(w){ var m=document.getElementById('modalImport'+w); if(m){ m.classList.remove('show'); document.body.style.overflow=''; } }
['Pen','Kal'].forEach(function(w){ var m=document.getElementById('modalImport'+w); if(m) m.addEventListener('click', function(e){ if(e.target===this) closeImport(w); }); });
function updateImpFileLabel(input){ if(input.files && input.files[0]) input.closest('label').childNodes[0].textContent = input.files[0].name; }

// ===== SIDE PANEL =====
function escapeHtml(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }

function openSide(json) {
    var d = typeof json === 'string' ? JSON.parse(json) : json;
    var body = document.getElementById('sideBody');
    var inisial = (d.nama || '?').substring(0,2).toUpperCase();

    var head = '<div class="sp-karyawan"><div class="sp-avatar">' + inisial + '</div>'
        + '<div><div class="sp-name">' + escapeHtml(d.nama) + '</div>'
        + '<div class="sp-sub">NIK ' + escapeHtml(d.nik) + ' · ' + d.items.length + ' data</div></div></div>';

    var rows = '';
    if (d.tipe_panel === 'penilaian') {
        document.getElementById('sideTitle').textContent = '📊 Riwayat Penilaian';
        rows = d.items.map(function(it){
            var tipeCls = it.tipe === 'KPI' ? 'background:#dcfce7;color:#15803d' : 'background:#dbeafe;color:#1d4ed8';
            return '<div class="sp-item">'
                + '<button class="sp-del" title="Hapus" onclick="openModal(\'' + it.del + '\',\'' + escapeHtml(it.info).replace(/'/g,"\\'") + '\')"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button>'
                + '<div class="sp-item-top"><span class="sp-year">' + escapeHtml(it.nilai) + '</span>'
                + '<span class="sp-meta" style="margin-right:28px">' + it.tahun + ' · ' + escapeHtml(it.periode) + '</span></div>'
                + '<div class="sp-judul">' + escapeHtml(it.judul) + ' '
                + '<span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:4px;' + tipeCls + '">' + escapeHtml(it.tipe) + '</span></div>'
                + (it.keterangan ? '<div class="sp-ket">💬 ' + escapeHtml(it.keterangan) + '</div>' : '')
                + '</div>';
        }).join('');
    } else {
        document.getElementById('sideTitle').textContent = '🎯 Riwayat Kalibrasi';
        rows = d.items.map(function(it){
            var bg = (it.color && it.color.bg) ? it.color.bg : '#f3f4f6';
            var tx = (it.color && it.color.text) ? it.color.text : '#374151';
            return '<div class="sp-item">'
                + '<button class="sp-del" title="Hapus" onclick="openModal(\'' + it.del + '\',\'' + escapeHtml(it.info).replace(/'/g,"\\'") + '\')"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button>'
                + '<div class="sp-item-top"><span class="sp-year">' + it.tahun + '</span>'
                + '<span class="nilai-badge" style="margin-right:28px;background:' + bg + ';color:' + tx + '">' + escapeHtml(it.nilai) + '</span></div>'
                + '<div class="sp-judul" style="font-size:11px;color:#6b7280">' + escapeHtml(it.label) + '</div>'
                + (it.keterangan ? '<div class="sp-ket">💬 ' + escapeHtml(it.keterangan) + '</div>' : '')
                + '</div>';
        }).join('');
    }

    body.innerHTML = head + '<div class="sp-section-title">Riwayat Lengkap</div>' + rows;
    document.getElementById('sideOverlay').classList.add('show');
    setTimeout(function(){ document.getElementById('sidePanel').classList.add('show'); }, 10);
    document.body.style.overflow = 'hidden';
}
function closeSide() {
    document.getElementById('sidePanel').classList.remove('show');
    document.getElementById('sideOverlay').classList.remove('show');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeModal(); closeSide(); closeImport('Pen'); closeImport('Kal'); } });
</script>
@endpush