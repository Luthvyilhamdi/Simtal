@extends('layouts.app')
@section('title', 'Tambah Penilaian')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'Tambah Penilaian')

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
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    /* Tipe cards */
    .tipe-group { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .tipe-card { display:flex;align-items:center;gap:12px;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:all .15s;background:#fafafa; }
    .tipe-card input { display:none; }
    .tipe-card:hover { border-color:#d1d5db; }
    .tipe-card.sel-kpi { border-color:#3b82f6;background:#eff6ff; }
    .tipe-card.sel-360 { border-color:#7c3aed;background:#f5f3ff; }
    .tipe-emoji { font-size:24px;flex-shrink:0; }
    .tipe-info .tipe-name { font-size:14px;font-weight:700;color:#374151; }
    .tipe-info .tipe-desc { font-size:11px;color:#9ca3af;margin-top:2px; }
    .tipe-card.sel-kpi .tipe-name { color:#1d4ed8; }
    .tipe-card.sel-360 .tipe-name { color:#7c3aed; }

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
        .tipe-group { grid-template-columns:1fr; }
        .form-actions-card { flex-direction:column;align-items:stretch; }
        .form-actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('penilaian_karyawan.index', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Penilaian
</a>

<div class="page-header">
    <div class="page-title">➕ Tambah Penilaian</div>
    <div class="page-sub">Tambah penilaian untuk <strong>{{ $karyawan->nama }}</strong></div>
</div>

<form method="POST" action="{{ route('penilaian_karyawan.store', $karyawan) }}">
    @csrf

    {{-- Tipe --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div>
                <div class="section-title">Tipe Penilaian</div>
                <div class="section-sub">Pilih jenis penilaian yang akan ditambahkan</div>
            </div>
        </div>
        @php $tipeVal = old('tipe', ''); @endphp
        <div class="tipe-group">
            <label class="tipe-card {{ $tipeVal==='KPI' ? 'sel-kpi' : '' }}" id="tipe-kpi" onclick="selectTipe('KPI')">
                <input type="radio" name="tipe" value="KPI" {{ $tipeVal==='KPI' ? 'checked' : '' }}>
                <span class="tipe-emoji">📊</span>
                <div class="tipe-info">
                    <div class="tipe-name">KPI</div>
                    <div class="tipe-desc">Key Performance Indicator</div>
                </div>
            </label>
            <label class="tipe-card {{ $tipeVal==='360' ? 'sel-360' : '' }}" id="tipe-360" onclick="selectTipe('360')">
                <input type="radio" name="tipe" value="360" {{ $tipeVal==='360' ? 'checked' : '' }}>
                <span class="tipe-emoji">🔄</span>
                <div class="tipe-info">
                    <div class="tipe-name">360°</div>
                    <div class="tipe-desc">Penilaian 360 derajat</div>
                </div>
            </label>
        </div>
        @error('tipe')<div class="error-msg" style="margin-top:8px">{{ $message }}</div>@enderror
    </div>

    {{-- Data Penilaian --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div>
                <div class="section-title">Data Penilaian</div>
                <div class="section-sub">Isi detail penilaian karyawan</div>
            </div>
        </div>

        <div class="form-grid">
            {{-- Tahun --}}
            <div class="form-group">
                <label class="form-label">Tahun <span class="req">*</span></label>
                <input type="number" name="tahun" value="{{ old('tahun', now()->year) }}"
                    class="form-input {{ $errors->has('tahun') ? 'error-input' : '' }}"
                    min="2000" max="2100" />
                @error('tahun')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Periode --}}
            <div class="form-group">
                <label class="form-label">Periode <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="periode" class="form-input {{ $errors->has('periode') ? 'error-input' : '' }}">
                        <option value="">-- Pilih Periode --</option>
                        <option value="triwulan_1" {{ old('periode') === 'triwulan_1' ? 'selected' : '' }}>Triwulan I</option>
                        <option value="triwulan_2" {{ old('periode') === 'triwulan_2' ? 'selected' : '' }}>Triwulan II</option>
                        <option value="triwulan_3" {{ old('periode') === 'triwulan_3' ? 'selected' : '' }}>Triwulan III</option>
                        <option value="triwulan_4" {{ old('periode') === 'triwulan_4' ? 'selected' : '' }}>Triwulan IV</option>
                        <option value="tahunan"    {{ old('periode') === 'tahunan'    ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>
                @error('periode')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Judul --}}
            <div class="form-group full">
                <label class="form-label">Judul Penilaian <span class="req">*</span></label>
                <input type="text" name="judul" value="{{ old('judul') }}"
                    class="form-input {{ $errors->has('judul') ? 'error-input' : '' }}"
                    placeholder="cth: Penilaian KPI Triwulan II Tahun 2025" />
                @error('judul')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Nilai --}}
            <div class="form-group">
                <label class="form-label">Nilai <span class="req">*</span></label>
                <input type="number" name="nilai" value="{{ old('nilai') }}"
                    class="form-input {{ $errors->has('nilai') ? 'error-input' : '' }}"
                    step="0.01" min="0" max="999.99"
                    placeholder="cth: 100.00" />
                <span style="font-size:11px;color:#9ca3af">Format: 100,00 — gunakan titik sebagai desimal</span>
                @error('nilai')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Keterangan --}}
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="3" class="form-input" style="resize:vertical"
                    placeholder="Catatan tambahan (opsional)...">{{ old('keterangan') }}</textarea>
                @error('keterangan')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('penilaian_karyawan.index', $karyawan) }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Penilaian
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function selectTipe(val) {
    document.getElementById('tipe-kpi').className = 'tipe-card' + (val === 'KPI' ? ' sel-kpi' : '');
    document.getElementById('tipe-360').className = 'tipe-card' + (val === '360' ? ' sel-360' : '');
    document.querySelector(`input[name="tipe"][value="${val}"]`).checked = true;
}
</script>
@endpush