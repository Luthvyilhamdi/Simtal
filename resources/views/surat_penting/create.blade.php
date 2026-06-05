@extends('layouts.app')
@section('title', 'Upload Surat')
@section('breadcrumb-parent', 'Surat Penting')
@section('breadcrumb', 'Upload Surat')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }
    .form-wrap { max-width:100%; }
    .form-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:28px;margin-bottom:16px; }
    .section-header { display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f3f4f6; }
    .section-icon { width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .section-icon svg { width:16px;height:16px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .section-title { font-size:14px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }
    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px; }
    .req { color:#ef4444; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    /* Tipe Toggle */
    .tipe-toggle { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:4px; }
    .tipe-option { position:relative; }
    .tipe-option input[type=radio] { position:absolute;opacity:0;width:0;height:0; }
    .tipe-label {
        display:flex;align-items:center;gap:10px;padding:14px 16px;
        border:2px solid #e5e7eb;border-radius:12px;cursor:pointer;
        transition:all 0.15s;background:#fafafa;
    }
    .tipe-label:hover { border-color:#d1d5db;background:white; }
    .tipe-option input:checked + .tipe-label {
        border-color:#16a34a;background:#f0fdf4;
    }
    .tipe-label-icon { width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
    .tipe-label-text { font-size:13px;font-weight:600;color:#111827; }
    .tipe-label-sub { font-size:11px;color:#9ca3af;margin-top:1px; }
    .tipe-option input:checked + .tipe-label .tipe-label-text { color:#15803d; }

    /* File Upload */
    .file-upload-area { border:2px dashed #d1d5db;border-radius:12px;padding:32px;text-align:center;cursor:pointer;transition:all 0.15s;background:#fafafa;position:relative; }
    .file-upload-area:hover { border-color:#16a34a;background:#f0fdf4; }
    .file-upload-area.dragover { border-color:#16a34a;background:#f0fdf4; }
    .file-upload-area input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
    .file-upload-icon { font-size:40px;margin-bottom:12px; }
    .file-upload-text { font-size:14px;color:#374151;font-weight:600;margin-bottom:4px; }
    .file-upload-text strong { color:#15803d; }
    .file-upload-hint { font-size:12px;color:#9ca3af; }
    .file-preview { display:none;align-items:center;gap:12px;padding:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;margin-top:10px; }
    .file-preview.show { display:flex; }
    .file-preview-icon { font-size:28px;flex-shrink:0; }
    .file-preview-name { font-size:13px;font-weight:600;color:#111827;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .file-preview-size { font-size:12px;color:#6b7280;flex-shrink:0; }
    .file-preview-remove { width:24px;height:24px;border-radius:50%;border:none;background:#fee2e2;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0; }

    .form-actions-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .form-actions-right { display:flex;gap:10px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all 0.15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all 0.15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }
    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
        .tipe-toggle { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')
<div class="form-wrap">
    <a href="{{ route('surat_penting.index') }}" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke Surat Penting
    </a>

    <div class="page-header">
        <div class="page-title">📤 Upload Surat Penting</div>
        <div class="page-sub">Upload surat karyawan atau dokumen umum / pedoman</div>
    </div>

    <form method="POST" action="{{ route('surat_penting.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Tipe Surat --}}
        <div class="form-card">
            <div class="section-header">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <div class="section-title">Tipe Dokumen</div>
                    <div class="section-sub">Pilih apakah surat ini milik karyawan tertentu atau dokumen umum</div>
                </div>
            </div>

            <div class="tipe-toggle">
                <div class="tipe-option">
                    <input type="radio" name="tipe" id="tipePersonal" value="personal"
                        {{ old('tipe', 'personal') === 'personal' ? 'checked' : '' }}
                        onchange="onTipeChange(this.value)">
                    <label class="tipe-label" for="tipePersonal">
                        <div class="tipe-label-icon" style="background:#f0fdf4">👤</div>
                        <div>
                            <div class="tipe-label-text">Personal</div>
                            <div class="tipe-label-sub">Surat milik karyawan tertentu</div>
                        </div>
                    </label>
                </div>
                <div class="tipe-option">
                    <input type="radio" name="tipe" id="tipeUmum" value="umum"
                        {{ old('tipe') === 'umum' ? 'checked' : '' }}
                        onchange="onTipeChange(this.value)">
                    <label class="tipe-label" for="tipeUmum">
                        <div class="tipe-label-icon" style="background:#fff7ed">📋</div>
                        <div>
                            <div class="tipe-label-text">Umum / Pedoman</div>
                            <div class="tipe-label-sub">Pedoman, SOP, kebijakan perusahaan</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Info Surat --}}
        <div class="form-card">
            <div class="section-header">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <div class="section-title">Informasi Surat</div>
                    <div class="section-sub">Detail dokumen yang akan diupload</div>
                </div>
            </div>

            <div class="form-grid">
                {{-- Karyawan (hanya muncul jika tipe Personal) --}}
                <div class="form-group full" id="fieldKaryawan" style="{{ old('tipe','personal')==='umum' ? 'display:none' : '' }}">
                    <label class="form-label">Karyawan <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="karyawan_id" id="inputKaryawan" class="form-input {{ $errors->has('karyawan_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id }}" {{ old('karyawan_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }} — NIK {{ $k->nik }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('karyawan_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Judul --}}
                <div class="form-group full">
                    <label class="form-label">Judul <span class="req">*</span></label>
                    <input type="text" name="judul" value="{{ old('judul') }}"
                           class="form-input {{ $errors->has('judul') ? 'error-input' : '' }}"
                           id="inputJudul"
                           placeholder="cth: SK Pengangkatan / Pedoman Rekrutmen" />
                    @error('judul')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Nomor Surat --}}
                <div class="form-group">
                    <label class="form-label">Nomor Surat</label>
                    <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}"
                           class="form-input" placeholder="cth: 001/SK/HRD/2025" />
                </div>

                {{-- Kategori --}}
                <div class="form-group">
                    <label class="form-label">Kategori <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="kategori" id="inputKategori" class="form-input {{ $errors->has('kategori') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Kategori --</option>
                            <optgroup label="Surat Karyawan" id="groupPersonal">
                                <option value="sk_jabatan"       {{ old('kategori')=='sk_jabatan' ? 'selected' : '' }}>SK Jabatan</option>
                                <option value="sk_promosi"       {{ old('kategori')=='sk_promosi' ? 'selected' : '' }}>SK Promosi</option>
                                <option value="sk_mutasi"        {{ old('kategori')=='sk_mutasi' ? 'selected' : '' }}>SK Mutasi</option>
                                <option value="sk_pensiun"       {{ old('kategori')=='sk_pensiun' ? 'selected' : '' }}>SK Pensiun</option>
                                <option value="surat_tugas"      {{ old('kategori')=='surat_tugas' ? 'selected' : '' }}>Surat Tugas</option>
                                <option value="surat_peringatan" {{ old('kategori')=='surat_peringatan' ? 'selected' : '' }}>Surat Peringatan</option>
                                <option value="kontrak"          {{ old('kategori')=='kontrak' ? 'selected' : '' }}>Kontrak</option>
                                <option value="sertifikat"       {{ old('kategori')=='sertifikat' ? 'selected' : '' }}>Sertifikat</option>
                            </optgroup>
                            <optgroup label="Dokumen Umum" id="groupUmum">
                                <option value="pedoman"   {{ old('kategori')=='pedoman' ? 'selected' : '' }}>Pedoman</option>
                                <option value="prosedur"  {{ old('kategori')=='prosedur' ? 'selected' : '' }}>Prosedur / SOP</option>
                                <option value="kebijakan" {{ old('kategori')=='kebijakan' ? 'selected' : '' }}>Kebijakan</option>
                            </optgroup>
                            <option value="lainnya" {{ old('kategori')=='lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    @error('kategori')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Tanggal Surat --}}
                <div class="form-group">
                    <label class="form-label">Tanggal Surat <span class="req">*</span></label>
                    <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}"
                           class="form-input {{ $errors->has('tanggal_surat') ? 'error-input' : '' }}" />
                    @error('tanggal_surat')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Tanggal Expired --}}
                <div class="form-group">
                    <label class="form-label">Berlaku Hingga</label>
                    <input type="date" name="tanggal_exp" value="{{ old('tanggal_exp') }}"
                           class="form-input" />
                    <span class="form-hint">Kosongkan jika tidak ada masa berlaku</span>
                </div>

                {{-- Keterangan --}}
                <div class="form-group full">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="form-input" style="resize:vertical;"
                              placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Upload File --}}
        <div class="form-card">
            <div class="section-header">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div>
                    <div class="section-title">Upload File</div>
                    <div class="section-sub">PDF, JPG, JPEG, PNG — maksimal 10MB</div>
                </div>
            </div>

            <div class="file-upload-area" id="dropZone">
                <input type="file" name="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png"
                       onchange="previewFile(this)" />
                <div class="file-upload-icon">📁</div>
                <div class="file-upload-text"><strong>Klik untuk upload</strong> atau drag & drop</div>
                <div class="file-upload-hint">PDF, JPG, JPEG, PNG (maks. 10MB)</div>
            </div>

            <div class="file-preview" id="filePreview">
                <span class="file-preview-icon" id="previewIcon">📄</span>
                <span class="file-preview-name" id="previewName">-</span>
                <span class="file-preview-size" id="previewSize">-</span>
                <button type="button" class="file-preview-remove" onclick="removeFile()">✕</button>
            </div>
            @error('file')<div class="error-msg" style="margin-top:8px">{{ $message }}</div>@enderror
        </div>

        {{-- Actions --}}
        <div class="form-actions-card">
            <div style="font-size:12px;color:#9ca3af;"><span style="color:#ef4444">*</span> Wajib diisi</div>
            <div class="form-actions-right">
                <a href="{{ route('surat_penting.index') }}" class="btn-cancel">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Batal
                </a>
                <button type="submit" class="btn-save">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload Surat
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function onTipeChange(tipe) {
    const fieldKaryawan = document.getElementById('fieldKaryawan');
    const inputKaryawan = document.getElementById('inputKaryawan');
    if (tipe === 'umum') {
        fieldKaryawan.style.display = 'none';
        inputKaryawan.value = '';
    } else {
        fieldKaryawan.style.display = '';
    }
}

// Init on load
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name=tipe]:checked');
    if (checked) onTipeChange(checked.value);
});

function previewFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const ext  = file.name.split('.').pop().toLowerCase();
        const icon = ext === 'pdf' ? '📄' : ['jpg','jpeg','png'].includes(ext) ? '🖼️' : '📎';
        const size = file.size >= 1048576
            ? (file.size / 1048576).toFixed(1) + ' MB'
            : (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('previewIcon').textContent = icon;
        document.getElementById('previewName').textContent = file.name;
        document.getElementById('previewSize').textContent = size;
        document.getElementById('filePreview').classList.add('show');
    }
}

function removeFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').classList.remove('show');
}

const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('dragover'); });
dropZone.addEventListener('drop', e => {
    e.preventDefault(); dropZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length) { document.getElementById('fileInput').files = files; previewFile(document.getElementById('fileInput')); }
});
</script>
@endpush