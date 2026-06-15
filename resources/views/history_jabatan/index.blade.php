@extends('layouts.app')
@section('title', 'History Jabatan')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'History Jabatan')

@push('styles')
<style>
    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; color: #6b7280; text-decoration: none;
        margin-bottom: 20px; transition: color 0.12s;
    }
    .back-link:hover { color: #15803d; }
    .back-link svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; }

    /* Profile Card */
    .profile-card {
        background: white; border-radius: 16px; border: 1px solid #e5e7eb;
        padding: 20px 24px; margin-bottom: 20px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 16px;
    }
    .profile-left { display: flex; align-items: center; gap: 14px; }
    .profile-avatar {
        width: 52px; height: 52px; border-radius: 50%;
        background: #dcfce7; color: #15803d;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 700; flex-shrink: 0;
        overflow: hidden; border: 2px solid #bbf7d0;
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-name { font-size: 16px; font-weight: 700; color: #111827; }
    .profile-meta { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .profile-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
    .profile-tag {
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
        background: #f3f4f6; color: #374151;
    }
    .profile-tag.green { background: #dcfce7; color: #15803d; }
    .profile-stats { display: flex; gap: 24px; }
    .stat { text-align: center; }
    .stat-num { font-size: 22px; font-weight: 700; color: #111827; }
    .stat-num.green { color: #15803d; }
    .stat-label { font-size: 11px; color: #9ca3af; margin-top: 2px; }

    /* Page header */
    .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
    }
    .page-title { font-size: 18px; font-weight: 700; color: #111827; }
    .page-sub { font-size: 13px; color: #6b7280; margin-top: 3px; }
    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: #15803d; color: white; padding: 10px 18px;
        border-radius: 10px; font-size: 13px; font-weight: 600;
        text-decoration: none; transition: background 0.15s; white-space: nowrap;
    }
    .btn-primary:hover { background: #166534; }
    .btn-primary svg { width: 14px; height: 14px; stroke: white; fill: none; stroke-width: 2.5; }

    /* Timeline */
    .timeline { position: relative; padding-left: 32px; }
    .timeline::before {
        content: ''; position: absolute;
        left: 10px; top: 0; bottom: 0;
        width: 2px; background: #e5e7eb;
    }
    .timeline-item { position: relative; margin-bottom: 16px; }
    .timeline-dot {
        position: absolute; left: -26px; top: 20px;
        width: 12px; height: 12px; border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #e5e7eb;
        background: #9ca3af; z-index: 1;
    }
    .timeline-dot.current {
        background: #16a34a;
        box-shadow: 0 0 0 3px rgba(22,163,74,0.2);
        width: 14px; height: 14px; left: -27px;
    }
    .timeline-card {
        background: white; border-radius: 14px;
        border: 1px solid #e5e7eb; padding: 18px 20px;
        transition: box-shadow 0.15s;
    }
    .timeline-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
    .timeline-card.current-card { border-color: #bbf7d0; background: #fafffe; }

    .card-header {
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: 12px;
        margin-bottom: 14px; flex-wrap: wrap;
    }
    .card-left { flex: 1; min-width: 0; }
    .card-tipe {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.3px;
        margin-bottom: 6px;
    }
    .tipe-promosi   { background: #dcfce7; color: #15803d; }
    .tipe-mutasi    { background: #dbeafe; color: #1d4ed8; }
    .tipe-demosi    { background: #fee2e2; color: #dc2626; }
    .tipe-onboarding{ background: #fef3c7; color: #d97706; }
    .card-jabatan { font-size: 15px; font-weight: 700; color: #111827; }
    .card-dept { font-size: 12px; color: #6b7280; margin-top: 2px; }
    .card-right { display: flex; align-items: flex-start; gap: 8px; flex-shrink: 0; }
    .badge-current {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px;
        background: #dcfce7; color: #15803d;
        font-size: 11px; font-weight: 700;
    }
    .badge-current::before { content: '●'; font-size: 8px; }

    .card-details {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 10px; margin-bottom: 12px;
    }
    .detail-item { }
    .detail-label { font-size: 10px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    .detail-val { font-size: 12px; color: #374151; font-weight: 600; margin-top: 2px; }

    .card-period {
        display: flex; align-items: center; gap: 8px;
        padding-top: 12px; border-top: 1px solid #f3f4f6;
        font-size: 12px; color: #6b7280;
    }
    .card-period svg { width: 13px; height: 13px; stroke: #9ca3af; fill: none; stroke-width: 2; }
    .card-period strong { color: #374151; }
    .period-badge {
        margin-left: auto; padding: 2px 8px;
        border-radius: 6px; background: #f3f4f6;
        font-size: 11px; color: #6b7280; font-weight: 600;
    }

    .card-keterangan {
        margin-top: 10px; padding: 10px 12px;
        background: #f9fafb; border-radius: 8px;
        font-size: 12px; color: #6b7280; font-style: italic;
        border-left: 3px solid #e5e7eb;
    }

    .btn-del {
        width: 30px; height: 30px; border-radius: 7px;
        border: 1px solid #e5e7eb; background: white;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.12s;
    }
    .btn-del:hover { background: #fef2f2; border-color: #fecaca; }
    .btn-del svg { width: 13px; height: 13px; stroke: #ef4444; fill: none; stroke-width: 2; }

    /* Empty */
    .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .empty-state svg { width: 48px; height: 48px; margin: 0 auto 12px; display: block; stroke: #d1d5db; fill: none; stroke-width: 1.5; }
    .empty-state p { font-size: 14px; font-weight: 600; color: #6b7280; margin-bottom: 4px; }

    /* Toast */
    .toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 9999; pointer-events: none; }
    .toast {
        display: flex; align-items: center; gap: 10px;
        background: white; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a;
        border-radius: 12px; padding: 14px 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        font-size: 13px; color: #15803d; font-weight: 500;
        min-width: 280px; position: relative; overflow: hidden;
        pointer-events: all;
        animation: toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards;
    }
    .toast.hiding { animation: toastOut 0.3s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast-icon { width: 22px; height: 22px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .toast-icon svg { width: 12px; height: 12px; stroke: #16a34a; fill: none; stroke-width: 2.5; }
    .toast-close { border: none; background: transparent; color: #9ca3af; cursor: pointer; font-size: 18px; padding: 0; margin-left: auto; }
    .toast-progress { position: absolute; bottom: 0; left: 0; height: 3px; background: #16a34a; animation: toastProgress 3s linear forwards; }
    @keyframes toastIn { from { opacity:0; transform:translateX(110%); } to { opacity:1; transform:translateX(0); } }
    @keyframes toastOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(110%); } }
    @keyframes toastProgress { from { width:100%; } to { width:0%; } }

    /* Modal */
    .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(3px); z-index:1000; display:none; align-items:center; justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white; border-radius:16px; padding:28px; width:100%; max-width:400px; margin:16px; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center; animation: modalIn 0.25s cubic-bezier(0.4,0,0.2,1); }
    .modal-icon-wrap { width:56px; height:56px; border-radius:50%; background:#fef2f2; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px; height:26px; stroke:#ef4444; fill:none; stroke-width:1.8; }
    .modal-title { font-size:17px; font-weight:700; color:#111827; margin-bottom:8px; }
    .modal-desc { font-size:13px; color:#6b7280; line-height:1.6; margin-bottom:24px; }
    .modal-actions { display:flex; gap:10px; }
    .modal-btn { flex:1; padding:11px; border-radius:10px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; border:none; transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb; color:#374151; border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444; color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    @keyframes modalIn { from { opacity:0; transform:scale(0.92); } to { opacity:1; transform:scale(1); } }

    @media (max-width: 640px) {
        .profile-card { flex-direction: column; }
        .profile-stats { width: 100%; justify-content: space-around; border-top: 1px solid #f3f4f6; padding-top: 12px; }
        .card-details { grid-template-columns: 1fr 1fr; }
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
        <div class="modal-title">Hapus History Jabatan?</div>
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
            </div>
        </div>
    </div>
    <div class="profile-stats">
        <div class="stat">
            <div class="stat-num green">{{ $histories->count() }}</div>
            <div class="stat-label">Total Jabatan</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ $histories->where('tipe', 'promosi')->count() }}</div>
            <div class="stat-label">Promosi</div>
        </div>
        <div class="stat">
            <div class="stat-num">{{ \Carbon\Carbon::parse($karyawan->tanggal_masuk)->age }}</div>
            <div class="stat-label">Tahun Kerja</div>
        </div>
    </div>
</div>

{{-- Page Header --}}
<div class="page-header">
    <div>
        <div class="page-title">History Jabatan</div>
        <div class="page-sub">Riwayat Jabatan {{ $karyawan->nama }}</div>
    </div>
    <a href="{{ route('history_jabatan.create', $karyawan) }}" class="btn-primary">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Jabatan
    </a>
</div>

{{-- Timeline --}}
@if($histories->count() > 0)
<div class="timeline">
    @foreach($histories as $h)
    <div class="timeline-item">
        <div class="timeline-dot {{ $h->is_current ? 'current' : '' }}"></div>
        <div class="timeline-card {{ $h->is_current ? 'current-card' : '' }}">
            <div class="card-header">
                <div class="card-left">
                    <div>
                        <span class="card-tipe tipe-{{ $h->tipe }}">
                            @if($h->tipe === 'promosi') ↑ @elseif($h->tipe === 'demosi') ↓ @elseif($h->tipe === 'mutasi') ↔ @else ★ @endif
                            {{ ucfirst($h->tipe) }}
                        </span>
                        @if($h->is_current)
                            <span class="badge-current">Jabatan Saat Ini</span>
                        @endif
                    </div>
                    <div class="card-jabatan">{{ $h->jabatan_saat_ini ?? $h->jabatan->nama_jabatan ?? '-' }}</div>
                    <div class="card-dept">{{ $h->departemen->nama_departemen ?? '-' }} · {{ $h->direktorat->nama_direktorat ?? '-' }}</div>
                </div>
                <div class="card-right">
                    <button type="button" class="btn-del"
                        data-url="{{ route('history_jabatan.destroy', [$karyawan, $h]) }}"
                        data-jabatan="{{ $h->jabatan->nama_jabatan ?? '-' }}"
                        onclick="openModal(this.dataset.url, this.dataset.jabatan)">
                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                </div>
            </div>

            <div class="card-details">
                <div class="detail-item">
                    <div class="detail-label">Job Grade</div>
                    <div class="detail-val">{{ $h->jobGrade->job_grade ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Person Grade</div>
                    <div class="detail-val">{{ $h->personGrade->person_grade ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Kompartemen</div>
                    <div class="detail-val">{{ $h->kompartemen->nama_kompartemen ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Kode Struktur</div>
                    <div class="detail-val">{{ $h->kodeStruktur->kode_struktur ?? '-' }}</div>
                </div>
                {{-- Tambahkan ini --}}
                @if($h->no_sk)
                <div class="detail-item">
                    <div class="detail-label">No. SK</div>
                    <div class="detail-val">{{ $h->no_sk }}</div>
                </div>
                @endif
                @if($h->tanggal_sk)
                <div class="detail-item">
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
                        echo $diff->y > 0 ? $diff->y . 'th ' : '';
                        echo $diff->m > 0 ? $diff->m . 'bl' : ($diff->y == 0 ? $diff->d . 'hr' : '');
                    @endphp
                </span>
            </div>

            @if($h->keterangan)
            <div class="card-keterangan">
                💬 {{ $h->keterangan }}
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<div style="background:white;border-radius:14px;border:1px solid #e5e7eb;">
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        <p>Belum ada history jabatan</p>
        <span style="font-size:12px;">Klik "Tambah Jabatan" untuk menambahkan riwayat jabatan</span>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Toast
    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => { document.getElementById('toastWrap')?.remove(); }, 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000);
    });

    // Modal
    let deleteUrl = '';
    function openModal(url, jabatan) {
        deleteUrl = url;
        document.getElementById('modalDesc').innerHTML =
            'Kamu akan menghapus jabatan <strong>' + jabatan + '</strong>.<br>Tindakan ini tidak dapat dibatalkan.';
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