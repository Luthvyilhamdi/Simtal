@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Beranda')

@push('styles')
<style>
    .welcome-card {
        background: linear-gradient(135deg, #14532d 0%, #166534 60%, #15803d 100%);
        border-radius: 16px; padding: 28px 32px;
        margin-bottom: 24px; color: white;
        display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 16px;
        position: relative; overflow: hidden;
    }
    .welcome-card::before {
        content: ''; position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        border-radius: 50%; background: rgba(255,255,255,0.05);
    }
    .welcome-card::after {
        content: ''; position: absolute;
        bottom: -30px; right: 120px;
        width: 120px; height: 120px;
        border-radius: 50%; background: rgba(255,255,255,0.05);
    }
    .welcome-title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .welcome-sub { font-size: 13px; color: rgba(255,255,255,0.7); }
    .welcome-date { font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 600;
        background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 20px; }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
    .stat-card {
        background: white; border-radius: 14px; border: 1px solid #e5e7eb;
        padding: 18px 20px; display: flex; align-items: center; gap: 14px;
        transition: box-shadow 0.15s;
    }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
    .stat-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 20px;
    }
    .stat-icon.green { background: #dcfce7; }
    .stat-icon.blue { background: #dbeafe; }
    .stat-icon.purple { background: #f5f3ff; }
    .stat-icon.orange { background: #fef3c7; }
    .stat-icon.red { background: #fee2e2; }
    .stat-num { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; }
    .stat-label { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .stat-badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; margin-top: 4px; display: inline-block; }
    .badge-up { background: #dcfce7; color: #15803d; }
    .badge-down { background: #fee2e2; color: #dc2626; }

    /* Section */
    .section-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
    .section-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px; }
    .card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; }
    .card-title { font-size: 14px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; }
    .card-body { padding: 16px 20px; }
    .view-all { font-size: 12px; color: #16a34a; text-decoration: none; font-weight: 600; }
    .view-all:hover { text-decoration: underline; }

    /* Mini stats dalam card */
    .mini-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .mini-stat { background: #f9fafb; border-radius: 10px; padding: 12px; text-align: center; }
    .mini-stat-num { font-size: 20px; font-weight: 800; color: #111827; }
    .mini-stat-label { font-size: 11px; color: #9ca3af; font-weight: 600; margin-top: 2px; }

    /* Pejabat grid */
    .pejabat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .pejabat-item { background: #f9fafb; border-radius: 10px; padding: 12px; text-align: center; border: 1px solid #f3f4f6; }
    .pejabat-num { font-size: 22px; font-weight: 800; }
    .pejabat-label { font-size: 11px; font-weight: 700; letter-spacing: 0.5px; margin-top: 2px; }
    .pejabat-item.svp .pejabat-num { color: #d97706; }
    .pejabat-item.svp .pejabat-label { color: #d97706; }
    .pejabat-item.vp .pejabat-num { color: #1d4ed8; }
    .pejabat-item.vp .pejabat-label { color: #1d4ed8; }
    .pejabat-item.spm .pejabat-num { color: #7c3aed; }
    .pejabat-item.spm .pejabat-label { color: #7c3aed; }
    .pejabat-item.pm .pejabat-num { color: #15803d; }
    .pejabat-item.pm .pejabat-label { color: #15803d; }

    /* Assessment bar */
    .assessment-bar { margin-bottom: 14px; }
    .abar-label { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; }
    .abar-label span { color: #6b7280; font-weight: 500; }
    .abar-label strong { color: #111827; }
    .abar { height: 7px; background: #f3f4f6; border-radius: 20px; overflow: hidden; }
    .abar-fill { height: 100%; border-radius: 20px; }

    /* List items */
    .list-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f9fafb; }
    .list-item:last-child { border-bottom: none; padding-bottom: 0; }
    .list-avatar { width: 34px; height: 34px; border-radius: 50%; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; overflow: hidden; }
    .list-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .list-name { font-size: 13px; font-weight: 600; color: #111827; }
    .list-sub { font-size: 11px; color: #9ca3af; margin-top: 1px; }
    .list-right { margin-left: auto; text-align: right; flex-shrink: 0; }
    .list-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }

    /* Tipe badge */
    .tipe-promosi { background: #dcfce7; color: #15803d; }
    .tipe-mutasi { background: #dbeafe; color: #1d4ed8; }
    .tipe-demosi { background: #fee2e2; color: #dc2626; }
    .tipe-onboarding { background: #fef3c7; color: #d97706; }

    /* Pensiun warning */
    .pensiun-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f9fafb; }
    .pensiun-item:last-child { border-bottom: none; }
    .sisa-tahun { font-size: 18px; font-weight: 800; min-width: 36px; text-align: center; }
    .sisa-tahun.danger { color: #ef4444; }
    .sisa-tahun.warning { color: #f59e0b; }
    .sisa-tahun.ok { color: #6b7280; }

    /* Expire assessment */
    .expire-item { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #f9fafb; }
    .expire-item:last-child { border-bottom: none; }
    .expire-days { font-size: 16px; font-weight: 800; color: #ef4444; min-width: 32px; }

    @media (max-width: 1024px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .section-grid { grid-template-columns: 1fr; }
        .section-grid-3 { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .pejabat-grid { grid-template-columns: repeat(2, 1fr); }
        .mini-stats { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')

{{-- Welcome Card --}}
<div class="welcome-card">
    <div style="position:relative;z-index:1;">
        <div class="welcome-title">Selamat Datang, {{ auth()->user()->name }}! 👋</div>
        <div class="welcome-sub">Berikut ringkasan data SIMTAL hari ini</div>
    </div>
    <div class="welcome-date" style="position:relative;z-index:1;">
        📅 {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
    </div>
</div>

{{-- Stats Utama --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">👥</div>
        <div>
            <div class="stat-num">{{ $totalKaryawan }}</div>
            <div class="stat-label">Total Karyawan</div>
            @if($karyawanBaru > 0)
                <span class="stat-badge badge-up">+{{ $karyawanBaru }} bulan ini</span>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">📋</div>
        <div>
            <div class="stat-num">{{ $totalHistoryJabatan }}</div>
            <div class="stat-label">Total Mutasi/Promosi</div>
            <span class="stat-badge badge-up">{{ $promosiThisYear + $mutasiThisYear }} tahun ini</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">📊</div>
        <div>
            <div class="stat-num">{{ $totalAssessment }}</div>
            <div class="stat-label">Total Assessment</div>
            <span class="stat-badge badge-up">{{ $assessmentReady }} ready</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">⭐</div>
        <div>
            <div class="stat-num">{{ $pejabatAktif }}</div>
            <div class="stat-label">Pejabat Aktif</div>
            <span class="stat-badge" style="background:#fef3c7;color:#d97706;">SVP/VP/SPM/PM</span>
        </div>
    </div>
</div>

{{-- Row 1 --}}
<div class="section-grid">

    {{-- Karyawan --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">👥 Status Karyawan</div>
            <a href="{{ route('karyawan.index') }}" class="view-all">Lihat Semua →</a>
        </div>
        <div class="card-body">
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#15803d;">{{ $karyawanAktif }}</div>
                    <div class="mini-stat-label">Aktif</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#ef4444;">{{ $karyawanTidakAktif }}</div>
                    <div class="mini-stat-label">Tidak Aktif</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#3b82f6;">{{ $karyawanBaru }}</div>
                    <div class="mini-stat-label">Masuk Bulan Ini</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#f59e0b;">{{ $akanPensiun->count() }}</div>
                    <div class="mini-stat-label">Akan Pensiun</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pergerakan Jabatan --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📈 Pergerakan Jabatan {{ now()->year }}</div>
            <a href="{{ route('history_pejabat.index') }}" class="view-all">Lihat →</a>
        </div>
        <div class="card-body">
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#15803d;">{{ $promosiThisYear }}</div>
                    <div class="mini-stat-label">Promosi ↑</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#3b82f6;">{{ $mutasiThisYear }}</div>
                    <div class="mini-stat-label">Mutasi ↔</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#ef4444;">{{ $demosiThisYear }}</div>
                    <div class="mini-stat-label">Demosi ↓</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-num" style="color:#6b7280;">{{ $pgsAktif + $pjsAktif }}</div>
                    <div class="mini-stat-label">PGS/PJS Aktif</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row 2 --}}
<div class="section-grid-3">

    {{-- Assessment --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Assessment</div>
        </div>
        <div class="card-body">
            @php $total = max($totalAssessment, 1); @endphp
            <div class="assessment-bar">
                <div class="abar-label">
                    <span>Ready</span>
                    <strong>{{ $assessmentReady }}</strong>
                </div>
                <div class="abar">
                    <div class="abar-fill" style="width:{{ round(($assessmentReady/$total)*100) }}%;background:#16a34a;"></div>
                </div>
            </div>
            <div class="assessment-bar">
                <div class="abar-label">
                    <span>Ready w/ Dev</span>
                    <strong>{{ $assessmentRWD }}</strong>
                </div>
                <div class="abar">
                    <div class="abar-fill" style="width:{{ round(($assessmentRWD/$total)*100) }}%;background:#f59e0b;"></div>
                </div>
            </div>
            <div class="assessment-bar">
                <div class="abar-label">
                    <span>Not Ready</span>
                    <strong>{{ $assessmentNR }}</strong>
                </div>
                <div class="abar">
                    <div class="abar-fill" style="width:{{ round(($assessmentNR/$total)*100) }}%;background:#ef4444;"></div>
                </div>
            </div>
            <div style="text-align:center;margin-top:8px;font-size:12px;color:#9ca3af;">Total {{ $totalAssessment }} assessment</div>
        </div>
    </div>

    {{-- Pejabat Aktif --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">⭐ Pejabat Aktif</div>
            <a href="{{ route('history_pejabat.index') }}" class="view-all">Lihat →</a>
        </div>
        <div class="card-body">
            <div class="pejabat-grid">
                <div class="pejabat-item svp">
                    <div class="pejabat-num">{{ $pejabatSVP }}</div>
                    <div class="pejabat-label">SVP</div>
                </div>
                <div class="pejabat-item vp">
                    <div class="pejabat-num">{{ $pejabatVP }}</div>
                    <div class="pejabat-label">VP</div>
                </div>
                <div class="pejabat-item spm">
                    <div class="pejabat-num">{{ $pejabatSPM }}</div>
                    <div class="pejabat-label">SPM</div>
                </div>
                <div class="pejabat-item pm">
                    <div class="pejabat-num">{{ $pejabatPM }}</div>
                    <div class="pejabat-label">PM</div>
                </div>
            </div>
            <div style="margin-top:12px;display:flex;gap:8px;">
                <div style="flex:1;background:#eff6ff;border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-size:18px;font-weight:800;color:#1d4ed8;">{{ $pgsAktif }}</div>
                    <div style="font-size:11px;color:#1d4ed8;font-weight:700;">PGS Aktif</div>
                </div>
                <div style="flex:1;background:#f5f3ff;border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-size:18px;font-weight:800;color:#7c3aed;">{{ $pjsAktif }}</div>
                    <div style="font-size:11px;color:#7c3aed;font-weight:700;">PJS Aktif</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assessment Akan Expire --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">⚠️ IDP Akan Expire</div>
            <span style="font-size:11px;color:#9ca3af;">30 hari ke depan</span>
        </div>
        <div class="card-body">
            @forelse($assessmentExpire as $a)
            <div class="expire-item">
                <div class="expire-days">{{ (int) now()->diffInDays($a->tanggal_exp_idp) }}h</div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#111827;">{{ $a->karyawan->nama }}</div>
                    <div style="font-size:11px;color:#9ca3af;">Exp: {{ $a->tanggal_exp_idp->format('d M Y') }}</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">
                ✅ Tidak ada IDP yang akan expire
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Row 3 --}}
<div class="section-grid">

    {{-- Aktivitas Terbaru --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">🕐 Aktivitas Jabatan Terbaru</div>
            <a href="{{ route('history_karyawan.index') }}" class="view-all">Lihat →</a>
        </div>
        <div class="card-body">
            @forelse($aktivitasTerbaru as $a)
            <div class="list-item">
                <div class="list-avatar">
                    @if($a->karyawan->foto)
                        <img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                    @else
                        {{ strtoupper(substr($a->karyawan->nama, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <div class="list-name">{{ $a->karyawan->nama }}</div>
                    <div class="list-sub">{{ $a->jabatan->nama_jabatan ?? '-' }}</div>
                </div>
                <div class="list-right">
                    <span class="list-badge tipe-{{ $a->tipe }}">{{ ucfirst($a->tipe) }}</span>
                    <div style="font-size:10px;color:#9ca3af;margin-top:3px;">
                        {{ $a->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">Belum ada aktivitas</div>
            @endforelse
        </div>
    </div>

    {{-- Akan Pensiun --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">🎯 Mendekati Pensiun</div>
            <span style="font-size:11px;color:#9ca3af;">Usia ≥ 53 tahun</span>
        </div>
        <div class="card-body">
            @forelse($akanPensiun as $k)
            @php
                $usia = \Carbon\Carbon::parse($k->tanggal_lahir)->age;
                $sisaTahun = 56 - $usia;
                $warnaClass = $sisaTahun <= 1 ? 'danger' : ($sisaTahun <= 2 ? 'warning' : 'ok');
            @endphp
            <div class="pensiun-item">
                <div class="sisa-tahun {{ $warnaClass }}">{{ $sisaTahun }}th</div>
                <div class="list-avatar">
                    @if($k->foto)
                        <img src="{{ Storage::url($k->foto) }}" alt="">
                    @else
                        {{ strtoupper(substr($k->nama, 0, 2)) }}
                    @endif
                </div>
                <div>
                    <div class="list-name">{{ $k->nama }}</div>
                    <div class="list-sub">{{ $k->jabatan_saat_ini ?? $k->jabatan->nama_jabatan ?? '-' }} · {{ $usia }} tahun</div>
                </div>
                <div class="list-right">
                    <a href="{{ route('karyawan.show', $k) }}" style="font-size:11px;color:#16a34a;text-decoration:none;font-weight:600;">Detail →</a>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">
                ✅ Tidak ada karyawan mendekati pensiun
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Karyawan Terbaru --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <div class="card-title">🆕 Karyawan Terbaru</div>
        <a href="{{ route('karyawan.index') }}" class="view-all">Lihat Semua →</a>
    </div>
    <div class="card-body" style="padding:0;overflow-x:auto;-webkit-overflow-scrolling:touch;">
        <table style="width:max-content;min-width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr>
                    <th style="padding:10px 20px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap;">Karyawan</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap;">Jabatan</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap;">Departemen</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap;">Tanggal Masuk</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawanTerbaru as $k)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 20px;white-space:nowrap;">
                        <div class="list-item" style="padding:0;border:none;">
                            <div class="list-avatar">
                                @if($k->foto)
                                    <img src="{{ Storage::url($k->foto) }}" alt="">
                                @else
                                    {{ strtoupper(substr($k->nama, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="list-name">{{ $k->nama }}</div>
                                <div class="list-sub">NIK {{ $k->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px;font-size:12px;color:#374151;white-space:nowrap;">{{ $k->jabatan_saat_ini ?? $k->jabatan->nama_jabatan ?? '-' }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#374151;white-space:nowrap;">{{ $k->departemen->nama_departemen ?? '-' }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#374151;white-space:nowrap;">{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d M Y') }}</td>
                    <td style="padding:12px 16px;white-space:nowrap;">
                        @if($k->status === 'aktif')
                            <span style="background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">● Aktif</span>
                        @else
                            <span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">● Tidak Aktif</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;font-size:13px;">Belum ada data karyawan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection