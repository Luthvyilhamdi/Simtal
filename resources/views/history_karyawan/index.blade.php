@extends('layouts.app')
@section('title', 'History Karyawan')
@section('breadcrumb', 'History Karyawan')

@push('styles')
<style>
    .page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .search-bar { display:flex;align-items:center;gap:10px;background:white;border:1px solid #e5e7eb;border-radius:10px;padding:9px 14px;margin-bottom:16px; }
    .search-bar input { border:none;outline:none;font-size:13px;font-family:inherit;flex:1;color:#111827;background:transparent; }
    .search-bar svg { width:16px;height:16px;flex-shrink:0;stroke:#9ca3af;fill:none; }

    .table-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:560px; }
    thead th { padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:13px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:10px; }
    .karyawan-avatar { width:36px;height:36px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:13px; }
    .karyawan-nik { font-size:11px;color:#9ca3af;margin-top:1px; }

    .badge { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600; }
    .badge-green { background:#dcfce7;color:#15803d; }
    .badge-red { background:#fee2e2;color:#dc2626; }
    .badge-gray { background:#f3f4f6;color:#6b7280; }

    .history-count { display:flex;align-items:center;gap:6px; }
    .count-num { font-size:16px;font-weight:700;color:#111827; }
    .count-label { font-size:11px;color:#9ca3af; }

    .btn-view { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.12s;white-space:nowrap; }
    .btn-view:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-view svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:30px;height:30px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px;color:#9ca3af; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">History Karyawan</div>
        <div class="page-sub">Riwayat jabatan seluruh karyawan · {{ $karyawans->total() }} karyawan</div>
    </div>
    <a href="{{ route('history_karyawan.export') }}"
       style="display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:14px;height:14px;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export Excel
    </a>
</div>

<form method="GET">
    <div class="search-bar">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIK karyawan..." />
    </div>
</form>

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
            <tbody>
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
                            Lihat History
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px;">Belum ada data karyawan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>
            Menampilkan <strong>{{ $karyawans->firstItem() ?? 0 }}</strong>–<strong>{{ $karyawans->lastItem() ?? 0 }}</strong>
            dari <strong>{{ $karyawans->total() }}</strong> karyawan
        </span>

        @if($karyawans->hasPages())
        <div class="pagination-wrap">
            @if($karyawans->onFirstPage())
                <span class="page-btn disabled">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $karyawans->previousPageUrl() }}" class="page-btn">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
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
                <a href="{{ $karyawans->nextPageUrl() }}" class="page-btn">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span class="page-btn disabled">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif
        </div>
        @endif
    </div>
</div>

@endsection