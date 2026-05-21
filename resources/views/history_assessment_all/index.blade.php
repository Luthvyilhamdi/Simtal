@extends('layouts.app')
@section('title', 'History Assessment')
@section('breadcrumb', 'History Assessment')

@push('styles')
<style>
    .page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .stats-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px; }
    .stat-card { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:16px;text-align:center;transition:box-shadow 0.15s; }
    .stat-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.06); }
    .stat-num { font-size:26px;font-weight:800;color:#111827; }
    .stat-label { font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px; }
    .stat-card.ready .stat-num { color:#15803d; }
    .stat-card.rwd .stat-num { color:#d97706; }
    .stat-card.nr .stat-num { color:#dc2626; }
    .stat-card.expire .stat-num { color:#ef4444; }

    .filter-row { display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap; }
    .search-bar { display:flex;align-items:center;gap:10px;background:white;border:1px solid #e5e7eb;border-radius:9px;padding:9px 14px;flex:1;min-width:200px; }
    .search-bar input { border:none;outline:none;font-size:13px;font-family:inherit;flex:1;color:#111827;background:transparent; }
    .search-bar svg { width:15px;height:15px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .filter-select { padding:9px 14px;border:1px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#374151;background:white;outline:none;cursor:pointer; }
    .filter-select:focus { border-color:#16a34a; }
    .btn-reset { display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:9px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;text-decoration:none;white-space:nowrap; }
    .btn-reset:hover { background:#f5f5f0; }

    .table-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:900px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:12px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle;white-space:nowrap; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:10px; }
    .karyawan-avatar { width:34px;height:34px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:13px; }
    .karyawan-nik { font-size:11px;color:#9ca3af; }

    .badge-final { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
    .final-ready { background:#dcfce7;color:#15803d; }
    .final-rwd { background:#fef3c7;color:#d97706; }
    .final-nr { background:#fee2e2;color:#dc2626; }
    .final-none { background:#f3f4f6;color:#6b7280; }

    .idp-badge { display:inline-flex;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600; }
    .idp-expired { background:#fee2e2;color:#dc2626; }
    .idp-aktif { background:#dcfce7;color:#15803d; }

    .rekom-bar { display:flex;align-items:center;gap:6px;min-width:100px; }
    .rekom-track { flex:1;height:5px;background:#f3f4f6;border-radius:20px;overflow:hidden; }
    .rekom-fill { height:100%;border-radius:20px; }
    .rekom-pct { font-size:11px;font-weight:700;color:#374151;min-width:32px; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px;color:#9ca3af; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    @media (max-width:768px) {
        .stats-grid { grid-template-columns:repeat(3,1fr); }
        .filter-row { flex-direction:column; }
    }
    @media (max-width:480px) {
        .stats-grid { grid-template-columns:repeat(2,1fr); }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <div class="page-title">History Assessment</div>
        <div class="page-sub">Riwayat assessment seluruh karyawan</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        {{-- Import — Super Admin Only --}}
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('history_assessment_all.import') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #e5e7eb;white-space:nowrap;transition:all 0.15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Import Excel
        </a>
        @endif
        {{-- Export --}}
        <a href="{{ route('history_assessment_all.export', request()->query()) }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#7c3aed;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:14px;height:14px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export Excel
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-num">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Assessment</div>
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
    <div class="stat-card expire">
        <div class="stat-num">{{ $stats['expire'] }}</div>
        <div class="stat-label">IDP Expired</div>
    </div>
</div>

{{-- Filter --}}
<form method="GET" id="filterForm">
    <div class="filter-row">
        <div class="search-bar">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama atau NIK karyawan..."
                   onchange="document.getElementById('filterForm').submit()" />
        </div>
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
            <a href="{{ route('history_assessment_all.index') }}" class="btn-reset">✕ Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan Saat Ini</th>
                    <th>Job Grade</th>
                    <th>Person Grade</th>
                    <th>Tgl Pelaksanaan</th>
                    <th>Rek. Inti</th>
                    <th>Rek. Primer</th>
                    <th>Rek. Sekunder</th>
                    <th>Rekomendasi Final</th>
                    <th>Tgl Exp IDP</th>
                    <th>Status IDP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $a)
                <tr>
                    <td>
                        <div class="karyawan-info">
                            <div class="karyawan-avatar">
                                @if($a->karyawan->foto)
                                    <img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                                @else
                                    {{ strtoupper(substr($a->karyawan->nama, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="karyawan-name">{{ $a->karyawan->nama }}</div>
                                <div class="karyawan-nik">NIK {{ $a->karyawan->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;">{{ $a->jabatan_saat_ini ?? '-' }}</td>
                    <td>{{ $a->job_grade ?? '-' }}</td>
                    <td>{{ $a->person_grade ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}</td>

                    {{-- Rekomendasi Inti --}}
                    <td>
                        @if($a->rekomendasi_inti)
                        <div class="rekom-bar">
                            <div class="rekom-track">
                                <div class="rekom-fill" style="width:{{ $a->rekomendasi_inti }}%;background:#15803d;"></div>
                            </div>
                            <span class="rekom-pct">{{ $a->rekomendasi_inti }}%</span>
                        </div>
                        @else - @endif
                    </td>

                    {{-- Rekomendasi Primer --}}
                    <td>
                        @if($a->rekomendasi_primer)
                        <div class="rekom-bar">
                            <div class="rekom-track">
                                <div class="rekom-fill" style="width:{{ $a->rekomendasi_primer }}%;background:#3b82f6;"></div>
                            </div>
                            <span class="rekom-pct">{{ $a->rekomendasi_primer }}%</span>
                        </div>
                        @else - @endif
                    </td>

                    {{-- Rekomendasi Sekunder --}}
                    <td>
                        @if($a->rekomendasi_skunder)
                        <div class="rekom-bar">
                            <div class="rekom-track">
                                <div class="rekom-fill" style="width:{{ $a->rekomendasi_skunder }}%;background:#f59e0b;"></div>
                            </div>
                            <span class="rekom-pct">{{ $a->rekomendasi_skunder }}%</span>
                        </div>
                        @else - @endif
                    </td>

                    {{-- Rekomendasi Final --}}
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

                    {{-- Tgl Exp IDP --}}
                    <td>
                        @if($a->tanggal_exp_idp)
                            <span style="color:{{ \Carbon\Carbon::parse($a->tanggal_exp_idp)->isPast() ? '#ef4444' : '#374151' }};font-weight:{{ \Carbon\Carbon::parse($a->tanggal_exp_idp)->isPast() ? '700' : '400' }};">
                                {{ \Carbon\Carbon::parse($a->tanggal_exp_idp)->format('d M Y') }}
                            </span>
                        @else - @endif
                    </td>

                    {{-- Status IDP --}}
                    <td>
                        @if($a->tanggal_exp_idp)
                            @if(\Carbon\Carbon::parse($a->tanggal_exp_idp)->isPast())
                                <span class="idp-badge idp-expired">⚠ Expired</span>
                            @else
                                <span class="idp-badge idp-aktif">✓ Aktif</span>
                            @endif
                        @else
                            <span style="color:#9ca3af;font-size:12px;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px;">Belum ada data assessment</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($assessments->hasPages())
    <div class="table-footer">
        <span>Menampilkan <strong>{{ $assessments->firstItem() }}</strong>–<strong>{{ $assessments->lastItem() }}</strong> dari <strong>{{ $assessments->total() }}</strong></span>
        <div class="pagination-wrap">
            @if($assessments->onFirstPage())
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
            @else
                <a href="{{ $assessments->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
            @endif
            @php $cur=$assessments->currentPage();$last=$assessments->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s>1)<a href="{{ $assessments->url(1) }}" class="page-btn">1</a>@if($s>2)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif@endif
            @for($i=$s;$i<=$e;$i++)<a href="{{ $assessments->url($i) }}" class="page-btn {{ $i==$cur?'active':'' }}">{{ $i }}</a>@endfor
            @if($e<$last)@if($e<$last-1)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif<a href="{{ $assessments->url($last) }}" class="page-btn">{{ $last }}</a>@endif
            @if($assessments->hasMorePages())
                <a href="{{ $assessments->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
            @else
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            @endif
        </div>
    </div>
    @endif
</div>

@endif
@endsection