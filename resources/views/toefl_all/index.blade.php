@extends('layouts.app')
@section('title', 'Nilai TOEFL')
@section('breadcrumb-parent', 'Assessment & Penilaian')
@section('breadcrumb', 'TOEFL')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

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

    .btn-export { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap; }
    .btn-export:hover { background:#166534; }
    .btn-export svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:2; }

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

    .skor-badge { display:inline-block;padding:3px 10px;border-radius:8px;font-size:13px;font-weight:800;background:#eef2ff;color:#4338ca; }
    .jenis-tag { font-size:11px;color:#6b7280;margin-left:6px; }
    .muted { color:#9ca3af; }
    .count-pill { display:inline-flex;align-items:center;gap:5px; }
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

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 4s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

<div class="page-header">
    <div>
        <div class="page-title">Nilai TOEFL</div>
        <div class="page-sub" id="jumlahInfo">{{ $karyawans->total() }} karyawan</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <div class="search-mini">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Cari nama / NIK..." value="{{ request('search') }}" autocomplete="off">
            <div class="search-spinner" id="searchSpinner"></div>
            <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearBtn" onclick="clearSearch()">×</button>
        </div>
        <a href="{{ route('toefl_all.export', ['search' => request('search')]) }}" class="btn-export">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan</th>
                    <th>Departemen</th>
                    <th>Skor Terbaru</th>
                    <th>Jumlah Tes</th>
                    <th>Lihat</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($karyawans as $k)
                @php $latest = $k->toefls->first(); @endphp
                <tr>
                    <td>
                        <div class="karyawan-info">
                            <div class="karyawan-avatar">
                                @if($k->foto)<img src="{{ Storage::url($k->foto) }}" alt="">@else{{ initials($k->nama) }}@endif
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
                        @if($latest)
                            <span class="skor-badge">{{ $latest->skor }}</span>
                            @if($latest->jenis)<span class="jenis-tag">{{ $latest->jenis }}</span>@endif
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="count-pill">
                            <span class="count-num">{{ $k->toefls_count }}</span>
                            <span class="count-label">tes</span>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('toefl.index', $k) }}" class="btn-view">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Lihat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:3px;">Belum ada data TOEFL</p>
                            <span style="font-size:12px;">Buka profil karyawan → kelola TOEFL untuk menambahkan</span>
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
            @php $cur=$karyawans->currentPage(); $last=$karyawans->lastPage(); $s=max(1,$cur-2); $e=min($last,$cur+2); @endphp
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

@endsection

@push('scripts')
<script>
function closeToast() {
    const t = document.getElementById('toast');
    if (!t) return;
    t.classList.add('hiding');
    setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
}
window.addEventListener('DOMContentLoaded', () => { if (document.getElementById('toast')) setTimeout(() => closeToast(), 4000); });

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
searchInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { clearTimeout(searchTimer); doSearch(this.value.trim()); } });
function clearSearch() { searchInput.value=''; clearBtn.classList.remove('visible'); doSearch(''); searchInput.focus(); }
function doSearch(keyword) {
    const url = new URL(window.location.href);
    if (keyword) url.searchParams.set('search', keyword); else url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.history.pushState({}, '', url.toString());
    spinner.classList.add('show'); tableBody.style.opacity = '0.5';
    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nb = doc.getElementById('tableBody'); if (nb) tableBody.innerHTML = nb.innerHTML;
            const nf = doc.getElementById('tableFooter'); if (nf) tableFooter.innerHTML = nf.innerHTML;
            const ni = doc.getElementById('jumlahInfo'); if (ni) jumlahInfo.textContent = ni.textContent;
            tableBody.style.opacity = '1'; spinner.classList.remove('show');
            if (keyword) highlightText(tableBody, keyword);
        })
        .catch(() => { tableBody.style.opacity='1'; spinner.classList.remove('show'); });
}
function highlightText(el, keyword) {
    const regex = new RegExp('(' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    el.querySelectorAll('.karyawan-name, .karyawan-nik').forEach(node => { node.innerHTML = node.textContent.replace(regex, '<mark>$1</mark>'); });
}
window.addEventListener('popstate', () => {
    const kw = new URL(window.location.href).searchParams.get('search') || '';
    searchInput.value = kw; clearBtn.classList.toggle('visible', kw.length > 0); doSearch(kw);
});
</script>
@endpush
