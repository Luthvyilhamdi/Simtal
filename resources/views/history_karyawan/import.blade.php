@extends('layouts.app')
@section('title', 'Import History Jabatan')
@section('breadcrumb-parent', 'History Karyawan')
@section('breadcrumb', 'Import Excel')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .import-wrap { max-width:100%; }
    .page-title { font-size:20px;font-weight:700;color:#111827;margin-bottom:4px; }
    .page-sub { font-size:13px;color:#6b7280;margin-bottom:24px; }

    .step-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:24px;margin-bottom:16px; }
    .step-header { display:flex;align-items:center;gap:12px;margin-bottom:16px; }
    .step-num { width:32px;height:32px;border-radius:50%;background:#15803d;color:white;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0; }
    .step-title { font-size:15px;font-weight:700;color:#111827; }
    .step-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .template-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:18px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; }
    .template-info { display:flex;align-items:center;gap:12px; }
    .template-name { font-size:14px;font-weight:700;color:#111827; }
    .template-desc { font-size:12px;color:#6b7280;margin-top:2px; }
    .btn-download-tpl { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;white-space:nowrap;transition:background 0.15s;font-family:inherit; }
    .btn-download-tpl:hover { background:#166534; }

    .kolom-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px; }
    .kolom-item { background:#f9fafb;border-radius:8px;padding:10px 12px;border:1px solid #f3f4f6; }
    .kolom-name { font-size:12px;font-weight:700;color:#374151;font-family:monospace; }
    .kolom-desc { font-size:11px;color:#9ca3af;margin-top:2px; }
    .kolom-req { font-size:10px;font-weight:700;color:#ef4444; }

    .example-box { background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-top:12px; }
    .example-title { font-size:12px;font-weight:700;color:#d97706;margin-bottom:8px; }
    .example-table { width:100%;border-collapse:collapse;font-size:11px; }
    .example-table th { padding:6px 10px;background:#fef3c7;color:#92400e;font-weight:700;text-align:left;border:1px solid #fde68a; }
    .example-table td { padding:6px 10px;color:#374151;border:1px solid #fde68a; }
    .example-table tr:nth-child(even) td { background:#fffbeb; }
    .highlight-row td { background:#f0fdf4 !important;color:#15803d;font-weight:600; }

    .upload-area { border:2px dashed #d1d5db;border-radius:12px;padding:40px;text-align:center;cursor:pointer;transition:all 0.15s;background:#fafafa;position:relative; }
    .upload-area:hover { border-color:#16a34a;background:#f0fdf4; }
    .upload-area.dragover { border-color:#16a34a;background:#f0fdf4; }
    .upload-area input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
    .upload-icon { font-size:44px;margin-bottom:12px; }
    .upload-text { font-size:15px;color:#374151;font-weight:600;margin-bottom:4px; }
    .upload-text strong { color:#15803d; }
    .upload-hint { font-size:12px;color:#9ca3af; }

    .file-preview { display:none;align-items:center;gap:12px;padding:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;margin-top:12px; }
    .file-preview.show { display:flex; }
    .file-preview-name { font-size:13px;font-weight:600;color:#111827;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .file-preview-size { font-size:12px;color:#6b7280;flex-shrink:0; }
    .file-preview-remove { width:24px;height:24px;border-radius:50%;border:none;background:#fee2e2;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0; }

    .error-msg { font-size:11px;color:#ef4444;margin-top:4px; }

    .warning-box { background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;display:flex;gap:10px;margin-top:16px; }
    .warning-box svg { width:18px;height:18px;stroke:#d97706;fill:none;flex-shrink:0;margin-top:1px; }
    .warning-text { font-size:12px;color:#92400e;line-height:1.6; }

    .form-actions { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:16px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none; }
    .btn-import { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all 0.15s; }
    .btn-import:hover { background:#166534; }
    .btn-import:disabled { opacity:0.6;cursor:not-allowed; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border-left:4px solid #ef4444;border:1px solid #fecaca;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#dc2626;font-weight:500;min-width:280px;pointer-events:all;animation:toastIn 0.35s forwards; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes spin { to{transform:rotate(360deg)} }

    @media (max-width:640px) {
        .kolom-grid { grid-template-columns:1fr 1fr; }
        .template-box { flex-direction:column;align-items:flex-start; }
        .example-table { font-size:10px; }
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

<div class="import-wrap">
    <a href="{{ route('history_karyawan.index') }}" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke History Karyawan
    </a>

    <div class="page-title">📥 Import History Jabatan</div>
    <div class="page-sub">Upload file Excel untuk menambahkan history jabatan karyawan secara massal — 1 karyawan bisa punya banyak baris</div>

    {{-- Step 1 --}}
    <div class="step-card">
        <div class="step-header">
            <div class="step-num">1</div>
            <div>
                <div class="step-title">Download Template Excel</div>
                <div class="step-sub">Gunakan template ini sebagai panduan format data</div>
            </div>
        </div>

        <div class="template-box">
            <div class="template-info">
                <span style="font-size:32px;">📊</span>
                <div>
                    <div class="template-name">template-import-history-jabatan.xlsx</div>
                    <div class="template-desc">Template dengan contoh 1 karyawan 5 jabatan</div>
                </div>
            </div>
            <button type="button" class="btn-download-tpl" data-url="{{ route('history_karyawan.import.template') }}" onclick="triggerDownload(this.dataset.url)">
                ⬇ Download Template
            </button>
        </div>

        {{-- Panduan Kolom --}}
        <div style="margin-top:16px;">
            <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">📋 Panduan Kolom:</div>
            <div class="kolom-grid">
                <div class="kolom-item">
                    <div class="kolom-name">nik <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">NIK karyawan (harus sudah ada di sistem)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">jabatan <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">Nama jabatan (auto-create jika belum ada)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">jabatan_saat_ini</div>
                    <div class="kolom-desc">Jabatan lengkap/spesifik</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">direktorat</div>
                    <div class="kolom-desc">Nama direktorat (auto-create)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">kompartemen</div>
                    <div class="kolom-desc">Nama kompartemen (auto-create)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">departemen</div>
                    <div class="kolom-desc">Nama departemen (auto-create)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">job_grade</div>
                    <div class="kolom-desc">Job grade (auto-create)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">person_grade</div>
                    <div class="kolom-desc">Person grade (auto-create)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">kode_struktur</div>
                    <div class="kolom-desc">Kode struktur (opsional)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">tipe <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">onboarding / promosi / mutasi / rotasi / demosi</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">tanggal_mulai <span class="kolom-req">*wajib</span></div>
                    <div class="kolom-desc">Format: dd/mm/yyyy</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">tanggal_selesai</div>
                    <div class="kolom-desc">Kosongkan jika jabatan saat ini</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">no_sk</div>
                    <div class="kolom-desc">Nomor SK (opsional)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">tanggal_sk</div>
                    <div class="kolom-desc">Format: dd/mm/yyyy (opsional)</div>
                </div>
                <div class="kolom-item">
                    <div class="kolom-name">keterangan</div>
                    <div class="kolom-desc">Catatan tambahan (opsional)</div>
                </div>
            </div>
        </div>

        {{-- Contoh format --}}
        <div class="example-box">
            <div class="example-title">💡 Contoh: 1 Karyawan NIK 10001 dengan 3 Jabatan</div>
            <div style="overflow-x:auto;">
                <table class="example-table">
                    <thead>
                        <tr>
                            <th>nik</th>
                            <th>jabatan</th>
                            <th>tipe</th>
                            <th>tanggal_mulai</th>
                            <th>tanggal_selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>10001</td><td>Staff</td><td>onboarding</td><td>01/01/2015</td><td>31/12/2018</td>
                        </tr>
                        <tr>
                            <td>10001</td><td>Senior Staff</td><td>promosi</td><td>01/01/2019</td><td>31/12/2021</td>
                        </tr>
                        <tr class="highlight-row">
                            <td>10001</td><td>Manager</td><td>promosi</td><td>01/01/2022</td><td><em>kosong = jabatan aktif</em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="font-size:11px;color:#92400e;margin-top:8px;">
                ⚠ Baris dengan <span style="color:#15803d;font-weight:700;">tanggal_selesai kosong</span> akan otomatis menjadi jabatan aktif dan memperbarui profil karyawan.
            </div>
        </div>
    </div>

    {{-- Step 2 --}}
    <div class="step-card">
        <div class="step-header">
            <div class="step-num">2</div>
            <div>
                <div class="step-title">Upload File Excel</div>
                <div class="step-sub">Pilih file yang sudah diisi sesuai template</div>
            </div>
        </div>

        <form method="POST" action="{{ route('history_karyawan.import.store') }}" enctype="multipart/form-data" id="importForm">
            @csrf

            <div class="upload-area" id="dropZone">
                <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" onchange="previewFile(this)" />
                <div class="upload-icon">📂</div>
                <div class="upload-text"><strong>Klik untuk pilih file</strong> atau drag & drop</div>
                <div class="upload-hint">Format: .xlsx, .xls, .csv — Maks. 10MB</div>
            </div>

            <div class="file-preview" id="filePreview">
                <span style="font-size:24px;">📊</span>
                <span class="file-preview-name" id="previewName">-</span>
                <span class="file-preview-size" id="previewSize">-</span>
                <button type="button" class="file-preview-remove" onclick="removeFile()">✕</button>
            </div>

            @error('file')<div class="error-msg">{{ $message }}</div>@enderror

            <div class="warning-box">
                <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div class="warning-text">
                    <strong>Perhatian:</strong> NIK yang tidak ditemukan akan dilewati otomatis. Baris dengan <strong>tanggal_selesai kosong</strong> akan jadi jabatan aktif dan memperbarui profil karyawan. Urutkan data dari jabatan terlama ke terbaru agar riwayat terurut dengan benar.
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('history_karyawan.index') }}" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-import" id="btnImport" disabled>
                    📥 Import Sekarang
                </button>
            </div>
        </form>
    </div>

    {{-- Step 3 --}}
    <div class="step-card" style="background:#f9fafb;">
        <div class="step-header" style="margin-bottom:10px;">
            <div class="step-num" style="background:#6b7280;">3</div>
            <div>
                <div class="step-title">Setelah Import</div>
                <div class="step-sub">Yang akan terjadi setelah berhasil import</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#6b7280;">
                <span style="color:#15803d;font-weight:700;">✓</span>
                History jabatan langsung masuk ke sistem
            </div>
            <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#6b7280;">
                <span style="color:#15803d;font-weight:700;">✓</span>
                Master data baru auto-create jika belum ada
            </div>
            <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#6b7280;">
                <span style="color:#15803d;font-weight:700;">✓</span>
                Profil karyawan diperbarui dari jabatan aktif
            </div>
            <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#6b7280;">
                <span style="color:#f59e0b;font-weight:700;">⚠</span>
                NIK tidak ditemukan akan dilewati (skip)
            </div>
            <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#6b7280;">
                <span style="color:#15803d;font-weight:700;">✓</span>
                Jabatan SVP/VP/SPM/PM otomatis masuk History Pejabat
            </div>
            <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#6b7280;">
                <span style="color:#6b7280;font-weight:700;">ℹ</span>
                Urutkan dari jabatan terlama ke terbaru
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function previewFile(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const size = file.size >= 1048576
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

    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length) {
            document.getElementById('fileInput').files = files;
            previewFile(document.getElementById('fileInput'));
        }
    });

    document.getElementById('importForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnImport');
        btn.disabled = true;
        btn.innerHTML = '⏳ Mengimport...';
    });

    // ===== DOWNLOAD TEMPLATE (tanpa trigger spinner halaman) =====
    function triggerDownload(url) {
        var toast = document.getElementById('downloadToast');
        toast.style.display = 'flex';
        document.getElementById('downloadFrame').src = url;
        setTimeout(function() {
            toast.style.opacity = '0';
            setTimeout(function() {
                toast.style.display = 'none';
                toast.style.opacity = '1';
                document.getElementById('downloadFrame').src = 'about:blank';
            }, 400);
        }, 4000);
    }
</script>

{{-- Hidden iframe untuk download tanpa navigasi --}}
<iframe id="downloadFrame" src="about:blank" style="display:none;"></iframe>

{{-- Toast notif download --}}
<div id="downloadToast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;
    background:white;border:1px solid #bbf7d0;border-left:4px solid #15803d;border-radius:12px;
    padding:12px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);
    align-items:center;gap:10px;font-size:13px;font-weight:500;color:#15803d;
    min-width:240px;transition:opacity 0.4s;">
    <div style="width:20px;height:20px;border:2px solid #bbf7d0;border-top-color:#15803d;border-radius:50%;
        animation:spin 0.7s linear infinite;flex-shrink:0;"></div>
    <span>Menyiapkan file Excel...</span>
</div>

@endpush