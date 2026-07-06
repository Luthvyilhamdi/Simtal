@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('breadcrumb-parent', 'Profil Karyawan')
@section('breadcrumb', 'Tambah Karyawan')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }
    .req { color:#ef4444; }

    .form-card { background:white;border-radius:16px;border:1px solid var(--card-border);padding:26px 28px;margin-bottom:16px;box-shadow:var(--card-shadow); }

    .section-header { display:flex;align-items:center;gap:12px;margin-bottom:22px;padding-bottom:14px;border-bottom:1px solid #f3f4f6; }
    .section-icon { width:36px;height:36px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .section-icon svg { width:17px;height:17px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .section-icon.blue { background:#eff6ff; } .section-icon.blue svg { stroke:#2563eb; }
    .section-icon.purple { background:#f5f3ff; } .section-icon.purple svg { stroke:#7c3aed; }
    .section-icon.amber { background:#fffbeb; } .section-icon.amber svg { stroke:#d97706; }
    .section-title { font-size:14.5px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:18px; }
    .form-group { display:flex;flex-direction:column;gap:7px; }
    .form-group.full { grid-column:1/-1; }

    .form-label { font-size:12px;font-weight:600;color:#475467;display:flex;align-items:center;gap:4px; }

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
    .foto-preview-box { width:80px;height:80px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;overflow:hidden;border:3px solid #bbf7d0; }
    .foto-preview-box img { width:100%;height:100%;object-fit:cover; }
    .foto-upload-area { flex:1;min-width:180px;border:1.5px dashed #d0d5dd;border-radius:11px;padding:14px;text-align:center;cursor:pointer;transition:all 0.15s;background:#fcfcfd;position:relative; }
    .foto-upload-area:hover { border-color:#16a34a;background:#f0fdf4; }
    .foto-upload-area input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
    .foto-upload-icon { font-size:20px;margin-bottom:4px; }
    .foto-upload-text { font-size:12px;color:#6b7280; } .foto-upload-text strong { color:#15803d; }
    .foto-upload-hint { font-size:10px;color:#9ca3af;margin-top:2px; }

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

<a href="{{ route('karyawan.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Profil Karyawan
</a>

<div class="page-header">
    <div class="page-title">Tambah Karyawan Baru</div>
    <div class="page-sub">Lengkapi data karyawan di bawah ini. Kolom bertanda <span class="req">*</span> wajib diisi.</div>
</div>

<form method="POST" action="{{ route('karyawan.store') }}" enctype="multipart/form-data">
    @csrf

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
                <input type="text" name="nik" value="{{ old('nik') }}" class="form-input {{ $errors->has('nik') ? 'error-input' : '' }}" placeholder="Nomor Induk Karyawan" />
                @error('nik')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" class="form-input {{ $errors->has('nama') ? 'error-input' : '' }}" placeholder="Nama lengkap karyawan" />
                @error('nama')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Jenis Kelamin <span class="req">*</span></label>
                @php $jk = old('jenis_kelamin'); @endphp
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
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="form-input" placeholder="Kota kelahiran" />
                @error('tempat_lahir')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Lahir <span class="req">*</span></label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="form-input" />
                @error('tanggal_lahir')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Masuk <span class="req">*</span></label>
                <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}" class="form-input" />
                @error('tanggal_masuk')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="req">*</span></label>
                @php $status = old('status', 'aktif'); @endphp
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
                <select name="status_kepegawaian" class="form-input">
                    <option value="">— Pilih Status Kepegawaian —</option>
                    @foreach(\App\Models\Karyawan::STATUS_KEPEGAWAIAN as $sk)
                        <option value="{{ $sk }}" {{ old('status_kepegawaian')==$sk ? 'selected' : '' }}>{{ $sk }}</option>
                    @endforeach
                </select>
                @error('status_kepegawaian')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Foto Karyawan</label>
                <div class="foto-upload-wrap">
                    <div class="foto-preview-box">
                        <span id="fotoInitial">📷</span>
                        <img id="fotoPreview" style="display:none;" alt="foto">
                    </div>
                    <div class="foto-upload-area">
                        <input type="file" name="foto" accept="image/*" onchange="previewFoto(this)" />
                        <div class="foto-upload-icon">📷</div>
                        <div class="foto-upload-text"><strong>Klik untuk upload</strong> atau drag & drop</div>
                        <div class="foto-upload-hint">PNG, JPG, JPEG (maks. 2MB)</div>
                    </div>
                </div>
                @error('foto')<div class="error-msg">{{ $message }}</div>@enderror
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
                <div class="section-sub">Nomor telepon, email, dan riwayat pendidikan</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp') }}" class="form-input" placeholder="cth: 081234567890" inputmode="tel" />
                @error('no_hp')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-input {{ $errors->has('email') ? 'error-input' : '' }}" placeholder="cth: nama@pupuk-indonesia.com" />
                @error('email')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Jenjang Pendidikan</label>
                <div class="select-wrap">
                    <select name="jenjang_pendidikan" class="form-input">
                        <option value="">-- Pilih --</option>
                        @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','D4','S1','S2','S3'] as $jp)
                            <option value="{{ $jp }}" {{ old('jenjang_pendidikan')==$jp ? 'selected' : '' }}>{{ $jp }}</option>
                        @endforeach
                    </select>
                </div>
                @error('jenjang_pendidikan')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Jurusan</label>
                <input type="text" name="jurusan" value="{{ old('jurusan') }}" class="form-input" placeholder="cth: Teknik Industri" />
                @error('jurusan')<div class="error-msg">{{ $message }}</div>@enderror
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
                            <option value="{{ $j->id }}" {{ old('jabatan_id')==$j->id ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
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
                        <option value="Struktural" {{ old('struktural_fungsional')=='Struktural' ? 'selected' : '' }}>Struktural</option>
                        <option value="Fungsional" {{ old('struktural_fungsional')=='Fungsional' ? 'selected' : '' }}>Fungsional</option>
                    </select>
                </div>
                @error('struktural_fungsional')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group full">
                <label class="form-label">Jabatan Saat Ini <span class="req">*</span></label>
                <input type="text" name="jabatan_saat_ini" value="{{ old('jabatan_saat_ini') }}" class="form-input" placeholder="cth: Associate Officer Talenta Manajemen" />
                <span class="form-hint">Jabatan lengkap yang ditampilkan di profil karyawan</span>
                @error('jabatan_saat_ini')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Direktorat <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="direktorat_id" class="form-input">
                        <option value="">-- Pilih Direktorat --</option>
                        @foreach($direktorats as $d)
                            <option value="{{ $d->id }}" {{ old('direktorat_id')==$d->id ? 'selected' : '' }}>{{ $d->nama_direktorat }}</option>
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
                            <option value="{{ $k->id }}" {{ old('kompartemen_id')==$k->id ? 'selected' : '' }}>{{ $k->nama_kompartemen }}</option>
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
                            <option value="{{ $d->id }}" {{ old('departemen_id')==$d->id ? 'selected' : '' }}>{{ $d->nama_departemen }}</option>
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
                            <option value="{{ $ks->id }}" {{ old('kode_struktur_id')==$ks->id ? 'selected' : '' }}>{{ $ks->kode_struktur }}</option>
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
            <span class="band-info-badge" id="bandDisplay">-</span>
            <div style="font-size:12px;color:#374151;">
                <strong>Band</strong> — dihitung otomatis dari Job Grade yang dipilih
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Job Grade <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="job_grade_id" class="form-input" id="jobGradeSelect" onchange="updateBand()">
                        <option value="">-- Pilih Job Grade --</option>
                        @foreach($jobGrades as $j)
                            <option value="{{ $j->id }}" data-grade="{{ $j->job_grade }}" {{ old('job_grade_id')==$j->id ? 'selected' : '' }}>JG {{ $j->job_grade }}</option>
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
                        @foreach($personGrades as $p)
                            <option value="{{ $p->id }}" {{ old('person_grade_id')==$p->id ? 'selected' : '' }}>PG {{ $p->person_grade }}</option>
                        @endforeach
                    </select>
                </div>
                @error('person_grade_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mdg-hint-box">
            <strong>📋 Ketentuan Masa Dinas Grade (MDG):</strong>
            <ul>
                <li>Naik <strong>Person Grade</strong> → min <strong>1 tahun</strong> TMT PG saat ini</li>
                <li>Naik <strong>Job Grade</strong> → min <strong>2 tahun</strong> TMT JG saat ini (PG harus = JG)</li>
                <li>Naik <strong>Band</strong> → MDG JG min <strong>2 tahun</strong>, MDG PG min <strong>1 tahun</strong>, MDG Band min <strong>3 tahun</strong></li>
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
                <div class="section-sub">Tanggal mulai berlaku grade — untuk menghitung MDG (opsional)</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">TMT Job Grade</label>
                <input type="date" name="tanggal_mulai_jg" value="{{ old('tanggal_mulai_jg') }}" class="form-input" />
                <span class="form-hint">Tanggal mulai di Job Grade saat ini</span>
                @error('tanggal_mulai_jg')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">TMT Person Grade</label>
                <input type="date" name="tanggal_mulai_pg" value="{{ old('tanggal_mulai_pg') }}" class="form-input" />
                <span class="form-hint">Tanggal mulai di Person Grade saat ini</span>
                @error('tanggal_mulai_pg')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">TMT Band</label>
                <input type="date" name="tanggal_mulai_band" value="{{ old('tanggal_mulai_band') }}" class="form-input" />
                <span class="form-hint">Tanggal mulai di Band saat ini (untuk MDG Band). Bila kosong, memakai TMT Job Grade.</span>
                @error('tanggal_mulai_band')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- FORM ACTIONS --}}
    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af;"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('karyawan.index') }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Karyawan
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
    function previewFoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.getElementById('fotoPreview');
                const initial = document.getElementById('fotoInitial');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (initial) initial.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
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
