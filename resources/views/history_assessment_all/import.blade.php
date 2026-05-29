@extends('layouts.app')
@section('title', 'Import Assessment')
@section('breadcrumb-parent', 'History Assessment')
@section('breadcrumb', 'Import Excel')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#7c3aed; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-title { font-size:20px;font-weight:700;color:#111827;margin-bottom:4px; }
    .page-sub { font-size:13px;color:#6b7280;margin-bottom:20px; }

    /* TYPE SELECTOR */
    .type-selector { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px; }
    .type-card { background:white;border:2px solid #e5e7eb;border-radius:14px;padding:18px 20px;cursor:pointer;transition:all 0.15s;position:relative;overflow:hidden; }
    .type-card:hover { border-color:#c4b5fd; }
    .type-card.selected { border-color:#7c3aed;background:#faf5ff; }
    .type-card.selected::after { content:'✓';position:absolute;top:12px;right:14px;width:22px;height:22px;background:#7c3aed;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700; }
    .type-card input[type=radio] { display:none; }
    .type-icon { font-size:28px;margin-bottom:8px; }
    .type-title { font-size:14px;font-weight:700;color:#111827;margin-bottom:4px; }
    .type-desc { font-size:12px;color:#6b7280;line-height:1.5; }
    .type-badge { display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-top:6px; }
    .type-badge.rekom { background:#f3e8ff;color:#7c3aed; }
    .type-badge.komp  { background:#f0fdf4;color:#15803d; }

    .step-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:24px;margin-bottom:16px; }
    .step-header { display:flex;align-items:center;gap:12px;margin-bottom:16px; }
    .step-num { width:30px;height:30px;border-radius:50%;background:#7c3aed;color:white;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0; }
    .step-title { font-size:14px;font-weight:700;color:#111827; }
    .step-sub { font-size:11px;color:#9ca3af;margin-top:1px; }

    .template-box { background:#f5f3ff;border:1px solid #ddd6fe;border-radius:12px;padding:16px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
    .template-info { display:flex;align-items:center;gap:10px; }
    .template-name { font-size:13px;font-weight:700;color:#111827; }
    .template-desc { font-size:11px;color:#6b7280;margin-top:2px; }
    .btn-download-tpl { display:inline-flex;align-items:center;gap:6px;background:#7c3aed;color:white;padding:9px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:background 0.15s; }
    .btn-download-tpl:hover { background:#6d28d9; }

    .kolom-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:12px; }
    .kolom-item { background:#f9fafb;border-radius:8px;padding:9px 11px;border:1px solid #f3f4f6; }
    .kolom-name { font-size:11px;font-weight:700;color:#374151;font-family:monospace; }
    .kolom-desc { font-size:10px;color:#9ca3af;margin-top:2px; }
    .kolom-req { font-size:9px;font-weight:700;color:#ef4444; }

    .integrated-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 14px;margin-top:12px; }
    .integrated-title { font-size:11px;font-weight:700;color:#15803d;margin-bottom:7px; }
    .integrated-list { display:grid;grid-template-columns:1fr 1fr;gap:5px; }
    .integrated-item { font-size:11px;color:#374151; }

    .upload-area { border:2px dashed #ddd6fe;border-radius:12px;padding:36px;text-align:center;cursor:pointer;transition:all 0.15s;background:#fafafa;position:relative; }
    .upload-area:hover, .upload-area.dragover { border-color:#7c3aed;background:#f5f3ff; }
    .upload-area input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
    .upload-text { font-size:14px;color:#374151;font-weight:600;margin-bottom:4px; }
    .upload-text strong { color:#7c3aed; }
    .upload-hint { font-size:12px;color:#9ca3af; }

    .file-preview { display:none;align-items:center;gap:12px;padding:12px 14px;background:#f5f3ff;border:1px solid #ddd6fe;border-radius:10px;margin-top:12px; }
    .file-preview.show { display:flex; }
    .file-preview-name { font-size:13px;font-weight:600;color:#111827;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .file-preview-size { font-size:12px;color:#6b7280;flex-shrink:0; }
    .file-preview-remove { width:22px;height:22px;border-radius:50%;border:none;background:#fee2e2;color:#ef4444;cursor:pointer;font-size:13px;flex-shrink:0; }

    .error-msg { font-size:11px;color:#ef4444;margin-top:4px; }

    .warning-box { background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 14px;display:flex;gap:10px;margin-top:14px; }
    .warning-box svg { width:16px;height:16px;stroke:#d97706;fill:none;flex-shrink:0;margin-top:1px; }
    .warning-text { font-size:12px;color:#92400e;line-height:1.6; }

    .form-actions { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:16px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:6px;background:white;color:#374151;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all 0.15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-import { display:inline-flex;align-items:center;gap:6px;background:#7c3aed;color:white;padding:9px 22px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all 0.15s; }
    .btn-import:hover { background:#6d28d9; }
    .btn-import:disabled { opacity:0.55;cursor:not-allowed; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border-left:4px solid #ef4444;border:1px solid #fecaca;border-radius:12px;padding:12px 14px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#dc2626;font-weight:500;min-width:260px;pointer-events:all;animation:toastIn 0.35s forwards; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }

    @media (max-width:640px) {
        .type-selector { grid-template-columns:1fr; }
        .kolom-grid { grid-template-columns:1fr 1fr; }
        .integrated-list { grid-template-columns:1fr; }
        .template-box { flex-direction:column;align-items:flex-start; }
    }
</style>
@endpush

@section('content')

@if(session('error'))
<div class="toast-wrap">
    <div class="toast">
        <div>⚠️ {{ session('error') }}</div>
        <button class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
    </div>
</div>
@endif

<a href="{{ route('history_assessment_all.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke History Assessment
</a>

<div class="page-title">📥 Import Data Assessment</div>
<div class="page-sub">Pilih jenis assessment yang ingin diimport, lalu upload file Excel</div>

{{-- ===== STEP 0: PILIH JENIS ===== --}}
<div class="step-card">
    <div class="step-header" style="margin-bottom:12px;">
        <div class="step-num">1</div>
        <div>
            <div class="step-title">Pilih Jenis Assessment</div>
            <div class="step-sub">Tentukan jenis data yang akan diimport</div>
        </div>
    </div>
    <div class="type-selector">
        <label class="type-card selected" id="cardRekom" onclick="selectType('rekom')">
            <input type="radio" name="jenis" value="rekom" checked>
            <div class="type-icon">📋</div>
            <div class="type-title">Assessment Rekomendasi</div>
            <div class="type-desc">Data hasil penilaian kesiapan karyawan: Ready, Ready with Development, atau Not Ready.</div>
            <span class="type-badge rekom">Rek. Inti · Primer · Sekunder · Final</span>
        </label>
        <label class="type-card" id="cardKomp" onclick="selectType('komp')">
            <input type="radio" name="jenis" value="komp">
            <div class="type-icon">⭐</div>
            <div class="type-title">Assessment Kompetensi</div>
            <div class="type-desc">Data penilaian kompetensi perilaku dan professional qualification karyawan (nilai 1–4).</div>
            <span class="type-badge komp">Kompetensi Perilaku · Qualification</span>
        </label>
    </div>
</div>

{{-- ===== STEP 2: TEMPLATE ===== --}}
<div class="step-card">
    <div class="step-header">
        <div class="step-num">2</div>
        <div>
            <div class="step-title">Download Template Excel</div>
            <div class="step-sub">Gunakan template sebagai panduan format data</div>
        </div>
    </div>

    {{-- Template REKOMENDASI --}}
    <div id="tplRekom">
        <div class="template-box">
            <div class="template-info">
                <span style="font-size:28px;">📊</span>
                <div>
                    <div class="template-name">template-import-assessment-rekomendasi.xlsx</div>
                    <div class="template-desc">Template dengan contoh data Assessment Rekomendasi</div>
                </div>
            </div>
            <button type="button" class="btn-download-tpl" onclick="triggerDownload('{{ route('history_assessment_all.import.template') }}')">
                ⬇ Download Template
            </button>
        </div>

        <div style="margin-top:14px;">
            <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:8px;">📋 Kolom yang perlu diisi:</div>
            <div class="kolom-grid">
                <div class="kolom-item">
                    <div class="kolom-name">nik <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">NIK karyawan terdaftar</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">tanggal_pelaksanaan <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">Format: dd/mm/yyyy</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">rekomendasi_final <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">ready / ready_with_development / not_ready</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">rekomendasi_inti</div>
                    <div class="kolom-desc">Angka 0–100 (persen)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">rekomendasi_primer</div>
                    <div class="kolom-desc">Angka 0–100 (persen)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">rekomendasi_skunder</div>
                    <div class="kolom-desc">Angka 0–100 (persen)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">job_stream</div>
                    <div class="kolom-desc">cth: Technical, Managerial</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">tingkat_pengukuran</div>
                    <div class="kolom-desc">cth: Level 1, Level 2</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">keterangan</div>
                    <div class="kolom-desc">Catatan tambahan (opsional)</div>
                </div>
            </div>
        </div>

        <div class="integrated-box">
            <div class="integrated-title">✅ Diambil OTOMATIS dari profil karyawan:</div>
            <div class="integrated-list">
                <div class="integrated-item">✓ Jabatan Saat Ini</div>
                <div class="integrated-item">✓ Job Grade & Person Grade</div>
                <div class="integrated-item">✓ Jenis Kelamin & Usia</div>
                <div class="integrated-item">✓ Tgl Exp IDP (+2 tahun otomatis)</div>
            </div>
        </div>
    </div>

    {{-- Template KOMPETENSI --}}
    <div id="tplKomp" style="display:none;">
        <div class="template-box">
            <div class="template-info">
                <span style="font-size:28px;">📊</span>
                <div>
                    <div class="template-name">template-import-assessment-kompetensi.xlsx</div>
                    <div class="template-desc">Template dengan semua kolom Kompetensi Perilaku dan Qualification</div>
                </div>
            </div>
            <button type="button" class="btn-download-tpl" onclick="triggerDownload('{{ route('history_assessment_all.import.template.kompetensi') }}')">
                ⬇ Download Template
            </button>
        </div>

        <div style="margin-top:14px;">
            <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:8px;">📋 Kolom yang perlu diisi:</div>
            <div class="kolom-grid">
                <div class="kolom-item">
                    <div class="kolom-name">nik <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">NIK karyawan terdaftar</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">tanggal_assessment <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">Format: dd/mm/yyyy</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">periode</div>
                    <div class="kolom-desc">cth: 2024, Q1-2024</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">[kompetensi_*] <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">Nilai 1–4 per kompetensi (lihat template)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">[qualification_*] <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">Nilai 1–4 per qualification (lihat template)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">keterangan</div>
                    <div class="kolom-desc">Catatan tambahan (opsional)</div>
                </div>
            </div>
        </div>

        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 14px;margin-top:12px;font-size:12px;color:#92400e;">
            <strong>📌 Catatan Kompetensi:</strong> Kesimpulan (QUALIFIED / NOT QUALIFIED) dihitung <strong>otomatis</strong> oleh sistem berdasarkan kriteria:
            <ul style="margin:6px 0 0 16px;line-height:1.8;">
                <li>Tidak ada kompetensi dengan rating <strong>1</strong></li>
                <li>Kompetensi rating <strong>2</strong> maksimal <strong>3 item</strong></li>
                <li>Semua qualification minimal nilai <strong>2</strong></li>
            </ul>
        </div>
    </div>
</div>

{{-- ===== STEP 3: UPLOAD ===== --}}
<div class="step-card">
    <div class="step-header">
        <div class="step-num">3</div>
        <div>
            <div class="step-title">Upload File Excel</div>
            <div class="step-sub">Pilih file yang sudah diisi sesuai template</div>
        </div>
    </div>

    <form method="POST" action="{{ route('history_assessment_all.import.store') }}"
          enctype="multipart/form-data" id="importForm">
        @csrf
        <input type="hidden" name="jenis" id="inputJenis" value="rekom">

        <div class="upload-area" id="dropZone">
            <input type="file" name="file" id="fileInput"
                   accept=".xlsx,.xls,.csv"
                   onchange="previewFile(this)" />
            <div style="font-size:40px;margin-bottom:10px;">📂</div>
            <div class="upload-text"><strong>Klik untuk pilih file</strong> atau drag & drop</div>
            <div class="upload-hint">Format: .xlsx, .xls, .csv — Maks. 10MB</div>
        </div>

        <div class="file-preview" id="filePreview">
            <span style="font-size:20px;">📊</span>
            <span class="file-preview-name" id="previewName">-</span>
            <span class="file-preview-size" id="previewSize">-</span>
            <button type="button" class="file-preview-remove" onclick="removeFile()">✕</button>
        </div>

        @error('file')
        <div class="error-msg">{{ $message }}</div>
        @enderror

        <div class="warning-box">
            <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div class="warning-text" id="warningText">
                <strong>Perhatian:</strong> NIK yang tidak ditemukan di sistem akan dilewati otomatis. Pastikan format kolom sesuai template. Nilai rekomendasi dalam bentuk angka <strong>0-100</strong> (tanpa tanda %).
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('history_assessment_all.index') }}" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-import" id="btnImport" disabled>
                📥 Import Assessment Rekomendasi
            </button>
        </div>
    </form>
</div>

{{-- Step 4: Info --}}
<div class="step-card" style="background:#f9fafb;">
    <div class="step-header" style="margin-bottom:10px;">
        <div class="step-num" style="background:#6b7280;">4</div>
        <div>
            <div class="step-title">Setelah Import</div>
            <div class="step-sub">Proses yang terjadi setelah berhasil import</div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <div style="font-size:12px;color:#6b7280;"><span style="color:#15803d;font-weight:700;">✓</span> Data langsung masuk ke sistem</div>
        <div style="font-size:12px;color:#6b7280;"><span style="color:#15803d;font-weight:700;">✓</span> NIK tidak ditemukan dilewati (skip)</div>
        <div id="info1" style="font-size:12px;color:#6b7280;"><span style="color:#15803d;font-weight:700;">✓</span> Jabatan, job grade, usia otomatis dari profil</div>
        <div id="info2" style="font-size:12px;color:#6b7280;"><span style="color:#15803d;font-weight:700;">✓</span> Tgl Exp IDP otomatis +2 tahun</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var currentType = 'rekom';

function selectType(type) {
    currentType = type;
    document.getElementById('inputJenis').value = type;

    // Update card UI
    document.getElementById('cardRekom').classList.toggle('selected', type === 'rekom');
    document.getElementById('cardKomp').classList.toggle('selected',  type === 'komp');

    // Toggle template info
    document.getElementById('tplRekom').style.display = type === 'rekom' ? 'block' : 'none';
    document.getElementById('tplKomp').style.display  = type === 'komp'  ? 'block' : 'none';

    // Update form action
    var routeRekom = '{{ route("history_assessment_all.import.store") }}';
    var routeKomp  = '{{ route("history_assessment_all.import.store.kompetensi") }}';
    document.getElementById('importForm').action = type === 'rekom' ? routeRekom : routeKomp;

    // Update tombol import
    var btnText = type === 'rekom'
        ? '📥 Import Assessment Rekomendasi'
        : '📥 Import Assessment Kompetensi';
    var btn = document.getElementById('btnImport');
    btn.innerHTML = btnText;
    btn.disabled = !document.getElementById('fileInput').files.length;

    // Update warning
    if (type === 'komp') {
        document.getElementById('warningText').innerHTML =
            '<strong>Perhatian:</strong> NIK tidak ditemukan akan dilewati. Nilai kompetensi harus <strong>1–4</strong> (integer). Kesimpulan QUALIFIED/NOT QUALIFIED dihitung otomatis oleh sistem.';
        document.getElementById('info1').innerHTML = '<span style="color:#15803d;font-weight:700;">✓</span> Kesimpulan dihitung otomatis (QUALIFIED/NOT QUALIFIED)';
        document.getElementById('info2').innerHTML = '<span style="color:#15803d;font-weight:700;">✓</span> Under Competency & Qualification dihitung otomatis';
    } else {
        document.getElementById('warningText').innerHTML =
            '<strong>Perhatian:</strong> NIK yang tidak ditemukan di sistem akan dilewati otomatis. Nilai rekomendasi dalam bentuk angka <strong>0-100</strong> (tanpa tanda %).';
        document.getElementById('info1').innerHTML = '<span style="color:#15803d;font-weight:700;">✓</span> Jabatan, job grade, usia otomatis dari profil';
        document.getElementById('info2').innerHTML = '<span style="color:#15803d;font-weight:700;">✓</span> Tgl Exp IDP otomatis +2 tahun';
    }

    // Reset file
    removeFile();
}

function previewFile(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var size = file.size >= 1048576
            ? (file.size/1048576).toFixed(1) + ' MB'
            : (file.size/1024).toFixed(1) + ' KB';
        document.getElementById('previewName').textContent = file.name;
        document.getElementById('previewSize').textContent = size;
        document.getElementById('filePreview').classList.add('show');
        document.getElementById('btnImport').disabled = false;
    }
}

function removeFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').classList.remove('show');
    document.getElementById('btnImport').disabled = true;
}

// Drag & drop
var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', function() { dropZone.classList.remove('dragover'); });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        document.getElementById('fileInput').files = e.dataTransfer.files;
        previewFile(document.getElementById('fileInput'));
    }
});

// Submit loading
document.getElementById('importForm').addEventListener('submit', function() {
    var btn = document.getElementById('btnImport');
    btn.disabled = true;
    btn.innerHTML = '⏳ Mengimport...';
});

// ===== DOWNLOAD TEMPLATE (tanpa trigger spinner halaman) =====
function triggerDownload(url) {
    // Tampil toast download
    var toast = document.getElementById('downloadToast');
    toast.style.display = 'flex';

    // Trigger lewat iframe tersembunyi — halaman tidak navigasi
    var iframe = document.getElementById('downloadFrame');
    iframe.src = url;

    // Sembunyikan toast setelah 4 detik
    setTimeout(function() {
        toast.style.opacity = '0';
        setTimeout(function() {
            toast.style.display = 'none';
            toast.style.opacity = '1';
            iframe.src = 'about:blank';
        }, 400);
    }, 4000);
}
</script>

{{-- Hidden iframe untuk download tanpa navigasi --}}
<iframe id="downloadFrame" src="about:blank" style="display:none;"></iframe>

{{-- Toast notif download --}}
<div id="downloadToast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;
    background:white;border:1px solid #ddd6fe;border-left:4px solid #7c3aed;border-radius:12px;
    padding:12px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);
    align-items:center;gap:10px;font-size:13px;font-weight:500;color:#5b21b6;
    min-width:240px;transition:opacity 0.4s;">
    <div style="width:20px;height:20px;border:2px solid #ddd6fe;border-top-color:#7c3aed;border-radius:50%;
        animation:spin 0.7s linear infinite;flex-shrink:0;"></div>
    <span>Menyiapkan file Excel...</span>
</div>

@endpush