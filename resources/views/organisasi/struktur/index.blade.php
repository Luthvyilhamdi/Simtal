@extends('layouts.app')
@section('title', 'Riwayat Struktur Organisasi')
@section('breadcrumb-parent', 'Organization & HC Strategy')
@section('breadcrumb', 'Riwayat Struktur Organisasi')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }

    .btn-add { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap; }
    .btn-add:hover { background:#166534; }
    .btn-add svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }

    .table-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:640px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:12px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .sk-nomor { font-weight:700;color:#111827; }
    .badge-aktif { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#dcfce7;color:#15803d; }
    .badge-lampau { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#f3f4f6;color:#6b7280; }
    .badge-draft { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#fffbeb;color:#92400e; }
    .badge-final { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#eff6ff;color:#1d4ed8; }
    .muted { color:#9ca3af; }
    .count-pill { display:inline-flex;align-items:center;gap:5px; }
    .count-num { font-size:15px;font-weight:700;color:#111827; }
    .count-label { font-size:11px;color:#9ca3af; }

    .btn-view { display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:7px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.12s;white-space:nowrap; }
    .btn-view:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    .btn-view svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:50px 20px;color:#9ca3af; }
    .empty-state svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    .compare-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:16px; }
    .compare-title { font-size:13px;font-weight:700;color:#111827;margin-bottom:2px; }
    .compare-sub { font-size:11.5px;color:#9ca3af;margin-bottom:14px; }
    .compare-row { display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap; }
    .compare-group { display:flex;flex-direction:column;gap:5px;min-width:220px;flex:1; }
    .compare-label { font-size:10.5px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px; }
    .compare-select { padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12.5px;font-family:inherit;color:#111827;background:#fafafa;width:100%; }
    .compare-select:focus { border-color:#16a34a;background:white;outline:none; }
    .compare-arrow { padding-bottom:9px;color:#9ca3af; }
    .btn-compare { display:inline-flex;align-items:center;gap:6px;background:#111827;color:white;padding:9px 18px;border-radius:8px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .btn-compare:hover { background:#1f2937; }
    .compare-hint { font-size:11px;color:#9ca3af;margin-top:8px; }

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
        <div class="page-title">Riwayat Struktur Organisasi</div>
        <div class="page-sub">{{ $versiList->count() }} versi tercatat</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('organisasi.struktur.search') }}" class="btn-view" style="padding:9px 16px;">
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Cari Unit
        </a>
        @if($versiList->isEmpty())
        <a href="{{ route('organisasi.struktur.import') }}" class="btn-view" style="padding:9px 16px;">
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import dari Excel
        </a>
        @endif
        <a href="{{ route('organisasi.struktur.create') }}" class="btn-add">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Input Versi Baru
        </a>
    </div>
</div>

@php
    $versiFinal = $versiList->filter(fn ($v) => $v->isFinal())->sortBy('tanggal_mulai_berlaku')->values();
    $preselect  = request('bandingkan');
@endphp

@if($versiFinal->isNotEmpty())
<div class="compare-card">
    <div class="compare-title">Import Versi Lanjutan dari Excel</div>
    <div class="compare-sub">Transkrip bagan versi berikutnya (relatif terhadap salah satu versi final yang sudah ada) lewat template Excel — sistem akan meresolusi transisi &amp; mendeteksi kandidat bubar otomatis.</div>
    <a href="{{ route('organisasi.struktur.import-lanjutan') }}" class="btn-view" style="padding:9px 16px;display:inline-flex;">
        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Import Versi Lanjutan
    </a>
</div>
@endif

@if($versiFinal->count() >= 2)
<div class="compare-card">
    <div class="compare-title">Bandingkan Antar Versi</div>
    <div class="compare-sub">Pilih 2 versi yang sudah final untuk melihat ringkasan perubahan di antara keduanya.</div>
    <form method="GET" action="{{ route('organisasi.struktur.compare') }}" class="compare-row">
        <div class="compare-group">
            <label class="compare-label">Versi Lama</label>
            <select name="lama" class="compare-select" required>
                <option value="">— Pilih versi —</option>
                @foreach($versiFinal as $v)
                    <option value="{{ $v->id }}" {{ (string) $preselect === (string) $v->id ? 'selected' : '' }}>
                        {{ $v->nomor_sk }} · {{ $v->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="compare-arrow">→</div>
        <div class="compare-group">
            <label class="compare-label">Versi Baru</label>
            <select name="baru" class="compare-select" required>
                <option value="">— Pilih versi —</option>
                @foreach($versiFinal as $v)
                    <option value="{{ $v->id }}">
                        {{ $v->nomor_sk }} · {{ $v->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-compare">
            <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3M16 3h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-3M12 8v8M9 11l3-3 3 3"/></svg>
            Bandingkan
        </button>
    </form>
    <div class="compare-hint">Versi "baru" harus punya tanggal mulai berlaku setelah versi "lama". Hanya versi berstatus final yang bisa dibandingkan.</div>
</div>
@endif

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nomor SK</th>
                    <th>Tanggal SK</th>
                    <th>Mulai Berlaku</th>
                    <th>Berakhir</th>
                    <th>Jumlah Unit</th>
                    <th>Status</th>
                    <th>Tahap</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($versiList as $versi)
                <tr>
                    <td class="sk-nomor">{{ $versi->nomor_sk }}</td>
                    <td>{{ $versi->tanggal_sk->translatedFormat('d F Y') }}</td>
                    <td>{{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</td>
                    <td>
                        @if($versi->tanggal_berakhir)
                            {{ $versi->tanggal_berakhir->translatedFormat('d F Y') }}
                        @else
                            <span class="muted">masih berlaku</span>
                        @endif
                    </td>
                    <td>
                        <div class="count-pill">
                            <span class="count-num">{{ $versi->unit_organisasi_snapshots_count }}</span>
                            <span class="count-label">unit</span>
                        </div>
                    </td>
                    <td>
                        @if(!$versi->tanggal_berakhir)
                            <span class="badge-aktif">Aktif</span>
                        @else
                            <span class="badge-lampau">Lampau</span>
                        @endif
                    </td>
                    <td>
                        @if($versi->isDraft())
                            <span class="badge-draft">📝 Draft</span>
                        @else
                            <span class="badge-final">🔒 Final</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('organisasi.struktur.show', $versi) }}" class="btn-view">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Lihat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                            Belum ada versi struktur organisasi. Mulai dengan membuat versi pertama (baseline).
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
