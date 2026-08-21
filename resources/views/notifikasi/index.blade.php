@extends('layouts.app')
@section('title', 'Notifikasi')
@section('breadcrumb', 'Notifikasi')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .notif-list { display:flex;flex-direction:column;gap:10px; }
    .notif-item { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:16px 18px;display:flex;align-items:flex-start;gap:14px;transition:box-shadow 0.15s; }
    .notif-item:hover { box-shadow:var(--card-shadow-hover); }
    .notif-item.unread { border-left:3px solid #16a34a; }
    .notif-item.unread.warning { border-left-color:#f59e0b; }
    .notif-item.unread.danger { border-left-color:#ef4444; }

    .notif-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
    .notif-content { flex:1;min-width:0; }
    .notif-judul { font-size:14px;font-weight:700;color:#111827;margin-bottom:4px; }
    .notif-pesan { font-size:13px;color:#6b7280;line-height:1.5; }
    .notif-waktu { font-size:11px;color:#9ca3af;margin-top:6px; }
    .notif-right { display:flex;align-items:center;gap:8px;flex-shrink:0; }
    .unread-dot { width:8px;height:8px;border-radius:50%;background:#16a34a;flex-shrink:0; }

    .btn-read { font-size:11px;color:#16a34a;border:none;background:transparent;cursor:pointer;font-weight:600;white-space:nowrap; }
    .btn-del-notif { width:26px;height:26px;border-radius:6px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-del-notif:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del-notif svg { width:12px;height:12px;stroke:#ef4444;fill:none;stroke-width:2; }

    .filter-row { display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap; }
    .filter-chip { padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid #e5e7eb;background:white;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .filter-chip:hover { background:#f5f5f0; }
    .filter-chip.active { background:#15803d;color:white;border-color:#15803d; }

    .btn-read-all { display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;background:#f0fdf4;color:#15803d;font-size:13px;font-weight:600;border:1px solid #bbf7d0;cursor:pointer;font-family:inherit;transition:all 0.15s;text-decoration:none; }
    .btn-read-all:hover { background:#dcfce7; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:16px 0;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px;margin-top:8px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px;background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);color:#9ca3af; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">🔔 Notifikasi</div>
        <div class="page-sub"><span id="unreadCount">{{ $unread }}</span> belum dibaca · <span id="totalCount">{{ $notifikasis->total() }}</span> total</div>
    </div>
    <button type="button" class="btn-read-all" id="btnReadAll" onclick="readAllNotifIndex()"
            style="{{ $unread > 0 ? '' : 'display:none;' }}">
        ✓ Tandai Semua Dibaca
    </button>
</div>

{{-- Filter --}}
@php
    // Sembunyikan chip filter untuk jenis notifikasi yang tak boleh dilihat user.
    $u = auth()->user();
    $tipeMenu = \App\Support\MenuAccess::notifikasiTypeMenu();
    $canSeeTipe = fn ($tipe) => $u->isSuperAdmin() || !isset($tipeMenu[$tipe]) || $u->canAccessMenu($tipeMenu[$tipe]);
@endphp
<div class="filter-row">
    <a href="{{ route('notifikasi.index') }}"
       class="filter-chip {{ !request('tipe') ? 'active' : '' }}">Semua</a>
    @if($canSeeTipe('idp_expire'))
    <a href="{{ route('notifikasi.index', ['tipe' => 'idp_expire']) }}"
       class="filter-chip {{ request('tipe') == 'idp_expire' ? 'active' : '' }}">📋 Assessment Expire</a>
    @endif
    @if($canSeeTipe('pensiun'))
    <a href="{{ route('notifikasi.index', ['tipe' => 'pensiun']) }}"
       class="filter-chip {{ request('tipe') == 'pensiun' ? 'active' : '' }}">🎯 Pensiun</a>
    @endif
    @if($canSeeTipe('masa_kerja'))
    <a href="{{ route('notifikasi.index', ['tipe' => 'masa_kerja']) }}"
       class="filter-chip {{ request('tipe') == 'masa_kerja' ? 'active' : '' }}">🏆 Masa Kerja</a>
    @endif
    @if($canSeeTipe('pgs_pjs_berakhir'))
    <a href="{{ route('notifikasi.index', ['tipe' => 'pgs_pjs_berakhir']) }}"
       class="filter-chip {{ request('tipe') == 'pgs_pjs_berakhir' ? 'active' : '' }}">⏰ PGS/PJS</a>
    @endif
</div>

@if($notifikasis->count() > 0)

<div class="notif-list">
    @foreach($notifikasis as $n)
    @php $w = $n->warna; @endphp
    <div class="notif-item {{ !$n->is_read ? 'unread ' . $n->level : '' }}"
         id="notif-{{ $n->id }}" data-id="{{ $n->id }}"
         style="{{ !$n->is_read ? 'background:'.$w['bg'].';' : '' }}">
        <div class="notif-icon" style="background:{{ $w['bg'] }};border:1px solid {{ $w['border'] }};">
            {{ $n->icon }}
        </div>
        <div class="notif-content">
            <div class="notif-judul" style="{{ !$n->is_read ? 'color:'.$w['text'].';' : '' }}">
                {{ $n->judul }}
            </div>
            <div class="notif-pesan">{{ $n->pesan }}</div>
            <div class="notif-waktu">{{ $n->created_at->diffForHumans() }}</div>
        </div>
        <div class="notif-right">
            @if(!$n->is_read)
                <button type="button" class="btn-read js-read" onclick="markReadIndex({{ $n->id }})">Tandai dibaca</button>
                <div class="unread-dot js-dot" style="background:{{ $w['text'] }};"></div>
            @endif
            <button type="button" class="btn-del-notif" title="Hapus" onclick="deleteNotifIndex({{ $n->id }})">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    </div>
    @endforeach
</div>

<div class="table-footer">
    <span>
        Menampilkan <strong>{{ $notifikasis->firstItem() }}</strong>–<strong>{{ $notifikasis->lastItem() }}</strong>
        dari <strong>{{ $notifikasis->total() }}</strong>
    </span>

    @if($notifikasis->hasPages())
    <div class="pagination-wrap">

        {{-- Prev --}}
        @if($notifikasis->onFirstPage())
            <span class="page-btn disabled">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            </span>
        @else
            <a href="{{ $notifikasis->previousPageUrl() }}" class="page-btn">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
        @endif

        {{-- Nomor Halaman --}}
        @php
            $cur  = $notifikasis->currentPage();
            $last = $notifikasis->lastPage();
            $s    = max(1, $cur - 2);
            $e    = min($last, $cur + 2);
        @endphp

        @if($s > 1)
            <a href="{{ $notifikasis->url(1) }}" class="page-btn">1</a>
            @if($s > 2)
                <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
            @endif
        @endif

        @for($i = $s; $i <= $e; $i++)
            <a href="{{ $notifikasis->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
        @endfor

        @if($e < $last)
            @if($e < $last - 1)
                <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
            @endif
            <a href="{{ $notifikasis->url($last) }}" class="page-btn">{{ $last }}</a>
        @endif

        {{-- Next --}}
        @if($notifikasis->hasMorePages())
            <a href="{{ $notifikasis->nextPageUrl() }}" class="page-btn">
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
    <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px;">Tidak ada notifikasi</p>
    <span style="font-size:12px;">Notifikasi akan muncul otomatis setiap hari</span>
</div>

@endif

@endsection

@push('scripts')
<script>
    const CSRF = '{{ csrf_token() }}';

    function currentUnread() {
        return parseInt(document.getElementById('unreadCount').textContent) || 0;
    }
    function setUnread(n) {
        n = Math.max(0, n);
        document.getElementById('unreadCount').textContent = n;
        const btn = document.getElementById('btnReadAll');
        if (btn) btn.style.display = n > 0 ? '' : 'none';
    }
    // Ubah tampilan kartu jadi "sudah dibaca"
    function markCardRead(card) {
        if (!card) return;
        card.classList.remove('unread', 'info', 'warning', 'danger');
        card.style.background = '';
        const judul = card.querySelector('.notif-judul');
        if (judul) judul.style.color = '';
        card.querySelectorAll('.js-read, .js-dot').forEach(el => el.remove());
    }

    // Tandai 1 notifikasi sudah dibaca
    async function markReadIndex(id) {
        const card = document.getElementById('notif-' + id);
        try {
            await fetch(`/notifikasi/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
        } catch (e) { return; }
        if (card && card.classList.contains('unread')) setUnread(currentUnread() - 1);
        markCardRead(card);
    }

    // Hapus 1 notifikasi (langsung hilang tanpa pindah halaman)
    async function deleteNotifIndex(id) {
        const card = document.getElementById('notif-' + id);
        try {
            await fetch(`/notifikasi/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
        } catch (e) { return; }
        if (card) {
            if (card.classList.contains('unread')) setUnread(currentUnread() - 1);
            card.remove();
        }
        const totalEl = document.getElementById('totalCount');
        if (totalEl) totalEl.textContent = Math.max(0, (parseInt(totalEl.textContent) || 0) - 1);
        // Bila halaman jadi kosong, muat ulang agar empty-state / paginasi benar
        if (!document.querySelector('.notif-item')) location.reload();
    }

    // Tandai semua sudah dibaca (untuk akun ini)
    async function readAllNotifIndex() {
        try {
            await fetch('{{ route('notifikasi.readAll') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
        } catch (e) { return; }
        document.querySelectorAll('.notif-item.unread').forEach(markCardRead);
        setUnread(0);
    }
</script>
@endpush