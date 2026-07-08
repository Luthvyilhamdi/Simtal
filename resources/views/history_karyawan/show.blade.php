@extends('layouts.app')
@section('title', 'History ' . $karyawan->nama)
@section('breadcrumb-parent', 'History Karyawan')
@section('breadcrumb', $karyawan->nama)

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .profile-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px; }
    .profile-left { display:flex;align-items:center;gap:14px; }
    .profile-avatar { width:52px;height:52px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;flex-shrink:0;overflow:hidden;border:2px solid #bbf7d0; }
    .profile-avatar img { width:100%;height:100%;object-fit:cover; }
    .profile-name { font-size:16px;font-weight:700;color:#111827; }
    .profile-meta { font-size:12px;color:#6b7280;margin-top:3px; }
    .profile-tags { display:flex;gap:6px;flex-wrap:wrap;margin-top:6px; }
    .profile-tag { padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151; }
    .profile-tag.green { background:#dcfce7;color:#15803d; }
    .profile-stats { display:flex;gap:24px; }
    .stat { text-align:center; }
    .stat-num { font-size:22px;font-weight:700;color:#111827; }
    .stat-num.green { color:#15803d; }
    .stat-label { font-size:11px;color:#9ca3af;margin-top:2px; }

    .section-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px; }
    .section-title { font-size:16px;font-weight:700;color:#111827; }
    .section-sub { font-size:13px;color:#6b7280;margin-top:2px; }

    /* Timeline */
    .timeline { position:relative;padding-left:32px; }
    .timeline::before { content:'';position:absolute;left:10px;top:0;bottom:0;width:2px;background:#e5e7eb; }
    .timeline-item { position:relative;margin-bottom:16px; }
    .timeline-dot { position:absolute;left:-26px;top:20px;width:12px;height:12px;border-radius:50%;border:2px solid white;box-shadow:0 0 0 2px #e5e7eb;background:#9ca3af;z-index:1; }
    .timeline-dot.current { background:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,0.2);width:14px;height:14px;left:-27px; }

    .timeline-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 20px;transition:box-shadow 0.15s; }
    .timeline-card:hover { box-shadow:var(--card-shadow-hover); }
    .timeline-card.current-card { border-color:#bbf7d0;background:#fafffe; }

    .card-header { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap; }
    .card-tipe { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:6px; }
    .tipe-promosi { background:#dcfce7;color:#15803d; }
    .tipe-mutasi { background:#dbeafe;color:#1d4ed8; }
    .tipe-rotasi { background:#ecfeff;color:#0891b2; }
    .tipe-demosi { background:#fee2e2;color:#dc2626; }
    .tipe-onboarding { background:#fef3c7;color:#d97706; }
    .card-jabatan { font-size:15px;font-weight:700;color:#111827; }
    .card-dept { font-size:12px;color:#6b7280;margin-top:2px; }
    .badge-current { display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;background:#dcfce7;color:#15803d;font-size:11px;font-weight:700; }
    .badge-current::before { content:'●';font-size:8px; }

    .card-details { display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:12px; }
    .detail-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px; }
    .detail-val { font-size:12px;color:#374151;font-weight:600;margin-top:2px; }

    .card-period { display:flex;align-items:center;gap:8px;padding-top:12px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280; }
    .card-period svg { width:13px;height:13px;stroke:#9ca3af;fill:none;stroke-width:2; }
    .card-period strong { color:#374151; }
    .period-badge { margin-left:auto;padding:2px 8px;border-radius:6px;background:#f3f4f6;font-size:11px;color:#6b7280;font-weight:600; }
    .card-keterangan { margin-top:10px;padding:10px 12px;background:#f9fafb;border-radius:8px;font-size:12px;color:#6b7280;font-style:italic;border-left:3px solid #e5e7eb; }

    .empty-state { text-align:center;padding:60px 20px;color:#9ca3af; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none; }

    @media (max-width:640px) {
        .profile-card { flex-direction:column; }
        .profile-stats { width:100%;justify-content:space-around;border-top:1px solid #f3f4f6;padding-top:12px; }
        .card-details { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush

@section('content')

<a href="{{ route('history_karyawan.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke History Karyawan
</a>

{{-- Profile Card (read only) --}}
<div class="profile-card">
    <div class="profile-left">
        <div class="profile-avatar">
            @if($karyawan->foto)
                <img src="{{ Storage::url($karyawan->foto) }}" alt="">
            @else
                {{ initials($karyawan->nama) }}
            @endif
        </div>
        <div>
            <div class="profile-name">{{ $karyawan->nama }}</div>
            {{-- SESUDAH --}}
<div class="profile-meta">{{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? 'Belum ada jabatan' }}</div>
            <div class="profile-tags">
                <span class="profile-tag green">NIK {{ $karyawan->nik }}</span>
                <span class="profile-tag">{{ $karyawan->departemen->nama_departemen ?? '-' }}</span>
                <span class="profile-tag">Bergabung {{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->translatedFormat('M Y') }}</span>
                <span class="profile-tag">
                    @if($karyawan->status === 'aktif')
                        <span style="color:#15803d">● Aktif</span>
                    @else
                        <span style="color:#dc2626">● Tidak Aktif</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
    <div class="profile-stats">
        <div class="stat">
            <div class="stat-num green">{{ $histories->count() }}</div>
            <div class="stat-label">Total Jabatan</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $histories->where('tipe','promosi')->count() }}</div>
            <div class="stat-label">Promosi</div>
        </div>
        <div class="stat">
            <div class="stat-num green">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->age }}</div>
            <div class="stat-label">Tahun Kerja</div>
        </div>
    </div>
</div>

{{-- Section Header --}}
<div class="section-header">
    <div>
        <div class="section-title">History Jabatan</div>
        <div class="section-sub">{{ $histories->count() }} jabatan tercatat dalam sistem</div>
    </div>
</div>

{{-- Timeline (read only, tanpa tombol hapus) --}}
@if($histories->count() > 0)
<div class="timeline">
    @foreach($histories as $h)
    <div class="timeline-item">
        <div class="timeline-dot {{ $h->is_current ? 'current' : '' }}"></div>
        <div class="timeline-card {{ $h->is_current ? 'current-card' : '' }}">
            <div class="card-header">
                <div>
                    <div>
                        <span class="card-tipe tipe-{{ $h->tipe }}">
                            @if($h->tipe==='promosi') ↑ @elseif($h->tipe==='demosi') ↓ @elseif($h->tipe==='mutasi') ↔ @elseif($h->tipe==='rotasi') ↻ @else ★ @endif
                            {{ ucfirst($h->tipe) }}
                        </span>
                        @if($h->is_current)
                            <span class="badge-current">Jabatan Saat Ini</span>
                        @endif
                    </div>
                    <div class="card-jabatan">{{ $h->jabatan_saat_ini ?? $h->jabatan->nama_jabatan ?? '-' }}</div>
                    <div class="card-dept">{{ $h->departemen->nama_departemen ?? '-' }} · {{ $h->direktorat->nama_direktorat ?? '-' }}</div>
                </div>
            </div>

            <div class="card-details">
                <div>
                    <div class="detail-label">Job Grade</div>
                    <div class="detail-val">{{ $h->jobGrade->job_grade ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Person Grade</div>
                    <div class="detail-val">{{ $h->personGrade->person_grade ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Kompartemen</div>
                    <div class="detail-val">{{ $h->kompartemen->nama_kompartemen ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Kode Struktur</div>
                    <div class="detail-val">{{ $h->kodeStruktur->kode_struktur ?? '-' }}</div>
                </div>
                @if($h->no_sk)
                <div>
                    <div class="detail-label">No. SK</div>
                    <div class="detail-val">{{ $h->no_sk }}</div>
                </div>
                @endif
                @if($h->tanggal_sk)
                <div>
                    <div class="detail-label">Tanggal SK</div>
                    <div class="detail-val">{{ \Carbon\Carbon::parse($h->tanggal_sk)->format('d M Y') }}</div>
                </div>
                @endif
            </div>

            <div class="card-period">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <strong>{{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M Y') }}</strong>
                <span>→</span>
                <strong>{{ $h->tanggal_selesai ? \Carbon\Carbon::parse($h->tanggal_selesai)->format('d M Y') : 'Sekarang' }}</strong>
                <span class="period-badge">
                    @php
                        $end = $h->tanggal_selesai ?? now();
                        $diff = \Carbon\Carbon::parse($h->tanggal_mulai)->diff($end);
                        echo $diff->y > 0 ? $diff->y.'th ' : '';
                        echo $diff->m > 0 ? $diff->m.'bl' : ($diff->y == 0 ? $diff->d.'hr' : '');
                    @endphp
                </span>
            </div>

            @if($h->keterangan)
            <div class="card-keterangan">💬 {{ $h->keterangan }}</div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<div style="background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);">
    <div class="empty-state">
        <svg viewBox="0 0 24 24" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        <p style="font-size:14px;font-weight:600;color:#6b7280;">Belum ada history jabatan</p>
        <span style="font-size:12px;">History jabatan bisa ditambahkan melalui halaman Profil Karyawan</span>
    </div>
</div>
@endif

@endsection