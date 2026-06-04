@extends('layouts.app')
@section('title', 'Profil Karyawan')
@section('breadcrumb', 'Profil Karyawan')

@push('styles')
<style>
    .page-header {
        display: flex; align-items: center;
        justify-content: space-between; margin-bottom: 20px;
        gap: 12px; flex-wrap: wrap;
    }
    .page-title { font-size: 20px; font-weight: 700; color: #111827; }
    .page-sub { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .btn-primary {
        display: inline-flex; align-items: center; gap: 6px;
        background: #15803d; color: white; padding: 8px 16px;
        border-radius: 8px; font-size: 12px; font-weight: 600;
        text-decoration: none; border: none; cursor: pointer;
        font-family: inherit; white-space: nowrap;
    }
    .btn-primary:hover { background: #166534; }

    /* === TOAST === */
    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none; }
    .toast {
        display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;
        border-left:4px solid #16a34a;border-radius:12px;padding:12px 14px;
        box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;
        min-width:260px;max-width:340px;position:relative;overflow:hidden;pointer-events:all;
        animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards;
    }
    .toast.hiding { animation:toastOut 0.3s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast-icon { width:20px;height:20px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:11px;height:11px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-msg { flex:1;line-height:1.4; }
    .toast-close { width:20px;height:20px;border-radius:50%;border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;padding:0; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;border-radius:0 0 0 8px;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%)} to{opacity:1;transform:translateX(0)} }
    @keyframes toastOut { from{opacity:1;transform:translateX(0)} to{opacity:0;transform:translateX(110%)} }
    @keyframes toastProgress { from{width:100%} to{width:0%} }

    /* === MODAL HAPUS === */
    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:380px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center; }
    .modal-icon-wrap { width:52px;height:52px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px; }
    .modal-icon-wrap svg { width:24px;height:24px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:22px; }
    .modal-name { font-weight:600;color:#111827; }
    .modal-actions { display:flex;gap:10px; }
    .modal-btn { flex:1;padding:10px;border-radius:9px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.cancel:hover { background:#f3f4f6; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }

    /* === TOOLBAR === */
    .toolbar { display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap; }

    /* Search kecil */
    .search-mini {
        display:flex;align-items:center;gap:8px;
        background:white;border:1px solid #e5e7eb;
        border-radius:8px;padding:7px 12px;
        width:220px;transition:border-color 0.15s;
    }
    .search-mini:focus-within { border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,0.1); }
    .search-mini svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-mini input {
        border:none;outline:none;font-size:12px;
        font-family:inherit;color:#111827;background:transparent;width:100%;
    }
    .search-mini input::placeholder { color:#9ca3af; }
    .clear-btn { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:15px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .clear-btn.visible { display:block; }

    /* Spinner pencarian */
    .search-spinner { display:none;width:12px;height:12px;border:2px solid #e5e7eb;border-top-color:#15803d;border-radius:50%;animation:spin 0.6s linear infinite;flex-shrink:0; }
    .search-spinner.show { display:block; }
    @keyframes spin { to{transform:rotate(360deg)} }

    /* === TABLE === */
    .table-card { background:white;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:600px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:12px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:10px; }
    .karyawan-avatar { width:34px;height:34px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:13px;white-space:nowrap; }
    .karyawan-nik { font-size:11px;color:#9ca3af;margin-top:1px; }

    .badge { display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap; }
    .badge-green { background:#dcfce7;color:#15803d; }
    .badge-red { background:#fee2e2;color:#dc2626; }
    .badge-gray { background:#f3f4f6;color:#6b7280; }

    .action-btns { display:flex;align-items:center;gap:4px; }
    .btn-icon { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;cursor:pointer;background:white;transition:all 0.12s;text-decoration:none; }
    .btn-icon svg { width:13px;height:13px;fill:none; }
    .btn-icon.view:hover { background:#eff6ff;border-color:#bfdbfe; }
    .btn-icon.view svg { stroke:#3b82f6; }
    .btn-icon.edit:hover { background:#f0fdf4;border-color:#bbf7d0; }
    .btn-icon.edit svg { stroke:#16a34a; }
    .btn-icon.del:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-icon.del svg { stroke:#ef4444; }

    /* === PAGINATION === */
    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px; }
    .pagination-wrap { display:flex;align-items:center;gap:3px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:50px 20px;color:#9ca3af; }
    .empty-state svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none; }
    .empty-state p { font-size:14px;font-weight:500;color:#6b7280;margin-bottom:3px; }
    .empty-state span { font-size:12px; }

    /* highlight search */
    mark { background:#fef08a;border-radius:2px;padding:0 1px;color:inherit;font-weight:600; }
</style>
@endpush

@section('content')

{{-- TOAST --}}
@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="toast-msg">{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- MODAL HAPUS --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus Data Karyawan?</div>
        <div class="modal-desc">Kamu akan menghapus data karyawan<br><span class="modal-name" id="modalNama">—</span><br>Tindakan ini tidak dapat dibatalkan.</div>
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
        <div class="page-title">Profil Karyawan</div>
        <div class="page-sub" id="jumlahInfo">{{ $karyawans->total() }} karyawan terdaftar</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        {{-- Search kecil --}}
        <div class="search-mini" id="searchWrap">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Cari nama / NIK..." value="{{ request('search') }}" autocomplete="off">
            <div class="search-spinner" id="searchSpinner"></div>
            <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearBtn" onclick="clearSearch()">×</button>
        </div>

        {{-- Export --}}
        <a href="{{ route('karyawan.export', request()->query()) }}"
           style="display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #e5e7eb;white-space:nowrap;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </a>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('karyawan.import') }}"
           style="display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #e5e7eb;white-space:nowrap;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </a>
        @endif
        <a href="{{ route('karyawan.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah
        </a>
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
                    <th>Job Grade</th>
                    <th>Tgl Masuk</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($karyawans as $k)
                <tr>
                    <td>
                        <div class="karyawan-info">
                            <div class="karyawan-avatar">
                                @if($k->foto)
                                    <img src="{{ Storage::url($k->foto) }}" alt="{{ $k->nama }}">
                                @else
                                    {{ strtoupper(substr($k->nama, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="karyawan-name">{{ $k->nama }}</div>
                                <div class="karyawan-nik">NIK {{ $k->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $k->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $k->departemen->nama_departemen ?? '-' }}</td>
                    <td><span class="badge badge-gray">{{ $k->jobGrade->job_grade ?? '-' }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d M Y') }}</td>
                    <td>
                        @if($k->status === 'aktif')
                            <span class="badge badge-green">● Aktif</span>
                        @else
                            <span class="badge badge-red">● Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('karyawan.show', $k) }}" class="btn-icon view" title="Detail">
                                <svg viewBox="0 0 24 24" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('karyawan.edit', $k) }}" class="btn-icon edit" title="Edit">
                                <svg viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <button type="button" class="btn-icon del" title="Hapus"
                                data-id="{{ $k->id }}" data-nama="{{ $k->nama }}"
                                data-url="{{ route('karyawan.destroy', $k) }}"
                                onclick="openModal(this.dataset.id, this.dataset.nama, this.dataset.url)">
                                <svg viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <p>Belum ada data karyawan</p>
                        <span>Klik "Tambah" untuk menambahkan data</span>
                    </div>
                </td></tr>
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
            @php $cur=$karyawans->currentPage();$last=$karyawans->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s > 1)
                <a href="{{ $karyawans->url(1) }}" class="page-btn">1</a>
                @if($s > 2)
                    <span class="page-btn disabled" style="border:none;background:transparent;width:auto;padding:0 2px;">…</span>
                @endif
            @endif
            @for($i = $s; $i <= $e; $i++)
                <a href="{{ $karyawans->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
            @endfor
            @if($e < $last)
                @if($e < $last - 1)
                    <span class="page-btn disabled" style="border:none;background:transparent;width:auto;padding:0 2px;">…</span>
                @endif
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

@endsection

@push('scripts')
<script>
// === TOAST ===
function closeToast() {
    const t = document.getElementById('toast');
    if (!t) return;
    t.classList.add('hiding');
    setTimeout(() => { const w=document.getElementById('toastWrap'); if(w)w.remove(); }, 300);
}
window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('toast')) setTimeout(closeToast, 3000);
});

// === MODAL HAPUS ===
let deleteUrl = '';
function openModal(id, nama, url) {
    deleteUrl = url;
    document.getElementById('modalNama').textContent = nama;
    document.getElementById('modalHapus').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('modalHapus').classList.remove('show');
    document.body.style.overflow = '';
    deleteUrl = '';
}
function submitHapus() {
    const form = document.getElementById('formHapus');
    form.action = deleteUrl;
    form.submit();
}
document.getElementById('modalHapus').addEventListener('click', e => { if(e.target===e.currentTarget)closeModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape')closeModal(); });

// === REAL-TIME SEARCH ===
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

// Juga support Enter untuk compat
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
    // Update URL tanpa reload
    const url = new URL(window.location.href);
    if (keyword) url.searchParams.set('search', keyword);
    else url.searchParams.delete('search');
    url.searchParams.delete('page'); // reset ke halaman 1

    window.history.pushState({}, '', url.toString());

    // Fetch hasil
    spinner.classList.add('show');
    tableBody.style.opacity = '0.5';

    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Update table body
            const newBody = doc.getElementById('tableBody');
            if (newBody) tableBody.innerHTML = newBody.innerHTML;

            // Update footer
            const newFooter = doc.getElementById('tableFooter');
            if (newFooter) tableFooter.innerHTML = newFooter.innerHTML;

            // Update jumlah info di header
            const newInfo = doc.getElementById('jumlahInfo');
            if (newInfo) jumlahInfo.textContent = newInfo.textContent;

            tableBody.style.opacity = '1';
            spinner.classList.remove('show');

            // Highlight keyword di hasil
            if (keyword) highlightText(tableBody, keyword);
        })
        .catch(() => {
            tableBody.style.opacity = '1';
            spinner.classList.remove('show');
        });
}

function highlightText(el, keyword) {
    const kw = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${kw})`, 'gi');
    // Hanya highlight di kolom nama dan NIK
    el.querySelectorAll('.karyawan-name, .karyawan-nik').forEach(node => {
        node.innerHTML = node.textContent.replace(regex, '<mark>$1</mark>');
    });
}

// Handle browser back/forward
window.addEventListener('popstate', () => {
    const url = new URL(window.location.href);
    const kw = url.searchParams.get('search') || '';
    searchInput.value = kw;
    clearBtn.classList.toggle('visible', kw.length > 0);
    doSearch(kw);
});
</script>
@endpush