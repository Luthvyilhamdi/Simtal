@extends('layouts.app')
@section('title', 'Job Family')
@section('breadcrumb-parent', 'Organisasi')
@section('breadcrumb', 'Job Family')

{{--
    Halaman kecil kelola master Job Family — dipakai dropdown WAJIB pilih di form Import
    Kompetensi Teknis. Minimal (list + tambah) sesuai kebutuhan saat ini, form tambah
    INLINE di halaman ini (bukan halaman create terpisah). Edit/delete belum ada (lihat
    catatan di JobFamilyController).
--}}

@push('styles')
<style>
    .page-header { margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .success-banner { background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px; }
    .error-banner { background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px; }

    .form-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 24px;margin-bottom:16px; }
    .form-card-title { font-size:13.5px;font-weight:700;color:#111827;margin-bottom:12px; }
    .inline-form { display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap; }
    .inline-form .form-input { flex:1;min-width:260px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none; }
    .inline-form .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .inline-form .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444;margin-top:4px;width:100%; }
    .btn-add { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;white-space:nowrap; }
    .btn-add:hover { background:#166534; }
    .btn-add svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }

    .komtek-table-wrap { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow-x:auto; }
    .komtek-table { width:100%;border-collapse:collapse;font-size:12.5px; }
    .komtek-table thead th { text-align:left;padding:11px 14px;background:#fafaf8;border-bottom:1px solid #f3f4f6;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap; }
    .komtek-table tbody td { padding:10px 14px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    .komtek-table tbody tr:last-child td { border-bottom:none; }
    .komtek-table tbody tr:hover { background:#fafaf8; }
    .col-no { width:40px;color:#9ca3af; }
    .nama-jf { font-weight:700;color:#111827; }
    .jumlah-badge { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;background:#f5f3ff;color:#7c3aed; }
    .jumlah-badge.kosong { background:#f3f4f6;color:#9ca3af; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">Job Family</div>
    <div class="page-sub">{{ $jobFamilies->count() }} Job Family terdaftar — dipakai sbg master saat import Kompetensi Teknis</div>
</div>

@if(session('success'))
<div class="success-banner">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="error-banner">{{ session('error') }}</div>
@endif

<div class="form-card">
    <div class="form-card-title">Tambah Job Family Baru</div>
    <form method="POST" action="{{ route('organisasi.job-family.store') }}" class="inline-form">
        @csrf
        <div style="flex:1;min-width:260px;">
            <input type="text" name="nama" value="{{ old('nama') }}" placeholder="mis. Digital Transformation"
                   class="form-input @error('nama') error-input @enderror" required maxlength="255">
            @error('nama')<div class="error-msg">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn-add">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah
        </button>
    </form>
</div>

<div class="komtek-table-wrap">
    <table class="komtek-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th>Nama Job Family</th>
                <th>Jumlah Kompetensi Teknis</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobFamilies as $i => $jf)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="nama-jf">{{ $jf->nama }}</td>
                    <td>
                        <span class="jumlah-badge {{ $jf->kompetensi_teknis_count === 0 ? 'kosong' : '' }}">
                            {{ $jf->kompetensi_teknis_count }} kompetensi
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:30px 0;">Belum ada Job Family.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
