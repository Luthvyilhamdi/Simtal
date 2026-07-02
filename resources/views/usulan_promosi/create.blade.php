@extends('layouts.app')
@section('title', 'Buat Usulan Promosi')
@section('breadcrumb-parent', 'Usulan Promosi')
@section('breadcrumb', 'Buat Usulan')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .form-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:28px;margin-bottom:16px; }
    .section-header { display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f3f4f6; }
    .section-icon { width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .section-icon svg { width:16px;height:16px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .section-icon.blue { background:#eff6ff; }
    .section-icon.blue svg { stroke:#1d4ed8; }
    .section-icon.amber { background:#fffbeb; }
    .section-icon.amber svg { stroke:#d97706; }
    .section-icon.purple { background:#f5f3ff; }
    .section-icon.purple svg { stroke:#7c3aed; }
    .section-title { font-size:14px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px; }
    .req { color:#ef4444; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all .15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .form-input[readonly] { background:#f3f4f6;color:#374151;cursor:default; }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    /* Search karyawan */
    .search-karyawan-wrap { position:relative; }
    .search-karyawan-dropdown { position:absolute;top:100%;left:0;right:0;background:white;border:1.5px solid #e5e7eb;border-radius:9px;box-shadow:0 8px 24px rgba(0,0,0,.1);z-index:100;max-height:280px;overflow-y:auto;display:none; }
    .search-karyawan-dropdown.show { display:block; }
    .search-karyawan-item { padding:10px 14px;cursor:pointer;border-bottom:1px solid #f3f4f6;transition:background .1s; }
    .search-karyawan-item:last-child { border-bottom:none; }
    .search-karyawan-item:hover { background:#f0fdf4; }
    .ski-nama { font-size:13px;font-weight:600;color:#111827; }
    .ski-meta { font-size:11px;color:#9ca3af;margin-top:2px; }

    /* Karyawan info box */
    .karyawan-info-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;display:none; }
    .karyawan-info-box.show { display:block; }
    .ki-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px; }
    .ki-item { background:white;border-radius:8px;padding:10px 12px;border:1px solid #e5e7eb; }
    .ki-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.3px; }
    .ki-val { font-size:13px;font-weight:700;color:#111827;margin-top:3px; }
    .mdg-check { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px; }
    .mdg-ok { background:#dcfce7;color:#15803d; }
    .mdg-no { background:#fee2e2;color:#dc2626; }

    /* Assessment card */
    .assessment-card { border:1.5px solid #e5e7eb;border-radius:10px;padding:14px;cursor:pointer;transition:all .15s;background:#fafafa; }
    .assessment-card:hover { border-color:#15803d;background:#f0fdf4; }
    .assessment-card.selected { border-color:#15803d;background:#f0fdf4; }
    .assessment-card input { display:none; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .form-actions-right { display:flex;gap:10px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:white; }

    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
        .ki-grid { grid-template-columns:1fr 1fr; }
        .form-actions-card { flex-direction:column;align-items:stretch; }
        .form-actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('usulan_promosi.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Usulan Promosi
</a>

<div class="page-header">
    <div class="page-title">🏆 Buat Usulan Promosi</div>
    <div class="page-sub">Isi formulir usulan promosi karyawan</div>
</div>

<form method="POST" action="{{ route('usulan_promosi.store') }}" id="formUsulan">
    @csrf

    {{-- ===== KARYAWAN ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="section-title">Data Karyawan</div>
                <div class="section-sub">Cari karyawan berdasarkan NIK atau nama</div>
            </div>
        </div>

        <div class="form-group full">
            <label class="form-label">NIK / Nama Karyawan <span class="req">*</span></label>
            <div class="search-karyawan-wrap">
                <input type="text" id="searchKaryawan" class="form-input"
                    placeholder="Ketik NIK atau nama karyawan..." autocomplete="off">
                <div class="search-karyawan-dropdown" id="dropdownKaryawan"></div>
            </div>
            <input type="hidden" name="karyawan_id" id="karyawanId">
            @error('karyawan_id')<div class="error-msg">{{ $message }}</div>@enderror
        </div>

        {{-- Info karyawan terpilih --}}
        <div class="karyawan-info-box" id="karyawanInfoBox">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:40px;height:40px;border-radius:50%;background:#15803d;color:white;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0" id="karyawanInitial">—</div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#111827" id="karyawanNama">—</div>
                    <div style="font-size:12px;color:#6b7280" id="karyawanJabatan">—</div>
                </div>
            </div>
            <div class="ki-grid">
                <div class="ki-item">
                    <div class="ki-label">Job Grade</div>
                    <div class="ki-val" id="infoJG">-</div>
                </div>
                <div class="ki-item">
                    <div class="ki-label">Person Grade</div>
                    <div class="ki-val" id="infoPG">-</div>
                </div>
                <div class="ki-item">
                    <div class="ki-label">Band</div>
                    <div class="ki-val" id="infoBand">-</div>
                </div>
                <div class="ki-item">
                    <div class="ki-label">Struktural/Fungsional</div>
                    <div class="ki-val" id="infoStruk">-</div>
                </div>
                <div class="ki-item" style="grid-column:1/-1">
                    <div class="ki-label">Status MDG</div>
                    <div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap" id="infoMDG"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== USULAN PROMOSI ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon blue">
                <svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
            </div>
            <div>
                <div class="section-title">Usulan Promosi</div>
                <div class="section-sub">Jabatan, grade, dan unit yang diusulkan</div>
            </div>
        </div>

        <div class="form-grid">
            {{-- 1) JABATAN TUJUAN (teks isi) --}}
            <div class="form-group full">
                <label class="form-label">Jabatan Tujuan <span class="req">*</span></label>
                <input type="text" name="jabatan_tujuan" id="jabatanTujuan" value="{{ old('jabatan_tujuan') }}"
                    class="form-input {{ $errors->has('jabatan_tujuan') ? 'error-input' : '' }}"
                    placeholder="cth: Senior Officer Talenta Manajemen" autocomplete="off" />
                <div class="form-hint">Label jabatan yang akan tampil di riwayat & profil karyawan.</div>
                @error('jabatan_tujuan')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- 2) JABATAN MASTER (dropdown) --}}
            <div class="form-group full">
                <label class="form-label">Jabatan (Master) <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="jabatan_tujuan_id" id="jabatanMaster" class="form-input {{ $errors->has('jabatan_tujuan_id') ? 'error-input' : '' }}" required>
                        <option value="">— Pilih Jabatan Master —</option>
                        @foreach($jabatans as $jb)
                        <option value="{{ $jb->id }}" {{ old('jabatan_tujuan_id')==$jb->id ? 'selected' : '' }}>{{ $jb->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-hint">Untuk struktur & deteksi tingkat pejabat (SVP/VP/SPM/PM).</div>
                @error('jabatan_tujuan_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Job Grade Promosi</label>
                <input type="text" name="job_grade_promosi" value="{{ old('job_grade_promosi') }}"
                    class="form-input" placeholder="cth: 16" />
            </div>
            <div class="form-group">
                <label class="form-label">Person Grade Promosi</label>
                <input type="text" name="person_grade_promosi" value="{{ old('person_grade_promosi') }}"
                    class="form-input" placeholder="cth: 16" />
            </div>

            {{-- ==== UNIT TUJUAN ==== --}}
            <div class="form-group full">
                <label class="form-label">Direktorat Tujuan</label>
                <div class="select-wrap">
                    <select name="direktorat_tujuan_id" id="dirTujuan" class="form-input">
                        <option value="">— Pilih Direktorat —</option>
                        @foreach($direktorats as $d)
                        <option value="{{ $d->id }}" {{ old('direktorat_tujuan_id')==$d->id ? 'selected' : '' }}>{{ $d->nama_direktorat ?? $d->nama ?? ('#'.$d->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-hint">Default mengikuti unit karyawan. Ubah jika promosi memindahkan unit.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Kompartemen Tujuan</label>
                <div class="select-wrap">
                    <select name="kompartemen_tujuan_id" id="kompTujuan" class="form-input">
                        <option value="">— Pilih Kompartemen —</option>
                        @foreach($kompartemens as $kp)
                        <option value="{{ $kp->id }}" {{ old('kompartemen_tujuan_id')==$kp->id ? 'selected' : '' }}>{{ $kp->nama_kompartemen ?? ('#'.$kp->id) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Departemen Tujuan</label>
                <div class="select-wrap">
                    <select name="departemen_tujuan_id" id="deptTujuan" class="form-input">
                        <option value="">— Pilih Departemen —</option>
                        @foreach($departemens as $dp)
                        <option value="{{ $dp->id }}" {{ old('departemen_tujuan_id')==$dp->id ? 'selected' : '' }}>{{ $dp->nama_departemen ?? ('#'.$dp->id) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group full">
                <label class="form-label">Tanggal Usulan</label>
                <input type="date" name="tanggal_usulan" value="{{ old('tanggal_usulan', now()->format('Y-m-d')) }}"
                    class="form-input" />
            </div>
        </div>
    </div>

    {{-- ===== ASSESSMENT ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon purple">
                <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div>
                <div class="section-title">Assessment Rekomendasi</div>
                <div class="section-sub">Pilih assessment rekomendasi karyawan — opsional</div>
            </div>
        </div>

        <div id="assessmentList">
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">
                Pilih karyawan terlebih dahulu untuk menampilkan daftar assessment
            </div>
        </div>
        <input type="hidden" name="assessment_id" id="assessmentId" value="{{ old('assessment_id') }}">

        {{-- Preview assessment terpilih --}}
        <div id="assessmentPreview" style="display:none;margin-top:14px;background:#f9fafb;border-radius:10px;padding:14px 16px;border:1px solid #e5e7eb">
            <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Assessment Terpilih</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                <div>
                    <div style="font-size:10px;color:#9ca3af">Hasil</div>
                    <div style="font-size:13px;font-weight:700;color:#111827" id="prevHasil">-</div>
                </div>
                <div>
                    <div style="font-size:10px;color:#9ca3af">Berlaku s/d</div>
                    <div style="font-size:13px;font-weight:700;color:#111827" id="prevTglBerlaku">-</div>
                </div>
                <div>
                    <div style="font-size:10px;color:#9ca3af">Level Ukur</div>
                    <div style="font-size:13px;font-weight:700;color:#111827" id="prevLevelUkur">-</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TALENT POOL & KPI ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon amber">
                <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <div>
                <div class="section-title">Talent Pool, KPI & Kalibrasi</div>
                <div class="section-sub">Data diambil otomatis dari sistem</div>
            </div>
        </div>

        <div id="talentKpiPreview" style="color:#9ca3af;font-size:13px;text-align:center;padding:16px">
            Pilih karyawan untuk menampilkan data talent pool, KPI, dan kalibrasi
        </div>
    </div>

    {{-- ===== PENILAIAN LAINNYA ===== --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
                <div class="section-title">Penilaian & Evaluasi</div>
                <div class="section-sub">Data pendukung usulan promosi</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Absensi</label>
                <input type="text" name="absensi" value="{{ old('absensi') }}"
                    class="form-input" placeholder="cth: Baik" />
            </div>
            <div class="form-group">
                <label class="form-label">Kehadiran</label>
                <input type="text" name="kehadiran" value="{{ old('kehadiran') }}"
                    class="form-input" placeholder="cth: 98%" />
            </div>
            <div class="form-group">
                <label class="form-label">Periode Penilaian</label>
                <input type="text" name="periode_penilaian" value="{{ old('periode_penilaian') }}"
                    class="form-input" placeholder="cth: 2023-2025" />
            </div>
            <div class="form-group">
                <label class="form-label">Tata Kelola</label>
                <input type="text" name="tata_kelola" value="{{ old('tata_kelola') }}"
                    class="form-input" placeholder="cth: Baik" />
            </div>
            <div class="form-group">
                <label class="form-label">MC (Man Count)</label>
                <div class="select-wrap">
                    <select name="mc_tersedia" class="form-input">
                        <option value="0" {{ old('mc_tersedia')==='0' ? 'selected':'' }}>Tidak Tersedia</option>
                        <option value="1" {{ old('mc_tersedia')==='1' ? 'selected':'' }}>Tersedia</option>
                    </select>
                </div>
            </div>
            <div class="form-group full">
                <label class="form-label">Hasil Evaluasi</label>
                <textarea name="hasil_evaluasi" rows="3" class="form-input" style="resize:vertical"
                    placeholder="Isi hasil evaluasi...">{{ old('hasil_evaluasi') }}</textarea>
            </div>
            <div class="form-group full">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" rows="2" class="form-input" style="resize:vertical"
                    placeholder="Catatan tambahan (opsional)...">{{ old('catatan') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('usulan_promosi.index') }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Usulan
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let searchTimer = null;
let selectedKaryawanId = null;
let selectedKaryawanData = null; // simpan data karyawan terpilih untuk dipakai ulang

// ===== SEARCH KARYAWAN =====
document.getElementById('searchKaryawan').addEventListener('input', function() {
    const val = this.value.trim();
    clearTimeout(searchTimer);
    if (val.length < 2) { hideDropdown(); return; }
    searchTimer = setTimeout(() => searchKaryawan(val), 300);
});

function searchKaryawan(q) {
    fetch(`{{ route('usulan_promosi.karyawan_data') }}?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
            const dd = document.getElementById('dropdownKaryawan');
            if (!data.length) {
                dd.innerHTML = '<div style="padding:12px 14px;font-size:13px;color:#9ca3af">Tidak ditemukan</div>';
            } else {
                dd.innerHTML = data.map(k => `
                    <div class="search-karyawan-item" onclick="selectKaryawan(${JSON.stringify(k).replace(/"/g, '&quot;')})">
                        <div class="ski-nama">${k.nama}</div>
                        <div class="ski-meta">NIK ${k.nik} · JG ${k.job_grade} · ${k.jabatan_saat_ini ?? '-'}</div>
                    </div>
                `).join('');
            }
            dd.classList.add('show');
        });
}

function hideDropdown() {
    document.getElementById('dropdownKaryawan').classList.remove('show');
}

// tandai kalau user mengetik jabatan tujuan manual → jangan ditimpa master
let jtTouched = {{ old('jabatan_tujuan') ? 'true' : 'false' }};
document.getElementById('jabatanTujuan').addEventListener('input', () => { jtTouched = true; });

// pilih master → auto-isi teks jabatan tujuan jika masih kosong
document.getElementById('jabatanMaster').addEventListener('change', function () {
    const jt = document.getElementById('jabatanTujuan');
    if (jtTouched && jt.value.trim() !== '') return;
    const opt = this.options[this.selectedIndex];
    jt.value = (opt && opt.value) ? opt.textContent.trim() : '';
});

function selectKaryawan(k) {
    selectedKaryawanId  = k.id;
    selectedKaryawanData = k;
    document.getElementById('karyawanId').value = k.id;
    document.getElementById('searchKaryawan').value = k.nama + ' — NIK ' + k.nik;
    hideDropdown();

    // Update info box header
    document.getElementById('karyawanNama').textContent = k.nama;
    document.getElementById('karyawanJabatan').textContent = k.jabatan_saat_ini ?? '-';
    document.getElementById('karyawanInitial').textContent = (typeof initials === 'function')
        ? initials(k.nama) : k.nama.substring(0, 2).toUpperCase();
    document.getElementById('infoJG').textContent = 'JG ' + k.job_grade;
    document.getElementById('infoPG').textContent = 'PG ' + k.person_grade;
    document.getElementById('infoBand').textContent = k.band;
    document.getElementById('infoStruk').textContent = k.struktural_fungsional ?? '-';

    // Default unit tujuan = unit karyawan saat ini (bisa diubah jika pindah unit)
    const setSel = (id, val) => { const el = document.getElementById(id); if (el) el.value = (val ?? '') === null ? '' : (val ?? ''); };
    setSel('dirTujuan',  k.direktorat_id);
    setSel('kompTujuan', k.kompartemen_id);
    setSel('deptTujuan', k.departemen_id);

    // Tampilkan info box dulu (MDG akan diisi setelah talent pool dimuat)
    document.getElementById('infoMDG').innerHTML = '<span style="color:#9ca3af;font-size:11px">Memuat status MDG...</span>';
    document.getElementById('karyawanInfoBox').classList.add('show');

    loadAssessments(k.id);

    // loadTalentKpi sekaligus hitung MDG via callback
    loadTalentKpi(k.id, function(talentPool) {
        renderMdgCheck(k, talentPool);
    });
}

// ===== RENDER MDG CHECK (shortlist-aware) =====
function renderMdgCheck(k, talentPool) {
    const isShortlist = talentPool && talentPool.klasifikasi === 'shortlist';
    const minBand = isShortlist ? 24 : 36;
    const minJg   = isShortlist ? 12 : 24;
    const minPg   = 12;

    const items = [
        { label: 'MDG Band', ok: k.mdg_band_bulan >= minBand, val: k.mdg_band_bulan + ' bln / ' + minBand + ' bln' + (isShortlist ? ' ✦' : '') },
        { label: 'MDG JG',   ok: k.mdg_jg_bulan   >= minJg,   val: k.mdg_jg_bulan   + ' bln / ' + minJg   + ' bln' + (isShortlist ? ' ✦' : '') },
        { label: 'MDG PG',   ok: k.mdg_pg_bulan   >= minPg,   val: k.mdg_pg_bulan   + ' bln / ' + minPg   + ' bln' },
    ];

    let html = items.map(m =>
        `<span class="mdg-check ${m.ok ? 'mdg-ok' : 'mdg-no'}">${m.ok ? '✅' : '❌'} ${m.label}: ${m.val}</span>`
    ).join('');

    if (isShortlist) {
        html += `<div style="font-size:10px;color:#15803d;margin-top:6px;width:100%">
            ✦ Ketentuan MDG khusus Shortlist Talent Pool ${talentPool.periode}
        </div>`;
    }

    document.getElementById('infoMDG').innerHTML = html;
}

// ===== LOAD ASSESSMENTS =====
function loadAssessments(karyawanId) {
    fetch(`{{ route('usulan_promosi.assessments') }}?karyawan_id=${karyawanId}`)
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('assessmentList');
            if (!data.length) {
                el.innerHTML = '<div style="text-align:center;padding:16px;color:#9ca3af;font-size:13px">Belum ada assessment rekomendasi</div>';
                return;
            }
            const labelMap = { ready:'Ready', ready_with_development:'Ready with Development', not_ready:'Not Ready' };
            const colorMap = { ready:'#15803d', ready_with_development:'#d97706', not_ready:'#dc2626' };
            el.innerHTML = '<div style="display:flex;flex-direction:column;gap:8px">' +
                data.map(a => `
                    <label class="assessment-card" id="ac-${a.id}" onclick="selectAssessment(${a.id}, '${a.label}', '${a.tanggal_exp_idp}', '${a.tingkat_pengukuran}')">
                        <input type="radio" name="_assessment_radio" value="${a.id}">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#111827">📅 ${a.tanggal_pelaksanaan}</div>
                                <div style="font-size:12px;color:#6b7280;margin-top:2px">
                                    Level Ukur: <strong>${a.tingkat_pengukuran}</strong>
                                    · Berlaku s/d: <strong>${a.tanggal_exp_idp}</strong>
                                    ${a.lembaga !== '-' ? '· Lembaga: ' + a.lembaga : ''}
                                </div>
                            </div>
                            <span style="background:${colorMap[a.rekomendasi_final] ?? '#f3f4f6'}22;color:${colorMap[a.rekomendasi_final] ?? '#374151'};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;border:1px solid ${colorMap[a.rekomendasi_final] ?? '#e5e7eb'}44">
                                ${labelMap[a.rekomendasi_final] ?? a.rekomendasi_final ?? '-'}
                            </span>
                        </div>
                    </label>
                `).join('') + '</div>';
        });
}

function selectAssessment(id, label, tglBerlaku, levelUkur) {
    document.querySelectorAll('.assessment-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('ac-' + id);
    if (card) card.classList.add('selected');
    document.getElementById('assessmentId').value = id;
    document.getElementById('prevHasil').textContent = label;
    document.getElementById('prevTglBerlaku').textContent = tglBerlaku;
    document.getElementById('prevLevelUkur').textContent = levelUkur || '-';
    document.getElementById('assessmentPreview').style.display = 'block';
}

// ===== LOAD TALENT & KPI =====
// callback(talentPool) dipanggil setelah data berhasil dimuat
function loadTalentKpi(karyawanId, callback) {
    fetch(`/api/karyawan/${karyawanId}/talent-kpi-preview`)
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('talentKpiPreview');
            let html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';

            html += '<div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Talent Pool Tahun Lalu</div>';
            if (data.talent_pool) {
                const klasBg  = data.talent_pool.klasifikasi === 'shortlist' ? '#dcfce7' : '#dbeafe';
                const klasTxt = data.talent_pool.klasifikasi === 'shortlist' ? '#15803d' : '#1d4ed8';
                html += `<div style="background:white;border:1px solid #e5e7eb;border-radius:8px;padding:12px">
                    <span style="font-size:12px;font-weight:700;color:#111827">Periode ${data.talent_pool.periode}</span>
                    <span style="background:${klasBg};color:${klasTxt};padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;margin-left:8px">${data.talent_pool.klasifikasi}</span>
                </div>`;
            } else {
                html += '<div style="color:#9ca3af;font-size:13px;padding:8px">Tidak ada data talent pool tahun lalu</div>';
            }
            html += '</div>';

            html += '<div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">KPI 4 Tahun Terakhir</div>';
            if (data.kpi && data.kpi.length) {
                html += '<div style="display:flex;flex-direction:column;gap:6px">' +
                    data.kpi.map(k => `<div style="background:white;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12px;color:#6b7280">${k.tahun} · ${k.periode_label}</span>
                        <span style="font-size:14px;font-weight:800;color:#111827">${k.nilai_format}</span>
                    </div>`).join('') + '</div>';
            } else {
                html += '<div style="color:#9ca3af;font-size:13px;padding:8px">Belum ada data KPI</div>';
            }
            html += '</div>';

            html += '</div><div style="margin-top:14px"><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Kalibrasi 3 Tahun Terakhir</div>';
            if (data.kalibrasi && data.kalibrasi.length) {
                const colorMap = { FEE:'#15803d', EXE:'#1d4ed8', PEE:'#0e7490', MEE:'#374151', ME:'#4b5563', SME:'#ca8a04', PME:'#ea580c', BEE:'#d97706', NME:'#b91c1c', FBE:'#dc2626' };
                const bgMap    = { FEE:'#dcfce7', EXE:'#dbeafe', PEE:'#ecfeff', MEE:'#f3f4f6', ME:'#f3f4f6', SME:'#fef9c3', PME:'#ffedd5', BEE:'#fef3c7', NME:'#fee2e2', FBE:'#fee2e2' };
                html += '<div style="display:flex;gap:10px;flex-wrap:wrap">' +
                    data.kalibrasi.map(k => `<div style="background:white;border:1px solid #e5e7eb;border-radius:8px;padding:10px 16px;text-align:center">
                        <div style="font-size:12px;color:#6b7280">${k.tahun}</div>
                        <span style="background:${bgMap[k.nilai]};color:${colorMap[k.nilai]};padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;display:inline-block;margin-top:4px">${k.nilai}</span>
                    </div>`).join('') + '</div>';
            } else {
                html += '<div style="color:#9ca3af;font-size:13px;padding:8px">Belum ada data kalibrasi</div>';
            }
            html += '</div>';

            el.innerHTML = html;
            el.style.textAlign = 'left';

            // Panggil callback dengan data talent pool agar MDG check bisa dihitung
            if (typeof callback === 'function') {
                callback(data.talent_pool);
            }
        })
        .catch(() => {
            document.getElementById('talentKpiPreview').innerHTML =
                '<div style="color:#9ca3af;font-size:13px;text-align:center">Gagal memuat data</div>';
            // Tetap panggil callback dengan null agar MDG fallback ke threshold normal
            if (typeof callback === 'function') {
                callback(null);
            }
        });
}

// Close dropdown on outside click
document.addEventListener('click', e => {
    if (!e.target.closest('.search-karyawan-wrap')) hideDropdown();
});
</script>
@endpush