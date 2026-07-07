@extends('layouts.app')
@section('title', 'History Assessment')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'History Assessment')

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
    .profile-meta { font-size:12px;color:#6b7280;margin-top:2px; }
    .profile-tags { display:flex;gap:6px;flex-wrap:wrap;margin-top:6px; }
    .profile-tag { padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151; }
    .profile-tag.green { background:#dcfce7;color:#15803d; }
    .profile-stats { display:flex;gap:24px; }
    .stat { text-align:center; }
    .stat-num { font-size:22px;font-weight:700;color:#111827; }
    .stat-num.green { color:#15803d; }
    .stat-label { font-size:11px;color:#9ca3af;margin-top:2px; }

    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px; }
    .page-title { font-size:18px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:3px; }

    .btn-primary { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.15s;white-space:nowrap; }
    .btn-primary:hover { background:#166534; }
    .btn-primary svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2.5; }
    .btn-outline-green { display:inline-flex;align-items:center;gap:8px;background:white;color:#15803d;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #15803d;transition:all 0.15s;white-space:nowrap; }
    .btn-outline-green:hover { background:#f0fdf4; }
    .btn-outline-green svg { width:14px;height:14px;stroke:#15803d;fill:none;stroke-width:2.5; }
    .btn-group { display:flex;gap:10px;flex-wrap:wrap; }

    .tab-wrap { display:flex;gap:4px;background:#f3f4f6;border-radius:10px;padding:4px;margin-bottom:20px; }
    .tab-btn { flex:1;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;color:#6b7280;background:transparent;transition:all 0.15s;text-align:center; }
    .tab-btn.active { background:white;color:#15803d;box-shadow:0 1px 4px rgba(0,0,0,0.08); }

    .assessment-list { display:flex;flex-direction:column;gap:14px; }
    .acard { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px;transition:box-shadow 0.15s; }
    .acard:hover { box-shadow:0 4px 16px rgba(0,0,0,0.06); }
    .acard-top { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap; }
    .acard-left { flex:1;min-width:0; }
    .acard-date { font-size:11px;color:#9ca3af;font-weight:600;margin-bottom:6px; }
    .acard-jabatan { font-size:15px;font-weight:700;color:#111827;margin-bottom:3px; }
    .acard-meta { font-size:12px;color:#6b7280; }
    .acard-right { display:flex;align-items:center;gap:8px;flex-shrink:0; }

    .badge-final { display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700; }
    .badge-dot { width:8px;height:8px;border-radius:50%; }
    .badge-qualified   { background:#dcfce7;color:#15803d; }
    .badge-notqualified{ background:#fee2e2;color:#dc2626; }

    /* FIX: badge rekomendasi final warna via class statis */
    .badge-rekom-ready { background:#dcfce7;color:#15803d; }
    .badge-rekom-rwd   { background:#fef3c7;color:#d97706; }
    .badge-rekom-nr    { background:#fee2e2;color:#dc2626; }
    .badge-rekom-none  { background:#f3f4f6;color:#6b7280; }
    .badge-dot-ready   { background:#15803d; }
    .badge-dot-rwd     { background:#d97706; }
    .badge-dot-nr      { background:#dc2626; }
    .badge-dot-none    { background:#6b7280; }

    /* FIX: badge-dot kompetensi via class statis */
    .badge-dot-qualified    { background:#15803d; }
    .badge-dot-notqualified { background:#dc2626; }

    .rekom-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px; }
    .rekom-item { }
    .rekom-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:6px; }
    .rekom-bar-wrap { display:flex;align-items:center;gap:8px; }
    .rekom-bar { flex:1;height:6px;background:#f3f4f6;border-radius:20px;overflow:hidden; }
    .rekom-fill { height:100%;border-radius:20px;transition:width 0.5s ease; }
    /* FIX: rekom-fill warna via class statis */
    .rekom-fill-green { background:#15803d; }
    .rekom-fill-blue  { background:#3b82f6; }
    .rekom-fill-amber { background:#f59e0b; }
    .rekom-pct { font-size:12px;font-weight:700;color:#111827;min-width:36px;text-align:right; }

    .komp-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;margin-bottom:14px; }
    .komp-item { background:#f9fafb;border-radius:8px;padding:10px 12px; }
    .komp-name { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:4px; }
    .komp-val { font-size:16px;font-weight:800; }
    .komp-val.under { color:#dc2626; }
    .komp-val.ok { color:#15803d; }

    .detail-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;padding-top:14px;border-top:1px solid #f3f4f6; }
    .detail-item { }
    .detail-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px; }
    .detail-val { font-size:12px;color:#374151;font-weight:600;margin-top:2px; }
    .expired { color:#ef4444;font-weight:700; }

    .btn-del { width:30px;height:30px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-del:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del svg { width:13px;height:13px;stroke:#ef4444;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px;background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow); }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:400px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center;animation:modalIn 0.25s cubic-bezier(0.4,0,0.2,1); }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:17px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:24px; }
    .modal-actions { display:flex;gap:10px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    .modal-btn.green { background:#15803d;color:white; }
    .modal-btn.green:hover { background:#166534; }
    .lf-btn { width:30px;height:30px;border-radius:7px;border:1px solid #e5e7eb;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s;flex-shrink:0;text-decoration:none; }
    .lf-btn svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .lf-btn.open { color:#15803d;border-color:#bbf7d0;background:#f0fdf4; }
    .lf-btn.open:hover { background:#dcfce7; }
    .lf-btn.edit { color:#374151; }
    .lf-btn.edit:hover { background:#f9fafb;border-color:#d1d5db; }
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    @media (max-width:640px) {
        .profile-card { flex-direction:column; }
        .profile-stats { width:100%;justify-content:space-around;border-top:1px solid #f3f4f6;padding-top:12px; }
        .rekom-grid { grid-template-columns:1fr; }
        .detail-grid { grid-template-columns:1fr 1fr; }
        .komp-grid { grid-template-columns:1fr 1fr; }
        .btn-group { flex-direction:column; }
    }
</style>
@endpush

@section('content')

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

<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus Assessment?</div>
        <div class="modal-desc" id="modalDesc">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

{{-- MODAL LINK FILE --}}
<div class="modal-backdrop" id="modalLinkFile">
    <div class="modal-box" style="max-width:460px;text-align:left;">
        <div class="modal-title">🔗 Link File Assessment</div>
        <div class="modal-desc" style="text-align:left;margin-bottom:14px;">Tempel link file assessment (Google Drive / OneDrive), diawali http:// atau https://. Kosongkan untuk menghapus link.</div>
        <form id="formLinkFile" method="POST">
            @csrf
            @method('PATCH')
            <input type="url" name="link_file" id="linkFileInput" placeholder="https://drive.google.com/..."
                   style="width:100%;border:1px solid #d1d5db;border-radius:9px;padding:10px 12px;font-size:13px;font-family:inherit;">
            <div class="modal-actions" style="margin-top:16px;">
                <button type="button" class="modal-btn cancel" onclick="closeLinkFile()">Batal</button>
                <button type="submit" class="modal-btn green">Simpan Link</button>
            </div>
        </form>
    </div>
</div>

<a href="{{ route('karyawan.show', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Profil {{ $karyawan->nama }}
</a>

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
            <div class="profile-meta">{{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}</div>
            <div class="profile-tags">
                <span class="profile-tag green">NIK {{ $karyawan->nik }}</span>
                <span class="profile-tag">{{ $karyawan->jobGrade->job_grade ?? '-' }}</span>
                <span class="profile-tag">{{ $karyawan->personGrade->person_grade ?? '-' }}</span>
                <span class="profile-tag">{{ $karyawan->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                <span class="profile-tag">{{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->age }} tahun</span>
            </div>
        </div>
    </div>
    <div class="profile-stats">
        <div class="stat">
            <div class="stat-num green">{{ $assessments->count() + $assessmentKompetensi->count() }}</div>
            <div class="stat-label">Total Assessment</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $assessments->count() }}</div>
            <div class="stat-label">Rekomendasi</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $assessmentKompetensi->count() }}</div>
            <div class="stat-label">Kompetensi</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $assessmentKompetensi->where('kesimpulan','QUALIFIED')->count() }}</div>
            <div class="stat-label">Qualified</div>
        </div>
    </div>
</div>

<div class="page-header">
    <div>
        <div class="page-title">History Assessment</div>
        <div class="page-sub">Riwayat assessment {{ $karyawan->nama }}</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('history_assessment.create', $karyawan) }}" class="btn-outline-green">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            + Rekomendasi
        </a>
        <a href="{{ route('assessment_kompetensi.create', $karyawan) }}" class="btn-primary">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            + Kompetensi
        </a>
    </div>
</div>

<div class="tab-wrap">
    {{-- FIX: onclick switchTab pakai this.dataset.tab --}}
    <button class="tab-btn active" id="tab-rekom" data-tab="rekom" onclick="switchTab(this.dataset.tab)">
        📋 Assessment Rekomendasi ({{ $assessments->count() }})
    </button>
    <button class="tab-btn" id="tab-komp" data-tab="komp" onclick="switchTab(this.dataset.tab)">
        ⭐ Assessment Kompetensi ({{ $assessmentKompetensi->count() }})
    </button>
</div>

{{-- TAB REKOMENDASI --}}
<div id="panel-rekom">
    @if($assessments->count() > 0)
    <div class="assessment-list">
        @foreach($assessments as $a)
        @php
            $warna   = $a->rekomendasiFinalWarna;
            $rekomKey = match($a->rekomendasi_final) {
                'ready'                 => 'ready',
                'ready_with_development'=> 'rwd',
                'not_ready'             => 'nr',
                default                 => 'none',
            };
        @endphp
        <div class="acard">
            <div class="acard-top">
                <div class="acard-left">
                    <div class="acard-date">
                        📅 {{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}
                        @if($a->tingkat_pengukuran) · <span style="color:#6b7280;">{{ $a->tingkat_pengukuran }}</span> @endif
                        @if($a->lembaga) · 🏢 <span style="color:#6b7280;">{{ $a->lembaga }}</span> @endif
                    </div>
                    <div class="acard-jabatan">{{ $a->jabatan_saat_ini ?? '-' }}</div>
                    <div class="acard-meta">
                        Job Grade: <strong>{{ $a->job_grade ?? '-' }}</strong>
                        · Person Grade: <strong>{{ $a->person_grade ?? '-' }}</strong>
                        @if($a->job_stream) · Job Stream: <strong>{{ $a->job_stream }}</strong> @endif
                    </div>
                </div>
                <div class="acard-right">
                    @if($a->rekomendasi_final)
                    {{-- FIX: style Blade di badge diganti class statis --}}
                    <span class="badge-final badge-rekom-{{ $rekomKey }}">
                        <span class="badge-dot badge-dot-{{ $rekomKey }}"></span>
                        {{ $a->rekomendasiFinalLabel }}
                    </span>
                    @endif
                    @if($a->link_file)
                        <a href="{{ $a->link_file }}" target="_blank" rel="noopener" class="lf-btn open" title="Buka file assessment"><svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>
                    @endif
                    <button type="button" class="lf-btn edit" title="{{ $a->link_file ? 'Ubah link file' : 'Tambah link file' }}"
                        data-url="{{ route('history_assessment_all.link_file', $a) }}" data-link="{{ $a->link_file }}"
                        onclick="openLinkFile(this.dataset.url, this.dataset.link)"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></button>
                    <button type="button" class="btn-del"
                        data-url="{{ route('history_assessment.destroy', [$karyawan, $a]) }}"
                        data-tgl="{{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}"
                        onclick="openModal(this.dataset.url, this.dataset.tgl)">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                </div>
            </div>

            @if($a->rekomendasi_inti || $a->rekomendasi_primer || $a->rekomendasi_skunder)
            <div class="rekom-grid">
                <div class="rekom-item">
                    <div class="rekom-label">Rekomendasi Inti</div>
                    <div class="rekom-bar-wrap">
                        {{-- FIX: width Blade diganti data-pct + apply via JS --}}
                        <div class="rekom-bar"><div class="rekom-fill rekom-fill-green" data-pct="{{ $a->rekomendasi_inti ?? 0 }}"></div></div>
                        <span class="rekom-pct">{{ $a->rekomendasi_inti ?? 0 }}%</span>
                    </div>
                </div>
                <div class="rekom-item">
                    <div class="rekom-label">Rekomendasi Primer</div>
                    <div class="rekom-bar-wrap">
                        <div class="rekom-bar"><div class="rekom-fill rekom-fill-blue" data-pct="{{ $a->rekomendasi_primer ?? 0 }}"></div></div>
                        <span class="rekom-pct">{{ $a->rekomendasi_primer ?? 0 }}%</span>
                    </div>
                </div>
                <div class="rekom-item">
                    <div class="rekom-label">Rekomendasi Sekunder</div>
                    <div class="rekom-bar-wrap">
                        <div class="rekom-bar"><div class="rekom-fill rekom-fill-amber" data-pct="{{ $a->rekomendasi_skunder ?? 0 }}"></div></div>
                        <span class="rekom-pct">{{ $a->rekomendasi_skunder ?? 0 }}%</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Jenis Kelamin</div>
                    <div class="detail-val">{{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Usia Saat Assessment</div>
                    <div class="detail-val">{{ $a->usia }} tahun</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tanggal Exp IDP</div>
                    <div class="detail-val {{ $a->isExpired ? 'expired' : '' }}">
                        {{ \Carbon\Carbon::parse($a->tanggal_exp_idp)->format('d M Y') }}
                        @if($a->isExpired) ⚠ @endif
                    </div>
                </div>
                @if($a->keterangan)
                <div class="detail-item" style="grid-column:1/-1;">
                    <div class="detail-label">Keterangan</div>
                    <div class="detail-val" style="font-style:italic;color:#6b7280;">{{ $a->keterangan }}</div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <p>Belum ada assessment rekomendasi</p>
        <span style="font-size:12px;color:#9ca3af;">Klik "+ Rekomendasi" untuk menambahkan</span>
    </div>
    @endif
</div>

{{-- TAB KOMPETENSI --}}
<div id="panel-komp" style="display:none;">
    @if($assessmentKompetensi->count() > 0)
    <div class="assessment-list">
        @foreach($assessmentKompetensi as $ak)
        @php $isQualified = $ak->kesimpulan === 'QUALIFIED'; @endphp
        <div class="acard">
            <div class="acard-top">
                <div class="acard-left">
                    <div class="acard-date">
                        📅 {{ $ak->tanggal_assessment->format('d M Y') }}
                        @if($ak->periode) · <span style="color:#6b7280;">Periode: {{ $ak->periode }}</span> @endif
                        @if($ak->lembaga) · 🏢 <span style="color:#6b7280;">{{ $ak->lembaga }}</span> @endif
                    </div>
                    <div class="acard-jabatan">Assessment Kompetensi</div>
                    <div class="acard-meta">
                        Under Competency: <strong>{{ $ak->total_competency_under }}</strong>
                        · Under Qualification: <strong>{{ $ak->total_qualification_under }}</strong>
                    </div>
                </div>
                <div class="acard-right">
                    {{-- FIX: badge-dot style Blade diganti class statis --}}
                    <span class="badge-final {{ $isQualified ? 'badge-qualified' : 'badge-notqualified' }}">
                        <span class="badge-dot {{ $isQualified ? 'badge-dot-qualified' : 'badge-dot-notqualified' }}"></span>
                        {{ $ak->kesimpulan ?? '-' }}
                    </span>
                    @if($ak->link_file)
                        <a href="{{ $ak->link_file }}" target="_blank" rel="noopener" class="lf-btn open" title="Buka file assessment"><svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>
                    @endif
                    <button type="button" class="lf-btn edit" title="{{ $ak->link_file ? 'Ubah link file' : 'Tambah link file' }}"
                        data-url="{{ route('assessment_kompetensi_all.link_file', $ak) }}" data-link="{{ $ak->link_file }}"
                        onclick="openLinkFile(this.dataset.url, this.dataset.link)"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></button>
                    <button type="button" class="btn-del"
                        data-url="{{ route('assessment_kompetensi.destroy', [$karyawan, $ak]) }}"
                        data-tgl="{{ $ak->tanggal_assessment->format('d M Y') }}"
                        onclick="openModal(this.dataset.url, this.dataset.tgl)">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                </div>
            </div>

            <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Competencies</div>
            <div class="komp-grid">
                @foreach(\App\Models\HistoryAssessmentKompetensi::competencies() as $key => $label)
                <div class="komp-item">
                    <div class="komp-name">{{ $label }}</div>
                    <div class="komp-val {{ ($ak->$key ?? 0) < 3 ? 'under' : 'ok' }}">
                        {{ $ak->$key ?? '-' }}
                        @if(($ak->$key ?? 0) < 3) <span style="font-size:10px;">⚠</span> @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;margin-top:8px;">Professional Qualification</div>
            <div class="komp-grid">
                @foreach(\App\Models\HistoryAssessmentKompetensi::qualifications() as $key => $label)
                <div class="komp-item">
                    <div class="komp-name">{{ $label }}</div>
                    <div class="komp-val {{ ($ak->$key ?? 0) < 3 ? 'under' : 'ok' }}">
                        {{ $ak->$key ?? '-' }}
                        @if(($ak->$key ?? 0) < 3) <span style="font-size:10px;">⚠</span> @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if($ak->keterangan)
            <div class="detail-grid" style="margin-top:12px;">
                <div class="detail-item" style="grid-column:1/-1;">
                    <div class="detail-label">Keterangan</div>
                    <div class="detail-val" style="font-style:italic;color:#6b7280;">{{ $ak->keterangan }}</div>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <p>Belum ada assessment kompetensi</p>
        <span style="font-size:12px;color:#9ca3af;">Klik "+ Kompetensi" untuk menambahkan</span>
    </div>
    @endif
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

    // Apply rekom-fill width dari data-pct
    document.querySelectorAll('.rekom-fill[data-pct]').forEach(el => {
        el.style.width = el.dataset.pct + '%';
    });
});

function switchTab(tab) {
    document.getElementById('panel-rekom').style.display = tab === 'rekom' ? 'block' : 'none';
    document.getElementById('panel-komp').style.display  = tab === 'komp'  ? 'block' : 'none';
    document.getElementById('tab-rekom').classList.toggle('active', tab === 'rekom');
    document.getElementById('tab-komp').classList.toggle('active',  tab === 'komp');
}

let deleteUrl = '';
function openModal(url, tgl) {
    deleteUrl = url;
    document.getElementById('modalDesc').innerHTML =
        'Kamu akan menghapus assessment tanggal <strong>' + tgl + '</strong>.<br>Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('modalHapus').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('modalHapus').classList.remove('show');
    document.body.style.overflow = '';
}
function submitHapus() {
    document.getElementById('formHapus').action = deleteUrl;
    document.getElementById('formHapus').submit();
}
document.getElementById('modalHapus').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ===== Modal Link File =====
function openLinkFile(url, current) {
    document.getElementById('formLinkFile').action = url;
    document.getElementById('linkFileInput').value = current || '';
    document.getElementById('modalLinkFile').classList.add('show');
    setTimeout(() => document.getElementById('linkFileInput').focus(), 50);
}
function closeLinkFile() {
    document.getElementById('modalLinkFile').classList.remove('show');
}
document.getElementById('modalLinkFile').addEventListener('click', function(e) {
    if (e.target === this) closeLinkFile();
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeLinkFile(); } });
</script>
@endpush