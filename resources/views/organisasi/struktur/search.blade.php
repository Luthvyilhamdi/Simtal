@extends('layouts.app')
@section('title', 'Cari Unit Organisasi')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Cari Unit')

@push('styles')
<style>
    [x-cloak] { display:none !important; }

    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .filter-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:16px; }
    .filter-row { display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap; }
    .filter-group { display:flex;flex-direction:column;gap:5px;flex:1;min-width:180px; }
    .filter-group.grow { flex:2; }
    .filter-label { font-size:10.5px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px; }
    .filter-input, .filter-select { padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12.5px;font-family:inherit;color:#111827;background:#fafafa;width:100%; }
    .filter-input:focus, .filter-select:focus { border-color:#16a34a;background:white;outline:none; }
    .btn-search { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:9px 18px;border-radius:8px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .btn-search:hover { background:#166534; }
    .btn-reset { display:inline-flex;align-items:center;padding:9px 14px;border-radius:8px;font-size:12.5px;font-weight:600;color:#6b7280;text-decoration:none;border:1.5px solid #e5e7eb;background:white; }
    .btn-reset:hover { background:#f9fafb; }

    .result-list { display:flex;flex-direction:column;gap:10px; }
    .result-card { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap; }
    .result-main { flex:1;min-width:220px; }
    .result-nama { font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
    .result-level { font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:6px;background:#f3f4f6;color:#374151;text-transform:capitalize; }
    .result-sebelumnya { font-size:11.5px;color:#9ca3af;margin-top:4px; }

    .status-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap; }
    .status-aktif { background:#dcfce7;color:#15803d; }
    .status-bubar { background:#fef2f2;color:#dc2626; }
    .status-pecah, .status-gabung { background:#f5f3ff;color:#7c3aed; }
    .status-tidak_jelas { background:#f3f4f6;color:#6b7280; }

    .successor-list { font-size:11.5px;color:#7c3aed;margin-top:3px; }
    .successor-list a { color:inherit;font-weight:600;text-decoration:underline; }

    .btn-timeline { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap; }
    .btn-timeline:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }

    .empty-state { text-align:center;padding:50px 20px;color:#9ca3af;background:white;border-radius:var(--radius);border:1px solid var(--card-border); }
    .empty-state svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    .transisi-filter { width:100%;display:flex;flex-direction:column;gap:8px;padding-top:12px;margin-top:2px;border-top:1px dashed #e5e7eb; }
    .transisi-checkbox-row { display:flex;flex-wrap:wrap;gap:8px 14px; }
    .transisi-checkbox { display:inline-flex;align-items:center;gap:6px;font-size:12px;color:#374151;cursor:pointer;user-select:none; }
    .transisi-checkbox input { width:15px;height:15px;accent-color:#15803d;cursor:pointer; }
    .transisi-subfilter { margin-left:2px;padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px; }
    .transisi-subfilter .transisi-checkbox { color:#166534;font-weight:600; }

    .event-badge-list { display:flex;flex-direction:column;gap:5px;margin-top:8px;width:100%; }
    .event-badge { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:7px 11px;font-size:11.5px;color:#166534;line-height:1.45; }
    .event-badge strong { color:#14532d; }
    .event-badge-jenis { display:inline-block;font-weight:700;text-transform:uppercase;letter-spacing:.3px;font-size:9.5px;color:#15803d;margin-right:6px; }
    .event-badge-tanggal { color:#4d7c0f;font-size:10.5px;margin-left:6px;white-space:nowrap; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:12px 16px;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px;margin-top:4px; }
    .pagination-wrap { display:flex;align-items:center;gap:3px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    @media (max-width:640px) {
        .result-card { flex-direction:column;align-items:flex-start; }
    }
</style>
@endpush

@php
    $jenisTransisiLabels = [
        'rename'       => 'Ganti Nama',
        'pindah_induk' => 'Pindah Induk',
        'ganti_level'  => 'Ganti Level',
        'pecah'        => 'Pecah',
        'gabung'       => 'Gabung',
        'bubar'        => 'Bubar',
        'baru'         => 'Unit Baru',
    ];
@endphp

@section('content')
<a href="{{ route('organisasi.struktur.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Riwayat Struktur Organisasi
</a>

<div class="page-header">
    <div class="page-title">Cari Unit Organisasi</div>
    <div class="page-sub">Telusuri unit lintas semua versi final — cocok untuk pertanyaan seperti "unit X dulu namanya apa sebelum direstrukturisasi?"</div>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('organisasi.struktur.search') }}" class="filter-row" x-data="{ pindahInduk: {{ in_array('pindah_induk', $jenisTransisiFilter, true) ? 'true' : 'false' }} }">
        <div class="filter-group grow">
            <label class="filter-label">Nama Unit</label>
            <input type="text" name="q" value="{{ $q }}" class="filter-input" placeholder="Ketik nama unit (nama lama maupun baru)...">
        </div>
        <div class="filter-group">
            <label class="filter-label">Level</label>
            <select name="level" class="filter-select">
                <option value="">Semua level</option>
                @foreach($levels as $lvl)
                    <option value="{{ $lvl }}" {{ $level === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                @endforeach
            </select>
        </div>
        @if($direktoratOptions->count())
        <div class="filter-group">
            <label class="filter-label">Direktorat (saat ini)</label>
            <select name="direktorat" class="filter-select">
                <option value="">Semua direktorat</option>
                @foreach($direktoratOptions as $d)
                    <option value="{{ $d->unit_organisasi_id }}" {{ (string) $direktoratId === (string) $d->unit_organisasi_id ? 'selected' : '' }}>{{ $d->nama_unit }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="btn-search">
            <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Cari
        </button>
        @if($q || $level || $direktoratId || count($jenisTransisiFilter))
        <a href="{{ route('organisasi.struktur.search') }}" class="btn-reset">Reset</a>
        @endif

        <div class="transisi-filter">
            <label class="filter-label">Filter Jenis Transisi</label>
            <div class="transisi-checkbox-row">
                @foreach($jenisTransisiOptions as $opt)
                <label class="transisi-checkbox">
                    <input type="checkbox" name="jenis_transisi[]" value="{{ $opt }}"
                        {{ in_array($opt, $jenisTransisiFilter, true) ? 'checked' : '' }}
                        @if($opt === 'pindah_induk') @change="pindahInduk = $event.target.checked" @endif>
                    {{ $jenisTransisiLabels[$opt] ?? ucfirst($opt) }}
                </label>
                @endforeach
            </div>
            <div class="transisi-subfilter" x-show="pindahInduk" x-cloak>
                <label class="transisi-checkbox">
                    <input type="checkbox" name="lintas_direktorat" value="1" {{ $lintasDirektoratOnly ? 'checked' : '' }}>
                    Lintas Direktorat Saja
                </label>
            </div>
        </div>
    </form>
</div>

<div class="result-list">
    @forelse($results as $r)
    <div class="result-card">
        <div class="result-main">
            <div class="result-nama">
                {{ formatUnitLabel($r['nama_saat_ini'], $r['level']) }}
                <span class="result-level">{{ $r['level'] }}</span>
                <span class="status-badge status-{{ $r['status']['jenis'] }}">{{ $r['status']['label'] }}</span>
            </div>
            @if(count($r['nama_sebelumnya']))
            <div class="result-sebelumnya">Sebelumnya: {{ implode(' → ', array_map(fn ($era) => formatUnitLabel($era['nama_unit'], $era['level']), $r['nama_sebelumnya'])) }}</div>
            @endif
            @if(count($r['status']['successors']))
            <div class="successor-list">
                {{ $r['status']['jenis'] === 'pecah' ? 'Menjadi: ' : 'Bergabung dengan unit lain menjadi: ' }}
                @foreach($r['status']['successors'] as $i => $s)
                    <a href="{{ route('organisasi.unit.timeline', $s['unit_organisasi_id']) }}">{{ formatUnitLabel($s['nama'], $s['level']) }}</a>{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </div>
            @endif
            @if(count($jenisTransisiFilter) && count($r['matched_events']))
            <div class="event-badge-list">
                @foreach($r['matched_events'] as $ev)
                <div class="event-badge">
                    <span class="event-badge-jenis">{{ $jenisTransisiLabels[$ev['jenis']] ?? ucfirst($ev['jenis']) }}{{ ($ev['jenis'] === 'pindah_induk' && $lintasDirektoratOnly) ? ' (Lintas Direktorat)' : '' }}</span>
                    {!! $ev['narasi']['html'] !!}
                    <span class="event-badge-tanggal">&middot; {{ $ev['tanggal'] ? $ev['tanggal']->translatedFormat('F Y') : '-' }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        <a href="{{ route('organisasi.unit.timeline', $r['unit_organisasi_id']) }}" class="btn-timeline">
            <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Lihat Timeline
        </a>
    </div>
    @empty
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        @if($q || $level || $direktoratId || count($jenisTransisiFilter))
            Tidak ada unit yang cocok dengan pencarian ini.
        @else
            Belum ada versi final — data unit akan muncul di sini setelah minimal 1 versi difinalisasi.
        @endif
    </div>
    @endforelse
</div>

@if($showPagination)
<div class="table-footer">
    <span>
        Menampilkan <strong>{{ $results->firstItem() ?? 0 }}</strong>–<strong>{{ $results->lastItem() ?? 0 }}</strong>
        dari <strong>{{ $results->total() }}</strong> unit
    </span>
    <div class="pagination-wrap">
        @if($results->onFirstPage())
            <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
        @else
            <a href="{{ $results->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
        @endif
        @php $cur=$results->currentPage();$last=$results->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
        @if($s > 1)
            <a href="{{ $results->url(1) }}" class="page-btn">1</a>
            @if($s > 2)
                <span class="page-btn disabled" style="border:none;background:transparent;width:auto;padding:0 2px;">…</span>
            @endif
        @endif
        @for($i = $s; $i <= $e; $i++)
            <a href="{{ $results->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
        @endfor
        @if($e < $last)
            @if($e < $last - 1)
                <span class="page-btn disabled" style="border:none;background:transparent;width:auto;padding:0 2px;">…</span>
            @endif
            <a href="{{ $results->url($last) }}" class="page-btn">{{ $last }}</a>
        @endif
        @if($results->hasMorePages())
            <a href="{{ $results->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
        @else
            <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
        @endif
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
