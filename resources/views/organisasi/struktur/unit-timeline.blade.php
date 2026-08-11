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

    .relation-card { background:#f5f3ff;border:1px solid #ddd6fe;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:12.5px;color:#5b21b6; }
    .relation-card a { color:inherit;font-weight:700;text-decoration:underline; }
    .successor-card { background:#f5f3ff;border:1px solid #ddd6fe;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:12.5px;color:#5b21b6; }
    .successor-card a { color:inherit;font-weight:700;text-decoration:underline; }
    .successor-card.bubar { background:#fef2f2;border-color:#fecaca;color:#dc2626; }

    .timeline-wrap { position:relative;padding-left:26px; }
    .timeline-wrap::before { content:'';position:absolute;left:6px;top:6px;bottom:6px;width:2px;background:#e5e7eb; }
    .timeline-point { position:relative;margin-bottom:20px; }
    .timeline-dot { position:absolute;left:-26px;top:4px;width:14px;height:14px;border-radius:50%;background:#15803d;border:3px solid #dcfce7; }
    .timeline-point.anomali .timeline-dot { background:#d97706;border-color:#fef3c7; }
    .timeline-card { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:16px 20px; }
    .timeline-versi { font-size:11.5px;color:#9ca3af;margin-bottom:4px; }
    .timeline-versi a { color:inherit;text-decoration:underline; }
    .timeline-nama { font-size:15px;font-weight:700;color:#111827;margin-bottom:8px; }
    .timeline-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:10px;font-size:12px;color:#374151;margin-bottom:8px; }
    .timeline-grid-item .label { font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px; }
    .timeline-grid-item .val { font-weight:600; }

    .badge-jenis { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;margin-bottom:8px; }
    .badge-jenis.baru { background:#eff6ff;color:#1d4ed8; }
    .badge-jenis.rename { background:#f0fdf4;color:#15803d; }
    .badge-jenis.pindah_induk { background:#fffbeb;color:#92400e; }
    .badge-jenis.ganti_level { background:#fdf4ff;color:#a21caf; }
    .badge-jenis.pecah, .badge-jenis.gabung { background:#f5f3ff;color:#7c3aed; }

    .perubahan-list { list-style:none;padding:0;margin:8px 0 0;display:flex;flex-direction:column;gap:4px; }
    .perubahan-list li { font-size:12px;color:#374151;background:#fafafa;border-radius:7px;padding:6px 10px; }
    .anomali-note { font-size:11px;color:#d97706;font-weight:600;margin-top:8px; }

    @media (max-width:640px) {
        .timeline-grid { grid-template-columns:1fr 1fr; }
    }
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

@if($asalDari->count())
<div class="relation-card">
    🔗 Unit ini terbentuk dari
    {{ $asalDari->first()['jenis'] === 'gabung' ? 'penggabungan' : 'pemecahan' }}:
    @foreach($asalDari as $i => $a)
        <a href="{{ route('organisasi.unit.timeline', $a['unit_organisasi_id']) }}">{{ $a['nama'] }}</a>{{ !$loop->last ? ', ' : '' }}
    @endforeach
</div>
@endif

@if($statusInfo['jenis'] !== 'aktif' && count($statusInfo['successors']))
<div class="successor-card {{ $statusInfo['jenis'] === 'bubar' ? 'bubar' : '' }}">
    @if($statusInfo['jenis'] === 'pecah')
        ✂️ Unit ini terpecah menjadi:
        @foreach($statusInfo['successors'] as $s)
            <a href="{{ route('organisasi.unit.timeline', $s['unit_organisasi_id']) }}">{{ $s['nama'] }}</a>{{ !$loop->last ? ', ' : '' }}
        @endforeach
    @elseif($statusInfo['jenis'] === 'gabung')
        🔗 Unit ini bergabung menjadi:
        @foreach($statusInfo['successors'] as $s)
            <a href="{{ route('organisasi.unit.timeline', $s['unit_organisasi_id']) }}">{{ $s['nama'] }}</a>{{ !$loop->last ? ', ' : '' }}
        @endforeach
    @endif
</div>
@elseif($statusInfo['jenis'] === 'bubar')
<div class="successor-card bubar">🗑️ Unit ini sudah bubar dan tidak memiliki penerus.</div>
@endif

<div class="timeline-wrap">
    @foreach($points as $p)
    <div class="timeline-point {{ $p['anomali'] ? 'anomali' : '' }}">
        <div class="timeline-dot"></div>
        <div class="timeline-card">
            <div class="timeline-versi">
                <a href="{{ route('organisasi.struktur.show', $p['versi']) }}">SK {{ $p['versi']->nomor_sk }}</a>
                &middot; {{ $p['versi']->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
            </div>

            @if($p['jenis_resmi'])
            <span class="badge-jenis {{ $p['jenis_resmi'] }}">
                {{ ['baru' => 'Baru', 'rename' => 'Rename', 'pindah_induk' => 'Pindah Induk', 'ganti_level' => 'Ganti Level', 'pecah' => 'Pecah', 'gabung' => 'Gabung'][$p['jenis_resmi']] ?? ucfirst($p['jenis_resmi']) }}
            </span>
            @endif

            <div class="timeline-nama">{{ $p['nama_unit'] }}</div>

            <div class="timeline-grid">
                <div class="timeline-grid-item">
                    <div class="label">Level</div>
                    <div class="val">{{ ucfirst($p['level']) }}</div>
                </div>
                <div class="timeline-grid-item">
                    <div class="label">Parent</div>
                    <div class="val">{{ $p['parent_nama'] }}</div>
                </div>
                <div class="timeline-grid-item">
                    <div class="label">Formasi Unit</div>
                    <div class="val">{{ $p['mc_formasi'] }}</div>
                </div>
            </div>

            @if(count($p['perubahan']))
            <ul class="perubahan-list">
                @foreach($p['perubahan'] as $c)
                <li>{{ $c }}</li>
                @endforeach
            </ul>
            @endif

            @if($p['anomali'])
            <div class="anomali-note">⚠️ Perubahan ini tidak tercatat sebagai transisi resmi di versi ini — perlu dicek ulang.</div>
            @endif
        </div>
    </div>
    @endforeach
</div>

@endsection
