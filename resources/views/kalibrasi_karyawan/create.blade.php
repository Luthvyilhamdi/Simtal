@extends('layouts.app')
@section('title', isset($kalibrasi) ? 'Edit Kalibrasi' : 'Tambah Kalibrasi')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', isset($kalibrasi) ? 'Edit Kalibrasi' : 'Tambah Kalibrasi')

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
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    /* Nilai cards */
    .nilai-group { display:flex;flex-direction:column;gap:10px; }
    .nilai-card { display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:all .15s;background:#fafafa; }
    .nilai-card input { display:none; }
    .nilai-card:hover { border-color:#d1d5db;background:#f5f5f0; }
    .nilai-dot { width:14px;height:14px;border-radius:50%;background:#d1d5db;flex-shrink:0;transition:background .15s; }
    .nilai-name { font-size:13px;font-weight:700;color:#374151; }
    .nilai-desc { font-size:11px;color:#9ca3af;margin-top:1px; }

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
        .form-actions-card { flex-direction:column;align-items:stretch; }
        .form-actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('kalibrasi_karyawan.index', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Kalibrasi
</a>

<div class="page-header">
    <div class="page-title">{{ isset($kalibrasi) ? '✏️ Edit Kalibrasi' : '➕ Tambah Kalibrasi' }}</div>
    <div class="page-sub">{{ isset($kalibrasi) ? 'Perbarui' : 'Tambah' }} kalibrasi untuk <strong>{{ $karyawan->nama }}</strong></div>
</div>

@php
    $nilaiList = [
        'FEE' => ['label' => 'Far Exceeded Expectation', 'color' => '#15803d', 'bg' => '#f0fdf4'],
        'EXE' => ['label' => 'Exceeds Expectation',      'color' => '#1d4ed8', 'bg' => '#eff6ff'],
        'MEE' => ['label' => 'Meet Expectation',          'color' => '#374151', 'bg' => '#f9fafb'],
        'BEE' => ['label' => 'Below Expectation',         'color' => '#d97706', 'bg' => '#fffbeb'],
        'FBE' => ['label' => 'Far Below Expectation',     'color' => '#dc2626', 'bg' => '#fef2f2'],
    ];
    $selectedNilai = old('nilai', $kalibrasi->nilai ?? '');
@endphp

<form method="POST" action="{{ isset($kalibrasi) ? route('kalibrasi_karyawan.update', [$karyawan, $kalibrasi]) : route('kalibrasi_karyawan.store', $karyawan) }}">
    @csrf
    @if(isset($kalibrasi)) @method('PUT') @endif

    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div>
                <div class="section-title">Data Kalibrasi</div>
                <div class="section-sub">Satu kalibrasi per tahun per karyawan</div>
            </div>
        </div>

        <div class="form-grid">
            {{-- Tahun --}}
            <div class="form-group">
                <label class="form-label">Tahun <span class="req">*</span></label>
                <input type="number" name="tahun"
                    value="{{ old('tahun', $kalibrasi->tahun ?? now()->year) }}"
                    class="form-input {{ $errors->has('tahun') ? 'error-input' : '' }}"
                    min="2000" max="2100"
                    {{ isset($kalibrasi) ? 'readonly' : '' }} />
                @if(isset($kalibrasi))
                    <span style="font-size:11px;color:#9ca3af">Tahun tidak bisa diubah</span>
                @endif
                @error('tahun')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            {{-- Placeholder agar grid seimbang --}}
            <div></div>

            {{-- Nilai Kalibrasi --}}
            <div class="form-group full">
                <label class="form-label">Nilai Kalibrasi <span class="req">*</span></label>
                <div class="nilai-group">
                    @foreach($nilaiList as $key => $opt)
                    {{-- FIX: onclick dan style CSS var diganti data-* + class Blade ternary --}}
                    <label class="nilai-card {{ $selectedNilai === $key ? 'sel' : '' }}"
                           id="nilai-{{ $key }}"
                           data-nilai="{{ $key }}"
                           data-color="{{ $opt['color'] }}"
                           data-bg="{{ $opt['bg'] }}">
                        <input type="radio" name="nilai" value="{{ $key }}"
                               {{ $selectedNilai === $key ? 'checked' : '' }}>
                        <div class="nilai-dot"></div>
                        <div>
                            <div class="nilai-name">{{ $key }}</div>
                            <div class="nilai-desc">{{ $opt['label'] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('nilai')<div class="error-msg" style="margin-top:6px">{{ $message }}</div>@enderror
            </div>

            {{-- Keterangan --}}
            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="3" class="form-input" style="resize:vertical"
                    placeholder="Catatan tambahan (opsional)...">{{ old('keterangan', $kalibrasi->keterangan ?? '') }}</textarea>
                @error('keterangan')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('kalibrasi_karyawan.index', $karyawan) }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                {{ isset($kalibrasi) ? 'Perbarui' : 'Simpan' }} Kalibrasi
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Warna setiap nilai — diambil dari data-* agar tidak ada Blade di JS/style
const nilaiColors = {};
document.querySelectorAll('.nilai-card[data-nilai]').forEach(function(el) {
    nilaiColors[el.dataset.nilai] = {
        color: el.dataset.color,
        bg:    el.dataset.bg
    };
});

function applyNilaiStyle(key, isSelected) {
    const el = document.getElementById('nilai-' + key);
    if (!el) return;
    if (isSelected) {
        el.classList.add('sel');
        el.style.borderColor = nilaiColors[key].color;
        el.style.background  = nilaiColors[key].bg;
        const dot  = el.querySelector('.nilai-dot');
        const name = el.querySelector('.nilai-name');
        if (dot)  dot.style.background = nilaiColors[key].color;
        if (name) name.style.color     = nilaiColors[key].color;
    } else {
        el.classList.remove('sel');
        el.style.borderColor = '';
        el.style.background  = '';
        const dot  = el.querySelector('.nilai-dot');
        const name = el.querySelector('.nilai-name');
        if (dot)  dot.style.background = '';
        if (name) name.style.color     = '';
    }
}

function selectNilai(key) {
    Object.keys(nilaiColors).forEach(k => applyNilaiStyle(k, k === key));
    const radio = document.querySelector('input[name="nilai"][value="' + key + '"]');
    if (radio) radio.checked = true;
}

// Delegasi klik pada label nilai-card (menggantikan onclick Blade)
document.addEventListener('click', function(e) {
    const card = e.target.closest('.nilai-card[data-nilai]');
    if (card) selectNilai(card.dataset.nilai);
});

// Apply style untuk nilai yang sudah dipilih saat halaman load (edit mode)
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="nilai"]:checked');
    if (checked) applyNilaiStyle(checked.value, true);
});
</script>
@endpush