@extends('layouts.app')
@section('title', 'Edit Profil')
@section('breadcrumb', 'Profil Saya')

@push('styles')
<style>
    .profile-wrap { max-width: 100%; }
    .page-title { font-size:20px;font-weight:700;color:#111827;margin-bottom:4px; }
    .page-sub { font-size:13px;color:#6b7280;margin-bottom:24px; }

    /* Profile Header Card */
    .profile-hero {
        background: linear-gradient(135deg, #14532d 0%, #15803d 100%);
        border-radius: 16px; padding: 28px; margin-bottom: 20px;
        color: white; display: flex; align-items: center; gap: 20px;
        flex-wrap: wrap; position: relative; overflow: hidden;
    }
    .profile-hero::before {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 180px; height: 180px; border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .hero-avatar {
        width: 72px; height: 72px; border-radius: 50%;
        background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.4);
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; font-weight: 800; flex-shrink: 0;
        overflow: hidden;
    }
    .hero-avatar img { width:100%;height:100%;object-fit:cover; }
    .hero-name { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .hero-email { font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 8px; }
    .hero-badges { display: flex; gap: 8px; flex-wrap: wrap; }
    .hero-badge { padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.15); }

    /* Card */
    .form-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:24px;margin-bottom:16px; }
    .card-header-row { display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f3f4f6; }
    .card-icon { width:34px;height:34px;border-radius:9px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .card-icon svg { width:16px;height:16px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .card-icon.blue { background:#eff6ff; }
    .card-icon.blue svg { stroke:#2563eb; }
    .card-icon.red { background:#fef2f2; }
    .card-icon.red svg { stroke:#ef4444; }
    .card-icon.purple { background:#f5f3ff; }
    .card-icon.purple svg { stroke:#7c3aed; }
    .card-title { font-size:14px;font-weight:700;color:#111827; }
    .card-sub { font-size:12px;color:#9ca3af;margin-top:1px; }

    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    .form-group { display:flex;flex-direction:column;gap:6px; }
    .form-group.full { grid-column:1/-1; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all 0.15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,0.08); }
    .form-input[readonly] { background:#f3f4f6;color:#6b7280;cursor:not-allowed; }
    .form-input.error-input { border-color:#ef4444; }
    .error-msg { font-size:11px;color:#ef4444; }
    .form-hint { font-size:11px;color:#9ca3af;margin-top:2px; }

    .btn-save { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all 0.15s; }
    .btn-save:hover { background:#166534; }
    .btn-save svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }

    /* Karyawan Info Card */
    .karyawan-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden;margin-bottom:16px; }
    .karyawan-card-header { padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between; }
    .karyawan-info-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0; }
    .karyawan-info-item { padding:14px 20px;border-right:1px solid #f3f4f6;border-bottom:1px solid #f3f4f6; }
    .karyawan-info-item:last-child { border-right:none; }
    .ki-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:4px; }
    .ki-val { font-size:13px;color:#111827;font-weight:600; }

    /* Stats mini */
    .stats-mini { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:16px 20px; }
    .stat-mini { text-align:center;padding:12px;background:#f9fafb;border-radius:10px; }
    .stat-mini-num { font-size:22px;font-weight:800;color:#111827; }
    .stat-mini-label { font-size:11px;color:#9ca3af;font-weight:600;margin-top:2px; }

    /* Not linked box */
    .not-linked { background:#f9fafb;border:1px dashed #e5e7eb;border-radius:12px;padding:24px;text-align:center;margin-bottom:16px; }

    /* Toast */
    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    @media (max-width:640px) {
        .form-grid { grid-template-columns:1fr; }
        .form-group.full { grid-column:1; }
        .stats-mini { grid-template-columns:repeat(2,1fr); }
        .karyawan-info-grid { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush

@section('content')

{{-- Toast Success --}}
@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif
@if(session('success_password'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success_password') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

<div class="profile-wrap">

    {{-- Hero Card --}}
    <div class="profile-hero">
        <div class="hero-avatar" style="position:relative;z-index:1;">
            @if($karyawan && $karyawan->foto)
                <img src="{{ Storage::url($karyawan->foto) }}" alt="">
            @else
                {{ strtoupper(substr($user->name, 0, 2)) }}
            @endif
        </div>
        <div style="position:relative;z-index:1;">
            <div class="hero-name">{{ $user->name }}</div>
            <div class="hero-email">{{ $user->email }}</div>
            <div class="hero-badges">
                <span class="hero-badge">
                    {{ $user->isSuperAdmin() ? '⭐ Super Admin' : '🔵 Administrator' }}
                </span>
                @if($karyawan)
                    <span class="hero-badge">👤 NIK {{ $karyawan->nik }}</span>
                    <span class="hero-badge">📌 {{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}</span>
                @else
                    <span class="hero-badge">🔗 Belum terhubung ke data karyawan</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Data Karyawan Terintegrasi --}}
    @if($karyawan)
    <div class="karyawan-card">
        <div class="karyawan-card-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <div class="card-title">Data Karyawan Terintegrasi</div>
                    <div class="card-sub">Informasi dari profil karyawan SIMTAL</div>
                </div>
            </div>
            <a href="{{ route('karyawan.show', $karyawan) }}"
               style="font-size:12px;color:#16a34a;text-decoration:none;font-weight:600;">
                Lihat Profil →
            </a>
        </div>

        {{-- Info Grid --}}
        <div class="karyawan-info-grid">
            <div class="karyawan-info-item">
                <div class="ki-label">Jabatan Saat Ini</div>
                <div class="ki-val">{{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}</div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Direktorat</div>
                <div class="ki-val">{{ $karyawan->direktorat->nama_direktorat ?? '-' }}</div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Departemen</div>
                <div class="ki-val">{{ $karyawan->departemen->nama_departemen ?? '-' }}</div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Job Grade</div>
                <div class="ki-val">{{ $karyawan->jobGrade->job_grade ?? '-' }}</div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Person Grade</div>
                <div class="ki-val">{{ $karyawan->personGrade->person_grade ?? '-' }}</div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Status</div>
                <div class="ki-val">
                    @if($karyawan->status === 'aktif')
                        <span style="color:#15803d;">● Aktif</span>
                    @else
                        <span style="color:#dc2626;">● Tidak Aktif</span>
                    @endif
                </div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Tanggal Masuk</div>
                <div class="ki-val">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->format('d M Y') }}</div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Masa Kerja</div>
                <div class="ki-val">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->diffInYears(now()) }} Tahun</div>
            </div>
            <div class="karyawan-info-item">
                <div class="ki-label">Usia</div>
                <div class="ki-val">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age }} Tahun</div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stats-mini">
            <div class="stat-mini">
                <div class="stat-mini-num" style="color:#15803d;">{{ $karyawan->historyJabatan->count() }}</div>
                <div class="stat-mini-label">History Jabatan</div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-num" style="color:#7c3aed;">{{ $karyawan->historyAssessment->count() }}</div>
                <div class="stat-mini-label">Total Assessment</div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-num" style="color:#d97706;">{{ $karyawan->historyJabatan->where('tipe','promosi')->count() }}</div>
                <div class="stat-mini-label">Total Promosi</div>
            </div>
        </div>
    </div>

    @else
    {{-- Belum terhubung --}}
    <div class="not-linked">
        <div style="font-size:32px;margin-bottom:10px;">🔗</div>
        <div style="font-size:14px;font-weight:700;color:#374151;margin-bottom:4px;">Akun belum terhubung ke data karyawan</div>
        <div style="font-size:12px;color:#9ca3af;">Masukkan NIK karyawan di form Edit Profil untuk menghubungkan akun dengan data karyawan</div>
    </div>
    @endif

    {{-- Form Edit Profil --}}
    <div class="form-card">
        <div class="card-header-row">
            <div class="card-icon blue">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div>
                <div class="card-title">Edit Profil</div>
                <div class="card-sub">Perbarui informasi akun dan hubungkan dengan data karyawan</div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-input {{ $errors->has('name') ? 'error-input' : '' }}" required />
                    @error('name')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-input {{ $errors->has('email') ? 'error-input' : '' }}" required />
                    @error('email')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">NIK Karyawan</label>
                    <input type="text" name="nik" value="{{ old('nik', $user->nik) }}"
                           class="form-input" placeholder="Masukkan NIK untuk terhubung ke data karyawan" />
                    <span class="form-hint">
                        @if($karyawan)
                            ✅ Terhubung dengan {{ $karyawan->nama }}
                        @else
                            Isi NIK untuk menghubungkan akun dengan profil karyawan
                        @endif
                    </span>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-input" readonly
                           value="{{ $user->isSuperAdmin() ? 'Super Admin' : 'Administrator' }}" />
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="btn-save">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Ganti Password --}}
    <div class="form-card">
        <div class="card-header-row">
            <div class="card-icon red">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <div class="card-title">Ganti Password</div>
                <div class="card-sub">Pastikan password baru minimal 8 karakter</div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PATCH')
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label">Password Lama *</label>
                    <input type="password" name="current_password"
                           class="form-input {{ $errors->has('current_password') ? 'error-input' : '' }}"
                           placeholder="Masukkan password lama" required />
                    @error('current_password')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru *</label>
                    <input type="password" name="password"
                           class="form-input {{ $errors->has('password') ? 'error-input' : '' }}"
                           placeholder="Min. 8 karakter" required />
                    @error('password')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru *</label>
                    <input type="password" name="password_confirmation"
                           class="form-input" placeholder="Ulangi password baru" required />
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="btn-save" style="background:#ef4444;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                    <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Ganti Password
                </button>
            </div>
        </form>
    </div>

</div>

@endsection

@push('scripts')
<script>
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000);
    });
</script>
@endpush