@extends('layouts.app')
@section('title', 'Manajemen Akun')
@section('breadcrumb-parent', 'Administrasi')
@section('breadcrumb', 'Manajemen Akun')

@push('styles')
<style>
    /* ===================== Kepala halaman ===================== */
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:var(--fs-h1);font-weight:700;color:var(--text-strong);letter-spacing:-.2px; }
    .page-sub { font-size:var(--fs-sm);color:var(--text-muted);margin-top:3px; }

    .btn-primary-sm { display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:var(--radius-sm);background:var(--brand);color:#fff;border:none;font-size:var(--fs-body);font-weight:600;font-family:inherit;cursor:pointer;transition:all .15s;text-decoration:none;white-space:nowrap; }
    .btn-primary-sm:hover { background:#166534;box-shadow:0 4px 12px rgba(21,128,61,.22); }
    .btn-primary-sm svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2.2; }

    /* ===================== Ringkasan ===================== */
    .stats-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px; }
    .stat-card { display:flex;align-items:center;gap:13px;background:#fff;border:1px solid var(--card-border);border-radius:var(--radius);box-shadow:var(--card-shadow);padding:15px 16px; }
    .stat-ic { width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .stat-ic svg { width:19px;height:19px;fill:none;stroke-width:1.9; }
    .stat-num { font-size:22px;font-weight:800;color:var(--text-strong);line-height:1.05; }
    .stat-label { font-size:var(--fs-xs);color:var(--text-faint);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:3px; }
    .ic-total { background:#f0fdf4; } .ic-total svg { stroke:#15803d; }
    .ic-super { background:#fffbeb; } .ic-super svg { stroke:#d97706; }
    .ic-admin { background:#eff6ff; } .ic-admin svg { stroke:#1d4ed8; }
    .ic-user  { background:#f4f5f7; } .ic-user  svg { stroke:#667085; }

    /* ===================== Saringan ===================== */
    .filter-row { display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center; }
    .search-mini { display:flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--card-border);border-radius:9px;padding:8px 12px;width:260px;transition:all .15s; }
    .search-mini:focus-within { border-color:var(--brand);box-shadow:0 0 0 3px rgba(21,128,61,.08); }
    .search-mini svg { width:14px;height:14px;stroke:var(--text-faint);fill:none;stroke-width:2;flex-shrink:0; }
    .search-mini input { border:none;outline:none;font-size:var(--fs-sm);font-family:inherit;color:var(--text-strong);background:transparent;width:100%; }
    .search-mini input::placeholder { color:var(--text-faint); }
    .clear-btn { background:none;border:none;cursor:pointer;color:var(--text-faint);font-size:15px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .clear-btn.visible { display:block; }
    .filter-select { padding:8px 12px;border:1px solid var(--card-border);border-radius:9px;font-size:var(--fs-sm);font-family:inherit;color:var(--text);background:#fff;outline:none;cursor:pointer; }
    .filter-select:focus { border-color:var(--brand); }
    .btn-reset { display:inline-flex;align-items:center;gap:5px;padding:8px 13px;border-radius:9px;border:1px solid var(--card-border);background:#fff;color:var(--text-muted);font-size:var(--fs-sm);font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap; }
    .btn-reset:hover { background:#f7f8f9;color:var(--text); }

    /* ===================== Tabel ===================== */
    .table-card { background:#fff;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .table-header { display:flex;align-items:center;justify-content:space-between;gap:10px;padding:15px 20px;border-bottom:1px solid var(--divider);flex-wrap:wrap; }
    .table-title { font-size:var(--fs-h2);font-weight:700;color:var(--text-strong);display:flex;align-items:center;gap:9px; }
    .table-title::before { content:'';width:3px;height:15px;border-radius:2px;background:var(--brand); }
    .table-count { font-size:var(--fs-sm);color:var(--text-faint);font-weight:500; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:var(--fs-body);min-width:960px; }
    thead th { padding:11px 18px;text-align:left;font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--divider);background:#fbfcfd;white-space:nowrap; }
    tbody td { padding:13px 18px;border-bottom:1px solid var(--divider);color:var(--text);vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafbfc; }

    .user-info { display:flex;align-items:center;gap:11px;min-width:0; }
    .user-avatar { position:relative;width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:12.5px;font-weight:700;flex-shrink:0;letter-spacing:.3px; }
    .presence-dot { position:absolute;right:-3px;bottom:-3px;width:12px;height:12px;border-radius:50%;background:#22c55e;border:2.5px solid #fff;box-shadow:0 0 0 2px rgba(34,197,94,.18); }
    .av-super { background:#fef3c7;color:#b45309; }
    .av-admin { background:#dbeafe;color:#1d4ed8; }
    .av-user  { background:#eef0f3;color:#5b6472; }
    .user-name { font-weight:600;color:var(--text-strong);font-size:var(--fs-body);display:flex;align-items:center;gap:6px;flex-wrap:wrap; }
    .user-meta { font-size:var(--fs-xs);color:var(--text-faint);margin-top:2px;display:flex;align-items:center;gap:7px;flex-wrap:wrap; }
    .user-meta .dot { width:3px;height:3px;border-radius:50%;background:#d0d5dd;flex-shrink:0; }
    .user-email { overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:230px; }
    .user-nik { display:inline-flex;align-items:center;gap:7px;white-space:nowrap; }
    .you-badge { display:inline-flex;padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700;background:#f0fdf4;color:var(--brand);border:1px solid #bbf7d0; }
    .warn-badge { display:inline-flex;padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700;background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }

    .role-badge { display:inline-flex;align-items:center;gap:6px;padding:4px 11px;border-radius:20px;font-size:var(--fs-xs);font-weight:700;white-space:nowrap;border:1px solid transparent; }
    .role-badge .rdot { width:6px;height:6px;border-radius:50%;flex-shrink:0; }
    .role-super { background:#fffbeb;color:#b45309;border-color:#fde68a; } .role-super .rdot { background:#d97706; }
    .role-admin { background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe; } .role-admin .rdot { background:#2563eb; }
    .role-user  { background:#f4f5f7;color:#5b6472;border-color:#e4e7ec; } .role-user  .rdot { background:#98a2b3; }

    .akses-chip { display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:7px;font-size:var(--fs-xs);font-weight:600;white-space:nowrap;background:#f4f5f7;color:#5b6472;border:1px solid #e4e7ec; }
    .akses-chip.full    { background:#fffbeb;color:#b45309;border-color:#fde68a; }
    .akses-chip.partial { background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe; }
    .akses-chip.none    { background:#fef2f2;color:#dc2626;border-color:#fecaca; }

    .cell-date { font-size:var(--fs-sm);color:var(--text-muted);white-space:nowrap; }

    /* Status kehadiran — dibaca dari tabel sessions, lihat AkunController::statusOnline() */
    .presence { display:inline-flex;align-items:center;gap:6px;font-size:var(--fs-xs);font-weight:600;white-space:nowrap; }
    .presence .pdot { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
    .presence.on { color:#15803d; }
    .presence.on .pdot { background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.16); }
    .presence.idle { color:var(--text-muted); }
    .presence.idle .pdot { background:#cbd5e1; }
    .presence.off { color:var(--text-faint);font-weight:500; }

    .action-btns { display:flex;gap:6px;align-items:center;justify-content:flex-end; }
    .btn-ghost { display:inline-flex;align-items:center;gap:6px;height:30px;padding:0 11px;border-radius:8px;border:1px solid var(--card-border);background:#fff;color:var(--text);font-size:var(--fs-sm);font-weight:600;cursor:pointer;font-family:inherit;transition:all .12s;white-space:nowrap; }
    .btn-ghost svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-ghost:hover { background:#f0fdf4;border-color:#bbf7d0;color:var(--brand); }
    .btn-ghost.danger { width:30px;padding:0;justify-content:center;color:#ef4444; }
    .btn-ghost.danger:hover { background:#fef2f2;border-color:#fecaca;color:#dc2626; }
    .btn-ghost.disabled { opacity:.35;cursor:not-allowed;pointer-events:none; }

    .empty-state { text-align:center;padding:46px 20px; }
    .empty-state svg { width:38px;height:38px;margin:0 auto 12px;display:block;stroke:#d0d5dd;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:var(--fs-body);font-weight:600;color:var(--text-muted);margin-bottom:3px; }
    .empty-state span { font-size:var(--fs-sm);color:var(--text-faint); }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:13px 20px;border-top:1px solid var(--divider);font-size:var(--fs-sm);color:var(--text-muted);flex-wrap:wrap;gap:10px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { min-width:30px;height:30px;padding:0 6px;border-radius:8px;border:1px solid var(--card-border);background:#fff;display:flex;align-items:center;justify-content:center;font-size:var(--fs-sm);font-weight:600;color:var(--text);cursor:pointer;text-decoration:none;transition:all .12s; }
    .page-btn:hover { background:#f7f8f9; }
    .page-btn.active { background:var(--brand);color:#fff;border-color:var(--brand); }
    .page-btn.disabled { opacity:.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.2; }

    /* ===================== Formulir & modal ===================== */
    .modal-backdrop { position:fixed;inset:0;background:rgba(16,24,40,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:16px; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:#fff;border-radius:16px;width:100%;max-width:640px;box-shadow:0 24px 64px rgba(16,24,40,.22);animation:modalIn .22s cubic-bezier(.4,0,.2,1);max-height:92vh;display:flex;flex-direction:column;overflow:hidden; }
    .modal-box.sm { max-width:400px; }
    .modal-box form { display:flex;flex-direction:column;min-height:0;overflow:hidden; }
    .modal-head { display:flex;align-items:center;gap:12px;padding:20px 24px;border-bottom:1px solid var(--divider);flex-shrink:0; }
    .modal-head-ic { width:38px;height:38px;border-radius:11px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .modal-head-ic svg { width:18px;height:18px;stroke:var(--brand);fill:none;stroke-width:2; }
    .modal-title { font-size:var(--fs-h2);font-weight:700;color:var(--text-strong); }
    .modal-desc { font-size:var(--fs-sm);color:var(--text-muted);margin-top:2px; }
    .modal-x { margin-left:auto;width:30px;height:30px;border-radius:8px;border:none;background:transparent;color:var(--text-faint);font-size:20px;line-height:1;cursor:pointer;flex-shrink:0; }
    .modal-x:hover { background:#f4f5f7;color:var(--text); }
    .modal-body { padding:22px 24px;overflow-y:auto; }
    .modal-foot { display:flex;gap:10px;justify-content:flex-end;padding:16px 24px;border-top:1px solid var(--divider);background:#fbfcfd;flex-shrink:0; }
    .modal-btn { padding:10px 20px;border-radius:var(--radius-sm);font-size:var(--fs-body);font-weight:600;font-family:inherit;cursor:pointer;border:1px solid transparent;transition:all .15s; }
    .modal-btn.cancel { background:#fff;color:var(--text);border-color:var(--card-border); }
    .modal-btn.cancel:hover { background:#f4f5f7; }
    .modal-btn.save { background:var(--brand);color:#fff; }
    .modal-btn.save:hover { background:#166534; }
    .modal-btn.danger { background:#dc2626;color:#fff; }
    .modal-btn.danger:hover { background:#b91c1c; }
    @keyframes modalIn { from{opacity:0;transform:translateY(8px) scale(.98);}to{opacity:1;transform:none;} }

    .modal-box.center .modal-body { text-align:center;padding:26px 24px 22px; }
    .modal-icon-wrap { width:54px;height:54px;border-radius:16px;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px; }
    .modal-icon-wrap svg { width:24px;height:24px;stroke:#dc2626;fill:none;stroke-width:1.8; }
    .modal-box.center .modal-foot { justify-content:stretch; }
    .modal-box.center .modal-btn { flex:1; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px 16px; }
    .form-group { display:flex;flex-direction:column;gap:6px;min-width:0; }
    .form-group.full { grid-column:1 / -1; }
    .form-label { font-size:var(--fs-xs);font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.5px; }
    .form-label .req { color:#dc2626; }
    .form-input { padding:10px 13px;border:1px solid var(--card-border);border-radius:9px;font-size:var(--fs-body);font-family:inherit;color:var(--text-strong);background:#fff;outline:none;transition:all .15s;width:100%; }
    .form-input:focus { border-color:var(--brand);box-shadow:0 0 0 3px rgba(21,128,61,.08); }
    .form-input::placeholder { color:var(--text-faint); }
    .error-msg { font-size:var(--fs-xs);color:#dc2626;font-weight:600; }
    .form-hint { font-size:var(--fs-xs);color:var(--text-faint);line-height:1.5; }
    .form-sep { grid-column:1 / -1;display:flex;align-items:center;gap:10px;font-size:var(--fs-xs);font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-faint);margin-top:4px; }
    .form-sep::after { content:'';flex:1;height:1px;background:var(--divider); }

    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:13px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid var(--text-faint);pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:34px;cursor:pointer;width:100%; }

    .role-info { display:flex;gap:9px;background:#f9fafb;border:1px solid var(--card-border);border-left:3px solid var(--brand);border-radius:9px;padding:10px 12px;font-size:var(--fs-xs);color:var(--text);line-height:1.6; }
    .role-info svg { width:14px;height:14px;stroke:var(--brand);fill:none;stroke-width:2;flex-shrink:0;margin-top:1px; }
    .role-info strong { color:var(--text-strong); }

    /* Panel akses menu */
    .ma-head { display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap; }
    .ma-head-right { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
    .ma-count { font-size:var(--fs-xs);font-weight:700;color:var(--brand);background:#f0fdf4;border:1px solid #bbf7d0;border-radius:20px;padding:2px 10px;white-space:nowrap; }
    .menu-access-tools { display:flex;gap:6px; }
    .menu-access-tools button { font-size:var(--fs-xs);color:var(--text-muted);background:#fff;border:1px solid var(--card-border);cursor:pointer;font-weight:600;font-family:inherit;padding:3px 10px;border-radius:7px;transition:all .12s; }
    .menu-access-tools button:hover { background:#f0fdf4;border-color:#bbf7d0;color:var(--brand); }
    .menu-access-box { border:1px solid var(--card-border);border-radius:var(--radius-sm);padding:2px 14px 12px;max-height:250px;overflow-y:auto;background:#fbfcfd; }
    .ma-group-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-faint);margin:14px 0 5px;display:flex;align-items:center;gap:8px; }
    .ma-group:first-child .ma-group-label { margin-top:10px; }
    .ma-group-label::after { content:'';flex:1;height:1px;background:var(--divider); }
    .ma-items { display:grid;grid-template-columns:1fr 1fr;gap:1px 10px; }
    .ma-check { display:flex;align-items:center;gap:9px;font-size:12.5px;color:var(--text);padding:5px 8px;border-radius:7px;cursor:pointer;transition:background .12s;min-width:0; }
    .ma-check:hover { background:#f0fdf4; }
    .ma-check span { overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .ma-check input { width:15px;height:15px;accent-color:var(--brand);cursor:pointer;flex-shrink:0; }

    /* ===================== Toast ===================== */
    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #bbf7d0;border-left:4px solid var(--brand-600);border-radius:12px;padding:13px 16px;box-shadow:0 8px 32px rgba(16,24,40,.12);font-size:var(--fs-body);color:var(--brand);font-weight:600;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn .35s cubic-bezier(.4,0,.2,1) forwards; }
    .toast.error { border-color:#fecaca;border-left-color:#ef4444;color:#dc2626; }
    .toast.hiding { animation:toastOut .3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast.error .toast-icon { background:#fef2f2; }
    .toast-icon svg { width:12px;height:12px;stroke:var(--brand-600);fill:none;stroke-width:2.5; }
    .toast.error .toast-icon svg { stroke:#ef4444; }
    .toast-close { border:none;background:transparent;color:var(--text-faint);cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:var(--brand-600);animation:toastProgress 3s linear forwards; }
    .toast.error .toast-progress { background:#ef4444; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    /* ===================== Responsif ===================== */
    @media (max-width:1024px) {
        .stats-grid { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:640px) {
        .page-title { font-size:18px; }
        .stats-grid { gap:10px; }
        .stat-card { padding:12px 13px;gap:10px; }
        .stat-ic { width:34px;height:34px;border-radius:10px; }
        .stat-num { font-size:19px; }

        .form-grid { grid-template-columns:minmax(0,1fr); }
        .ma-items { grid-template-columns:minmax(0,1fr); }
        .modal-head, .modal-body, .modal-foot { padding-left:16px;padding-right:16px; }
        .modal-foot { flex-direction:column-reverse; }
        .modal-btn { width:100%; }

        .table-header { padding:14px 16px; }
        .table-wrap { overflow-x:visible; }
        table { min-width:0; }
        table thead { display:none; }
        table, tbody { display:block;width:100%; }
        tbody { display:flex;flex-direction:column;gap:10px;padding:12px 12px 4px; }
        tbody tr { display:flex;flex-wrap:wrap;align-items:center;position:relative;border:1px solid var(--card-border);border-radius:var(--radius-sm);padding:14px;background:#fff; }
        tbody tr:hover td { background:transparent; }
        tbody td { display:block;border:none;padding:0; }
        td.col-index { display:none; }
        td.cell-user { flex:1 1 100%;min-width:0; }
        /* Ruang untuk badge role hanya disisakan di baris nama (badge ada di pojok
           kanan atas), supaya email & NIK di bawahnya tetap dapat lebar penuh. */
        .user-name { padding-right:114px; }
        .user-meta { flex-direction:column;align-items:flex-start;gap:2px; }
        .user-meta .dot { display:none; }
        .user-email { max-width:100%; }
        td.cell-role { position:absolute;top:14px;right:14px; }
        /* Baris kartu di HP: (1) identitas  (2) status + akses  (3) tanggal + aksi.
           Urutan diatur lewat `order` karena di DOM status berada sebelum akses,
           dan ::after milik <tr> dipakai sebagai pemaksa ganti baris. */
        td.cell-user { order:1; }
        td.cell-status { flex:0 0 auto;margin-top:11px;margin-right:12px;order:2; }
        td.cell-akses { flex:0 0 auto;margin-top:11px;order:3; }
        tbody tr::after { content:'';flex:0 0 100%;height:0;order:4; }
        td.cell-tgl { flex:1 1 auto;margin-top:9px;font-size:11.5px;color:var(--text-faint);font-weight:600;order:5; }
        td.cell-tgl::before { content:'Terdaftar · ';color:#d0d5dd; }
        td.cell-aksi { margin-top:9px;margin-left:auto;order:6; }
        .action-btns { flex-wrap:wrap;justify-content:flex-end; }
    }
</style>
@endpush

@section('content')

@php
    /* Penanda supaya pesan validasi muncul di modal yang benar setelah redirect back. */
    $errCreate = old('form_mode') === 'create';
    $errEdit   = old('form_mode') === 'edit';
@endphp

{{-- ===================== Notifikasi ===================== --}}
@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif
@if(session('error'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast error" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
        <div>{{ session('error') }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- ===================== Modal Tambah ===================== --}}
<div class="modal-backdrop" id="modalTambah">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-head-ic">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
                <div class="modal-title">Tambah Akun</div>
                <div class="modal-desc">Buat akun baru beserta hak aksesnya</div>
            </div>
            <button type="button" class="modal-x" onclick="closeModal('modalTambah')">&times;</button>
        </div>
        <form method="POST" action="{{ route('akun.store') }}">
            @csrf
            <input type="hidden" name="form_mode" value="create">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama <span class="req">*</span></label>
                        <input type="text" name="name" value="{{ $errCreate ? old('name') : '' }}" class="form-input" placeholder="Nama lengkap" required>
                        @if($errCreate) @error('name')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK <span class="req">*</span></label>
                        <input type="text" name="nik" value="{{ $errCreate ? old('nik') : '' }}" class="form-input" placeholder="Nomor Induk Karyawan" required>
                        <span class="form-hint">Dipakai untuk login. Harus sama dengan NIK di Data Karyawan.</span>
                        @if($errCreate) @error('nik')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" value="{{ $errCreate ? old('email') : '' }}" class="form-input" placeholder="email@perusahaan.com" required>
                        @if($errCreate) @error('email')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>

                    <div class="form-sep">Hak Akses</div>
                    <div class="form-group full">
                        <label class="form-label">Role <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select name="role" id="createRole" class="form-input" onchange="onRoleChange('create', this.value)">
                                <option value="user"        {{ $errCreate && old('role') === 'user'        ? 'selected' : '' }}>User</option>
                                <option value="admin"       {{ $errCreate && old('role') === 'admin'       ? 'selected' : '' }}>Admin</option>
                                <option value="super_admin" {{ $errCreate && old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                        </div>
                        <div class="role-info" id="createRoleInfo">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span></span>
                        </div>
                        @if($errCreate) @error('role')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>

                    @include('akun._menu_access', ['prefix' => 'create', 'selected' => $errCreate ? old('menu_access', []) : []])

                    <div class="form-sep">Kata Sandi</div>
                    <div class="form-group">
                        <label class="form-label">Password <span class="req">*</span></label>
                        <input type="password" name="password" class="form-input" placeholder="Min. 8 karakter" required>
                        @if($errCreate) @error('password')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span class="req">*</span></label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="modal-btn cancel" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="modal-btn save">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== Modal Edit ===================== --}}
<div class="modal-backdrop" id="modalEdit">
    <div class="modal-box">
        <div class="modal-head">
            <div class="modal-head-ic">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div>
                <div class="modal-title">Edit Akun</div>
                <div class="modal-desc" id="editSubtitle">Ubah data dan hak akses pengguna</div>
            </div>
            <button type="button" class="modal-x" onclick="closeModal('modalEdit')">&times;</button>
        </div>
        <form method="POST" id="formEdit">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_mode" value="edit">
            <input type="hidden" name="edit_id" id="editId">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama <span class="req">*</span></label>
                        <input type="text" name="name" id="editName" class="form-input" required>
                        @if($errEdit) @error('name')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK <span class="req">*</span></label>
                        <input type="text" name="nik" id="editNik" class="form-input" required>
                        <span class="form-hint">Dipakai untuk login. Harus sama dengan NIK di Data Karyawan.</span>
                        @if($errEdit) @error('nik')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-input" required>
                        @if($errEdit) @error('email')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>

                    <div class="form-sep">Hak Akses</div>
                    <div class="form-group full">
                        <label class="form-label">Role <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select name="role" id="editRole" class="form-input" onchange="onRoleChange('edit', this.value)">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        <div class="role-info" id="editRoleInfo">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span></span>
                        </div>
                        @if($errEdit) @error('role')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>

                    @include('akun._menu_access', ['prefix' => 'edit', 'selected' => []])

                    <div class="form-sep">Kata Sandi</div>
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-input" placeholder="Kosongkan jika tidak diubah">
                        @if($errEdit) @error('password')<div class="error-msg">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="modal-btn cancel" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="modal-btn save">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== Modal Hapus ===================== --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box sm center">
        <div class="modal-body">
            <div class="modal-icon-wrap">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </div>
            <div class="modal-title">Hapus Akun?</div>
            <p class="modal-desc" style="margin-top:8px;line-height:1.6;" id="hapusDesc">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-foot">
            <button type="button" class="modal-btn cancel" onclick="closeModal('modalHapus')">Batal</button>
            <button type="button" class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

{{-- ===================== Kepala halaman ===================== --}}
<div class="page-header">
    <div>
        <div class="page-title">Manajemen Akun</div>
        <div class="page-sub">Kelola pengguna, role, dan hak akses menu SiMental</div>
    </div>
    <button type="button" class="btn-primary-sm" onclick="openTambahModal()">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Akun
    </button>
</div>

{{-- ===================== Ringkasan ===================== --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-ic ic-total"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Total Akun</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-ic ic-super"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></div>
        <div><div class="stat-num">{{ $stats['super_admin'] }}</div><div class="stat-label">Super Admin</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-ic ic-admin"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
        <div><div class="stat-num">{{ $stats['admin'] }}</div><div class="stat-label">Admin</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-ic ic-user"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <div><div class="stat-num">{{ $stats['user'] }}</div><div class="stat-label">User</div></div>
    </div>
</div>

{{-- ===================== Saringan ===================== --}}
<form method="GET" action="{{ route('akun.index') }}" id="filterForm">
    <div class="filter-row">
        <div class="search-mini">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Cari nama, NIK, atau email…" autocomplete="off">
            {{-- id TIDAK boleh sama dengan nama fungsi di onclick: di dalam form,
                 elemen ber-id ikut jadi properti form dan menutupi fungsi global. --}}
            <button type="button" class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearSearchBtn" onclick="clearSearch()">&times;</button>
        </div>
        <div class="select-wrap">
            <select name="role" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Role</option>
                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin"       {{ request('role') === 'admin'       ? 'selected' : '' }}>Admin</option>
                <option value="user"        {{ request('role') === 'user'        ? 'selected' : '' }}>User</option>
            </select>
        </div>
        @if(request('search') || request('role'))
        <a href="{{ route('akun.index') }}" class="btn-reset">
            <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.2;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Reset
        </a>
        @endif
    </div>
</form>

{{-- ===================== Daftar akun ===================== --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Daftar Akun</div>
        <span class="table-count">
            @if(request('search') || request('role'))
                {{ $users->total() }} akun cocok dengan saringan
            @else
                {{-- @if/@endif WAJIB didahului spasi: bila menempel pada huruf,
                     Blade tidak mengenalinya sebagai direktif (regex \B@). --}}
                {{ $users->total() }} akun terdaftar @if($stats['online'])&middot; {{ $stats['online'] }} sedang online @endif
            @endif
        </span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:48px;">#</th>
                    <th>Pengguna</th>
                    <th style="width:150px;">Role</th>
                    <th style="width:126px;">Status</th>
                    <th style="width:172px;">Akses Menu</th>
                    <th style="width:120px;">Terdaftar</th>
                    <th style="width:130px;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $isSuper = $user->isSuperAdmin();
                    $isAdmin = $user->role === 'admin';
                    $ragam   = $isSuper ? 'super' : ($isAdmin ? 'admin' : 'user');
                    $jmlMenu = is_array($user->menu_access) ? count($user->menu_access) : 0;
                    $status  = $statusAkun[$user->id] ?? ['online' => false, 'keterangan' => ''];
                @endphp
                <tr>
                    <td class="col-index" style="color:var(--text-faint);">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                    <td class="cell-user">
                        <div class="user-info">
                            <div class="user-avatar av-{{ $ragam }}">
                                {{ initials($user->name) }}
                                @if($status['online'])<span class="presence-dot" title="Sedang online"></span>@endif
                            </div>
                            <div style="min-width:0;">
                                <div class="user-name">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())<span class="you-badge">Kamu</span>@endif
                                </div>
                                <div class="user-meta">
                                    <span class="user-email">{{ $user->email }}</span>
                                    @if($user->nik)
                                        {{-- titik pemisah & NIK satu unit: tidak terputus antar baris.
                                             Di layar sempit titiknya disembunyikan, baris ditumpuk. --}}
                                        <span class="user-nik"><span class="dot"></span>NIK {{ $user->nik }}</span>
                                    @else
                                        <span class="warn-badge">NIK belum diisi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="cell-role">
                        <span class="role-badge role-{{ $ragam }}">
                            <span class="rdot"></span>{{ $isSuper ? 'Super Admin' : ($isAdmin ? 'Admin' : 'User') }}
                        </span>
                    </td>
                    <td class="cell-status">
                        @if($status['online'])
                            <span class="presence on"><span class="pdot"></span>Online</span>
                        @elseif($status['keterangan'])
                            <span class="presence idle"><span class="pdot"></span>{{ $status['keterangan'] }}</span>
                        @else
                            <span class="presence off">Tidak aktif</span>
                        @endif
                    </td>
                    <td class="cell-akses">
                        @if($isSuper)
                            <span class="akses-chip full">Akses penuh</span>
                        @elseif($isAdmin)
                            @if($jmlMenu > 0)
                                <span class="akses-chip partial">{{ $jmlMenu }} dari {{ $totalMenu }} menu</span>
                            @else
                                <span class="akses-chip none">Belum diatur</span>
                            @endif
                        @else
                            <span class="akses-chip">Struktur Organisasi</span>
                        @endif
                    </td>
                    <td class="cell-tgl cell-date">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="cell-aksi">
                        <div class="action-btns">
                            <button type="button" class="btn-ghost"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-nik="{{ $user->nik }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-menus='@json($user->menu_access ?? [])'
                                data-url="{{ route('akun.update', $user) }}"
                                onclick="openEditModal(this)">
                                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button type="button"
                                class="btn-ghost danger {{ $user->id === auth()->id() ? 'disabled' : '' }}"
                                onclick="openHapusModal('{{ route('akun.destroy', $user) }}', @js($user->name))"
                                title="{{ $user->id === auth()->id() ? 'Tidak bisa hapus akun sendiri' : 'Hapus akun' }}">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                            <p>{{ request('search') || request('role') ? 'Tidak ada akun yang cocok' : 'Belum ada akun' }}</p>
                            <span>{{ request('search') || request('role') ? 'Coba ubah kata kunci atau saringan role.' : 'Tambahkan akun pertama lewat tombol di atas.' }}</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="table-footer">
        <span>Menampilkan <strong>{{ $users->firstItem() }}</strong>&ndash;<strong>{{ $users->lastItem() }}</strong> dari <strong>{{ $users->total() }}</strong></span>
        <div class="pagination-wrap">
            @if($users->onFirstPage())
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
            @endif
            @php $cur=$users->currentPage();$last=$users->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s > 1)
                <a href="{{ $users->url(1) }}" class="page-btn">1</a>
                @if($s > 2)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
            @endif
            @for($i = $s; $i <= $e; $i++)
                <a href="{{ $users->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
            @endfor
            @if($e < $last)
                @if($e < $last - 1)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                <a href="{{ $users->url($last) }}" class="page-btn">{{ $last }}</a>
            @endif
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
            @else
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    const TOTAL_MENU = {{ $totalMenu }};

    const roleDesc = {
        user:        '<strong>User</strong> — hanya dapat melihat dan mengekspor Struktur Organisasi.',
        admin:       '<strong>Admin</strong> — akses mengikuti menu yang dicentang di bawah.',
        super_admin: '<strong>Super Admin</strong> — akses penuh ke seluruh sistem, termasuk Master Data dan Manajemen Akun.',
    };

    /* Ganti role → perbarui keterangan, dan panel akses menu hanya tampil untuk Admin. */
    function onRoleChange(prefix, val) {
        const info = document.querySelector('#' + prefix + 'RoleInfo span');
        if (info) info.innerHTML = roleDesc[val] || '';
        const panel = document.getElementById(prefix + 'MenuPanel');
        if (panel) panel.style.display = (val === 'admin') ? '' : 'none';
        updateMenuCount(prefix);
    }

    function toggleAllMenus(boxId, checked = true) {
        document.querySelectorAll('#' + boxId + ' input[type=checkbox]').forEach(cb => cb.checked = checked);
        updateMenuCount(boxId.replace('MenuBox', ''));
    }

    function updateMenuCount(prefix) {
        const box = document.getElementById(prefix + 'MenuBox');
        const el  = document.getElementById(prefix + 'MenuCount');
        if (!box || !el) return;
        const checked = box.querySelectorAll('input[type=checkbox]:checked').length;
        el.textContent = checked + ' / ' + TOTAL_MENU + ' dipilih';
    }

    ['create', 'edit'].forEach(prefix => {
        const box = document.getElementById(prefix + 'MenuBox');
        if (box) box.addEventListener('change', () => updateMenuCount(prefix));
    });

    /* ── Modal ── */
    function openModal(id) {
        document.getElementById(id).classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('show');
        document.body.style.overflow = '';
    }

    function openTambahModal() {
        onRoleChange('create', document.getElementById('createRole').value);
        openModal('modalTambah');
    }

    function openEditModal(btn) {
        document.getElementById('editId').value    = btn.dataset.id;
        document.getElementById('editName').value  = btn.dataset.name;
        document.getElementById('editNik').value   = btn.dataset.nik;
        document.getElementById('editEmail').value = btn.dataset.email;
        document.getElementById('editRole').value  = btn.dataset.role;
        document.getElementById('formEdit').action = btn.dataset.url;
        document.getElementById('editSubtitle').textContent = btn.dataset.name;

        let menus = [];
        try { menus = JSON.parse(btn.dataset.menus || '[]'); } catch (e) { menus = []; }
        document.querySelectorAll('#editMenuBox input[type=checkbox]')
            .forEach(cb => cb.checked = menus.includes(cb.value));

        onRoleChange('edit', btn.dataset.role);
        openModal('modalEdit');
    }

    /* ── Hapus ── */
    let hapusUrl = '';
    function openHapusModal(url, nama) {
        hapusUrl = url;
        document.getElementById('hapusDesc').innerHTML =
            'Akun <strong>' + nama + '</strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.';
        openModal('modalHapus');
    }
    function submitHapus() {
        const f = document.getElementById('formHapus');
        f.action = hapusUrl;
        f.submit();
    }

    ['modalTambah', 'modalEdit', 'modalHapus'].forEach(id => {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) closeModal(id);
        });
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.show').forEach(m => closeModal(m.id));
        }
    });

    /* ── Pencarian ── */
    let cariTimer = null;
    const cari = document.getElementById('searchInput');
    if (cari) {
        cari.addEventListener('input', function () {
            document.getElementById('clearSearchBtn').classList.toggle('visible', this.value.length > 0);
            clearTimeout(cariTimer);
            cariTimer = setTimeout(() => document.getElementById('filterForm').submit(), 500);
        });
    }
    function clearSearch() {
        clearTimeout(cariTimer);          // batalkan submit tertunda dari ketikan terakhir
        cari.value = '';
        document.getElementById('filterForm').submit();
    }

    /* ── Toast ── */
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }

    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(closeToast, 3000);
        onRoleChange('create', document.getElementById('createRole').value);

        @if($errors->any() && $errCreate)
            openModal('modalTambah');
        @endif

        @if($errors->any() && $errEdit)
            (function () {
                const btn = document.querySelector('.btn-ghost[data-id="{{ old('edit_id') }}"]');
                if (!btn) return;
                openEditModal(btn);
                document.getElementById('editName').value  = @js(old('name'));
                document.getElementById('editNik').value   = @js(old('nik'));
                document.getElementById('editEmail').value = @js(old('email'));
                document.getElementById('editRole').value  = @js(old('role'));
                const menus = @js(old('menu_access', []));
                document.querySelectorAll('#editMenuBox input[type=checkbox]')
                    .forEach(cb => cb.checked = menus.includes(cb.value));
                onRoleChange('edit', @js(old('role')));
            })();
        @endif
    });
</script>
@endpush
