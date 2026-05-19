@extends('layouts.app')
@section('title', 'History Pejabat')
@section('breadcrumb', 'History Pejabat')

@push('styles')
<style>
    .page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .stats-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px; }
    .stat-card { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:16px;text-align:center; }
    .stat-num { font-size:24px;font-weight:800;color:#111827; }
    .stat-label { font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px; }
    .stat-card.svp .stat-num { color:#d97706; }
    .stat-card.vp .stat-num { color:#1d4ed8; }
    .stat-card.spm .stat-num { color:#7c3aed; }
    .stat-card.pm .stat-num { color:#15803d; }

    .filter-row { display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap; }
    .search-bar { display:flex;align-items:center;gap:10px;background:white;border:1px solid #e5e7eb;border-radius:9px;padding:8px 14px;flex:1;min-width:200px; }
    .search-bar input { border:none;outline:none;font-size:13px;font-family:inherit;flex:1;color:#111827;background:transparent; }
    .search-bar svg { width:15px;height:15px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .filter-select { padding:8px 14px;border:1px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#374151;background:white;outline:none;cursor:pointer; }
    .filter-select:focus { border-color:#16a34a; }
    .btn-reset { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;text-decoration:none;white-space:nowrap; }
    .btn-reset:hover { background:#f5f5f0; }

    .section-wrap { margin-bottom:28px; }
    .section-head { display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px; }
    .section-title-label { font-size:15px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px; }
    .count-badge { padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600; }
    .badge-aktif { background:#dcfce7;color:#15803d; }
    .badge-selesai { background:#f3f4f6;color:#6b7280; }

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

    .jabatan-badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:0.5px; }
    .badge-svp { background:#fef3c7;color:#d97706; }
    .badge-vp { background:#eff6ff;color:#1d4ed8; }
    .badge-spm { background:#f5f3ff;color:#7c3aed; }
    .badge-pm { background:#f0fdf4;color:#15803d; }

    .durasi-chip { display:inline-flex;padding:2px 8px;border-radius:6px;background:#f3f4f6;font-size:11px;color:#6b7280;font-weight:600; }
    .dot-aktif { color:#15803d;font-weight:600;font-size:12px; }
    .dot-selesai { color:#9ca3af;font-weight:600;font-size:12px; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:48px 20px;color:#9ca3af; }
    .empty-state svg { width:44px;height:44px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:13px;font-weight:600;color:#6b7280;margin-bottom:4px; }

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
        <div class="page-title">History Pejabat</div>
        <div class="page-sub">Riwayat pejabat SVP, VP, SPM & PM — terintegrasi otomatis dari history jabatan</div>
    </div>
    <a href="{{ route('history_pejabat.export', request()->query()) }}"
       style="display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:14px;height:14px;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export Excel
    </a>
</div>

{{-- Stats --}}
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

{{-- Filter --}}
<form method="GET" id="filterForm">
    <div class="filter-row">
        <div class="search-bar">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama atau NIK karyawan..."
                   onchange="document.getElementById('filterForm').submit()" />
        </div>
        <select name="jabatan" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Jabatan</option>
            @foreach(['SVP','VP','SPM','PM'] as $j)
                <option value="{{ $j }}" {{ request('jabatan') == $j ? 'selected' : '' }}>{{ $j }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','jabatan']))
            <a href="{{ route('history_pejabat.index') }}" class="btn-reset">✕ Reset</a>
        @endif
    </div>
</form>

{{-- ===== PEJABAT AKTIF ===== --}}
<div class="section-wrap">
    <div class="section-head">
        <div class="section-title-label">
            🟢 Pejabat Sedang Aktif
            <span class="count-badge badge-aktif">{{ $aktif->total() }} pejabat</span>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($aktif as $h)
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">
                                    @if($h->karyawan->foto)
                                        <img src="{{ Storage::url($h->karyawan->foto) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($h->karyawan->nama, 0, 2)) }}
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <p>Tidak ada pejabat aktif</p>
                                <span style="font-size:12px;">History otomatis dibuat saat karyawan SVP/VP/SPM/PM berganti jabatan</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($aktif->hasPages())
        <div class="table-footer">
            <span>Menampilkan <strong>{{ $aktif->firstItem() }}</strong>–<strong>{{ $aktif->lastItem() }}</strong> dari <strong>{{ $aktif->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($aktif->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $aktif->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif
                @php
                    $cur = $aktif->currentPage();
                    $last = $aktif->lastPage();
                    $s = max(1, $cur - 2);
                    $e = min($last, $cur + 2);
                @endphp
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
            <span class="count-badge badge-selesai">{{ $selesai->total() }} pejabat</span>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($selesai as $h)
                    <tr>
                        <td>
                            <div class="karyawan-info">
                                <div class="karyawan-avatar">
                                    @if($h->karyawan->foto)
                                        <img src="{{ Storage::url($h->karyawan->foto) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($h->karyawan->nama, 0, 2)) }}
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13">
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
        <div class="table-footer">
            <span>Menampilkan <strong>{{ $selesai->firstItem() }}</strong>–<strong>{{ $selesai->lastItem() }}</strong> dari <strong>{{ $selesai->total() }}</strong></span>
            <div class="pagination-wrap">
                @if($selesai->onFirstPage())
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
                @else
                    <a href="{{ $selesai->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                @endif
                @php
                    $cur2 = $selesai->currentPage();
                    $last2 = $selesai->lastPage();
                    $s2 = max(1, $cur2 - 2);
                    $e2 = min($last2, $cur2 + 2);
                @endphp
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