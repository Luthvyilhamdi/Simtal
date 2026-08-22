@extends('layouts.app')
@section('title', 'Timeline Unit')
@section('breadcrumb-parent', 'Cari Unit')
@section('breadcrumb', 'Timeline')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .status-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap; }
    .status-aktif { background:#dcfce7;color:#15803d; }
    .status-bubar { background:#fef2f2;color:#dc2626; }
    .status-pecah, .status-gabung { background:#f5f3ff;color:#7c3aed; }
    .status-tidak_jelas { background:#f3f4f6;color:#6b7280; }

    .view-tabs { display:flex;gap:8px;margin-bottom:16px; }
    .view-tab { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12.5px;font-weight:600;border:1px solid #e5e7eb;background:white;color:#6b7280;cursor:pointer;font-family:inherit; }
    .view-tab svg { width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2; }
    .view-tab:hover { background:#f9fafb; }
    .view-tab.active { background:#15803d;border-color:#15803d;color:white; }
    [x-cloak] { display:none !important; }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.search') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Cari Unit
</a>

<div class="page-header">
    <div>
        <div class="page-title">
            {{ end($points)['nama_unit'] }}
            <span class="status-badge status-{{ $statusInfo['jenis'] }}">{{ $statusInfo['label'] }}</span>
        </div>
        <div class="page-sub">Timeline unit ini melintasi {{ count($points) }} versi final</div>
    </div>
</div>

<div x-data="{ tab: 'diagram' }">
    <div class="view-tabs">
        <button type="button" class="view-tab" :class="{ 'active': tab === 'diagram' }" @click="tab = 'diagram'">
            <svg viewBox="0 0 24 24"><circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="12" cy="18" r="2.5"/><path d="M6 8.5V12a4 4 0 0 0 4 4M18 8.5V12a4 4 0 0 0-4 4"/></svg>
            Tampilan Diagram
        </button>
        <button type="button" class="view-tab" :class="{ 'active': tab === 'list' }" @click="tab = 'list'">
            <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Tampilan List
        </button>
        <button type="button" class="view-tab" :class="{ 'active': tab === 'job-profile' }" @click="tab = 'job-profile'">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Job Profile
        </button>
    </div>

    <div x-show="tab === 'diagram'" x-cloak>
        @include('organisasi.struktur.partials.unit-timeline-diagram', ['graph' => $graph])
    </div>

    <div x-show="tab === 'list'" x-cloak>
        {{-- Sumber data SAMA PERSIS dgn Tab Diagram di atas (GenealogyGraphBuilder +
             GenealogyBandLayout, lewat $graph) — sebelumnya tab ini pakai $points (cuma
             rantai identitas unit ini sendiri, TIDAK lintas pecah/gabung/rename ke
             identitas lain, jadi suka berhenti di tengah jalan). Partial KHUSUS tab ini
             (bukan partials.riwayat-narasi-list yg dipakai overlay riwayat Tree View &
             Detail Versi) krn nama unit di sini diberi prefix level. --}}
        @include('organisasi.struktur.partials.riwayat-narasi-list-tab', ['graph' => $graph])
    </div>

    <div x-show="tab === 'job-profile'" x-cloak>
        @include('organisasi.struktur.partials.unit-timeline-job-profile', ['jobProfileTimeline' => $jobProfileTimeline])
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
