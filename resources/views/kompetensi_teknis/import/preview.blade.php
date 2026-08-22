@extends('layouts.app')
@section('title', 'Preview Import Kompetensi Teknis')
@section('breadcrumb-parent', 'Kompetensi Teknis')
@section('breadcrumb', 'Preview Import')

{{--
    Preview MENTAH hasil parsing (Step 1) — tabel HTML biasa, belum ada search/filter
    canggih (itu di halaman list utama, bukan di sini) & BELUM ada tombol lanjut ke
    mapping unit/commit (tahap terpisah berikutnya, di luar scope Step 1 ini). Data
    dibaca dari file temp JSON (storage/app/temp/kompetensi-teknis/{token}.json) yg
    ditulis KompetensiTeknisImportController::store() — token ada di URL supaya halaman
    ini reload-safe & bisa dibagikan/dibuka ulang selama file temp-nya belum dihapus.
--}}

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .next-step-banner { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }

    .summary-grid { display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:16px; }
    .summary-card { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:16px 18px; }
    .summary-label { font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px; }
    .summary-val { font-size:22px;font-weight:800;color:#111827; }
    .summary-val.native { color:#374151; }
    .summary-val.generic { color:#b45309; }

    .info-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:16px; }
    .info-card-title { font-size:13.5px;font-weight:700;color:#111827;margin-bottom:10px; }
    .chip-list { display:flex;flex-wrap:wrap;gap:6px; }
    .chip { display:inline-block;font-size:11.5px;padding:4px 10px;border-radius:20px;background:#f3f4f6;color:#374151; }
    .chip-generic { background:#fef3c7;color:#b45309; }
    .empty-note { font-size:12.5px;color:#9ca3af;font-style:italic; }

    .warning-card { background:white;border-radius:var(--radius);border:1px solid #fde68a;box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:16px; }
    .warning-card-title { font-size:13.5px;font-weight:700;color:#b45309;margin-bottom:10px;display:flex;align-items:center;gap:8px; }
    .warning-list { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px; }
    .warning-item { font-size:12.5px;color:#374151;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px; }
    .warning-item .baris-tag { font-weight:700;color:#b45309;margin-right:6px; }

    .komtek-table-wrap { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow-x:auto; }
    .komtek-table { width:100%;border-collapse:collapse;font-size:12.5px;min-width:1000px; }
    .komtek-table thead th { text-align:left;padding:10px 12px;background:#fafaf8;border-bottom:1px solid #f3f4f6;font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap; }
    .komtek-table tbody td { padding:8px 12px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle;white-space:nowrap; }
    .komtek-table tbody tr:last-child td { border-bottom:none; }
    .komtek-table tbody tr:hover { background:#fafaf8; }

    .managerial-badge { display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap; }
    .managerial-badge.ya { background:#dcfce7;color:#15803d; }
    .managerial-badge.tidak { background:#f3f4f6;color:#6b7280; }

    .asal-badge { display:inline-block;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;white-space:nowrap;text-transform:capitalize; }
    .asal-badge.native { background:#f3f4f6;color:#374151; }
    .asal-badge.generic { background:#fef3c7;color:#b45309; }

    .section-gap { margin-bottom:10px;font-size:13.5px;font-weight:700;color:#111827; }

    .rumpun-unresolved { color:#b45309;font-weight:600;font-style:italic;white-space:normal; }

    .error-banner { background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px; }
    .success-banner { background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px; }

    .btn-lanjut { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:9px 18px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;flex-shrink:0; }
    .btn-lanjut:hover { background:#166534; }
    .btn-lanjut svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }
    .next-step-row { display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap; }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.kompetensi-teknis.import.create') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Upload File Lain
</a>

<div class="page-header">
    <div class="page-title">Preview Import — {{ $payload['original_filename'] }}</div>
    <div class="page-sub">
        Job Family: <strong>{{ $payload['job_family_nama'] }}</strong>
        &middot; Versi acuan: <strong>{{ $versi ? 'SK ' . $versi->nomor_sk : '—' }}</strong>
        &middot; Diupload {{ \Illuminate\Support\Carbon::parse($payload['uploaded_at'])->translatedFormat('d F Y H:i') }}
    </div>
</div>

@if(session('error'))
<div class="error-banner">{{ session('error') }}</div>
@endif
@if(session('success'))
<div class="success-banner">{{ session('success') }}</div>
@endif

@php
    $primaryDone = array_key_exists('primary_row_ids', $payload);
@endphp
<div class="next-step-banner next-step-row">
    <div>
        ✅ Parsing selesai — data tidy masih tersimpan sementara, belum masuk database.
        @if($primaryDone)
            Mapping unit &amp; pilihan Primary sudah tersimpan — lanjut ke Review &amp; Commit.
        @elseif($unitMappingComplete)
            Mapping unit sudah tersimpan ({{ count($payload['unit_mapping']) }} kandidat) — lanjut pilih kompetensi Primary.
        @else
            Lanjutkan ke tahap mapping kandidat_nama_unit ke unit_organisasi.
        @endif
    </div>
    @if($primaryDone)
        <a href="{{ route('organisasi.kompetensi-teknis.import.review', ['token' => $token]) }}" class="btn-lanjut">
            Lanjut ke Review &amp; Commit
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    @elseif($unitMappingComplete)
        <a href="{{ route('organisasi.kompetensi-teknis.import.primary', ['token' => $token]) }}" class="btn-lanjut">
            Lanjut Pilih Primary
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    @else
        <a href="{{ route('organisasi.kompetensi-teknis.import.mapping', ['token' => $token]) }}" class="btn-lanjut">
            Lanjut ke Mapping Unit
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    @endif
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-label">Total Baris Tidy</div>
        <div class="summary-val">{{ count($tidyRows) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Native</div>
        <div class="summary-val native">{{ $tally['native'] ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Generic</div>
        <div class="summary-val generic">{{ $tally['generic'] ?? 0 }}</div>
    </div>
</div>

<div class="info-card">
    <div class="info-card-title">Kandidat Nama Unit Unik ({{ $units->count() }})</div>
    @if($units->isEmpty())
        <div class="empty-note">Tidak ada.</div>
    @else
        <div class="chip-list">
            @foreach($units as $u)
                <span class="chip">{{ $u }}</span>
            @endforeach
        </div>
    @endif
</div>

<div class="info-card">
    <div class="info-card-title">Kompetensi Generic Unik ({{ $generic->count() }}) — nama_kompetensi || Job Family</div>
    @if($generic->isEmpty())
        <div class="empty-note">Tidak ada.</div>
    @else
        <div class="chip-list">
            @foreach($generic as $g)
                <span class="chip chip-generic">{{ $g }}</span>
            @endforeach
        </div>
    @endif
</div>

<div class="warning-card">
    <div class="warning-card-title">
        <svg viewBox="0 0 24 24" width="15" height="15" stroke="#b45309" fill="none" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Warning ({{ count($warnings) }})
    </div>
    @if(empty($warnings))
        <div class="empty-note">Tidak ada warning.</div>
    @else
        <ul class="warning-list">
            @foreach($warnings as $w)
                <li class="warning-item">
                    @if($w['baris_asal_excel'] !== '')<span class="baris-tag">Baris {{ $w['baris_asal_excel'] }}</span>@endif
                    {{ $w['pesan'] }}
                </li>
            @endforeach
        </ul>
    @endif
</div>

<div class="section-gap">Data Tidy Mentah ({{ count($tidyRows) }} baris)</div>
<div class="komtek-table-wrap">
    <table class="komtek-table">
        <thead>
            <tr>
                <th>Baris Excel</th>
                <th>Kandidat Nama Unit</th>
                <th>Jenjang</th>
                <th>Urutan</th>
                <th>Grade</th>
                <th>Nama Jobs</th>
                <th>Managerial</th>
                <th>Kompetensi Teknis</th>
                <th>Job Family</th>
                <th>Level</th>
                <th>Asal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tidyRows as $row)
                <tr>
                    <td>{{ $row['baris_asal_excel'] }}</td>
                    <td>{{ $row['kandidat_nama_unit'] }}</td>
                    <td>{{ $row['jenjang_jabatan'] ?? '–' }}</td>
                    <td>{{ $row['urutan_jenjang'] ?? '–' }}</td>
                    <td>{{ $row['grade'] ?: '–' }}</td>
                    <td>{{ $row['nama_jobs'] }}</td>
                    <td>
                        @if($row['managerial'] === '')
                            <span class="managerial-badge tidak">?</span>
                        @else
                            <span class="managerial-badge {{ $row['managerial'] ? 'ya' : 'tidak' }}">{{ $row['managerial'] ? 'Managerial' : 'Non-Managerial' }}</span>
                        @endif
                    </td>
                    <td>{{ $row['nama_kompetensi'] }}</td>
                    <td>
                        @if($row['job_family_id'] !== null)
                            {{ $jobFamilyNames[$row['job_family_id']] ?? '–' }}
                        @else
                            <span class="rumpun-unresolved">Rumpun tidak dikenali — perlu review manual</span>
                        @endif
                    </td>
                    <td>Level {{ $row['level'] }}</td>
                    <td><span class="asal-badge {{ $row['asal'] }}">{{ ucfirst($row['asal']) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;color:#9ca3af;padding:30px 0;">Tidak ada baris tidy.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
