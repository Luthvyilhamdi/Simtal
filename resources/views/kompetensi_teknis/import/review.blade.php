@extends('layouts.app')
@section('title', 'Review & Commit — Import Kompetensi Teknis')
@section('breadcrumb-parent', 'Kompetensi Teknis')
@section('breadcrumb', 'Review & Commit')

{{--
    STEP 3 (terakhir) alur import self-service — laporan di halaman ini HASIL DRY-RUN
    SUNGGUHAN (App\Services\KompetensiTeknisImporter dgn $commit=false, transaksi
    dijalankan penuh lalu di-rollback), BUKAN simulasi/hitungan terpisah, jadi 100% akurat
    thd kondisi database saat ini (termasuk deteksi duplikat by unique constraint). Tombol
    commit HANYA muncul kalau $result 0 error & tidak stoppedEarly — WAJIB bersih dulu
    sebelum boleh commit sungguhan.

    Konfirmasi sebelum submit form commit SENGAJA vanilla JS confirm() sederhana (bukan
    modal custom) — cukup utk aksi 1x klik yg permanen, konsisten dgn keputusan "vanilla
    JS saja, jangan Alpine" di seluruh alur import ini.
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

    .blocker-card { background:#fef2f2;border:2px solid #fecaca;border-radius:var(--radius);padding:22px 26px;margin-bottom:16px; }
    .blocker-title { font-size:15px;font-weight:800;color:#dc2626;margin-bottom:6px;display:flex;align-items:center;gap:8px; }
    .blocker-sub { font-size:12.5px;color:#7f1d1d;margin-bottom:14px; }
    .blocker-list { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px; }
    .blocker-item { font-size:12.5px;color:#7f1d1d;background:white;border:1px solid #fecaca;border-radius:8px;padding:8px 12px; }

    .summary-grid { display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:16px; }
    .summary-card { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:16px 18px; }
    .summary-label { font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .summary-val { font-size:22px;font-weight:800;color:#111827; }
    .summary-val.insert { color:#15803d; }
    .summary-val.duplikat { color:#7c3aed; }
    .summary-val.error { color:#dc2626; }
    .summary-val.native { color:#374151; }
    .summary-val.generic { color:#b45309; }
    .summary-val.primary { color:#15803d; }
    .summary-val.secondary { color:#1d4ed8; }

    .info-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:16px; }
    .info-card-title { font-size:13.5px;font-weight:700;color:#111827;margin-bottom:10px; }
    .chip-list { display:flex;flex-wrap:wrap;gap:6px; }
    .chip { display:inline-block;font-size:11.5px;padding:4px 10px;border-radius:20px;background:#f3f4f6;color:#374151; }
    .chip-generic { background:#fef3c7;color:#b45309; }
    .chip-konflik { background:#fef2f2;color:#dc2626; }
    .empty-note { font-size:12.5px;color:#9ca3af;font-style:italic; }

    .unit-breakdown-table { width:100%;border-collapse:collapse;font-size:12.5px; }
    .unit-breakdown-table th { text-align:left;padding:8px 10px;font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;border-bottom:1px solid #f3f4f6; }
    .unit-breakdown-table td { padding:8px 10px;border-bottom:1px solid #f3f4f6;color:#374151; }
    .unit-breakdown-table tr:last-child td { border-bottom:none; }

    .commit-card { background:white;border-radius:var(--radius);border:2px solid #bbf7d0;box-shadow:var(--card-shadow);padding:24px 28px;text-align:center; }
    .commit-card-title { font-size:15px;font-weight:800;color:#111827;margin-bottom:6px; }
    .commit-card-sub { font-size:12.5px;color:#6b7280;margin-bottom:18px; }
    .btn-commit { display:inline-flex;align-items:center;gap:10px;background:#15803d;color:white;padding:14px 32px;border-radius:12px;font-size:14.5px;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-commit:hover { background:#166534; }
    .btn-commit svg { width:17px;height:17px;stroke:white;fill:none;stroke-width:2; }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.kompetensi-teknis.import.primary', ['token' => $token]) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Pilih Primary
</a>

<div class="page-header">
    <div class="page-title">Review &amp; Commit</div>
    <div class="page-sub">
        Step 4 dari 4 (terakhir) &middot; File: <strong>{{ $payload['original_filename'] }}</strong>
        &middot; Job Family: <strong>{{ $payload['job_family_nama'] }}</strong>
        &middot; Versi acuan: <strong>SK {{ $versi->nomor_sk }}</strong>
    </div>
</div>

@if(session('error'))
<div class="error-banner">{{ session('error') }}</div>
@endif

@php
    $adaError = $result['stoppedEarly'] || count($result['errors']) > 0;
@endphp

@if($adaError)
<div class="blocker-card">
    <div class="blocker-title">
        <svg viewBox="0 0 24 24" width="18" height="18" stroke="#dc2626" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Ditemukan {{ count($result['errors']) }} error — commit TIDAK BISA dilakukan
    </div>
    <div class="blocker-sub">Perbaiki dulu (biasanya kembali ke mapping unit atau upload ulang), baru bisa lanjut commit. Tidak ada data yang tersimpan.</div>
    <ul class="blocker-list">
        @foreach($result['errors'] as $e)
            <li class="blocker-item">{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-label">Total Baris Diproses</div>
        <div class="summary-val">{{ $result['totalDiproses'] }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Akan Di-insert</div>
        <div class="summary-val insert">{{ $result['totalInsert'] }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Duplikat (di-skip)</div>
        <div class="summary-val duplikat">{{ $result['totalDuplikat'] }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Error</div>
        <div class="summary-val error">{{ count($result['errors']) }}</div>
    </div>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-label">Native</div>
        <div class="summary-val native">{{ $result['tallyAsal']['native'] ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Generic</div>
        <div class="summary-val generic">{{ $result['tallyAsal']['generic'] ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Primary</div>
        <div class="summary-val primary">{{ $result['tallyPrioritas']['primary'] ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Secondary</div>
        <div class="summary-val secondary">{{ $result['tallyPrioritas']['secondary'] ?? 0 }}</div>
    </div>
</div>

<div class="info-card">
    <div class="info-card-title">Breakdown per Unit ({{ $perUnitNamed->count() }} unit)</div>
    @if($perUnitNamed->isEmpty())
        <div class="empty-note">Tidak ada.</div>
    @else
        <table class="unit-breakdown-table">
            <thead><tr><th>Unit</th><th>Jumlah Baris</th></tr></thead>
            <tbody>
                @foreach($perUnitNamed as $u)
                    <tr><td>{{ $u['nama_unit'] }}</td><td>{{ $u['jumlah'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="info-card">
    <div class="info-card-title">Kompetensi Teknis BARU yang akan dibuat ({{ count($result['kompetensiBaru']) }})</div>
    @if(empty($result['kompetensiBaru']))
        <div class="empty-note">Tidak ada — semua kompetensi sudah ada di master.</div>
    @else
        <div class="chip-list">
            @foreach($result['kompetensiBaru'] as $k)
                <span class="chip chip-generic">{{ $k }}</span>
            @endforeach
        </div>
    @endif
</div>

<div class="info-card">
    <div class="info-card-title">Warning Konflik Job Family ({{ count($result['konflikRumpun']) }})</div>
    @if(empty($result['konflikRumpun']))
        <div class="empty-note">Tidak ada konflik.</div>
    @else
        <div class="chip-list">
            @foreach($result['konflikRumpun'] as $k)
                <span class="chip chip-konflik">{{ $k }}</span>
            @endforeach
        </div>
        <div class="empty-note" style="margin-top:8px;">job_family yang sudah ada di database TIDAK akan di-overwrite otomatis — putuskan manual kalau perlu diubah.</div>
    @endif
</div>

@if(!$adaError)
<div class="commit-card">
    <div class="commit-card-title">Siap disimpan ke database</div>
    <div class="commit-card-sub">{{ $result['totalInsert'] }} baris kompetensi teknis akan tersimpan permanen. Aksi ini tidak bisa dibatalkan dari halaman ini.</div>
    <form method="POST" action="{{ route('organisasi.kompetensi-teknis.import.review.commit', ['token' => $token]) }}"
          onsubmit="return confirm('Yakin? Data akan tersimpan permanen ke database.');">
        @csrf
        <button type="submit" class="btn-commit">
            <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Simpan Semua ke Database
        </button>
    </form>
</div>
@endif
@endsection
