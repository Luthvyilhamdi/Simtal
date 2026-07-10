@extends('layouts.app')
@php
    // $tipe: 'pgs' | 'pjs' | null — dibawa dari menu PJS / PGS.
    $labelTipe = $tipe ? strtoupper($tipe) : 'PGS / PJS';
@endphp
@section('title', 'Tambah ' . $labelTipe)
@section('breadcrumb-parent', $tipe ? $labelTipe : 'PGS & PJS')
@section('breadcrumb', 'Tambah ' . $labelTipe)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }
    .form-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:28px;margin-bottom:16px; }
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
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }
    .tipe-group { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .tipe-card { display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:all 0.15s;background:#fafafa;text-align:center; }
    .tipe-card input[type=radio] { display:none; }
    .tipe-card:hover { border-color:#d1d5db;background:#f5f5f0; }
    .tipe-card.sel-pgs { border-color:#3b82f6;background:#eff6ff; }
    .tipe-card.sel-pjs { border-color:#7c3aed;background:#f5f3ff; }
    .tipe-emoji { font-size:28px; }
    .tipe-name { font-size:16px;font-weight:800;letter-spacing:1px; }
    .tipe-card.sel-pgs .tipe-name { color:#1d4ed8; }
    .tipe-card.sel-pjs .tipe-name { color:#7c3aed; }
    .tipe-desc { font-size:11px;color:#9ca3af; }

    /* Karyawan search */
    .search-karyawan-wrap { position:relative; }
    .search-karyawan-input-wrap { display:flex;align-items:center;gap:8px;border:1.5px solid #e5e7eb;border-radius:9px;padding:10px 14px;background:#fafafa;transition:all 0.15s; }
    .search-karyawan-input-wrap:focus-within { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .search-karyawan-input-wrap.error-input { border-color:#ef4444; }
    .search-karyawan-input-wrap svg { width:15px;height:15px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-karyawan-input-wrap input { border:none;background:none;outline:none;font-size:13px;font-family:inherit;color:#111827;width:100%; }
    .search-karyawan-input-wrap input::placeholder { color:#9ca3af; }
    .search-clear { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .search-clear.show { display:block; }
    .karyawan-dropdown { display:none;position:absolute;left:0;right:0;top:calc(100% + 4px);background:white;border:1.5px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.1);z-index:100;max-height:220px;overflow-y:auto; }
    .karyawan-dropdown.show { display:block; }
    .kd-item { display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #f3f4f6;transition:background 0.1s; }
    .kd-item:last-child { border-bottom:none; }
    .kd-item:hover,.kd-item.active { background:#f0fdf4; }
    .kd-avatar { width:32px;height:32px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0; }
    .kd-nama { font-size:13px;font-weight:600;color:#111827; }
    .kd-nik { font-size:11px;color:#9ca3af; }
    .kd-empty { padding:14px;text-align:center;font-size:13px;color:#9ca3af; }

    /* Preview */
    .karyawan-preview { display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-top:8px; }
    .karyawan-preview.show { display:flex;align-items:center;gap:10px; }
    .kp-avatar { width:36px;height:36px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0; }
    .kp-name { font-size:13px;font-weight:700;color:#111827; }
    .kp-detail { font-size:11px;color:#6b7280;margin-top:2px; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
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
        .tipe-group { grid-template-columns:1fr 1fr; }
        .form-actions-card { flex-direction:column;align-items:stretch; }
        .form-actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('pgs_pjs.index', $tipe ? ['tipe' => $tipe] : []) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke {{ $tipe ? $labelTipe : 'PGS & PJS' }}
</a>

<div class="page-header">
    <div class="page-title">➕ Tambah {{ $labelTipe }}</div>
    <div class="page-sub">Tambahkan data pejabat sementara baru</div>
</div>

<form method="POST" action="{{ route('pgs_pjs.store') }}">
    @csrf

    {{-- Tipe — hanya dipilih bila belum ditentukan dari menu (PJS/PGS) --}}
    @if($tipe)
        <input type="hidden" name="tipe" value="{{ $tipe }}">
    @else
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="section-title">Tipe Pejabat Sementara</div>
                <div class="section-sub">Pilih jenis penugasan sementara</div>
            </div>
        </div>
        @php $tipeVal = old('tipe', ''); @endphp
        <div class="tipe-group">
            <label class="tipe-card {{ $tipeVal=='pgs' ? 'sel-pgs' : '' }}" id="tipe-pgs" onclick="selectTipe('pgs')">
                <input type="radio" name="tipe" value="pgs" {{ $tipeVal=='pgs' ? 'checked' : '' }}>
                <span class="tipe-emoji">🔵</span>
                <span class="tipe-name">PGS</span>
                <span class="tipe-desc">Pejabat Sementara</span>
            </label>
            <label class="tipe-card {{ $tipeVal=='pjs' ? 'sel-pjs' : '' }}" id="tipe-pjs" onclick="selectTipe('pjs')">
                <input type="radio" name="tipe" value="pjs" {{ $tipeVal=='pjs' ? 'checked' : '' }}>
                <span class="tipe-emoji">🟣</span>
                <span class="tipe-name">PJS</span>
                <span class="tipe-desc">Pejabat Jabatan Sementara</span>
            </label>
        </div>
        @error('tipe')<div class="error-msg" style="margin-top:8px">{{ $message }}</div>@enderror
    </div>
    @endif

    {{-- Data --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div>
                <div class="section-title">Data {{ $labelTipe }}</div>
                <div class="section-sub">Informasi penugasan sementara</div>
            </div>
        </div>

        <div class="form-grid">

            {{-- Karyawan search --}}
            <div class="form-group full">
                <label class="form-label">Karyawan <span class="req">*</span></label>

                {{-- Hidden select untuk submit form --}}
                <select name="karyawan_id" id="karyawanSelect" style="display:none">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($karyawans as $k)
                        <option value="{{ $k->id }}"
                            data-nama="{{ $k->nama }}"
                            data-nik="{{ $k->nik }}"
                            data-jabatan="{{ $k->jabatan_saat_ini ?? '-' }}"
                            {{ old('karyawan_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} — NIK {{ $k->nik }}
                        </option>
                    @endforeach
                </select>

                {{-- Search Input --}}
                <div class="search-karyawan-wrap">
                    <div class="search-karyawan-input-wrap {{ $errors->has('karyawan_id') ? 'error-input' : '' }}" id="searchWrap">
                        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="karyawanSearch"
                            placeholder="Ketik atau paste nama / NIK karyawan..."
                            autocomplete="off"
                            oninput="onKaryawanSearch(this.value)"
                            onkeydown="onKaryawanKeydown(event)">
                        <button type="button" class="search-clear" id="searchClear" onclick="clearKaryawan()">×</button>
                    </div>
                    <div class="karyawan-dropdown" id="karyawanDropdown">
                        <div id="karyawanDropdownList"></div>
                    </div>
                </div>

                {{-- Preview karyawan terpilih --}}
                <div class="karyawan-preview" id="karyawanPreview">
                    <div class="kp-avatar" id="kpAvatar"></div>
                    <div>
                        <div class="kp-name" id="kpNama"></div>
                        <div class="kp-detail" id="kpDetail"></div>
                    </div>
                    <button type="button" onclick="clearKaryawan()" style="border:none;background:none;color:#9ca3af;cursor:pointer;font-size:18px;margin-left:auto;padding:0;line-height:1">×</button>
                </div>
                @error('karyawan_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Jabatan PGS/PJS --}}
            <div class="form-group full">
                <label class="form-label">Jabatan yang Diduduki <span class="req">*</span></label>
                <input type="text" name="jabatan_pgs_pjs" value="{{ old('jabatan_pgs_pjs') }}"
                       class="form-input {{ $errors->has('jabatan_pgs_pjs') ? 'error-input' : '' }}"
                       placeholder="cth: Manager SDM, Kepala Divisi Keuangan" />
                @error('jabatan_pgs_pjs')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Direktorat --}}
            <div class="form-group">
                <label class="form-label">Kompartemen</label>
                <input type="text" name="direktorat" value="{{ old('direktorat') }}"
                       class="form-input" placeholder="Kompartemen tujuan" />
            </div>

            {{-- Departemen --}}
            <div class="form-group">
                <label class="form-label">Departemen</label>
                <input type="text" name="departemen" value="{{ old('departemen') }}"
                       class="form-input" placeholder="Departemen tujuan" />
            </div>

            {{-- No SK --}}
            <div class="form-group">
                <label class="form-label">No. SK</label>
                <input type="text" name="no_sk" value="{{ old('no_sk') }}"
                       class="form-input" placeholder="Nomor Surat Keputusan" />
            </div>

            {{-- Tanggal SK --}}
            <div class="form-group">
                <label class="form-label">Tanggal SK</label>
                <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk') }}"
                       class="form-input" />
            </div>

            {{-- Tanggal Mulai --}}
            <div class="form-group">
                <label class="form-label">Tanggal Mulai <span class="req">*</span></label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                       class="form-input {{ $errors->has('tanggal_mulai') ? 'error-input' : '' }}" />
                @error('tanggal_mulai')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Keterangan --}}
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="3" class="form-input" style="resize:vertical;"
                          placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
            </div>

        </div>
    </div>

    {{-- Actions --}}
    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af;"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('pgs_pjs.index', $tipe ? ['tipe' => $tipe] : []) }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan {{ $tipe ? $labelTipe : 'PGS/PJS' }}
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function selectTipe(val) {
    ['pgs','pjs'].forEach(t => {
        const el = document.getElementById('tipe-' + t);
        el.className = 'tipe-card';
        if (t === val) el.classList.add('sel-' + t);
    });
    document.querySelector(`input[name="tipe"][value="${val}"]`).checked = true;
}

// ===== KARYAWAN SEARCH =====
let kdHighlight = -1;

function onKaryawanSearch(val) {
    const clr = document.getElementById('searchClear');
    clr.classList.toggle('show', val.length > 0);
    document.getElementById('karyawanPreview').classList.remove('show');
    document.getElementById('karyawanSelect').value = '';
    if (val.length < 1) { document.getElementById('karyawanDropdown').classList.remove('show'); return; }
    const kw = val.toLowerCase();
    const opts = document.querySelectorAll('#karyawanSelect option');
    const results = [];
    opts.forEach(opt => {
        if (!opt.value) return;
        const nama = (opt.dataset.nama || '').toLowerCase();
        const nik  = (opt.dataset.nik  || '').toLowerCase();
        if (nama.includes(kw) || nik.includes(kw)) {
            results.push({ id: opt.value, nama: opt.dataset.nama, nik: opt.dataset.nik, jabatan: opt.dataset.jabatan });
        }
    });
    renderDropdown(results);
}

function renderDropdown(results) {
    const list = document.getElementById('karyawanDropdownList');
    const dd   = document.getElementById('karyawanDropdown');
    kdHighlight = -1;
    if (results.length === 0) {
        list.innerHTML = '<div class="kd-empty">Karyawan tidak ditemukan</div>';
    } else {
        list.innerHTML = results.slice(0, 20).map((r, i) =>
            `<div class="kd-item" data-idx="${i}"
                onclick="selectKaryawan('${r.id}','${r.nama.replace(/'/g,"\'")}','${r.nik}','${(r.jabatan||'').replace(/'/g,"\'")}')">
                <div class="kd-avatar">${r.nama.substring(0,2).toUpperCase()}</div>
                <div>
                    <div class="kd-nama">${r.nama}</div>
                    <div class="kd-nik">NIK ${r.nik} · ${r.jabatan||'-'}</div>
                </div>
            </div>`
        ).join('');
    }
    dd.classList.add('show');
}

function onKaryawanKeydown(e) {
    const items = document.querySelectorAll('.kd-item');
    if (!items.length) return;
    if (e.key === 'ArrowDown') {
        kdHighlight = Math.min(kdHighlight + 1, items.length - 1);
        items.forEach((el, i) => el.classList.toggle('active', i === kdHighlight));
        e.preventDefault();
    } else if (e.key === 'ArrowUp') {
        kdHighlight = Math.max(kdHighlight - 1, 0);
        items.forEach((el, i) => el.classList.toggle('active', i === kdHighlight));
        e.preventDefault();
    } else if (e.key === 'Enter' && kdHighlight >= 0) {
        items[kdHighlight].click();
        e.preventDefault();
    } else if (e.key === 'Escape') {
        document.getElementById('karyawanDropdown').classList.remove('show');
    }
}

function selectKaryawan(id, nama, nik, jabatan) {
    document.getElementById('karyawanSelect').value = id;
    document.getElementById('karyawanSearch').value = nama + ' — NIK ' + nik;
    document.getElementById('searchClear').classList.add('show');
    document.getElementById('karyawanDropdown').classList.remove('show');
    document.getElementById('kpAvatar').textContent = nama.substring(0,2).toUpperCase();
    document.getElementById('kpNama').textContent = nama;
    document.getElementById('kpDetail').textContent = 'NIK ' + nik + ' · ' + (jabatan||'-');
    document.getElementById('karyawanPreview').classList.add('show');
}

function clearKaryawan() {
    document.getElementById('karyawanSelect').value = '';
    document.getElementById('karyawanSearch').value = '';
    document.getElementById('searchClear').classList.remove('show');
    document.getElementById('karyawanDropdown').classList.remove('show');
    document.getElementById('karyawanPreview').classList.remove('show');
    document.getElementById('karyawanSearch').focus();
}

// Tutup dropdown saat klik luar
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-karyawan-wrap')) {
        document.getElementById('karyawanDropdown').classList.remove('show');
    }
});

// Restore old value jika ada
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('karyawanSelect');
    if (sel && sel.value) {
        const opt = sel.options[sel.selectedIndex];
        selectKaryawan(opt.value, opt.dataset.nama, opt.dataset.nik, opt.dataset.jabatan);
    }
});
</script>
@endpush