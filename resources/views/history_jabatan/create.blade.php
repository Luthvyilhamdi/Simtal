@extends('layouts.app')
@section('title', 'Tambah History Jabatan')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'Tambah Jabatan')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:24px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }
    .form-wrap { max-width:100%; }
    .form-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:28px;margin-bottom:16px; }
    .section-header { display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f3f4f6; }
    .section-icon { width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .section-icon svg { width:16px;height:16px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .section-title { font-size:14px;font-weight:700;color:#111827; }
    .section-sub { font-size:12px;color:#9ca3af;margin-top:1px; }
    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;display:flex;align-items:center;gap:4px; }
    .form-label .req { color:#ef4444; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444; }
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;-webkit-appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    /* Tipe Jabatan Cards */
    .tipe-group { display:grid;grid-template-columns:repeat(5,1fr);gap:8px; }
    .tipe-card {
        display:flex;flex-direction:column;align-items:center;gap:6px;
        padding:12px 8px;border:1.5px solid #e5e7eb;border-radius:10px;
        cursor:pointer;transition:all 0.15s;background:#fafafa;text-align:center;
    }
    .tipe-card input[type=radio] { display:none; }
    .tipe-card:hover { border-color:#d1d5db;background:#f5f5f0; }
    .tipe-card.selected-promosi   { border-color:#16a34a;background:#f0fdf4; }
    .tipe-card.selected-mutasi    { border-color:#3b82f6;background:#eff6ff; }
    .tipe-card.selected-rotasi    { border-color:#0891b2;background:#ecfeff; }
    .tipe-card.selected-demosi    { border-color:#ef4444;background:#fef2f2; }
    .tipe-card.selected-onboarding{ border-color:#f59e0b;background:#fffbeb; }
    .tipe-emoji { font-size:22px; }
    .tipe-name { font-size:12px;font-weight:700;color:#374151; }
    .tipe-desc { font-size:10px;color:#9ca3af; }

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
<div class="form-wrap">
    <a href="{{ route('history_jabatan.index', $karyawan) }}" class="back-link">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke History Jabatan
    </a>

    <div class="page-header">
        <div class="page-title">➕ Tambah History Jabatan</div>
        <div class="page-sub">Tambah history jabatan baru untuk <strong>{{ $karyawan->nama }}</strong> — profil akan otomatis diperbarui</div>
    </div>

    <form method="POST" action="{{ route('history_jabatan.store', $karyawan) }}">
        @csrf

        {{-- Tipe Perubahan --}}
        <div class="form-card">
            <div class="section-header">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <div>
                    <div class="section-title">Tipe Perubahan Jabatan</div>
                    <div class="section-sub">Pilih jenis perubahan jabatan yang terjadi</div>
                </div>
            </div>

            @php $tipe = old('tipe', 'mutasi'); @endphp
            <div class="tipe-group">
                <label class="tipe-card {{ $tipe=='promosi' ? 'selected-promosi' : '' }}" id="tipe-promosi" onclick="selectTipe('promosi')">
                    <input type="radio" name="tipe" value="promosi" {{ $tipe=='promosi' ? 'checked' : '' }}>
                    <span class="tipe-emoji">↑</span>
                    <span class="tipe-name">Promosi</span>
                    <span class="tipe-desc">Naik jabatan</span>
                </label>
                <label class="tipe-card {{ $tipe=='mutasi' ? 'selected-mutasi' : '' }}" id="tipe-mutasi" onclick="selectTipe('mutasi')">
                    <input type="radio" name="tipe" value="mutasi" {{ $tipe=='mutasi' ? 'checked' : '' }}>
                    <span class="tipe-emoji">↔</span>
                    <span class="tipe-name">Mutasi</span>
                    <span class="tipe-desc">Pindah unit</span>
                </label>
                <label class="tipe-card {{ $tipe=='rotasi' ? 'selected-rotasi' : '' }}" id="tipe-rotasi" onclick="selectTipe('rotasi')">
                    <input type="radio" name="tipe" value="rotasi" {{ $tipe=='rotasi' ? 'checked' : '' }}>
                    <span class="tipe-emoji">↻</span>
                    <span class="tipe-name">Rotasi</span>
                    <span class="tipe-desc">Rotasi jabatan</span>
                </label>
                <label class="tipe-card {{ $tipe=='demosi' ? 'selected-demosi' : '' }}" id="tipe-demosi" onclick="selectTipe('demosi')">
                    <input type="radio" name="tipe" value="demosi" {{ $tipe=='demosi' ? 'checked' : '' }}>
                    <span class="tipe-emoji">↓</span>
                    <span class="tipe-name">Demosi</span>
                    <span class="tipe-desc">Turun jabatan</span>
                </label>
                <label class="tipe-card {{ $tipe=='onboarding' ? 'selected-onboarding' : '' }}" id="tipe-onboarding" onclick="selectTipe('onboarding')">
                    <input type="radio" name="tipe" value="onboarding" {{ $tipe=='onboarding' ? 'checked' : '' }}>
                    <span class="tipe-emoji">★</span>
                    <span class="tipe-name">Onboarding</span>
                    <span class="tipe-desc">Jabatan awal</span>
                </label>
            </div>
            @error('tipe')<div class="error-msg" style="margin-top:8px">{{ $message }}</div>@enderror
        </div>

        {{-- Jabatan & Struktur --}}
        <div class="form-card">
            <div class="section-header">
                <div class="section-icon">
                    <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <div>
                    <div class="section-title">Jabatan & Struktur Baru</div>
                    <div class="section-sub">Data jabatan yang akan menggantikan jabatan saat ini</div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Jabatan <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="jabatan_id" class="form-input {{ $errors->has('jabatan_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach($jabatans as $j)
                                <option value="{{ $j->id }}" {{ old('jabatan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('jabatan_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Direktorat <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="direktorat_id" class="form-input {{ $errors->has('direktorat_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Direktorat --</option>
                            @foreach($direktorats as $d)
                                <option value="{{ $d->id }}" {{ old('direktorat_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_direktorat }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('direktorat_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Kompartemen <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="kompartemen_id" class="form-input {{ $errors->has('kompartemen_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Kompartemen --</option>
                            @foreach($kompartemens as $k)
                                <option value="{{ $k->id }}" {{ old('kompartemen_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kompartemen }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('kompartemen_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Departemen <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="departemen_id" class="form-input {{ $errors->has('departemen_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departemens as $d)
                                <option value="{{ $d->id }}" {{ old('departemen_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_departemen }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('departemen_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Job Grade <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="job_grade_id" class="form-input {{ $errors->has('job_grade_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Job Grade --</option>
                            @foreach($jobGrades as $j)
                                <option value="{{ $j->id }}" {{ old('job_grade_id') == $j->id ? 'selected' : '' }}>{{ $j->job_grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('job_grade_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Person Grade <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="person_grade_id" class="form-input {{ $errors->has('person_grade_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Person Grade --</option>
                            @foreach($personGrades as $p)
                                <option value="{{ $p->id }}" {{ old('person_grade_id') == $p->id ? 'selected' : '' }}>{{ $p->person_grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('person_grade_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Kode Struktur <span class="req">*</span></label>
                    <div class="select-wrap">
                        <select name="kode_struktur_id" class="form-input {{ $errors->has('kode_struktur_id') ? 'error-input' : '' }}">
                            <option value="">-- Pilih Kode Struktur --</option>
                            @foreach($kodeStrukturs as $ks)
                                <option value="{{ $ks->id }}" {{ old('kode_struktur_id') == $ks->id ? 'selected' : '' }}>{{ $ks->kode_struktur }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('kode_struktur_id')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- JABATAN SAAT INI --}}
                <div class="form-group">
                    <label class="form-label">Jabatan Saat Ini</label>
                    <input type="text" name="jabatan_saat_ini"
                        value="{{ old('jabatan_saat_ini', $karyawan->jabatan_saat_ini) }}"
                        class="form-input"
                        placeholder="cth: Associate Officer Talenta Manajemen" />
                    @error('jabatan_saat_ini')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Mulai <span class="req">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                           class="form-input {{ $errors->has('tanggal_mulai') ? 'error-input' : '' }}" />
                    @error('tanggal_mulai')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                {{-- No SK --}}
                <div class="form-group">
                    <label class="form-label">No. SK</label>
                    <input type="text" name="no_sk" value="{{ old('no_sk') }}"
                        class="form-input"
                        placeholder="Nomor Surat Keputusan (opsional)" />
                    @error('no_sk')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Tanggal SK --}}
                <div class="form-group">
                    <label class="form-label">Tanggal SK</label>
                    <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk') }}"
                        class="form-input" />
                    @error('tanggal_sk')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group full">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" rows="3" placeholder="Catatan atau alasan perubahan jabatan (opsional)..."
                              class="form-input" style="resize:vertical;">{{ old('keterangan') }}</textarea>
                    @error('keterangan')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="form-actions-card">
            <div style="font-size:12px;color:#9ca3af;"><span style="color:#ef4444">*</span> Wajib diisi · Profil karyawan akan otomatis diperbarui</div>
            <div class="form-actions-right">
                <a href="{{ route('history_jabatan.index', $karyawan) }}" class="btn-cancel">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Batal
                </a>
                <button type="submit" class="btn-save">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan & Perbarui Profil
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const tipes = ['promosi', 'mutasi', 'rotasi', 'demosi', 'onboarding'];
    function selectTipe(val) {
        tipes.forEach(t => {
            const el = document.getElementById('tipe-' + t);
            el.className = 'tipe-card';
            if (t === val) el.classList.add('selected-' + t);
        });
        document.querySelector(`input[value="${val}"]`).checked = true;
    }
</script>
@endpush