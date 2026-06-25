@extends('layouts.app')
@section('title', 'Data Talent Pool')
@section('breadcrumb', 'Data Talent')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:3px; }
    .btn-primary { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;transition:background 0.15s;white-space:nowrap; }
    .btn-primary:hover { background:#166534; }
    .btn-primary svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:2.5; }

    .search-mini { display:flex;align-items:center;gap:8px;background:white;border:1px solid #e5e7eb;border-radius:8px;padding:7px 12px;width:220px;transition:border-color .15s; }
    .search-mini:focus-within { border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,.1); }
    .search-mini svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-mini input { border:none;outline:none;font-size:12px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-mini input::placeholder { color:#9ca3af; }
    .clear-btn { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:15px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .clear-btn.visible { display:block; }
    .search-spinner { display:none;width:12px;height:12px;border:2px solid #e5e7eb;border-top-color:#15803d;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0; }
    .search-spinner.show { display:block; }
    @keyframes spin { to{transform:rotate(360deg)} }

    .stats-row { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px; }
    .stat-card { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:16px 20px;display:flex;align-items:center;gap:14px; }
    .stat-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .stat-icon svg { width:20px;height:20px;fill:none;stroke-width:1.8; }
    .stat-num { font-size:24px;font-weight:800;color:#111827; }
    .stat-label { font-size:12px;color:#6b7280;margin-top:1px; }

    .filter-bar { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .filter-select { border:1.5px solid #e5e7eb;border-radius:9px;padding:7px 14px;font-size:13px;font-family:inherit;color:#111827;background:white;outline:none;cursor:pointer;appearance:none;padding-right:28px; }
    .filter-select:focus { border-color:#15803d; }
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:10px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }

    .table-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto; }
    table { width:100%;border-collapse:collapse; }
    thead th { background:#f9fafb;padding:11px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;text-align:left;border-bottom:1px solid #e5e7eb;white-space:nowrap; }
    tbody tr { border-bottom:1px solid #f3f4f6;transition:background .1s; }
    tbody tr:last-child { border-bottom:none; }
    tbody tr:hover { background:#fafafa; }
    tbody td { padding:12px 16px;font-size:13px;color:#374151;vertical-align:middle; }
    .td-nik { font-size:12px;color:#6b7280;font-weight:600; }
    .td-nama { font-weight:600;color:#111827; }
    .td-jabatan { font-size:12px;color:#6b7280; }
    .badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap; }
    .badge-longlist  { background:#dbeafe;color:#1d4ed8; }
    .badge-shortlist { background:#dcfce7;color:#15803d; }
    .badge-band { background:#fdf4ff;color:#a21caf; }
    .td-actions { display:flex;align-items:center;gap:6px; }
    .btn-act { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s; }
    .btn-act.edit:hover { background:#eff6ff;border-color:#bfdbfe; }
    .btn-act.edit svg { stroke:#3b82f6; }
    .btn-act.del:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-act.del svg { stroke:#ef4444; }
    .btn-act svg { width:13px;height:13px;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn .35s cubic-bezier(.4,0,.2,1) forwards; }
    .toast.hiding { animation:toastOut .3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    .toast.error { border-color:#fecaca; border-left-color:#dc2626; color:#dc2626; }
    .toast.error .toast-icon { background:#fee2e2; }
    .toast.error .toast-icon svg { stroke:#dc2626; }
    .toast.error .toast-progress { background:#dc2626; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:400px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center;animation:modalIn .25s cubic-bezier(.4,0,.2,1); }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:17px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:24px; }
    .modal-actions { display:flex;gap:10px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    .modal-btn.green { background:#15803d;color:white; }
    .modal-btn.green:hover { background:#166534; }
    @keyframes modalIn { from{opacity:0;transform:scale(.92);}to{opacity:1;transform:scale(1);} }
    .edit-modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:440px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn .25s cubic-bezier(.4,0,.2,1); }
    .edit-modal-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:18px; }
    .form-group { display:flex;flex-direction:column;gap:6px;margin-bottom:14px; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all .15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white; }
    .klas-group { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
    .klas-card { display:flex;align-items:center;gap:10px;padding:12px;border:2px solid #e5e7eb;border-radius:10px;cursor:pointer;transition:all .15s; }
    .klas-card input { display:none; }
    .klas-card.sel-longlist  { border-color:#3b82f6;background:#eff6ff; }
    .klas-card.sel-shortlist { border-color:#15803d;background:#f0fdf4; }
    .klas-name { font-size:13px;font-weight:700;color:#374151; }
    .klas-card.sel-longlist  .klas-name { color:#1d4ed8; }
    .klas-card.sel-shortlist .klas-name { color:#15803d; }

    /* Import modal */
    .imp-cols { font-size:11px;color:#6b7280;background:#f9fafb;border:1px solid #f3f4f6;border-radius:8px;padding:10px 12px;line-height:1.6;margin-bottom:12px;text-align:left; }
    .imp-cols code { background:#eef2ff;color:#4338ca;padding:1px 5px;border-radius:4px;font-size:11px; }
    .imp-file-label { display:block;width:100%;border:2px dashed #d1d5db;border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:#fafafa;font-size:12px;color:#6b7280;margin-bottom:12px;box-sizing:border-box;transition:all .15s; }
    .imp-file-label:hover { border-color:#15803d;background:#f0fdf4;color:#15803d; }
    .imp-file-label input[type="file"] { position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0; }
    .imp-tmpl { display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#15803d;text-decoration:none;margin-bottom:14px; }

    @media (max-width:640px) {
        .stats-row { grid-template-columns:1fr; }
        .filter-bar { flex-direction:column;align-items:stretch; }
    }
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

@if(session('error') || $errors->any())
<div class="toast-wrap" id="toastWrapError">
    <div class="toast error" id="toastError">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
        <div>{{ session('error') ?: $errors->first() }}</div>
        <button class="toast-close" onclick="closeToastError()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- Modal Hapus --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus dari Talent Pool?</div>
        <div class="modal-desc" id="modalDesc">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeHapus()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

{{-- Modal Edit --}}
<div class="modal-backdrop" id="modalEdit">
    <div class="edit-modal-box">
        <div class="edit-modal-title">✏️ Edit Klasifikasi</div>
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <div class="form-label">Klasifikasi <span style="color:#ef4444">*</span></div>
                <div class="klas-group">
                    <label class="klas-card" id="edit-longlist" onclick="selectKlas('longlist')">
                        <input type="radio" name="klasifikasi" value="longlist">
                        <span style="font-size:20px">🔵</span>
                        <span class="klas-name">Longlist</span>
                    </label>
                    <label class="klas-card" id="edit-shortlist" onclick="selectKlas('shortlist')">
                        <input type="radio" name="klasifikasi" value="shortlist">
                        <span style="font-size:20px">🟢</span>
                        <span class="klas-name">Shortlist</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="form-label">Catatan</div>
                <textarea name="catatan" id="editCatatan" rows="3" class="form-input" style="resize:vertical" placeholder="Catatan opsional..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px">
                <button type="button" class="modal-btn cancel" style="flex:1" onclick="closeEdit()">Batal</button>
                <button type="submit" class="modal-btn danger" style="flex:1;background:#15803d">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Import --}}
@if(auth()->user()->isSuperAdmin())
<div class="modal-backdrop" id="modalImport">
    <div class="modal-box" style="max-width:440px;text-align:left;">
        <div class="modal-title" style="text-align:left;margin-bottom:12px;">📥 Import Talent Pool</div>
        <div class="imp-cols">
            Kolom: <code>NIK</code> <code>Periode</code> <code>Klasifikasi</code> <code>Catatan</code><br>
            Klasifikasi: <strong>longlist</strong> atau <strong>shortlist</strong> · Duplikat dilewati otomatis
        </div>
        <a href="{{ route('talent_pool.import.template') }}"
           id="btnTemplate"
           class="imp-tmpl"
           onclick="startDownload(this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span id="dlText">Download Template</span>
        </a>
        <form method="POST" action="{{ route('talent_pool.import.store') }}" enctype="multipart/form-data" id="importForm">
            @csrf
            <label class="imp-file-label" id="importLabel">
                📄 Pilih file Excel/CSV
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required onchange="updateImportLabel(this)" />
            </label>
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" onclick="closeImport()">Batal</button>
                <button type="submit" class="modal-btn green" id="btnImport">Import</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">🎯 Data Talent Pool</div>
        <div class="page-sub">Daftar karyawan Longlist & Shortlist per periode</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        @if(auth()->user()->isSuperAdmin())
        <button onclick="openImport()"
           style="display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid #e5e7eb;white-space:nowrap;cursor:pointer;font-family:inherit;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </button>
        @endif
        <a href="{{ route('talent_pool.export', request()->query()) }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#374151;color:white;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </a>
        <a href="{{ route('talent_pool.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Talent
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4">
            <svg viewBox="0 0 24 24" style="stroke:#15803d"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <div class="stat-num">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Talent {{ $periode }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff">
            <svg viewBox="0 0 24 24" style="stroke:#3b82f6"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
            <div class="stat-num" style="color:#1d4ed8">{{ $stats['longlist'] }}</div>
            <div class="stat-label">Longlist</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7">
            <svg viewBox="0 0 24 24" style="stroke:#15803d"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div>
            <div class="stat-num" style="color:#15803d">{{ $stats['shortlist'] }}</div>
            <div class="stat-label">Shortlist</div>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('talent_pool.index') }}" id="filterForm" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%">
        <input type="hidden" name="search" id="searchHidden" value="{{ request('search') }}">
        <div style="margin-left:auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <div class="search-mini">
                <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchInput" value="{{ request('search') }}"
                    placeholder="Cari nama / NIK..." autocomplete="off">
                <div class="search-spinner" id="searchSpinner"></div>
                <button type="button" class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearBtn" onclick="clearSearch()">×</button>
            </div>
            <div class="select-wrap">
                <select name="periode" class="filter-select" onchange="submitFilter()">
                    @foreach($periodeList as $p)
                        <option value="{{ $p }}" {{ $p == $periode ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                    @if(!$periodeList->contains(now()->year))
                        <option value="{{ now()->year }}" {{ now()->year == $periode ? 'selected' : '' }}>{{ now()->year }}</option>
                    @endif
                </select>
            </div>
            <div class="select-wrap">
                <select name="klasifikasi" class="filter-select" onchange="submitFilter()">
                    <option value="">Semua Klasifikasi</option>
                    <option value="longlist"  {{ request('klasifikasi') === 'longlist'  ? 'selected' : '' }}>Longlist</option>
                    <option value="shortlist" {{ request('klasifikasi') === 'shortlist' ? 'selected' : '' }}>Shortlist</option>
                </select>
            </div>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    @if($talents->total() > 0)
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan Saat Ini</th>
                    <th>Job Grade</th>
                    <th>Person Grade</th>
                    <th>Band</th>
                    <th>Klasifikasi</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @foreach($talents as $i => $t)
                @php $k = $t->karyawan; @endphp
                <tr>
                    <td style="color:#9ca3af;font-size:12px">{{ ($talents->currentPage() - 1) * $talents->perPage() + $i + 1 }}</td>
                    <td class="td-nik">{{ $k->nik ?? '-' }}</td>
                    <td><div class="td-nama">{{ $k->nama ?? '-' }}</div></td>
                    <td class="td-jabatan">{{ $k->jabatan_saat_ini ?? '-' }}</td>
                    <td><span style="font-size:12px;font-weight:600;color:#374151">{{ $k->jobGrade->job_grade ?? '-' }}</span></td>
                    <td><span style="font-size:12px;font-weight:600;color:#374151">{{ $k->personGrade->person_grade ?? '-' }}</span></td>
                    <td>
                        @if($k->band)
                            <span class="badge badge-band">{{ $k->band }}</span>
                        @else
                            <span style="color:#d1d5db;font-size:12px">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $t->klasifikasi }}">{{ $t->klasifikasi_label }}</span>
                    </td>
                    <td style="font-size:12px;color:#6b7280;max-width:160px">
                        {{ $t->catatan ? \Illuminate\Support\Str::limit($t->catatan, 40) : '-' }}
                    </td>
                    <td>
                        <div class="td-actions">
                            <button type="button" class="btn-act edit"
                                data-id="{{ $t->id }}"
                                data-klas="{{ $t->klasifikasi }}"
                                data-catatan="{{ addslashes($t->catatan ?? '') }}">
                                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button type="button" class="btn-act del"
                                data-id="{{ $t->id }}"
                                data-nama="{{ addslashes($k->nama ?? '') }}">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="paginationFooter" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px;">
        <span>
            Menampilkan <strong>{{ $talents->firstItem() ?? 0 }}</strong>–<strong>{{ $talents->lastItem() ?? 0 }}</strong>
            dari <strong>{{ $talents->total() }}</strong> data
        </span>
        @if($talents->hasPages())
        <div style="display:flex;align-items:center;gap:3px;">
            @if($talents->onFirstPage())
                <span style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;opacity:0.4;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $talents->previousPageUrl() }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @endif
            @php $cur=$talents->currentPage();$last=$talents->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s > 1)
                <a href="{{ $talents->url(1) }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;font-size:12px;">1</a>
                @if($s > 2)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
            @endif
            @for($pg = $s; $pg <= $e; $pg++)
                @if($pg == $cur)
                    <span style="width:28px;height:28px;border-radius:7px;border:1px solid #15803d;background:#15803d;display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:600;">{{ $pg }}</span>
                @else
                    <a href="{{ $talents->url($pg) }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;font-size:12px;">{{ $pg }}</a>
                @endif
            @endfor
            @if($e < $last)
                @if($e < $last - 1)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
                <a href="{{ $talents->url($last) }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;font-size:12px;">{{ $last }}</a>
            @endif
            @if($talents->hasMorePages())
                <a href="{{ $talents->nextPageUrl() }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;opacity:0.4;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif
        </div>
        @endif
    </div>
    @else
    {{-- Empty state — ditampilkan di dalam tbody agar posisi center di dalam tabel --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan Saat Ini</th>
                    <th>Job Grade</th>
                    <th>Person Grade</th>
                    <th>Band</th>
                    <th>Klasifikasi</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            @if(request('search'))
                                <p>Tidak ada data talent yang cocok dengan "<strong>{{ request('search') }}</strong>"</p>
                                <span style="font-size:12px;color:#9ca3af">Coba kata kunci lain atau hapus filter pencarian</span>
                            @else
                                <p>Belum ada data talent untuk periode {{ $periode }}</p>
                                <span style="font-size:12px;color:#9ca3af">Klik "Tambah Talent" untuk mulai menambahkan</span>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function closeToast() {
    const t = document.getElementById('toast');
    if (!t) return;
    t.classList.add('hiding');
    setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
}
function closeToastError() {
    const t = document.getElementById('toastError');
    if (!t) return;
    t.classList.add('hiding');
    setTimeout(() => document.getElementById('toastWrapError')?.remove(), 300);
}
window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000);
    if (document.getElementById('toastError')) setTimeout(() => closeToastError(), 6000);
});

let hapusId = '';
function openHapus(id, nama) {
    hapusId = id;
    document.getElementById('modalDesc').innerHTML = 'Hapus <strong>' + nama + '</strong> dari Talent Pool?<br>Data tidak dapat dikembalikan.';
    document.getElementById('modalHapus').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeHapus() {
    document.getElementById('modalHapus').classList.remove('show');
    document.body.style.overflow = '';
}
function submitHapus() {
    document.getElementById('formHapus').action = '/talent-pool/' + hapusId;
    document.getElementById('formHapus').submit();
}

function openEdit(id, klas, catatan) {
    document.getElementById('formEdit').action = '/talent-pool/' + id;
    document.getElementById('editCatatan').value = catatan;
    selectKlas(klas);
    document.getElementById('modalEdit').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeEdit() {
    document.getElementById('modalEdit').classList.remove('show');
    document.body.style.overflow = '';
}
function selectKlas(val) {
    document.getElementById('edit-longlist').className  = 'klas-card' + (val === 'longlist'  ? ' sel-longlist'  : '');
    document.getElementById('edit-shortlist').className = 'klas-card' + (val === 'shortlist' ? ' sel-shortlist' : '');
    document.querySelector(`input[name="klasifikasi"][value="${val}"]`).checked = true;
}

// ===== IMPORT MODAL =====
function openImport() {
    const m = document.getElementById('modalImport');
    if (m) { m.classList.add('show'); document.body.style.overflow = 'hidden'; }
}
function closeImport() {
    const m = document.getElementById('modalImport');
    if (m) {
        m.classList.remove('show');
        document.body.style.overflow = '';
        document.getElementById('importForm').reset();
        const lbl = document.getElementById('importLabel');
        if (lbl) lbl.childNodes[0].textContent = '📄 Pilih file Excel/CSV';
        const btn = document.getElementById('btnImport');
        if (btn) { btn.disabled = false; btn.textContent = 'Import'; }
    }
}
function updateImportLabel(input) {
    if (input.files && input.files[0]) {
        input.closest('label').childNodes[0].textContent = '📄 ' + input.files[0].name;
    }
}
document.getElementById('importForm')?.addEventListener('submit', function(e) {
    const fileInput = this.querySelector('input[type="file"]');
    if (!fileInput || !fileInput.files || !fileInput.files.length) { e.preventDefault(); return; }
    const btn = document.getElementById('btnImport');
    if (btn) { setTimeout(() => { btn.disabled = true; btn.textContent = 'Mengimport...'; }, 0); }
});
function startDownload(el) {
    const text = document.getElementById('dlText');
    text.textContent      = '✓ Mengunduh...';
    el.style.pointerEvents = 'none';
    setTimeout(() => {
        text.textContent      = 'Download Template';
        el.style.pointerEvents = '';
    }, 2500);
}

window.addEventListener('pageshow', function(e) {
    if (e.persisted) {
        const btn = document.getElementById('btnImport');
        if (btn) { btn.disabled = false; btn.textContent = 'Import'; }
    }
});

const modalImport = document.getElementById('modalImport');
if (modalImport) modalImport.addEventListener('click', function(e) { if (e.target === this) closeImport(); });

function submitFilter() {
    document.getElementById('searchHidden').value = document.getElementById('searchInput').value;
    document.getElementById('filterForm').submit();
}

['modalHapus','modalEdit'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) {
        if (e.target === this) { closeHapus(); closeEdit(); }
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeHapus(); closeEdit(); closeImport(); }
});

document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.btn-act.edit');
    if (editBtn) { openEdit(editBtn.dataset.id, editBtn.dataset.klas, editBtn.dataset.catatan); return; }
    const delBtn = e.target.closest('.btn-act.del');
    if (delBtn) { openHapus(delBtn.dataset.id, delBtn.dataset.nama); }
});

// === REAL-TIME SEARCH ===
let searchTimer = null;
const searchInput  = document.getElementById('searchInput');
const searchHidden = document.getElementById('searchHidden');
const clearBtn     = document.getElementById('clearBtn');
const spinner      = document.getElementById('searchSpinner');

searchInput.addEventListener('input', function() {
    const val = this.value.trim();
    clearBtn.classList.toggle('visible', val.length > 0);
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => doSearch(val), 300);
});

searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { clearTimeout(searchTimer); doSearch(this.value.trim()); }
});

function clearSearch() {
    searchInput.value = '';
    clearBtn.classList.remove('visible');
    doSearch('');
    searchInput.focus();
}

function doSearch(keyword) {
    const url = new URL(window.location.href);
    if (keyword) url.searchParams.set('search', keyword);
    else url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.history.pushState({}, '', url.toString());
    if (searchHidden) searchHidden.value = keyword;

    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return;

    spinner.classList.add('show');
    tableBody.style.opacity = '0.5';

    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newBody = doc.getElementById('tableBody');
            if (newBody) tableBody.innerHTML = newBody.innerHTML;
            tableBody.style.opacity = '1';
            spinner.classList.remove('show');
        })
        .catch(() => {
            tableBody.style.opacity = '1';
            spinner.classList.remove('show');
        });
}

window.addEventListener('popstate', () => {
    const kw = new URL(window.location.href).searchParams.get('search') || '';
    searchInput.value = kw;
    clearBtn.classList.toggle('visible', kw.length > 0);
    doSearch(kw);
});
</script>
@endpush