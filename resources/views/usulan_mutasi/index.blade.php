@extends('layouts.app')
@section('title', 'Rotasi & Mutasi')
@section('breadcrumb', 'Rotasi & Mutasi')

@push('styles')
<style>
* { box-sizing: border-box; }

/* ===== HEADER ===== */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 12px;
    flex-wrap: wrap;
}
.page-title { font-size: 18px; font-weight: 700; color: #111827; }
.page-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
.header-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #15803d;
    color: white;
    padding: 9px 16px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background .15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.btn-primary:hover { background: #166534; }
.btn-primary svg { width: 13px; height: 13px; stroke: white; fill: none; stroke-width: 2.5; }

/* ===== SEARCH ===== */
.search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    padding: 8px 12px;
    width: 240px;
    transition: border-color .15s;
}
.search-box:focus-within { border-color: #15803d; box-shadow: 0 0 0 2px rgba(21,128,61,.08); }
.search-box svg { width: 14px; height: 14px; stroke: #9ca3af; fill: none; flex-shrink: 0; }
.search-box input { border: none; outline: none; font-size: 13px; font-family: inherit; color: #111827; background: transparent; width: 100%; min-width: 0; }
.search-box input::placeholder { color: #9ca3af; }
.clear-btn { background: none; border: none; cursor: pointer; color: #d1d5db; font-size: 16px; padding: 0; display: none; flex-shrink: 0; }
.clear-btn.visible { display: block; }
.spin { display: none; width: 12px; height: 12px; border: 2px solid #e5e7eb; border-top-color: #15803d; border-radius: 50%; animation: rot .6s linear infinite; flex-shrink: 0; }
.spin.show { display: block; }
@keyframes rot { to { transform: rotate(360deg); } }

/* ===== CONTENT WRAPPER (AJAX search) ===== */
#upContent { transition: opacity .15s ease; }

/* ===== STATS ===== */
.stats-outer { overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px; padding-bottom: 4px; }
.stats-inner { display: flex; gap: 10px; width: max-content; }
.stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 18px; min-width: 120px; }
.stat-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
.stat-lbl { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.stat-num { font-size: 26px; font-weight: 800; line-height: 1; }

/* ===== WORKFLOW BAR (pill status + search) ===== */
.flow-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px; margin-bottom: 20px; }
.flow-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.flow-tag { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .6px; width: 84px; flex-shrink: 0; }

/* Outcome pills (Menunggu SK / Selesai) */
.outcomes { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; overflow-x: auto; }
.outcome-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 13px; border-radius: 20px; border: 1.5px solid #e5e7eb;
    background: white; cursor: pointer; font-family: inherit; font-size: 12px; font-weight: 600;
    color: #6b7280; white-space: nowrap; transition: all .15s;
}
.outcome-tab:hover { border-color: #d1d5db; }
.outcome-count { font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 20px; background: #f3f4f6; color: #6b7280; }

/* ===== TABLE CARD ===== */
.table-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; }
.table-outer { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.table-outer::-webkit-scrollbar { height: 8px; }
.table-outer::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
.table-outer::-webkit-scrollbar-track { background: #f3f4f6; }
.table-outer table { border-collapse: collapse; width: 100%; min-width: 1000px; }
.table-outer thead th {
    background: #f9fafb;
    padding: 11px 16px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .5px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.table-outer tbody tr { border-bottom: 1px solid #f3f4f6; }
.table-outer tbody tr:last-child { border-bottom: none; }
.table-outer tbody tr:hover { background: #fafafa; }
.table-outer tbody td { padding: 14px 16px; font-size: 13px; color: #374151; vertical-align: top; }

/* Avatar */
.av { width: 36px; height: 36px; border-radius: 50%; background: #f0fdf4; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; border: 1.5px solid #bbf7d0; }
.td-nama { font-weight: 700; color: #111827; font-size: 13px; }
.td-nik { font-size: 11px; color: #9ca3af; margin-top: 2px; }

/* Posisi cols */
.pos-block { border-left: 3px solid #e5e7eb; padding-left: 10px; }
.pos-block.tujuan { border-left-color: #15803d; }
.pos-title { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .7px; margin-bottom: 8px; color: #9ca3af; }
.pos-block.tujuan .pos-title { color: #15803d; }
.pos-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 4px; }
.pos-lbl { font-size: 10px; color: #9ca3af; font-weight: 600; flex-shrink: 0; width: 80px; }
.pos-val { font-size: 12px; color: #111827; font-weight: 500; line-height: 1.4; }

/* Badge */
.badge { display: inline-flex; align-items: center; padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }

/* Inline actions */
.icon-row { display: flex; align-items: center; gap: 5px; }
.btn-ic { width: 28px; height: 28px; border-radius: 7px; border: 1px solid #e5e7eb; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .12s; text-decoration: none; flex-shrink: 0; }
.btn-ic.v:hover { background: #eff6ff; border-color: #bfdbfe; }
.btn-ic.v svg { stroke: #3b82f6; }
.btn-ic.d:hover { background: #fef2f2; border-color: #fecaca; }
.btn-ic.d svg { stroke: #ef4444; }
.btn-ic svg { width: 13px; height: 13px; fill: none; stroke-width: 2; }

/* Empty */
.empty-wrap { text-align: center; padding: 56px 20px; }
.empty-ico { width: 52px; height: 52px; background: #f9fafb; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; border: 1px solid #e5e7eb; }
.empty-ico svg { width: 22px; height: 22px; stroke: #d1d5db; fill: none; stroke-width: 1.5; }
.empty-wrap h3 { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 5px; }
.empty-wrap p { font-size: 13px; color: #9ca3af; }

/* Pagination */
.pag-wrap { display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; border-top: 1px solid #f3f4f6; background: #fafafa; font-size: 12px; color: #6b7280; flex-wrap: wrap; gap: 8px; }
.pag-btn { width: 30px; height: 30px; border-radius: 7px; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #374151; background: white; font-size: 12px; transition: background .12s; cursor: pointer; }
.pag-btn.active { background: #15803d; border-color: #15803d; color: white; font-weight: 700; }
.pag-btn.disabled { opacity: .35; pointer-events: none; }
.pag-row { display: flex; align-items: center; gap: 3px; }

/* Highlight search */
mark { background: #fef08a; border-radius: 2px; padding: 0 1px; color: inherit; font-weight: 700; }

/* Toast */
.toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 9999; pointer-events: none; }
.toast { display: flex; align-items: center; gap: 10px; background: white; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; border-radius: 12px; padding: 14px 18px; box-shadow: 0 8px 32px rgba(0,0,0,.12); font-size: 13px; color: #15803d; font-weight: 500; min-width: 280px; position: relative; overflow: hidden; pointer-events: all; animation: tIn .3s forwards; }
.toast.hiding { animation: tOut .3s forwards; }
.toast-ic { width: 22px; height: 22px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.toast-ic svg { width: 11px; height: 11px; stroke: #16a34a; fill: none; stroke-width: 2.5; }
.toast-x { border: none; background: transparent; color: #9ca3af; cursor: pointer; font-size: 18px; padding: 0; margin-left: auto; }
.toast-bar { position: absolute; bottom: 0; left: 0; height: 3px; background: #16a34a; animation: tProg 3s linear forwards; }
@keyframes tIn { from{opacity:0;transform:translateX(110%)}to{opacity:1;transform:translateX(0)} }
@keyframes tOut { from{opacity:1}to{opacity:0;transform:translateX(110%)} }
@keyframes tProg { from{width:100%}to{width:0%} }

/* Modal */
.modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,.5); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; }
.modal-bg.show { display: flex; }
.modal-box { background: white; border-radius: 18px; padding: 30px; width: 100%; max-width: 380px; margin: 16px; box-shadow: 0 24px 64px rgba(0,0,0,.18); text-align: center; animation: mIn .25s cubic-bezier(.4,0,.2,1); }
.modal-ico { width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
.modal-ico svg { width: 26px; height: 26px; stroke: #ef4444; fill: none; stroke-width: 1.8; }
.modal-title { font-size: 17px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.modal-desc { font-size: 13px; color: #6b7280; line-height: 1.6; margin-bottom: 22px; }
.modal-acts { display: flex; gap: 10px; }
.mbtn { flex: 1; padding: 11px; border-radius: 10px; font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer; border: none; }
.mbtn.c { background: #f9fafb; color: #374151; border: 1px solid #e5e7eb; }
.mbtn.c:hover { background: #f3f4f6; }
.mbtn.r { background: #ef4444; color: white; }
.mbtn.r:hover { background: #dc2626; }
@keyframes mIn { from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)} }

/* ===== FORM TERBIT SK ===== */
.sk-lbl { display:block; font-size:11px; font-weight:700; color:#6b7280; margin-bottom:4px; }
.sk-req { color:#9ca3af; font-weight:700; }
.sk-note { font-size:11px; color:#9ca3af; margin:-2px 0 4px; }
.sk-inp { width:100%; border:1.5px solid #e5e7eb; border-radius:8px; padding:8px 10px; font-size:13px; font-family:inherit; color:#111827; outline:none; background:white; }
.sk-inp:focus { border-color:#16a34a; box-shadow:0 0 0 2px rgba(22,163,74,.08); }
.btn-sk { padding:6px 12px; background:#15803d; color:white; border:none; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; white-space:nowrap; display:inline-flex; align-items:center; gap:5px; }
.btn-sk:hover { background:#166534; }
.btn-sk svg { width:11px; height:11px; stroke:white; fill:none; stroke-width:2; }
.sk-done { display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#15803d; border-radius:8px; padding:6px 9px; font-size:10px; font-weight:700; line-height:1.3; }
.sk-done svg { width:12px; height:12px; stroke:#15803d; fill:none; stroke-width:2.5; flex-shrink:0; }

@media (max-width: 480px) {
    .search-box { width: 100%; }
    .header-right { width: 100%; }
    .btn-primary { flex: 1; justify-content: center; }
    .page-header { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 640px) {
    .flow-row { flex-wrap: nowrap; }
    .flow-tag { width: 70px; }
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="toast-wrap" id="twrap">
    <div class="toast" id="toast">
        <div class="toast-ic"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <span>{{ session('success') }}</span>
        <button class="toast-x" onclick="closeToast()">×</button>
        <div class="toast-bar"></div>
    </div>
</div>
@endif

<div class="modal-bg" id="mHapus">
    <div class="modal-box">
        <div class="modal-ico"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></div>
        <div class="modal-title">Hapus Usulan?</div>
        <div class="modal-desc" id="mDesc">Data tidak dapat dikembalikan.</div>
        <div class="modal-acts">
            <button class="mbtn c" onclick="closeModal()">Batal</button>
            <button class="mbtn r" onclick="submitHapus()">Hapus</button>
        </div>
    </div>
</div>
<form id="fHapus" method="POST" style="display:none">@csrf @method('DELETE')</form>

{{-- MODAL TERBIT SK --}}
<div class="modal-bg" id="skModal">
    <div class="modal-box" style="max-width:420px;text-align:left">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
            <div style="width:42px;height:42px;border-radius:11px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;border:1px solid #bbf7d0">
                <svg viewBox="0 0 24 24" width="19" height="19" stroke="#15803d" fill="none" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>
            </div>
            <div>
                <div class="modal-title" style="margin:0">Terbitkan SK</div>
                <div style="font-size:12px;color:#9ca3af"><span id="skJenis">—</span> · <span id="skNama">—</span></div>
            </div>
        </div>
        <div class="sk-note">Kolom bertanda <span class="sk-req">*</span> wajib diisi.</div>

        <form id="skForm" method="POST">
            @csrf
            @method('PATCH')
            <div style="display:grid;gap:12px;margin-top:10px">
                <div>
                    <label class="sk-lbl">Nomor SK <span class="sk-req">*</span></label>
                    <input type="text" name="no_sk" id="skNoSk" class="sk-inp" placeholder="cth: 123/SK/DIR/2026" required>
                </div>
                <div>
                    <label class="sk-lbl">TMT — Tanggal Mulai Berlaku <span class="sk-req">*</span></label>
                    <input type="date" name="tmt" id="skTmt" class="sk-inp" required>
                </div>
                <div>
                    <label class="sk-lbl">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" class="sk-inp" placeholder="Catatan tambahan...">
                </div>
                <div style="display:flex;gap:8px;font-size:11px;color:#6b7280;background:#fafafa;border:1px solid #f3f4f6;border-radius:8px;padding:9px 11px;line-height:1.5">
                    <svg viewBox="0 0 24 24" width="14" height="14" stroke="#9ca3af" fill="none" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <div>Posisi tujuan sudah dipilih saat usulan dibuat. Menyimpan akan membuat <strong>riwayat jabatan baru</strong> (tipe: mutasi) &amp; memperbarui posisi karyawan. <strong>Job Grade &amp; Person Grade tidak berubah.</strong></div>
                </div>
            </div>
            <div class="modal-acts" style="margin-top:18px">
                <button type="button" class="mbtn c" onclick="closeSk()">Batal</button>
                <button type="submit" class="mbtn" style="background:#15803d;color:white">Terbitkan SK</button>
            </div>
        </form>
    </div>
</div>

{{-- DEFINISI ARRAY (dipakai stats, workflow bar, panels) --}}
@php
$sc = [
    'menunggu' => ['Menunggu SK', '#d97706'],
    'selesai'  => ['Selesai',     '#15803d'],
];
$tabs = [
    'menunggu' => 'Menunggu SK',
    'selesai'  => 'Selesai',
];
// Hasil/status — pill warnanya sama dengan $sc supaya konsisten
$outcomes = [
    'menunggu' => ['Menunggu SK', '#d97706', '#fef3c7'],
    'selesai'  => ['Selesai',     '#15803d', '#dcfce7'],
];
@endphp

{{-- HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">Rotasi &amp; Mutasi</div>
        <div class="page-sub">Kelola usulan rotasi dan mutasi karyawan hingga penerbitan SK</div>
    </div>
    <div class="header-right">
        <a href="{{ route('usulan_mutasi.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Usulan
        </a>
    </div>
</div>

{{-- STATS (di luar #upContent, angka di-update via JS) --}}
<div class="stats-outer">
    <div class="stats-inner">
        @foreach($sc as $k => $v)
        <div class="stat-card">
            <div class="stat-lbl"><span class="stat-dot" style="background:{{ $v[1] }}"></span>{{ $v[0] }}</div>
            <div class="stat-num" id="stat-{{ $k }}" style="color:{{ $v[1] }}">{{ $counts[$k] }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- WORKFLOW BAR: status (pill) + search --}}
<div class="flow-card">
    <div class="flow-row">
        <span class="flow-tag">Status</span>
        <div class="outcomes">
            @foreach($outcomes as $k => $o)
            @php
                $oActive = $activeTab === $k;
                $ocText = $oActive ? $o[1] : '#6b7280';
                $ocBorder = $oActive ? $o[1] : '#e5e7eb';
                $ocBg = $oActive ? $o[2] : 'white';
                $ocCountBg = $oActive ? 'white' : '#f3f4f6';
            @endphp
            <button class="outcome-tab {{ $oActive?'active':'' }}"
                style="color:{{ $ocText }};border-color:{{ $ocBorder }};background:{{ $ocBg }}"
                onclick="switchTab('{{ $k }}',this)" data-tabkey="{{ $k }}">
                {{ $o[0] }} <span class="outcome-count" style="background:{{ $ocCountBg }};color:{{ $ocText }}">{{ $counts[$k] }}</span>
            </button>
            @endforeach
        </div>
        <div class="search-box">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="sInput" value="{{ request('search') }}" placeholder="Cari nama / NIK..." autocomplete="off">
            <div class="spin" id="spin"></div>
            <button class="clear-btn {{ request('search') ? 'visible':'' }}" id="clrBtn" onclick="clearSearch()">×</button>
        </div>
    </div>
</div>

{{-- ===== KONTEN YANG DI-UPDATE SAAT SEARCH (panel saja) ===== --}}
<div id="upContent">
<div id="countData" data-json='@json($counts)' hidden></div>

{{-- PANELS --}}
@foreach($tabs as $tabKey => $tabLabel)
<div id="p-{{ $tabKey }}" style="{{ $activeTab===$tabKey?'':'display:none' }}">
<div class="table-card">
    @php $d = $statusGroups[$tabKey]; @endphp
    @if($d->total() > 0)
    <div class="table-outer">
        <table>
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Posisi Awal</th>
                    <th>Posisi Tujuan</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d as $i => $u)
                @php
                    $jc = $u->jenis_color;
                    $dirTo = optional($u->direktoratTujuan)->nama_direktorat ?? optional($u->direktoratTujuan)->nama;
                @endphp
                <tr>
                    <td style="color:#d1d5db;font-size:11px;font-weight:600;vertical-align:middle">
                        {{ ($d->currentPage()-1)*$d->perPage()+$i+1 }}
                    </td>

                    {{-- NAMA --}}
                    <td style="vertical-align:middle;min-width:160px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="av">{{ initials($u->karyawan->nama) }}</div>
                            <div>
                                <div class="td-nama">{{ $u->karyawan->nama??'-' }}</div>
                                <div class="td-nik">{{ $u->karyawan->nik??'-' }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- JENIS --}}
                    <td style="vertical-align:middle;white-space:nowrap">
                        <span class="badge" style="background:{{ $jc['bg'] }};color:{{ $jc['text'] }}">{{ $u->jenis_label }}</span>
                    </td>

                    {{-- POSISI AWAL --}}
                    <td style="min-width:200px">
                        <div class="pos-block">
                        <div class="pos-title">Posisi Awal</div>
                        <div class="pos-row"><span class="pos-lbl">Jabatan</span><span class="pos-val">{{ $u->jabatan_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Direktorat</span><span class="pos-val">{{ $u->direktorat_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Kompartemen</span><span class="pos-val">{{ $u->kompartemen_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Departemen</span><span class="pos-val">{{ $u->departemen_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Grade</span><span class="pos-val" style="font-weight:700">JG {{ $u->job_grade_saat_ini??'-' }} · PG {{ $u->person_grade_saat_ini??'-' }}</span></div>
                        </div>
                    </td>

                    {{-- POSISI TUJUAN --}}
                    <td style="min-width:200px">
                        <div class="pos-block tujuan">
                        <div class="pos-title">Posisi Tujuan</div>
                        <div class="pos-row"><span class="pos-lbl">Jabatan</span><span class="pos-val" style="font-weight:600;color:#111827">{{ optional($u->jabatanTujuan)->nama_jabatan ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Direktorat</span><span class="pos-val">{{ $dirTo ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Kompartemen</span><span class="pos-val">{{ optional($u->kompartemenTujuan)->nama_kompartemen ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Departemen</span><span class="pos-val">{{ optional($u->departemenTujuan)->nama_departemen ?? '-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Grade</span><span class="pos-val" style="font-weight:700;color:#15803d">JG {{ $u->job_grade_saat_ini??'-' }} · PG {{ $u->person_grade_saat_ini??'-' }} <span style="color:#9ca3af;font-weight:500">(tetap)</span></span></div>
                        </div>
                    </td>

                    {{-- DIBUAT OLEH --}}
                    <td style="vertical-align:middle;min-width:120px">
                        <div style="font-size:12px;font-weight:600;color:#374151">{{ $u->createdBy->name??'-' }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $u->created_at->format('d M Y') }}</div>
                    </td>

                    {{-- STATUS --}}
                    <td style="vertical-align:middle;white-space:nowrap">
                        @if($u->sk_diproses)
                            <span class="badge" style="background:#dcfce7;color:#15803d">Selesai</span>
                        @else
                            <span class="badge" style="background:#fef3c7;color:#d97706">Menunggu SK</span>
                        @endif
                    </td>

                    {{-- AKSI --}}
                    <td style="vertical-align:middle;min-width:160px">
                        <div class="icon-row">
                            @if($u->sk_diproses)
                                <span class="sk-done" title="SK sudah diterbitkan">
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                    SK {{ $u->no_sk }} · TMT {{ \Carbon\Carbon::parse($u->tmt)->format('d M Y') }}
                                </span>
                            @else
                                <button type="button" class="btn-sk"
                                    data-url="{{ route('usulan_mutasi.terbitkan_sk', $u) }}"
                                    data-nama="{{ $u->karyawan->nama }}"
                                    data-jenis="{{ $u->jenis_label }}"
                                    onclick="openSk(this)">
                                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    Terbit SK
                                </button>
                            @endif
                            <button type="button" class="btn-ic d" title="Hapus"
                                data-url="{{ route('usulan_mutasi.destroy',$u) }}"
                                data-nama="{{ addslashes($u->karyawan->nama??'') }}">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="pag-wrap">
        <span>Tampil <strong style="color:#374151">{{ $d->firstItem() }}–{{ $d->lastItem() }}</strong> dari <strong style="color:#374151">{{ $d->total() }}</strong></span>
        @if($d->hasPages())
        <div class="pag-row">
            <a href="{{ $d->onFirstPage() ? '#' : $d->previousPageUrl().'&tab='.$tabKey }}" class="pag-btn {{ $d->onFirstPage()?'disabled':'' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            @php $cur=$d->currentPage();$last=$d->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s > 1)
                <a href="{{ $d->url(1) }}&tab={{ $tabKey }}" class="pag-btn">1</a>
                @if($s > 2)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
            @endif
            @for($pg=$s;$pg<=$e;$pg++)
                <a href="{{ $d->url($pg) }}&tab={{ $tabKey }}" class="pag-btn {{ $pg===$cur?'active':'' }}">{{ $pg }}</a>
            @endfor
            @if($e < $last)
                @if($e < $last-1)<span style="padding:0 2px;color:#9ca3af">…</span>@endif
                <a href="{{ $d->url($last) }}&tab={{ $tabKey }}" class="pag-btn">{{ $last }}</a>
            @endif
            <a href="{{ $d->hasMorePages() ? $d->nextPageUrl().'&tab='.$tabKey : '#' }}" class="pag-btn {{ $d->hasMorePages()?'':'disabled' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        @endif
    </div>

    @else
    <div class="empty-wrap">
        <div class="empty-ico"><svg viewBox="0 0 24 24"><path d="M16 3h5v5"/><path d="M21 3l-7 7"/><path d="M8 21H3v-5"/><path d="M3 21l7-7"/></svg></div>
        <h3>Tidak ada data {{ $tabLabel }}</h3>
        <p>Belum ada usulan rotasi/mutasi dengan status ini</p>
    </div>
    @endif
</div>
</div>
@endforeach

</div>{{-- /#upContent --}}

@endsection

@push('scripts')
<script>
// Toast
function closeToast() {
    const t = document.getElementById('toast');
    if(!t) return;
    t.classList.add('hiding');
    setTimeout(() => document.getElementById('twrap')?.remove(), 300);
}
window.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('toast')) setTimeout(closeToast, 3000);
});

// Tabs (pill status Menunggu SK / Selesai)
const OUTCOME_COLORS = {
    menunggu: ['#d97706', '#fef3c7'],
    selesai:  ['#15803d', '#dcfce7'],
};

function switchTab(tab) {
    document.querySelectorAll('[id^="p-"]').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('p-' + tab);
    if (panel) panel.style.display = '';

    document.querySelectorAll('.outcome-tab').forEach(b => {
        const isActive = b.dataset.tabkey === tab;
        b.classList.toggle('active', isActive);
        const countEl = b.querySelector('.outcome-count');
        const colors = OUTCOME_COLORS[b.dataset.tabkey];
        const [text, bg] = isActive && colors ? colors : ['#6b7280', 'white'];
        b.style.color = text;
        b.style.borderColor = isActive ? text : '#e5e7eb';
        b.style.background = bg;
        if (countEl) { countEl.style.background = isActive ? 'white' : '#f3f4f6'; countEl.style.color = text; }
    });

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url.toString());
}

// Modal Hapus
let dUrl = '';
function openModal(url, nama) {
    dUrl = url;
    document.getElementById('mDesc').innerHTML = 'Hapus usulan untuk <strong>'+nama+'</strong>?<br>Data tidak dapat dikembalikan.';
    document.getElementById('mHapus').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('mHapus').classList.remove('show');
    document.body.style.overflow = '';
}
function submitHapus() {
    document.getElementById('fHapus').action = dUrl;
    document.getElementById('fHapus').submit();
}
document.getElementById('mHapus').addEventListener('click', e => { if(e.target===document.getElementById('mHapus')) closeModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') { closeModal(); closeSk(); } });

// Delegasi klik tombol hapus (data-* attributes, hindari onclick inline berisi route())
document.addEventListener('click', function(e) {
    const delBtn = e.target.closest('.btn-ic.d');
    if (delBtn) openModal(delBtn.dataset.url, delBtn.dataset.nama);
});

// ===== MODAL TERBIT SK =====
function openSk(btn) {
    const d = btn.dataset;
    document.getElementById('skForm').action = d.url;
    document.getElementById('skNama').textContent = d.nama || '';
    document.getElementById('skJenis').textContent = d.jenis || '';
    document.getElementById('skNoSk').value = '';
    document.getElementById('skTmt').value = '';
    document.getElementById('skModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeSk() {
    document.getElementById('skModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('skModal').addEventListener('click', e => { if(e.target===document.getElementById('skModal')) closeSk(); });

// ===== REAL-TIME SEARCH (AJAX, tanpa reload halaman) =====
let sTimer = null;
const sInp = document.getElementById('sInput');
const clrB = document.getElementById('clrBtn');
const spin = document.getElementById('spin');

sInp.addEventListener('input', function() {
    clrB.classList.toggle('visible', this.value.trim().length > 0);
    clearTimeout(sTimer);
    sTimer = setTimeout(() => doSearch(this.value.trim()), 300);
});
sInp.addEventListener('keydown', e => {
    if (e.key === 'Enter') { clearTimeout(sTimer); doSearch(sInp.value.trim()); }
});

function clearSearch() {
    sInp.value = '';
    clrB.classList.remove('visible');
    doSearch('');
    sInp.focus();
}

function doSearch(kw) {
    const url = new URL(window.location.href);
    if (kw) url.searchParams.set('search', kw);
    else url.searchParams.delete('search');
    // reset semua pagination ke halaman 1
    ['page_menunggu','page_selesai'].forEach(p => url.searchParams.delete(p));

    window.history.pushState({}, '', url.toString());

    const content = document.getElementById('upContent');
    spin.classList.add('show');
    content.style.opacity = '0.5';

    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const fresh = doc.getElementById('upContent');
            if (fresh) content.innerHTML = fresh.innerHTML;
            updateCounts();        // sinkron angka stats & pill (di luar #upContent)
            content.style.opacity = '1';
            spin.classList.remove('show');
            if (kw) highlightText(content, kw);
        })
        .catch(() => {
            content.style.opacity = '1';
            spin.classList.remove('show');
        });
}

// Update angka stats & pill count dari #countData (hasil filter)
function updateCounts() {
    const cd = document.getElementById('countData');
    if (!cd) return;
    let counts;
    try { counts = JSON.parse(cd.dataset.json); } catch(e) { return; }
    Object.keys(counts).forEach(k => {
        const st = document.getElementById('stat-' + k);
        if (st) st.textContent = counts[k];
        document.querySelectorAll('[data-tabkey="' + k + '"] .outcome-count')
            .forEach(el => el.textContent = counts[k]);
    });
}

function highlightText(root, keyword) {
    const kw = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp('(' + kw + ')', 'gi');
    root.querySelectorAll('.td-nama, .td-nik').forEach(node => {
        node.innerHTML = node.textContent.replace(regex, '<mark>$1</mark>');
    });
}

// Browser back/forward
window.addEventListener('popstate', () => {
    const url = new URL(window.location.href);
    const kw  = url.searchParams.get('search') || '';
    sInp.value = kw;
    clrB.classList.toggle('visible', kw.length > 0);
    doSearch(kw);
});
</script>
@endpush
