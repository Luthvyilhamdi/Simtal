@extends('layouts.app')
@section('title', 'PGS & PJS')
@section('breadcrumb', 'PGS & PJS')

@push('styles')
<style>
    .page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:22px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }
    .btn-primary { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.15s;white-space:nowrap; }
    .btn-primary:hover { background:#166534; }
    .btn-primary svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2.5; }

    /* Section Label */
    .section-label { font-size:14px;font-weight:700;color:#111827;margin-bottom:14px;display:flex;align-items:center;gap:10px; }
    .section-label .count-badge { background:#f3f4f6;color:#6b7280;font-size:11px;padding:2px 8px;border-radius:20px;font-weight:600; }
    .section-label .count-badge.aktif { background:#dcfce7;color:#15803d; }

    /* Aktif Cards */
    .aktif-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:14px;margin-bottom:32px; }
    .aktif-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:18px 20px;transition:box-shadow 0.15s;position:relative;overflow:hidden; }
    .aktif-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.06); }
    .aktif-card::before { content:'';position:absolute;top:0;left:0;right:0;height:3px; }
    .aktif-card.pgs::before { background:#3b82f6; }
    .aktif-card.pjs::before { background:#7c3aed; }

    .acard-top { display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:14px; }
    .acard-left { flex:1;min-width:0; }
    .tipe-badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:1px;margin-bottom:6px; }
    .tipe-pgs { background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }
    .tipe-pjs { background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe; }
    .acard-jabatan { font-size:15px;font-weight:700;color:#111827;margin-bottom:3px; }
    .acard-karyawan { font-size:12px;color:#6b7280; }

    .acard-right { display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0; }
    .sisa-hari { text-align:right; }
    .sisa-num { font-size:20px;font-weight:800;color:#111827; }
    .sisa-num.warning { color:#f59e0b; }
    .sisa-num.danger { color:#ef4444; }
    .sisa-label { font-size:10px;color:#9ca3af;font-weight:600; }

    .acard-details { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px; }
    /* .detail-item { } */
    .detail-label { font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:0.3px; }
    .detail-val { font-size:12px;color:#374151;font-weight:600;margin-top:1px; }

    .acard-period { display:flex;align-items:center;gap:6px;padding-top:10px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280; }
    .acard-period svg { width:12px;height:12px;stroke:#9ca3af;fill:none;stroke-width:2; }
    .acard-period strong { color:#374151; }

    /* Progress bar sisa waktu */
    .progress-wrap { margin:10px 0; }
    .progress-bar { height:5px;background:#f3f4f6;border-radius:20px;overflow:hidden; }
    .progress-fill { height:100%;border-radius:20px;transition:width 0.5s; }

    .btn-del-sm { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s; }
    .btn-del-sm:hover { background:#fef2f2;border-color:#fecaca; }
    .btn-del-sm svg { width:12px;height:12px;stroke:#ef4444;fill:none;stroke-width:2; }

    /* Empty aktif */
    .empty-aktif { background:white;border-radius:14px;border:1px dashed #e5e7eb;padding:40px 20px;text-align:center;color:#9ca3af;margin-bottom:32px; }
    .empty-aktif svg { width:40px;height:40px;margin:0 auto 10px;display:block;stroke:#d1d5db;fill:none;stroke-width:1.5; }

    /* History Table */
    .table-card { background:white;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden; }
    .table-wrap { overflow-x:auto; }
    table { width:100%;border-collapse:collapse;font-size:13px;min-width:600px; }
    thead th { padding:11px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #f3f4f6;background:#f9fafb;white-space:nowrap; }
    tbody td { padding:12px 16px;border-bottom:1px solid #f3f4f6;color:#374151;vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#fafaf8; }

    .karyawan-info { display:flex;align-items:center;gap:8px; }
    .karyawan-avatar { width:32px;height:32px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden; }
    .karyawan-avatar img { width:100%;height:100%;object-fit:cover; }
    .karyawan-name { font-weight:600;color:#111827;font-size:13px; }
    .karyawan-nik { font-size:11px;color:#9ca3af; }

    .table-footer { display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f3f4f6;font-size:12px;color:#6b7280;flex-wrap:wrap;gap:10px; }
    .pagination-wrap { display:flex;align-items:center;gap:4px; }
    .page-btn { width:28px;height:28px;border-radius:7px;border:1px solid #e5e7eb;background:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none;transition:all 0.12s; }
    .page-btn:hover { background:#f5f5f0; }
    .page-btn.active { background:#15803d;color:white;border-color:#15803d; }
    .page-btn.disabled { opacity:0.4;pointer-events:none; }
    .page-btn svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    /* Toast & Modal */
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
    @keyframes modalIn { from{opacity:0;transform:scale(0.92);}to{opacity:1;transform:scale(1);} }

    @media (max-width:640px) {
        .aktif-grid { grid-template-columns:1fr; }
        .acard-details { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')

{{-- Toast --}}
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
        <div class="modal-title">Hapus Data PGS/PJS?</div>
        <div class="modal-desc" id="modalDesc">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Batal</button>
            <button class="modal-btn danger" onclick="submitHapus()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="formHapus" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

{{-- Modal Akhiri --}}
<div class="modal-backdrop" id="modalAkhiri">
    <div class="modal-box">
        <div class="modal-icon-wrap" style="background:#fffbeb;">
            <svg viewBox="0 0 24 24" style="stroke:#f59e0b;fill:none;stroke-width:1.8;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="modal-title">Akhiri PGS/PJS?</div>
        <div class="modal-desc" id="akhiriDesc">Masukkan tanggal berakhir jabatan.</div>
        <form id="formAkhiri" method="POST">
            @csrf
            @method('PATCH')
            <div style="margin-bottom:20px;text-align:left;">
                <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">
                    Tanggal Berakhir <span style="color:#ef4444">*</span>
                </label>
                <input type="date" name="tanggal_berakhir" id="inputTglAkhiri"
                       style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;outline:none;background:#fafafa;box-sizing:border-box;"
                       required />
            </div>
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel" onclick="closeAkhiriModal()">Batal</button>
                <button type="submit" class="modal-btn" style="background:#f59e0b;color:white;border:none;">Ya, Akhiri</button>
            </div>
        </form>
    </div>
</div>

{{-- Page Header --}}
<div class="page-header">
    <div>
        <div class="page-title">PGS & PJS</div>
        <div class="page-sub">Pejabat Sementara dan Pejabat Jabatan Sementara</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        {{-- Tombol Export --}}
        <a href="{{ route('pgs_pjs.export', request()->query()) }}"
           style="display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #e5e7eb;white-space:nowrap;transition:all 0.15s;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export Excel
        </a>
        {{-- Tombol Tambah --}}
        <a href="{{ route('pgs_pjs.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah PGS / PJS
        </a>
    </div>
</div>

{{-- SEDANG BERLANGSUNG --}}
<div class="section-label">
    🟢 Sedang Berlangsung
    <span class="count-badge aktif">{{ $aktif->count() }} aktif</span>
</div>

@if($aktif->count() > 0)
<div class="aktif-grid">
    @foreach($aktif as $a)
    @php
        $totalHari        = $a->tanggal_mulai->diffInDays($a->tanggal_berakhir);
        $sudahJalan       = $a->tanggal_mulai->diffInDays(now());
        $totalHariRounded = round($totalHari);
        $progress         = $totalHariRounded > 0 ? min(100, round(($sudahJalan / $totalHariRounded) * 100)) : 100;
        $sisaHari         = round($a->sisaHari);
        $warnaProgress    = $sisaHari <= 7 ? '#ef4444' : ($sisaHari <= 30 ? '#f59e0b' : '#16a34a');
        $warnaSisa        = $sisaHari <= 7 ? 'danger' : ($sisaHari <= 30 ? 'warning' : '');
    @endphp
    <div class="aktif-card {{ $a->tipe }}">
        <div class="acard-top">
            <div class="acard-left">
                <span class="tipe-badge tipe-{{ $a->tipe }}">{{ $a->tipeLabel }}</span>
                <div class="acard-jabatan">{{ $a->jabatan_pgs_pjs }}</div>
                <div class="acard-karyawan">
                    👤 {{ $a->karyawan->nama }}
                    <span style="color:#9ca3af;">· NIK {{ $a->karyawan->nik }}</span>
                </div>
            </div>
            <div class="acard-right">
                <div class="sisa-hari">
                    <div class="sisa-num {{ $warnaSisa }}">{{ $sisaHari }}</div>
                    <div class="sisa-label">SISA HARI</div>
                </div>
                {{-- Tombol Hapus --}}
                <button type="button" class="btn-del-sm"
                    data-url="{{ route('pgs_pjs.destroy', $a) }}"
                    data-nama="{{ $a->jabatan_pgs_pjs }}"
                    onclick="openModal(this.dataset.url, this.dataset.nama)">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
                {{-- Tombol Akhiri --}}
                <button type="button"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;border:1px solid #fcd34d;background:#fffbeb;color:#d97706;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.12s;white-space:nowrap;"
                    data-url="{{ route('pgs_pjs.akhiri', $a) }}"
                    data-nama="{{ $a->jabatan_pgs_pjs }}"
                    data-mulai="{{ $a->tanggal_mulai->format('Y-m-d') }}"
                    onclick="openAkhiriModal(this.dataset.url, this.dataset.nama, this.dataset.mulai)">
                    ⏹ Akhiri
                </button>
            </div>
        </div>

        {{-- Progress Waktu --}}
        <div class="progress-wrap">
            <div class="progress-bar">
                <div class="progress-fill" style="width: 75%; background-color: #22c55e;"></div>
            </div>
        </div>

        <div class="acard-details">
            @if($a->direktorat)
            <div class="detail-item">
                <div class="detail-label">Kompartemen</div>
                <div class="detail-val">{{ $a->direktorat }}</div>
            </div>
            @endif
            @if($a->departemen)
            <div class="detail-item">
                <div class="detail-label">Departemen</div>
                <div class="detail-val">{{ $a->departemen }}</div>
            </div>
            @endif
            @if($a->no_sk)
            <div class="detail-item">
                <div class="detail-label">No. SK</div>
                <div class="detail-val">{{ $a->no_sk }}</div>
            </div>
            @endif
            @if($a->tanggal_sk)
            <div class="detail-item">
                <div class="detail-label">Tanggal SK</div>
                <div class="detail-val">{{ $a->tanggal_sk->format('d M Y') }}</div>
            </div>
            @endif
        </div>

        <div class="acard-period">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <strong>{{ $a->tanggal_mulai->format('d M Y') }}</strong>
            <span>→</span>
            <strong>{{ $a->tanggal_berakhir ? $a->tanggal_berakhir->format('d M Y') : 'Sedang Berlangsung' }}</strong>
            @if($totalHari > 0)
                <span style="margin-left:auto;font-size:11px;color:#9ca3af;">{{ (int) $totalHari }} hari total</span>
            @endif
        </div>

        @if($a->keterangan)
        <div style="margin-top:10px;padding:8px 12px;background:#f9fafb;border-radius:8px;font-size:12px;color:#6b7280;font-style:italic;border-left:3px solid #e5e7eb;">
            💬 {{ $a->keterangan }}
        </div>
        @endif
    </div>
    @endforeach
</div>
@else
<div class="empty-aktif">
    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    <p style="font-size:13px;font-weight:600;color:#6b7280;">Tidak ada PGS/PJS yang sedang berlangsung</p>
</div>
@endif

{{-- HISTORY --}}
<div class="section-label">
    🕐 History PGS & PJS
    <span class="count-badge">{{ $history->total() }} data</span>
</div>

@if($history->count() > 0)
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Tipe</th>
                    <th>Jabatan PGS/PJS</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Berakhir</th>
                    <th>No. SK</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history as $h)
                <tr>
                    <td>
                        <div class="karyawan-info">
                            <div class="karyawan-avatar">
                                @if($h->karyawan->foto)
                                    <img src="{{ Storage::url($h->karyawan->foto) }}" alt="">
                                @else
                                    {{ initials($h->karyawan->nama) }}
                                @endif
                            </div>
                            <div>
                                <div class="karyawan-name">{{ $h->karyawan->nama }}</div>
                                <div class="karyawan-nik">NIK {{ $h->karyawan->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="tipe-badge tipe-{{ $h->tipe }}">{{ $h->tipeLabel }}</span>
                    </td>
                    <td style="font-weight:600;">{{ $h->jabatan_pgs_pjs }}</td>
                    <td>{{ $h->tanggal_mulai->format('d M Y') }}</td>
                    <td>{{ $h->tanggal_berakhir->format('d M Y') }}</td>
                    <td>{{ $h->no_sk ?? '-' }}</td>
                    <td>
                        <button type="button" class="btn-del-sm"
                            data-url="{{ route('pgs_pjs.destroy', $h) }}"
                            data-nama="{{ $h->jabatan_pgs_pjs }}"
                            onclick="openModal(this.dataset.url, this.dataset.nama)">
                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($history->hasPages())
    <div class="table-footer">
        <span>Menampilkan <strong>{{ $history->firstItem() }}</strong>–<strong>{{ $history->lastItem() }}</strong> dari <strong>{{ $history->total() }}</strong></span>
        <div class="pagination-wrap">

            {{-- Prev --}}
            @if($history->onFirstPage())
                <span class="page-btn disabled">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $history->previousPageUrl() }}" class="page-btn">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @endif

            {{-- Nomor halaman --}}
            @php
                $cur  = $history->currentPage();
                $last = $history->lastPage();
                $s    = max(1, $cur - 2);
                $e    = min($last, $cur + 2);
            @endphp

            @if($s > 1)
                <a href="{{ $history->url(1) }}" class="page-btn">1</a>
                @if($s > 2)
                    <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                @endif
            @endif

            @for($i = $s; $i <= $e; $i++)
                <a href="{{ $history->url($i) }}" class="page-btn {{ $i == $cur ? 'active' : '' }}">{{ $i }}</a>
            @endfor

            @if($e < $last)
                @if($e < $last - 1)
                    <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
                @endif
                <a href="{{ $history->url($last) }}" class="page-btn">{{ $last }}</a>
            @endif

            {{-- Next --}}
            @if($history->hasMorePages())
                <a href="{{ $history->nextPageUrl() }}" class="page-btn">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span class="page-btn disabled">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif

        </div>
    </div>
    @endif
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
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 3000);
    });

    // Modal Hapus
    let deleteUrl = '';
    function openModal(url, nama) {
        deleteUrl = url;
        document.getElementById('modalDesc').innerHTML =
            'Kamu akan menghapus data <strong>' + nama + '</strong>.<br>Tindakan ini tidak dapat dibatalkan.';
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

    // Modal Akhiri
    function openAkhiriModal(url, nama, tglMulai) {
        document.getElementById('formAkhiri').action = url;
        document.getElementById('akhiriDesc').innerHTML =
            'Masukkan tanggal berakhir untuk <strong>' + nama + '</strong>.';
        document.getElementById('inputTglAkhiri').min   = tglMulai;
        document.getElementById('inputTglAkhiri').value = new Date().toISOString().split('T')[0];
        document.getElementById('modalAkhiri').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeAkhiriModal() {
        document.getElementById('modalAkhiri').classList.remove('show');
        document.body.style.overflow = '';
    }
    document.getElementById('modalAkhiri').addEventListener('click', function(e) {
        if (e.target === this) closeAkhiriModal();
    });

    // Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal();
            closeAkhiriModal();
        }
    });
</script>
@endpush