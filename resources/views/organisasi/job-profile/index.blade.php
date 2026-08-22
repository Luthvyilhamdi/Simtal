@extends('layouts.app')
@section('title', 'Job Profile')
@section('breadcrumb-parent', 'Organisasi')
@section('breadcrumb', 'Job Profile')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

    .table-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:640px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:12px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .sk-nomor { font-weight:700;color:#111827; }
    .muted { color:#9ca3af; }

    .progress-wrap { display:flex;flex-direction:column;gap:5px;min-width:180px; }
    .progress-text { font-size:12px;font-weight:600;color:#374151; }
    .progress-text .num { color:#111827;font-weight:700; }
    .progress-bar-track { height:6px;border-radius:4px;background:#f3f4f6;overflow:hidden; }
    .progress-bar-fill { height:100%;border-radius:4px;background:#16a34a; }
    .progress-bar-fill.empty { background:#e5e7eb; }

    .btn-view { display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:7px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.12s;white-space:nowrap; }
    .btn-view:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-view svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:50px 20px;color:#9ca3af; }
    .empty-state svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.error { border-color:#fecaca;border-left-color:#ef4444;color:#dc2626; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast.error .toast-icon { background:#fef2f2; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast.error .toast-icon svg { stroke:#ef4444; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 4s linear forwards; }
    .toast.error .toast-progress { background:#ef4444; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }
</style>
@endpush

@section('content')

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

<div class="page-header">
    <div>
        <div class="page-title">Job Profile</div>
        <div class="page-sub">Arsip file Job Profile per posisi, per versi struktur organisasi final ({{ $versiList->count() }} versi)</div>
    </div>
</div>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nomor SK</th>
                    <th>Mulai Berlaku</th>
                    <th>Jumlah Unit</th>
                    <th>Progress Job Profile</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($versiList as $versi)
                @php
                    $totalUnit = $versi->unit_organisasi_snapshots_count;
                    $withProfile = $unitWithProfileCountByVersi[$versi->id] ?? 0;
                    $pct = $totalUnit > 0 ? round(($withProfile / $totalUnit) * 100) : 0;
                @endphp
                <tr>
                    <td class="sk-nomor">{{ $versi->nomor_sk }}</td>
                    <td>{{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</td>
                    <td>{{ $totalUnit }} unit</td>
                    <td>
                        <div class="progress-wrap">
                            <div class="progress-text"><span class="num">{{ $withProfile }}</span> dari {{ $totalUnit }} unit sudah punya minimal 1 Job Profile</div>
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill {{ $withProfile === 0 ? 'empty' : '' }}" style="width:{{ $pct }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('organisasi.job-profile.show', $versi) }}" class="btn-view">
                            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Lihat Job Profile
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Belum ada versi struktur organisasi berstatus final. Job Profile hanya bisa dikelola untuk versi yang sudah final.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

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
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 4000);
    });
</script>
@endpush
