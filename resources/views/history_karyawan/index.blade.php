@extends('layouts.app')
@section('title', 'History Karyawan')
@section('breadcrumb', 'History Karyawan')

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

    .table-card { background:white;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:560px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:12px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:10px; }
    .karyawan-avatar { width:34px;height:34px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:13px; }
    .karyawan-nik { font-size:11px;color:#9ca3af;margin-top:1px; }

    .badge { display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600; }
    .badge-green { background:#dcfce7;color:#15803d; }
    .badge-red { background:#fee2e2;color:#dc2626; }

    .history-count { display:flex;align-items:center;gap:5px; }
    .count-num { font-size:15px;font-weight:700;color:#111827; }
    .count-label { font-size:11px;color:#9ca3af; }

    .btn-view { display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:7px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.12s;white-space:nowrap; }
    .btn-view:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-view svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

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
</style>
@endpush

@section('content')

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">History Karyawan</div>
        <div class="page-sub" id="jumlahInfo">{{ $karyawans->total() }} karyawan</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        {{-- Search kecil --}}
        <div class="search-mini">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Cari nama / NIK..." value="{{ request('search') }}" autocomplete="off">
            <div class="search-spinner" id="searchSpinner"></div>
            <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearBtn" onclick="clearSearch()">×</button>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('history_karyawan.import') }}"
           style="display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #e5e7eb;white-space:nowrap;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </a>
        @endif

        <a href="{{ route('history_karyawan.export') }}"
           style="display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
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
                    <th>Status</th>
                    <th>Total Jabatan</th>
                    <th>Lihat History</th>
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
                    <td>
                        @if($k->status === 'aktif')
                            <span class="badge badge-green">● Aktif</span>
                        @else
                            <span class="badge badge-red">● Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="history-count">
                            <span class="count-num">{{ $k->historyJabatan->count() }}</span>
                            <span class="count-label">jabatan</span>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('history_karyawan.show', $k) }}" class="btn-view">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Lihat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:3px;">Belum ada data</p>
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
                @if($s > 2)
                    <span class="page-btn disabled" style="border:none;background:transparent;width:auto;">…</span>
                @endif
            @endif

            @for($i = $s; $i <= $e; $i++)
                <a href="{{ $karyawans->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
            @endfor

            @if($e < $last)
                @if($e < $last - 1)
                    <span class="page-btn disabled" style="border:none;background:transparent;width:auto;">…</span>
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
</script>
@endpush