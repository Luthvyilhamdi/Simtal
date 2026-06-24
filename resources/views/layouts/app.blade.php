<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>SIMTAL - @yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Baca cookie & pasang class collapsed SEBELUM halaman render (cegah kedip) --}}
    <script>
        if (document.cookie.split('; ').find(c => c.startsWith('sidebar_collapsed=1'))) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f9fafb; display: flex; height: 100vh; overflow: hidden; }

        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 40; backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        .sidebar {
            width: 248px; min-width: 248px; background: white;
            border-right: 1px solid #e5e7eb; display: flex; flex-direction: column;
            height: 100vh; overflow: visible;
            transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
            position: relative; z-index: 50; flex-shrink: 0;
            box-shadow: 1px 0 0 rgba(15,23,42,.02), 2px 0 16px rgba(15,23,42,.03);
        }
        html.sidebar-collapsed .sidebar { width: 64px; min-width: 64px; }
        html.sidebar-collapsed .sidebar .brand-name, html.sidebar-collapsed .sidebar .brand-sub,
        html.sidebar-collapsed .sidebar .nav-section-label, html.sidebar-collapsed .sidebar .nav-text,
        html.sidebar-collapsed .sidebar .user-info { display: none; }
        html.sidebar-collapsed .sidebar .nav-link { justify-content: center; padding: 10px; }
        html.sidebar-collapsed .sidebar .nav-link svg { margin: 0; }
        html.sidebar-collapsed .sidebar .toggle-chevron { display: none; }
        html.sidebar-collapsed .sidebar .master-sub { display: none; }
        html.sidebar-collapsed .sidebar .user-row { justify-content: center; padding: 8px; }
        html.sidebar-collapsed .sidebar .brand-row { justify-content: center; }
        html.sidebar-collapsed .sidebar .brand-icon { margin: 0; }
        html.sidebar-collapsed .sidebar .logout-btn { justify-content: center; padding: 10px; }
        html.sidebar-collapsed .sidebar .collapse-btn svg { transform: rotate(180deg); }
        html.sidebar-collapsed .sidebar .nav-sep { display: none; }

        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -248px; height: 100vh; z-index: 50; box-shadow: 4px 0 24px rgba(0,0,0,0.1); }
            .sidebar.mobile-open { left: 0; }
            html.sidebar-collapsed .sidebar { width: 248px; min-width: 248px; left: -248px; }
            html.sidebar-collapsed .sidebar .brand-name, html.sidebar-collapsed .sidebar .brand-sub,
            html.sidebar-collapsed .sidebar .nav-section-label, html.sidebar-collapsed .sidebar .nav-text,
            html.sidebar-collapsed .sidebar .user-info { display: block; }
            html.sidebar-collapsed .sidebar .toggle-chevron { display: block; }
            html.sidebar-collapsed .sidebar .master-sub.open { display: block; }
            html.sidebar-collapsed .sidebar .nav-link { justify-content: flex-start; padding: 8px 10px; }
            html.sidebar-collapsed .sidebar .master-sub .nav-link { padding-left: 32px; }
            html.sidebar-collapsed .sidebar .user-row { justify-content: flex-start; padding: 10px; }
            html.sidebar-collapsed .sidebar .brand-row { justify-content: flex-start; }
            html.sidebar-collapsed .sidebar .logout-btn { justify-content: flex-start; padding: 8px 10px; }
        }

        .sidebar-brand { padding: 18px 16px 14px; display: flex; align-items: center; border-bottom: 1px solid #f3f4f6; flex-shrink: 0; }
        .brand-row { display: flex; align-items: center; gap: 10px; overflow: hidden; }
        .brand-icon { width: 34px; height: 34px; flex-shrink: 0; background: #f0fdf4; border-radius: 9px; display: flex; align-items: center; justify-content: center; border: 1px solid #bbf7d0; }
        .brand-icon img { width: 22px; height: 22px; object-fit: contain; }
        .brand-name { font-size: 13px; font-weight: 800; color: #111827; letter-spacing: 1px; white-space: nowrap; }
        .brand-sub { font-size: 10px; color: #9ca3af; margin-top: 1px; white-space: nowrap; font-weight: 500; }

        /* Collapse handle: kapsul menempel di tepi border sidebar, ikon double-chevron khas app corporate */
        .collapse-btn {
            position: absolute; top: 27px; right: -14px;
            width: 28px; height: 28px; border-radius: 9px; flex-shrink: 0;
            border: 1px solid #e5e7eb; background: linear-gradient(180deg, #ffffff, #f9fafb);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #6b7280; z-index: 55;
            box-shadow: 0 2px 6px rgba(15,23,42,.08), 0 0 0 3px white;
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
        }
        .collapse-btn:hover { background: linear-gradient(135deg, #16a34a, #15803d); border-color: #15803d; color: white; box-shadow: 0 6px 16px rgba(21,128,61,.35), 0 0 0 3px white; transform: scale(1.08); }
        .collapse-btn:active { transform: scale(0.92); }
        .collapse-btn svg { width: 14px; height: 14px; transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); }

        .sidebar-nav { flex: 1; padding: 12px 10px; overflow-y: auto; overflow-x: hidden; }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }

        .nav-group { margin-bottom: 8px; }
        .nav-section-label { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .6px; padding: 10px 10px 5px; white-space: nowrap; overflow: hidden; }
        .nav-sep { height: 1px; background: #f3f4f6; margin: 8px 4px; }

        .nav-link { display: flex; align-items: center; gap: 10px; padding: 8px 11px; border-radius: 9px; color: #374151; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.12s; margin-bottom: 2px; white-space: nowrap; overflow: hidden; position: relative; }
        .nav-link:hover { background: #f3f4f6; color: #111827; }
        .nav-link.active { background: #d6f5e6; color: #0f9b6e; font-weight: 700; box-shadow: none; }
        .nav-link.active:hover { background: #c4f0db; color: #0f9b6e; }
        .nav-link svg { width: 15px; height: 15px; flex-shrink: 0; stroke: currentColor; }
        .nav-text { overflow: hidden; text-overflow: ellipsis; }

        html.sidebar-collapsed .sidebar .nav-link:hover::after { content: attr(data-tooltip); position: absolute; left: 56px; top: 50%; transform: translateY(-50%); background: #111827; color: white; font-size: 12px; font-weight: 500; padding: 5px 10px; border-radius: 6px; white-space: nowrap; z-index: 100; pointer-events: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        .master-toggle { cursor: pointer; user-select: none; }
        .master-toggle .toggle-chevron { width: 12px; height: 12px; margin-left: auto; flex-shrink: 0; transition: transform 0.2s; stroke: currentColor; fill: none; }
        .master-toggle.open .toggle-chevron { transform: rotate(180deg); }
        .master-sub { overflow: hidden; max-height: 0; transition: max-height 0.3s ease; }
        .master-sub.open { max-height: 520px; }
        .master-sub .nav-link { padding-left: 32px; font-size: 12.5px; color: #6b7280; }

        .sidebar-bottom { padding: 10px; border-top: 1px solid #f3f4f6; flex-shrink: 0; }
        .user-row { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 10px; background: #f9fafb; overflow: hidden; border: 1px solid #f3f4f6; }
        .user-avatar { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, #16a34a, #15803d); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: white; }
        .user-info { overflow: hidden; flex: 1; }
        .user-name { font-size: 12px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 10px; color: #9ca3af; margin-top: 1px; }

        .main-wrap { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
        .topbar { background: white; border-bottom: 1px solid #e5e7eb; padding: 0 20px; height: 56px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; gap: 12px; }
        .topbar-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .hamburger { display: none; width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e5e7eb; background: white; align-items: center; justify-content: center; cursor: pointer; color: #6b7280; flex-shrink: 0; transition: all 0.12s; }
        .hamburger:hover { background: #f3f4f6; }
        .hamburger svg { width: 18px; height: 18px; }
        @media (max-width: 768px) { .hamburger { display: flex; } }

        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; min-width: 0; }
        .breadcrumb-item { color: #9ca3af; white-space: nowrap; }
        .breadcrumb-item.active { color: #1a1a1a; font-weight: 600; overflow: hidden; text-overflow: ellipsis; }
        .breadcrumb-sep { color: #d1d5db; flex-shrink: 0; }

        .topbar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .topbar-icon-btn { width: 34px; height: 34px; border-radius: 50%; border: 1px solid #e5e7eb; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; color: #6b7280; transition: all 0.12s; flex-shrink: 0; }
        .topbar-icon-btn:hover { background: #f5f5f0; }
        .topbar-icon-btn svg { width: 16px; height: 16px; }
        .notif-badge { position: absolute; top: -2px; right: -2px; background: #ef4444; color: white; font-size: 9px; font-weight: 700; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; }

        .user-dropdown-wrap { position: relative; }
        .user-trigger { display: flex; align-items: center; gap: 8px; padding: 4px 10px 4px 4px; border-radius: 40px; border: 1px solid #e5e7eb; background: white; cursor: pointer; transition: all 0.15s; }
        .user-trigger:hover { background: #f9fafb; border-color: #d1d5db; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .user-trigger-avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: white; flex-shrink: 0; }
        .user-trigger-info { line-height: 1.25; }
        .user-trigger-name { font-size: 12px; font-weight: 600; color: #111827; white-space: nowrap; }
        .user-trigger-role { font-size: 10px; color: #9ca3af; }
        .user-trigger-chevron { width: 14px; height: 14px; stroke: #9ca3af; fill: none; stroke-width: 2.5; transition: transform 0.2s; flex-shrink: 0; }
        .user-dropdown-wrap.open .user-trigger-chevron { transform: rotate(180deg); }
        .user-dropdown-wrap.open .user-trigger { border-color: #bbf7d0; background: #f0fdf4; }
        @media (max-width: 480px) { .user-trigger-info { display: none; } .user-trigger { padding: 4px; border-radius: 50%; } .user-trigger-chevron { display: none; } }

        .user-dropdown-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); width: 240px; background: white; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 16px 48px rgba(0,0,0,0.12); overflow: hidden; z-index: 1000; animation: dropIn 0.18s cubic-bezier(0.4,0,0.2,1); }
        .user-dropdown-wrap.open .user-dropdown-menu { display: block; }
        @keyframes dropIn { from { opacity: 0; transform: translateY(-8px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .dropdown-header { padding: 16px; position: relative; overflow: hidden; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-bottom: 1px solid #bbf7d0; }
        .dropdown-header::before { content: ''; position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(22,163,74,0.08); }
        .dropdown-avatar-lg { width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #16a34a, #15803d); display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: white; margin-bottom: 10px; box-shadow: 0 3px 10px rgba(21,128,61,0.3); }
        .dropdown-header-name { font-size: 13px; font-weight: 700; color: #15803d; margin-bottom: 2px; }
        .dropdown-header-email { font-size: 11px; color: #6b7280; margin-bottom: 8px; }
        .dropdown-header-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; background: white; color: #15803d; border: 1px solid #bbf7d0; }

        .dropdown-body { padding: 6px 0; }
        .dropdown-divider { height: 1px; background: #f3f4f6; margin: 4px 8px; }
        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 9px 16px; font-size: 13px; color: #374151; font-weight: 500; cursor: pointer; transition: all 0.12s; text-decoration: none; border: none; background: transparent; width: 100%; font-family: inherit; text-align: left; }
        .dropdown-item:hover { background: #f9fafb; color: #111827; }
        .dropdown-item .di-icon { width: 28px; height: 28px; border-radius: 7px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .dropdown-item:hover .di-icon { background: #e5e7eb; }
        .dropdown-item .di-icon svg { width: 13px; height: 13px; stroke: #6b7280; fill: none; stroke-width: 2; }
        .dropdown-item.danger { color: #ef4444; }
        .dropdown-item.danger:hover { background: #fef2f2; color: #dc2626; }
        .dropdown-item.danger .di-icon { background: #fee2e2; }
        .dropdown-item.danger .di-icon svg { stroke: #ef4444; }

        .content-area { flex: 1; overflow-y: auto; padding: 24px; }
        @media (max-width: 480px) { .content-area { padding: 16px; } }

        #pageLoader { display:none;position:fixed;inset:0;background:rgba(255,255,255,0.78);z-index:99999;align-items:center;justify-content:center;backdrop-filter:blur(2px); }
        @keyframes spin-fade { 0%,100%{opacity:1} 50%{opacity:0.15} }
        .sp { position:absolute;width:5px;height:16px;border-radius:3px;background:#15803d;left:50%;top:50%;transform-origin:center 25px; }
        .sp:nth-child(1)  { transform:translateX(-50%) rotate(0deg);   animation:spin-fade 1.2s 0s    infinite; }
        .sp:nth-child(2)  { transform:translateX(-50%) rotate(30deg);  animation:spin-fade 1.2s 0.1s  infinite; }
        .sp:nth-child(3)  { transform:translateX(-50%) rotate(60deg);  animation:spin-fade 1.2s 0.2s  infinite; }
        .sp:nth-child(4)  { transform:translateX(-50%) rotate(90deg);  animation:spin-fade 1.2s 0.3s  infinite; }
        .sp:nth-child(5)  { transform:translateX(-50%) rotate(120deg); animation:spin-fade 1.2s 0.4s  infinite; }
        .sp:nth-child(6)  { transform:translateX(-50%) rotate(150deg); animation:spin-fade 1.2s 0.5s  infinite; }
        .sp:nth-child(7)  { transform:translateX(-50%) rotate(180deg); animation:spin-fade 1.2s 0.6s  infinite; }
        .sp:nth-child(8)  { transform:translateX(-50%) rotate(210deg); animation:spin-fade 1.2s 0.7s  infinite; }
        .sp:nth-child(9)  { transform:translateX(-50%) rotate(240deg); animation:spin-fade 1.2s 0.8s  infinite; }
        .sp:nth-child(10) { transform:translateX(-50%) rotate(270deg); animation:spin-fade 1.2s 0.9s  infinite; }
        .sp:nth-child(11) { transform:translateX(-50%) rotate(300deg); animation:spin-fade 1.2s 1.0s  infinite; }
        .sp:nth-child(12) { transform:translateX(-50%) rotate(330deg); animation:spin-fade 1.2s 1.1s  infinite; }
    </style>
    @stack('styles')
</head>
<body>

<div id="pageLoader">
  <div style="position:relative;width:24px;height:24px">
    <div class="sp"></div><div class="sp"></div><div class="sp"></div>
    <div class="sp"></div><div class="sp"></div><div class="sp"></div>
    <div class="sp"></div><div class="sp"></div><div class="sp"></div>
    <div class="sp"></div><div class="sp"></div><div class="sp"></div>
  </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-row">
            <div class="brand-icon"><img src="{{ asset('images/logo.png') }}" alt="Logo"></div>
            <div>
                <div class="brand-name">SIMTAL</div>
                <div class="brand-sub">Talent Management System</div>
            </div>
        </div>
        <button class="collapse-btn" id="collapseBtn" onclick="toggleSidebar()" title="Perkecil/perbesar sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="11 17 6 12 11 7"/><polyline points="17 17 12 12 17 7"/></svg>
        </button>
    </div>

    <div class="sidebar-nav">

        @if(auth()->user()->role === 'user')
        {{-- ===== SIDEBAR USER ===== --}}
        <div class="nav-group">
            <div class="nav-section-label">Akses Saya</div>
            <a href="{{ route('struktur-organisasi.index') }}" data-tooltip="Struktur Organisasi" class="nav-link {{ request()->routeIs('struktur-organisasi.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><rect x="8" y="2" width="8" height="4" rx="1"/><rect x="1" y="14" width="6" height="4" rx="1"/><rect x="9" y="14" width="6" height="4" rx="1"/><rect x="17" y="14" width="6" height="4" rx="1"/><path d="M4 14v-3h16v3"/><path d="M12 6v5"/></svg>
                <span class="nav-text">Struktur Organisasi</span>
            </a>
        </div>
        @else
        {{-- ===== SIDEBAR ADMIN / SUPER ADMIN ===== --}}

        <div class="nav-group">
            <div class="nav-section-label">Beranda</div>
            <a href="{{ route('dashboard') }}" data-tooltip="Dashboard" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                <span class="nav-text">Home</span>
            </a>
        </div>

        <div class="nav-sep"></div>

        <div class="nav-group">
            <div class="nav-section-label">Menu Utama</div>

            {{-- Data Karyawan (accordion) --}}
            <div class="nav-link master-toggle {{ request()->routeIs('karyawan.*','history_karyawan.*','struktur-organisasi.*') ? 'active open' : '' }}" data-tooltip="Data Karyawan" onclick="toggleMaster(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="nav-text">Data Karyawan</span>
                <svg class="toggle-chevron" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="master-sub {{ request()->routeIs('karyawan.*','history_karyawan.*','struktur-organisasi.*') ? 'open' : '' }}">
                <a href="{{ route('karyawan.index') }}" data-tooltip="Profil Karyawan" class="nav-link {{ request()->routeIs('karyawan.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="nav-text">Profil Karyawan</span>
                </a>
                <a href="{{ route('history_karyawan.index') }}" data-tooltip="Riwayat Jabatan" class="nav-link {{ request()->routeIs('history_karyawan.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span class="nav-text">Riwayat Jabatan</span>
                </a>
                <a href="{{ route('struktur-organisasi.index') }}" data-tooltip="Struktur Organisasi" class="nav-link {{ request()->routeIs('struktur-organisasi.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><rect x="8" y="2" width="8" height="4" rx="1"/><rect x="1" y="14" width="6" height="4" rx="1"/><rect x="9" y="14" width="6" height="4" rx="1"/><rect x="17" y="14" width="6" height="4" rx="1"/><path d="M4 14v-3h16v3"/><path d="M12 6v5"/></svg>
                    <span class="nav-text">Struktur Organisasi</span>
                </a>
            </div>

            {{-- Manajemen Talenta (accordion) --}}
            <div class="nav-link master-toggle {{ request()->routeIs('talent_pool.*','usulan_promosi.*','usulan_mutasi.*') ? 'active open' : '' }}" data-tooltip="Manajemen Talenta" onclick="toggleMaster(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                <span class="nav-text">Manajemen Talenta</span>
                <svg class="toggle-chevron" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="master-sub {{ request()->routeIs('talent_pool.*','usulan_promosi.*','usulan_mutasi.*') ? 'open' : '' }}">
                <a href="{{ route('talent_pool.index') }}" data-tooltip="Data Talent" class="nav-link {{ request()->routeIs('talent_pool.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span class="nav-text">Data Talent</span>
                </a>
                <a href="{{ route('usulan_promosi.index') }}" data-tooltip="Usulan Promosi" class="nav-link {{ request()->routeIs('usulan_promosi.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><polyline points="18 15 12 9 6 15"/><path d="M12 9v12"/><path d="M4 6h16"/></svg>
                    <span class="nav-text">Usulan Promosi</span>
                </a>
                <a href="{{ route('usulan_mutasi.index') }}" data-tooltip="Rotasi & Mutasi" class="nav-link {{ request()->routeIs('usulan_mutasi.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M16 3h5v5"/><path d="M21 3l-7 7"/><path d="M8 21H3v-5"/><path d="M3 21l7-7"/></svg>
                    <span class="nav-text">Rotasi &amp; Mutasi</span>
                </a>
            </div>

            {{-- Kepejabatan (accordion) --}}
            <div class="nav-link master-toggle {{ request()->routeIs('history_pejabat.*','pgs_pjs.*') ? 'active open' : '' }}" data-tooltip="Kepejabatan" onclick="toggleMaster(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <span class="nav-text">Kepejabatan</span>
                <svg class="toggle-chevron" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="master-sub {{ request()->routeIs('history_pejabat.*','pgs_pjs.*') ? 'open' : '' }}">
                <a href="{{ route('history_pejabat.index') }}" data-tooltip="Pejabat Definitif" class="nav-link {{ request()->routeIs('history_pejabat.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <span class="nav-text">Pejabat Definitif</span>
                </a>
                <a href="{{ route('pgs_pjs.index') }}" data-tooltip="PGS & PJS" class="nav-link {{ request()->routeIs('pgs_pjs.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="nav-text">PGS & PJS</span>
                </a>
            </div>

            {{-- Assessment & Penilaian (accordion) --}}
            <div class="nav-link master-toggle {{ request()->routeIs('history_assessment_all.*','assessment_kompetensi.*','history_penilaian_kalibrasi.*') ? 'active open' : '' }}" data-tooltip="Assessment & Penilaian" onclick="toggleMaster(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span class="nav-text">Assessment & Penilaian</span>
                <svg class="toggle-chevron" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="master-sub {{ request()->routeIs('history_assessment_all.*','assessment_kompetensi.*','history_penilaian_kalibrasi.*') ? 'open' : '' }}">
                <a href="{{ route('history_assessment_all.index') }}" data-tooltip="History Assessment" class="nav-link {{ request()->routeIs('history_assessment_all.*') || request()->routeIs('assessment_kompetensi.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <span class="nav-text">History Assessment</span>
                </a>
                <a href="{{ route('history_penilaian_kalibrasi.index') }}" data-tooltip="Penilaian & Kalibrasi" class="nav-link {{ request()->routeIs('history_penilaian_kalibrasi.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="8"/><rect x="12" y="6" width="3" height="12"/><rect x="17" y="13" width="3" height="5"/></svg>
                    <span class="nav-text">Penilaian &amp; Kalibrasi</span>
                </a>
            </div>
        </div>

        <div class="nav-sep"></div>

        <div class="nav-group">
            <div class="nav-section-label">Layanan & Laporan</div>

            {{-- Layanan (accordion) --}}
            <div class="nav-link master-toggle {{ request()->routeIs('surat_penting.*','faq') ? 'active open' : '' }}" data-tooltip="Layanan" onclick="toggleMaster(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>
                <span class="nav-text">Layanan</span>
                <svg class="toggle-chevron" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="master-sub {{ request()->routeIs('surat_penting.*','faq') ? 'open' : '' }}">
                <a href="{{ route('surat_penting.index') }}" data-tooltip="Surat Penting" class="nav-link {{ request()->routeIs('surat_penting.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span class="nav-text">Surat Penting</span>
                </a>
                <a href="https://luthvyilhamdi.github.io/idp-dashboard/" target="_blank" rel="noopener noreferrer" data-tooltip="Progress IDP" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span class="nav-text">Progress IDP</span>
                </a>
                <a href="{{ route('faq') }}" data-tooltip="FAQ" class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span class="nav-text">FAQ & Panduan</span>
                </a>
            </div>

            {{-- Laporan (link langsung) --}}
            <a href="{{ route('laporan.bulanan') }}" data-tooltip="Laporan Bulanan" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="nav-text">Laporan Bulanan</span>
            </a>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <div class="nav-sep"></div>

        <div class="nav-group">
            <div class="nav-section-label">Administrasi</div>
            <a href="{{ route('activity_log.index') }}" data-tooltip="Log Aktivitas" class="nav-link {{ request()->routeIs('activity_log.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <span class="nav-text">Log Aktivitas</span>
            </a>
            <a href="{{ route('akun.index') }}" data-tooltip="Manajemen Akun" class="nav-link {{ request()->routeIs('akun.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="nav-text">Manajemen Akun</span>
            </a>

            <div class="nav-link master-toggle {{ request()->routeIs('master.*') ? 'active open' : '' }}" data-tooltip="Master Data" onclick="toggleMaster(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                <span class="nav-text">Master Data</span>
                <svg class="toggle-chevron" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="master-sub {{ request()->routeIs('master.*') ? 'open' : '' }}">
                <a href="{{ route('master.jabatan.index') }}"       data-tooltip="Jabatan"       class="nav-link {{ request()->routeIs('master.jabatan.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><span class="nav-text">Jabatan</span></a>
                <a href="{{ route('master.direktorat.index') }}"    data-tooltip="Direktorat"    class="nav-link {{ request()->routeIs('master.direktorat.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><span class="nav-text">Direktorat</span></a>
                <a href="{{ route('master.kompartemen.index') }}"   data-tooltip="Kompartemen"   class="nav-link {{ request()->routeIs('master.kompartemen.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span class="nav-text">Kompartemen</span></a>
                <a href="{{ route('master.departemen.index') }}"    data-tooltip="Departemen"    class="nav-link {{ request()->routeIs('master.departemen.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span class="nav-text">Departemen</span></a>
                <a href="{{ route('master.job-grade.index') }}"     data-tooltip="Job Grade"     class="nav-link {{ request()->routeIs('master.job-grade.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg><span class="nav-text">Job Grade</span></a>
                <a href="{{ route('master.person-grade.index') }}"  data-tooltip="Person Grade"  class="nav-link {{ request()->routeIs('master.person-grade.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg><span class="nav-text">Person Grade</span></a>
                <a href="{{ route('master.kode-struktur.index') }}" data-tooltip="Kode Struktur" class="nav-link {{ request()->routeIs('master.kode-struktur.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg><span class="nav-text">Kode Struktur</span></a>
            </div>
        </div>
        @endif

        @endif

    </div>
</div>

<div class="main-wrap">
    <div class="topbar">
        <div class="topbar-left">
            <button class="hamburger" onclick="openSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="breadcrumb">
                <span class="breadcrumb-item">@yield('breadcrumb-parent', 'SIMTAL')</span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-item active">@yield('breadcrumb', 'Dashboard')</span>
            </div>
        </div>

        <div class="topbar-right">
            <div style="position:relative;">
                <button class="topbar-icon-btn" id="notifBtn" onclick="toggleNotif()" title="Notifikasi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <div class="notif-badge" id="notifBadge" style="display:none;">0</div>
                </button>
                <div id="notifDropdown" style="display:none;position:absolute;right:0;top:44px;width:360px;background:white;border-radius:14px;border:1px solid #e5e7eb;box-shadow:0 12px 40px rgba(0,0,0,0.12);z-index:1000;overflow:hidden;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:14px;font-weight:700;color:#111827;">🔔 Notifikasi</span>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <button onclick="readAllNotif()" style="font-size:12px;color:#16a34a;font-weight:600;border:none;background:transparent;cursor:pointer;">Tandai semua dibaca</button>
                            <a href="{{ route('notifikasi.index') }}" style="font-size:12px;color:#6b7280;text-decoration:none;">Lihat semua</a>
                        </div>
                    </div>
                    <div id="notifList" style="max-height:380px;overflow-y:auto;">
                        <div style="text-align:center;padding:40px 20px;color:#9ca3af;font-size:13px;">Memuat notifikasi...</div>
                    </div>
                </div>
            </div>

            <div class="user-dropdown-wrap" id="userDropdownWrap">
                <button class="user-trigger" onclick="toggleUserDropdown()">
                    <div class="user-trigger-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div class="user-trigger-info">
                        <div class="user-trigger-name">{{ auth()->user()->name }}</div>
                        <div class="user-trigger-role">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : (auth()->user()->isAdmin() ? 'Administrator' : 'User') }}</div>
                    </div>
                    <svg class="user-trigger-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar-lg">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                        <div class="dropdown-header-name">{{ auth()->user()->name }}</div>
                        <div class="dropdown-header-email">{{ auth()->user()->email }}</div>
                        <div class="dropdown-header-badge">
                            {{ auth()->user()->isSuperAdmin() ? '⭐ Super Admin' : (auth()->user()->isAdmin() ? '🔵 Administrator' : '👤 User') }}
                        </div>
                    </div>
                    <div class="dropdown-body">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <div class="di-icon"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                            Edit Profil
                        </a>
                        @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('akun.index') }}" class="dropdown-item">
                            <div class="di-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                            Manajemen Akun
                        </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item danger">
                                <div class="di-icon"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></div>
                                Keluar dari SIMTAL
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-area">
        @yield('content')
    </div>
</div>

<script>
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const isMobile = () => window.innerWidth <= 768;

    // Desktop collapse (perkecil/perbesar) + simpan ke cookie (cegah kedip)
    function toggleSidebar() {
        if (isMobile()) { closeSidebar(); }
        else {
            document.documentElement.classList.toggle('sidebar-collapsed');
            const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
            document.cookie = 'sidebar_collapsed=' + (isCollapsed ? '1' : '0') + ';path=/;max-age=31536000';
        }
    }
    function openSidebar()  { sidebar.classList.add('mobile-open'); overlay.classList.add('show'); document.body.style.overflow = 'hidden'; }
    function closeSidebar() { sidebar.classList.remove('mobile-open'); overlay.classList.remove('show'); document.body.style.overflow = ''; }
    window.addEventListener('resize', () => {
        if (!isMobile()) { sidebar.classList.remove('mobile-open'); overlay.classList.remove('show'); document.body.style.overflow = ''; }
    });

    // Accordion eksklusif: buka satu grup -> grup lain otomatis tertutup
    function toggleMaster(el) {
        const willOpen = !el.classList.contains('open');
        document.querySelectorAll('.sidebar-nav .master-toggle.open').forEach(t => {
            t.classList.remove('open');
            if (t.nextElementSibling) t.nextElementSibling.classList.remove('open');
        });
        if (willOpen) {
            el.classList.add('open');
            if (el.nextElementSibling) el.nextElementSibling.classList.add('open');
        }
    }

    function toggleUserDropdown() { document.getElementById('userDropdownWrap').classList.toggle('open'); }
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('userDropdownWrap');
        if (wrap && !wrap.contains(e.target)) wrap.classList.remove('open');
    });

    let notifOpen = false;
    function toggleNotif() {
        notifOpen = !notifOpen;
        const dropdown = document.getElementById('notifDropdown');
        dropdown.style.display = notifOpen ? 'block' : 'none';
        if (notifOpen) fetchNotif();
    }
    async function fetchNotif() {
        try {
            const res  = await fetch('{{ route("notifikasi.fetch") }}');
            const data = await res.json();
            const badge = document.getElementById('notifBadge');
            if (data.unread > 0) { badge.style.display = 'flex'; badge.textContent = data.unread > 99 ? '99+' : data.unread; }
            else { badge.style.display = 'none'; }
            const list = document.getElementById('notifList');
            if (data.notifikasis.length === 0) { list.innerHTML = `<div style="text-align:center;padding:40px 20px;color:#9ca3af;font-size:13px;">🔔 Tidak ada notifikasi</div>`; return; }
            list.innerHTML = data.notifikasis.map(n => `
                <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:${n.is_read ? 'white' : n.warna.bg};cursor:pointer;" onclick="markRead(${n.id}, this)">
                    <div style="width:36px;height:36px;border-radius:9px;background:${n.warna.bg};border:1px solid ${n.warna.border};display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">${n.icon}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:${n.is_read ? '500' : '700'};color:${n.is_read ? '#374151' : n.warna.text};margin-bottom:2px;">${n.judul}</div>
                        <div style="font-size:12px;color:#6b7280;line-height:1.4;">${n.pesan}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:4px;">${n.waktu}</div>
                    </div>
                    ${!n.is_read ? `<div style="width:8px;height:8px;border-radius:50%;background:${n.warna.text};flex-shrink:0;margin-top:4px;"></div>` : ''}
                </div>
            `).join('');
            list.innerHTML += `<div style="padding:12px;text-align:center;border-top:1px solid #f3f4f6;"><a href="{{ route('notifikasi.index') }}" style="font-size:12px;color:#16a34a;font-weight:600;text-decoration:none;">Lihat Semua Notifikasi →</a></div>`;
        } catch(e) { console.error(e); }
    }
    async function markRead(id, el) {
        await fetch(`/notifikasi/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } });
        el.style.background = 'white'; fetchNotif();
    }
    async function readAllNotif() {
        await fetch('{{ route("notifikasi.readAll") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        fetchNotif();
    }
    document.addEventListener('click', function(e) {
        const btn = document.getElementById('notifBtn');
        const dropdown = document.getElementById('notifDropdown');
        if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) { dropdown.style.display = 'none'; notifOpen = false; }
    });
    fetchNotif();
    setInterval(fetchNotif, 5 * 60 * 1000);

    const loader = document.getElementById('pageLoader');
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || a.target === '_blank') return;
        if (href.includes('/export') || href.includes('/download') || href.includes('template') || a.getAttribute('download') !== null) return;
        loader.style.display = 'flex';
    });
    document.addEventListener('submit', function(e) { loader.style.display = 'flex'; });
    window.addEventListener('pageshow', function(e) { loader.style.display = 'none'; });
</script>

@stack('scripts')
</body>
</html>