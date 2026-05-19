@extends('layouts.app')
@section('title', 'Profil Karyawan')
@section('breadcrumb', 'Profil Karyawan')

@push('styles')
<style>
    .page-header {
        display: flex; align-items: flex-start;
        justify-content: space-between; margin-bottom: 24px;
        gap: 12px; flex-wrap: wrap;
    }
    .page-title { font-size: 22px; font-weight: 700; color: #111827; }
    .page-sub { font-size: 13px; color: #6b7280; margin-top: 4px; }
    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        background: #15803d; color: white; padding: 10px 20px;
        border-radius: 10px; font-size: 13px; font-weight: 600;
        text-decoration: none; border: none; cursor: pointer;
        font-family: inherit; transition: background 0.15s; white-space: nowrap;
    }
    .btn-primary:hover { background: #166534; }

    /* === TOAST === */
    .toast-wrap {
        position: fixed; top: 20px; right: 20px;
        z-index: 9999; display: flex; flex-direction: column; gap: 10px;
        pointer-events: none;
    }
    .toast {
        display: flex; align-items: center; gap: 10px;
        background: white; border: 1px solid #bbf7d0;
        border-left: 4px solid #16a34a;
        border-radius: 12px; padding: 14px 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        font-size: 13px; color: #15803d; font-weight: 500;
        min-width: 280px; max-width: 360px;
        position: relative; overflow: hidden;
        pointer-events: all;
        animation: toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards;
    }
    .toast.hiding { animation: toastOut 0.3s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast-icon {
        width: 22px; height: 22px; background: #dcfce7;
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; flex-shrink: 0;
    }
    .toast-icon svg { width: 12px; height: 12px; stroke: #16a34a; fill: none; stroke-width: 2.5; }
    .toast-msg { flex: 1; line-height: 1.4; }
    .toast-close {
        width: 22px; height: 22px; border-radius: 50%;
        border: none; background: transparent; color: #9ca3af;
        cursor: pointer; font-size: 18px; line-height: 1;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; padding: 0;
    }
    .toast-close:hover { background: #f3f4f6; }
    .toast-progress {
        position: absolute; bottom: 0; left: 0; height: 3px;
        background: #16a34a; border-radius: 0 0 0 8px;
        animation: toastProgress 3s linear forwards;
    }
    @keyframes toastIn {
        from { opacity: 0; transform: translateX(110%); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes toastOut {
        from { opacity: 1; transform: translateX(0); }
        to   { opacity: 0; transform: translateX(110%); }
    }
    @keyframes toastProgress {
        from { width: 100%; }
        to   { width: 0%; }
    }

    /* === MODAL HAPUS === */
    .modal-backdrop {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(3px);
        z-index: 1000;
        display: none; align-items: center; justify-content: center;
        animation: fadeIn 0.2s ease;
    }
    .modal-backdrop.show { display: flex; }
    .modal-box {
        background: white; border-radius: 16px;
        padding: 28px; width: 100%; max-width: 400px;
        margin: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        animation: modalIn 0.25s cubic-bezier(0.4,0,0.2,1);
        text-align: center;
    }
    .modal-icon-wrap {
        width: 56px; height: 56px; border-radius: 50%;
        background: #fef2f2; display: flex;
        align-items: center; justify-content: center;
        margin: 0 auto 16px;
    }
    .modal-icon-wrap svg { width: 26px; height: 26px; stroke: #ef4444; fill: none; stroke-width: 1.8; }
    .modal-title { font-size: 17px; font-weight: 700; color: #111827; margin-bottom: 8px; }
    .modal-desc { font-size: 13px; color: #6b7280; line-height: 1.6; margin-bottom: 24px; }
    .modal-name { font-weight: 600; color: #111827; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-btn {
        flex: 1; padding: 11px; border-radius: 10px;
        font-size: 13px; font-weight: 600; font-family: inherit;
        cursor: pointer; border: none; transition: all 0.15s;
    }
    .modal-btn.cancel {
        background: #f9fafb; color: #374151;
        border: 1px solid #e5e7eb;
    }
    .modal-btn.cancel:hover { background: #f3f4f6; }
    .modal-btn.danger { background: #ef4444; color: white; }
    .modal-btn.danger:hover { background: #dc2626; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.92) translateY(10px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* === SEARCH === */
    .search-filter-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
    .search-bar {
        display: flex; align-items: center; gap: 10px;
        background: white; border: 1px solid #e5e7eb;
        border-radius: 10px; padding: 9px 14px; flex: 1; min-width: 200px;
    }
    .search-bar input {
        border: none; outline: none; font-size: 13px;
        font-family: inherit; flex: 1; color: #111827;
        background: transparent; min-width: 0;
    }
    .search-bar svg { width: 16px; height: 16px; flex-shrink: 0; stroke: #9ca3af; fill: none; }

    /* === TABLE === */
    .table-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; }
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 600px; }
    thead th {
        padding: 12px 16px; text-align: left;
        font-size: 11px; font-weight: 600; color: #6b7280;
        text-transform: uppercase; letter-spacing: 0.5px;
        border-bottom: 1px solid #f3f4f6; background: #f9fafb; white-space: nowrap;
    }
    tbody td { padding: 13px 16px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #fafaf8; }

    .karyawan-info { display: flex; align-items: center; gap: 10px; }
    .karyawan-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: #dcfce7; color: #15803d;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0; overflow: hidden;
    }
    .karyawan-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .karyawan-name { font-weight: 600; color: #111827; font-size: 13px; white-space: nowrap; }
    .karyawan-nik { font-size: 11px; color: #9ca3af; margin-top: 1px; }

    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap;
    }
    .badge-green { background: #dcfce7; color: #15803d; }
    .badge-red { background: #fee2e2; color: #dc2626; }
    .badge-gray { background: #f3f4f6; color: #6b7280; }

    .action-btns { display: flex; align-items: center; gap: 5px; }
    .btn-icon {
        width: 30px; height: 30px; border-radius: 7px; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; background: white; transition: all 0.12s; text-decoration: none;
    }
    .btn-icon svg { width: 13px; height: 13px; fill: none; }
    .btn-icon.view:hover { background: #eff6ff; border-color: #bfdbfe; }
    .btn-icon.view svg { stroke: #3b82f6; }
    .btn-icon.edit:hover { background: #f0fdf4; border-color: #bbf7d0; }
    .btn-icon.edit svg { stroke: #16a34a; }
    .btn-icon.del:hover { background: #fef2f2; border-color: #fecaca; }
    .btn-icon.del svg { stroke: #ef4444; }

    /* === PAGINATION === */
    .table-footer {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 20px; border-top: 1px solid #f3f4f6;
        font-size: 12px; color: #6b7280; flex-wrap: wrap; gap: 10px;
    }
    .pagination-wrap { display: flex; align-items: center; gap: 4px; }
    .page-btn {
        width: 30px; height: 30px; border-radius: 7px;
        border: 1px solid #e5e7eb; background: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 500; color: #374151;
        cursor: pointer; text-decoration: none; transition: all 0.12s;
    }
    .page-btn:hover { background: #f5f5f0; }
    .page-btn.active { background: #15803d; color: white; border-color: #15803d; }
    .page-btn.disabled { opacity: 0.4; pointer-events: none; }
    .page-btn svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; }

    .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .empty-state svg { width: 48px; height: 48px; margin: 0 auto 12px; display: block; stroke: #d1d5db; fill: none; }
    .empty-state p { font-size: 14px; font-weight: 500; color: #6b7280; margin-bottom: 4px; }
    .empty-state span { font-size: 12px; }

    @media (max-width: 640px) {
        .page-title { font-size: 18px; }
        .table-footer { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush

@section('content')

{{-- TOAST --}}
@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="toast-msg">{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">×</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

{{-- MODAL HAPUS --}}
<div class="modal-backdrop" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon-wrap">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </div>
        <div class="modal-title">Hapus Data Karyawan?</div>
        <div class="modal-desc">
            Kamu akan menghapus data karyawan<br>
            <span class="modal-name" id="modalNama">—</span><br>
            Tindakan ini tidak dapat dibatalkan.
        </div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" id="modalConfirmBtn" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>

{{-- Form hapus (hidden, di-submit via JS) --}}
<form id="formHapus" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">Profil Karyawan</div>
        <div class="page-sub">Daftar seluruh data karyawan · {{ $karyawans->total() }} karyawan terdaftar</div>
    </div>
    <a href="{{ route('karyawan.create') }}" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Karyawan
    </a>
</div>

{{-- SEARCH --}}
<form method="GET">
    <div class="search-filter-row">
        <div class="search-bar">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIK karyawan..." />
        </div>
    </div>
</form>

{{-- TABLE --}}
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jabatan</th>
                    <th>Departemen</th>
                    <th>Job Grade</th>
                    <th>Tgl Masuk</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawans as $k)
                <tr>
                    <td>
                        <div class="karyawan-info">
                            <div class="karyawan-avatar">
                                @if($k->foto)
                                    <img src="{{ Storage::url($k->foto) }}" alt="{{ $k->nama }}">
                                @else
                                    {{ strtoupper(substr($k->nama, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="karyawan-name">{{ $k->nama }}</div>
                                <div class="karyawan-nik">NIK {{ $k->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $k->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $k->departemen->nama_departemen ?? '-' }}</td>
                    <td><span class="badge badge-gray">{{ $k->jobGrade->job_grade ?? '-' }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d M Y') }}</td>
                    <td>
                        @if($k->status === 'aktif')
                            <span class="badge badge-green">● Aktif</span>
                        @else
                            <span class="badge badge-red">● Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('karyawan.show', $k) }}" class="btn-icon view" title="Detail">
                                <svg viewBox="0 0 24 24" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('karyawan.edit', $k) }}" class="btn-icon edit" title="Edit">
                                <svg viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            {{-- Tombol hapus → buka modal --}}
                            <button type="button" class="btn-icon del" title="Hapus"
                                data-id="{{ $k->id }}"
                                data-nama="{{ $k->nama }}"
                                data-url="{{ route('karyawan.destroy', $k->id) }}"
                                onclick="openModal(this.dataset.id, this.dataset.nama, this.dataset.url)">
                                <svg viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <p>Belum ada data karyawan</p>
                            <span>Klik "Tambah Karyawan" untuk menambahkan data</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>
            Menampilkan <strong>{{ $karyawans->firstItem() ?? 0 }}</strong>–<strong>{{ $karyawans->lastItem() ?? 0 }}</strong>
            dari <strong>{{ $karyawans->total() }}</strong> karyawan
        </span>

        @if($karyawans->hasPages())
        <div class="pagination-wrap">
            {{-- Prev --}}
            @if($karyawans->onFirstPage())
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></span>
            @else
                <a href="{{ $karyawans->previousPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
            @endif

            @php
                $cur  = $karyawans->currentPage();
                $last = $karyawans->lastPage();
                $s    = max(1, $cur - 2);
                $e    = min($last, $cur + 2);
            @endphp

            @if($s > 1)
                <a href="{{ $karyawans->url(1) }}" class="page-btn">1</a>
                @if($s > 2)<span class="page-btn disabled" style="border:none;background:transparent;width:auto;padding:0 4px;">…</span>@endif
            @endif

            @for($i = $s; $i <= $e; $i++)
                <a href="{{ $karyawans->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
            @endfor

            @if($e < $last)
                @if($e < $last - 1)<span class="page-btn disabled" style="border:none;background:transparent;width:auto;padding:0 4px;">…</span>@endif
                <a href="{{ $karyawans->url($last) }}" class="page-btn">{{ $last }}</a>
            @endif

            {{-- Next --}}
            @if($karyawans->hasMorePages())
                <a href="{{ $karyawans->nextPageUrl() }}" class="page-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
            @else
                <span class="page-btn disabled"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            @endif
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // === TOAST ===
    function closeToast() {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.classList.add('hiding');
        setTimeout(() => {
            const wrap = document.getElementById('toastWrap');
            if (wrap) wrap.remove();
        }, 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) {
            setTimeout(() => closeToast(), 3000);
        }
    });

    // === MODAL HAPUS ===
    let deleteUrl = '';

    function openModal(id, nama, url) {
        deleteUrl = url;
        document.getElementById('modalNama').textContent = nama;
        document.getElementById('modalHapus').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('modalHapus').classList.remove('show');
        document.body.style.overflow = '';
        deleteUrl = '';
    }

    function submitHapus() {
        const form = document.getElementById('formHapus');
        form.action = deleteUrl;
        form.submit();
    }

    // Klik backdrop untuk tutup modal
    document.getElementById('modalHapus').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Escape key tutup modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
</script>
@endpush