@extends('layouts.app')
@section('title', 'Mapping Unit — Import Kompetensi Teknis')
@section('breadcrumb-parent', 'Kompetensi Teknis')
@section('breadcrumb', 'Mapping Unit')

{{--
    STEP 2 dari alur import self-service — mapping kandidat_nama_unit (hasil parsing) ke
    unit_organisasi_id sungguhan (snapshot versi yg dipilih di Step 1). Exact match
    (case-insensitive) SUDAH di-pre-select via `selected` di Blade (server-side, lihat
    $autoMatch dari controller) — badge "Auto-matched" hijau jadi penanda visual mana yg
    perlu diperiksa lebih santai vs mana yg WAJIB diperiksa teliti krn tidak ke-auto-match.
    TIDAK ada fuzzy-match/tebakan otomatis sama sekali (beda dgn analisis manual
    sebelumnya) — kandidat tanpa exact match dibiarkan kosong, admin yg putuskan.

    Quick-filter di atas daftar SENGAJA vanilla JS murni (bukan Alpine, konsisten dgn
    keputusan final project ini) — cuma filter tampilan (style.display), tidak menyentuh
    isi <select> sama sekali.
--}}

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .error-banner { background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px;white-space:pre-line; }

    .progress-note { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }

    .filter-box { display:flex;align-items:center;gap:8px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 14px;margin-bottom:14px; }
    .filter-box svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .filter-box input { border:none;outline:none;font-size:13px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .filter-box input::placeholder { color:#9ca3af; }

    .mapping-list { display:flex;flex-direction:column;gap:10px;margin-bottom:20px; }
    .mapping-row { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px 18px;display:flex;align-items:center;gap:14px;flex-wrap:wrap; }
    .mapping-kandidat { flex:1;min-width:220px; }
    .mapping-kandidat-nama { font-weight:700;color:#111827;font-size:13.5px; }
    .mapping-kandidat-meta { display:flex;align-items:center;gap:6px;margin-top:4px;flex-wrap:wrap; }
    .baris-count-badge { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;background:#f5f3ff;color:#7c3aed; }
    .auto-match-badge { display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;background:#dcfce7;color:#15803d; }
    .auto-match-badge svg { width:10px;height:10px;stroke:currentColor;fill:none;stroke-width:3; }
    .no-match-badge { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;background:#fef3c7;color:#b45309; }

    .mapping-select { min-width:340px;flex:1;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:12.5px;font-family:inherit;color:#111827;background:#fafafa; }
    .mapping-select:focus { border-color:#16a34a;background:white;outline:none;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .mapping-select.matched { border-color:#bbf7d0;background:#f0fdf4; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;position:sticky;bottom:16px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.kompetensi-teknis.import.preview', ['token' => $token]) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Preview
</a>

<div class="page-header">
    <div class="page-title">Mapping Unit</div>
    <div class="page-sub">
        Step 2 dari 3 &middot; Job Family: <strong>{{ $payload['job_family_nama'] }}</strong>
        &middot; Versi acuan: <strong>SK {{ $versi->nomor_sk }}</strong>
        &middot; {{ $kandidatCounts->count() }} kandidat unit perlu dipetakan
    </div>
</div>

<div class="progress-note">
    🔗 Cocokkan tiap kandidat nama unit (hasil ekstraksi Excel) ke unit organisasi sungguhan di versi ini. Badge hijau "Auto-matched" = sudah ketemu exact match otomatis (tetap boleh diganti manual); tanpa badge = wajib dipilih manual.
</div>

@if(session('error'))
<div class="error-banner">{{ session('error') }}</div>
@endif

<div class="filter-box">
    <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="mapping-quick-filter" oninput="filterMappingRows()" placeholder="Filter kandidat unit...">
</div>

<form method="POST" action="{{ route('organisasi.kompetensi-teknis.import.mapping.store', ['token' => $token]) }}">
    @csrf

    <div class="mapping-list" id="mapping-list">
        @foreach($kandidatCounts as $kandidat => $jumlahBaris)
            @php
                $matchedId   = $autoMatch[$kandidat] ?? null;
                $selectedVal = old("unit_mapping.$kandidat", $matchedId ?? '');
            @endphp
            <div class="mapping-row" data-kandidat="{{ mb_strtolower($kandidat) }}">
                <div class="mapping-kandidat">
                    <div class="mapping-kandidat-nama">{{ $kandidat }}</div>
                    <div class="mapping-kandidat-meta">
                        <span class="baris-count-badge">{{ $jumlahBaris }} baris kompetensi</span>
                        @if($matchedId)
                            <span class="auto-match-badge">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Auto-matched
                            </span>
                        @else
                            <span class="no-match-badge">Perlu dipilih manual</span>
                        @endif
                    </div>
                </div>
                <select name="unit_mapping[{{ $kandidat }}]" class="mapping-select {{ $matchedId ? 'matched' : '' }}" required
                        onchange="this.classList.toggle('matched', this.value !== '')">
                    <option value="">— Pilih unit —</option>
                    @foreach($groupedOptions as $groupLabel => $opts)
                        <optgroup label="{{ $groupLabel }}">
                            @foreach($opts as $opt)
                                <option value="{{ $opt['unit_organisasi_id'] }}" {{ (string) $selectedVal === (string) $opt['unit_organisasi_id'] ? 'selected' : '' }}>
                                    {{ $opt['label'] }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>

    <div class="form-actions-card">
        <a href="{{ route('organisasi.kompetensi-teknis.import.preview', ['token' => $token]) }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Mapping
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // VANILLA JS MURNI, tanpa Alpine — quick-filter tampilan saja, tidak menyentuh <select>.
    function filterMappingRows() {
        const q = document.getElementById('mapping-quick-filter').value.toLowerCase().trim();
        document.querySelectorAll('.mapping-row').forEach(row => {
            row.style.display = (q === '' || row.dataset.kandidat.includes(q)) ? '' : 'none';
        });
    }
</script>
@endpush
