@extends('layouts.app')
@section('title', 'Tambah Assessment Kompetensi')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'Assessment Kompetensi')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .form-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:28px;margin-bottom:16px; }
    .section-label { font-size:13px;font-weight:700;color:#374151;margin:0 0 16px;padding-bottom:10px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px; }
    .req { color:#ef4444; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }

    /* Criteria Info Box */
    .criteria-box { background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:12px;color:#92400e; }
    .criteria-box strong { color:#78350f; }
    .criteria-box ul { margin:6px 0 0 16px; }
    .criteria-box ul li { margin-bottom:3px; }

    /* Kompetensi Table */
    .komp-table { width:100%;border-collapse:collapse; }
    .komp-table th { font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;padding:10px 12px;background:#f9fafb;border:1px solid #e5e7eb;text-align:left; }
    .komp-table td { padding:10px 12px;border:1px solid #e5e7eb;vertical-align:middle; }
    .komp-table tr:hover td { background:#fafafa; }
    .komp-name { font-size:13px;font-weight:600;color:#111827; }

    /* Score Buttons */
    .score-group { display:flex;gap:6px; }
    .score-btn { width:40px;height:36px;border-radius:8px;border:1.5px solid #e5e7eb;background:white;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.15s;font-family:inherit;color:#6b7280; }
    .score-btn:hover { border-color:#16a34a;color:#15803d;background:#f0fdf4; }
    .score-btn.sel-1 { background:#fef2f2;border-color:#fca5a5;color:#dc2626; }
    .score-btn.sel-2 { background:#fffbeb;border-color:#fcd34d;color:#d97706; }
    .score-btn.sel-3 { background:#f0fdf4;border-color:#86efac;color:#16a34a; }
    .score-btn.sel-4 { background:#eff6ff;border-color:#93c5fd;color:#1d4ed8; }

    /* Hasil Preview */
    .hasil-preview { background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-top:16px; }
    .hasil-row { display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f3f4f6; }
    .hasil-row:last-child { border-bottom:none; }
    .hasil-label { font-size:12px;color:#6b7280;font-weight:500;display:flex;align-items:center;gap:6px; }
    .hasil-val { font-size:14px;font-weight:700; }
    .badge-qualified { background:#dcfce7;color:#15803d;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700; }
    .badge-not { background:#fee2e2;color:#dc2626;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700; }
    .badge-pending { background:#f3f4f6;color:#6b7280;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700; }

    /* Status indicator */
    .crit-ok  { display:inline-block;width:8px;height:8px;border-radius:50%;background:#16a34a;margin-right:4px; }
    .crit-err { display:inline-block;width:8px;height:8px;border-radius:50%;background:#ef4444;margin-right:4px; }
    .crit-gray{ display:inline-block;width:8px;height:8px;border-radius:50%;background:#d1d5db;margin-right:4px; }

    .form-actions { display:flex;gap:12px;justify-content:flex-end;margin-top:20px; }
    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:11px 24px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all 0.15s; }
    .btn-save:hover { background:#166534; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:11px 20px;border-radius:10px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all 0.15s; }
    .btn-cancel:hover { background:#f9fafb; }

    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .score-btn { width:34px;height:32px;font-size:13px; }
    }
</style>
@endpush

@section('content')

<a href="{{ route('history_assessment.index', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke History Assessment
</a>

<form method="POST" action="{{ route('assessment_kompetensi.store', $karyawan) }}">
    @csrf

    {{-- Info Karyawan --}}
    <div class="form-card">
        <div class="section-label">👤 Informasi Karyawan</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nama</label>
                <input type="text" class="form-input" value="{{ $karyawan->nama }}" readonly />
            </div>
            <div class="form-group">
                <label class="form-label">NIK</label>
                <input type="text" class="form-input" value="{{ $karyawan->nik }}" readonly />
            </div>
            <div class="form-group">
                <label class="form-label">Job Grade</label>
                <input type="text" class="form-input" value="JG {{ $karyawan->jobGrade->job_grade ?? '-' }}" readonly />
            </div>
            <div class="form-group">
                <label class="form-label">Person Grade</label>
                <input type="text" class="form-input" value="PG {{ $karyawan->personGrade->person_grade ?? '-' }}" readonly />
            </div>
        </div>
    </div>

    {{-- Info Assessment --}}
    <div class="form-card">
        <div class="section-label">📅 Informasi Assessment</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Tanggal Assessment <span class="req">*</span></label>
                <input type="date" name="tanggal_assessment" class="form-input {{ $errors->has('tanggal_assessment') ? 'error-input' : '' }}"
                       value="{{ old('tanggal_assessment') }}" />
                @error('tanggal_assessment')<div class="error-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Periode</label>
                <input type="text" name="periode" class="form-input"
                       value="{{ old('periode') }}" placeholder="cth: 2024, Q1-2024" />
                <span class="form-hint">Opsional — periode pelaksanaan assessment</span>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" class="form-input"
                       value="{{ old('keterangan') }}" placeholder="Keterangan tambahan (opsional)" />
            </div>
        </div>
    </div>

    {{-- Kompetensi Perilaku --}}
    <div class="form-card">
        <div class="section-label">⭐ Kompetensi Perilaku</div>

        <div class="criteria-box">
            <strong>📋 Kriteria QUALIFIED (Kompetensi Perilaku):</strong>
            <ul>
                <li>Kompetensi yang mendapat rating <strong>2 berjumlah maksimal 3</strong></li>
                <li><strong>Tidak ada</strong> kompetensi yang mendapat rating <strong>1</strong></li>
            </ul>
        </div>

        <div style="overflow-x:auto;">
            <table class="komp-table">
                <thead>
                    <tr>
                        <th style="width:50%;">Kompetensi</th>
                        <th>Nilai (1–4)</th>
                        <th style="width:80px;text-align:center;">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($competencies as $key => $label)
                    <tr>
                        <td><div class="komp-name">{{ $label }}</div></td>
                        <td>
                            <div class="score-group">
                                @for($i = 1; $i <= 4; $i++)
                                <button type="button" class="score-btn {{ old($key) == $i ? 'sel-'.$i : '' }}"
                                        onclick="selectScore('{{ $key }}', {{ $i }}, this, 'comp')">
                                    {{ $i }}
                                </button>
                                @endfor
                                <input type="hidden" name="{{ $key }}" id="input_{{ $key }}" value="{{ old($key) }}" />
                            </div>
                            @error($key)<div class="error-msg">{{ $message }}</div>@enderror
                        </td>
                        <td style="text-align:center;">
                            <span id="display_{{ $key }}" style="font-size:18px;font-weight:800;color:{{ old($key) ? (old($key) < 3 ? '#dc2626' : '#15803d') : '#d1d5db' }};">
                                {{ old($key) ?? '—' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Professional Qualification --}}
    <div class="form-card">
        <div class="section-label">🏆 Professional Qualification</div>

        <div class="criteria-box">
            <strong>📋 Kriteria QUALIFIED (Professional Qualification):</strong>
            <ul>
                <li>Seluruh bidang mendapat nilai <strong>minimal 2</strong> (tidak ada nilai 1)</li>
            </ul>
        </div>

        <div style="overflow-x:auto;">
            <table class="komp-table">
                <thead>
                    <tr>
                        <th style="width:50%;">Qualification</th>
                        <th>Nilai (1–4)</th>
                        <th style="width:80px;text-align:center;">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($qualifications as $key => $label)
                    <tr>
                        <td><div class="komp-name">{{ $label }}</div></td>
                        <td>
                            <div class="score-group">
                                @for($i = 1; $i <= 4; $i++)
                                <button type="button" class="score-btn {{ old($key) == $i ? 'sel-'.$i : '' }}"
                                        onclick="selectScore('{{ $key }}', {{ $i }}, this, 'qual')">
                                    {{ $i }}
                                </button>
                                @endfor
                                <input type="hidden" name="{{ $key }}" id="input_{{ $key }}" value="{{ old($key) }}" />
                            </div>
                            @error($key)<div class="error-msg">{{ $message }}</div>@enderror
                        </td>
                        <td style="text-align:center;">
                            <span id="display_{{ $key }}" style="font-size:18px;font-weight:800;color:{{ old($key) ? (old($key) < 2 ? '#dc2626' : '#15803d') : '#d1d5db' }};">
                                {{ old($key) ?? '—' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Hasil Preview --}}
        <div class="hasil-preview">
            <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">📊 Preview Hasil</div>

            {{-- Kompetensi detail --}}
            <div class="hasil-row">
                <span class="hasil-label">
                    <span class="crit-gray" id="dot_comp_r1"></span>
                    Kompetensi rating 1 (harus = 0)
                </span>
                <span class="hasil-val" id="preview_comp_r1" style="color:#dc2626;">0</span>
            </div>
            <div class="hasil-row">
                <span class="hasil-label">
                    <span class="crit-gray" id="dot_comp_r2"></span>
                    Kompetensi rating 2 (maks. 3)
                </span>
                <span class="hasil-val" id="preview_comp_r2" style="color:#d97706;">0</span>
            </div>
            <div class="hasil-row">
                <span class="hasil-label">
                    <span class="crit-gray" id="dot_qual_r1"></span>
                    Qualification nilai &lt; 2 (harus = 0)
                </span>
                <span class="hasil-val" id="preview_qual_r1" style="color:#dc2626;">0</span>
            </div>
            <div class="hasil-row">
                <span class="hasil-label" style="font-weight:700;color:#374151;">Kesimpulan</span>
                <span id="preview_kesimpulan" class="badge-pending">Belum lengkap</span>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('history_assessment.index', $karyawan) }}" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-save">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Assessment
        </button>
    </div>

</form>

@endsection

@push('scripts')
<script>
const competencies   = @json(array_keys($competencies));
const qualifications = @json(array_keys($qualifications));

function selectScore(field, val, btn, type) {
    btn.closest('.score-group').querySelectorAll('.score-btn').forEach(b => b.className = 'score-btn');
    btn.className = 'score-btn sel-' + val;
    document.getElementById('input_' + field).value = val;

    const disp = document.getElementById('display_' + field);
    disp.textContent = val;
    if (type === 'comp') {
        disp.style.color = val < 3 ? '#dc2626' : '#15803d';
    } else {
        // Qualification: merah jika < 2
        disp.style.color = val < 2 ? '#dc2626' : '#15803d';
    }
    updatePreview();
}

function updatePreview() {
    let compR1 = 0, compR2 = 0, qualR1 = 0;
    let allFilled = true;

    // Hitung kompetensi
    competencies.forEach(f => {
        const val = parseInt(document.getElementById('input_' + f).value);
        if (!val) { allFilled = false; return; }
        if (val === 1) compR1++;
        if (val === 2) compR2++;
    });

    // Hitung qualification
    qualifications.forEach(f => {
        const val = parseInt(document.getElementById('input_' + f).value);
        if (!val) { allFilled = false; return; }
        if (val < 2) qualR1++; // nilai < 2 = tidak memenuhi minimal
    });

    // Update display
    document.getElementById('preview_comp_r1').textContent = compR1;
    document.getElementById('preview_comp_r2').textContent = compR2;
    document.getElementById('preview_qual_r1').textContent = qualR1;

    // Update dot indicators
    const setDot = (id, ok) => {
        const el = document.getElementById(id);
        el.className = ok ? 'crit-ok' : (allFilled ? 'crit-err' : 'crit-gray');
    };

    const compR1ok = compR1 === 0;
    const compR2ok = compR2 <= 3;
    const qualOk   = qualR1 === 0;

    setDot('dot_comp_r1', compR1ok);
    setDot('dot_comp_r2', compR2ok);
    setDot('dot_qual_r1', qualOk);

    // Update r2 color
    const r2el = document.getElementById('preview_comp_r2');
    r2el.style.color = compR2 > 3 ? '#dc2626' : (compR2 > 0 ? '#d97706' : '#374151');

    // Kesimpulan
    const el = document.getElementById('preview_kesimpulan');
    if (!allFilled) {
        el.textContent = 'Belum lengkap';
        el.className = 'badge-pending';
    } else if (compR1ok && compR2ok && qualOk) {
        el.textContent = '✅ QUALIFIED';
        el.className = 'badge-qualified';
    } else {
        el.textContent = '❌ NOT QUALIFIED';
        el.className = 'badge-not';
    }
}

window.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush