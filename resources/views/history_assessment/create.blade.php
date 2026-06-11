@extends('layouts.app')
@section('title', 'Tambah Assessment')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'Tambah Assessment')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    /* Info Card Karyawan */
    .info-card { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap; }
    .info-avatar { width:44px;height:44px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0;overflow:hidden;border:2px solid #bbf7d0; }
    .info-avatar img { width:100%;height:100%;object-fit:cover; }
    .info-name { font-size:14px;font-weight:700;color:#111827; }
    .info-detail { font-size:12px;color:#6b7280;margin-top:2px; }
    .info-note { margin-left:auto;font-size:11px;color:#15803d;background:#dcfce7;padding:4px 10px;border-radius:20px;font-weight:600;white-space:nowrap; }

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
    .form-input[readonly] { background:#f3f4f6;color:#6b7280;cursor:not-allowed; }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }

    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    /* Rekomendasi Final Cards */
    .final-group { display:grid;grid-template-columns:repeat(3,1fr);gap:10px; }
    .final-card { display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 10px;border:1.5px solid #e5e7eb;border-radius:10px;cursor:pointer;transition:all 0.15s;background:#fafafa;text-align:center; }
    .final-card input[type=radio] { display:none; }
    .final-card:hover { border-color:#d1d5db;background:#f5f5f0; }
    .final-card.sel-ready { border-color:#16a34a;background:#f0fdf4; }
    .final-card.sel-ready_with_development { border-color:#f59e0b;background:#fffbeb; }
    .final-card.sel-not_ready { border-color:#ef4444;background:#fef2f2; }
    .final-dot { width:12px;height:12px;border-radius:50%;background:#d1d5db; }
    .final-card.sel-ready .final-dot { background:#16a34a; }
    .final-card.sel-ready_with_development .final-dot { background:#f59e0b; }
    .final-card.sel-not_ready .final-dot { background:#ef4444; }
    .final-name { font-size:12px;font-weight:700;color:#374151; }
    .final-card.sel-ready .final-name { color:#15803d; }
    .final-card.sel-ready_with_development .final-name { color:#d97706; }
    .final-card.sel-not_ready .final-name { color:#ef4444; }

    /* Persen input */
    .persen-wrap { position:relative; }
    .persen-wrap input { padding-right:36px; }
    .persen-suffix { position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:600;color:#9ca3af;pointer-events:none; }

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
        .final-group { grid-template-columns:1fr; }
        .form-actions-card { flex-direction:column;align-items:stretch; }
        .form-actions-right { flex-direction:column; }
        .btn-cancel,.btn-save { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')

<a href="{{ route('history_assessment.index', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke History Assessment
</a>

<div class="page-header">
    <div class="page-title">📋 Tambah Assessment</div>
    <div class="page-sub">Data jabatan, job grade & person grade diambil otomatis dari profil karyawan</div>
</div>

{{-- Info Karyawan --}}
<div class="info-card">
    <div class="info-avatar">
        @if($karyawan->foto)
            <img src="{{ Storage::url($karyawan->foto) }}" alt="">
        @else
            {{ strtoupper(substr($karyawan->nama, 0, 2)) }}
        @endif
    </div>
    <div>
        <div class="info-name">{{ $karyawan->nama }}</div>
        <div class="info-detail">
            {{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}
            · JG: {{ $karyawan->jobGrade->job_grade ?? '-' }}
            · PG: {{ $karyawan->personGrade->person_grade ?? '-' }}
        </div>
    </div>
    <div class="info-note">✓ Data terintegrasi</div>
</div>

<form method="POST" action="{{ route('history_assessment.store', $karyawan) }}">
    @csrf

    {{-- Data Terintegrasi (readonly) --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="section-title">Data Karyawan (Otomatis)</div>
                <div class="section-sub">Diambil dari profil karyawan saat ini — akan ikut berubah jika profil diperbarui</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group full">
                <label class="form-label">Jabatan Saat Ini</label>
                <input type="text" class="form-input" readonly
                       value="{{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}" />
            </div>
            <div class="form-group">
                <label class="form-label">Job Grade</label>
                <input type="text" class="form-input" readonly
                       value="{{ $karyawan->jobGrade->job_grade ?? '-' }}" />
            </div>
            <div class="form-group">
                <label class="form-label">Person Grade</label>
                <input type="text" class="form-input" readonly
                       value="{{ $karyawan->personGrade->person_grade ?? '-' }}" />
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Kelamin</label>
                <input type="text" class="form-input" readonly
                       value="{{ $karyawan->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}" />
            </div>
            <div class="form-group">
                <label class="form-label">Usia Saat Ini</label>
                <input type="text" class="form-input" readonly
                       value="{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age }} tahun" />
            </div>
            <div class="form-group full">
                <label class="form-label">Lembaga Assessor</label>
                <input type="text" name="lembaga" value="{{ old('lembaga') }}"
                    class="form-input" placeholder="cth: PT. XYZ Consulting, Lembaga ABC" />
                <span class="form-hint">Opsional</span>
            </div>
        </div>
    </div>

    {{-- Data Assessment --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
                <div class="section-title">Data Assessment</div>
                <div class="section-sub">Informasi pelaksanaan assessment</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Tanggal Pelaksanaan <span class="req">*</span></label>
                <input type="date" name="tanggal_pelaksanaan" value="{{ old('tanggal_pelaksanaan') }}"
                       class="form-input {{ $errors->has('tanggal_pelaksanaan') ? 'error-input' : '' }}"
                       id="tglPelaksanaan" onchange="hitungExpIdp(this.value)" />
                @error('tanggal_pelaksanaan')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Exp IDP</label>
                <input type="text" class="form-input" readonly id="tglExpIdpDisplay"
                       value="{{ old('tanggal_pelaksanaan') ? \Carbon\Carbon::parse(old('tanggal_pelaksanaan'))->addYears(2)->format('d M Y') : '-' }}" />
                <span class="form-hint">Otomatis 2 tahun dari tanggal pelaksanaan</span>
            </div>

            <div class="form-group">
                <label class="form-label">Job Stream</label>
                <input type="text" name="job_stream" value="{{ old('job_stream') }}"
                       class="form-input" placeholder="cth: Operation, Finance" />
            </div>

            <div class="form-group">
                <label class="form-label">Tingkat Pengukuran</label>
                <input type="text" name="tingkat_pengukuran" value="{{ old('tingkat_pengukuran') }}"
                       class="form-input" placeholder="cth: Band 1, ToBe Band 1" />
            </div>

            <div class="form-group full">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" rows="3" class="form-input" style="resize:vertical;"
                          placeholder="Catatan tambahan assessment...">{{ old('keterangan') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Rekomendasi --}}
    <div class="form-card">
        <div class="section-header">
            <div class="section-icon">
                <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div>
                <div class="section-title">Rekomendasi</div>
                <div class="section-sub">Hasil rekomendasi dari assessment (dalam persentase)</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Rekomendasi Inti</label>
                <div class="persen-wrap">
                    <input type="number" name="rekomendasi_inti" value="{{ old('rekomendasi_inti') }}"
                           class="form-input" min="0" max="100" step="0.01" placeholder="0.00" />
                    <span class="persen-suffix">%</span>
                </div>
                @error('rekomendasi_inti')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Rekomendasi Primer</label>
                <div class="persen-wrap">
                    <input type="number" name="rekomendasi_primer" value="{{ old('rekomendasi_primer') }}"
                           class="form-input" min="0" max="100" step="0.01" placeholder="0.00" />
                    <span class="persen-suffix">%</span>
                </div>
                @error('rekomendasi_primer')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Rekomendasi Sekunder</label>
                <div class="persen-wrap">
                    <input type="number" name="rekomendasi_skunder" value="{{ old('rekomendasi_skunder') }}"
                           class="form-input" min="0" max="100" step="0.01" placeholder="0.00" />
                    <span class="persen-suffix">%</span>
                </div>
                @error('rekomendasi_skunder')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group full">
                <label class="form-label">Rekomendasi Final</label>
                @php $finalVal = old('rekomendasi_final', ''); @endphp
                <div class="final-group">
                    <label class="final-card {{ $finalVal=='ready' ? 'sel-ready' : '' }}"
                           id="final-ready" onclick="selectFinal('ready')">
                        <input type="radio" name="rekomendasi_final" value="ready"
                               {{ $finalVal=='ready' ? 'checked' : '' }}>
                        <div class="final-dot"></div>
                        <span class="final-name">Ready</span>
                    </label>
                    <label class="final-card {{ $finalVal=='ready_with_development' ? 'sel-ready_with_development' : '' }}"
                           id="final-ready_with_development" onclick="selectFinal('ready_with_development')">
                        <input type="radio" name="rekomendasi_final" value="ready_with_development"
                               {{ $finalVal=='ready_with_development' ? 'checked' : '' }}>
                        <div class="final-dot"></div>
                        <span class="final-name">Ready with Development</span>
                    </label>
                    <label class="final-card {{ $finalVal=='not_ready' ? 'sel-not_ready' : '' }}"
                           id="final-not_ready" onclick="selectFinal('not_ready')">
                        <input type="radio" name="rekomendasi_final" value="not_ready"
                               {{ $finalVal=='not_ready' ? 'checked' : '' }}>
                        <div class="final-dot"></div>
                        <span class="final-name">Not Ready</span>
                    </label>
                </div>
                @error('rekomendasi_final')<div class="error-msg" style="margin-top:8px">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="form-actions-card">
        <div style="font-size:12px;color:#9ca3af;"><span style="color:#ef4444">*</span> Wajib diisi</div>
        <div class="form-actions-right">
            <a href="{{ route('history_assessment.index', $karyawan) }}" class="btn-cancel">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Batal
            </a>
            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Assessment
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    // Hitung exp IDP otomatis
    function hitungExpIdp(val) {
        if (!val) { document.getElementById('tglExpIdpDisplay').value = '-'; return; }
        const d = new Date(val);
        d.setFullYear(d.getFullYear() + 2);
        const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
        document.getElementById('tglExpIdpDisplay').value =
            d.getDate().toString().padStart(2,'0') + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }

    // Rekomendasi Final
    const finals = ['ready','ready_with_development','not_ready'];
    function selectFinal(val) {
        finals.forEach(f => {
            const el = document.getElementById('final-' + f);
            el.className = 'final-card';
            if (f === val) el.classList.add('sel-' + f);
        });
        document.querySelector(`input[name="rekomendasi_final"][value="${val}"]`).checked = true;
    }
</script>
@endpush