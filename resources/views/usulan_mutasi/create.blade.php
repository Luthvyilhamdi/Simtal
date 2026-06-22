@extends('layouts.app')
@section('title', 'Buat Usulan Rotasi/Mutasi')
@section('breadcrumb-parent', 'Rotasi & Mutasi')
@section('breadcrumb', 'Buat Usulan')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#64748b;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:24px; }
    .page-eyebrow { font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#15803d;margin-bottom:7px; }
    .page-title { font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-.02em; }
    .page-sub { font-size:13px;color:#64748b;margin-top:5px; }

    .form-card { background:#fff;border-radius:16px;border:1px solid #e7ebf0;padding:26px;margin-bottom:16px; }
    .section-header { display:flex;align-items:center;gap:11px;margin-bottom:20px;padding-bottom:13px;border-bottom:1px solid #f1f5f9; }
    .section-icon { width:34px;height:34px;border-radius:9px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid #bbf7d0; }
    .section-icon svg { width:16px;height:16px;stroke:#15803d;fill:none;stroke-width:1.8; }
    .section-icon.blue { background:#eff6ff;border-color:#bfdbfe; }
    .section-icon.blue svg { stroke:#1d4ed8; }
    .section-title { font-size:14px;font-weight:700;color:#0f172a; }
    .section-sub { font-size:12px;color:#94a3b8;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em; }
    .req { color:#ef4444; }
    .form-input { padding:10px 13px;border:1.5px solid #e7ebf0;border-radius:9px;font-size:13px;font-family:inherit;color:#0f172a;background:#fff;outline:none;transition:all .15s;width:100%; }
    .form-input:focus { border-color:#15803d;box-shadow:0 0 0 3px rgba(21,128,61,.08); }
    .form-hint { font-size:11px;color:#94a3b8;margin-top:2px; }
    .error-msg { font-size:11px;color:#ef4444; }
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:13px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #94a3b8;pointer-events:none; }
    .select-wrap select { appearance:none;padding-right:34px;cursor:pointer;width:100%; }

    /* Jenis selector */
    .jenis-row { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .jenis-opt { position:relative;border:1.5px solid #e7ebf0;border-radius:12px;padding:14px 16px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:12px;background:#fff; }
    .jenis-opt input { position:absolute;opacity:0;pointer-events:none; }
    .jenis-opt .j-ic { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .jenis-opt .j-ic svg { width:18px;height:18px;fill:none;stroke-width:1.9; }
    .jenis-opt .j-name { font-size:14px;font-weight:700;color:#0f172a; }
    .jenis-opt .j-desc { font-size:11.5px;color:#94a3b8;margin-top:2px; }
    .jenis-opt:has(input:checked) { border-color:#15803d;background:#f0fdf4;box-shadow:0 0 0 3px rgba(21,128,61,.07); }
    .jenis-opt.rotasi .j-ic { background:#dbeafe; } .jenis-opt.rotasi .j-ic svg { stroke:#1d4ed8; }
    .jenis-opt.mutasi .j-ic { background:#f5f3ff; } .jenis-opt.mutasi .j-ic svg { stroke:#7c3aed; }

    /* Search karyawan */
    .search-wrap { position:relative; }
    .search-dd { position:absolute;top:100%;left:0;right:0;background:#fff;border:1.5px solid #e7ebf0;border-radius:10px;box-shadow:0 12px 28px rgba(15,23,42,.1);z-index:100;max-height:280px;overflow-y:auto;display:none;margin-top:4px; }
    .search-dd.show { display:block; }
    .search-item { padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;transition:background .1s; }
    .search-item:last-child { border-bottom:none; }
    .search-item:hover { background:#f0fdf4; }
    .si-nama { font-size:13px;font-weight:600;color:#0f172a; }
    .si-meta { font-size:11px;color:#94a3b8;margin-top:2px; }

    .info-box { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 18px;display:none;margin-top:14px; }
    .info-box.show { display:block; }
    .info-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:12px; }
    .info-item { background:#fff;border-radius:9px;padding:10px 12px;border:1px solid #e7ebf0; }
    .info-lbl { font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.03em; }
    .info-val { font-size:13px;font-weight:700;color:#0f172a;margin-top:3px; }

    .actions-card { background:#fff;border-radius:16px;border:1px solid #e7ebf0;padding:18px 26px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .actions-right { display:flex;gap:10px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:7px;background:#fff;color:#475569;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;border:1.5px solid #e7ebf0;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f8fafc; }
    .btn-save { display:inline-flex;align-items:center;gap:7px;background:#15803d;color:#fff;padding:10px 22px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s;box-shadow:0 1px 2px rgba(21,128,61,.25); }
    .btn-save:hover { background:#166534; }
    .btn-save svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-save svg { stroke:#fff; }

    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
        .jenis-row { grid-template-columns:1fr; }
        .info-grid { grid-template-columns:1fr 1fr; }
        .actions-card { flex-direction:column;align-items:stretch; }
        .actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('usulan_mutasi.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Rotasi & Mutasi
</a>

<div class="page-header">
    <div class="page-eyebrow">Manajemen Talenta</div>
    <div class="page-title">Buat Usulan Rotasi / Mutasi</div>
    <div class="page-sub">Perpindahan setara — Job Grade & Person Grade tidak berubah</div>
</div>

<form method="POST" action="{{ route('usulan_mutasi.store') }}" id="formMutasi">
    @csrf

    {{-- KARYAWAN --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <div><div class="section-title">Data Karyawan</div><div class="section-sub">Cari berdasarkan NIK atau nama</div></div>
        </div>

        <div class="form-group full">
            <label class="form-label">NIK / Nama Karyawan <span class="req">*</span></label>
            <div class="search-wrap">
                <input type="text" id="searchKaryawan" class="form-input" placeholder="Ketik NIK atau nama karyawan…" autocomplete="off">
                <div class="search-dd" id="searchDd"></div>
            </div>
            <input type="hidden" name="karyawan_id" id="karyawanId">
            @error('karyawan_id')<div class="error-msg">{{ $message }}</div>@enderror
        </div>

        <div class="info-box" id="infoBox">
            <div style="display:flex;align-items:center;gap:11px">
                <div style="width:42px;height:42px;border-radius:11px;background:#15803d;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0" id="infoInitial">—</div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#0f172a" id="infoNama">—</div>
                    <div style="font-size:12px;color:#64748b" id="infoJabatan">—</div>
                </div>
            </div>
            <div class="info-grid">
                <div class="info-item"><div class="info-lbl">Job Grade</div><div class="info-val" id="infoJG">-</div></div>
                <div class="info-item"><div class="info-lbl">Person Grade</div><div class="info-val" id="infoPG">-</div></div>
                <div class="info-item"><div class="info-lbl">Band</div><div class="info-val" id="infoBand">-</div></div>
                <div class="info-item"><div class="info-lbl">Struktural</div><div class="info-val" id="infoStruk">-</div></div>
            </div>
            <div style="font-size:11px;color:#15803d;margin-top:10px;display:flex;align-items:center;gap:6px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                Grade tidak berubah pada rotasi/mutasi
            </div>
        </div>
    </div>

    {{-- DETAIL --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon blue"><svg viewBox="0 0 24 24"><path d="M16 3h5v5"/><path d="M21 3l-7 7"/><path d="M8 21H3v-5"/><path d="M3 21l7-7"/></svg></div>
            <div><div class="section-title">Detail Perpindahan</div><div class="section-sub">Jenis usulan & posisi tujuan</div></div>
        </div>

        <div class="form-group full" style="margin-bottom:18px">
            <label class="form-label" style="margin-bottom:8px">Jenis Usulan <span class="req">*</span></label>
            <div class="jenis-row">
                <label class="jenis-opt rotasi">
                    <input type="radio" name="jenis" value="rotasi" {{ old('jenis','rotasi')==='rotasi'?'checked':'' }}>
                    <div class="j-ic"><svg viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg></div>
                    <div><div class="j-name">Rotasi</div><div class="j-desc">Perputaran jabatan setara</div></div>
                </label>
                <label class="jenis-opt mutasi">
                    <input type="radio" name="jenis" value="mutasi" {{ old('jenis')==='mutasi'?'checked':'' }}>
                    <div class="j-ic"><svg viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg></div>
                    <div><div class="j-name">Mutasi</div><div class="j-desc">Perpindahan unit / lokasi</div></div>
                </label>
            </div>
        </div>

        <div class="form-grid">
            {{-- 1) JABATAN TUJUAN (teks isi) --}}
            <div class="form-group full">
                <label class="form-label">Jabatan Tujuan <span class="req">*</span></label>
                <input type="text" name="jabatan_tujuan" id="jabatanTujuan"
                    value="{{ old('jabatan_tujuan') }}"
                    class="form-input {{ $errors->has('jabatan_tujuan')?'error-input':'' }}"
                    placeholder="Ketik jabatan tujuan (judul yang akan tampil)…" autocomplete="off">
                <div class="form-hint">Label jabatan yang akan tampil di riwayat & profil karyawan.</div>
                @error('jabatan_tujuan')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- 2) JABATAN MASTER (dropdown) --}}
            <div class="form-group full">
                <label class="form-label">Jabatan (Master) <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="jabatan_tujuan_id" id="jabatanMaster" class="form-input {{ $errors->has('jabatan_tujuan_id')?'error-input':'' }}" required>
                        <option value="">— Pilih Jabatan Master —</option>
                        @foreach($jabatans as $jb)
                        <option value="{{ $jb->id }}" {{ old('jabatan_tujuan_id')==$jb->id?'selected':'' }}>{{ $jb->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-hint">Untuk struktur & deteksi tingkat pejabat (SVP/VP/SPM/PM).</div>
                @error('jabatan_tujuan_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group full">
                <label class="form-label">Direktorat Tujuan <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="direktorat_tujuan_id" id="dirTujuan" class="form-input" required>
                        <option value="">— Pilih Direktorat —</option>
                        @foreach($direktorats as $d)
                        <option value="{{ $d->id }}" {{ old('direktorat_tujuan_id')==$d->id?'selected':'' }}>{{ $d->nama_direktorat ?? $d->nama ?? ('#'.$d->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-hint">Default mengikuti unit karyawan. Ubah jika pindah unit.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Kompartemen Tujuan <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="kompartemen_tujuan_id" id="kompTujuan" class="form-input" required>
                        <option value="">— Pilih —</option>
                        @foreach($kompartemens as $kp)
                        <option value="{{ $kp->id }}" {{ old('kompartemen_tujuan_id')==$kp->id?'selected':'' }}>{{ $kp->nama_kompartemen ?? ('#'.$kp->id) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Departemen Tujuan <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="departemen_tujuan_id" id="deptTujuan" class="form-input" required>
                        <option value="">— Pilih —</option>
                        @foreach($departemens as $dp)
                        <option value="{{ $dp->id }}" {{ old('departemen_tujuan_id')==$dp->id?'selected':'' }}>{{ $dp->nama_departemen ?? ('#'.$dp->id) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group full">
                <label class="form-label">Kode Struktur <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="kode_struktur_tujuan_id" class="form-input" required>
                        <option value="">— Pilih Kode Struktur —</option>
                        @foreach($kodeStrukturs as $ks)
                        <option value="{{ $ks->id }}" {{ old('kode_struktur_tujuan_id')==$ks->id?'selected':'' }}>{{ $ks->nama ?? $ks->kode_struktur ?? $ks->kode ?? ('#'.$ks->id) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group full">
                <label class="form-label">Alasan</label>
                <textarea name="alasan" rows="2" class="form-input" style="resize:vertical" placeholder="cth: Pengembangan kompetensi, kebutuhan organisasi…">{{ old('alasan') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Usulan</label>
                <input type="date" name="tanggal_usulan" value="{{ old('tanggal_usulan', now()->format('Y-m-d')) }}" class="form-input">
            </div>
        </div>
    </div>

    <div class="actions-card">
        <div style="font-size:12px;color:#94a3b8"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="actions-right">
            <a href="{{ route('usulan_mutasi.index') }}" class="btn-cancel">
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

document.getElementById('searchKaryawan').addEventListener('input', function() {
    const val = this.value.trim();
    clearTimeout(searchTimer);
    if (val.length < 2) { hideDd(); return; }
    searchTimer = setTimeout(() => searchKaryawan(val), 300);
});

function searchKaryawan(q) {
    fetch(`{{ route('usulan_promosi.karyawan_data') }}?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
            const dd = document.getElementById('searchDd');
            if (!data.length) {
                dd.innerHTML = '<div style="padding:12px 14px;font-size:13px;color:#94a3b8">Tidak ditemukan</div>';
            } else {
                dd.innerHTML = data.map(k => `
                    <div class="search-item" onclick='selectKaryawan(${JSON.stringify(k).replace(/'/g, "&#39;")})'>
                        <div class="si-nama">${k.nama}</div>
                        <div class="si-meta">NIK ${k.nik} · JG ${k.job_grade} · ${k.jabatan_saat_ini ?? '-'}</div>
                    </div>`).join('');
            }
            dd.classList.add('show');
        });
}
function hideDd() { document.getElementById('searchDd').classList.remove('show'); }

// tandai kalau user mengetik jabatan tujuan manual → jangan ditimpa master
let jtTouched = {{ old('jabatan_tujuan') ? 'true' : 'false' }};
document.getElementById('jabatanTujuan').addEventListener('input', () => { jtTouched = true; });

// pilih master → auto-isi teks jabatan tujuan jika masih kosong / belum diketik manual
document.getElementById('jabatanMaster').addEventListener('change', function () {
    const jt = document.getElementById('jabatanTujuan');
    if (jtTouched && jt.value.trim() !== '') return;
    const opt = this.options[this.selectedIndex];
    jt.value = (opt && opt.value) ? opt.textContent.trim() : '';
});

function selectKaryawan(k) {
    document.getElementById('karyawanId').value = k.id;
    document.getElementById('searchKaryawan').value = k.nama + ' — NIK ' + k.nik;
    hideDd();

    document.getElementById('infoNama').textContent = k.nama;
    document.getElementById('infoJabatan').textContent = k.jabatan_saat_ini ?? '-';
    document.getElementById('infoInitial').textContent = (typeof initials === 'function') ? initials(k.nama) : k.nama.substring(0,2).toUpperCase();
    document.getElementById('infoJG').textContent = 'JG ' + k.job_grade;
    document.getElementById('infoPG').textContent = 'PG ' + k.person_grade;
    document.getElementById('infoBand').textContent = k.band;
    document.getElementById('infoStruk').textContent = k.struktural_fungsional ?? '-';
    document.getElementById('infoBox').classList.add('show');

    // Default unit tujuan = unit karyawan saat ini
    const setSel = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
    setSel('dirTujuan',  k.direktorat_id);
    setSel('kompTujuan', k.kompartemen_id);
    setSel('deptTujuan', k.departemen_id);
}

document.addEventListener('click', e => { if (!e.target.closest('.search-wrap')) hideDd(); });
</script>
@endpush