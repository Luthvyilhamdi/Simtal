@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Beranda')

@push('styles')
<style>
    /* Welcome */
    .welcome-card {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        border-radius: 16px; padding: 28px 32px; margin-bottom: 24px;
        color: white; display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 16px;
        position: relative; overflow: hidden;
    }
    .welcome-card::before {
        content: ''; position: absolute; top: -60px; right: -60px;
        width: 220px; height: 220px; border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .welcome-card::after {
        content: ''; position: absolute; bottom: -40px; right: 140px;
        width: 140px; height: 140px; border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .welcome-title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .welcome-sub { font-size: 13px; color: rgba(255,255,255,0.6); }
    .welcome-right { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .welcome-date { font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 600;
        background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 20px; }
    .welcome-pills { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .welcome-pill { font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
    .pill-green { background: rgba(22,163,74,0.3); color: #4ade80; }
    .pill-blue  { background: rgba(59,130,246,0.3); color: #93c5fd; }
    .pill-amber { background: rgba(245,158,11,0.3); color: #fcd34d; }

    /* KPI Grid */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
    .kpi-card {
        background: white; border-radius: 14px; border: 1px solid #e5e7eb;
        padding: 20px; display: flex; align-items: flex-start;
        justify-content: space-between; gap: 12px; transition: box-shadow 0.15s;
        position: relative; overflow: hidden;
    }
    .kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.07); }
    .kpi-card::before { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; }
    .kpi-card.green::before { background: #16a34a; }
    .kpi-card.blue::before  { background: #2563eb; }
    .kpi-card.purple::before{ background: #7c3aed; }
    .kpi-card.amber::before { background: #d97706; }
    .kpi-left { flex: 1; }
    .kpi-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .kpi-num { font-size: 32px; font-weight: 800; color: #111827; line-height: 1; margin-bottom: 6px; }
    .kpi-sub { font-size: 12px; color: #6b7280; }
    .kpi-badge { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 20px; margin-top: 6px; display: inline-block; }
    .kpi-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .kpi-icon.green { background: #f0fdf4; }
    .kpi-icon.blue  { background: #eff6ff; }
    .kpi-icon.purple{ background: #f5f3ff; }
    .kpi-icon.amber { background: #fffbeb; }

    /* Section Title */
    .sec-title { font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .sec-title::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }

    /* Chart Cards */
    .chart-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .chart-grid-3 { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px; }
    .chart-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; padding: 20px; }
    .chart-card-title { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .chart-card-sub { font-size: 12px; color: #9ca3af; margin-bottom: 16px; }
    .chart-wrap { position: relative; }

    /* Bar Chart Custom */
    .bar-chart { display: flex; flex-direction: column; gap: 10px; }
    .bar-row { display: flex; align-items: center; gap: 10px; }
    .bar-label { font-size: 11px; color: #6b7280; font-weight: 600; min-width: 80px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bar-track { flex: 1; height: 22px; background: #f3f4f6; border-radius: 6px; overflow: hidden; position: relative; }
    .bar-fill { height: 100%; border-radius: 6px; display: flex; align-items: center; padding-left: 8px; font-size: 11px; font-weight: 700; color: white; transition: width 0.8s ease; min-width: 30px; }
    .bar-val { font-size: 12px; font-weight: 700; color: #374151; min-width: 28px; text-align: right; }

    /* Pie Chart */
    .pie-wrap { display: flex; align-items: center; gap: 20px; }
    .pie-legend { display: flex; flex-direction: column; gap: 10px; flex: 1; }
    .pie-item { display: flex; align-items: center; gap: 8px; }
    .pie-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .pie-item-label { font-size: 12px; color: #6b7280; flex: 1; }
    .pie-item-val { font-size: 13px; font-weight: 700; color: #111827; }
    .pie-item-pct { font-size: 11px; color: #9ca3af; margin-left: 4px; }

    /* Tren Chart (Line/Bar) */
    .tren-chart { width: 100%; height: 200px; position: relative; }

    /* Gender & Usia */
    .demo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .demo-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; padding: 20px; }

    /* Ringkasan Direktorat */
    .tabel-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 20px; }
    .tabel-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #f3f4f6; }
    .tabel-title { font-size: 14px; font-weight: 700; color: #111827; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead th { padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f3f4f6; background: #f9fafb; white-space: nowrap; }
    thead th.center { text-align: center; }
    tbody td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
    tbody td.center { text-align: center; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #fafaf8; }

    .progress-mini { height: 5px; background: #f3f4f6; border-radius: 20px; overflow: hidden; margin-top: 4px; }
    .progress-mini-fill { height: 100%; border-radius: 20px; }

    /* Bottom Grid */
    .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .list-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; }
    .list-card-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #f3f4f6; }
    .list-card-title { font-size: 13px; font-weight: 700; color: #111827; }
    .list-card-body { padding: 0 18px; }
    .list-item { display: flex; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px solid #f9fafb; }
    .list-item:last-child { border-bottom: none; }
    .list-avatar { width: 32px; height: 32px; border-radius: 50%; background: #f0fdf4; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; overflow: hidden; }
    .list-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .list-name { font-size: 13px; font-weight: 600; color: #111827; }
    .list-sub { font-size: 11px; color: #9ca3af; margin-top: 1px; }
    .list-right { margin-left: auto; flex-shrink: 0; text-align: right; }
    .tipe-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
    .tipe-promosi { background: #dcfce7; color: #15803d; }
    .tipe-mutasi  { background: #dbeafe; color: #1d4ed8; }
    .tipe-demosi  { background: #fee2e2; color: #dc2626; }
    .tipe-onboarding { background: #fef3c7; color: #d97706; }

    .view-all { font-size: 12px; color: #16a34a; text-decoration: none; font-weight: 600; }
    .view-all:hover { text-decoration: underline; }

    @media (max-width: 1024px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .chart-grid-2 { grid-template-columns: 1fr; }
        .chart-grid-3 { grid-template-columns: 1fr; }
        .demo-grid { grid-template-columns: 1fr; }
        .bottom-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .welcome-right { align-items: flex-start; }
    }
</style>
@endpush

@section('content')

{{-- Welcome --}}
<div class="welcome-card">
    <div style="position:relative;z-index:1;">
        <div class="welcome-title">Selamat Datang, {{ auth()->user()->name }}</div>
        <div class="welcome-sub">Sistem Manajemen Talenta — Ringkasan data per hari ini</div>
        <div class="welcome-pills" style="margin-top:10px;justify-content:flex-start;">
            <span class="welcome-pill pill-green">{{ $karyawanAktif }} Karyawan Aktif</span>
            <span class="welcome-pill pill-blue">{{ $pejabatAktif }} Pejabat Aktif</span>
            <span class="welcome-pill pill-amber">{{ $pgsAktif + $pjsAktif }} PGS/PJS Aktif</span>
        </div>
    </div>
    <div class="welcome-right" style="position:relative;z-index:1;">
        <div class="welcome-date">📅 {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.5);text-align:right;">
            {{ auth()->user()->isSuperAdmin() ? '⭐ Super Admin' : '🔵 Administrator' }}
        </div>
    </div>
</div>

{{-- KPI Utama --}}
<div class="kpi-grid">
    <div class="kpi-card green">
        <div class="kpi-left">
            <div class="kpi-label">Total Karyawan</div>
            <div class="kpi-num">{{ $totalKaryawan }}</div>
            <div class="kpi-sub">{{ $karyawanTidakAktif }} tidak aktif</div>
            @if($karyawanBaru > 0)
            <span class="kpi-badge" style="background:#dcfce7;color:#15803d;">+{{ $karyawanBaru }} bulan ini</span>
            @endif
        </div>
        <div class="kpi-icon green">👥</div>
    </div>
    <div class="kpi-card blue">
        <div class="kpi-left">
            <div class="kpi-label">Pergerakan Jabatan</div>
            <div class="kpi-num">{{ $promosiThisYear + $mutasiThisYear + $demosiThisYear }}</div>
            <div class="kpi-sub">Tahun {{ now()->year }}</div>
            <span class="kpi-badge" style="background:#dbeafe;color:#1d4ed8;">{{ $promosiThisYear }} promosi · {{ $mutasiThisYear }} mutasi</span>
        </div>
        <div class="kpi-icon blue">📈</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-left">
            <div class="kpi-label">Total Assessment</div>
            <div class="kpi-num">{{ $totalAssessment }}</div>
            <div class="kpi-sub">{{ $assessmentReady }} ready · {{ $assessmentNR }} not ready</div>
            @php $pctReady = $totalAssessment > 0 ? round(($assessmentReady/$totalAssessment)*100) : 0; @endphp
            <span class="kpi-badge" style="background:#f5f3ff;color:#7c3aed;">{{ $pctReady }}% ready rate</span>
        </div>
        <div class="kpi-icon purple">📊</div>
    </div>
    <div class="kpi-card amber">
        <div class="kpi-left">
            <div class="kpi-label">Pejabat Aktif</div>
            <div class="kpi-num">{{ $pejabatAktif }}</div>
            <div class="kpi-sub">SVP/VP/SPM/PM</div>
            <span class="kpi-badge" style="background:#fef3c7;color:#d97706;">{{ $pgsAktif + $pjsAktif }} PGS/PJS aktif</span>
        </div>
        <div class="kpi-icon amber">⭐</div>
    </div>
</div>

{{-- Chart Row 1: Tren + Assessment Pie --}}
<div class="sec-title">📊 Analitik & Grafik</div>
<div class="chart-grid-3">

    {{-- Tren 12 Bulan --}}
    <div class="chart-card">
        <div class="chart-card-title">Tren Pergerakan Jabatan</div>
        <div class="chart-card-sub">12 bulan terakhir — promosi, mutasi & demosi</div>
        <canvas id="trenChart" height="200"></canvas>
    </div>

    {{-- Assessment Pie --}}
    <div class="chart-card">
        <div class="chart-card-title">Rekomendasi Assessment</div>
        <div class="chart-card-sub">Distribusi hasil assessment</div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:16px;">
            <canvas id="pieChart" width="160" height="160"></canvas>
            <div class="pie-legend" style="width:100%;">
                @php $totalA = max($totalAssessment, 1); @endphp
                @foreach($assessmentChart as $a)
                <div class="pie-item">
                    <div class="pie-dot" style="background:{{ $a['color'] }};"></div>
                    <span class="pie-item-label">{{ $a['label'] }}</span>
                    <span class="pie-item-val">{{ $a['value'] }}</span>
                    <span class="pie-item-pct">({{ round(($a['value']/$totalA)*100) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Chart Row 2: Direktorat + Job Grade --}}
<div class="chart-grid-2">
    {{-- Distribusi per Direktorat --}}
    <div class="chart-card">
        <div class="chart-card-title">Distribusi Karyawan per Direktorat</div>
        <div class="chart-card-sub">Jumlah karyawan aktif per direktorat</div>
        @php $maxDir = $distribusiDirektorat->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @foreach($distribusiDirektorat->take(11) as $d)
            <div class="bar-row">
                <div class="bar-label" title="{{ $d['nama'] }}">{{ Str::limit($d['nama'], 23) }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ ($d['total']/$maxDir)*100 }}%;background:#2563eb;">
                        {{ $d['total'] }}
                    </div>
                </div>
                <div class="bar-val">{{ $d['total'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Distribusi Job Grade --}}
    <div class="chart-card">
        <div class="chart-card-title">Distribusi per Job Grade</div>
        <div class="chart-card-sub">Jumlah karyawan aktif per job grade</div>
        @php $maxJG = $distribusiJobGrade->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @foreach($distribusiJobGrade->take(8) as $j)
            <div class="bar-row">
                <div class="bar-label">{{ $j['nama'] }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ ($j['total']/$maxJG)*100 }}%;background:#7c3aed;">
                        {{ $j['total'] }}
                    </div>
                </div>
                <div class="bar-val">{{ $j['total'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Demografi --}}
<div class="demo-grid">
    {{-- Gender --}}
    <div class="demo-card">
        <div class="chart-card-title">Demografi Gender</div>
        <div class="chart-card-sub">Karyawan aktif berdasarkan jenis kelamin</div>
        @php
            $totalGender = max($genderChart['L'] + $genderChart['P'], 1);
            $pctL = round(($genderChart['L']/$totalGender)*100);
            $pctP = 100 - $pctL;
        @endphp
        <div style="display:flex;align-items:center;gap:16px;margin-top:8px;">
            <canvas id="genderChart" width="120" height="120"></canvas>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <div style="width:12px;height:12px;border-radius:50%;background:#2563eb;flex-shrink:0;"></div>
                    <div style="flex:1;font-size:13px;color:#374151;">Laki-laki</div>
                    <div style="font-size:16px;font-weight:800;color:#111827;">{{ $genderChart['L'] }}</div>
                    <div style="font-size:12px;color:#9ca3af;">({{ $pctL }}%)</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:12px;height:12px;border-radius:50%;background:#ec4899;flex-shrink:0;"></div>
                    <div style="flex:1;font-size:13px;color:#374151;">Perempuan</div>
                    <div style="font-size:16px;font-weight:800;color:#111827;">{{ $genderChart['P'] }}</div>
                    <div style="font-size:12px;color:#9ca3af;">({{ $pctP }}%)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Usia --}}
    <div class="demo-card">
        <div class="chart-card-title">Demografi Usia</div>
        <div class="chart-card-sub">Karyawan aktif berdasarkan kelompok usia</div>
        @php $maxUsia = max(array_values($usiaChart)) ?: 1; @endphp
        <div class="bar-chart" style="margin-top:8px;">
            @foreach($usiaChart as $label => $val)
            @php
                $colors = ['< 30'=>'#06b6d4','30-39'=>'#2563eb','40-49'=>'#7c3aed','50+'=>'#d97706'];
                $color = $colors[$label] ?? '#6b7280';
            @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $label }} thn</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ ($val/$maxUsia)*100 }}%;background:{{ $color }};">
                        {{ $val }}
                    </div>
                </div>
                <div class="bar-val">{{ $val }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Pejabat Stats --}}
<div class="sec-title">⭐ Statistik Pejabat</div>
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px;">
    @foreach([['SVP',$pejabatSVP,'#d97706','#fef3c7'],['VP',$pejabatVP,'#1d4ed8','#eff6ff'],['SPM',$pejabatSPM,'#7c3aed','#f5f3ff'],['PM',$pejabatPM,'#15803d','#f0fdf4'],['PGS',$pgsAktif,'#0891b2','#ecfeff'],['PJS',$pjsAktif,'#be185d','#fdf2f8']] as [$label,$val,$color,$bg])
    <div style="background:white;border-radius:12px;border:1px solid #e5e7eb;padding:16px;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:{{ $color }};">{{ $val }}</div>
        <div style="font-size:12px;font-weight:700;color:{{ $color }};margin-top:4px;letter-spacing:0.5px;">{{ $label }}</div>
        <div style="font-size:10px;color:#9ca3af;margin-top:2px;">Aktif</div>
    </div>
    @endforeach
</div>

{{-- Ringkasan per Direktorat --}}
<div class="sec-title">🏢 Ringkasan per Direktorat</div>
<div class="tabel-card">
    <div class="tabel-header">
        <div class="tabel-title">Ringkasan Karyawan & Aktivitas per Direktorat · {{ now()->year }}</div>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Direktorat</th>
                    <th class="center">Total Karyawan</th>
                    <th class="center">Aktif</th>
                    <th class="center">Proporsi</th>
                    <th class="center">Promosi {{ now()->year }}</th>
                    <th class="center">Mutasi {{ now()->year }}</th>
                    <th class="center">Total Assessment</th>
                    <th class="center">Ready</th>
                    <th class="center">Ready Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ringkasanDirektorat as $r)
                @php
                    $readyRate = $r['assessment'] > 0 ? round(($r['ready']/$r['assessment'])*100) : 0;
                    $proporsi  = $totalKaryawan > 0 ? round(($r['aktif']/$totalKaryawan)*100) : 0;
                @endphp
                <tr>
                    <td style="font-weight:600;color:#111827;">{{ $r['nama'] }}</td>
                    <td class="center">{{ $r['total'] }}</td>
                    <td class="center">
                        <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">{{ $r['aktif'] }}</span>
                    </td>
                    <td class="center" style="min-width:100px;">
                        <div style="font-size:11px;color:#6b7280;margin-bottom:3px;">{{ $proporsi }}%</div>
                        <div class="progress-mini">
                            <div class="progress-mini-fill" style="width:{{ $proporsi }}%;background:#2563eb;"></div>
                        </div>
                    </td>
                    <td class="center">
                        @if($r['promosi'] > 0)
                            <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">{{ $r['promosi'] }}</span>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td class="center">
                        @if($r['mutasi'] > 0)
                            <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">{{ $r['mutasi'] }}</span>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td class="center">{{ $r['assessment'] ?: '—' }}</td>
                    <td class="center">
                        @if($r['ready'] > 0)
                            <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;">{{ $r['ready'] }}</span>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td class="center" style="min-width:90px;">
                        @if($r['assessment'] > 0)
                            <div style="font-size:11px;color:#6b7280;margin-bottom:3px;">{{ $readyRate }}%</div>
                            <div class="progress-mini">
                                <div class="progress-mini-fill" style="width:{{ $readyRate }}%;background:#16a34a;"></div>
                            </div>
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:30px;color:#9ca3af;">Belum ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Bottom: Aktivitas + Pensiun + Expire --}}
<div class="sec-title">📋 Pemantauan & Aktivitas</div>
<div class="bottom-grid">

    {{-- Aktivitas Terbaru --}}
    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">🕐 Aktivitas Jabatan Terbaru</div>
            <a href="{{ route('history_karyawan.index') }}" class="view-all">Lihat Semua →</a>
        </div>
        <div class="list-card-body">
            @forelse($aktivitasTerbaru as $a)
            <div class="list-item">
                <div class="list-avatar">
                    @if($a->karyawan->foto)<img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                    @else {{ strtoupper(substr($a->karyawan->nama, 0, 2)) }} @endif
                </div>
                <div>
                    <div class="list-name">{{ $a->karyawan->nama }}</div>
                    <div class="list-sub">{{ $a->jabatan->nama_jabatan ?? '-' }}</div>
                </div>
                <div class="list-right">
                    <span class="tipe-badge tipe-{{ $a->tipe }}">{{ ucfirst($a->tipe) }}</span>
                    <div style="font-size:10px;color:#9ca3af;margin-top:3px;">{{ $a->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">Belum ada aktivitas</div>
            @endforelse
        </div>
    </div>

    {{-- Mendekati Pensiun --}}
    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">🎯 Mendekati Pensiun</div>
            <span style="font-size:11px;color:#9ca3af;">Usia ≥ 53 tahun</span>
        </div>
        <div class="list-card-body">
            @forelse($akanPensiun as $k)
            @php
                $usia = \Carbon\Carbon::parse($k->tanggal_lahir)->age;
                $sisaTahun = 56 - $usia;
                $warnaClass = $sisaTahun <= 1 ? '#ef4444' : ($sisaTahun <= 2 ? '#f59e0b' : '#6b7280');
            @endphp
            <div class="list-item">
                <div style="font-size:18px;font-weight:800;color:{{ $warnaClass }};min-width:36px;text-align:center;">{{ $sisaTahun }}th</div>
                <div class="list-avatar">
                    @if($k->foto)<img src="{{ Storage::url($k->foto) }}" alt="">
                    @else {{ strtoupper(substr($k->nama, 0, 2)) }} @endif
                </div>
                <div>
                    <div class="list-name">{{ $k->nama }}</div>
                    <div class="list-sub">{{ $k->jabatan_saat_ini ?? $k->jabatan->nama_jabatan ?? '-' }} · {{ $usia }} thn</div>
                </div>
                <div class="list-right">
                    <a href="{{ route('karyawan.show', $k) }}" style="font-size:11px;color:#16a34a;text-decoration:none;font-weight:600;">Detail →</a>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">✅ Tidak ada</div>
            @endforelse
        </div>
    </div>

    {{-- IDP Expire --}}
    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">⚠️ Assessment Akan Expire</div>
            <span style="font-size:11px;color:#9ca3af;">30 hari ke depan</span>
        </div>
        <div class="list-card-body">
            @forelse($assessmentExpire as $a)
            <div class="list-item">
                <div style="font-size:16px;font-weight:800;color:#ef4444;min-width:32px;text-align:center;">
                    {{ (int) now()->diffInDays($a->tanggal_exp_idp) }}h
                </div>
                <div class="list-avatar" style="background:#fef2f2;color:#dc2626;">
                    @if($a->karyawan->foto)<img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                    @else {{ strtoupper(substr($a->karyawan->nama, 0, 2)) }} @endif
                </div>
                <div>
                    <div class="list-name">{{ $a->karyawan->nama }}</div>
                    <div class="list-sub">Exp: {{ $a->tanggal_exp_idp->format('d M Y') }}</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">✅ Tidak ada</div>
            @endforelse
        </div>
    </div>

    {{-- Karyawan Terbaru --}}
    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">🆕 Karyawan Terbaru</div>
            <a href="{{ route('karyawan.index') }}" class="view-all">Lihat Semua →</a>
        </div>
        <div class="list-card-body">
            @forelse($karyawanTerbaru as $k)
            <div class="list-item">
                <div class="list-avatar">
                    @if($k->foto)<img src="{{ Storage::url($k->foto) }}" alt="">
                    @else {{ strtoupper(substr($k->nama, 0, 2)) }} @endif
                </div>
                <div>
                    <div class="list-name">{{ $k->nama }}</div>
                    <div class="list-sub">{{ $k->jabatan_saat_ini ?? $k->jabatan->nama_jabatan ?? '-' }}</div>
                </div>
                <div class="list-right">
                    <div style="font-size:11px;color:#9ca3af;">{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d M Y') }}</div>
                    @if($k->status === 'aktif')
                        <span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;">Aktif</span>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">Belum ada data</div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#6b7280';

// === TREN CHART ===
const trenData = @json($trenBulan);
new Chart(document.getElementById('trenChart'), {
    type: 'bar',
    data: {
        labels: trenData.map(d => d.bulan),
        datasets: [
            {
                label: 'Promosi',
                data: trenData.map(d => d.promosi),
                backgroundColor: '#16a34a',
                borderRadius: 4,
            },
            {
                label: 'Mutasi',
                data: trenData.map(d => d.mutasi),
                backgroundColor: '#2563eb',
                borderRadius: 4,
            },
            {
                label: 'Demosi',
                data: trenData.map(d => d.demosi),
                backgroundColor: '#ef4444',
                borderRadius: 4,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 11 } } },
            tooltip: { mode: 'index', intersect: false },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 11 } } },
        },
    }
});

// === PIE CHART ===
const pieData = @json($assessmentChart);
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: pieData.map(d => d.label),
        datasets: [{
            data: pieData.map(d => d.value),
            backgroundColor: pieData.map(d => d.color),
            borderWidth: 2,
            borderColor: '#ffffff',
        }]
    },
    options: {
        responsive: false,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } },
        },
    }
});

// === GENDER CHART ===
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: ['Laki-laki', 'Perempuan'],
        datasets: [{
            data: [{{ $genderChart['L'] }}, {{ $genderChart['P'] }}],
            backgroundColor: ['#2563eb', '#ec4899'],
            borderWidth: 2,
            borderColor: '#ffffff',
        }]
    },
    options: {
        responsive: false,
        cutout: '65%',
        plugins: {
            legend: { display: false },
        },
    }
});
</script>
@endpush