@extends('layouts.app')
@section('title', 'History Pejabat')
@section('breadcrumb', 'History Pejabat')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

    .stats-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:18px; }
    .stat-card { background:white;border-radius:10px;border:1px solid #e5e7eb;padding:14px;text-align:center; }
    .stat-num { font-size:22px;font-weight:800;color:#111827; }
    .stat-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:3px; }
    .stat-card.svp .stat-num { color:#d97706; }
    .stat-card.vp .stat-num { color:#1d4ed8; }
    .stat-card.spm .stat-num { color:#7c3aed; }
    .stat-card.pm .stat-num { color:#15803d; }

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

    .filter-row { display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center; }
    .filter-select { padding:7px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:12px;font-family:inherit;color:#374151;background:white;outline:none;cursor:pointer; }
    .btn-reset { display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:12px;font-weight:500;cursor:pointer;text-decoration:none;white-space:nowrap; }
    .btn-reset:hover { background:#f5f5f0; }

    .section-wrap { margin-bottom:24px; }
    .section-head { display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px; }
    .section-title-label { font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px; }
    .count-badge { padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600; }
    .badge-aktif { background:#dcfce7;color:#15803d; }
    .badge-selesai { background:#f3f4f6;color:#6b7280; }

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

    .jabatan-badge { display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:0.5px; }
    .badge-svp { background:#fef3c7;color:#d97706; }
    .badge-vp { background:#eff6ff;color:#1d4ed8; }
    .badge-spm { background:#f5f3ff;color:#7c3aed; }
    .badge-pm { background:#f0fdf4;color:#15803d; }

    .durasi-chip { display:inline-flex;padding:2px 7px;border-radius:5px;background:#f3f4f6;font-size:11px;color:#6b7280;font-weight:600; }

    /* Tombol hapus (super admin) */
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

    .empty-state { text-align:center;padding:44px 20px;color:#9ca3af; }
    .empty-state svg { width:38px;height:38px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:13px;font-weight:600;color:#6b7280;margin-bottom:3px; }

    mark { background:#fef08a;border-radius:2px;padding:0 1px;color:inherit;font-weight:600; }

    /* Toast */
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

    /* Modal hapus */
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

    @media (max-width:768px) { .stats-grid { grid-template-columns:repeat(3,1fr); } }
    @media (max-width:480px) { .stats-grid { grid-template-columns:repeat(2,1fr); } }
</style>
@endpush

@section('content')

@php $isSA = auth()->user()->isSuperAdmin(); @endphp

{{-- TOAST --}}
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

{{-- MODAL HAPUS (super admin) --}}
@if($isSA)
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus History Pejabat?</div>
        <div class="modal-desc" id="modalDesc">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>
@endif

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">History Pejabat</div>
        <div class="page-sub">Riwayat pejabat SVP, VP, SPM & PM — terintegrasi otomatis dari history jabatan</div>
    </div>
    <a href="{{ route('history_pejabat.export', request()->query()) }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:13px;height:13px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export
    </a>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-num">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Aktif</div>
    </div>
    <div class="stat-card svp">
        <div class="stat-num">{{ $stats['svp'] }}</div>
        <div class="stat-label">SVP Aktif</div>
    </div>
    <div class="stat-card vp">
        <div class="stat-num">{{ $stats['vp'] }}</div>
        <div class="stat-label">VP Aktif</div>
    </div>
    <div class="stat-card spm">
        <div class="stat-num">{{ $stats['spm'] }}</div>
        <div class="stat-label">SPM Aktif</div>
    </div>
    <div class="stat-card pm">
        <div class="stat-num">{{ $stats['pm'] }}</div>
        <div class="stat-label">PM Aktif</div>
    </div>
</div>

{{-- FILTER --}}
<div class="filter-row">
    <div class="search-mini">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Cari nama / NIK..." value="{{ request('search') }}" autocomplete="off">
        <div class="search-spinner" id="searchSpinner"></div>
        <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearBtn" onclick="clearSearch()">×</button>
    </div>
    <form method="GET" id="filterForm" style="display:contents">
        <input type="hidden" name="search" id="hiddenSearch" value="{{ request('search') }}">
        <select name="jabatan" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Jabatan</option>
            @foreach(['SVP','VP','SPM','PM'] as $j)
                <option value="{{ $j }}" {{ request('jabatan') == $j ? 'selected' : '' }}>{{ $j }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','jabatan']))
            <a href="{{ route('history_pejabat.index') }}" class="btn-reset">× Reset</a>
        @endif
    </form>
</div>

{{-- ===== PEJABAT AKTIF ===== --}}
<div class="section-wrap">
    <div class="section-head">
        <div class="section-title-label">
            🟢 Pejabat Sedang Aktif
            <span class="count-badge badge-aktif" id="countAktif">{{ $aktif->total() }} pejabat</span>
        </div>
    </div>
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jabatan</th>
                        <th>Jabatan Lengkap</th>
                        <th>Direktorat</th>
                        <th>Kompartemen</th>
                        <th>Departemen</th>
                        <th>Job Grade</th>
                        <th>Person Grade</th>
                        <th>No. SK</th>
                        <th>Tgl SK</th>
                        <th>Tgl Mulai</th>
                        <th>Lama Menjabat</th>
                        @if($isSA)<th>Aksi</th>@endif
                    </tr>
                </thead>
                <tbody id="tableBodyAktif">
                    @forelse($aktif as $h)
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">
                                    @if($h->karyawan->foto)
                                        <img src="{{ Storage::url($h->karyawan->foto) }}" alt="">
                                    @else
                                        {{ initials($h->karyawan->nama) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="karyawan-name">{{ $h->karyawan->nama }}</div>
                                    <div class="karyawan-nik">NIK {{ $h->karyawan->nik }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="jabatan-badge badge-{{ strtolower($h->jabatan) }}">{{ $h->jabatan }}</span></td>
                        <td>{{ $h->jabatan_saat_ini ?? '-' }}</td>
                        <td>{{ $h->direktorat ?? '-' }}</td>
                        <td>{{ $h->kompartemen ?? '-' }}</td>
                        <td>{{ $h->departemen ?? '-' }}</td>
                        <td>{{ $h->job_grade ?? '-' }}</td>
                        <td>{{ $h->person_grade ?? '-' }}</td>
                        <td>{{ $h->no_sk ?? '-' }}</td>
                        <td>{{ $h->tanggal_sk ? $h->tanggal_sk->format('d M Y') : '-' }}</td>
                        <td>{{ $h->tanggal_mulai->format('d M Y') }}</td>
                        <td><span class="durasi-chip">{{ $h->durasi }}</span></td>
                        @if($isSA)
                        <td>
                            <button type="button" class="btn-del"
                                data-url="{{ route('history_pejabat.destroy', $h) }}"
                                data-nama="{{ $h->karyawan->nama }}"
                                data-jab="{{ $h->jabatan }}"
                                onclick="openModal(this.dataset.url, this.dataset.nama, this.dataset.jab)">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isSA ? 13 : 12 }}">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <p>Tidak ada pejabat aktif</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($aktif->hasPages())
        <div class="table-footer" id="footerAktif">
            <span>Menampilkan <strong>{{ $aktif->firstItem() }}</strong>–<strong>{{ $aktif->lastItem() }}</strong> dari <strong>{{ $aktif->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($aktif->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $aktif->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif
                @php $cur=$aktif->currentPage();$last=$aktif->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                @if($s > 1)
                    <a href="{{ $aktif->url(1) }}" class="page-btn">1</a>
                    @if($s > 2)
                        <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                    @endif
                @endif
                @for($i = $s; $i <= $e; $i++)
                    <a href="{{ $aktif->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                @if($e < $last)
                    @if($e < $last - 1)
                        <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                    @endif
                    <a href="{{ $aktif->url($last) }}" class="page-btn">{{ $last }}</a>
                @endif
                @if($aktif->hasMorePages())
                    <a href="{{ $aktif->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
                @else
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ===== PEJABAT SELESAI ===== --}}
<div class="section-wrap">
    <div class="section-head">
        <div class="section-title-label">
            ⬜ Pejabat Sudah Selesai
            <span class="count-badge badge-selesai" id="countSelesai">{{ $selesai->total() }} pejabat</span>
        </div>
    </div>
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jabatan</th>
                        <th>Jabatan Lengkap</th>
                        <th>Direktorat</th>
                        <th>Kompartemen</th>
                        <th>Departemen</th>
                        <th>Job Grade</th>
                        <th>Person Grade</th>
                        <th>No. SK</th>
                        <th>Tgl SK</th>
                        <th>Tgl Mulai</th>
                        <th>Tgl Selesai</th>
                        <th>Durasi</th>
                        @if($isSA)<th>Aksi</th>@endif
                    </tr>
                </thead>
                <tbody id="tableBodySelesai">
                    @forelse($selesai as $h)
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">
                                    @if($h->karyawan->foto)
                                        <img src="{{ Storage::url($h->karyawan->foto) }}" alt="">
                                    @else
                                        {{ initials($h->karyawan->nama) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="karyawan-name">{{ $h->karyawan->nama }}</div>
                                    <div class="karyawan-nik">NIK {{ $h->karyawan->nik }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="jabatan-badge badge-{{ strtolower($h->jabatan) }}">{{ $h->jabatan }}</span></td>
                        <td>{{ $h->jabatan_saat_ini ?? '-' }}</td>
                        <td>{{ $h->direktorat ?? '-' }}</td>
                        <td>{{ $h->kompartemen ?? '-' }}</td>
                        <td>{{ $h->departemen ?? '-' }}</td>
                        <td>{{ $h->job_grade ?? '-' }}</td>
                        <td>{{ $h->person_grade ?? '-' }}</td>
                        <td>{{ $h->no_sk ?? '-' }}</td>
                        <td>{{ $h->tanggal_sk ? $h->tanggal_sk->format('d M Y') : '-' }}</td>
                        <td>{{ $h->tanggal_mulai->format('d M Y') }}</td>
                        <td>{{ $h->tanggal_selesai->format('d M Y') }}</td>
                        <td><span class="durasi-chip">{{ $h->durasi }}</span></td>
                        @if($isSA)
                        <td>
                            <button type="button" class="btn-del"
                                data-url="{{ route('history_pejabat.destroy', $h) }}"
                                data-nama="{{ $h->karyawan->nama }}"
                                data-jab="{{ $h->jabatan }}"
                                onclick="openModal(this.dataset.url, this.dataset.nama, this.dataset.jab)">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isSA ? 14 : 13 }}">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <p>Belum ada history pejabat yang selesai</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($selesai->hasPages())
        <div class="table-footer" id="footerSelesai">
            <span>Menampilkan <strong>{{ $selesai->firstItem() }}</strong>–<strong>{{ $selesai->lastItem() }}</strong> dari <strong>{{ $selesai->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($selesai->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $selesai->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif
                @php $cur2=$selesai->currentPage();$last2=$selesai->lastPage();$s2=max(1,$cur2-2);$e2=min($last2,$cur2+2); @endphp
                @if($s2 > 1)
                    <a href="{{ $selesai->url(1) }}" class="page-btn">1</a>
                    @if($s2 > 2)
                        <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                    @endif
                @endif
                @for($i = $s2; $i <= $e2; $i++)
                    <a href="{{ $selesai->url($i) }}" class="page-btn {{ $i == $cur2 ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                @if($e2 < $last2)
                    @if($e2 < $last2 - 1)
                        <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                    @endif
                    <a href="{{ $selesai->url($last2) }}" class="page-btn">{{ $last2 }}</a>
                @endif
                @if($selesai->hasMorePages())
                    <a href="{{ $selesai->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
                @else
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
@if($isSA)
// ===== MODAL HAPUS =====
var deleteUrl = '';
function openModal(url, nama, jab) {
    deleteUrl = url;
    document.getElementById('modalDesc').innerHTML =
        'Hapus history pejabat <strong>' + jab + '</strong> untuk <strong>' + nama + '</strong>?<br>Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('modalHapus').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('modalHapus').classList.remove('show');
    document.body.style.overflow = '';
}
function submitHapus() {
    document.getElementById('formHapus').action = deleteUrl;
    document.getElementById('formHapus').submit();
}
document.getElementById('modalHapus').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeModal(); });
@endif

// ===== TOAST =====
function closeToast() {
    var t = document.getElementById('toast'); if(!t) return;
    t.classList.add('hiding');
    setTimeout(function(){ var w=document.getElementById('toastWrap'); if(w) w.remove(); }, 300);
}
window.addEventListener('DOMContentLoaded', function(){
    if (document.getElementById('toast')) setTimeout(closeToast, 3000);
});

// ===== SEARCH =====
var searchTimer = null;
var searchInput = document.getElementById('searchInput');
var clearBtn    = document.getElementById('clearBtn');
var spinner     = document.getElementById('searchSpinner');
var bodyAktif   = document.getElementById('tableBodyAktif');
var bodySelesai = document.getElementById('tableBodySelesai');

searchInput.addEventListener('input', function() {
    var val = this.value.trim();
    clearBtn.classList.toggle('visible', val.length > 0);
    document.getElementById('hiddenSearch').value = val;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() { doSearch(val); }, 300);
});

searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { clearTimeout(searchTimer); doSearch(this.value.trim()); }
});

function clearSearch() {
    searchInput.value = '';
    clearBtn.classList.remove('visible');
    document.getElementById('hiddenSearch').value = '';
    doSearch('');
    searchInput.focus();
}

function doSearch(keyword) {
    var url = new URL(window.location.href);
    if (keyword) url.searchParams.set('search', keyword);
    else url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.history.pushState({}, '', url.toString());

    spinner.classList.add('show');
    bodyAktif.style.opacity = '0.5';
    bodySelesai.style.opacity = '0.5';

    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');

            var nb1 = doc.getElementById('tableBodyAktif');
            if (nb1) bodyAktif.innerHTML = nb1.innerHTML;

            var nb2 = doc.getElementById('tableBodySelesai');
            if (nb2) bodySelesai.innerHTML = nb2.innerHTML;

            var f1 = doc.getElementById('footerAktif');
            if (f1 && document.getElementById('footerAktif')) document.getElementById('footerAktif').innerHTML = f1.innerHTML;

            var f2 = doc.getElementById('footerSelesai');
            if (f2 && document.getElementById('footerSelesai')) document.getElementById('footerSelesai').innerHTML = f2.innerHTML;

            var c1 = doc.getElementById('countAktif');
            if (c1) document.getElementById('countAktif').textContent = c1.textContent;

            var c2 = doc.getElementById('countSelesai');
            if (c2) document.getElementById('countSelesai').textContent = c2.textContent;

            bodyAktif.style.opacity = '1';
            bodySelesai.style.opacity = '1';
            spinner.classList.remove('show');

            if (keyword) {
                highlightText(bodyAktif, keyword);
                highlightText(bodySelesai, keyword);
            }
        })
        .catch(function() {
            bodyAktif.style.opacity = '1';
            bodySelesai.style.opacity = '1';
            spinner.classList.remove('show');
        });
}

function highlightText(el, keyword) {
    var regex = new RegExp('(' + keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    el.querySelectorAll('.karyawan-name, .karyawan-nik').forEach(function(node) {
        node.innerHTML = node.textContent.replace(regex, '<mark>$1</mark>');
    });
}

window.addEventListener('popstate', function() {
    var kw = new URL(window.location.href).searchParams.get('search') || '';
    searchInput.value = kw;
    clearBtn.classList.toggle('visible', kw.length > 0);
    doSearch(kw);
});
</script>
@endpush