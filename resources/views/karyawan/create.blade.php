@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('breadcrumb', 'Tambah Karyawan')

@push('styles')
<style>
    .form-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; padding: 28px; }
    .form-title { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group input, .form-group select {
        padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 6px;
        font-size: 13px; font-family: inherit; color: #111827;
        background: #f9fafb; outline: none; transition: all 0.15s;
    }
    .form-group input:focus, .form-group select:focus { border-color: #16a34a; background: white; box-shadow: 0 0 0 3px rgba(22,163,74,0.08); }
    .form-group input[readonly] { background: #f3f4f6; color: #15803d; font-weight: 700; cursor: not-allowed; }
    .form-group .error { font-size: 11px; color: #dc2626; margin-top: 2px; }
    .form-group .hint { font-size: 11px; color: #9ca3af; margin-top: 2px; }
    .foto-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; display: none; }
    .form-actions { display: flex; gap: 12px; margin-top: 24px; justify-content: flex-end; }
    .btn-primary { background: #15803d; color: white; padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; font-family: inherit; }
    .btn-primary:hover { background: #166534; }
    .btn-secondary { background: white; color: #374151; padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 600; border: 1px solid #e5e7eb; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-flex; align-items: center; }
    .section-label { font-size: 13px; font-weight: 700; color: #374151; margin: 24px 0 16px; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; display:flex;align-items:center;gap:8px; }
    .mdg-info { background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-top:12px;font-size:12px;color:#92400e; }
    .mdg-info ul { margin:6px 0 0 16px;display:flex;flex-direction:column;gap:3px; }
</style>
@endpush

@section('content')
<div style="max-width: 1500px; width:100%;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('karyawan.index') }}" style="color:#6b7280;text-decoration:none;font-size:13px;">← Kembali</a>
        <span style="color:#d1d5db;">/</span>
        <span style="font-size:13px;font-weight:600;color:#111827;">Tambah Karyawan</span>
    </div>

    <div class="form-card">
        <div class="form-title">📋 Data Karyawan Baru</div>

        <form method="POST" action="{{ route('karyawan.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- ===== DATA PRIBADI ===== --}}
            <div class="section-label">👤 Data Pribadi</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>NIK *</label>
                    <input type="text" name="nik" value="{{ old('nik') }}" placeholder="Nomor Induk Karyawan" />
                    @error('nik')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Nama lengkap karyawan" />
                    @error('nama')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin *</label>
                    <select name="jenis_kelamin">
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ old('jenis_kelamin')=='L'?'selected':'' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin')=='P'?'selected':'' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Tempat Lahir *</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" placeholder="Kota kelahiran" />
                    @error('tempat_lahir')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir *</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" />
                    @error('tanggal_lahir')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}" />
                    @error('tanggal_masuk')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status">
                        <option value="aktif" {{ old('status','aktif')=='aktif'?'selected':'' }}>Aktif</option>
                        <option value="tidak aktif" {{ old('status')=='tidak aktif'?'selected':'' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Foto</label>
                    <input type="file" name="foto" accept="image/*" onchange="previewFoto(this)" />
                    <img id="preview" class="foto-preview" />
                    @error('foto')<div class="error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ===== DATA JABATAN & STRUKTUR ===== --}}
            <div class="section-label">🏢 Data Jabatan & Struktur</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Jabatan *</label>
                    <select name="jabatan_id">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $j)
                            <option value="{{ $j->id }}" {{ old('jabatan_id')==$j->id?'selected':'' }}>{{ $j->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    @error('jabatan_id')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Jabatan Saat Ini</label>
                    <input type="text" name="jabatan_saat_ini" value="{{ old('jabatan_saat_ini') }}"
                           placeholder="cth: Associate Officer Talenta Manajemen" />
                    <span class="hint">Jabatan lengkap yang ditampilkan di profil karyawan</span>
                    @error('jabatan_saat_ini')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Direktorat *</label>
                    <select name="direktorat_id">
                        <option value="">-- Pilih Direktorat --</option>
                        @foreach($direktorats as $d)
                            <option value="{{ $d->id }}" {{ old('direktorat_id')==$d->id?'selected':'' }}>{{ $d->nama_direktorat }}</option>
                        @endforeach
                    </select>
                    @error('direktorat_id')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Kompartemen *</label>
                    <select name="kompartemen_id">
                        <option value="">-- Pilih Kompartemen --</option>
                        @foreach($kompartemens as $k)
                            <option value="{{ $k->id }}" {{ old('kompartemen_id')==$k->id?'selected':'' }}>{{ $k->nama_kompartemen }}</option>
                        @endforeach
                    </select>
                    @error('kompartemen_id')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Departemen *</label>
                    <select name="departemen_id">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departemens as $d)
                            <option value="{{ $d->id }}" {{ old('departemen_id')==$d->id?'selected':'' }}>{{ $d->nama_departemen }}</option>
                        @endforeach
                    </select>
                    @error('departemen_id')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Kode Struktur *</label>
                    <select name="kode_struktur_id">
                        <option value="">-- Pilih Kode Struktur --</option>
                        @foreach($kodeStrukturs as $ks)
                            <option value="{{ $ks->id }}" {{ old('kode_struktur_id')==$ks->id?'selected':'' }}>{{ $ks->kode_struktur }}</option>
                        @endforeach
                    </select>
                    @error('kode_struktur_id')<div class="error">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ===== BAND & GRADE ===== --}}
            <div class="section-label">⭐ Band & Grade</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Job Grade *</label>
                    <select name="job_grade_id" id="jobGradeSelect" onchange="updateBand()">
                        <option value="">-- Pilih Job Grade --</option>
                        @foreach($jobGrades as $j)
                            <option value="{{ $j->id }}"
                                    data-grade="{{ $j->job_grade }}"
                                    {{ old('job_grade_id')==$j->id?'selected':'' }}>
                                JG {{ $j->job_grade }}
                            </option>
                        @endforeach
                    </select>
                    @error('job_grade_id')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Person Grade *</label>
                    <select name="person_grade_id">
                        <option value="">-- Pilih Person Grade --</option>
                        @foreach($personGrades as $p)
                            <option value="{{ $p->id }}" {{ old('person_grade_id')==$p->id?'selected':'' }}>
                                PG {{ $p->person_grade }}
                            </option>
                        @endforeach
                    </select>
                    @error('person_grade_id')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Band</label>
                    <input type="text" id="bandDisplay" readonly value="-" />
                    <span class="hint">Dihitung otomatis dari Job Grade</span>
                </div>
            </div>

            {{-- MDG Info --}}
            <div class="mdg-info">
                <strong>📋 Ketentuan Masa Dinas Grade (MDG):</strong>
                <ul>
                    <li>Naik <strong>Person Grade</strong> → min <strong>1 tahun</strong> TMT PG saat ini</li>
                    <li>Naik <strong>Job Grade</strong> → min <strong>2 tahun</strong> TMT JG saat ini (PG harus = JG)</li>
                    <li>Naik <strong>Band</strong> → MDG JG min <strong>2 tahun</strong>, MDG PG min <strong>1 tahun</strong>, MDG Band min <strong>3 tahun</strong> (dihitung dari TMT JG saat masuk band)</li>
                </ul>
            </div>

            {{-- ===== TMT GRADE ===== --}}
            <div class="section-label" style="margin-top:20px;">📅 TMT Grade</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>TMT Job Grade</label>
                    <input type="date" name="tanggal_mulai_jg" value="{{ old('tanggal_mulai_jg') }}" />
                    <span class="hint">Tanggal mulai di Job Grade saat ini (opsional)</span>
                    @error('tanggal_mulai_jg')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>TMT Person Grade</label>
                    <input type="date" name="tanggal_mulai_pg" value="{{ old('tanggal_mulai_pg') }}" />
                    <span class="hint">Tanggal mulai di Person Grade saat ini (opsional)</span>
                    @error('tanggal_mulai_pg')<div class="error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('karyawan.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Karyawan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewFoto(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}

const bandMap = {
    22:'Band 1', 21:'Band 1', 20:'Band 1',
    19:'Band 2', 18:'Band 2', 17:'Band 2',
    16:'Band 3', 15:'Band 3',
    14:'Band 4', 13:'Band 4',
    12:'Band 5', 11:'Band 5', 10:'Band 5',
     9:'Band 6',  8:'Band 6',  7:'Band 6',
};

function updateBand() {
    const sel   = document.getElementById('jobGradeSelect');
    const opt   = sel.options[sel.selectedIndex];
    const grade = parseInt(opt.dataset.grade);
    document.getElementById('bandDisplay').value = bandMap[grade] || '-';
}

window.addEventListener('DOMContentLoaded', updateBand);
</script>
@endpush