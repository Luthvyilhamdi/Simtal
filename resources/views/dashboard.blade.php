@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Beranda')

@push('styles')
<style>
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

    .kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px; }
    .kpi-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;transition:box-shadow 0.15s;position:relative;overflow:hidden; }
    .kpi-card { transition:box-shadow .15s, transform .15s; }
    .kpi-card:hover,.chart-card:hover,.demo-card:hover,.list-card:hover,.tabel-card:hover { box-shadow:var(--card-shadow-hover); }
    .kpi-card:hover { transform:translateY(-1px); }
    .chart-card,.demo-card,.list-card,.tabel-card { transition:box-shadow .15s; }
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
    .kpi-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .kpi-icon svg { width:20px;height:20px;fill:none;stroke-width:1.8; }
    .kpi-icon.green  { background:#f0fdf4; }
    .kpi-icon.blue   { background:#eff6ff; }
    .kpi-icon.purple { background:#f5f3ff; }
    .kpi-icon.amber  { background:#fffbeb; }
    .kpi-icon.teal   { background:#ecfeff; }

    /* Tab dashboard (hierarki) */
    .dash-tabs { display:flex;gap:2px;margin:2px 0 18px;border-bottom:1px solid var(--divider);flex-wrap:wrap; }
    .dash-tab-btn { border:none;background:none;font-family:inherit;font-size:13px;font-weight:600;color:var(--text-muted);padding:10px 16px;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;white-space:nowrap; }
    .dash-tab-btn:hover { color:var(--text); }
    .dash-tab-btn.active { color:var(--brand);border-bottom-color:var(--brand); }
    .dash-panel { display:none; }
    .dash-panel.active { display:block;animation:panelFade .25s ease; }
    @keyframes panelFade { from{opacity:0;transform:translateY(4px)} to{opacity:1;transform:none} }

    .sec-title { font-size:12px;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:0.6px;margin:4px 0 13px;display:flex;align-items:center;gap:9px; }
    .sec-title::before { content:'';width:3px;height:14px;background:var(--brand);border-radius:2px;flex-shrink:0; }
    .sec-title::after { content:'';flex:1;height:1px;background:var(--divider); }

    .chart-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px; }
    .chart-grid-3 { display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px; }
    .so-status-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:14px; }
    .chart-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px; }
    .chart-card-title { font-size:13px;font-weight:700;color:#111827;margin-bottom:3px; }
    .chart-card-sub { font-size:11px;color:#9ca3af;margin-bottom:14px; }

    .bar-chart { display:flex;flex-direction:column;gap:8px; }
    .bar-row { display:flex;align-items:center;gap:8px; }
    .bar-label { font-size:11px;color:#6b7280;font-weight:600;min-width:80px;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .bar-track { flex:1;height:20px;background:#f3f4f6;border-radius:5px;overflow:hidden; }
    .bar-fill { height:100%;border-radius:5px;display:flex;align-items:center;padding-left:7px;font-size:10px;font-weight:700;color:white;transition:width 0.8s ease;min-width:28px; }
    .bar-val { font-size:11px;font-weight:700;color:#374151;min-width:26px;text-align:right; }

    /* FIX: bar-fill warna via class statis */
    .bar-fill-blue   { background:#2563eb; }
    .bar-fill-purple { background:#7c3aed; }
    .bar-fill-brand  { background:var(--chart-brand); }  /* magnitude: 1 hue */
    /* FIX: bar-val deviasi warna via class statis */
    .bar-val-neg  { color:#ef4444; }
    .bar-val-pos  { color:#16a34a; }
    .bar-val-zero { color:#6b7280; }
    .bar-fill-neg  { background:#ef4444; }
    .bar-fill-pos  { background:#16a34a; }
    .bar-fill-zero { background:#6b7280; }
    .bar-val-wide  { min-width:76px;white-space:nowrap; }

    /* Kartu bisa diklik → buka detail popup */
    .chart-card.clickable { cursor:pointer;transition:box-shadow .15s,transform .15s; }
    .chart-card.clickable:hover { box-shadow:0 8px 24px rgba(16,24,40,.10);transform:translateY(-1px); }
    .cc-head { display:flex;justify-content:space-between;align-items:flex-start;gap:8px; }
    .cc-expand { width:15px;height:15px;color:#cbd5e1;flex-shrink:0;stroke-width:2;fill:none;stroke:currentColor; }
    .chart-card.clickable:hover .cc-expand { color:#15803d; }

    /* Popup detail */
    .dash-modal { position:fixed;inset:0;z-index:200;display:none;align-items:center;justify-content:center;padding:20px; }
    .dash-modal.open { display:flex; }
    .dash-modal-backdrop { position:absolute;inset:0;background:rgba(17,24,39,.5); }
    .dash-modal-card { position:relative;background:#fff;border-radius:14px;box-shadow:0 24px 60px rgba(0,0,0,.28);width:100%;max-width:640px;max-height:82vh;display:flex;flex-direction:column;overflow:hidden; }
    .dash-modal-head { padding:16px 20px;border-bottom:1px solid #eef0f2;display:flex;align-items:flex-start;justify-content:space-between;gap:12px; }
    .dash-modal-title { font-size:15px;font-weight:700;color:#111827; }
    .dash-modal-sub { font-size:12px;color:#6b7280;margin-top:2px; }
    .dash-modal-close { border:none;background:#f3f4f6;border-radius:8px;width:30px;height:30px;font-size:18px;line-height:1;cursor:pointer;color:#6b7280;flex-shrink:0; }
    .dash-modal-close:hover { background:#e5e7eb;color:#111827; }
    .dash-modal-body { overflow-y:auto;padding:6px 20px 18px; }
    .dash-tbl { width:100%;border-collapse:collapse;font-size:12.5px; }
    .dash-tbl th, .dash-tbl td { padding:8px 10px;text-align:left;border-bottom:1px solid #f1f3f5; }
    .dash-tbl th { position:sticky;top:0;background:#fff;color:#6b7280;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.03em; }
    .dash-tbl td.num, .dash-tbl th.num { text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap; }
    .dash-tbl tbody tr:hover td { background:#f9fafb; }
    .dash-pill { display:inline-block;padding:1px 7px;border-radius:999px;font-size:11px;font-weight:700; }
    .dash-pill-pos { background:#dcfce7;color:#15803d; }
    .dash-pill-neg { background:#fee2e2;color:#dc2626; }
    .dash-tbl tr.row-parent td { font-weight:700;color:#111827;background:#f6f8fa;border-top:1px solid #e5e7eb; }
    .dash-tbl td.child { padding-left:22px;color:#4b5563; }
    .dash-tbl td.child::before { content:'└';color:#cbd5e1;margin-right:6px; }

    .pie-legend { display:flex;flex-direction:column;gap:8px;flex:1; }
    .pie-item { display:flex;align-items:center;gap:7px; }
    .pie-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
    .pie-item-label { font-size:11px;color:#6b7280;flex:1; }
    .pie-item-val { font-size:12px;font-weight:700;color:#111827; }
    .pie-item-pct { font-size:10px;color:#9ca3af;margin-left:3px; }

    .demo-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px; }
    .demo-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px; }

    .so-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:18px; }
    .tp-grid { display:grid;grid-template-columns:1fr;gap:14px;margin-bottom:18px; }
    @media (min-width:880px) { .tp-grid { grid-template-columns:1fr 1fr; } }

    .pejabat-grid { display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:18px; }
    .pejabat-mini { background:white;border-radius:var(--radius-sm);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px;text-align:center;transition:box-shadow .15s,transform .15s; }
    .pejabat-mini:hover { box-shadow:var(--card-shadow-hover);transform:translateY(-1px); }
    .pejabat-num { font-size:26px;font-weight:800; }
    .pejabat-label { font-size:11px;font-weight:700;margin-top:3px;letter-spacing:0.5px; }
    .pejabat-sub { font-size:10px;color:#9ca3af;margin-top:2px; }

    .tabel-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden;margin-bottom:18px; }
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

    .bottom-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px; }
    .list-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
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

    .komp-stat-row { display:flex;align-items:center;gap:8px;margin-top:8px; }
    .komp-stat-item { flex:1;text-align:center;padding:8px 6px;border-radius:8px; }
    .komp-stat-num { font-size:20px;font-weight:800;line-height:1; }
    .komp-stat-label { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;margin-top:2px; }

    /* FIX: pejabat warna via class statis */
    .pejabat-svp { color:#d97706; }
    .pejabat-vp  { color:#1d4ed8; }
    .pejabat-spm { color:#7c3aed; }
    .pejabat-pm  { color:#15803d; }
    .pejabat-pgs { color:#0891b2; }
    .pejabat-pjs { color:#be185d; }

    /* FIX: pensiun warna via class statis */
    .pensiun-kritis { color:#ef4444; }
    .pensiun-warn   { color:#f59e0b; }
    .pensiun-normal { color:#6b7280; }

    @media (max-width:1024px) {
        .kpi-grid { grid-template-columns:repeat(2,1fr); }
        .chart-grid-2,.chart-grid-3 { grid-template-columns:1fr; }
        .so-status-grid { grid-template-columns:1fr; }
        .demo-grid { grid-template-columns:1fr; }
        .bottom-grid { grid-template-columns:1fr; }
        .pejabat-grid { grid-template-columns:repeat(3,1fr); }
    }
    @media (max-width:768px) {
        .welcome-card { padding:18px 16px; }
        .welcome-title { font-size:15px; }
        .welcome-date { font-size:11px;padding:5px 10px; }
        .kpi-grid { grid-template-columns:1fr 1fr;gap:8px; }
        .kpi-card { padding:14px 12px; }
        .kpi-num { font-size:22px; }
        .kpi-icon { width:36px;height:36px; }
        .kpi-icon svg { width:16px;height:16px; }
        .komp-stat-num { font-size:18px; }
        .komp-stat-row { gap:6px; }
        .so-grid { gap:8px; }
        .chart-grid-2,.chart-grid-3 { grid-template-columns:1fr;gap:10px; }
        .so-status-grid { grid-template-columns:1fr;gap:10px; }
        .chart-card { padding:14px; }
        .chart-card-title { font-size:12px; }
        .bar-label { min-width:60px;font-size:10px; }
        .bar-fill { font-size:9px; }
        .pejabat-grid { grid-template-columns:repeat(3,1fr);gap:8px; }
        .pejabat-mini { padding:10px 8px; }
        .pejabat-num { font-size:20px; }
        .pejabat-label { font-size:10px; }
        .tabel-card { overflow-x:auto; }
        table { font-size:11px; }
        thead th { padding:8px 10px; }
        tbody td { padding:8px 10px; }
        .bottom-grid { grid-template-columns:1fr;gap:10px; }
        .list-card-title { font-size:11px; }
        .list-name { font-size:11px; }
        .list-sub { font-size:9px; }
        .demo-grid { grid-template-columns:1fr; }
    }
    @media (max-width:640px) {
        .tabel-card table thead { display:none; }
        .tabel-card table, .tabel-card tbody, .tabel-card tr, .tabel-card td { display:block; width:100%; }
        .tabel-card tbody tr { border-bottom:8px solid #f9fafb; padding:14px 16px; }
        .tabel-card tbody tr:last-child { border-bottom:none; }
        .tabel-card tbody td { border-bottom:none; padding:5px 0; text-align:left !important; }
        .tabel-card tbody td.dir-name { font-size:14px; padding-bottom:8px; margin-bottom:4px; border-bottom:1px solid #f3f4f6; }
        .tabel-card tbody td[data-label]::before {
            content:attr(data-label); display:block; font-size:10px; font-weight:700; color:#9ca3af;
            text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;
        }
        .tabel-card .progress-mini { max-width:160px; }
    }
    @media (max-width:480px) {
        .kpi-grid { grid-template-columns:1fr 1fr;gap:6px; }
        .kpi-card { padding:12px 10px; }
        .kpi-num { font-size:20px; }
        .kpi-label { font-size:9px; }
        .kpi-sub { font-size:10px; }
        .kpi-icon { width:30px;height:30px; }
        .kpi-icon svg { width:15px;height:15px; }
        .welcome-card { padding:14px 12px; }
        .welcome-title { font-size:14px; }
        .welcome-pills { gap:4px; }
        .welcome-pill { font-size:10px;padding:2px 8px; }
        .welcome-sub { display:none; }
        .pejabat-grid { grid-template-columns:repeat(3,1fr);gap:6px; }
        .pejabat-num { font-size:18px; }
        .sec-title { font-size:11px; }
        .bar-label { min-width:50px; }
        .komp-stat-num { font-size:15px; }
        .komp-stat-label { font-size:8px; letter-spacing:0; }
        .komp-stat-item { padding:6px 3px; }
        .komp-stat-row { gap:5px; }
    }
</style>
@endpush

@section('content')

{{-- Data untuk Chart.js — di luar script agar tidak ada Blade di dalam script --}}
<div id="dashMeta"
    data-tren-bulan='@json($trenBulan)'
    data-assessment-chart='@json($assessmentChart)'
    data-tren-kompetensi='@json($trenKompetensi)'
    data-gender-l="{{ $genderChart['L'] }}"
    data-gender-p="{{ $genderChart['P'] }}"
    data-total-qualified="{{ $totalQualified }}"
    data-total-not-qualified="{{ $totalNotQualified }}"
    data-so-core="{{ $soCore }}"
    data-so-non-core="{{ $soNonCore }}"
    style="display:none"></div>

@php
$icoUsers     = '<svg viewBox="0 0 24 24" stroke="#15803d"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
$icoTrendDash = '<svg viewBox="0 0 24 24" stroke="#1d4ed8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>';
$icoClipDash  = '<svg viewBox="0 0 24 24" stroke="#7c3aed"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>';
$icoStarDash  = '<svg viewBox="0 0 24 24" stroke="#0891b2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
@endphp

{{-- WELCOME --}}
@php
$roleDotDash = auth()->user()->isSuperAdmin() ? '#4ade80' : (auth()->user()->isAdmin() ? '#93c5fd' : '#d1d5db');
$roleNameDash = auth()->user()->isSuperAdmin() ? 'Super Admin' : (auth()->user()->isAdmin() ? 'Administrator' : 'User');
@endphp
<div class="welcome-card">
    <div style="position:relative;z-index:1;">
        <div class="welcome-title">Selamat Datang, {{ auth()->user()->name }}</div>
        <div class="welcome-sub">Sistem Manajemen Talenta — Ringkasan data per hari ini</div>
        <div class="welcome-pills">
            <span class="welcome-pill pill-green">{{ $karyawanAktif }} Karyawan Aktif</span>
            <span class="welcome-pill pill-blue">{{ $pejabatAktif }} Pejabat Aktif</span>
            <span class="welcome-pill pill-amber">{{ $pgsAktif + $pjsAktif }} PGS/PJS Aktif</span>
        </div>
    </div>
    <div style="position:relative;z-index:1;display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
        <div class="welcome-date">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </div>
        <div style="font-size:11px;color:rgba(255,255,255,0.55);display:flex;align-items:center;gap:5px;">
            <span style="width:6px;height:6px;border-radius:50%;background:{{ $roleDotDash }};display:inline-block"></span>
            {{ $roleNameDash }}
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
        <div class="kpi-icon green">{!! $icoUsers !!}</div>
    </div>
    <div class="kpi-card blue">
        <div class="kpi-left">
            <div class="kpi-label">Pergerakan Jabatan</div>
            <div class="kpi-num">{{ $promosiThisYear + $mutasiThisYear + $demosiThisYear }}</div>
            <div class="kpi-sub">Tahun {{ now()->year }}</div>
            <span class="kpi-badge" style="background:#dbeafe;color:#1d4ed8;">{{ $promosiThisYear }} promosi · {{ $mutasiThisYear }} mutasi</span>
        </div>
        <div class="kpi-icon blue">{!! $icoTrendDash !!}</div>
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
                <div class="komp-stat-item" style="background:#fef3c7;">
                    <div class="komp-stat-num" style="color:#d97706;">{{ $assessmentRWD }}</div>
                    <div class="komp-stat-label" style="color:#d97706;">R. Dev</div>
                </div>
                <div class="komp-stat-item" style="background:#fee2e2;">
                    <div class="komp-stat-num" style="color:#dc2626;">{{ $assessmentNR }}</div>
                    <div class="komp-stat-label" style="color:#dc2626;">Not Ready</div>
                </div>
            </div>
        </div>
        <div class="kpi-icon purple">{!! $icoClipDash !!}</div>
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
        </div>
        <div class="kpi-icon teal">{!! $icoStarDash !!}</div>
    </div>
</div>

{{-- ===== TAB HIERARKI ===== --}}
<div class="dash-tabs" role="tablist">
    <button class="dash-tab-btn active" data-tab="ringkasan" type="button">Ringkasan</button>
    <button class="dash-tab-btn" data-tab="analitik" type="button">Analitik &amp; Grafik</button>
    <button class="dash-tab-btn" data-tab="pejabat" type="button">Pejabat &amp; Direktorat</button>
    <button class="dash-tab-btn" data-tab="pemantauan" type="button">Pemantauan</button>
</div>
<div class="dash-panels">
<section class="dash-panel active" data-tab="ringkasan">

{{-- SO CORE & NON CORE --}}
@php
$namaBulanDash = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$pctTerisi  = $soTotalMc > 0 ? round(($soTerisi/$soTotalMc)*100) : 0;
$pctCoreTerisi    = $soCoreMc > 0 ? round(($soCoreTerisi/$soCoreMc)*100) : 0;
$pctNonCoreTerisi = $soNonCoreMc > 0 ? round(($soNonCoreTerisi/$soNonCoreMc)*100) : 0;
@endphp
<div class="sec-title">Struktur Organisasi — {{ $namaBulanDash[$soBulan] }} {{ $soTahun }}</div>
<div class="so-grid">

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
    {{-- FIX: width Blade diganti data-pct + apply via JS --}}
    <div style="height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden;margin-top:4px;width:100%">
      <div class="progress-mini-fill" data-pct="{{ $pctTerisi }}" style="background:#16a34a;height:100%;border-radius:20px;"></div>
    </div>
  </div>

  <div class="kpi-card" style="border-top:3px solid #2563eb;flex-direction:column;align-items:flex-start;gap:6px">
    <div class="kpi-label" style="color:#9ca3af">Core — Keterisian</div>
    <div class="kpi-num" style="color:#2563eb">{{ $soCoreTerisi }} <span style="font-size:14px;color:#9ca3af">/ {{ $soCoreMc }}</span></div>
    <div class="kpi-sub">{{ $pctCoreTerisi }}% terisi · {{ $soCore }} posisi Core</div>
    <div style="height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden;margin-top:4px;width:100%">
      <div class="progress-mini-fill" data-pct="{{ $pctCoreTerisi }}" style="background:#2563eb;height:100%;border-radius:20px;"></div>
    </div>
  </div>

  <div class="kpi-card" style="border-top:3px solid #7c3aed;flex-direction:column;align-items:flex-start;gap:6px">
    <div class="kpi-label" style="color:#9ca3af">Non Core — Keterisian</div>
    <div class="kpi-num" style="color:#7c3aed">{{ $soNonCoreTerisi }} <span style="font-size:14px;color:#9ca3af">/ {{ $soNonCoreMc }}</span></div>
    <div class="kpi-sub">{{ $pctNonCoreTerisi }}% terisi · {{ $soNonCore }} posisi Non Core</div>
    <div style="height:4px;background:#f3f4f6;border-radius:20px;overflow:hidden;margin-top:4px;width:100%">
      <div class="progress-mini-fill" data-pct="{{ $pctNonCoreTerisi }}" style="background:#7c3aed;height:100%;border-radius:20px;"></div>
    </div>
  </div>

</div>

{{-- SO CHARTS --}}
<div class="chart-card" style="margin-bottom:18px">
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

{{-- SO STATUS PENGISIAN: DIREKTORAT, KOMPARTEMEN, DEPARTEMEN --}}
<div class="so-status-grid" style="margin-bottom:18px">
    @foreach([
        ['key' => 'status-dir',  'label' => 'Status Pengisian per Direktorat',  'data' => $soPerDirektorat,  'sub' => 'Posisi MC/TKO tersedia: belum terisi vs total tersedia', 'strip' => true],
        ['key' => 'status-komp', 'label' => 'Status Pengisian per Kompartemen', 'data' => $soPerKompartemen, 'sub' => 'Diurutkan dari yang belum terisi terbanyak', 'strip' => false],
        ['key' => 'status-dept', 'label' => 'Status Pengisian per Departemen',  'data' => $soPerDepartemen,  'sub' => 'Diurutkan dari yang belum terisi terbanyak', 'strip' => false],
    ] as $grp)
    <div class="chart-card clickable" onclick="openDashModal('{{ $grp['key'] }}')">
        <div class="cc-head">
            <div>
                <div class="chart-card-title">{{ $grp['label'] }}</div>
                <div class="chart-card-sub">{{ $grp['sub'] }}</div>
            </div>
            <svg class="cc-expand" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
        </div>
        @php $maxKosongGrp = $grp['data']->max('belum_terisi') ?: 1; @endphp
        <div class="bar-chart" style="margin-top:6px;max-height:290px;overflow-y:auto;padding-right:4px">
            @forelse($grp['data'] as $d)
            @php
                $kosong    = (int) $d->belum_terisi;
                $barW      = $kosong > 0 ? min(100, round($kosong / $maxKosongGrp * 100)) : 0;
                $devClass  = $kosong > 0 ? 'neg' : 'pos';
                $pctIsi    = $d->tersedia > 0 ? round($d->terisi / $d->tersedia * 100) : 0;
                $shortName = $grp['strip']
                    ? Str::limit(str_replace(['Direktorat ', 'Directorat '], '', $d->nama), 18)
                    : Str::limit($d->nama, 18);
            @endphp
            <div class="bar-row">
                <div class="bar-label" title="{{ $d->nama }}">{{ $shortName }}</div>
                <div class="bar-track">
                    <div class="bar-fill bar-fill-{{ $devClass }}" data-pct="{{ $barW }}">{{ $kosong }}</div>
                </div>
                <div class="bar-val bar-val-wide" style="color:#9ca3af">{{ $d->terisi }}/{{ $d->tersedia }} · {{ $pctIsi }}%</div>
            </div>
            @empty
            <div style="font-size:12px;color:#9ca3af;text-align:center;padding:12px 0">Tidak ada data</div>
            @endforelse
        </div>
    </div>

    {{-- Popup detail status pengisian --}}
    <div class="dash-modal" id="dashmodal-{{ $grp['key'] }}">
        <div class="dash-modal-backdrop" onclick="closeDashModal(this)"></div>
        <div class="dash-modal-card">
            <div class="dash-modal-head">
                <div>
                    <div class="dash-modal-title">{{ $grp['label'] }}</div>
                    <div class="dash-modal-sub">Periode {{ \App\Http\Controllers\ExportBuilderController::BULAN[$soBulan] ?? $soBulan }} {{ $soTahun }} · posisi dengan MC/TKO tersedia</div>
                </div>
                <button type="button" class="dash-modal-close" onclick="closeDashModal(this)">&times;</button>
            </div>
            <div class="dash-modal-body">
                <table class="dash-tbl">
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th class="num">Terisi</th>
                            <th class="num">Tersedia</th>
                            <th class="num">Belum</th>
                            <th class="num">% Terisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($soDrill[$grp['key']]))
                            @forelse($soDrill[$grp['key']] as $p)
                            @php $pPct = $p['tersedia'] > 0 ? round($p['terisi'] / $p['tersedia'] * 100) : 0; @endphp
                            <tr class="row-parent">
                                <td>{{ $p['nama'] }}</td>
                                <td class="num">{{ $p['terisi'] }}</td>
                                <td class="num">{{ $p['tersedia'] }}</td>
                                <td class="num">{{ $p['belum'] }}</td>
                                <td class="num"><span class="dash-pill {{ $p['belum'] > 0 ? 'dash-pill-neg' : 'dash-pill-pos' }}">{{ $pPct }}%</span></td>
                            </tr>
                            @foreach($p['children'] as $c)
                            @php $cPct = $c['tersedia'] > 0 ? round($c['terisi'] / $c['tersedia'] * 100) : 0; @endphp
                            <tr>
                                <td class="child">{{ $c['nama'] }}</td>
                                <td class="num">{{ $c['terisi'] }}</td>
                                <td class="num">{{ $c['tersedia'] }}</td>
                                <td class="num">{{ $c['belum'] }}</td>
                                <td class="num"><span class="dash-pill {{ $c['belum'] > 0 ? 'dash-pill-neg' : 'dash-pill-pos' }}">{{ $cPct }}%</span></td>
                            </tr>
                            @endforeach
                            @empty
                            <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:14px">Tidak ada data</td></tr>
                            @endforelse
                        @else
                            @forelse($grp['data'] as $d)
                            @php $pctIsi = $d->tersedia > 0 ? round($d->terisi / $d->tersedia * 100) : 0; @endphp
                            <tr>
                                <td>{{ $d->nama }}</td>
                                <td class="num">{{ $d->terisi }}</td>
                                <td class="num">{{ $d->tersedia }}</td>
                                <td class="num">{{ $d->belum_terisi }}</td>
                                <td class="num"><span class="dash-pill {{ $d->belum_terisi > 0 ? 'dash-pill-neg' : 'dash-pill-pos' }}">{{ $pctIsi }}%</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:14px">Tidak ada data</td></tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- SO TOTAL PENGISIAN (headcount) PER DIREKTORAT, KOMPARTEMEN, DEPARTEMEN --}}
<div class="so-status-grid" style="margin-bottom:18px">
    @foreach([
        ['key' => 'terisi-dir',  'label' => 'Total Terisi per Direktorat',  'data' => $soTerisiDirektorat,  'strip' => true],
        ['key' => 'terisi-komp', 'label' => 'Total Terisi per Kompartemen', 'data' => $soTerisiKompartemen, 'strip' => false],
        ['key' => 'terisi-dept', 'label' => 'Total Terisi per Departemen',  'data' => $soTerisiDepartemen,  'strip' => false],
    ] as $grp)
    <div class="chart-card clickable" onclick="openDashModal('{{ $grp['key'] }}')">
        <div class="cc-head">
            <div>
                <div class="chart-card-title">{{ $grp['label'] }}</div>
                <div class="chart-card-sub">Jumlah pengisian (headcount) yang terisi vs target MC/TKO</div>
            </div>
            <svg class="cc-expand" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
        </div>
        @php $maxTerisiGrp = $grp['data']->max('total_terisi') ?: 1; @endphp
        <div class="bar-chart" style="margin-top:6px;max-height:290px;overflow-y:auto;padding-right:4px">
            @forelse($grp['data'] as $d)
            @php
                $terisi    = (int) $d->total_terisi;
                $mc        = (int) $d->total_mc;
                $barW      = $terisi > 0 ? min(100, round($terisi / $maxTerisiGrp * 100)) : 0;
                $pctMc     = $mc > 0 ? round($terisi / $mc * 100) : null;
                $shortName = $grp['strip']
                    ? Str::limit(str_replace(['Direktorat ', 'Directorat '], '', $d->nama), 18)
                    : Str::limit($d->nama, 18);
            @endphp
            <div class="bar-row">
                <div class="bar-label" title="{{ $d->nama }}">{{ $shortName }}</div>
                <div class="bar-track">
                    <div class="bar-fill bar-fill-brand" data-pct="{{ $barW }}">{{ $terisi }}</div>
                </div>
                <div class="bar-val bar-val-wide" style="color:#9ca3af">{{ $terisi }}/{{ $mc }} · {{ $pctMc !== null ? $pctMc.'%' : '–' }}</div>
            </div>
            @empty
            <div style="font-size:12px;color:#9ca3af;text-align:center;padding:12px 0">Tidak ada data</div>
            @endforelse
        </div>
    </div>

    {{-- Popup detail total terisi --}}
    <div class="dash-modal" id="dashmodal-{{ $grp['key'] }}">
        <div class="dash-modal-backdrop" onclick="closeDashModal(this)"></div>
        <div class="dash-modal-card">
            <div class="dash-modal-head">
                <div>
                    <div class="dash-modal-title">{{ $grp['label'] }}</div>
                    <div class="dash-modal-sub">Periode {{ \App\Http\Controllers\ExportBuilderController::BULAN[$soBulan] ?? $soBulan }} {{ $soTahun }} · headcount terisi vs target MC/TKO</div>
                </div>
                <button type="button" class="dash-modal-close" onclick="closeDashModal(this)">&times;</button>
            </div>
            <div class="dash-modal-body">
                <table class="dash-tbl">
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th class="num">Terisi</th>
                            <th class="num">Target MC</th>
                            <th class="num">% Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($soDrill[$grp['key']]))
                            @forelse($soDrill[$grp['key']] as $p)
                            @php $pPct = $p['total_mc'] > 0 ? round($p['total_terisi'] / $p['total_mc'] * 100) : null; @endphp
                            <tr class="row-parent">
                                <td>{{ $p['nama'] }}</td>
                                <td class="num">{{ $p['total_terisi'] }}</td>
                                <td class="num">{{ $p['total_mc'] }}</td>
                                <td class="num">
                                    @if($pPct === null)<span style="color:#9ca3af">–</span>
                                    @else<span class="dash-pill {{ $pPct >= 100 ? 'dash-pill-pos' : 'dash-pill-neg' }}">{{ $pPct }}%</span>@endif
                                </td>
                            </tr>
                            @foreach($p['children'] as $c)
                            @php $cPct = $c['total_mc'] > 0 ? round($c['total_terisi'] / $c['total_mc'] * 100) : null; @endphp
                            <tr>
                                <td class="child">{{ $c['nama'] }}</td>
                                <td class="num">{{ $c['total_terisi'] }}</td>
                                <td class="num">{{ $c['total_mc'] }}</td>
                                <td class="num">
                                    @if($cPct === null)<span style="color:#9ca3af">–</span>
                                    @else<span class="dash-pill {{ $cPct >= 100 ? 'dash-pill-pos' : 'dash-pill-neg' }}">{{ $cPct }}%</span>@endif
                                </td>
                            </tr>
                            @endforeach
                            @empty
                            <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:14px">Tidak ada data</td></tr>
                            @endforelse
                        @else
                            @forelse($grp['data'] as $d)
                            @php
                                $terisi = (int) $d->total_terisi; $mc = (int) $d->total_mc;
                                $pctMc  = $mc > 0 ? round($terisi / $mc * 100) : null;
                            @endphp
                            <tr>
                                <td>{{ $d->nama }}</td>
                                <td class="num">{{ $terisi }}</td>
                                <td class="num">{{ $mc }}</td>
                                <td class="num">
                                    @if($pctMc === null)<span style="color:#9ca3af">–</span>
                                    @else<span class="dash-pill {{ $pctMc >= 100 ? 'dash-pill-pos' : 'dash-pill-neg' }}">{{ $pctMc }}%</span>@endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:14px">Tidak ada data</td></tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
    function openDashModal(key) {
        const m = document.getElementById('dashmodal-' + key);
        if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
    }
    function closeDashModal(el) {
        const m = el.closest('.dash-modal');
        if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dash-modal.open').forEach(m => m.classList.remove('open'));
            document.body.style.overflow = '';
        }
    });
</script>

{{-- TALENT POOL — otomatis mengikuti 2 periode terbaru yang ada datanya --}}
<div class="sec-title">Talent Pool</div>
@if($talentPool['utama'])
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;margin-bottom:18px">

    @foreach(array_filter([$talentPool['utama'], $talentPool['kedua']]) as $idx => $tp)
    @php $utama = $idx === 0; @endphp
    <div style="background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div style="font-size:13px;font-weight:700;color:#111827">Talent Pool {{ $tp['periode'] }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $utama ? 'Periode terbaru' : 'Periode sebelumnya' }}</div>
            </div>
            <a href="{{ route('talent_pool.index', ['periode' => $tp['periode']]) }}"
               style="font-size:11px;color:{{ $utama ? '#15803d' : '#6b7280' }};font-weight:600;text-decoration:none">Lihat →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
            <div style="text-align:center;background:{{ $utama ? '#f0fdf4' : '#f9fafb' }};border-radius:10px;padding:14px 8px">
                <div style="font-size:26px;font-weight:800;color:{{ $utama ? '#15803d' : '#374151' }}">{{ $tp['total'] }}</div>
                <div style="font-size:10px;font-weight:700;color:{{ $utama ? '#15803d' : '#6b7280' }};text-transform:uppercase;letter-spacing:0.3px;margin-top:3px">Total</div>
            </div>
            <div style="text-align:center;background:{{ $utama ? '#eff6ff' : '#f9fafb' }};border-radius:10px;padding:14px 8px">
                <div style="font-size:26px;font-weight:800;color:{{ $utama ? '#1d4ed8' : '#374151' }}">{{ $tp['longlist'] }}</div>
                <div style="font-size:10px;font-weight:700;color:{{ $utama ? '#1d4ed8' : '#6b7280' }};text-transform:uppercase;letter-spacing:0.3px;margin-top:3px">Longlist</div>
            </div>
            <div style="text-align:center;background:{{ $utama ? '#dcfce7' : '#f9fafb' }};border-radius:10px;padding:14px 8px">
                <div style="font-size:26px;font-weight:800;color:{{ $utama ? '#15803d' : '#374151' }}">{{ $tp['shortlist'] }}</div>
                <div style="font-size:10px;font-weight:700;color:{{ $utama ? '#15803d' : '#6b7280' }};text-transform:uppercase;letter-spacing:0.3px;margin-top:3px">Shortlist</div>
            </div>
        </div>
        @if($tp['total'] > 0)
        @php $pct = round(($tp['shortlist'] / $tp['total']) * 100); @endphp
        <div style="margin-top:14px">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:#6b7280;margin-bottom:4px">
                <span>Shortlist rate</span>
                <span style="font-weight:700;color:{{ $utama ? '#15803d' : '#6b7280' }}">{{ $pct }}%</span>
            </div>
            <div style="height:5px;background:#f3f4f6;border-radius:20px;overflow:hidden">
                <div class="progress-mini-fill" data-pct="{{ $pct }}" style="background:{{ $utama ? '#15803d' : '#9ca3af' }};height:100%;border-radius:20px;"></div>
            </div>
        </div>
        @endif
    </div>
    @endforeach

</div>
@else
<div style="background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:28px;text-align:center;color:#9ca3af;font-size:13px;margin-bottom:18px">
    Belum ada data Talent Pool. Ringkasan akan muncul otomatis setelah periode talent diinput.
</div>
@endif

{{-- REMINDER PROMOSI --}}
@if(!auth()->user()->isUser())
<div class="sec-title">Reminder Promosi</div>
<div style="background:linear-gradient(120deg,#14532d,#166534);border-radius:14px;padding:20px 22px;margin-bottom:18px;display:flex;align-items:center;gap:22px;flex-wrap:wrap;color:#fff;box-shadow:0 8px 24px rgba(20,83,45,0.18)">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,0.14);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.9"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div>
            <div style="font-size:28px;font-weight:800;line-height:1">{{ $reminderEligibleNow }}</div>
            <div style="font-size:12px;color:#bbf7d0;margin-top:3px">karyawan siap diusulkan promosi (MDG terpenuhi)</div>
        </div>
    </div>
    <div style="height:40px;width:1px;background:rgba(255,255,255,0.2)"></div>
    <div>
        <div style="font-size:22px;font-weight:800;line-height:1">{{ $reminderSoon }}</div>
        <div style="font-size:12px;color:#bbf7d0;margin-top:3px">akan memenuhi ≤ 3 bulan</div>
    </div>
    <a href="{{ route('reminder_promosi.index') }}" style="margin-left:auto;background:#fff;color:#15803d;padding:10px 18px;border-radius:9px;font-size:13px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:7px;white-space:nowrap">
        Lihat Reminder Promosi
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
</div>
@endif

</section>
<section class="dash-panel" data-tab="analitik">

{{-- GRAFIK --}}
<div class="sec-title">Analitik & Grafik</div>
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
                    {{-- FIX: style Blade diganti data-color + apply via JS --}}
                    <div class="pie-dot" data-color="{{ $a['color'] }}"></div>
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
        <div class="chart-card-title">Distribusi per Job Grade</div>
        <div class="chart-card-sub">Jumlah karyawan aktif (JG tertinggi → terendah)</div>
        @php $maxJG = $distribusiJobGrade->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @foreach($distribusiJobGrade as $j)
            @php $pctJG = round(($j['total']/$maxJG)*100); @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $j['nama'] }}</div>
                <div class="bar-track">
                    <div class="bar-fill bar-fill-brand" data-pct="{{ $pctJG }}">{{ $j['total'] }}</div>
                </div>
                <div class="bar-val">{{ $j['total'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-card-title">Distribusi per Person Grade</div>
        <div class="chart-card-sub">Jumlah karyawan aktif (PG tertinggi → terendah)</div>
        @php $maxPG = $distribusiPersonGrade->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @forelse($distribusiPersonGrade as $pg)
            @php $pctPG = round(($pg['total']/$maxPG)*100); @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $pg['nama'] }}</div>
                <div class="bar-track">
                    <div class="bar-fill bar-fill-brand" data-pct="{{ $pctPG }}">{{ $pg['total'] }}</div>
                </div>
                <div class="bar-val">{{ $pg['total'] }}</div>
            </div>
            @empty
            <div style="text-align:center;padding:14px;color:#9ca3af;font-size:12px;">Belum ada data person grade</div>
            @endforelse
        </div>
    </div>
</div>

{{-- DISTRIBUSI BAND & PENDIDIKAN --}}
<div class="chart-grid-2">
    <div class="chart-card">
        <div class="chart-card-title">Distribusi per Band</div>
        <div class="chart-card-sub">Jumlah karyawan aktif per Band</div>
        @php $maxBand = collect($distribusiBand)->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @foreach($distribusiBand as $b)
            @php $pctB = round(($b['total'] / $maxBand) * 100); @endphp
            <div class="bar-row">
                <div class="bar-label" title="{{ $b['nama'] }}">{{ $b['nama'] }}</div>
                <div class="bar-track"><div class="bar-fill bar-fill-brand" data-pct="{{ $pctB }}">{{ $b['total'] }}</div></div>
                <div class="bar-val">{{ $b['total'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-card-title">Distribusi Pendidikan</div>
        <div class="chart-card-sub">Jenjang pendidikan karyawan aktif</div>
        @php $maxPend = collect($distribusiPendidikan)->max('total') ?: 1; @endphp
        <div class="bar-chart">
            @forelse($distribusiPendidikan as $p)
            @php $pctP = round(($p['total'] / $maxPend) * 100); @endphp
            <div class="bar-row">
                <div class="bar-label" title="{{ $p['nama'] }}">{{ $p['nama'] }}</div>
                <div class="bar-track"><div class="bar-fill bar-fill-brand" data-pct="{{ $pctP }}">{{ $p['total'] }}</div></div>
                <div class="bar-val">{{ $p['total'] }}</div>
            </div>
            @empty
            <div style="text-align:center;padding:14px;color:#9ca3af;font-size:12px;">Belum ada data pendidikan</div>
            @endforelse
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
            @php
                // Magnitude → satu hue hijau (disiplin warna, teks putih tetap terbaca)
                $usiaColor = '#16a34a';
                $pctUsia   = round(($val/$maxUsia)*100);
            @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $label }} thn</div>
                <div class="bar-track">
                    {{-- FIX: width dan background Blade diganti data-pct + data-color --}}
                    <div class="bar-fill" data-pct="{{ $pctUsia }}" data-color="{{ $usiaColor }}">{{ $val }}</div>
                </div>
                <div class="bar-val">{{ $val }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

</section>
<section class="dash-panel" data-tab="pejabat">

{{-- PEJABAT --}}
<div class="sec-title">Statistik Pejabat</div>
<div class="pejabat-grid">
    @foreach([['SVP',$pejabatSVP,'svp'],['VP',$pejabatVP,'vp'],['SPM',$pejabatSPM,'spm'],['PM',$pejabatPM,'pm'],['PGS',$pgsAktif,'pgs'],['PJS',$pjsAktif,'pjs']] as [$label,$val,$cls])
    <div class="pejabat-mini">
        {{-- FIX: style Blade diganti class statis per jabatan --}}
        <div class="pejabat-num pejabat-{{ $cls }}">{{ $val }}</div>
        <div class="pejabat-label pejabat-{{ $cls }}">{{ $label }}</div>
        <div class="pejabat-sub">Aktif</div>
    </div>
    @endforeach
</div>

{{-- RINGKASAN DIREKTORAT --}}
<div class="sec-title">Ringkasan per Direktorat</div>
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
                    <td class="dir-name" style="font-weight:600;color:#111827;">{{ $r['nama'] }}</td>
                    <td class="center" data-label="Total">{{ $r['total'] }}</td>
                    <td class="center" data-label="Aktif"><span style="background:#dcfce7;color:#15803d;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['aktif'] }}</span></td>
                    <td class="center" data-label="Proporsi" style="min-width:90px;">
                        <div style="font-size:10px;color:#6b7280;margin-bottom:2px;">{{ $proporsi }}%</div>
                        <div class="progress-mini"><div class="progress-mini-fill" data-pct="{{ $proporsi }}" style="background:#2563eb;"></div></div>
                    </td>
                    <td class="center" data-label="Promosi {{ now()->year }}">
                        @if($r['promosi'] > 0)<span style="background:#dcfce7;color:#15803d;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['promosi'] }}</span>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center" data-label="Mutasi {{ now()->year }}">
                        @if($r['mutasi'] > 0)<span style="background:#dbeafe;color:#1d4ed8;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['mutasi'] }}</span>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center" data-label="Assessment">{{ $r['assessment'] ?: '—' }}</td>
                    <td class="center" data-label="Ready">
                        @if($r['ready'] > 0)<span style="background:#dcfce7;color:#15803d;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;">{{ $r['ready'] }}</span>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center" data-label="Ready Rate" style="min-width:80px;">
                        @if($r['assessment'] > 0)
                            <div style="font-size:10px;color:#6b7280;margin-bottom:2px;">{{ $readyRate }}%</div>
                            <div class="progress-mini"><div class="progress-mini-fill" data-pct="{{ $readyRate }}" style="background:#16a34a;"></div></div>
                        @else<span style="color:#d1d5db;">—</span>@endif
                    </td>
                    <td class="center" data-label="Qualified">
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

</section>
<section class="dash-panel" data-tab="pemantauan">

{{-- PEMANTAUAN --}}
<div class="sec-title">Pemantauan & Aktivitas</div>
<div class="bottom-grid">
    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">Aktivitas Jabatan Terbaru</div>
            <a href="{{ route('history_karyawan.index') }}" class="view-all">Lihat Semua →</a>
        </div>
        <div class="list-card-body">
            @forelse($aktivitasTerbaru as $a)
            <div class="list-item">
                <div class="list-avatar">
                    @if($a->karyawan->foto)<img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                    @else{{ initials($a->karyawan->nama) }}@endif
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
            <div class="list-card-title">Mendekati Pensiun</div>
            <span style="font-size:10px;color:#9ca3af;">Usia ≥ 53 tahun</span>
        </div>
        <div class="list-card-body">
            @forelse($akanPensiun as $k)
            @php
                $usia       = \Carbon\Carbon::parse($k->tanggal_lahir)->age;
                $sisaTahun  = 56 - $usia;
                $pensiunClass = $sisaTahun <= 1 ? 'pensiun-kritis' : ($sisaTahun <= 2 ? 'pensiun-warn' : 'pensiun-normal');
            @endphp
            <div class="list-item">
                {{-- FIX: style color Blade diganti class statis --}}
                <div style="font-size:16px;font-weight:800;min-width:32px;text-align:center;" class="{{ $pensiunClass }}">{{ $sisaTahun }}th</div>
                <div class="list-avatar">
                    @if($k->foto)<img src="{{ Storage::url($k->foto) }}" alt="">
                    @else{{ initials($k->nama) }}@endif
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
            <div style="text-align:center;padding:18px;color:#9ca3af;font-size:12px;">Tidak ada</div>
            @endforelse
        </div>
    </div>

    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">Assessment Akan Expire</div>
            <span style="font-size:10px;color:#9ca3af;">30 hari ke depan</span>
        </div>
        <div class="list-card-body">
            @forelse($assessmentExpire as $a)
            <div class="list-item">
                <div style="font-size:14px;font-weight:800;color:#ef4444;min-width:30px;text-align:center;">{{ (int) now()->diffInDays($a->tanggal_exp_idp) }}h</div>
                <div class="list-avatar" style="background:#fef2f2;color:#dc2626;">
                    @if($a->karyawan->foto)<img src="{{ Storage::url($a->karyawan->foto) }}" alt="">
                    @else{{ initials($a->karyawan->nama) }}@endif
                </div>
                <div>
                    <div class="list-name">{{ $a->karyawan->nama }}</div>
                    <div class="list-sub">Exp: {{ $a->tanggal_exp_idp->format('d M Y') }}</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:18px;color:#9ca3af;font-size:12px;">Tidak ada</div>
            @endforelse
        </div>
    </div>

    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">Karyawan Terbaru</div>
            <a href="{{ route('karyawan.index') }}" class="view-all">Lihat Semua →</a>
        </div>
        <div class="list-card-body">
            @forelse($karyawanTerbaru as $k)
            <div class="list-item">
                <div class="list-avatar">
                    @if($k->foto)<img src="{{ Storage::url($k->foto) }}" alt="">
                    @else{{ initials($k->nama) }}@endif
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

    <div class="list-card">
        <div class="list-card-header">
            <div class="list-card-title">Ulang Tahun Bulan Ini</div>
            <span style="font-size:10px;color:#9ca3af;">{{ now()->translatedFormat('F') }}</span>
        </div>
        <div class="list-card-body">
            @forelse($ulangTahunBulanIni as $k)
            <div class="list-item">
                <div style="font-size:12px;font-weight:800;color:#db2777;min-width:38px;text-align:center;">{{ \Carbon\Carbon::parse($k->tanggal_lahir)->format('d M') }}</div>
                <div>
                    <div class="list-name">{{ $k->nama }}</div>
                    <div class="list-sub">{{ $k->jabatan_saat_ini ?? '-' }} · {{ \Carbon\Carbon::parse($k->tanggal_lahir)->age }} thn</div>
                </div>
                <div class="list-right">
                    <a href="{{ route('karyawan.show', $k) }}" style="font-size:11px;color:#16a34a;text-decoration:none;font-weight:600;">Detail →</a>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:18px;color:#9ca3af;font-size:12px;">Tidak ada yang berulang tahun bulan ini</div>
            @endforelse
        </div>
    </div>
</div>
</section>{{-- /dash-panel pemantauan --}}
</div>{{-- /dash-panels --}}

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
// Tab dashboard: ganti panel + picu resize supaya chart yang tadinya tersembunyi ikut terukur
document.querySelectorAll('.dash-tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var t = this.dataset.tab;
        document.querySelectorAll('.dash-tab-btn').forEach(function (b) { b.classList.toggle('active', b.dataset.tab === t); });
        document.querySelectorAll('.dash-panel').forEach(function (p) { p.classList.toggle('active', p.dataset.tab === t); });
        window.dispatchEvent(new Event('resize')); // Chart.js re-measure
    });
});
</script>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#6b7280';

// Baca semua data dari dashMeta data-* (menghindari Blade directive di dalam script)
var _m              = document.getElementById('dashMeta');
var trenData        = JSON.parse(_m.dataset.trenBulan);
var pieData         = JSON.parse(_m.dataset.assessmentChart);
var kompTren        = JSON.parse(_m.dataset.trenKompetensi);
var genderL         = parseInt(_m.dataset.genderL);
var genderP         = parseInt(_m.dataset.genderP);
var totalQualified  = parseInt(_m.dataset.totalQualified);
var totalNotQualified = parseInt(_m.dataset.totalNotQualified);
var soCore          = parseInt(_m.dataset.soCore);
var soNonCore       = parseInt(_m.dataset.soNonCore);

// Apply semua data-pct (progress bar dan bar-fill)
document.addEventListener('DOMContentLoaded', function() {
    // progress-mini-fill: width dari data-pct
    document.querySelectorAll('.progress-mini-fill[data-pct]').forEach(function(el) {
        el.style.width = el.dataset.pct + '%';
    });

    // bar-fill: width dari data-pct, warna dari data-color (usia chart)
    document.querySelectorAll('.bar-fill[data-pct]').forEach(function(el) {
        el.style.width = el.dataset.pct + '%';
        if (el.dataset.color) el.style.background = el.dataset.color;
    });

    // pie-dot: warna dari data-color
    document.querySelectorAll('.pie-dot[data-color]').forEach(function(el) {
        el.style.background = el.dataset.color;
    });
});

new Chart(document.getElementById('trenChart'), {
    type: 'bar',
    data: {
        labels: trenData.map(function(d) { return d.bulan; }),
        datasets: [
            { label:'Promosi', data:trenData.map(function(d){return d.promosi;}), backgroundColor:'#16a34a', borderRadius:3 },
            { label:'Mutasi',  data:trenData.map(function(d){return d.mutasi;}),  backgroundColor:'#2563eb', borderRadius:3 },
            { label:'Demosi',  data:trenData.map(function(d){return d.demosi;}),  backgroundColor:'#ef4444', borderRadius:3 },
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

new Chart(document.getElementById('pieChart'), {
    type:'doughnut',
    data: {
        labels: pieData.map(function(d){return d.label;}),
        datasets:[{ data:pieData.map(function(d){return d.value;}), backgroundColor:pieData.map(function(d){return d.color;}), borderWidth:2, borderColor:'#fff' }]
    },
    options:{ responsive:false, cutout:'65%', plugins:{ legend:{display:false} } }
});

new Chart(document.getElementById('genderChart'), {
    type:'doughnut',
    data: {
        labels:['Laki-laki','Perempuan'],
        datasets:[{ data:[genderL, genderP], backgroundColor:['#2563eb','#ec4899'], borderWidth:2, borderColor:'#fff' }]
    },
    options:{ responsive:false, cutout:'65%', plugins:{ legend:{display:false} } }
});

new Chart(document.getElementById('kompChart'), {
    type: 'bar',
    data: {
        labels: kompTren.map(function(d){return d.bulan;}),
        datasets: [
            { label:'QUALIFIED',     data:kompTren.map(function(d){return d.qualified;}),     backgroundColor:'#15803d', borderRadius:3 },
            { label:'NOT QUALIFIED', data:kompTren.map(function(d){return d.not_qualified;}),  backgroundColor:'#ef4444', borderRadius:3 },
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
            data:[totalQualified, totalNotQualified],
            backgroundColor:['#15803d','#ef4444'],
            borderWidth:2, borderColor:'#fff'
        }]
    },
    options:{ responsive:false, cutout:'65%', plugins:{ legend:{display:false} } }
});

new Chart(document.getElementById('soCorePie'), {
    type: 'doughnut',
    data: {
        labels: ['Core', 'Non Core'],
        datasets: [{
            data: [soCore, soNonCore],
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