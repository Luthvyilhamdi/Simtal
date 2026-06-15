@extends('layouts.app')
@section('title', 'Penilaian Karyawan')
@section('breadcrumb-parent', $karyawan->nama)
@section('breadcrumb', 'Penilaian')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px; }
    .page-title { font-size:18px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:3px; }
    .btn-primary { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:background .15s;white-space:nowrap; }
    .btn-primary:hover { background:#166534; }
    .btn-primary svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2.5; }

    /* Profile mini */
    .profile-mini { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px; }
    .profile-mini-avatar { width:44px;height:44px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;overflow:hidden;border:2px solid #bbf7d0; }
    .profile-mini-avatar img { width:100%;height:100%;object-fit:cover; }

    /* Filter */
    .filter-bar { background:white;border-radius:12px;border:1px solid #e5e7eb;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .filter-select { border:1.5px solid #e5e7eb;border-radius:9px;padding:7px 28px 7px 12px;font-size:13px;font-family:inherit;color:#111827;background:white;outline:none;cursor:pointer;appearance:none; }
    .filter-select:focus { border-color:#15803d; }
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:10px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }

    /* Table */
    .table-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto; }
    table { width:100%;border-collapse:collapse; }
    thead th { background:#f9fafb;padding:11px 16px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid #e5e7eb;white-space:nowrap; }
    tbody tr { border-bottom:1px solid #f3f4f6;transition:background .1s; }
    tbody tr:last-child { border-bottom:none; }
    tbody tr:hover { background:#fafafa; }
    tbody td { padding:12px 16px;font-size:13px;color:#374151;vertical-align:middle; }
    .badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
    .badge-kpi  { background:#dbeafe;color:#1d4ed8; }
    .badge-360  { background:#f5f3ff;color:#7c3aed; }
    .nilai-num { font-size:15px;font-weight:800;color:#111827; }
    .btn-del { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .12s; }
    .btn-del:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del svg { width:13px;height:13px;stroke:#ef4444;fill:none;stroke-width:2; }

    /* Empty */
    .empty-state { text-align:center;padding:60px 20px; }
    .empty-state svg { width:48px;height:48px;margin:0 auto 12px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }
    .empty-state p { font-size:14px;font-weight:600;color:#6b7280;margin-bottom:4px; }

    /* Toast */
    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn .35s cubic-bezier(.4,0,.2,1) forwards; }
    .toast.hiding { animation:toastOut .3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    /* Modal */
    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:28px;width:100%;max-width:400px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center;animation:modalIn .25s cubic-bezier(.4,0,.2,1); }
    .modal-icon-wrap { width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
    .modal-icon-wrap svg { width:26px;height:26px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:17px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:24px; }
    .modal-actions { display:flex;gap:10px; }
    .modal-btn { flex:1;padding:11px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }
    @keyframes modalIn { from{opacity:0;transform:scale(.92);}to{opacity:1;transform:scale(1);} }
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
        <div class="modal-title">Hapus Penilaian?</div>
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

{{-- Profile mini --}}
<div class="profile-mini">
    <div class="profile-mini-avatar">
        @if($karyawan->foto)
            <img src="{{ Storage::url($karyawan->foto) }}" alt="">
        @else
            {{ initials($karyawan->nama) }}
        @endif
    </div>
    <div>
        <div style="font-size:14px;font-weight:700;color:#111827">{{ $karyawan->nama }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:2px">{{ $karyawan->jabatan_saat_ini ?? '-' }} · NIK {{ $karyawan->nik }}</div>
    </div>
</div>

{{-- Page header --}}
<div class="page-header">
    <div>
        <div class="page-title">📊 Penilaian Karyawan</div>
        <div class="page-sub">KPI & 360° — {{ $karyawan->nama }}</div>
    </div>
    <a href="{{ route('penilaian_karyawan.create', $karyawan) }}" class="btn-primary">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Penilaian
    </a>
</div>

{{-- Filter --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('penilaian_karyawan.index', $karyawan) }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%">
        <span style="font-size:12px;color:#6b7280;font-weight:600">Filter:</span>
        <div class="select-wrap">
            <select name="tahun" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                @foreach($tahuns as $t)
                    <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="select-wrap">
            <select name="tipe" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                <option value="KPI" {{ request('tipe') === 'KPI' ? 'selected' : '' }}>KPI</option>
                <option value="360" {{ request('tipe') === '360' ? 'selected' : '' }}>360°</option>
            </select>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    @if($penilaians->total() > 0)
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tahun</th>
                    <th>Periode</th>
                    <th>Tipe</th>
                    <th>Judul</th>
                    <th>Nilai</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penilaians as $i => $p)
                <tr>
                    <td style="color:#9ca3af;font-size:12px">{{ ($penilaians->currentPage() - 1) * $penilaians->perPage() + $i + 1 }}</td>
                    <td style="font-weight:600">{{ $p->tahun }}</td>
                    <td>{{ $p->periode_label }}</td>
                    <td><span class="badge badge-{{ strtolower($p->tipe) === 'kpi' ? 'kpi' : '360' }}">{{ $p->tipe }}</span></td>
                    <td style="max-width:200px">{{ $p->judul }}</td>
                    <td><span class="nilai-num">{{ $p->nilai_format }}</span></td>
                    <td style="font-size:12px;color:#6b7280;max-width:160px">{{ $p->keterangan ? \Illuminate\Support\Str::limit($p->keterangan, 50) : '-' }}</td>
                    <td>
                        <button type="button" class="btn-del"
                            onclick="openModal('{{ route('penilaian_karyawan.destroy', [$karyawan, $p]) }}', '{{ addslashes($p->judul) }}')">
                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- Pagination Footer --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px;">
        <span>
            Menampilkan <strong>{{ $penilaians->firstItem() ?? 0 }}</strong>–<strong>{{ $penilaians->lastItem() ?? 0 }}</strong>
            dari <strong>{{ $penilaians->total() }}</strong> data
        </span>
        @if($penilaians->hasPages())
        <div style="display:flex;align-items:center;gap:3px;">
            @if($penilaians->onFirstPage())
                <span style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;opacity:0.4;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $penilaians->previousPageUrl() }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @endif
            @php $cur=$penilaians->currentPage();$last=$penilaians->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s > 1)
                <a href="{{ $penilaians->url(1) }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;font-size:12px;">1</a>
                @if($s > 2)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
            @endif
            @for($pg = $s; $pg <= $e; $pg++)
                @if($pg == $cur)
                    <span style="width:28px;height:28px;border-radius:7px;border:1px solid #15803d;background:#15803d;display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:600;">{{ $pg }}</span>
                @else
                    <a href="{{ $penilaians->url($pg) }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;font-size:12px;">{{ $pg }}</a>
                @endif
            @endfor
            @if($e < $last)
                @if($e < $last - 1)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
                <a href="{{ $penilaians->url($last) }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;font-size:12px;">{{ $last }}</a>
            @endif
            @if($penilaians->hasMorePages())
                <a href="{{ $penilaians->nextPageUrl() }}" style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:#374151;background:white;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span style="width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;opacity:0.4;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif
        </div>
        @endif
    </div>
    @else
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <p>Belum ada data penilaian</p>
        <span style="font-size:12px">Klik "Tambah Penilaian" untuk mulai menambahkan</span>
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
});

let deleteUrl = '';
function openModal(url, judul) {
    deleteUrl = url;
    document.getElementById('modalDesc').innerHTML = 'Hapus penilaian <strong>' + judul + '</strong>?<br>Tindakan ini tidak dapat dibatalkan.';
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