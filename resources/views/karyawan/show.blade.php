@extends('layouts.app')
@section('title', 'Detail Karyawan')
@section('breadcrumb-parent', 'Profil Karyawan')
@section('breadcrumb', $karyawan->nama)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .profile-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:24px;margin-bottom:20px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap; }
    .profile-left { display:flex;align-items:center;gap:18px;flex:1;min-width:0; }
    .profile-avatar { width:72px;height:72px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;overflow:hidden;border:3px solid #bbf7d0; }
    .profile-avatar img { width:100%;height:100%;object-fit:cover; }
    .profile-name { font-size:20px;font-weight:700;color:#111827;margin-bottom:3px; }
    .profile-jabatan { font-size:13px;color:#6b7280;margin-bottom:10px; }
    .profile-tags { display:flex;flex-wrap:wrap;gap:6px; }
    .profile-tag { display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151; }
    .profile-tag.green { background:#dcfce7;color:#15803d; }

    .profile-stats { display:flex;gap:28px;flex-shrink:0; }
    .stat-item { text-align:center; }
    .stat-num { font-size:24px;font-weight:700;color:#111827;line-height:1; }
    .stat-num.green { color:#15803d; }
    .stat-num.red { color:#e50909; }
    .stat-label { font-size:11px;color:#9ca3af;margin-top:4px; }

    .action-row { display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap; }
    .btn-edit { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.15s; }
    .btn-edit:hover { background:#166534; }
    .btn-edit svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }
    .btn-outline { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #e5e7eb;transition:all 0.15s; }
    .btn-outline:hover { background:#f9fafb; }
    .btn-outline svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }

    .detail-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px; }
    .detail-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:20px; }
    .detail-card.full { grid-column:1/-1; }
    .detail-card-title { font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:7px; }
    .detail-card-title svg { width:14px;height:14px;stroke:#9ca3af;fill:none;stroke-width:1.8; }
    .detail-row { display:flex;justify-content:space-between;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid #f9fafb; }
    .detail-row:last-child { border-bottom:none;padding-bottom:0; }
    .detail-label { font-size:12px;color:#9ca3af;font-weight:500;flex-shrink:0; }
    .detail-value { font-size:13px;color:#111827;font-weight:600;text-align:right;word-break:break-word; }

    .foto-section { display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:20px 0; }
    .foto-large { width:120px;height:120px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:700;overflow:hidden;border:4px solid #bbf7d0; }
    .foto-large img { width:100%;height:100%;object-fit:cover; }
    .foto-name { font-size:15px;font-weight:700;color:#111827; }
    .foto-nik { font-size:12px;color:#9ca3af; }

    .badge { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600; }
    .badge-green { background:#dcfce7;color:#15803d; }
    .badge-red { background:#fee2e2;color:#dc2626; }
    .badge-gray { background:#f3f4f6;color:#374151; }
    .badge-blue { background:#eff6ff;color:#1d4ed8; }
    .badge-purple { background:#f5f3ff;color:#7c3aed; }
    .badge-amber { background:#fffbeb;color:#d97706; }

    /* Band & MDG */
    .band-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:20px;margin-bottom:16px; }
    .mdg-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px; }
    .mdg-item { text-align:center;background:#f9fafb;border-radius:10px;padding:14px 10px; }
    .mdg-num { font-size:22px;font-weight:800;color:#111827; }
    .mdg-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-top:3px; }
    .mdg-since { font-size:10px;color:#d1d5db;margin-top:3px; }

    .status-naik { border-radius:10px;padding:14px 16px;display:flex;align-items:flex-start;gap:12px;margin-bottom:16px; }
    .syarat-wrap { display:flex;gap:8px;margin-top:10px;flex-wrap:wrap; }
    .syarat-item { display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:8px;font-size:11px; }

    .progress-wrap { display:flex;flex-direction:column;gap:10px; }
    .progress-label { display:flex;justify-content:space-between;font-size:11px;color:#6b7280;margin-bottom:4px; }
    .progress-bar { height:6px;background:#f3f4f6;border-radius:20px;overflow:hidden; }
    .progress-fill { height:100%;border-radius:20px;transition:width 0.5s; }

    @media (max-width:768px) {
        .detail-grid { grid-template-columns:1fr; }
        .detail-card.full { grid-column:1; }
        .profile-card { flex-direction:column; }
        .profile-stats { width:100%;justify-content:space-around;border-top:1px solid #f3f4f6;padding-top:16px; }
        .mdg-grid { grid-template-columns:1fr 1fr; }
    }
    @media (max-width:480px) {
        .profile-avatar { width:56px;height:56px;font-size:18px; }
        .profile-name { font-size:17px; }
        .stat-num { font-size:20px; }
        .action-row { flex-direction:column; }
        .btn-edit,.btn-outline { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')

<a href="{{ route('karyawan.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Profil Karyawan
</a>

{{-- PROFILE CARD --}}
<div class="profile-card">
    <div class="profile-left">
        <div class="profile-avatar">
            @if($karyawan->foto)
                <img src="{{ Storage::url($karyawan->foto) }}" alt="{{ $karyawan->nama }}">
            @else
                {{ strtoupper(substr($karyawan->nama, 0, 2)) }}
            @endif
        </div>
        <div>
            <div class="profile-name">{{ $karyawan->nama }}</div>
            <div class="profile-jabatan">{{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}</div>
            <div class="profile-tags">
                <span class="profile-tag green">NIK {{ $karyawan->nik }}</span>
                <span class="profile-tag">{{ $karyawan->departemen->nama_departemen ?? '-' }}</span>
                <span class="profile-tag">{{ $karyawan->band }}</span>
                <span class="profile-tag">Bergabung {{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->translatedFormat('M Y') }}</span>
                <span class="profile-tag">
                    @if($karyawan->status === 'aktif')
                        <span style="color:#15803d">● Aktif</span>
                    @else
                        <span style="color:#dc2626">● Tidak Aktif</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="profile-stats">
        <div class="stat-item">
            <div class="stat-num green">{{ (int) \Carbon\Carbon::parse($karyawan->tanggal_masuk)->diffInYears(now()) }}</div>
            <div class="stat-label">Tahun Kerja</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age }}</div>
            <div class="stat-label">Usia</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $karyawan->personGrade->person_grade ?? '-' }}</div>
            <div class="stat-label">Person Grade</div>
        </div>
        <div class="stat-item">
            <div class="stat-num red">{{ max(0, 56 - \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age) }}</div>
            <div class="stat-label">Sisa Masa Kerja</div>
        </div>
    </div>
</div>

{{-- ACTION BUTTONS --}}
<div class="action-row">
    <a href="{{ route('karyawan.edit', $karyawan) }}" class="btn-edit">
        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit Data Karyawan
    </a>
    <a href="{{ route('history_jabatan.index', $karyawan) }}" class="btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        History Jabatan
    </a>
    <a href="{{ route('history_assessment.index', $karyawan) }}" class="btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        History Assessment
    </a>
    <a href="{{ route('karyawan.index') }}" class="btn-outline">
        <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Semua Karyawan
    </a>
</div>

{{-- DETAIL GRID --}}
<div class="detail-grid">

    {{-- Foto & Identitas --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M6 21v-1a6 6 0 0 1 12 0v1"/></svg>
            Foto & Identitas
        </div>
        <div class="foto-section">
            <div class="foto-large">
                @if($karyawan->foto)
                    <img src="{{ Storage::url($karyawan->foto) }}" alt="{{ $karyawan->nama }}">
                @else
                    {{ strtoupper(substr($karyawan->nama, 0, 2)) }}
                @endif
            </div>
            <div class="foto-name">{{ $karyawan->nama }}</div>
            <div class="foto-nik">NIK {{ $karyawan->nik }}</div>
            <div>
                @if($karyawan->status === 'aktif')
                    <span class="badge badge-green">● Aktif</span>
                @else
                    <span class="badge badge-red">● Tidak Aktif</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Data Pribadi --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Data Pribadi
        </div>
        <div class="detail-row">
            <span class="detail-label">NIK</span>
            <span class="detail-value">{{ $karyawan->nik }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Nama Lengkap</span>
            <span class="detail-value">{{ $karyawan->nama }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Jenis Kelamin</span>
            <span class="detail-value">{{ $karyawan->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tempat Lahir</span>
            <span class="detail-value">{{ $karyawan->tempat_lahir }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tanggal Lahir</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->translatedFormat('d F Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Usia</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age }} tahun</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tanggal Masuk</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->translatedFormat('d F Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Lama Bekerja</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->diffForHumans(null, true) }}</span>
        </div>
    </div>

    {{-- Jabatan & Struktur --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Jabatan & Struktur
        </div>
        <div class="detail-row">
            <span class="detail-label">Jabatan</span>
            <span class="detail-value">{{ $karyawan->jabatan->nama_jabatan ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Jabatan Saat Ini</span>
            <span class="detail-value">{{ $karyawan->jabatan_saat_ini ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Direktorat</span>
            <span class="detail-value">{{ $karyawan->direktorat->nama_direktorat ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Kompartemen</span>
            <span class="detail-value">{{ $karyawan->kompartemen->nama_kompartemen ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Departemen</span>
            <span class="detail-value">{{ $karyawan->departemen->nama_departemen ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Kode Struktur</span>
            <span class="detail-value">
                <span class="badge badge-purple">{{ $karyawan->kodeStruktur->kode_struktur ?? '-' }}</span>
            </span>
        </div>
    </div>

    {{-- Grade & Band --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Grade & Band
        </div>
        <div class="detail-row">
            <span class="detail-label">Band</span>
            <span class="detail-value">
                <span class="badge badge-green">{{ $karyawan->band }}</span>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Job Grade</span>
            <span class="detail-value">
                <span class="badge badge-gray">JG {{ $karyawan->jobGrade->job_grade ?? '-' }}</span>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Person Grade</span>
            <span class="detail-value">
                <span class="badge badge-blue">PG {{ $karyawan->personGrade->person_grade ?? '-' }}</span>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">TMT Job Grade</span>
            <span class="detail-value">{{ $karyawan->tanggal_mulai_jg ? $karyawan->tanggal_mulai_jg->format('d M Y') : '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">TMT Person Grade</span>
            <span class="detail-value">{{ $karyawan->tanggal_mulai_pg ? $karyawan->tanggal_mulai_pg->format('d M Y') : '-' }}</span>
        </div>
    </div>

</div>

{{-- BAND & MDG CARD --}}
@php $sk = $karyawan->statusKenaikan; @endphp
<div class="band-card">
    <div class="detail-card-title" style="margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;">
        <svg viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        Band & Masa Dinas Grade (MDG)
    </div>

    {{-- Status Kenaikan --}}
    <div class="status-naik" style="background:{{ $sk['eligible'] ? '#f0fdf4' : '#fffbeb' }};border:1px solid {{ $sk['eligible'] ? '#bbf7d0' : '#fde68a' }};">
        <div style="font-size:28px;flex-shrink:0;">{{ $sk['eligible'] ? '✅' : '⏳' }}</div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:700;color:{{ $sk['eligible'] ? '#15803d' : '#d97706' }};">
                {{ $sk['eligible'] ? 'ELIGIBLE — ' : 'Belum Eligible — ' }}{{ $sk['label'] }}
            </div>
            @if(!$sk['eligible'] && $sk['sisa_bulan'] > 0)
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">
                    Sisa <strong>{{ $sk['sisa_bulan'] }} bulan</strong> lagi untuk syarat terlama
                </div>
            @endif

            {{-- Checklist Syarat --}}
            @if(!empty($sk['syarat']))
            <div class="syarat-wrap">
                @foreach($sk['syarat'] as $syarat)
                <div class="syarat-item"
                     style="background:{{ $syarat['terpenuhi'] ? '#f0fdf4' : '#fef2f2' }};
                            border:1px solid {{ $syarat['terpenuhi'] ? '#bbf7d0' : '#fecaca' }};">
                    <span>{{ $syarat['terpenuhi'] ? '✅' : '❌' }}</span>
                    <span style="font-weight:600;color:{{ $syarat['terpenuhi'] ? '#15803d' : '#dc2626' }};">
                        {{ $syarat['label'] }}
                    </span>
                    <span style="color:#6b7280;">({{ $syarat['mdg'] }}/{{ $syarat['min'] }} bln)</span>
                </div>
                @endforeach
            </div>
            @endif

            @if(!empty($sk['blokir_info']))
                <div style="font-size:12px;color:#dc2626;margin-top:6px;">
                    🔒 {{ $sk['blokir_info'] }}
                </div>
            @endif
        </div>
    </div>

    {{-- MDG Grid --}}
    <div class="mdg-grid">
        <div class="mdg-item">
            <div class="mdg-label">MDG Job Grade</div>
            <div class="mdg-num" style="color:{{ ($karyawan->mdg_jg ?? 0) >= 2 ? '#15803d' : '#d97706' }};">
                {{ $karyawan->mdg_jg ?? 0 }}<span style="font-size:12px;font-weight:500;"> thn</span>
            </div>
            @if($karyawan->tanggal_mulai_jg)
                <div class="mdg-since">sejak {{ $karyawan->tanggal_mulai_jg->format('d M Y') }}</div>
            @endif
            <div class="mdg-since" style="color:#6b7280;">min 2 tahun</div>
        </div>
        <div class="mdg-item">
            <div class="mdg-label">MDG Person Grade</div>
            <div class="mdg-num" style="color:{{ ($karyawan->mdg_pg ?? 0) >= 1 ? '#15803d' : '#d97706' }};">
                {{ $karyawan->mdg_pg ?? 0 }}<span style="font-size:12px;font-weight:500;"> thn</span>
            </div>
            @if($karyawan->tanggal_mulai_pg)
                <div class="mdg-since">sejak {{ $karyawan->tanggal_mulai_pg->format('d M Y') }}</div>
            @endif
            <div class="mdg-since" style="color:#6b7280;">min 1 tahun</div>
        </div>
        <div class="mdg-item">
            <div class="mdg-label">MDG Band</div>
            <div class="mdg-num" style="color:{{ $karyawan->mdg_band >= 3 ? '#15803d' : '#d97706' }};">
                {{ $karyawan->mdg_band }}<span style="font-size:12px;font-weight:500;"> thn</span>
            </div>
            @if($karyawan->tanggal_mulai_jg)
                <div class="mdg-since">sejak {{ $karyawan->tanggal_mulai_jg->format('d M Y') }}</div>
            @endif
            <div class="mdg-since" style="color:#6b7280;">min 3 tahun</div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="progress-wrap">
        @php $pgPct   = min(100, ($karyawan->mdg_pg_bulan   / 12) * 100); @endphp
        @php $jgPct   = min(100, ($karyawan->mdg_jg_bulan   / 24) * 100); @endphp
        @php $bandPct = min(100, ($karyawan->mdg_band_bulan / 36) * 100); @endphp

        <div class="progress-item">
            <div class="progress-label">
                <span>MDG Person Grade</span>
                <span>{{ $karyawan->mdg_pg_bulan }} / 12 bulan</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $pgPct }}%;background:{{ $pgPct >= 100 ? '#15803d' : '#f59e0b' }};"></div>
            </div>
        </div>
        <div class="progress-item">
            <div class="progress-label">
                <span>MDG Job Grade</span>
                <span>{{ $karyawan->mdg_jg_bulan }} / 24 bulan</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $jgPct }}%;background:{{ $jgPct >= 100 ? '#15803d' : '#3b82f6' }};"></div>
            </div>
        </div>
        <div class="progress-item">
            <div class="progress-label">
                <span>MDG Band (dari TMT JG)</span>
                <span>{{ $karyawan->mdg_band_bulan }} / 36 bulan</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $bandPct }}%;background:{{ $bandPct >= 100 ? '#15803d' : '#7c3aed' }};"></div>
            </div>
        </div>
    </div>
</div>

@endsection