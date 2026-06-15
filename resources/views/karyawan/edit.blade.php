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

    .form-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:28px;margin-bottom:16px; }

    .section-header { display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f3f4f6; }
    .section-icon { width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .section-icon svg { width:16px;height:16px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .section-icon.blue { background:#eff6ff; }
    .section-icon.blue svg { stroke:#1d4ed8; }
    .section-icon.purple { background:#f5f3ff; }
    .section-icon.purple svg { stroke:#7c3aed; }
    .section-title { font-size:14px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }

    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;display:flex;align-items:center;gap:4px; }
    .req { color:#ef4444; }

    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .form-input[readonly] { background:#f3f4f6;color:#15803d;font-weight:700;cursor:not-allowed; }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }

    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    .radio-group { display:flex;gap:10px; }
    .radio-card { flex:1;display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;cursor:pointer;transition:all 0.15s;background:#fafafa; }
    .radio-card input[type=radio] { display:none; }
    .radio-card:hover { border-color:#16a34a;background:#f0fdf4; }
    .radio-card.selected { border-color:#16a34a;background:#f0fdf4; }
    .radio-dot { width:18px;height:18px;border-radius:50%;border:2px solid #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.15s; }
    .radio-card.selected .radio-dot { border-color:#16a34a;background:#16a34a; }
    .radio-card.selected .radio-dot::after { content:'';width:6px;height:6px;border-radius:50%;background:white; }
    .radio-label { font-size:13px;font-weight:600;color:#374151; }
    .radio-card.selected .radio-label { color:#15803d; }

    .status-group { display:flex;gap:10px; }
    .status-card { flex:1;display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;cursor:pointer;transition:all 0.15s;background:#fafafa; }
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
    .foto-upload-area { flex:1;min-width:180px;border:2px dashed #d1d5db;border-radius:10px;padding:14px;text-align:center;cursor:pointer;transition:all 0.15s;background:#fafafa;position:relative; }
    .foto-upload-area:hover { border-color:#16a34a;background:#f0fdf4; }
    .foto-upload-area input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
    .foto-upload-icon { font-size:20px;margin-bottom:4px; }
    .foto-upload-text { font-size:12px;color:#6b7280; }
    .foto-upload-text strong { color:#15803d; }
    .foto-upload-hint { font-size:10px;color:#9ca3af;margin-top:2px; }

    .band-info-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;margin-bottom:16px; }
    .band-info-badge { display:inline-flex;padding:4px 14px;border-radius:20px;font-size:14px;font-weight:800;background:#15803d;color:white; }
    .mdg-hint-box { background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;font-size:12px;color:#92400e;margin-top:8px; }
    .mdg-hint-box ul { margin:6px 0 0 16px;display:flex;flex-direction:column;gap:3px; }

    .form-actions-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .form-actions-right { display:flex;gap:10px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all 0.15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all 0.15s; }
    .btn-save:hover { background:#166534;transform:translateY(-1px);box-shadow:0 4px 12px rgba(21,128,61,0.3); }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }

    @media (max-width:640px) {
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

<a href="{{ route('karyawan.show', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Detail Karyawan
</a>

<div class="page-header">
    <div class="page-title">✏️ Edit Data Karyawan</div>
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
                <label class="form-label">Foto Karyawan</label>
                <div class="foto-upload-wrap">
                    <div class="foto-preview-box">
                        @if($karyawan->foto)
                            <img src="{{ Storage::url($karyawan->foto) }}" id="fotoPreview" alt="foto">
                        @else
                            <span id="fotoInitial">{{ initials($karyawan->nama) }}</span>
                            <img id="fotoPreview" style="display:none;" alt="foto">
                        @endif
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

            <div class="form-group">
                <label class="form-label">Jabatan Saat Ini</label>
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
                        @foreach($jobGrades as $j)
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
                        @foreach($personGrades as $p)
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
            <div class="section-icon blue">
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
        </div>

        {{-- Info MDG Band --}}
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px 16px;margin-top:12px;font-size:12px;color:#374151;">
            📊 <strong>MDG Band</strong> (dari TMT JG saat masuk band):
            <strong style="color:{{ $karyawan->mdg_band_bulan >= 36 ? '#15803d' : '#d97706' }};">
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