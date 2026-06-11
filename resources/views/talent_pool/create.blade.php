@extends('layouts.app')
@section('title', 'Tambah Talent Pool')
@section('breadcrumb-parent', 'Data Talent')
@section('breadcrumb', 'Tambah Talent')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }
    .form-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:28px;margin-bottom:16px; }
    .section-header { display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f3f4f6; }
    .section-icon { width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .section-icon svg { width:16px;height:16px;stroke:#16a34a;fill:none;stroke-width:1.8; }
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
    .error-msg { font-size:11px;color:#ef4444; }

    /* Search karyawan */
    .search-karyawan-wrap { position:relative; }
    .search-karyawan-input-wrap { display:flex;align-items:center;gap:8px;border:1.5px solid #e5e7eb;border-radius:9px;padding:10px 14px;background:#fafafa;transition:all .15s; }
    .search-karyawan-input-wrap:focus-within { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .search-karyawan-input-wrap.error-input { border-color:#ef4444; }
    .search-karyawan-input-wrap svg { width:15px;height:15px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-karyawan-input-wrap input { border:none;background:none;outline:none;font-size:13px;font-family:inherit;color:#111827;width:100%; }
    .search-karyawan-input-wrap input::placeholder { color:#9ca3af; }
    .search-clear { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .search-clear.show { display:block; }
    .karyawan-dropdown { display:none;position:absolute;left:0;right:0;top:calc(100% + 4px);background:white;border:1.5px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);z-index:100;max-height:220px;overflow-y:auto; }
    .karyawan-dropdown.show { display:block; }
    .kd-item { display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #f3f4f6;transition:background .1s; }
    .kd-item:last-child { border-bottom:none; }
    .kd-item:hover,.kd-item.active { background:#f0fdf4; }
    .kd-avatar { width:32px;height:32px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0; }
    .kd-nama { font-size:13px;font-weight:600;color:#111827; }
    .kd-nik { font-size:11px;color:#9ca3af; }
    .kd-empty { padding:14px;text-align:center;font-size:13px;color:#9ca3af; }
    .karyawan-preview { display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;margin-top:8px; }
    .karyawan-preview.show { display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px; }
    .kp-item { }
    .kp-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.3px; }
    .kp-val { font-size:13px;font-weight:700;color:#111827;margin-top:2px; }
    .kp-val.green { color:#15803d; }

    /* Klasifikasi cards */
    .klas-group { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .klas-card { display:flex;align-items:center;gap:14px;padding:16px 18px;border:2px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:all .15s;background:#fafafa; }
    .klas-card input { display:none; }
    .klas-card:hover { border-color:#d1d5db;background:#f5f5f0; }
    .klas-card.sel-longlist  { border-color:#3b82f6;background:#eff6ff; }
    .klas-card.sel-shortlist { border-color:#15803d;background:#f0fdf4; }
    .klas-emoji { font-size:28px;flex-shrink:0; }
    .klas-info { flex:1; }
    .klas-name { font-size:14px;font-weight:700;color:#374151; }
    .klas-desc { font-size:11px;color:#9ca3af;margin-top:2px; }
    .klas-card.sel-longlist  .klas-name { color:#1d4ed8; }
    .klas-card.sel-shortlist .klas-name { color:#15803d; }

    .form-actions-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
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
        .klas-group { grid-template-columns:1fr; }
        .form-actions-card { flex-direction:column;align-items:stretch; }
        .form-actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('talent_pool.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Data Talent
</a>

<div class="page-header">
    <div class="page-title">➕ Tambah Talent Pool</div>
    <div class="page-sub">Tambahkan karyawan ke dalam daftar talent</div>
</div>

<form method="POST" action="{{ route('talent_pool.store') }}">
    @csrf

    {{-- Periode + Karyawan --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="section-title">Data Talent</div>
                <div class="section-sub">Pilih periode dan karyawan yang akan dimasukkan</div>
            </div>
        </div>

        <div class="form-grid">
            {{-- Periode --}}
            <div class="form-group">
                <label class="form-label">Periode (Tahun) <span class="req">*</span></label>
                <input type="number" name="periode" value="{{ old('periode', $periode) }}"
                    class="form-input {{ $errors->has('periode') ? 'error-input' : '' }}"
                    min="2000" max="2100" placeholder="2025" />
                @error('periode')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div></div>{{-- spacer --}}

            {{-- Karyawan search --}}
            <div class="form-group full">
                <label class="form-label">Karyawan <span class="req">*</span></label>
                <select name="karyawan_id" id="karyawanSelect" style="display:none">
                    <option value="">-- Pilih --</option>
                    @foreach($karyawans as $k)
                        <option value="{{ $k->id }}"
                            data-nama="{{ $k->nama }}"
                            data-nik="{{ $k->nik }}"
                            data-jabatan="{{ $k->jabatan_saat_ini ?? '-' }}"
                            data-jg="{{ $k->jobGrade->job_grade ?? '-' }}"
                            data-pg="{{ $k->personGrade->person_grade ?? '-' }}"
                            data-band="{{ $k->band ?? '-' }}"
                            {{ old('karyawan_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }}
                        </option>
                    @endforeach
                </select>

                <div class="search-karyawan-wrap">
                    <div class="search-karyawan-input-wrap {{ $errors->has('karyawan_id') ? 'error-input' : '' }}">
                        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="karyawanSearch"
                            placeholder="Ketik atau paste nama / NIK..."
                            autocomplete="off"
                            oninput="onKaryawanSearch(this.value)"
                            onkeydown="onKaryawanKeydown(event)">
                        <button type="button" class="search-clear" id="searchClear" onclick="clearKaryawan()">×</button>
                    </div>
                    <div class="karyawan-dropdown" id="karyawanDropdown">
                        <div id="karyawanDropdownList"></div>
                    </div>
                </div>

                {{-- Preview --}}
                <div class="karyawan-preview" id="karyawanPreview">
                    <div class="kp-item">
                        <div class="kp-label">Nama</div>
                        <div class="kp-val green" id="kpNama"></div>
                    </div>
                    <div class="kp-item">
                        <div class="kp-label">NIK</div>
                        <div class="kp-val" id="kpNik"></div>
                    </div>
                    <div class="kp-item">
                        <div class="kp-label">Jabatan</div>
                        <div class="kp-val" id="kpJabatan"></div>
                    </div>
                    <div class="kp-item">
                        <div class="kp-label">Job Grade</div>
                        <div class="kp-val" id="kpJg"></div>
                    </div>
                    <div class="kp-item">
                        <div class="kp-label">Person Grade</div>
                        <div class="kp-val" id="kpPg"></div>
                    </div>
                    <div class="kp-item">
                        <div class="kp-label">Band</div>
                        <div class="kp-val" id="kpBand"></div>
                    </div>
                </div>
                @error('karyawan_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Klasifikasi --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon" style="background:#fdf4ff">
                <svg viewBox="0 0 24 24" style="stroke:#a21caf"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div>
                <div class="section-title">Klasifikasi Talent</div>
                <div class="section-sub">Tentukan apakah karyawan ini Longlist atau Shortlist</div>
            </div>
        </div>

        @php $klasVal = old('klasifikasi', ''); @endphp
        <div class="klas-group">
            <label class="klas-card {{ $klasVal==='longlist' ? 'sel-longlist' : '' }}" id="klas-longlist" onclick="selectKlas('longlist')">
                <input type="radio" name="klasifikasi" value="longlist" {{ $klasVal==='longlist' ? 'checked' : '' }}>
                <span class="klas-emoji">🔵</span>
                <div class="klas-info">
                    <div class="klas-name">Longlist</div>
                    <div class="klas-desc">Kandidat potensial yang masuk daftar awal</div>
                </div>
            </label>
            <label class="klas-card {{ $klasVal==='shortlist' ? 'sel-shortlist' : '' }}" id="klas-shortlist" onclick="selectKlas('shortlist')">
                <input type="radio" name="klasifikasi" value="shortlist" {{ $klasVal==='shortlist' ? 'checked' : '' }}>
                <span class="klas-emoji">🟢</span>
                <div class="klas-info">
                    <div class="klas-name">Shortlist</div>
                    <div class="klas-desc">Kandidat terpilih yang siap untuk pengembangan</div>
                </div>
            </label>
        </div>
        @error('klasifikasi')<div class="error-msg" style="margin-top:8px">{{ $message }}</div>@enderror

        <div class="form-group full" style="margin-top:16px">
            <label class="form-label">Catatan</label>
            <textarea name="catatan" rows="3" class="form-input" style="resize:vertical"
                placeholder="Catatan tambahan (opsional)...">{{ old('catatan') }}</textarea>
            @error('catatan')<div class="error-msg">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Actions --}}
    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('talent_pool.index') }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Klasifikasi
function selectKlas(val) {
    document.getElementById('klas-longlist').className  = 'klas-card' + (val === 'longlist'  ? ' sel-longlist'  : '');
    document.getElementById('klas-shortlist').className = 'klas-card' + (val === 'shortlist' ? ' sel-shortlist' : '');
    document.querySelector(`input[name="klasifikasi"][value="${val}"]`).checked = true;
}

// Search karyawan
let kdHighlight = -1;
function onKaryawanSearch(val) {
    document.getElementById('searchClear').classList.toggle('show', val.length > 0);
    document.getElementById('karyawanPreview').classList.remove('show');
    document.getElementById('karyawanSelect').value = '';
    if (val.length < 1) { document.getElementById('karyawanDropdown').classList.remove('show'); return; }
    const kw = val.toLowerCase();
    const results = [];
    document.querySelectorAll('#karyawanSelect option').forEach(opt => {
        if (!opt.value) return;
        if ((opt.dataset.nama||'').toLowerCase().includes(kw) || (opt.dataset.nik||'').toLowerCase().includes(kw)) {
            results.push(opt.dataset);
            results[results.length-1].id = opt.value;
        }
    });
    renderDropdown(results);
}
function renderDropdown(results) {
    const list = document.getElementById('karyawanDropdownList');
    const dd   = document.getElementById('karyawanDropdown');
    kdHighlight = -1;
    if (!results.length) {
        list.innerHTML = '<div class="kd-empty">Karyawan tidak ditemukan</div>';
    } else {
        list.innerHTML = results.slice(0,20).map((r,i) =>
            `<div class="kd-item" onclick="selectKaryawan('${r.id}','${r.nama.replace(/'/g,"\\'")}','${r.nik}','${(r.jabatan||'').replace(/'/g,"\\'")}','${r.jg||'-'}','${r.pg||'-'}','${r.band||'-'}')">
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
    if (e.key==='ArrowDown') { kdHighlight=Math.min(kdHighlight+1,items.length-1); items.forEach((el,i)=>el.classList.toggle('active',i===kdHighlight)); e.preventDefault(); }
    else if (e.key==='ArrowUp') { kdHighlight=Math.max(kdHighlight-1,0); items.forEach((el,i)=>el.classList.toggle('active',i===kdHighlight)); e.preventDefault(); }
    else if (e.key==='Enter' && kdHighlight>=0) { items[kdHighlight].click(); e.preventDefault(); }
    else if (e.key==='Escape') { document.getElementById('karyawanDropdown').classList.remove('show'); }
}
function selectKaryawan(id,nama,nik,jabatan,jg,pg,band) {
    document.getElementById('karyawanSelect').value = id;
    document.getElementById('karyawanSearch').value = nama + ' — NIK ' + nik;
    document.getElementById('searchClear').classList.add('show');
    document.getElementById('karyawanDropdown').classList.remove('show');
    document.getElementById('kpNama').textContent    = nama;
    document.getElementById('kpNik').textContent     = nik;
    document.getElementById('kpJabatan').textContent = jabatan;
    document.getElementById('kpJg').textContent      = jg;
    document.getElementById('kpPg').textContent      = pg;
    document.getElementById('kpBand').textContent    = band;
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
document.addEventListener('click', e => {
    if (!e.target.closest('.search-karyawan-wrap'))
        document.getElementById('karyawanDropdown').classList.remove('show');
});
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('karyawanSelect');
    if (sel && sel.value) {
        const opt = sel.options[sel.selectedIndex];
        selectKaryawan(opt.value,opt.dataset.nama,opt.dataset.nik,opt.dataset.jabatan,opt.dataset.jg,opt.dataset.pg,opt.dataset.band);
    }
});
</script>
@endpush