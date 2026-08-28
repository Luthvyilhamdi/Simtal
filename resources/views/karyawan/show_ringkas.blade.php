{{--
    Profil Karyawan versi RINGKAS — dipakai role 'user'.

    Sengaja hanya memuat data penempatan organisasi dan kontak kerja, yaitu
    informasi yang memang sudah terlihat di Struktur Organisasi. Data pribadi
    (tanggal lahir, usia, status kepegawaian), TMT, MDG, riwayat jabatan,
    assessment, dan pendidikan TIDAK ditampilkan di sini.

    Halaman lengkapnya ada di karyawan/show.blade.php untuk admin & super admin.
--}}
@extends('layouts.app')
@section('title', $karyawan->nama)
@section('breadcrumb-parent', 'Data Karyawan')
@section('breadcrumb', $karyawan->nama)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#667085;text-decoration:none;margin-bottom:16px;font-weight:500; }
    .back-link:hover { color:var(--brand); }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2.2; }

    .profil-head {
        display:flex;align-items:center;gap:18px;flex-wrap:wrap;
        background:#fff;border:1px solid var(--card-border);border-radius:var(--radius);
        box-shadow:var(--card-shadow);padding:22px 24px;margin-bottom:16px;
    }
    .profil-avatar {
        width:66px;height:66px;border-radius:18px;background:#f0fdf4;color:var(--brand);
        display:flex;align-items:center;justify-content:center;
        font-size:22px;font-weight:700;flex-shrink:0;overflow:hidden;
    }
    .profil-avatar img { width:100%;height:100%;object-fit:cover; }
    .profil-nama { font-size:20px;font-weight:700;color:var(--text-strong);line-height:1.25; }
    .profil-jabatan { font-size:13px;color:var(--text-muted);margin-top:3px;max-width:60ch; }
    .profil-nik {
        display:inline-flex;align-items:center;gap:6px;margin-top:8px;
        font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);
        background:#f4f5f7;border:1px solid var(--card-border);border-radius:20px;padding:3px 11px;
    }

    .kartu {
        background:#fff;border:1px solid var(--card-border);border-radius:var(--radius);
        box-shadow:var(--card-shadow);padding:20px 22px;
    }
    .kartu + .kartu { margin-top:14px; }
    .kartu-judul {
        font-size:var(--fs-xs);font-weight:700;color:var(--text-faint);
        text-transform:uppercase;letter-spacing:.6px;
        margin-bottom:14px;padding-bottom:11px;border-bottom:1px solid var(--divider);
        display:flex;align-items:center;gap:9px;
    }
    .kartu-ikon {
        width:26px;height:26px;border-radius:8px;background:#f0fdf4;
        display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .kartu-ikon svg { width:14px;height:14px;stroke:var(--brand);fill:none;stroke-width:2; }
    .kartu-ikon.biru { background:#eff6ff; } .kartu-ikon.biru svg { stroke:#1d4ed8; }
    .kartu-ikon.ungu { background:#f5f3ff; } .kartu-ikon.ungu svg { stroke:#7c3aed; }

    .baris { display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:9px 0;border-bottom:1px solid #f7f8f9; }
    .baris:last-child { border-bottom:none;padding-bottom:0; }
    .baris-label { font-size:var(--fs-sm);color:var(--text-muted);flex-shrink:0; }
    .baris-nilai { font-size:var(--fs-body);font-weight:600;color:var(--text-strong);text-align:right;word-break:break-word; }
    .baris-nilai a { color:var(--brand);text-decoration:none; }
    .baris-nilai a:hover { text-decoration:underline; }
    .kosong { color:var(--text-faint);font-weight:400; }

    .pil { display:inline-flex;padding:3px 11px;border-radius:20px;font-size:var(--fs-xs);font-weight:700; }
    .pil-hijau { background:#f0fdf4;color:var(--brand);border:1px solid #bbf7d0; }
    .pil-abu   { background:#f4f5f7;color:#5b6472;border:1px solid #e4e7ec; }
    .pil-biru  { background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }

    .kolom { display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start; }
    .kolom .kartu + .kartu { margin-top:0; }
    @media (max-width:820px) { .kolom { grid-template-columns:minmax(0,1fr); } }
</style>
@endpush

@section('content')

<a href="{{ route('karyawan.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Profil Karyawan
</a>

<div class="profil-head">
    <div class="profil-avatar">
        @if($karyawan->foto)
            <img src="{{ asset('storage/' . $karyawan->foto) }}" alt="">
        @else
            {{ initials($karyawan->nama) }}
        @endif
    </div>
    <div style="min-width:0;">
        <div class="profil-nama">{{ $karyawan->nama }}</div>
        <div class="profil-jabatan">{{ $karyawan->jabatan_saat_ini ?: ($karyawan->jabatan->nama_jabatan ?? '-') }}</div>
        <span class="profil-nik">NIK {{ $karyawan->nik }}</span>
    </div>
</div>

<div class="kolom">

    <div>
        <div class="kartu">
            <div class="kartu-judul">
                <span class="kartu-ikon"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>
                Unit Organisasi
            </div>
            <div class="baris">
                <span class="baris-label">Direktorat</span>
                <span class="baris-nilai">{{ $karyawan->direktorat->nama_direktorat ?? null ?: '—' }}</span>
            </div>
            <div class="baris">
                <span class="baris-label">Kompartemen</span>
                <span class="baris-nilai">{{ $karyawan->kompartemen->nama_kompartemen ?? null ?: '—' }}</span>
            </div>
            <div class="baris">
                <span class="baris-label">Departemen</span>
                <span class="baris-nilai">{{ $karyawan->departemen->nama_departemen ?? null ?: '—' }}</span>
            </div>
        </div>

        <div class="kartu">
            <div class="kartu-judul">
                <span class="kartu-ikon biru"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.74-1.74a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z"/></svg></span>
                Kontak
            </div>
            <div class="baris">
                <span class="baris-label">No. HP</span>
                <span class="baris-nilai">
                    @if($karyawan->no_hp)
                        <a href="{{ $karyawan->whatsapp_url }}" target="_blank" rel="noopener" title="Chat via WhatsApp">{{ $karyawan->no_hp }}</a>
                    @else
                        <span class="kosong">—</span>
                    @endif
                </span>
            </div>
            <div class="baris">
                <span class="baris-label">Email</span>
                <span class="baris-nilai">
                    @if($karyawan->email)
                        <a href="mailto:{{ $karyawan->email }}">{{ $karyawan->email }}</a>
                    @else
                        <span class="kosong">—</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div>
        <div class="kartu">
            <div class="kartu-judul">
                <span class="kartu-ikon ungu"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
                Band &amp; Grade
            </div>
            <div class="baris">
                <span class="baris-label">Band</span>
                <span class="baris-nilai"><span class="pil pil-hijau">{{ $karyawan->band }}</span></span>
            </div>
            <div class="baris">
                <span class="baris-label">Job Grade</span>
                <span class="baris-nilai"><span class="pil pil-abu">JG {{ $karyawan->jobGrade->job_grade ?? '—' }}</span></span>
            </div>
            <div class="baris">
                <span class="baris-label">Person Grade</span>
                <span class="baris-nilai"><span class="pil pil-biru">PG {{ $karyawan->personGrade->person_grade ?? '—' }}</span></span>
            </div>
        </div>

        <div class="kartu">
            <div class="kartu-judul">
                <span class="kartu-ikon"><svg viewBox="0 0 24 24"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg></span>
                Jobs
            </div>
            {{-- Jobs/Job Stream/Job Family diwarisi dari penempatan Struktur Organisasi
                 periode terbaru (accessor di model Karyawan), bukan diisi manual. --}}
            <div class="baris">
                <span class="baris-label">Jobs</span>
                <span class="baris-nilai">{{ $karyawan->jobs ?: '—' }}</span>
            </div>
            <div class="baris">
                <span class="baris-label">Job Stream</span>
                <span class="baris-nilai">{{ $karyawan->job_stream ?: '—' }}</span>
            </div>
            <div class="baris">
                <span class="baris-label">Job Family</span>
                <span class="baris-nilai">{{ $karyawan->job_family ?: '—' }}</span>
            </div>
        </div>
    </div>

</div>

@endsection
