@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Beranda')

@push('styles')
<style>
    /* Welcome */
    .welcome-card {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        border-radius: 14px; padding: 24px 28px; margin-bottom: 20px;
        color: white; display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 14px;
        position: relative; overflow: hidden;
    }
    .welcome-card::before { content:'';position:absolute;top:-50px;right:-50px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,0.04); }
    .welcome-card::after  { content:'';position:absolute;bottom:-30px;right:120px;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,0.04); }
    .welcome-title { font-size:18px;font-weight:700;margin-bottom:3px; }
    .welcome-sub { font-size:12px;color:rgba(255,255,255,0.55); }
    .welcome-pills { display:flex;gap:6px;flex-wrap:wrap;margin-top:10px; }
    .welcome-pill { font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px; }
    .pill-green { background:rgba(22,163,74,0.3);color:#4ade80; }
    .pill-blue  { background:rgba(59,130,246,0.3);color:#93c5fd; }
    .pill-amber { background:rgba(245,158,11,0.3);color:#fcd34d; }
    .welcome-date { font-size:12px;color:rgba(255,255,255,0.8);font-weight:600;background:rgba(255,255,255,0.1);padding:6px 14px;border-radius:20px; }

    /* KPI */
    .kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px; }
    .kpi-card { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:18px;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;transition:box-shadow 0.15s;position:relative;overflow:hidden; }
    .kpi-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); }
    .kpi-card::before { content:'';position:absolute;bottom:0;left:0;right:0;height:3px; }
    .kpi-card.green::before  { background:#16a34a; }
    .kpi-card.blue::before   { background:#2563eb; }
    .kpi-card.purple::before { background:#7c3aed; }
    .kpi-card.amber::before  { background:#d97706; }
    .kpi-card.teal::before   { background:#0891b2; }
    .kpi-label { font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px; }
    .kpi-num { font-size:28px;font-weight:800;color:#111827;line-height:1;margin-bottom:5px; }
    .kpi-sub { font-size:11px;color:#6b7280; }
    .kpi-badge { font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-top:5px;display:inline-block; }
    .kpi-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
    .kpi-icon.green  { background:#f0fdf4; }
    .kpi-icon.blue   { background:#eff6ff; }
    .kpi-icon.purple { background:#f5f3ff; }
    .kpi-icon.amber  { background:#fffbeb; }
    .kpi-icon.teal   { background:#ecfeff; }

    /* Section title */
    .sec-title { font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;display:flex;align-items:center;gap:8px; }
    .sec-title::after { content:'';flex:1;height:1px;background:#f3f4f6; }

    /* Chart cards */
    .chart-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px; }
    .chart-grid-3 { display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px; }
    .chart-card { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:18px; }
    .chart-card-title { font-size:13px;font-weight:700;color:#111827;margin-bottom:3px; }
    .chart-card-sub { font-size:11px;color:#9ca3af;margin-bottom:14px; }

    /* Bar chart */
    .bar-chart { display:flex;flex-direction:column;gap:8px; }
    .bar-row { display:flex;align-items:center;gap:8px; }
    .bar-label { font-size:11px;color:#6b7280;font-weight:600;min-width:80px;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .bar-track { flex:1;height:20px;background:#f3f4f6;border-radius:5px;overflow:hidden; }
    .bar-fill { height:100%;border-radius:5px;display:flex;align-items:center;padding-left:7px;font-size:10px;font-weight:700;color:white;transition:width 0.8s ease;min-width:28px; }
    .bar-val { font-size:11px;font-weight:700;color:#374151;min-width:26px;text-align:right; }

    /* Pie */
    .pie-legend { display:flex;flex-direction:column;gap:8px;flex:1; }
    .pie-item { display:flex;align-items:center;gap:7px; }
    .pie-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
    .pie-item-label { font-size:11px;color:#6b7280;flex:1; }
    .pie-item-val { font-size:12px;font-weight:700;color:#111827; }
    .pie-item-pct { font-size:10px;color:#9ca3af;margin-left:3px; }

    /* Demografi */
    .demo-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px; }
    .demo-card { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:18px; }

    /* Pejabat mini cards */
    .pejabat-grid { display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:18px; }
    .pejabat-mini { background:white;border-radius:10px;border:1px solid #e5e7eb;padding:14px;text-align:center; }
    .pejabat-num { font-size:26px;font-weight:800; }
    .pejabat-label { font-size:11px;font-weight:700;margin-top:3px;letter-spacing:0.5px; }
    .pejabat-sub { font-size:10px;color:#9ca3af;margin-top:2px; }

    /* Tabel Direktorat */
    .tabel-card { background:white;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;margin-bottom:18px; }
    .tabel-header { display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6; }
    .tabel-title { font-size:13px;font-weight:700;color:#111827; }
    table { width:100%;border-collapse:collapse;font-size:12px; }
    thead th { padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    thead th.center { text-align:center; }
    tbody td { padding:10px 14px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody td.center { text-align:center; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }
    .progress-mini { height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden;margin-top:3px; }
    .progress-mini-fill { height:100%;border-radius:20px; }

    /* Bottom cards */
    .bottom-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px; }
    .list-card { background:white;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden; }
    .list-card-header { display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6; }
    .list-card-title { font-size:12px;font-weight:700;color:#111827; }
    .list-card-body { padding:0 16px; }
    .list-item { display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f9fafb; }
    .list-item:last-child { border-bottom:none; }
    .list-avatar { width:30px;height:30px;border-radius:50%;background:#f0fdf4;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .list-avatar img { width:100%;height:100%;object-fit:cover; }
    .list-name { font-size:12px;font-weight:600;color:#111827; }
    .list-sub { font-size:10px;color:#9ca3af;margin-top:1px; }
    .list-right { margin-left:auto;flex-shrink:0;text-align:right; }
    .tipe-badge { font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px; }
    .tipe-promosi    { background:#dcfce7;color:#15803d; }
    .tipe-mutasi     { background:#dbeafe;color:#1d4ed8; }
    .tipe-demosi     { background:#fee2e2;color:#dc2626; }
    .tipe-onboarding { background:#fef3c7;color:#d97706; }
    .view-all { font-size:11px;color:#16a34a;text-decoration:none;font-weight:600; }
    .view-all:hover { text-decoration:underline; }

    /* Kompetensi stat mini */
    .komp-stat-row { display:flex;align-items:center;gap:8px;margin-top:8px; }
    .komp-stat-item { flex:1;text-align:center;padding:8px 6px;border-radius:8px; }
    .komp-stat-num { font-size:20px;font-weight:800;line-height:1; }
    .komp-stat-label { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;margin-top:2px; }

    @media (max-width:1024px) {
        .kpi-grid { grid-template-columns:repeat(2,1fr); }
        .chart-grid-2,.chart-grid-3 { grid-template-columns:1fr; }
        .demo-grid { grid-template-columns:1fr; }
        .bottom-grid { grid-template-columns:1fr; }
        .pejabat-grid { grid-template-columns:repeat(3,1fr); }
    }
    @media (max-width:640px) {
        .kpi-grid { grid-template-columns:1fr 1fr; }
        .pejabat-grid { grid-template-columns:repeat(3,1fr); }
    }
</style>
@endpush

@section('content')

{{-- WELCOME --}}
<div class="welcome-card">
    <div style="position:relative;z-index:1;">
        <div class="welcome-title">Selamat Datang, {{ auth()->user()->name }} 👋</div>
        <div class="welcome-sub">Sistem Manajemen Talenta — Ringkasan data per hari ini</div>
        <div class="welcome-pills">
            <span class="welcome-pill pill-green">{{ $karyawanAktif }} Karyawan Aktif</span>
            <span class="welcome-pill pill-blue">{{ $pejabatAktif }} Pejabat Aktif</span>
            <span class="welcome-pill pill-amber">{{ $pgsAktif + $pjsAktif }} PGS/PJS Aktif</span>
        </div>
    </div>
    <div style="position:relative;z-index:1;display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
        <div class="welcome-date">📅 {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,0.45);">
            {{ auth()->user()->isSuperAdmin() ? '⭐ Super Admin' : (auth()->user()->isAdmin() ? '🔵 Administrator' : '👤 User') }}
        </div>
    </div>
</div>

{{-- KPI UTAMA --}}
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
            <div class="kpi-label">Assessment Rekomendasi</div>
            <div class="kpi-num">{{ $totalAssessment }}</div>
            <div class="kpi-sub">Total assessment rekomendasi</div>
            <div class="komp-stat-row">
                <div class="komp-stat-item" style="background:#dcfce7;">
                    <div class="komp-stat-num" style="color:#15803d;">{{ $assessmentReady }}</div>
                    <div class="komp-stat-label" style="color:#15803d;">Ready</div>
                </div>
                <div class="komp-stat-item" style="background:#fef9c3;">
                    <div class="komp-stat-num" style="color:#a16207;">{{ $assessmentRWD }}</div>
                    <div class="komp-stat-label" style="color:#a16207;">Readt With Dev</div>
                </div>
                <div class="komp-stat-item" style="background:#fee2e2;">
                    <div class="komp-stat-num" style="color:#dc2626;">{{ $assessmentNR }}</div>
                    <div class="komp-stat-label" style="color:#dc2626;">Not Ready</div>
                </div>
            </div>
            @php $pctReady = $totalAssessment > 0 ? round(($assessmentReady/$totalAssessment)*100) : 0; @endphp
            <span class="kpi-badge" style="background:#f5f3ff;color:#7c3aed;">{{ $pctReady }}% ready rate</span>
        </div>
        <div class="kpi-icon purple">📋</div>
    </div>
    <div class="kpi-card teal">
        <div class="kpi-left">
            <div class="kpi-label">Assessment Kompetensi</div>
            <div class="kpi-num">{{ $totalKompetensi }}</div>
            <div class="kpi-sub">Total assessment kompetensi</div>
            <div class="komp-stat-row">
                <div class="komp-stat-item" style="background:#dcfce7;">
                    <div class="komp-stat-num" style="color:#15803d;">{{ $totalQualified }}</div>
                    <div class="komp-stat-label" style="color:#15803d;">Qualified</div>
                </div>
                <div class="komp-stat-item" style="background:#fee2e2;">
                    <div class="komp-stat-num" style="color:#dc2626;">{{ $totalNotQualified }}</div>
                    <div class="komp-stat-label" style="color:#dc2626;">Not Qual.</div>
                </div>
            </div>
            @php $pctQualified = $totalKompetensi > 0 ? round(($totalQualified/$totalKompetensi)*100) : 0; @endphp
            <span class="kpi-badge" style="background:#f0fdfa;color:#0f766e;">{{ $pctQualified }}% qualified rate</span>
        </div>
        <div class="kpi-icon teal">⭐</div>
    </div>
</div>


{{-- SO CORE & NON CORE --}}
@php
$namaBulanDash = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$pctTerisi  = $soTotalMc > 0 ? round(($soTerisi/$soTotalMc)*100) : 0;
@endphp
<div class="sec-title">🏗️ Struktur Organisasi — {{ $namaBulanDash[$soBulan] }} {{ $soTahun }}</div>
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px">

  <div class="kpi-card blue" style="flex-direction:column;align-items:flex-start;gap:6px">
    <div class="kpi-label">Total Posisi</div>
    <div class="kpi-num">{{ $soTotalPosisi }}</div>
    <div class="kpi-sub">Posisi terdaftar</div>
  </div>

  <div class="kpi-card amber" style="flex-direction:column;align-items:flex-start;gap:6px">
    <div class="kpi-label">Total MC/TKO</div>
    <div class="kpi-num">{{ $soTotalMc }}</div>
    <div class="kpi-sub">Man Count kebutuhan</div>
  </div>

  <div class="kpi-card green" style="flex-direction:column;align-items:flex-start;gap:6px">
    <div class="kpi-label">Terisi</div>
    <div class="kpi-num">{{ $soTerisi }}</div>
    <div class="kpi-sub">{{ $pctTerisi }}% dari MC/TKO</div>
    <div style="height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden;margin-top:4px;width:100%">
      <div style="height:100%;width:{{ $pctTerisi }}%;background:#16a34a;border-radius:20px"></div>
    </div>
  </div>

  <div class="kpi-card" style="border-top:3px solid #2563eb;flex-direction:column;align-items:flex-start;gap:6px">
    @php $pctCoreTerisi = $soCoreMc > 0 ? round(($soCoreTerisi/$soCoreMc)*100) : 0; @endphp
    <div class="kpi-label" style="color:#9ca3af">Core — Keterisian</div>
    <div class="kpi-num" style="color:#2563eb">{{ $soCoreTerisi }} <span style="font-size:14px;color:#9ca3af">/ {{ $soCoreMc }}</span></div>
    <div class="kpi-sub">{{ $pctCoreTerisi }}% terisi · {{ $soCore }} posisi Core</div>
    <div style="height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden;margin-top:4px;width:100%">
      <div style="height:100%;width:{{ $pctCoreTerisi }}%;background:#2563eb;border-radius:20px"></div>
    </div>
  </div>

  <div class="kpi-card" style="border-top:3px solid #7c3aed;flex-direction:column;align-items:flex-start;gap:6px">
    @php $pctNonCoreTerisi = $soNonCoreMc > 0 ? round(($soNonCoreTerisi/$soNonCoreMc)*100) : 0; @endphp
    <div class="kpi-label" style="color:#9ca3af">Non Core — Keterisian</div>
    <div class="kpi-num" style="color:#7c3aed">{{ $soNonCoreTerisi }} <span style="font-size:14px;color:#9ca3af">/ {{ $soNonCoreMc }}</span></div>
    <div class="kpi-sub">{{ $pctNonCoreTerisi }}% terisi · {{ $soNonCore }} posisi Non Core</div>
    <div style="height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden;margin-top:4px;width:100%">
      <div style="height:100%;width:{{ $pctNonCoreTerisi }}%;background:#7c3aed;border-radius:20px"></div>
    </div>
  </div>

</div>


{{-- SO CHARTS: Core/NonCore Pie + Deviasi per Direktorat --}}
<div class="chart-grid-2" style="margin-bottom:18px">
    <div class="chart-card">
        <div class="chart-card-title">Core vs Non Core</div>
        <div class="chart-card-sub">Distribusi posisi Struktur Organisasi {{ $namaBulanDash[$soBulan] }} {{ $soTahun }}</div>
        <div style="display:flex;align-items:center;gap:20px;margin-top:6px">
            <canvas id="soCorePie" width="130" height="130" style="flex-shrink:0"></canvas>
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
                    <div style="width:10px;height:10px;border-radius:50%;background:#2563eb;flex-shrink:0"></div>
                    <div style="flex:1;font-size:12px;color:#374151">Core</div>
                    <div style="font-size:15px;font-weight:800;color:#2563eb">{{ $soCore }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
                    <div style="width:10px;height:10px;border-radius:50%;background:#7c3aed;flex-shrink:0"></div>
                    <div style="flex:1;font-size:12px;color:#374151">Non Core</div>
                    <div style="font-size:15px;font-weight:800;color:#7c3aed">{{ $soNonCore }}</div>
                </div>
                <div style="margin-top:14px;padding-top:10px;border-top:1px solid #f3f4f6">
                    <div style="font-size:11px;color:#9ca3af">Terisi Core: <strong style="color:#2563eb">{{ $soCoreTerisi }}</strong> / {{ $soCoreMc }}</div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:3px">Terisi Non Core: <strong style="color:#7c3aed">{{ $soNonCoreTerisi }}</strong> / {{ $soNonCoreMc }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-card-title">Deviasi per Direktorat</div>
        <div class="chart-card-sub">Selisih MC/TKO vs Pengisian (negatif = kurang terisi)</div>
        @php $maxDev = $soPerDirektorat->max(fn($d) => abs($d->deviasi)) ?: 1; @endphp
        <div class="bar-chart" style="margin-top:6px">
            @foreach($soPerDirektorat as $d)
            @php
                $devVal  = (int) $d->deviasi;
                $barW    = min(100, round(abs($devVal) / $maxDev * 100));
                $barColor = $devVal < 0 ? '#ef4444' : ($devVal > 0 ? '#16a34a' : '#6b7280');
                $shortName = Str::limit(str_replace(['Direktorat ', 'Directorat '], '', $d->direktorat), 20);
            @endphp
            <div class="bar-row">
                <div class="bar-label" title="{{ $d->direktorat }}">{{ $shortName }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ $barW }}%;background:{{ $barColor }};">{{ $devVal }}</div>
                </div>
                <div class="bar-val" style="color:{{ $barColor }}">{{ $devVal }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- GRAFIK --}}
<div class="sec-title">📊 Analitik & Grafik</div>
<div class="chart-grid-3">
    <div class="chart-card">
        <div class="chart-card-title">Tren Pergerakan Jabatan</div>
        <div class="chart-card-sub">12 bulan terakhir — promosi, mutasi & demosi</div>
        <canvas id="trenChart" height="200"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-card-title">Rekomendasi Assessment</div>
        <div class="chart-card-sub">Distribusi hasil assessment</div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:14px;">
            <canvas id="pieChart" width="150" height="150"></canvas>
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

{{-- ASSESSMENT KOMPETENSI CHART --}}
<div class="chart-grid-3" style="margin-bottom:18px;">
    <div class="chart-card">
        <div class="chart-card-title">Tren Assessment Kompetensi</div>
        <div class="chart-card-sub">QUALIFIED vs NOT QUALIFIED per bulan (12 bulan terakhir)</div>
        <canvas id="kompChart" height="200"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-card-title">Kompetensi: QUALIFIED vs NOT QUALIFIED</div>
        <div class="chart-card-sub">Distribusi hasil assessment kompetensi</div>
        @php $totalK = max($totalKompetensi, 1); @endphp
        <div style="display:flex;flex-direction:column;align-items:center;gap:14px;">
            <canvas id="kompPieChart" width="150" height="150"></canvas>
            <div class="pie-legend" style="width:100%;">
                <div class="pie-item">
                    <div class="pie-dot" style="background:#15803d;"></div>
                    <span class="pie-item-label">QUALIFIED</span>
                    <span class="pie-item-val">{{ $totalQualified }}</span>
                    <span class="pie-item-pct">({{ round(($totalQualified/$totalK)*100) }}%)</span>
                </div>
                <div class="pie-item">
                    <div class="pie-dot" style="background:#dc2626;"></div>
                    <span class="pie-item-label">NOT QUALIFIED</span>
                    <span class="pie-item-val">{{ $totalNotQualified }}</span>
                    <span class="pie-item-pct">({{ round(($totalNotQualified/$totalK)*100) }}%)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="chart-grid-2">
    <div class="chart-card">
        <div class="chart-card-title">Distribusi per Direktorat</div>
        <div class="chart-card-sub">Jumlah karyawan aktif</div>
        @php $maxDir = $distribusiDirektorat->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @foreach($distribusiDirektorat->take(8) as $d)
            <div class="bar-row">
                <div class="bar-label" title="{{ $d['nama'] }}">{{ Str::limit($d['nama'], 18) }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ ($d['total']/$maxDir)*100 }}%;background:#2563eb;">{{ $d['total'] }}</div>
                </div>
                <div class="bar-val">{{ $d['total'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-card-title">Distribusi per Job Grade</div>
        <div class="chart-card-sub">Jumlah karyawan aktif</div>
        @php $maxJG = $distribusiJobGrade->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @foreach($distribusiJobGrade->take(8) as $j)
            <div class="bar-row">
                <div class="bar-label">{{ $j['nama'] }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ ($j['total']/$maxJG)*100 }}%;background:#7c3aed;">{{ $j['total'] }}</div>
                </div>
                <div class="bar-val">{{ $j['total'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- DEMOGRAFI --}}
<div class="demo-grid">
    <div class="demo-card">
        <div class="chart-card-title">Demografi Gender</div>
        <div class="chart-card-sub">Karyawan aktif berdasarkan jenis kelamin</div>
        @php $totalGender=max($genderChart['L']+$genderChart['P'],1);$pctL=round(($genderChart['L']/$totalGender)*100);$pctP=100-$pctL; @endphp
        <div style="display:flex;align-items:center;gap:14px;margin-top:6px;">
            <canvas id="genderChart" width="110" height="110"></canvas>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:#2563eb;flex-shrink:0;"></div>
                    <div style="flex:1;font-size:12px;color:#374151;">Laki-laki</div>
                    <div style="font-size:15px;font-weight:800;color:#111827;">{{ $genderChart['L'] }}</div>
                    <div style="font-size:11px;color:#9ca3af;">({{ $pctL }}%)</div>
                </div>
                <div style="display:flex;align-items:center;gap:7px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:#ec4899;flex-shrink:0;"></div>
                    <div style="flex:1;font-size:12px;color:#374151;">Perempuan</div>
                    <div style="font-size:15px;font-weight:800;color:#111827;">{{ $genderChart['P'] }}</div>
                    <div style="font-size:11px;color:#9ca3af;">({{ $pctP }}%)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="demo-card">
        <div class="chart-card-title">Demografi Usia</div>
        <div class="chart-card-sub">Karyawan aktif berdasarkan kelompok usia</div>
        @php $maxUsia=max(array_values($usiaChart))?:1; @endphp
        <div class="bar-chart" style="margin-top:6px;">
            @foreach($usiaChart as $label => $val)
            @php $colors=['< 30'=>'#06b6d4','30-39'=>'#2563eb','40-49'=>'#7c3aed','50+'=>'#d97706'];$color=$colors[$label]??'#6b7280'; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $label }} thn</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ ($val/$maxUsia)*100 }}%;background:{{ $color }};">{{ $val }}</div>
                </div>
                <div class="bar-val">{{ $val }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- PEJABAT --}}
<div class="sec-title">⭐ Statistik Pejabat</div>
<div class="pejabat-grid">
    @foreach([['SVP',$pejabatSVP,'#d97706'],['VP',$pejabatVP,'#1d4ed8'],['SPM',$pejabatSPM,'#7c3aed'],['PM',$pejabatPM,'#15803d'],['PGS',$pgsAktif,'#0891b2'],['PJS',$pjsAktif,'#be185d']] as [$label,$val,$color])
    <div class="pejabat-mini">
        <div class="pejabat-num" style="color:{{ $color }};">{{ $val }}</div>
        <div class="pejabat-label" style="color:{{ $color }};">{{ $label }}</div>
        <div class="pejabat-sub">Aktif</div>
    </div>
    @endforeach
</div>

{{-- RINGKASAN DIREKTORAT --}}
<div class="sec-title">🏢 Ringkasan per Direktorat</div>
<div class="tabel-card">
    <div class="tabel-header">
        <div class="tabel-title">Karyawan & Aktivitas per Direktorat · {{ now()->year }}</div>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Direktorat</th>
                    <th class="center">Total</th>
                    <th class="center">Aktif</th>
                    <th class="center">Proporsi</th>
                    <th class="center">Promosi {{ now()->year }}</th>
                    <th class="center">Mutasi {{ now()->year }}</th>
                    <th class="center">Assessment</th>
                    <th class="center">Ready</th>
                    <th class="center">Ready Rate</th>
                    <th class="center">Qualified</th>
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
                    <td class="center"><span style="background:#dcfce7;color:#15803d;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['aktif'] }}</span></td>
                    <td class="center" style="min-width:90px;">
                        <div style="font-size:10px;color:#6b7280;margin-bottom:2px;">{{ $proporsi }}%</div>
                        <div class="progress-mini"><div class="progress-mini-fill" style="width:{{ $proporsi }}%;background:#2563eb;"></div></div>
                    </td>
                    <td class="center">
                        @if($r['promosi'] > 0)<span style="background:#dcfce7;color:#15803d;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['promosi'] }}</span>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center">
                        @if($r['mutasi'] > 0)<span style="background:#dbeafe;color:#1d4ed8;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['mutasi'] }}</span>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center">{{ $r['assessment'] ?: '—' }}</td>
                    <td class="center">
                        @if($r['ready'] > 0)<span style="background:#dcfce7;color:#15803d;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['ready'] }}</span>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center" style="min-width:80px;">
                        @if($r['assessment'] > 0)
                            <div style="font-size:10px;color:#6b7280;margin-bottom:2px;">{{ $readyRate }}%</div>
                            <div class="progress-mini"><div class="progress-mini-fill" style="width:{{ $readyRate }}%;background:#16a34a;"></div></div>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center">
                        @if(isset($r['qualified']) && $r['qualified'] > 0)
                            <span style="background:#dbeafe;color:#1d4ed8;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['qualified'] }}</span>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center;padding:24px;color:#9ca3af;">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- PEMANTAUAN --}}
<div class="sec-title">📋 Pemantauan & Aktivitas</div>
<div class="bottom-grid">
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
                    @else{{ strtoupper(substr($a->karyawan->nama,0,2)) }}@endif
                </div>
                <div>
                    <div class="list-name">{{ $a->karyawan->nama }}</div>
                    <div class="list-sub">{{ $a->jabatan->nama_jabatan ?? '-' }}</div>
                </div>
                <div class="list-right">
                    <span class="tipe-badge tipe-{{ $a->tipe }}">{{ ucfirst($a->tipe) }}</span>
                    <div style="font-size:10px;color:#9ca3af;margin-top:2px;">{{ $a->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:18px;color:#9ca3af;font-size:12px;">Belum ada aktivitas</div>
            @endforelse
        </div>
    </div>

    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">🎯 Mendekati Pensiun</div>
            <span style="font-size:10px;color:#9ca3af;">Usia ≥ 53 tahun</span>
        </div>
        <div class="list-card-body">
            @forelse($akanPensiun as $k)
            @php $usia=\Carbon\Carbon::parse($k->tanggal_lahir)->age;$sisaTahun=56-$usia;$warnaClass=$sisaTahun<=1?'#ef4444':($sisaTahun<=2?'#f59e0b':'#6b7280'); @endphp
            <div class="list-item">
                <div style="font-size:16px;font-weight:800;color:{{ $warnaClass }};min-width:32px;text-align:center;">{{ $sisaTahun }}th</div>
                <div class="list-avatar">
                    @if($k->foto)<img src="{{ Storage::url($k->foto) }}" alt="">
                    @else{{ strtoupper(substr($k->nama,0,2)) }}@endif
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
            <div style="text-align:center;padding:18px;color:#9ca3af;font-size:12px;">✅ Tidak ada</div>
            @endforelse
        </div>
    </div>

    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">⚠️ Assessment Akan Expire</div>
            <span style="font-size:10px;color:#9ca3af;">30 hari ke depan</span>
        </div>
        <div class="list-card-body">
            @forelse($assessmentExpire as $a)
            <div class="list-item">
                <div style="font-size:14px;font-weight:800;color:#ef4444;min-width:30px;text-align:center;">{{ (int) now()->diffInDays($a->tanggal_exp_idp) }}h</div>
                <div class="list-avatar" style="background:#fef2f2;color:#dc2626;">
                    @if($a->karyawan->foto)<img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                    @else{{ strtoupper(substr($a->karyawan->nama,0,2)) }}@endif
                </div>
                <div>
                    <div class="list-name">{{ $a->karyawan->nama }}</div>
                    <div class="list-sub">Exp: {{ $a->tanggal_exp_idp->format('d M Y') }}</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:18px;color:#9ca3af;font-size:12px;">✅ Tidak ada</div>
            @endforelse
        </div>
    </div>

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
                    @else{{ strtoupper(substr($k->nama,0,2)) }}@endif
                </div>
                <div>
                    <div class="list-name">{{ $k->nama }}</div>
                    <div class="list-sub">{{ $k->jabatan_saat_ini ?? $k->jabatan->nama_jabatan ?? '-' }}</div>
                </div>
                <div class="list-right">
                    <div style="font-size:10px;color:#9ca3af;">{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d M Y') }}</div>
                    @if($k->status === 'aktif')
                    <span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;">Aktif</span>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:18px;color:#9ca3af;font-size:12px;">Belum ada data</div>
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

const trenData = @json($trenBulan);
new Chart(document.getElementById('trenChart'), {
    type: 'bar',
    data: {
        labels: trenData.map(d => d.bulan),
        datasets: [
            { label:'Promosi', data:trenData.map(d=>d.promosi), backgroundColor:'#16a34a', borderRadius:3 },
            { label:'Mutasi',  data:trenData.map(d=>d.mutasi),  backgroundColor:'#2563eb', borderRadius:3 },
            { label:'Demosi',  data:trenData.map(d=>d.demosi),  backgroundColor:'#ef4444', borderRadius:3 },
        ]
    },
    options: {
        responsive:true,
        plugins: {
            legend:{ position:'bottom', labels:{ boxWidth:10, padding:14, font:{size:11} } },
            tooltip:{ mode:'index', intersect:false },
        },
        scales: {
            x:{ grid:{display:false}, ticks:{font:{size:10}} },
            y:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{stepSize:1, font:{size:10}} },
        },
    }
});

const pieData = @json($assessmentChart);
new Chart(document.getElementById('pieChart'), {
    type:'doughnut',
    data: {
        labels: pieData.map(d=>d.label),
        datasets:[{ data:pieData.map(d=>d.value), backgroundColor:pieData.map(d=>d.color), borderWidth:2, borderColor:'#fff' }]
    },
    options:{ responsive:false, cutout:'65%', plugins:{ legend:{display:false} } }
});

new Chart(document.getElementById('genderChart'), {
    type:'doughnut',
    data: {
        labels:['Laki-laki','Perempuan'],
        datasets:[{ data:[{{ $genderChart['L'] }},{{ $genderChart['P'] }}], backgroundColor:['#2563eb','#ec4899'], borderWidth:2, borderColor:'#fff' }]
    },
    options:{ responsive:false, cutout:'65%', plugins:{ legend:{display:false} } }
});

// Chart Kompetensi — tren QUALIFIED vs NOT QUALIFIED
const kompTren = @json($trenKompetensi);
new Chart(document.getElementById('kompChart'), {
    type: 'bar',
    data: {
        labels: kompTren.map(d => d.bulan),
        datasets: [
            { label:'QUALIFIED',     data:kompTren.map(d=>d.qualified),     backgroundColor:'#15803d', borderRadius:3 },
            { label:'NOT QUALIFIED', data:kompTren.map(d=>d.not_qualified),  backgroundColor:'#ef4444', borderRadius:3 },
        ]
    },
    options: {
        responsive:true,
        plugins: {
            legend:{ position:'bottom', labels:{ boxWidth:10, padding:14, font:{size:11} } },
            tooltip:{ mode:'index', intersect:false },
        },
        scales: {
            x:{ grid:{display:false}, ticks:{font:{size:10}} },
            y:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{stepSize:1, font:{size:10}} },
        },
    }
});

new Chart(document.getElementById('kompPieChart'), {
    type:'doughnut',
    data: {
        labels:['QUALIFIED','NOT QUALIFIED'],
        datasets:[{
            data:[{{ $totalQualified }}, {{ $totalNotQualified }}],
            backgroundColor:['#15803d','#ef4444'],
            borderWidth:2, borderColor:'#fff'
        }]
    },
    options:{ responsive:false, cutout:'65%', plugins:{ legend:{display:false} } }
});

// SO Core vs Non Core Pie
new Chart(document.getElementById('soCorePie'), {
    type: 'doughnut',
    data: {
        labels: ['Core', 'Non Core'],
        datasets: [{
            data: [{{ $soCore }}, {{ $soNonCore }}],
            backgroundColor: ['#2563eb', '#7c3aed'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: false,
        cutout: '65%',
        plugins: { legend: { display: false } }
    }
});

</script>
@endpush