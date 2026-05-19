@extends('layouts.app')
@section('title', 'History Assessment')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'History Assessment')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color 0.12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .profile-card { background:white;border-radius:16px;border:1px solid #e5e7eb;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px; }
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

    /* Assessment Cards */
    .assessment-list { display:flex;flex-direction:column;gap:14px; }
    .acard { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:20px;transition:box-shadow 0.15s; }
    .acard:hover { box-shadow:0 4px 16px rgba(0,0,0,0.06); }

    .acard-top { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap; }
    .acard-left { flex:1;min-width:0; }
    .acard-date { font-size:11px;color:#9ca3af;font-weight:600;margin-bottom:6px; }
    .acard-jabatan { font-size:15px;font-weight:700;color:#111827;margin-bottom:3px; }
    .acard-meta { font-size:12px;color:#6b7280; }

    .acard-right { display:flex;align-items:center;gap:8px;flex-shrink:0; }

    /* Badge Rekomendasi Final */
    .badge-final { display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700; }
    .badge-dot { width:8px;height:8px;border-radius:50%; }

    /* Rekomendasi Progress */
    .rekom-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px; }
    .rekom-item { }
    .rekom-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:6px; }
    .rekom-bar-wrap { display:flex;align-items:center;gap:8px; }
    .rekom-bar { flex:1;height:6px;background:#f3f4f6;border-radius:20px;overflow:hidden; }
    .rekom-fill { height:100%;border-radius:20px;transition:width 0.5s ease; }
    .rekom-pct { font-size:12px;font-weight:700;color:#111827;min-width:36px;text-align:right; }

    /* Detail grid */
    .detail-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;padding-top:14px;border-top:1px solid #f3f4f6; }
    .detail-item { }
    .detail-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px; }
    .detail-val { font-size:12px;color:#374151;font-weight:600;margin-top:2px; }
    .expired { color:#ef4444;font-weight:700; }

    .btn-del { width:30px;height:30px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-del:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del svg { width:13px;height:13px;stroke:#ef4444;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px;background:white;border-radius:14px;border:1px solid #e5e7eb; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px; }

    /* Toast */
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

    /* Modal */
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
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    @media (max-width:640px) {
        .profile-card { flex-direction:column; }
        .profile-stats { width:100%;justify-content:space-around;border-top:1px solid #f3f4f6;padding-top:12px; }
        .rekom-grid { grid-template-columns:1fr; }
        .detail-grid { grid-template-columns:1fr 1fr; }
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

{{-- Modal Hapus --}}
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

<a href="{{ route('karyawan.show', $karyawan) }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Profil {{ $karyawan->nama }}
</a>

{{-- Profile Card --}}
<div class="profile-card">
    <div class="profile-left">
        <div class="profile-avatar">
            @if($karyawan->foto)
                <img src="{{ Storage::url($karyawan->foto) }}" alt="">
            @else
                {{ strtoupper(substr($karyawan->nama, 0, 2)) }}
            @endif
        </div>
        <div>
            <div class="profile-name">{{ $karyawan->nama }}</div>
            <div class="profile-meta">
                {{ $karyawan->jabatan_saat_ini ?? $karyawan->jabatan->nama_jabatan ?? '-' }}
            </div>
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
            <div class="stat-num green">{{ $assessments->count() }}</div>
            <div class="stat-label">Total Assessment</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $assessments->where('rekomendasi_final','ready')->count() }}</div>
            <div class="stat-label">Ready</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $assessments->where('rekomendasi_final','ready_with_development')->count() }}</div>
            <div class="stat-label">Ready With Dev</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $assessments->where('rekomendasi_final','not_ready')->count() }}</div>
            <div class="stat-label">Not Ready</div>
        </div>
    </div>
</div>

{{-- Page Header --}}
<div class="page-header">
    <div>
        <div class="page-title">History Assessment</div>
        <div class="page-sub">Riwayat assessment {{ $karyawan->nama }}</div>
    </div>
    <a href="{{ route('history_assessment.create', $karyawan) }}" class="btn-primary">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Assessment
    </a>
</div>

{{-- Assessment List --}}
@if($assessments->count() > 0)
<div class="assessment-list">
    @foreach($assessments as $a)
    @php
        $warna = $a->rekomendasiFinalWarna;
    @endphp
    <div class="acard">
        <div class="acard-top">
            <div class="acard-left">
                <div class="acard-date">
                    📅 {{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}
                    @if($a->tingkat_pengukuran)
                        · <span style="color:#6b7280;">{{ $a->tingkat_pengukuran }}</span>
                    @endif
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
                <span class="badge-final" style="background:{{ $warna['bg'] }};color:{{ $warna['text'] }};">
                    <span class="badge-dot" style="background:{{ $warna['text'] }};"></span>
                    {{ $a->rekomendasiFinalLabel }}
                </span>
                @endif
                <button type="button" class="btn-del"
                    data-url="{{ route('history_assessment.destroy', [$karyawan, $a]) }}"
                    data-tgl="{{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}"
                    onclick="openModal(this.dataset.url, this.dataset.tgl)">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
            </div>
        </div>

        {{-- Rekomendasi Progress Bar --}}
        @if($a->rekomendasi_inti || $a->rekomendasi_primer || $a->rekomendasi_skunder)
        <div class="rekom-grid">
            <div class="rekom-item">
                <div class="rekom-label">Rekomendasi Inti</div>
                <div class="rekom-bar-wrap">
                    <div class="rekom-bar">
                        <div class="rekom-fill" style="width:{{ $a->rekomendasi_inti ?? 0 }}%;background:#15803d;"></div>
                    </div>
                    <span class="rekom-pct">{{ $a->rekomendasi_inti ?? 0 }}%</span>
                </div>
            </div>
            <div class="rekom-item">
                <div class="rekom-label">Rekomendasi Primer</div>
                <div class="rekom-bar-wrap">
                    <div class="rekom-bar">
                        <div class="rekom-fill" style="width:{{ $a->rekomendasi_primer ?? 0 }}%;background:#3b82f6;"></div>
                    </div>
                    <span class="rekom-pct">{{ $a->rekomendasi_primer ?? 0 }}%</span>
                </div>
            </div>
            <div class="rekom-item">
                <div class="rekom-label">Rekomendasi Sekunder</div>
                <div class="rekom-bar-wrap">
                    <div class="rekom-bar">
                        <div class="rekom-fill" style="width:{{ $a->rekomendasi_skunder ?? 0 }}%;background:#f59e0b;"></div>
                    </div>
                    <span class="rekom-pct">{{ $a->rekomendasi_skunder ?? 0 }}%</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Detail --}}
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
    <p>Belum ada history assessment</p>
    <span style="font-size:12px;color:#9ca3af;">Klik "Tambah Assessment" untuk menambahkan</span>
</div>
@endif

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
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
@endpush