@extends('layouts.app')
@section('title', 'Preview Import Baseline')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Preview Import')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .mode-banner { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }

    .info-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 26px;margin-bottom:16px; }
    .info-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px; }
    .info-item { display:flex;flex-direction:column;gap:3px; }
    .info-label { font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px; }
    .info-val { font-size:14px;font-weight:700;color:#111827; }

    .ringkasan-grid { display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap; }
    .ringkasan-item { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:12px 18px;text-align:center;min-width:110px; }
    .ringkasan-num { font-size:20px;font-weight:800;color:#111827; }
    .ringkasan-label { font-size:10.5px;color:#6b7280;margin-top:2px;text-transform:capitalize; }

    .table-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden;margin-bottom:16px; }
    .table-wrap { overflow-x:auto; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:640px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:9px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }
    .level-badge { display:inline-block;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;background:#f3f4f6;color:#374151;text-transform:capitalize; }
    .nama-indent { color:#d1d5db;font-family:monospace; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-confirm { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-confirm:hover { background:#166534; }
    .btn-confirm svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-confirm svg { stroke:white; }

    @media (max-width:900px) {
        .info-grid { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.import') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Upload
</a>

<div class="page-header">
    <div class="page-title">Preview Import Baseline</div>
    <div class="page-sub">Periksa hierarki &amp; jumlah unit di bawah ini sebelum konfirmasi. Belum ada yang tersimpan ke database.</div>
</div>

<div class="mode-banner">
    ✓ File tervalidasi — {{ $totalUnit }} unit siap disimpan sebagai versi <strong>draft</strong>. Anda masih bisa mengedit roster setelah tersimpan, sebelum finalisasi.
</div>

<div class="info-card">
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Nomor SK</div>
            <div class="info-val">{{ $header['nomor_sk'] }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal SK</div>
            <div class="info-val">{{ \Carbon\Carbon::parse($header['tanggal_sk'])->translatedFormat('d F Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Mulai Berlaku</div>
            <div class="info-val">{{ \Carbon\Carbon::parse($header['tanggal_mulai_berlaku'])->translatedFormat('d F Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Unit</div>
            <div class="info-val">{{ $totalUnit }}</div>
        </div>
    </div>
    @if(!empty($header['keterangan']))
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f3f4f6;">
        <div class="info-label" style="margin-bottom:4px;">Keterangan</div>
        <div style="font-size:13px;color:#374151;">{{ $header['keterangan'] }}</div>
    </div>
    @endif
</div>

<div class="ringkasan-grid">
    @foreach($ringkasanLevel as $lvl => $count)
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ $count }}</div>
        <div class="ringkasan-label">{{ ucfirst($lvl) }}</div>
    </div>
    @endforeach
</div>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama Unit</th>
                    <th>Level</th>
                    <th>Parent</th>
                    <th>Formasi Unit</th>
                    <th>Total Bawahan</th>
                    <th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($previewRows as $row)
                <tr>
                    <td style="font-weight:600;color:#111827;">
                        @if($row['depth'] > 0)<span class="nama-indent">{{ str_repeat('—', $row['depth']) }} </span>@endif
                        {{ $row['nama_unit'] }}
                    </td>
                    <td><span class="level-badge">{{ ucfirst($row['level']) }}</span></td>
                    <td>{{ $row['parent_nama'] }}</td>
                    <td>{{ $row['mc_formasi'] }}</td>
                    <td>{{ is_null($row['total_bawahan']) ? '–' : $row['total_bawahan'] }}</td>
                    <td style="font-weight:700;color:#111827;">{{ $row['grand_total'] }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af;">Tidak ada baris.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<form method="POST" action="{{ route('organisasi.struktur.import.confirm') }}">
    @csrf
    <div class="form-actions-card">
        <a href="{{ route('organisasi.struktur.import') }}" class="btn-cancel">Batal &amp; Upload Ulang</a>
        <button type="submit" class="btn-confirm">
            <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            Konfirmasi &amp; Simpan sebagai Draft
        </button>
    </div>
</form>
@endsection
