@extends('layouts.app')
@section('title', 'Detail Karyawan')
@section('breadcrumb-parent', 'Profil Karyawan')
@section('breadcrumb', $karyawan->nama)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .profile-card { background:white;border-radius:16px;border:1px solid var(--card-border);padding:24px;margin-bottom:20px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;box-shadow:var(--card-shadow); }
    .profile-left { display:flex;align-items:center;gap:18px;flex:1;min-width:0; }
    .profile-avatar { width:74px;height:74px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;overflow:hidden;border:3px solid #bbf7d0; }
    .profile-avatar img { width:100%;height:100%;object-fit:cover; }
    .profile-name { font-size:20px;font-weight:700;color:#111827;margin-bottom:3px; }
    .profile-jabatan { font-size:13px;color:#6b7280;margin-bottom:10px; }
    .profile-tags { display:flex;flex-wrap:wrap;gap:6px; }
    .profile-tag { display:inline-flex;align-items:center;gap:4px;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151; }
    .profile-tag.green { background:#dcfce7;color:#15803d; }

    .profile-stats { display:flex;gap:10px;flex-shrink:0; }
    .stat-item { text-align:center;background:#f9fafb;border:1px solid #f1f2ee;border-radius:12px;padding:12px 16px;min-width:82px; }
    .stat-num { font-size:23px;font-weight:800;color:#111827;line-height:1; }
    .stat-num.green { color:#15803d; }
    .stat-num.red { color:#e50909; }
    .stat-label { font-size:10.5px;color:#9ca3af;margin-top:5px;font-weight:600; }

    .action-row { display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap; }
    .btn-edit { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.15s; }
    .btn-edit:hover { background:#166534; }
    .btn-edit svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }
    .btn-outline { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #e4e7ec;transition:all 0.15s; }
    .btn-outline:hover { background:#f9fafb;border-color:#bbf7d0;color:#15803d; }
    .btn-outline svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }

    .detail-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px; }
    .detail-card { background:white;border-radius:14px;border:1px solid var(--card-border);padding:22px;box-shadow:var(--card-shadow); }
    .detail-card.full { grid-column:1/-1; }
    .detail-card-title { font-size:11px;font-weight:700;color:#98a2b3;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px; }
    .dct-ico { width:26px;height:26px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .dct-ico svg { width:14px;height:14px;stroke:#16a34a;fill:none;stroke-width:1.8; }
    .dct-ico.blue { background:#eff6ff; } .dct-ico.blue svg { stroke:#2563eb; }
    .dct-ico.purple { background:#f5f3ff; } .dct-ico.purple svg { stroke:#7c3aed; }
    .detail-row { display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f7f8f9; }
    .detail-row:last-child { border-bottom:none;padding-bottom:0; }
    .detail-label { font-size:12px;color:#98a2b3;font-weight:500;flex-shrink:0; }
    .detail-value { font-size:13px;color:#111827;font-weight:600;text-align:right;word-break:break-word; }
    .detail-value a { color:#15803d;text-decoration:none; }
    .muted { color:#d1d5db; }

    .foto-section { display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:16px 0; }
    .foto-large { width:120px;height:120px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:700;overflow:hidden;border:4px solid #bbf7d0; }
    .foto-large img { width:100%;height:100%;object-fit:cover; }
    .foto-name { font-size:15px;font-weight:700;color:#111827; }
    .foto-nik { font-size:12px;color:#9ca3af; }

    .badge { display:inline-flex;align-items:center;gap:4px;padding:3px 11px;border-radius:20px;font-size:11px;font-weight:600; }
    .badge-green  { background:#dcfce7;color:#15803d; }
    .badge-red    { background:#fee2e2;color:#dc2626; }
    .badge-gray   { background:#f3f4f6;color:#374151; }
    .badge-blue   { background:#eff6ff;color:#1d4ed8; }
    .badge-purple { background:#f5f3ff;color:#7c3aed; }
    .badge-amber  { background:#fffbeb;color:#d97706; }

    .band-card { background:white;border-radius:14px;border:1px solid var(--card-border);padding:22px;margin-bottom:16px;box-shadow:var(--card-shadow); }
    .mdg-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px; }
    .mdg-item { text-align:center;background:#f9fafb;border-radius:11px;padding:14px 10px; }
    .mdg-num { font-size:22px;font-weight:800;color:#111827; }
    .mdg-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-top:3px; }
    .mdg-since { font-size:10px;color:#d1d5db;margin-top:3px; }

    .mdg-ok   { color:#15803d; }
    .mdg-warn { color:#d97706; }
    .mdg-border-ok   { border:1.5px solid #bbf7d0; }
    .mdg-border-warn { border:1.5px solid #fde68a; }
    .mdg-since-shortlist { color:#15803d;font-weight:700; }
    .mdg-since-normal   { color:#6b7280; }

    .status-naik { border-radius:11px;padding:14px 16px;display:flex;align-items:flex-start;gap:12px;margin-bottom:16px; }
    .status-naik-ok   { background:#f0fdf4;border:1px solid #bbf7d0; }
    .status-naik-warn { background:#fffbeb;border:1px solid #fde68a; }
    .status-label-ok   { font-size:13px;font-weight:700;color:#15803d; }
    .status-label-warn { font-size:13px;font-weight:700;color:#d97706; }

    .syarat-wrap { display:flex;gap:8px;margin-top:10px;flex-wrap:wrap; }
    .syarat-item { display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:8px;font-size:11px; }
    .syarat-ok   { background:#f0fdf4;border:1px solid #bbf7d0; }
    .syarat-fail { background:#fef2f2;border:1px solid #fecaca; }
    .syarat-label-ok   { font-weight:600;color:#15803d; }
    .syarat-label-fail { font-weight:600;color:#dc2626; }

    .progress-wrap { display:flex;flex-direction:column;gap:11px; }
    .progress-label { display:flex;justify-content:space-between;font-size:11px;color:#6b7280;margin-bottom:5px; }
    .progress-bar { height:6px;background:#f3f4f6;border-radius:20px;overflow:hidden; }
    .progress-fill { height:100%;border-radius:20px;transition:width 0.5s; }

    @media (max-width:768px) {
        .detail-grid { grid-template-columns:1fr; }
        .detail-card.full { grid-column:1; }
        .profile-card { flex-direction:column; }
        .profile-stats { width:100%;justify-content:space-between;gap:8px; }
        .stat-item { flex:1;min-width:0; }
        .mdg-grid { grid-template-columns:1fr; }
    }
    @media (max-width:480px) {
        .profile-avatar { width:60px;height:60px;font-size:20px; }
        .profile-name { font-size:17px; }
        .stat-num { font-size:20px; }
        .action-row { flex-direction:column; }
        .btn-edit,.btn-outline { width:100%;justify-content:center; }
    }
</style>
@endpush

@section('content')

<a href="{{ route('karyawan.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Profil Karyawan
</a>

{{-- PROFILE CARD --}}
<div class="profile-card">
    <div class="profile-left">
        <div class="profile-avatar">
            @if($karyawan->foto)
                <img src="{{ Storage::url($karyawan->foto) }}" alt="{{ $karyawan->nama }}">
            @else
                {{ initials($karyawan->nama) }}
            @endif
        </div>
        <div>
            <div class="profile-name">{{ $karyawan->nama }}</div>
            <div class="profile-jabatan">{{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}</div>
            <div class="profile-tags">
                <span class="profile-tag green">NIK {{ $karyawan->nik }}</span>
                <span class="profile-tag">{{ $karyawan->departemen->nama_departemen ?? '-' }}</span>
                <span class="profile-tag">{{ $karyawan->band }}</span>
                <span class="profile-tag">Bergabung {{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->translatedFormat('M Y') }}</span>
                <span class="profile-tag">
                    @if($karyawan->status === 'aktif')
                        <span style="color:#15803d">● Aktif</span>
                    @else
                        <span style="color:#dc2626">● Tidak Aktif</span>
                    @endif
                </span>
                @if($isShortlist)
                <span class="profile-tag" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0">🟢 Shortlist {{ $shortlistPeriode }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="profile-stats">
        <div class="stat-item">
            <div class="stat-num green">{{ (int) \Carbon\Carbon::parse($karyawan->tanggal_masuk)->diffInYears(now()) }}</div>
            <div class="stat-label">Tahun Kerja</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age }}</div>
            <div class="stat-label">Usia</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">{{ $karyawan->personGrade->person_grade ?? '-' }}</div>
            <div class="stat-label">Person Grade</div>
        </div>
        <div class="stat-item">
            <div class="stat-num red" style="font-size:19px">{{ $karyawan->sisa_pensiun_label }}</div>
            <div class="stat-label">Sisa Masa Kerja</div>
        </div>
    </div>
</div>

{{-- ACTION BUTTONS --}}
<div class="action-row">
    <a href="{{ route('karyawan.edit', $karyawan) }}" class="btn-edit">
        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit Data Karyawan
    </a>
    <a href="{{ route('history_jabatan.index', $karyawan) }}" class="btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        History Jabatan
    </a>
    <a href="{{ route('history_assessment.index', $karyawan) }}" class="btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        History Assessment
    </a>
    <a href="{{ route('penilaian_karyawan.index', $karyawan) }}" class="btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        Penilaian
    </a>
    <a href="{{ route('kalibrasi_karyawan.index', $karyawan) }}" class="btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Kalibrasi
    </a>
</div>

{{-- DETAIL GRID --}}
<div class="detail-grid">

    {{-- Data Pribadi --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span class="dct-ico"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            Data Pribadi
        </div>
        <div class="detail-row"><span class="detail-label">NIK</span><span class="detail-value">{{ $karyawan->nik }}</span></div>
        <div class="detail-row"><span class="detail-label">Nama Lengkap</span><span class="detail-value">{{ $karyawan->nama }}</span></div>
        <div class="detail-row"><span class="detail-label">Jenis Kelamin</span><span class="detail-value">{{ $karyawan->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
        <div class="detail-row"><span class="detail-label">Tempat Lahir</span><span class="detail-value">{{ $karyawan->tempat_lahir ?: '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Tanggal Lahir</span><span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->translatedFormat('d F Y') }}</span></div>
        <div class="detail-row"><span class="detail-label">Usia</span><span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age }} tahun</span></div>
        <div class="detail-row"><span class="detail-label">Tanggal Masuk</span><span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->translatedFormat('d F Y') }}</span></div>
        <div class="detail-row"><span class="detail-label">Lama Bekerja</span><span class="detail-value">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->diffForHumans(null, true) }}</span></div>
        <div class="detail-row"><span class="detail-label">Status Kepegawaian</span><span class="detail-value">@if($karyawan->status_kepegawaian)<span class="badge badge-purple">{{ $karyawan->status_kepegawaian }}</span>@else<span class="muted">-</span>@endif</span></div>
    </div>

    {{-- Kontak & Pendidikan --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span class="dct-ico blue"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.74-1.74a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z"/></svg></span>
            Kontak & Pendidikan
        </div>
        <div class="detail-row">
            <span class="detail-label">No. HP</span>
            <span class="detail-value">@if($karyawan->no_hp)<a href="{{ $karyawan->whatsapp_url }}" target="_blank" rel="noopener" title="Chat via WhatsApp">{{ $karyawan->no_hp }}</a>@else<span class="muted">-</span>@endif</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value">@if($karyawan->email)<a href="mailto:{{ $karyawan->email }}">{{ $karyawan->email }}</a>@else<span class="muted">-</span>@endif</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Pendidikan Terakhir</span>
            <span class="detail-value">@if($karyawan->jenjang_pendidikan)<span class="badge badge-blue">{{ $karyawan->jenjang_pendidikan }}</span>@if($karyawan->jurusan) <span style="color:#6b7280;">· {{ $karyawan->jurusan }}</span>@endif @else<span class="muted">-</span>@endif</span>
        </div>
        <div style="padding:12px 0 2px;border-top:1px solid #f7f8f9;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:#98a2b3;font-weight:500;">History Pendidikan</span>
                <a href="{{ route('riwayat_pendidikan.index', $karyawan) }}" style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#15803d;text-decoration:none;">
                    <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Kelola
                </a>
            </div>
            @forelse($karyawan->riwayatPendidikan->sortBy(fn($r) => array_search($r->jenjang, \App\Models\Karyawan::JENJANG_PENDIDIKAN)) as $rp)
            <div style="font-size:13px;color:#111827;margin-bottom:6px;display:flex;gap:8px;align-items:baseline;">
                <span class="badge badge-blue" style="flex-shrink:0;">{{ $rp->jenjang }}</span>
                <span>{{ $rp->jurusan ?: '-' }}@if($rp->institusi) <span style="color:#9ca3af;">· {{ $rp->institusi }}</span>@endif</span>
            </div>
            @empty
            <div style="font-size:12.5px;color:#9ca3af;font-style:italic;">Belum ada history pendidikan. Klik "Kelola" untuk menambahkan.</div>
            @endforelse
        </div>
    </div>

    {{-- Jabatan & Struktur --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span class="dct-ico"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>
            Jabatan & Struktur
        </div>
        <div class="detail-row"><span class="detail-label">Jabatan</span><span class="detail-value">{{ $karyawan->jabatan->nama_jabatan ?? '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Jabatan Saat Ini</span><span class="detail-value">{{ $karyawan->jabatan_saat_ini ?: '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Jobs</span><span class="detail-value">{{ $karyawan->jobs ?: '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Job Stream</span><span class="detail-value">{{ $karyawan->job_stream ?: '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Direktorat</span><span class="detail-value">{{ $karyawan->direktorat->nama_direktorat ?? '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Kompartemen</span><span class="detail-value">{{ $karyawan->kompartemen->nama_kompartemen ?? '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Departemen</span><span class="detail-value">{{ $karyawan->departemen->nama_departemen ?? '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">Kode Struktur</span><span class="detail-value"><span class="badge badge-purple">{{ $karyawan->kodeStruktur->kode_struktur ?? '-' }}</span></span></div>
        <div class="detail-row">
            <span class="detail-label">Struktural/Fungsional</span>
            <span class="detail-value">
                @if($karyawan->struktural_fungsional)
                    <span class="badge {{ $karyawan->struktural_fungsional === 'Struktural' ? 'badge-blue' : 'badge-amber' }}">{{ $karyawan->struktural_fungsional }}</span>
                @else <span class="muted">-</span> @endif
            </span>
        </div>
    </div>

    {{-- Grade & Band --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span class="dct-ico purple"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
            Grade & Band
        </div>
        <div class="detail-row"><span class="detail-label">Band</span><span class="detail-value"><span class="badge badge-green">{{ $karyawan->band }}</span></span></div>
        <div class="detail-row"><span class="detail-label">Job Grade</span><span class="detail-value"><span class="badge badge-gray">JG {{ $karyawan->jobGrade->job_grade ?? '-' }}</span></span></div>
        <div class="detail-row"><span class="detail-label">Person Grade</span><span class="detail-value"><span class="badge badge-blue">PG {{ $karyawan->personGrade->person_grade ?? '-' }}</span></span></div>
        <div class="detail-row"><span class="detail-label">TMT Job Grade</span><span class="detail-value">{{ $karyawan->tanggal_mulai_jg ? $karyawan->tanggal_mulai_jg->format('d M Y') : '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">TMT Person Grade</span><span class="detail-value">{{ $karyawan->tanggal_mulai_pg ? $karyawan->tanggal_mulai_pg->format('d M Y') : '-' }}</span></div>
        <div class="detail-row"><span class="detail-label">TMT Band</span><span class="detail-value">{{ $karyawan->tanggal_mulai_band ? $karyawan->tanggal_mulai_band->format('d M Y') : '-' }}</span></div>
    </div>

</div>

{{-- BAND & MDG CARD --}}
@php
    $sk = $karyawan->statusKenaikan;

    $minBand   = $isShortlist ? 24 : 36;
    $minJg     = $isShortlist ? 12 : 24;
    $minPg     = 12;
    $minBandTh = $isShortlist ? 2 : 3;
    $minJgTh   = $isShortlist ? 1 : 2;
    $minPgTh   = 1;

    $jgOk   = ($karyawan->mdg_jg_bulan   ?? 0) >= $minJg;
    $pgOk   = ($karyawan->mdg_pg_bulan   ?? 0) >= $minPg;
    $bandOk = ($karyawan->mdg_band_bulan ?? 0) >= $minBand;

    $pgPct   = min(100, (($karyawan->mdg_pg_bulan   ?? 0) / $minPg)   * 100);
    $jgPct   = min(100, (($karyawan->mdg_jg_bulan   ?? 0) / $minJg)   * 100);
    $bandPct = min(100, (($karyawan->mdg_band_bulan ?? 0) / $minBand) * 100);
@endphp

<div class="band-card">
    <div class="detail-card-title" style="flex-wrap:wrap;gap:8px">
        <span class="dct-ico"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></span>
        Band & Masa Dinas Grade (MDG)
        @if($isShortlist)
        <span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:none;letter-spacing:0;border:1px solid #bbf7d0">
            🟢 Shortlist {{ $shortlistPeriode }} — Ketentuan MDG Khusus
        </span>
        @endif
    </div>

    <div class="status-naik {{ $sk['eligible'] ? 'status-naik-ok' : 'status-naik-warn' }}">
        <div style="font-size:28px;flex-shrink:0;">{{ $sk['eligible'] ? '✅' : '⏳' }}</div>
        <div style="flex:1;min-width:0;">
            <div class="{{ $sk['eligible'] ? 'status-label-ok' : 'status-label-warn' }}">
                {{ $sk['eligible'] ? 'ELIGIBLE — ' : 'Belum Eligible — ' }}{{ $sk['label'] }}
            </div>
            @if(!$sk['eligible'] && $sk['sisa_bulan'] > 0)
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">
                    Sisa <strong>{{ $sk['sisa_bulan'] }} bulan</strong> lagi untuk syarat terlama
                </div>
            @endif
            @if(!empty($sk['syarat']))
            <div class="syarat-wrap">
                @foreach($sk['syarat'] as $syarat)
                <div class="syarat-item {{ $syarat['terpenuhi'] ? 'syarat-ok' : 'syarat-fail' }}">
                    <span>{{ $syarat['terpenuhi'] ? '✅' : '❌' }}</span>
                    <span class="{{ $syarat['terpenuhi'] ? 'syarat-label-ok' : 'syarat-label-fail' }}">{{ $syarat['label'] }}</span>
                    <span style="color:#6b7280;">({{ $syarat['mdg'] }}/{{ $syarat['min'] }} bln)</span>
                </div>
                @endforeach
            </div>
            @endif
            @if(!empty($sk['blokir_info']))
                <div style="font-size:12px;color:#dc2626;margin-top:6px;">🔒 {{ $sk['blokir_info'] }}</div>
            @endif
        </div>
    </div>

    <div class="mdg-grid">
        <div class="mdg-item {{ $jgOk ? 'mdg-border-ok' : 'mdg-border-warn' }}">
            <div class="mdg-label">MDG Job Grade</div>
            <div class="mdg-num {{ $jgOk ? 'mdg-ok' : 'mdg-warn' }}" style="font-size:15px;line-height:1.3">{{ $karyawan->mdg_jg_lengkap }}</div>
            @if($karyawan->tanggal_mulai_jg)<div class="mdg-since">sejak {{ $karyawan->tanggal_mulai_jg->format('d M Y') }}</div>@endif
            <div class="mdg-since {{ $isShortlist ? 'mdg-since-shortlist' : 'mdg-since-normal' }}">min {{ $minJgTh }} tahun{{ $isShortlist ? ' ✦' : '' }}</div>
        </div>
        <div class="mdg-item {{ $pgOk ? 'mdg-border-ok' : 'mdg-border-warn' }}">
            <div class="mdg-label">MDG Person Grade</div>
            <div class="mdg-num {{ $pgOk ? 'mdg-ok' : 'mdg-warn' }}" style="font-size:15px;line-height:1.3">{{ $karyawan->mdg_pg_lengkap }}</div>
            @if($karyawan->tanggal_mulai_pg)<div class="mdg-since">sejak {{ $karyawan->tanggal_mulai_pg->format('d M Y') }}</div>@endif
            <div class="mdg-since mdg-since-normal">min {{ $minPgTh }} tahun</div>
        </div>
        <div class="mdg-item {{ $bandOk ? 'mdg-border-ok' : 'mdg-border-warn' }}">
            <div class="mdg-label">MDG Band</div>
            <div class="mdg-num {{ $bandOk ? 'mdg-ok' : 'mdg-warn' }}" style="font-size:15px;line-height:1.3">{{ $karyawan->mdg_band_lengkap }}</div>
            @if($karyawan->tanggal_mulai_band ?? $karyawan->tanggal_mulai_jg)<div class="mdg-since">sejak {{ ($karyawan->tanggal_mulai_band ?? $karyawan->tanggal_mulai_jg)->format('d M Y') }}</div>@endif
            <div class="mdg-since {{ $isShortlist ? 'mdg-since-shortlist' : 'mdg-since-normal' }}">min {{ $minBandTh }} tahun{{ $isShortlist ? ' ✦' : '' }}</div>
        </div>
    </div>

    @if($isShortlist)
    <div style="font-size:11px;color:#15803d;margin-bottom:12px">✦ Ketentuan MDG khusus Shortlist Talent Pool {{ $shortlistPeriode }}</div>
    @endif

    <div class="progress-wrap">
        <div class="progress-item">
            <div class="progress-label"><span>MDG Person Grade</span><span>{{ $karyawan->mdg_pg_bulan ?? 0 }} / {{ $minPg }} bulan</span></div>
            <div class="progress-bar"><div class="progress-fill" data-pct="{{ $pgPct }}" data-color="{{ $pgPct >= 100 ? '#15803d' : '#f59e0b' }}"></div></div>
        </div>
        <div class="progress-item">
            <div class="progress-label"><span>MDG Job Grade</span><span>{{ $karyawan->mdg_jg_bulan ?? 0 }} / {{ $minJg }} bulan</span></div>
            <div class="progress-bar"><div class="progress-fill" data-pct="{{ $jgPct }}" data-color="{{ $jgPct >= 100 ? '#15803d' : '#3b82f6' }}"></div></div>
        </div>
        <div class="progress-item">
            <div class="progress-label"><span>MDG Band (dari TMT JG)</span><span>{{ $karyawan->mdg_band_bulan ?? 0 }} / {{ $minBand }} bulan</span></div>
            <div class="progress-bar"><div class="progress-fill" data-pct="{{ $bandPct }}" data-color="{{ $bandPct >= 100 ? '#15803d' : '#7c3aed' }}"></div></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.progress-fill[data-pct]').forEach(function(el) {
        el.style.width = el.dataset.pct + '%';
        el.style.background = el.dataset.color;
    });
});
</script>
@endpush
