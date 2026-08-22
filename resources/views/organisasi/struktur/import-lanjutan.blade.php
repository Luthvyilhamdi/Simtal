@extends('layouts.app')
@section('title', 'Import Versi Lanjutan dari Excel')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Import Versi Lanjutan')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:20px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .mode-banner { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }

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
    .template-cols { font-size:11.5px;color:#6b7280;margin-top:8px;font-family:monospace;background:white;border:1px solid #e5e7eb;border-radius:7px;padding:8px 12px;display:inline-block; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }

    .error-card { background:white;border-radius:var(--radius);border:1px solid #fecaca;box-shadow:var(--card-shadow);padding:22px 26px;margin-bottom:16px; }
    .error-title { font-size:14px;font-weight:700;color:#dc2626;margin-bottom:4px;display:flex;align-items:center;gap:8px; }
    .error-sub { font-size:12px;color:#9ca3af;margin-bottom:14px; }
    .error-table-wrap { overflow-x:auto;border:1px solid #fecaca;border-radius:9px; }
    table.error-table { width:100%;border-collapse:collapse;font-size:12.5px; }
    table.error-table th { padding:8px 12px;text-align:left;font-size:10.5px;font-weight:700;color:#dc2626;text-transform:uppercase;background:#fef2f2;border-bottom:1px solid #fecaca;white-space:nowrap; }
    table.error-table td { padding:8px 12px;border-bottom:1px solid #fef2f2;color:#374151; }
    table.error-table tr:last-child td { border-bottom:none; }
    .col-baris { width:70px;font-weight:700;color:#dc2626; }

    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Riwayat Struktur Organisasi
</a>

<div class="page-header">
    <div class="page-title">Import Versi Lanjutan dari Excel</div>
    <div class="page-sub">Untuk versi lanjutan (bukan baseline) — file berisi jenis_transisi per unit relatif terhadap Versi Dasar yang dipilih. Sistem akan memvalidasi, meresolusi transisi, mendeteksi kandidat bubar, lalu menampilkan preview sebelum disimpan.</div>
</div>

<div class="mode-banner">
    📥 Tiap baris di file mewakili 1 unit di roster versi <strong>baru</strong>. Isi kolom jenis_transisi &amp; unit_versi_sebelumnya hanya untuk unit yang berubah (rename/pindah_induk/ganti_level/pecah/gabung/baru) — kosongkan untuk unit yang lanjut apa adanya, sistem akan mencocokkannya otomatis dengan Versi Dasar.
</div>

@if(count($rowErrors))
<div class="error-card">
    <div class="error-title">
        <svg viewBox="0 0 24 24" width="16" height="16" stroke="#dc2626" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ count($rowErrors) }} baris bermasalah — belum ada yang disimpan
    </div>
    <div class="error-sub">Perbaiki file Excel sesuai daftar di bawah, lalu upload ulang. Tidak ada data yang tersimpan sampai semua baris valid.</div>
    <div class="error-table-wrap">
        <table class="error-table">
            <thead><tr><th class="col-baris">Baris</th><th>Alasan</th></tr></thead>
            <tbody>
                @foreach($rowErrors as $e)
                <tr><td class="col-baris">{{ $e['baris'] }}</td><td>{{ $e['alasan'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<form method="POST" action="{{ route('organisasi.struktur.import-lanjutan.upload') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-card">
        <div class="section-header">
            <div class="section-title">Data SK</div>
            <div class="section-sub">Nomor SK, tanggal, dan keterangan versi ini — bukan dari kolom Excel, diisi manual di sini</div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nomor SK <span class="req">*</span></label>
                <input type="text" name="nomor_sk" value="{{ old('nomor_sk', $old['nomor_sk'] ?? '') }}" class="form-input @error('nomor_sk') error-input @enderror" required maxlength="100">
                @error('nomor_sk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal SK <span class="req">*</span></label>
                <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk', $old['tanggal_sk'] ?? now()->format('Y-m-d')) }}" class="form-input @error('tanggal_sk') error-input @enderror" required>
                @error('tanggal_sk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Mulai Berlaku <span class="req">*</span></label>
                <input type="date" name="tanggal_mulai_berlaku" value="{{ old('tanggal_mulai_berlaku', $old['tanggal_mulai_berlaku'] ?? '') }}" class="form-input @error('tanggal_mulai_berlaku') error-input @enderror" required>
                @error('tanggal_mulai_berlaku')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Versi Dasar <span class="req">*</span></label>
                <select name="versi_dasar_id" class="form-input @error('versi_dasar_id') error-input @enderror" required>
                    <option value="">— Pilih versi final —</option>
                    @foreach($versiFinalOptions as $v)
                        <option value="{{ $v->id }}" {{ (string) old('versi_dasar_id', $old['versi_dasar_id'] ?? '') === (string) $v->id ? 'selected' : '' }}>
                            {{ $v->nomor_sk }} · {{ $v->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
                        </option>
                    @endforeach
                </select>
                @error('versi_dasar_id')<div class="error-msg">{{ $message }}</div>@enderror
                <div class="form-hint">Roster unit yang jadi acuan resolusi jenis_transisi &amp; deteksi bubar. Biasanya versi final terbaru, tapi boleh pilih versi final lain (konsisten dengan prinsip "boleh lompat versi" di fitur Bandingkan).</div>
            </div>
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-input" rows="2">{{ old('keterangan', $old['keterangan'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="section-header">
            <div class="section-title">File Excel</div>
            <div class="section-sub">Kolom wajib: kode_sementara, nama_unit, level, parent_kode_sementara, formasi, jenis_transisi, unit_versi_sebelumnya, keterangan_transisi. Kolom opsional: unit_versi_sebelumnya_level.</div>
        </div>
        <div class="file-drop">
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
            <div class="template-cols">kode_sementara | nama_unit | level | parent_kode_sementara | formasi | jenis_transisi | unit_versi_sebelumnya | unit_versi_sebelumnya_level | keterangan_transisi</div>
            <div class="form-hint" style="margin-top:10px;text-align:left;">
                Level harus salah satu dari: {{ implode(', ', \App\Http\Controllers\StrukturOrganisasiVersiController::LEVELS) }}.<br>
                parent_kode_sementara kosong = unit paling atas (root), merujuk kode_sementara lain DI FILE INI (bukan di versi dasar).<br>
                jenis_transisi kosong = lanjut (dicocokkan otomatis by nama unit + level persis terhadap Versi Dasar). Diisi kalau berubah: rename, pindah_induk, ganti_level, pecah, gabung, atau baru.<br>
                ganti_level = nama_unit &amp; parent PERSIS SAMA dengan Versi Dasar, cuma level yang berubah (identitas unit tetap sama, bukan unit baru).<br>
                unit_versi_sebelumnya = nama unit di Versi Dasar yang jadi rujukan (untuk gabung, pisahkan beberapa nama dengan koma). Wajib diisi untuk rename/pindah_induk/pecah/gabung/ganti_level, wajib kosong untuk baru.<br>
                unit_versi_sebelumnya_level (opsional) = isi kalau nama di unit_versi_sebelumnya tidak unik di Versi Dasar (ada di beberapa level sekaligus, mis. "Bengkel Listrik" ada di level seksi & foreman) — sebutkan level unit LAMA yang dimaksud secara eksplisit di sini, terutama wajib untuk jenis_transisi=ganti_level karena level barisnya sendiri sudah pasti beda dari level unit lama.<br>
                Kolom referensi lain di file (grand_total_dari_bagan, status, keterangan) opsional — boleh ada tapi diabaikan importer. File dengan lebih dari 1 sheet juga didukung.
            </div>
        </div>
        @error('file')<div class="error-msg" style="margin-top:8px;">{{ $message }}</div>@enderror
    </div>

    <div class="form-actions-card">
        <a href="{{ route('organisasi.struktur.index') }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Validasi &amp; Lanjut ke Preview
        </button>
    </div>
</form>
@endsection
