@extends('layouts.app')
@section('title', 'Edit Karyawan')
@section('breadcrumb-parent', 'Profil Karyawan')
@section('breadcrumb', 'Edit: ' . $karyawan->nama)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .form-card { background:white;border-radius:16px;border:1px solid var(--card-border);padding:26px 28px;margin-bottom:16px;box-shadow:var(--card-shadow); }

    .section-header { display:flex;align-items:center;gap:12px;margin-bottom:22px;padding-bottom:14px;border-bottom:1px solid #f3f4f6; }
    .section-icon { width:36px;height:36px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .section-icon svg { width:17px;height:17px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .section-icon.blue { background:#eff6ff; }
    .section-icon.blue svg { stroke:#2563eb; }
    .section-icon.purple { background:#f5f3ff; }
    .section-icon.purple svg { stroke:#7c3aed; }
    .section-icon.amber { background:#fffbeb; }
    .section-icon.amber svg { stroke:#d97706; }
    .section-title { font-size:14.5px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:18px; }
    .form-group { display:flex;flex-direction:column;gap:7px; }
    .form-group.full { grid-column:1/-1; }

    .form-label { font-size:12px;font-weight:600;color:#475467;display:flex;align-items:center;gap:4px; }
    .req { color:#ef4444; }

    .form-input { padding:11px 14px;border:1px solid #e4e7ec;border-radius:10px;font-size:13.5px;font-family:inherit;color:#111827;background:#fcfcfd;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.10); }
    .form-input[readonly] { background:#f3f4f6;color:#15803d;font-weight:700;cursor:not-allowed; }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11.5px;color:#ef4444; }
    .form-hint { font-size:11.5px;color:#9ca3af; }

    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #98a2b3;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    .radio-group { display:flex;gap:10px; }
    .radio-card { flex:1;display:flex;align-items:center;gap:10px;padding:11px 14px;border:1px solid #e4e7ec;border-radius:10px;cursor:pointer;transition:all 0.15s;background:#fcfcfd; }
    .radio-card input[type=radio] { display:none; }
    .radio-card:hover { border-color:#86efac;background:#f0fdf4; }
    .radio-card.selected { border-color:#16a34a;background:#f0fdf4; }
    .radio-dot { width:18px;height:18px;border-radius:50%;border:2px solid #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.15s; }
    .radio-card.selected .radio-dot { border-color:#16a34a;background:#16a34a; }
    .radio-card.selected .radio-dot::after { content:'';width:6px;height:6px;border-radius:50%;background:white; }
    .radio-label { font-size:13px;font-weight:600;color:#475467; }
    .radio-card.selected .radio-label { color:#15803d; }

    .status-group { display:flex;gap:10px; }
    .status-card { flex:1;display:flex;align-items:center;gap:10px;padding:11px 14px;border:1px solid #e4e7ec;border-radius:10px;cursor:pointer;transition:all 0.15s;background:#fcfcfd; }
    .status-card input[type=radio] { display:none; }
    .status-dot { width:10px;height:10px;border-radius:50%;background:#d1d5db;flex-shrink:0;transition:background 0.15s; }
    .status-card.aktif-card:hover,.status-card.aktif-card.selected { border-color:#16a34a;background:#f0fdf4; }
    .status-card.nonaktif-card:hover,.status-card.nonaktif-card.selected { border-color:#ef4444;background:#fef2f2; }
    .status-card.aktif-card.selected .status-dot { background:#16a34a; }
    .status-card.nonaktif-card.selected .status-dot { background:#ef4444; }
    .status-label { font-size:13px;font-weight:600;color:#6b7280; }
    .status-card.aktif-card.selected .status-label { color:#15803d; }
    .status-card.nonaktif-card.selected .status-label { color:#ef4444; }

    .foto-upload-wrap { display:flex;align-items:center;gap:16px;flex-wrap:wrap; }
    .foto-preview-box { width:80px;height:80px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;overflow:hidden;border:3px solid #bbf7d0; }
    .foto-preview-box img { width:100%;height:100%;object-fit:cover; }
    .foto-upload-area { flex:1;min-width:180px;border:1.5px dashed #d0d5dd;border-radius:11px;padding:14px;text-align:center;cursor:pointer;transition:all 0.15s;background:#fcfcfd;position:relative; }
    .foto-upload-area:hover { border-color:#16a34a;background:#f0fdf4; }
    .foto-upload-area input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
    .foto-upload-icon { font-size:20px;margin-bottom:4px; }
    .foto-upload-text { font-size:12px;color:#6b7280; }
    .foto-upload-text strong { color:#15803d; }
    .foto-upload-hint { font-size:10px;color:#9ca3af;margin-top:2px; }
    .btn-hapus-foto { display:inline-flex;align-items:center;gap:6px;padding:8px 13px;border-radius:9px;border:1px solid #fecaca;background:#fef2f2;color:#dc2626;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit; }
    .btn-hapus-foto:hover { background:#fee2e2; }
    .btn-hapus-foto svg { width:14px;height:14px; }
    .foto-hapus-note { margin-top:8px;font-size:12px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px; }
    .foto-undo { background:none;border:none;color:#15803d;font-weight:700;cursor:pointer;font-size:12px;font-family:inherit;text-decoration:underline;padding:0; }
    /* Modal crop foto */
    .crop-modal { display:none;position:fixed;inset:0;z-index:1000;align-items:center;justify-content:center; }
    .crop-modal.open { display:flex; }
    .crop-backdrop { position:absolute;inset:0;background:rgba(17,24,39,0.55); }
    .crop-card { position:relative;background:#fff;border-radius:16px;padding:20px;width:min(360px,92vw);box-shadow:0 20px 50px rgba(0,0,0,0.25); }
    .crop-head { margin-bottom:14px; }
    .crop-title { font-size:16px;font-weight:700;color:#111827; }
    .crop-sub { font-size:11.5px;color:#6b7280;margin-top:2px; }
    .crop-stage { position:relative;width:100%;aspect-ratio:1;background:#111827;border-radius:12px;overflow:hidden;cursor:grab;touch-action:none;user-select:none; }
    .crop-stage.grabbing { cursor:grabbing; }
    .crop-stage img { position:absolute;top:0;left:0;transform-origin:0 0;pointer-events:none;max-width:none; }
    .crop-mask { position:absolute;inset:0;pointer-events:none;box-shadow:0 0 0 9999px rgba(17,24,39,0.5) inset;border-radius:50%; }
    .crop-zoom { display:flex;align-items:center;gap:10px;margin:14px 0 4px;color:#6b7280;font-weight:700; }
    .crop-zoom input[type=range] { flex:1;accent-color:#16a34a; }
    .crop-actions { display:flex;gap:10px;margin-top:14px; }
    .crop-btn { flex:1;padding:10px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;border:1px solid transparent; }
    .crop-btn.cancel { background:#fff;border-color:#e5e7eb;color:#6b7280; }
    .crop-btn.cancel:hover { background:#f9fafb; }
    .crop-btn.apply { background:#16a34a;color:#fff; }
    .crop-btn.apply:hover { background:#15803d; }

    .band-info-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:11px;padding:14px 16px;display:flex;align-items:center;gap:12px;margin-bottom:18px; }
    .band-info-badge { display:inline-flex;padding:5px 15px;border-radius:20px;font-size:14px;font-weight:800;background:#15803d;color:white; }
    .mdg-hint-box { background:#fffbeb;border:1px solid #fde68a;border-radius:11px;padding:13px 16px;font-size:12px;color:#92400e;margin-top:10px; }
    .mdg-hint-box ul { margin:6px 0 0 16px;display:flex;flex-direction:column;gap:3px; }

    .form-actions-card { background:white;border-radius:16px;border:1px solid var(--card-border);padding:18px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:var(--card-shadow); }
    .form-actions-right { display:flex;gap:10px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:11px 22px;border-radius:10px;font-size:13px;font-weight:600;border:1px solid #e4e7ec;text-decoration:none;transition:all 0.15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:11px 26px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all 0.15s; }
    .btn-save:hover { background:#166534;box-shadow:0 4px 12px rgba(21,128,61,0.25); }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }

    @media (max-width:640px) {
        .form-card { padding:20px; }
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
        .radio-group,.status-group { flex-direction:column; }
        .form-actions-card { flex-direction:column;align-items:stretch; }
        .form-actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')

@php
    // Nilai placeholder / "belum ditentukan" agar tampil paling bawah pada dropdown
    $phValues = ['', '-', '–', '—', 'belum ditentukan', 'belum ada', 'tidak ada', 'n/a', 'na', 'undefined', 'null'];
    $sortMaster = function ($collection, $col) use ($phValues) {
        return $collection->sortBy(function ($x) use ($col, $phValues) {
            $val = trim(mb_strtolower($x->$col ?? ''));
            $isPlaceholder = in_array($val, $phValues, true);
            // Prefix "0"/"1": placeholder selalu ke bawah, sisanya alfabetis
            return ($isPlaceholder ? '1' : '0') . '_' . $val;
        })->values();
    };
    $direktorats   = $sortMaster($direktorats,   'nama_direktorat');
    $kompartemens  = $sortMaster($kompartemens,  'nama_kompartemen');
    $departemens   = $sortMaster($departemens,   'nama_departemen');
    $jabatans      = $sortMaster($jabatans,      'nama_jabatan');
    $kodeStrukturs = $sortMaster($kodeStrukturs, 'kode_struktur');
@endphp

<a href="{{ route('karyawan.show', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Detail Karyawan
</a>

<div class="page-header">
    <div class="page-title">Edit Data Karyawan</div>
    <div class="page-sub">Perbarui informasi karyawan <strong>{{ $karyawan->nama }}</strong></div>
</div>

<form method="POST" action="{{ route('karyawan.update', $karyawan) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    {{-- ===== DATA PRIBADI ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="section-title">Data Pribadi</div>
                <div class="section-sub">Informasi identitas dasar karyawan</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">NIK <span class="req">*</span></label>
                <input type="text" name="nik" value="{{ old('nik', $karyawan->nik) }}"
                       class="form-input {{ $errors->has('nik') ? 'error-input' : '' }}"
                       placeholder="Nomor Induk Karyawan" />
                @error('nik')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $karyawan->nama) }}"
                       class="form-input {{ $errors->has('nama') ? 'error-input' : '' }}"
                       placeholder="Nama lengkap karyawan" />
                @error('nama')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                @php $jk = old('jenis_kelamin', $karyawan->jenis_kelamin); @endphp
                <div class="radio-group">
                    <label class="radio-card {{ $jk=='L' ? 'selected' : '' }}" id="card-L">
                        <input type="radio" name="jenis_kelamin" value="L" {{ $jk=='L' ? 'checked' : '' }} onchange="selectRadio('L')">
                        <div class="radio-dot"></div>
                        <span class="radio-label">Laki-laki</span>
                    </label>
                    <label class="radio-card {{ $jk=='P' ? 'selected' : '' }}" id="card-P">
                        <input type="radio" name="jenis_kelamin" value="P" {{ $jk=='P' ? 'checked' : '' }} onchange="selectRadio('P')">
                        <div class="radio-dot"></div>
                        <span class="radio-label">Perempuan</span>
                    </label>
                </div>
                @error('jenis_kelamin')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tempat Lahir <span class="req">*</span></label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $karyawan->tempat_lahir) }}"
                       class="form-input" placeholder="Kota kelahiran" />
                @error('tempat_lahir')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Lahir <span class="req">*</span></label>
                <input type="date" name="tanggal_lahir"
                       value="{{ old('tanggal_lahir', $karyawan->tanggal_lahir?->format('Y-m-d')) }}"
                       class="form-input" />
                @error('tanggal_lahir')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Masuk <span class="req">*</span></label>
                <input type="date" name="tanggal_masuk"
                       value="{{ old('tanggal_masuk', $karyawan->tanggal_masuk?->format('Y-m-d')) }}"
                       class="form-input" />
                @error('tanggal_masuk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="req">*</span></label>
                @php $status = old('status', $karyawan->status); @endphp
                <div class="status-group">
                    <label class="status-card aktif-card {{ $status=='aktif' ? 'selected' : '' }}" id="status-aktif">
                        <input type="radio" name="status" value="aktif" {{ $status=='aktif' ? 'checked' : '' }} onchange="selectStatus('aktif')">
                        <div class="status-dot"></div>
                        <span class="status-label">Aktif</span>
                    </label>
                    <label class="status-card nonaktif-card {{ $status=='tidak aktif' ? 'selected' : '' }}" id="status-nonaktif">
                        <input type="radio" name="status" value="tidak aktif" {{ $status=='tidak aktif' ? 'checked' : '' }} onchange="selectStatus('tidak aktif')">
                        <div class="status-dot"></div>
                        <span class="status-label">Tidak Aktif</span>
                    </label>
                </div>
                @error('status')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Status Kepegawaian</label>
                <div class="select-wrap">
                    <select name="status_kepegawaian" class="form-input">
                        <option value="">— Pilih Status Kepegawaian —</option>
                        @foreach(\App\Models\Karyawan::STATUS_KEPEGAWAIAN as $sk)
                            <option value="{{ $sk }}" {{ old('status_kepegawaian', $karyawan->status_kepegawaian)==$sk ? 'selected' : '' }}>{{ $sk }}</option>
                        @endforeach
                    </select>
                </div>
                @error('status_kepegawaian')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Foto Karyawan</label>
                <div class="foto-upload-wrap">
                    <div class="foto-preview-box">
                        <span id="fotoInitial" style="{{ $karyawan->foto ? 'display:none;' : '' }}">{{ initials($karyawan->nama) }}</span>
                        <img id="fotoPreview" src="{{ $karyawan->foto ? Storage::url($karyawan->foto) : '' }}" style="{{ $karyawan->foto ? '' : 'display:none;' }}" alt="foto">
                    </div>
                    <div class="foto-upload-area">
                        <input type="file" id="fotoFile" name="foto" accept="image/*" onchange="onFotoPick(this)" />
                        <div class="foto-upload-icon">📷</div>
                        <div class="foto-upload-text"><strong>Klik untuk upload</strong> atau drag & drop</div>
                        <div class="foto-upload-hint">Bisa di-zoom &amp; geser setelah dipilih · PNG, JPG (maks. 2MB)</div>
                    </div>
                    <input type="hidden" name="hapus_foto" id="hapusFoto" value="0">
                    <button type="button" id="btnHapusFoto" class="btn-hapus-foto" onclick="hapusFotoAction()" style="{{ $karyawan->foto ? '' : 'display:none;' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Hapus Foto
                    </button>
                </div>
                <div id="fotoHapusNote" class="foto-hapus-note" style="display:none;">Foto akan dihapus saat disimpan. <button type="button" onclick="batalHapusFoto()" class="foto-undo">Batalkan</button></div>
                @error('foto')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Modal crop/zoom foto --}}
            <div class="crop-modal" id="cropModal">
                <div class="crop-backdrop" onclick="cropCancel()"></div>
                <div class="crop-card">
                    <div class="crop-head">
                        <div class="crop-title">Atur Foto</div>
                        <div class="crop-sub">Geser untuk memindah · zoom untuk memperbesar/memperkecil</div>
                    </div>
                    <div class="crop-stage" id="cropStage">
                        <img id="cropImg" alt="crop" draggable="false">
                        <div class="crop-mask"></div>
                    </div>
                    <div class="crop-zoom">
                        <span>−</span>
                        <input type="range" id="cropZoom" min="1" max="3" step="0.01" value="1">
                        <span>+</span>
                    </div>
                    <div class="crop-actions">
                        <button type="button" class="crop-btn cancel" onclick="cropCancel()">Batal</button>
                        <button type="button" class="crop-btn apply" onclick="cropApply()">Terapkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== KONTAK & PENDIDIKAN ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon blue">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.74-1.74a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
                <div class="section-title">Kontak & Pendidikan</div>
                <div class="section-sub">Nomor telepon, email, dan history pendidikan</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $karyawan->no_hp) }}"
                       class="form-input" placeholder="cth: 081234567890" inputmode="tel" />
                @error('no_hp')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $karyawan->email) }}"
                       class="form-input {{ $errors->has('email') ? 'error-input' : '' }}"
                       placeholder="cth: nama@pupuk-indonesia.com" />
                @error('email')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">History Pendidikan</label>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#f9fafb;border:1px solid #eef0f2;border-radius:10px;padding:12px 16px;">
                    <div style="font-size:13px;color:#6b7280;">
                        Pendidikan Terakhir:
                        <strong style="color:#111827;">{{ $karyawan->jenjang_pendidikan ?: '-' }}</strong>@if($karyawan->jurusan) · {{ $karyawan->jurusan }}@endif
                        <span style="color:#9ca3af;">({{ $karyawan->riwayatPendidikan->count() }} jenjang)</span>
                    </div>
                    <a href="{{ route('riwayat_pendidikan.index', $karyawan) }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1.5px solid #15803d;color:#15803d;padding:8px 14px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;">
                        Kelola History Pendidikan →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== JABATAN & STRUKTUR ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div>
                <div class="section-title">Jabatan & Struktur Organisasi</div>
                <div class="section-sub">Posisi dan penempatan karyawan dalam organisasi</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Jabatan <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="jabatan_id" class="form-input">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $j)
                            <option value="{{ $j->id }}" {{ old('jabatan_id', $karyawan->jabatan_id)==$j->id ? 'selected' : '' }}>
                                {{ $j->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('jabatan_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Struktural / Fungsional</label>
                <div class="select-wrap">
                    <select name="struktural_fungsional" class="form-input">
                        <option value="">-- Pilih --</option>
                        <option value="Struktural" {{ old('struktural_fungsional', $karyawan->struktural_fungsional) === 'Struktural' ? 'selected' : '' }}>Struktural</option>
                        <option value="Fungsional" {{ old('struktural_fungsional', $karyawan->struktural_fungsional) === 'Fungsional' ? 'selected' : '' }}>Fungsional</option>
                    </select>
                </div>
            </div>

            <div class="form-group full">
                <label class="form-label">Jabatan Saat Ini <span class="req">*</span></label>
                <input type="text" name="jabatan_saat_ini"
                       value="{{ old('jabatan_saat_ini', $karyawan->jabatan_saat_ini) }}"
                       class="form-input" placeholder="cth: Associate Officer Talenta Manajemen" />
                <span class="form-hint">Jabatan lengkap yang ditampilkan di profil</span>
                @error('jabatan_saat_ini')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Direktorat <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="direktorat_id" class="form-input">
                        <option value="">-- Pilih Direktorat --</option>
                        @foreach($direktorats as $d)
                            <option value="{{ $d->id }}" {{ old('direktorat_id', $karyawan->direktorat_id)==$d->id ? 'selected' : '' }}>
                                {{ $d->nama_direktorat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('direktorat_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Kompartemen <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="kompartemen_id" class="form-input">
                        <option value="">-- Pilih Kompartemen --</option>
                        @foreach($kompartemens as $k)
                            <option value="{{ $k->id }}" {{ old('kompartemen_id', $karyawan->kompartemen_id)==$k->id ? 'selected' : '' }}>
                                {{ $k->nama_kompartemen }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('kompartemen_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Departemen <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="departemen_id" class="form-input">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departemens as $d)
                            <option value="{{ $d->id }}" {{ old('departemen_id', $karyawan->departemen_id)==$d->id ? 'selected' : '' }}>
                                {{ $d->nama_departemen }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('departemen_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Kode Struktur <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="kode_struktur_id" class="form-input">
                        <option value="">-- Pilih Kode Struktur --</option>
                        @foreach($kodeStrukturs as $ks)
                            <option value="{{ $ks->id }}" {{ old('kode_struktur_id', $karyawan->kode_struktur_id)==$ks->id ? 'selected' : '' }}>
                                {{ $ks->kode_struktur }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('kode_struktur_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ===== BAND & GRADE ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon purple">
                <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <div>
                <div class="section-title">Band & Grade</div>
                <div class="section-sub">Job Grade, Person Grade dan Band karyawan</div>
            </div>
        </div>

        <div class="band-info-box">
            <span class="band-info-badge" id="bandDisplay">{{ $karyawan->band }}</span>
            <div style="font-size:12px;color:#374151;">
                <strong>Band saat ini</strong> — dihitung otomatis dari Job Grade
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Job Grade <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="job_grade_id" class="form-input" id="jobGradeSelect" onchange="updateBand()">
                        <option value="">-- Pilih Job Grade --</option>
                        @foreach($jobGrades->sortByDesc(fn($j) => (int) $j->job_grade) as $j)
                            <option value="{{ $j->id }}"
                                    data-grade="{{ $j->job_grade }}"
                                    {{ old('job_grade_id', $karyawan->job_grade_id)==$j->id ? 'selected' : '' }}>
                                JG {{ $j->job_grade }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('job_grade_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Person Grade <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="person_grade_id" class="form-input">
                        <option value="">-- Pilih Person Grade --</option>
                        @foreach($personGrades->sortByDesc(fn($p) => (int) $p->person_grade) as $p)
                            <option value="{{ $p->id }}" {{ old('person_grade_id', $karyawan->person_grade_id)==$p->id ? 'selected' : '' }}>
                                PG {{ $p->person_grade }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('person_grade_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mdg-hint-box">
            <strong>📋 Ketentuan MDG:</strong>
            <ul>
                <li>Naik <strong>Person Grade</strong> → min <strong>1 tahun</strong> TMT PG saat ini</li>
                <li>Naik <strong>Job Grade</strong> → min <strong>2 tahun</strong> TMT JG saat ini (PG harus = JG)</li>
                <li>Naik <strong>Band</strong> → MDG JG min <strong>2 tahun</strong>, MDG PG min <strong>1 tahun</strong>, MDG Band min <strong>3 tahun</strong> (dihitung dari TMT JG saat masuk band)</li>
            </ul>
        </div>
    </div>

    {{-- ===== TMT GRADE ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon amber">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
                <div class="section-title">TMT Grade</div>
                <div class="section-sub">Tanggal mulai berlaku grade — digunakan untuk menghitung MDG</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">TMT Job Grade</label>
                <input type="date" name="tanggal_mulai_jg"
                       value="{{ old('tanggal_mulai_jg', $karyawan->tanggal_mulai_jg?->format('Y-m-d')) }}"
                       class="form-input" />
                <span class="form-hint">
                    MDG JG saat ini:
                    <strong>{{ $karyawan->mdg_jg_bulan > 0 ? $karyawan->mdg_jg_bulan . ' bulan' : '-' }}</strong>
                </span>
                @error('tanggal_mulai_jg')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">TMT Person Grade</label>
                <input type="date" name="tanggal_mulai_pg"
                       value="{{ old('tanggal_mulai_pg', $karyawan->tanggal_mulai_pg?->format('Y-m-d')) }}"
                       class="form-input" />
                <span class="form-hint">
                    MDG PG saat ini:
                    <strong>{{ $karyawan->mdg_pg_bulan > 0 ? $karyawan->mdg_pg_bulan . ' bulan' : '-' }}</strong>
                </span>
                @error('tanggal_mulai_pg')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">TMT Band</label>
                <input type="date" name="tanggal_mulai_band"
                       value="{{ old('tanggal_mulai_band', $karyawan->tanggal_mulai_band?->format('Y-m-d')) }}"
                       class="form-input" />
                <span class="form-hint">
                    Tanggal mulai di Band saat ini. Untuk sementara diisi manual; ke depan bisa otomatis dari History Jabatan. Bila kosong, MDG Band memakai TMT Job Grade.
                </span>
                @error('tanggal_mulai_band')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="background:#f9fafb;border:1px solid var(--card-border);border-radius:11px;padding:13px 16px;margin-top:12px;font-size:12px;color:#374151;">
            📊 <strong>MDG Band</strong> (dari TMT Band; bila kosong pakai TMT JG):
            <strong style="color:{{ $karyawan->mdg_band_bulan >= 36 ? '#15803d' : '#d97706' }}">
                {{ $karyawan->mdg_band_bulan }} bulan
            </strong>
            dari min 36 bulan untuk naik Band
        </div>
    </div>

    {{-- FORM ACTIONS --}}
    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af;"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('karyawan.show', $karyawan) }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Update Karyawan
            </button>
        </div>
    </div>

</form>

@endsection

@push('scripts')
<script>
    const bandMap = {
        22:'Band 1', 21:'Band 1', 20:'Band 1',
        19:'Band 2', 18:'Band 2', 17:'Band 2',
        16:'Band 3', 15:'Band 3',
        14:'Band 4', 13:'Band 4',
        12:'Band 5', 11:'Band 5', 10:'Band 5',
         9:'Band 6',  8:'Band 6',  7:'Band 6',
    };

    function updateBand() {
        const sel   = document.getElementById('jobGradeSelect');
        const opt   = sel.options[sel.selectedIndex];
        const grade = parseInt(opt.dataset.grade);
        document.getElementById('bandDisplay').textContent = bandMap[grade] || '-';
    }

    // ===== Foto: hapus + crop/zoom saat upload =====
    const ORIG_FOTO = @json($karyawan->foto ? Storage::url($karyawan->foto) : '');
    const fFile    = document.getElementById('fotoFile');
    const fPreview = document.getElementById('fotoPreview');
    const fInitial = document.getElementById('fotoInitial');
    const fBtnHps  = document.getElementById('btnHapusFoto');
    const fHapus   = document.getElementById('hapusFoto');
    const fNote    = document.getElementById('fotoHapusNote');

    function onFotoPick(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => openCrop(e.target.result);
        reader.readAsDataURL(input.files[0]);
    }

    function hapusFotoAction() {
        fHapus.value = '1';
        fFile.value = '';
        fPreview.style.display = 'none';
        fInitial.style.display = '';
        fBtnHps.style.display = 'none';
        fNote.style.display = '';
    }
    function batalHapusFoto() {
        fHapus.value = '0';
        fNote.style.display = 'none';
        if (ORIG_FOTO) {
            fPreview.src = ORIG_FOTO;
            fPreview.style.display = 'block';
            fInitial.style.display = 'none';
            fBtnHps.style.display = 'inline-flex';
        } else {
            fInitial.style.display = '';
            fPreview.style.display = 'none';
        }
    }

    // ── Cropper ──
    const cModal = document.getElementById('cropModal');
    const cStage = document.getElementById('cropStage');
    const cImg   = document.getElementById('cropImg');
    const cZoom  = document.getElementById('cropZoom');
    let natW, natH, coverScale, scale, offX, offY, V;
    let dragging = false, lastX = 0, lastY = 0;

    function clampScale(s) { return Math.min(coverScale * 3, Math.max(coverScale, s)); }
    function clampOff() {
        const w = natW * scale, h = natH * scale;
        offX = Math.min(0, Math.max(V - w, offX));
        offY = Math.min(0, Math.max(V - h, offY));
    }
    function renderCrop() {
        cImg.style.width = natW + 'px';
        cImg.style.height = natH + 'px';
        cImg.style.transform = `translate(${offX}px,${offY}px) scale(${scale})`;
    }
    function setScale(ns) {
        ns = clampScale(ns);
        const px = (V / 2 - offX) / scale, py = (V / 2 - offY) / scale;
        scale = ns;
        offX = V / 2 - px * scale; offY = V / 2 - py * scale;
        clampOff(); renderCrop();
    }

    function openCrop(dataUrl) {
        cModal.classList.add('open');
        cImg.onload = () => {
            natW = cImg.naturalWidth; natH = cImg.naturalHeight;
            V = cStage.clientWidth || 300;
            coverScale = Math.max(V / natW, V / natH);
            scale = coverScale;
            offX = (V - natW * scale) / 2;
            offY = (V - natH * scale) / 2;
            cZoom.value = 1;
            renderCrop();
        };
        cImg.src = dataUrl;
    }
    function closeCrop() { cModal.classList.remove('open'); }

    function cropCancel() {
        closeCrop();
        fFile.value = ''; // buang file yang belum di-crop
    }

    function cropApply() {
        const C = 400;
        const canvas = document.createElement('canvas');
        canvas.width = C; canvas.height = C;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, C, C);
        ctx.save();
        ctx.beginPath(); ctx.arc(C / 2, C / 2, C / 2, 0, Math.PI * 2); ctx.clip();
        const sx = -offX / scale, sy = -offY / scale, sw = V / scale, sh = V / scale;
        ctx.drawImage(cImg, sx, sy, sw, sh, 0, 0, C, C);
        ctx.restore();
        canvas.toBlob(blob => {
            const file = new File([blob], 'foto.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer(); dt.items.add(file);
            fFile.files = dt.files;
            fPreview.src = canvas.toDataURL('image/jpeg', 0.9);
            fPreview.style.display = 'block';
            fInitial.style.display = 'none';
            fBtnHps.style.display = 'inline-flex';
            fHapus.value = '0';
            fNote.style.display = 'none';
            closeCrop();
        }, 'image/jpeg', 0.9);
    }

    cZoom.addEventListener('input', () => setScale(coverScale * parseFloat(cZoom.value)));
    cStage.addEventListener('wheel', e => {
        e.preventDefault();
        setScale(scale * (e.deltaY < 0 ? 1.08 : 0.92));
        cZoom.value = (scale / coverScale).toFixed(2);
    }, { passive: false });
    cStage.addEventListener('pointerdown', e => {
        dragging = true; lastX = e.clientX; lastY = e.clientY;
        cStage.setPointerCapture(e.pointerId); cStage.classList.add('grabbing');
    });
    cStage.addEventListener('pointermove', e => {
        if (!dragging) return;
        offX += e.clientX - lastX; offY += e.clientY - lastY;
        lastX = e.clientX; lastY = e.clientY;
        clampOff(); renderCrop();
    });
    cStage.addEventListener('pointerup', () => { dragging = false; cStage.classList.remove('grabbing'); });
    cStage.addEventListener('pointercancel', () => { dragging = false; cStage.classList.remove('grabbing'); });

    function selectRadio(val) {
        document.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('card-' + val).classList.add('selected');
    }

    function selectStatus(val) {
        document.getElementById('status-aktif').classList.remove('selected');
        document.getElementById('status-nonaktif').classList.remove('selected');
        document.getElementById(val === 'aktif' ? 'status-aktif' : 'status-nonaktif').classList.add('selected');
    }

    window.addEventListener('DOMContentLoaded', updateBand);
</script>
@endpush
