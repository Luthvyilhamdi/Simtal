@extends('layouts.app')
@section('title', 'Import Kompetensi Teknis')
@section('breadcrumb-parent', 'Kompetensi Teknis')
@section('breadcrumb', 'Import dari Excel')

{{--
    STEP 1 dari alur import self-service (upload -> parse -> preview mentah). Belum ada
    mapping unit/commit di sini — itu tahap terpisah berikutnya. Gaya form mengikuti
    konvensi organisasi/struktur/import.blade.php (form-card, form-grid, file-drop, dst)
    biar konsisten visual dgn form import lain di project ini.
--}}

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:20px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .mode-banner { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe; }

    .form-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:28px;margin-bottom:16px; }
    .section-header { margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid #f3f4f6; }
    .section-title { font-size:14px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px; }
    .req { color:#ef4444; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all .15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444;margin-top:2px; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }

    .file-drop { border:2px dashed #e5e7eb;border-radius:12px;padding:24px;text-align:center;background:#fafafa; }
    .file-drop input[type=file] { font-size:12.5px; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }

    .error-banner { background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px; }

    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.kompetensi-teknis.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Kompetensi Teknis
</a>

<div class="page-header">
    <div class="page-title">Import Kompetensi Teknis dari Excel</div>
    <div class="page-sub">Step 1 dari 3: Upload &amp; Parse. Setelah ini akan ada tahap mapping unit &amp; commit ke database (belum tersedia).</div>
</div>

<div class="mode-banner">
    📄 File harus format Excel asli (.xlsx/.xls) dari template "Profiling Kompetensi Teknis" — border cell dipakai untuk menentukan tipe Primary/Secondary, jadi CSV tidak bisa dipakai.
</div>

@if(session('error'))
<div class="error-banner">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('organisasi.kompetensi-teknis.import.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-card">
        <div class="section-header">
            <div class="section-title">Konteks Profiling</div>
            <div class="section-sub">Job Family yang sedang diprofilkan &amp; versi struktur acuan</div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Job Family <span class="req">*</span></label>
                <select name="job_family_id" class="form-input @error('job_family_id') error-input @enderror" required>
                    <option value="">— Pilih Job Family —</option>
                    @foreach($jobFamilyOptions as $jf)
                        <option value="{{ $jf->id }}" {{ old('job_family_id') == $jf->id ? 'selected' : '' }}>{{ $jf->nama }}</option>
                    @endforeach
                </select>
                @error('job_family_id')<div class="error-msg">{{ $message }}</div>@enderror
                <div class="form-hint">
                    Dipakai sebagai rumpun untuk kompetensi native (bukan pinjaman rumpun lain). Wajib pilih dari master —
                    kalau rumpun yang dicari belum ada, <a href="{{ route('organisasi.job-family.index') }}" target="_blank">tambahkan ke master Job Family</a> dulu.
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Versi Struktur Organisasi <span class="req">*</span></label>
                <select name="struktur_organisasi_versi_id" class="form-input @error('struktur_organisasi_versi_id') error-input @enderror" required>
                    <option value="">— Pilih versi —</option>
                    @foreach($versiList as $v)
                        <option value="{{ $v->id }}" {{ old('struktur_organisasi_versi_id') == $v->id ? 'selected' : '' }}>
                            SK {{ $v->nomor_sk }} &middot; {{ $v->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
                        </option>
                    @endforeach
                </select>
                @error('struktur_organisasi_versi_id')<div class="error-msg">{{ $message }}</div>@enderror
                <div class="form-hint">Belum dipakai untuk parsing di step ini — disimpan untuk tahap mapping unit berikutnya.</div>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="section-header">
            <div class="section-title">File Excel</div>
            <div class="section-sub">Format "Profiling Kompetensi Teknis" mentah — header di baris 1-2, data mulai baris 4</div>
        </div>
        <div class="file-drop">
            <input type="file" name="file" accept=".xlsx,.xls" required>
            <div class="form-hint" style="margin-top:10px;">
                Kolom B = List Jabatan, C = Grade, D = Jobs, E = Departemen, F = Managerial/Non Managerial,
                G dst = 1 kolom per kompetensi teknis sampai kolom "Total Kompetensi Teknis".
            </div>
        </div>
        @error('file')<div class="error-msg" style="margin-top:8px;">{{ $message }}</div>@enderror
    </div>

    <div class="form-actions-card">
        <a href="{{ route('organisasi.kompetensi-teknis.index') }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload &amp; Parse
        </button>
    </div>
</form>
@endsection
