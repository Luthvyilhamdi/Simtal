@extends('layouts.app')
@section('title', 'History Pendidikan')
@section('breadcrumb-parent', 'Data Karyawan')
@section('breadcrumb', 'History Pendidikan')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

    /* Search kecil */
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

    .table-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:640px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:12px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:10px; }
    .karyawan-avatar { width:34px;height:34px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:13px; }
    .karyawan-nik { font-size:11px;color:#9ca3af;margin-top:1px; }

    .badge-jenjang { display:inline-block;padding:3px 10px;border-radius:8px;font-size:12px;font-weight:800;background:#dbeafe;color:#1d4ed8; }
    .muted { color:#9ca3af; }

    .count-pill { display:inline-flex;align-items:center;gap:5px; }
    .count-num { font-size:15px;font-weight:700;color:#111827; }
    .count-label { font-size:11px;color:#9ca3af; }

    .btn-view { display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:7px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.12s;white-space:nowrap; }
    .btn-view:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-view svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .btn-import { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;border:none;cursor:pointer;font-family:inherit; }
    .btn-import:hover { background:#166534; }
    .btn-import svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:2; }
    .btn-export { display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;border:1px solid #e5e7eb;cursor:pointer;font-family:inherit; }
    .btn-export:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-export svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px; }
    .pagination-wrap { display:flex;align-items:center;gap:3px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:50px 20px;color:#9ca3af; }
    .empty-state svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    mark { background:#fef08a;border-radius:2px;padding:0 1px;color:inherit;font-weight:600; }

    /* Toast */
    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast.err { border-color:#fecaca;border-left-color:#ef4444;color:#dc2626; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 4s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    /* Modal Import */
    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:26px;width:100%;max-width:480px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.25s cubic-bezier(0.4,0,0.2,1);max-height:92vh;overflow-y:auto; }
    .modal-head { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:4px; }
    .modal-title { font-size:17px;font-weight:700;color:#111827; }
    .modal-sub { font-size:12.5px;color:#6b7280;margin-top:3px;line-height:1.5; }
    .modal-x { border:none;background:#f3f4f6;color:#6b7280;width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:18px;flex-shrink:0; }
    .modal-actions { display:flex;gap:10px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.green { background:#15803d;color:white; }
    .modal-btn.green:hover { background:#166534; }
    .modal-btn:disabled { opacity:0.55;cursor:not-allowed; }
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    .tpl-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin:14px 0;flex-wrap:wrap; }
    .tpl-name { font-size:12.5px;font-weight:700;color:#111827; }
    .tpl-desc { font-size:11px;color:#6b7280;margin-top:1px; }
    .btn-tpl { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:8px 13px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .kolom-hint { display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:6px; }
    .kolom-item { background:#f9fafb;border:1px solid #f3f4f6;border-radius:8px;padding:8px 10px; }
    .kolom-name { font-size:11px;font-weight:700;color:#374151;font-family:monospace; }
    .kolom-req { font-size:9px;font-weight:700;color:#ef4444; }
    .kolom-desc { font-size:10px;color:#9ca3af;margin-top:2px; }
    .upload-area { border:2px dashed #bbf7d0;border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:all 0.15s;background:#fafafa;position:relative;margin-top:12px; }
    .upload-area:hover, .upload-area.dragover { border-color:#15803d;background:#f0fdf4; }
    .upload-area input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
    .upload-text { font-size:13px;color:#374151;font-weight:600; }
    .upload-text strong { color:#15803d; }
    .upload-hint { font-size:11px;color:#9ca3af;margin-top:3px; }
    .file-preview { display:none;align-items:center;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;margin-top:10px; }
    .file-preview.show { display:flex; }
    .file-preview-name { font-size:12.5px;font-weight:600;color:#111827;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .file-preview-remove { width:22px;height:22px;border-radius:50%;border:none;background:#fee2e2;color:#ef4444;cursor:pointer;font-size:12px;flex-shrink:0; }

    @media (max-width:640px){ .kolom-hint { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

@if(session('success') || session('error'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast {{ session('error') ? 'err' : '' }}" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') ?? session('error') }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">History Pendidikan</div>
        <div class="page-sub" id="jumlahInfo">{{ $karyawans->total() }} karyawan</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        {{-- Search kecil (sama seperti History Karyawan) --}}
        <div class="search-mini">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Cari nama / NIK..." value="{{ request('search') }}" autocomplete="off">
            <div class="search-spinner" id="searchSpinner"></div>
            <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearBtn" onclick="clearSearch()">×</button>
        </div>

        <a href="{{ route('riwayat_pendidikan_all.export', ['search' => request('search')]) }}" class="btn-export">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </a>

        <button type="button" class="btn-import" onclick="openImport()">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </button>
    </div>
</div>

{{-- TABLE --}}
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan</th>
                    <th>Departemen</th>
                    <th>Pendidikan Terakhir</th>
                    <th>Jumlah Jenjang</th>
                    <th>Lihat</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($karyawans as $k)
                <tr>
                    <td>
                        <div class="karyawan-info">
                            <div class="karyawan-avatar">
                                @if($k->foto)
                                    <img src="{{ Storage::url($k->foto) }}" alt="">
                                @else
                                    {{ initials($k->nama) }}
                                @endif
                            </div>
                            <div>
                                <div class="karyawan-name">{{ $k->nama }}</div>
                                <div class="karyawan-nik">NIK {{ $k->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $k->jabatan_saat_ini ?: ($k->jabatan->nama_jabatan ?? '-') }}</td>
                    <td>{{ $k->departemen->nama_departemen ?? '-' }}</td>
                    <td>
                        @if($k->jenjang_pendidikan)
                            <span class="badge-jenjang">{{ $k->jenjang_pendidikan }}</span>
                            @if($k->jurusan)<span style="color:#6b7280;margin-left:4px;">· {{ $k->jurusan }}</span>@endif
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="count-pill">
                            <span class="count-num">{{ $k->riwayat_pendidikan_count }}</span>
                            <span class="count-label">jenjang</span>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('riwayat_pendidikan.index', $k) }}" class="btn-view">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Lihat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:3px;">Belum ada data pendidikan</p>
                            <span style="font-size:12px;">Klik "Import" untuk mengisi data secara massal</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" id="tableFooter">
        <span id="footerInfo">
            Menampilkan <strong>{{ $karyawans->firstItem() ?? 0 }}</strong>–<strong>{{ $karyawans->lastItem() ?? 0 }}</strong>
            dari <strong>{{ $karyawans->total() }}</strong> karyawan
        </span>

        @if($karyawans->hasPages())
        <div class="pagination-wrap" id="paginationWrap">
            @if($karyawans->onFirstPage())
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
            @else
                <a href="{{ $karyawans->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
            @endif

            @php
                $cur  = $karyawans->currentPage();
                $last = $karyawans->lastPage();
                $s    = max(1, $cur - 2);
                $e    = min($last, $cur + 2);
            @endphp

            @if($s > 1)
                <a href="{{ $karyawans->url(1) }}" class="page-btn">1</a>
                @if($s > 2)<span class="page-btn disabled" style="border:none;background:transparent;width:auto;">…</span>@endif
            @endif

            @for($i = $s; $i <= $e; $i++)
                <a href="{{ $karyawans->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
            @endfor

            @if($e < $last)
                @if($e < $last - 1)<span class="page-btn disabled" style="border:none;background:transparent;width:auto;">…</span>@endif
                <a href="{{ $karyawans->url($last) }}" class="page-btn">{{ $last }}</a>
            @endif

            @if($karyawans->hasMorePages())
                <a href="{{ $karyawans->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
            @else
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- MODAL IMPORT --}}
<div class="modal-backdrop" id="modalImport">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <div class="modal-title">📥 Import History Pendidikan</div>
                <div class="modal-sub">Satu baris = satu jenjang (NIK boleh berulang). Kunci <strong>NIK + Jenjang</strong>: sudah ada → diperbarui, belum ada → dibuat.</div>
            </div>
            <button type="button" class="modal-x" onclick="closeImport()">&times;</button>
        </div>

        <div class="tpl-box">
            <div>
                <div class="tpl-name">template-import-riwayat-pendidikan.xlsx</div>
                <div class="tpl-desc">Contoh: satu karyawan dengan beberapa jenjang</div>
            </div>
            <button type="button" class="btn-tpl" data-url="{{ route('riwayat_pendidikan_all.import.template') }}" onclick="triggerDownload(this.dataset.url)">⬇ Download Template</button>
        </div>

        <div class="kolom-hint">
            <div class="kolom-item"><div class="kolom-name">nik <span class="kolom-req">*wajib</span></div><div class="kolom-desc">NIK karyawan terdaftar</div></div>
            <div class="kolom-item"><div class="kolom-name">jenjang <span class="kolom-req">*wajib</span></div><div class="kolom-desc">{{ implode(' / ', \App\Models\Karyawan::JENJANG_PENDIDIKAN) }}</div></div>
            <div class="kolom-item"><div class="kolom-name">jurusan</div><div class="kolom-desc">opsional</div></div>
            <div class="kolom-item"><div class="kolom-name">institusi</div><div class="kolom-desc">opsional</div></div>
        </div>

        <form method="POST" action="{{ route('riwayat_pendidikan_all.import.store') }}" enctype="multipart/form-data" id="importForm">
            @csrf
            <div class="upload-area" id="dropZone">
                <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" onchange="previewFile(this)">
                <div style="font-size:30px;margin-bottom:6px;">📂</div>
                <div class="upload-text"><strong>Klik untuk pilih file</strong> atau drag &amp; drop</div>
                <div class="upload-hint">Format: .xlsx, .xls, .csv — Maks. 10MB</div>
            </div>
            <div class="file-preview" id="filePreview">
                <span style="font-size:18px;">📊</span>
                <span class="file-preview-name" id="previewName">-</span>
                <button type="button" class="file-preview-remove" onclick="removeFile()">✕</button>
            </div>
            <div style="font-size:11.5px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;padding:9px 11px;margin-top:12px;line-height:1.6;">
                ⚠️ Baris dengan NIK tidak ditemukan / jenjang tidak valid akan dilewati otomatis.
            </div>
            <div class="modal-actions" style="margin-top:16px;">
                <button type="button" class="modal-btn cancel" onclick="closeImport()">Batal</button>
                <button type="submit" class="modal-btn green" id="btnImportSubmit" disabled>Import Sekarang</button>
            </div>
        </form>
    </div>
</div>

<iframe id="downloadFrame" src="about:blank" style="display:none;"></iframe>

@endsection

@push('scripts')
<script>
// ===== Toast =====
function closeToast() {
    const t = document.getElementById('toast');
    if (!t) return;
    t.classList.add('hiding');
    setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
}
window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('toast')) setTimeout(() => closeToast(), 4000);
});

// ===== Live search (sama seperti History Karyawan) =====
let searchTimer = null;
const searchInput = document.getElementById('searchInput');
const clearBtn    = document.getElementById('clearBtn');
const spinner     = document.getElementById('searchSpinner');
const tableBody   = document.getElementById('tableBody');
const tableFooter = document.getElementById('tableFooter');
const jumlahInfo  = document.getElementById('jumlahInfo');

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

    spinner.classList.add('show');
    tableBody.style.opacity = '0.5';

    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newBody = doc.getElementById('tableBody');
            if (newBody) tableBody.innerHTML = newBody.innerHTML;
            const newFooter = doc.getElementById('tableFooter');
            if (newFooter) tableFooter.innerHTML = newFooter.innerHTML;
            const newInfo = doc.getElementById('jumlahInfo');
            if (newInfo) jumlahInfo.textContent = newInfo.textContent;
            tableBody.style.opacity = '1';
            spinner.classList.remove('show');
            if (keyword) highlightText(tableBody, keyword);
        })
        .catch(() => { tableBody.style.opacity='1'; spinner.classList.remove('show'); });
}
function highlightText(el, keyword) {
    const regex = new RegExp('(' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    el.querySelectorAll('.karyawan-name, .karyawan-nik').forEach(node => {
        node.innerHTML = node.textContent.replace(regex, '<mark>$1</mark>');
    });
}
window.addEventListener('popstate', () => {
    const kw = new URL(window.location.href).searchParams.get('search') || '';
    searchInput.value = kw;
    clearBtn.classList.toggle('visible', kw.length > 0);
    doSearch(kw);
});

// ===== Modal Import =====
function openImport() {
    document.getElementById('modalImport').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeImport() {
    document.getElementById('modalImport').classList.remove('show');
    document.body.style.overflow = '';
}
function previewFile(input) {
    if (input.files && input.files[0]) {
        document.getElementById('previewName').textContent = input.files[0].name;
        document.getElementById('filePreview').classList.add('show');
        document.getElementById('btnImportSubmit').disabled = false;
    }
}
function removeFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').classList.remove('show');
    document.getElementById('btnImportSubmit').disabled = true;
}
var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', function() { dropZone.classList.remove('dragover'); });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        document.getElementById('fileInput').files = e.dataTransfer.files;
        previewFile(document.getElementById('fileInput'));
    }
});
document.getElementById('importForm').addEventListener('submit', function() {
    var btn = document.getElementById('btnImportSubmit');
    btn.disabled = true;
    btn.textContent = '⏳ Mengimport...';
});
document.getElementById('modalImport').addEventListener('click', function(e) { if (e.target === this) closeImport(); });
function triggerDownload(url) { document.getElementById('downloadFrame').src = url; }

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeImport(); });
</script>
@endpush
