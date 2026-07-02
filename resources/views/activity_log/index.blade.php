@extends('layouts.app')
@section('title', 'Log Aktivitas')
@section('breadcrumb', 'Log Aktivitas')

@push('styles')
<style>
    .page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .filter-row { display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap; }
    .search-bar { display:flex;align-items:center;gap:10px;background:white;border:1px solid #e5e7eb;border-radius:9px;padding:9px 14px;flex:1;min-width:200px; }
    .search-bar input { border:none;outline:none;font-size:13px;font-family:inherit;flex:1;color:#111827;background:transparent; }
    .search-bar svg { width:15px;height:15px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .filter-select { padding:9px 14px;border:1px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#374151;background:white;outline:none;cursor:pointer; }
    .filter-select:focus { border-color:#16a34a; }
    .btn-reset { display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:9px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:13px;font-weight:500;text-decoration:none; }
    .btn-reset:hover { background:#f5f5f0; }
    .btn-hapus-log { display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:9px;border:1px solid #fecaca;background:#fef2f2;color:#dc2626;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
    .btn-hapus-log:hover { background:#fee2e2; }

    .log-list { display:flex;flex-direction:column;gap:8px; }
    .log-item { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px 18px;display:flex;align-items:flex-start;gap:14px;transition:box-shadow 0.15s; }
    .log-item:hover { box-shadow:var(--card-shadow-hover); }
    .log-icon { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
    .log-body { flex:1;min-width:0; }
    .log-top { display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px; }
    .log-user { font-size:13px;font-weight:700;color:#111827; }
    .log-aksi { font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px; }
    .log-modul { font-size:11px;color:#9ca3af;font-weight:600; }
    .log-target { font-size:13px;color:#374151;margin-bottom:2px; }
    .log-ket { font-size:12px;color:#9ca3af;font-style:italic; }
    .log-right { text-align:right;flex-shrink:0; }
    .log-time { font-size:11px;color:#9ca3af; }
    .log-ip { font-size:10px;color:#d1d5db;margin-top:2px; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:16px 0;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px;margin-top:8px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px;background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);color:#9ca3af; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:400px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center;animation:modalIn 0.25s cubic-bezier(0.4,0,0.2,1); }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:17px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:24px; }
    .modal-actions { display:flex;gap:10px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    @media (max-width:640px) { .filter-row { flex-direction:column; } }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- Modal Hapus Semua --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus Semua Log?</div>
        <div class="modal-desc">Seluruh log aktivitas akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus Semua</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" action="{{ route('activity_log.destroy') }}" style="display:none">
    @csrf
    @method('DELETE')
</form>

{{-- Page Header --}}
<div class="page-header">
    <div>
        <div class="page-title">Log Aktivitas</div>
        <div class="page-sub">Riwayat semua aktivitas di sistem SIMTAL</div>
    </div>
    <button type="button" class="btn-hapus-log" onclick="openModal()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
        Hapus Semua Log
    </button>
</div>

{{-- Filter --}}
<form method="GET" id="filterForm">
    <div class="filter-row">
        <div class="search-bar">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama user, target, keterangan..."
                   onchange="document.getElementById('filterForm').submit()" />
        </div>
        <select name="aksi" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Aksi</option>
            @php
            $aksiList = [
                'tambah'      => 'Tambah',
                'edit'        => 'Edit',
                'hapus'       => 'Hapus',
                'import'      => 'Import',
                'export'      => 'Export',
                'login'       => 'Login',
                'logout'      => 'Logout',
                'akhiri'      => 'Akhiri',
                'assign'      => 'Assign Karyawan',
                'salin'       => 'Salin Periode',
                'edit_posisi' => 'Edit Posisi',
            ];
            @endphp
            @foreach($aksiList as $val => $label)
                <option value="{{ $val }}" {{ request('aksi') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="modul" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Modul</option>
            @foreach($moduls as $m)
                <option value="{{ $m }}" {{ request('modul') === $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
               class="filter-select" onchange="this.form.submit()" />
        @if(request()->hasAny(['search','aksi','modul','tanggal']))
            <a href="{{ route('activity_log.index') }}" class="btn-reset">Reset</a>
        @endif
    </div>
</form>

{{-- Log List --}}
@if($logs->count() > 0)
<div class="log-list">
    @foreach($logs as $log)
    @php
        /** @var \App\Models\ActivityLog $log */
        $w = $log->warna;
    @endphp
    <div class="log-item">
        <div class="log-icon" style="background:{{ $w['bg'] }};border:1px solid {{ $w['border'] }};">
            {{ $log->icon }}
        </div>
        <div class="log-body">
            <div class="log-top">
                <span class="log-user">{{ $log->user_name }}</span>
                <span class="log-aksi" style="background:{{ $w['bg'] }};color:{{ $w['text'] }};">
                    {{ $log->label_aksi }}
                </span>
                <span class="log-modul">{{ $log->modul }}</span>
            </div>
            @if($log->target)
                <div class="log-target">&#8594; <strong>{{ $log->target }}</strong></div>
            @endif
            @if($log->keterangan)
                <div class="log-ket">{{ $log->keterangan }}</div>
            @endif
        </div>
        <div class="log-right">
            <div class="log-time">{{ $log->created_at->diffForHumans() }}</div>
            <div style="font-size:10px;color:#9ca3af;margin-top:2px;">
                {{ $log->created_at->format('d M Y H:i') }}
            </div>
            @if($log->ip_address)
                <div class="log-ip">{{ $log->ip_address }}</div>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="table-footer">
    <span>Menampilkan <strong>{{ $logs->firstItem() }}</strong>&#8211;<strong>{{ $logs->lastItem() }}</strong> dari <strong>{{ $logs->total() }}</strong> log</span>
    @if($logs->hasPages())
    <div class="pagination-wrap">
        @if($logs->onFirstPage())
            <span class="page-btn disabled">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            </span>
        @else
            <a href="{{ $logs->previousPageUrl() }}" class="page-btn">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
        @endif

        @php
            $cur  = $logs->currentPage();
            $last = $logs->lastPage();
            $s    = max(1, $cur - 2);
            $e    = min($last, $cur + 2);
        @endphp

        @if($s > 1)
            <a href="{{ $logs->url(1) }}" class="page-btn">1</a>
            @if($s > 2)
                <span class="page-btn disabled" style="border:none;background:transparent;">&#8230;</span>
            @endif
        @endif

        @for($i = $s; $i <= $e; $i++)
            <a href="{{ $logs->url($i) }}" class="page-btn {{ $i === $cur ? 'active' : '' }}">{{ $i }}</a>
        @endfor

        @if($e < $last)
            @if($e < $last - 1)
                <span class="page-btn disabled" style="border:none;background:transparent;">&#8230;</span>
            @endif
            <a href="{{ $logs->url($last) }}" class="page-btn">{{ $last }}</a>
        @endif

        @if($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="page-btn">
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

@else
<div class="empty-state">
    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px;">Belum ada log aktivitas</p>
    <span style="font-size:12px;">Log akan muncul setiap kali ada aktivitas di sistem</span>
</div>
@endif

@endsection

@push('scripts')
<script>
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000);
    });

    function openModal() {
        document.getElementById('modalHapus').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        document.getElementById('modalHapus').classList.remove('show');
        document.body.style.overflow = '';
    }
    function submitHapus() {
        document.getElementById('formHapus').submit();
    }
    document.getElementById('modalHapus').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
</script>
@endpush