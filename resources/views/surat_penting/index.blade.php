@extends('layouts.app')
@section('title', 'Surat Penting')
@section('breadcrumb', 'Surat Penting')

@push('styles')
<style>
    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:12px;color:#6b7280;margin-top:3px; }
    .btn-primary { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap; }
    .btn-primary:hover { background:#166534; }
    .btn-primary svg { width:13px;height:13px;stroke:white;fill:none;stroke-width:2.5; }

    .stats-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px; }
    .stat-card { background:white;border-radius:10px;border:1px solid #e5e7eb;padding:14px;text-align:center; }
    .stat-num { font-size:24px;font-weight:800;color:#111827; }
    .stat-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:3px; }
    .stat-card.expire .stat-num { color:#ef4444; }
    .stat-card.soon .stat-num { color:#f59e0b; }

    /* Search kecil */
    .search-mini { display:flex;align-items:center;gap:8px;background:white;border:1px solid #e5e7eb;border-radius:8px;padding:7px 12px;width:220px;transition:border-color 0.15s;flex-shrink:0; }
    .search-mini:focus-within { border-color:#15803d;box-shadow:0 0 0 2px rgba(21,128,61,0.1); }
    .search-mini svg { width:14px;height:14px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-mini input { border:none;outline:none;font-size:12px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-mini input::placeholder { color:#9ca3af; }
    .clear-btn { background:none;border:none;cursor:pointer;color:#9ca3af;font-size:15px;line-height:1;padding:0;display:none;flex-shrink:0; }
    .clear-btn.visible { display:block; }
    .search-spinner { display:none;width:12px;height:12px;border:2px solid #e5e7eb;border-top-color:#15803d;border-radius:50%;animation:spin 0.6s linear infinite;flex-shrink:0; }
    .search-spinner.show { display:block; }
    @keyframes spin { to{transform:rotate(360deg)} }

    .filter-row { display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center; }
    .filter-select { padding:7px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:12px;font-family:inherit;color:#374151;background:white;outline:none;cursor:pointer; }
    .filter-select:focus { border-color:#16a34a; }
    .btn-reset { display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:12px;font-weight:500;cursor:pointer;text-decoration:none; }
    .btn-reset:hover { background:#f5f5f0; }

    /* Grid cards */
    #suratGridWrap { transition:opacity 0.2s; }
    .surat-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px; }
    .surat-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:18px;transition:box-shadow 0.15s;position:relative;overflow:hidden; }
    .surat-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.08); }
    .surat-card.expired { border-color:#fecaca; }
    .surat-card.soon-expire { border-color:#fde68a; }

    .scard-top { display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px; }
    .scard-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
    .scard-kategori { font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;margin-bottom:6px;display:inline-block; }
    .scard-judul { font-size:14px;font-weight:700;color:#111827;line-height:1.3;margin-bottom:3px; }
    .scard-nomor { font-size:11px;color:#9ca3af; }

    .scard-karyawan { display:flex;align-items:center;gap:8px;padding:10px;background:#f9fafb;border-radius:8px;margin-bottom:10px; }
    .scard-avatar { width:28px;height:28px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .scard-avatar img { width:100%;height:100%;object-fit:cover; }
    .scard-kname { font-size:12px;font-weight:600;color:#111827; }
    .scard-knik { font-size:10px;color:#9ca3af; }

    .scard-meta { display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:12px; }
    .meta-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px; }
    .meta-val { font-size:12px;color:#374151;font-weight:600;margin-top:1px; }
    .meta-val.expired { color:#ef4444;font-weight:700; }
    .meta-val.soon { color:#f59e0b;font-weight:700; }

    .scard-file { display:flex;align-items:center;gap:8px;padding:8px 10px;background:#f3f4f6;border-radius:8px;margin-bottom:12px; }
    .scard-file-name { font-size:11px;color:#374151;font-weight:600;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .scard-file-size { font-size:10px;color:#9ca3af;flex-shrink:0; }

    .scard-actions { display:flex;gap:6px; }
    .btn-preview { display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;border:1px solid #e5e7eb;background:white;color:#374151;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;transition:all 0.12s;flex:1;justify-content:center; }
    .btn-preview:hover { background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8; }
    .btn-download { display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;border:1px solid #bbf7d0;background:#f0fdf4;color:#15803d;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .btn-download:hover { background:#dcfce7; }
    .btn-del-surat { width:32px;height:32px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s;flex-shrink:0; }
    .btn-del-surat:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del-surat svg { width:13px;height:13px;stroke:#ef4444;fill:none;stroke-width:2; }

    .expire-ribbon { position:absolute;top:12px;right:-8px;background:#ef4444;color:white;font-size:10px;font-weight:700;padding:3px 14px 3px 8px;clip-path:polygon(0 0,100% 0,88% 50%,100% 100%,0 100%); }
    .soon-ribbon { position:absolute;top:12px;right:-8px;background:#f59e0b;color:white;font-size:10px;font-weight:700;padding:3px 14px 3px 8px;clip-path:polygon(0 0,100% 0,88% 50%,100% 100%,0 100%); }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:14px 0;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:8px;margin-top:8px; }
    .pagination-wrap { display:flex;align-items:center;gap:3px; }
    .page-btn { width:27px;height:27px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-state { text-align:center;padding:60px 20px;background:white;border-radius:14px;border:1px solid #e5e7eb;color:#9ca3af; }
    .empty-state svg { width:44px;height:44px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:12px 14px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:260px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s forwards; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:20px;height:20px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast-icon svg { width:11px;height:11px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:16px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 3s linear forwards; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }

    .modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center; }
    .modal-backdrop.show { display:flex; }
    .modal-box { background:white;border-radius:16px;padding:24px;width:100%;max-width:380px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center; }
    .modal-icon-wrap { width:50px;height:50px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px; }
    .modal-icon-wrap svg { width:22px;height:22px;stroke:#ef4444;fill:none;stroke-width:1.8; }
    .modal-title { font-size:16px;font-weight:700;color:#111827;margin-bottom:8px; }
    .modal-desc { font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:20px; }
    .modal-actions { display:flex;gap:8px; }
    .modal-btn { flex:1;padding:10px;border-radius:9px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all 0.15s; }
    .modal-btn.cancel { background:#f9fafb;color:#374151;border:1px solid #e5e7eb; }
    .modal-btn.danger { background:#ef4444;color:white; }
    .modal-btn.danger:hover { background:#dc2626; }

    mark { background:#fef08a;border-radius:2px;padding:0 1px;color:inherit;font-weight:600; }

    @media (max-width:768px) {
        .stats-grid { grid-template-columns:repeat(2,1fr); }
        .surat-grid { grid-template-columns:1fr; }
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
        <div class="modal-title">Hapus Surat?</div>
        <div class="modal-desc" id="modalDesc">File akan dihapus permanen.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">📄 Surat Penting</div>
        <div class="page-sub">Kelola dokumen dan surat penting karyawan</div>
    </div>
    <a href="{{ route('surat_penting.create') }}" class="btn-primary">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Upload Surat
    </a>
</div>

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-num" id="statTotal">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Dokumen</div>
    </div>
    <div class="stat-card expire">
        <div class="stat-num">{{ $stats['expire'] }}</div>
        <div class="stat-label">Sudah Expired</div>
    </div>
    <div class="stat-card soon">
        <div class="stat-num">{{ $stats['soon'] }}</div>
        <div class="stat-label">Akan Expire (30hr)</div>
    </div>
</div>

{{-- FILTER --}}
<div class="filter-row">
    {{-- Search kecil realtime --}}
    <div class="search-mini">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Cari judul / nama / nomor..." value="{{ request('search') }}" autocomplete="off">
        <div class="search-spinner" id="searchSpinner"></div>
        <button class="clear-btn {{ request('search') ? 'visible' : '' }}" id="clearBtn" onclick="clearSearch()">×</button>
    </div>

    {{-- Select filter (tetap pakai form submit) --}}
    <form method="GET" id="filterForm" style="display:contents">
        <input type="hidden" name="search" id="hiddenSearch" value="{{ request('search') }}">
        <select name="kategori" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <option value="sk_jabatan"       {{ request('kategori')=='sk_jabatan' ? 'selected' : '' }}>SK Jabatan</option>
            <option value="sk_promosi"       {{ request('kategori')=='sk_promosi' ? 'selected' : '' }}>SK Promosi</option>
            <option value="sk_mutasi"        {{ request('kategori')=='sk_mutasi' ? 'selected' : '' }}>SK Mutasi</option>
            <option value="sk_pensiun"       {{ request('kategori')=='sk_pensiun' ? 'selected' : '' }}>SK Pensiun</option>
            <option value="surat_tugas"      {{ request('kategori')=='surat_tugas' ? 'selected' : '' }}>Surat Tugas</option>
            <option value="surat_peringatan" {{ request('kategori')=='surat_peringatan' ? 'selected' : '' }}>Surat Peringatan</option>
            <option value="kontrak"          {{ request('kategori')=='kontrak' ? 'selected' : '' }}>Kontrak</option>
            <option value="sertifikat"       {{ request('kategori')=='sertifikat' ? 'selected' : '' }}>Sertifikat</option>
            <option value="lainnya"          {{ request('kategori')=='lainnya' ? 'selected' : '' }}>Lainnya</option>
        </select>
        <select name="karyawan_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Semua Karyawan</option>
            @foreach($karyawans as $k)
                <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['search','kategori','karyawan_id']))
            <a href="{{ route('surat_penting.index') }}" class="btn-reset">× Reset</a>
        @endif
    </form>
</div>

{{-- SURAT GRID + PAGINATION (dibungkus untuk AJAX update) --}}
<div id="suratGridWrap">
    @if($surats->count() > 0)
    <div class="surat-grid" id="suratGrid">
        @foreach($surats as $s)
        @php
            $w = $s->kategoriWarna;
            $isExpired = $s->isExpired;
            $isSoon = $s->tanggal_exp && !$isExpired && now()->diffInDays($s->tanggal_exp) <= 30;
        @endphp
        <div class="surat-card {{ $isExpired ? 'expired' : ($isSoon ? 'soon-expire' : '') }}">
            @if($isExpired)
                <div class="expire-ribbon">EXPIRED</div>
            @elseif($isSoon)
                <div class="soon-ribbon">SOON</div>
            @endif
            <div class="scard-top">
                <div>
                    <span class="scard-kategori" @style(["background:{$w['bg']};color:{$w['text']};" => true])>{{ $s->kategoriLabel }}</span>
                    <div class="scard-judul">{{ $s->judul }}</div>
                    @if($s->nomor_surat)<div class="scard-nomor">No: {{ $s->nomor_surat }}</div>@endif
                </div>
                <div class="scard-icon" style="background:{{ $w['bg'] }};">
                    @if($s->isPdf) 📄 @elseif($s->isImage) 🖼️ @else 📎 @endif
                </div>
            </div>
            <div class="scard-karyawan">
                <div class="scard-avatar">
                    @if($s->karyawan->foto)<img src="{{ Storage::url($s->karyawan->foto) }}" alt="">
                    @else{{ strtoupper(substr($s->karyawan->nama, 0, 2)) }}@endif
                </div>
                <div>
                    <div class="scard-kname">{{ $s->karyawan->nama }}</div>
                    <div class="scard-knik">NIK {{ $s->karyawan->nik }}</div>
                </div>
            </div>
            <div class="scard-meta">
                <div class="meta-item">
                    <div class="meta-label">Tanggal Surat</div>
                    <div class="meta-val">{{ $s->tanggal_surat->format('d M Y') }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Berlaku Hingga</div>
                    <div class="meta-val {{ $isExpired ? 'expired' : ($isSoon ? 'soon' : '') }}">
                        @if($s->tanggal_exp){{ $s->tanggal_exp->format('d M Y') }}@if($isExpired) ⚠@elseif($isSoon) ⏰@endif
                        @else Tidak ada @endif
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Diupload oleh</div>
                    <div class="meta-val">{{ $s->uploader->name ?? '-' }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Upload pada</div>
                    <div class="meta-val">{{ $s->created_at->format('d M Y') }}</div>
                </div>
            </div>
            <div class="scard-file">
                <span>📎</span>
                <span class="scard-file-name">{{ $s->file_name }}</span>
                <span class="scard-file-size">{{ $s->file_size }}</span>
            </div>
            @if($s->keterangan)
            <div style="font-size:12px;color:#6b7280;font-style:italic;margin-bottom:10px;padding:8px;background:#f9fafb;border-radius:7px;">💬 {{ $s->keterangan }}</div>
            @endif
            <div class="scard-actions">
                <a href="{{ route('surat_penting.show', $s) }}" target="_blank" class="btn-preview">👁 Preview</a>
                <a href="{{ route('surat_penting.download', $s) }}" class="btn-download">⬇ Download</a>
                <button type="button" class="btn-del-surat"
                    data-url="{{ route('surat_penting.destroy', $s) }}"
                    data-judul="{{ $s->judul }}"
                    onclick="openModal(this.dataset.url, this.dataset.judul)">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <div class="table-footer" id="suratFooter">
        <span id="suratInfo">Menampilkan <strong>{{ $surats->firstItem() }}</strong>–<strong>{{ $surats->lastItem() }}</strong> dari <strong>{{ $surats->total() }}</strong> dokumen</span>
        @if($surats->hasPages())
        <div class="pagination-wrap">
            @if($surats->onFirstPage())
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
            @else
                <a href="{{ $surats->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
            @endif
            @php $cur=$surats->currentPage();$last=$surats->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s > 1)
                <a href="{{ $surats->url(1) }}" class="page-btn">1</a>
                @if($s > 2)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
            @endif
            @for($i = $s; $i <= $e; $i++)
                <a href="{{ $surats->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
            @endfor
            @if($e < $last)
                @if($e < $last - 1)<span class="page-btn disabled" style="border:none;background:transparent;">…</span>@endif
                <a href="{{ $surats->url($last) }}" class="page-btn">{{ $last }}</a>
            @endif
            @if($surats->hasMorePages())
                <a href="{{ $surats->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
            @else
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            @endif
        </div>
        @endif
    </div>

    @else
    <div class="empty-state" id="suratEmpty">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <p style="font-size:14px;font-weight:600;color:#6b7280;margin-bottom:3px;">Belum ada dokumen</p>
        <span style="font-size:12px;">Klik "Upload Surat" untuk menambahkan dokumen</span>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
// Toast
function closeToast() {
    var t=document.getElementById('toast');if(!t)return;
    t.classList.add('hiding');setTimeout(function(){var w=document.getElementById('toastWrap');if(w)w.remove();},300);
}
window.addEventListener('DOMContentLoaded',function(){
    if(document.getElementById('toast'))setTimeout(function(){closeToast();},3000);
});

// Modal
var deleteUrl='';
function openModal(url,judul){
    deleteUrl=url;
    document.getElementById('modalDesc').innerHTML='Kamu akan menghapus surat <strong>'+judul+'</strong>.<br>File akan dihapus permanen.';
    document.getElementById('modalHapus').classList.add('show');
    document.body.style.overflow='hidden';
}
function closeModal(){document.getElementById('modalHapus').classList.remove('show');document.body.style.overflow='';}
function submitHapus(){document.getElementById('formHapus').action=deleteUrl;document.getElementById('formHapus').submit();}
document.getElementById('modalHapus').addEventListener('click',function(e){if(e.target===this)closeModal();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeModal();});

// Real-time search
var searchTimer=null;
var searchInput=document.getElementById('searchInput');
var clearBtn=document.getElementById('clearBtn');
var spinner=document.getElementById('searchSpinner');
var gridWrap=document.getElementById('suratGridWrap');

searchInput.addEventListener('input',function(){
    var val=this.value.trim();
    clearBtn.classList.toggle('visible',val.length>0);
    document.getElementById('hiddenSearch').value=val;
    clearTimeout(searchTimer);
    searchTimer=setTimeout(function(){doSearch(val);},300);
});
searchInput.addEventListener('keydown',function(e){
    if(e.key==='Enter'){clearTimeout(searchTimer);doSearch(this.value.trim());}
});

function clearSearch(){
    searchInput.value='';clearBtn.classList.remove('visible');
    document.getElementById('hiddenSearch').value='';
    doSearch('');searchInput.focus();
}

function doSearch(keyword){
    var url=new URL(window.location.href);
    if(keyword)url.searchParams.set('search',keyword);
    else url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.history.pushState({},'',url.toString());

    spinner.classList.add('show');
    gridWrap.style.opacity='0.5';

    fetch(url.toString(),{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.text();})
        .then(function(html){
            var doc=new DOMParser().parseFromString(html,'text/html');
            var nb=doc.getElementById('suratGridWrap');
            if(nb)gridWrap.innerHTML=nb.innerHTML;
            gridWrap.style.opacity='1';
            spinner.classList.remove('show');

            if(keyword)highlightText(gridWrap,keyword);
        })
        .catch(function(){gridWrap.style.opacity='1';spinner.classList.remove('show');});
}

function highlightText(el,keyword){
    var regex=new RegExp('('+keyword.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi');
    el.querySelectorAll('.scard-judul,.scard-nomor,.scard-kname,.scard-knik').forEach(function(node){
        node.innerHTML=node.textContent.replace(regex,'<mark>$1</mark>');
    });
}

window.addEventListener('popstate',function(){
    var kw=new URL(window.location.href).searchParams.get('search')||'';
    searchInput.value=kw;clearBtn.classList.toggle('visible',kw.length>0);
    doSearch(kw);
});
</script>
@endpush