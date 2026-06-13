@extends('layouts.app')
@section('title', 'Usulan Promosi')
@section('breadcrumb', 'Usulan Promosi')

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

/* ===== STATS ===== */
.stats-outer { overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px; padding-bottom: 4px; }
.stats-inner { display: flex; gap: 10px; width: max-content; }
.stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 18px; min-width: 120px; }
.stat-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
.stat-lbl { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.stat-num { font-size: 26px; font-weight: 800; line-height: 1; }

/* ===== TABS ===== */
.tabs-outer { overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
.tabs-inner { display: flex; gap: 0; width: max-content; }
.tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    font-family: inherit;
    color: #6b7280;
    background: transparent;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all .15s;
    white-space: nowrap;
}
.tab-btn:hover { color: #374151; }
.tab-btn.active { color: #15803d; border-bottom-color: #15803d; }
.tab-count { font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 20px; background: #f3f4f6; color: #6b7280; }
.tab-btn.active .tab-count { background: #dcfce7; color: #15803d; }

/* ===== TABLE CARD ===== */
.table-card { background: white; border-radius: 14px; border: 1px solid #e5e7eb; overflow: hidden; }
.table-outer { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.table-outer table { border-collapse: collapse; width: 100%; min-width: 900px; }
.table-outer thead th {
    background: #f9fafb;
    padding: 11px 16px;
    font-size: 10px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .6px;
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
.pos-title { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .7px; margin-bottom: 8px; }
.pos-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 4px; }
.pos-lbl { font-size: 10px; color: #9ca3af; font-weight: 600; flex-shrink: 0; width: 80px; }
.pos-val { font-size: 12px; color: #111827; font-weight: 500; line-height: 1.4; }

/* Badge */
.badge { display: inline-flex; align-items: center; padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }

/* Inline actions */
.act-col { display: flex; flex-direction: column; gap: 6px; }
.act-select {
    border: 1.5px solid #e5e7eb;
    border-radius: 7px;
    padding: 5px 22px 5px 8px;
    font-size: 11px;
    font-family: inherit;
    color: #374151;
    background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%239ca3af'/%3E%3C/svg%3E") no-repeat right 6px center;
    outline: none;
    cursor: pointer;
    appearance: none;
    width: 100%;
    max-width: 160px;
    transition: border-color .15s;
}
.act-select:focus { border-color: #15803d; }
.act-date { border: 1.5px solid #e5e7eb; border-radius: 7px; padding: 5px 8px; font-size: 11px; font-family: inherit; color: #374151; background: white; outline: none; display: none; transition: border-color .15s; width: 100%; max-width: 160px; }
.act-date.show { display: block; }
.btn-s { padding: 5px 12px; background: #15803d; color: white; border: none; border-radius: 7px; font-size: 11px; font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; }
.btn-s:hover { background: #166534; }
.btn-v { padding: 5px 10px; background: #f59e0b; color: white; border: none; border-radius: 7px; font-size: 11px; font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; }
.btn-v:hover { background: #d97706; }
.icon-row { display: flex; align-items: center; gap: 5px; margin-top: 2px; }
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

@media (max-width: 480px) {
    .search-box { width: 100%; }
    .header-right { width: 100%; }
    .btn-primary { flex: 1; justify-content: center; }
    .page-header { flex-direction: column; align-items: flex-start; }
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

{{-- HEADER --}}
<div class="page-header">
    <div>
        <div class="page-title">🏆 Usulan Promosi</div>
        <div class="page-sub">Manajemen usulan promosi karyawan</div>
    </div>
    <div class="header-right">
        <div class="search-box">
            <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="sInput" value="{{ request('search') }}" placeholder="Cari nama / NIK..." autocomplete="off">
            <div class="spin" id="spin"></div>
            <button class="clear-btn {{ request('search') ? 'visible':'' }}" id="clrBtn" onclick="clearSearch()">×</button>
        </div>
        <a href="{{ route('usulan_promosi.create') }}" class="btn-primary">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Usulan
        </a>
    </div>
</div>

{{-- STATS --}}
@php
$sc = [
    'draft'        => ['Draft',       '#6b7280'],
    'verif_berkas' => ['Verif Berkas','#d97706'],
    'sidang'       => ['Sidang',      '#1d4ed8'],
    'lulus'        => ['Lulus',       '#15803d'],
    'tidak_lulus'  => ['Tidak Lulus', '#dc2626'],
    'tanpa_sidang' => ['Tanpa Sidang','#7c3aed'],
    'ditolak'      => ['Ditolak',     '#be185d'],
];
@endphp
<div class="stats-outer">
    <div class="stats-inner">
        @foreach($sc as $k => $v)
        <div class="stat-card">
            <div class="stat-lbl"><span class="stat-dot" style="background:{{ $v[1] }}"></span>{{ $v[0] }}</div>
            <div class="stat-num" style="color:{{ $v[1] }}">{{ $counts[$k] }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- TABS --}}
@php
$tabs = [
    'draft'        => ['Draft',       '📝'],
    'verif_berkas' => ['Verif Berkas','🔍'],
    'sidang'       => ['Sidang',      '⚖️'],
    'lulus'        => ['Lulus',       '✅'],
    'tidak_lulus'  => ['Tidak Lulus', '❌'],
    'tanpa_sidang' => ['Tanpa Sidang','📄'],
    'ditolak'      => ['Ditolak',     '🚫'],
];
$bc = [
    'draft'       =>['#f3f4f6','#374151'],'verif_berkas'=>['#fef3c7','#d97706'],
    'sidang'      =>['#dbeafe','#1d4ed8'],'lulus'       =>['#dcfce7','#15803d'],
    'tidak_lulus' =>['#fee2e2','#dc2626'],'tanpa_sidang'=>['#f5f3ff','#7c3aed'],
    'ditolak'     =>['#fce7f3','#be185d'],
];
@endphp
<div class="tabs-outer">
    <div class="tabs-inner">
        @foreach($tabs as $k => $t)
        <button class="tab-btn {{ $activeTab===$k?'active':'' }}" onclick="switchTab('{{ $k }}',this)">
            {{ $t[1] }} {{ $t[0] }} <span class="tab-count">{{ $counts[$k] }}</span>
        </button>
        @endforeach
    </div>
</div>

{{-- PANELS --}}
@foreach($tabs as $tabKey => $tab)
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
                    <th>Posisi Awal</th>
                    <th>Posisi Baru</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
                    @if($tabKey==='verif_berkas')<th>Tindak Lanjut</th>@endif
                    @if($tabKey==='sidang')<th>Hasil Sidang</th>@endif
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d as $i => $u)
                @php $cl = $bc[$u->status] ?? ['#f3f4f6','#374151']; @endphp
                <tr>
                    <td style="color:#d1d5db;font-size:11px;font-weight:600;vertical-align:middle">
                        {{ ($d->currentPage()-1)*$d->perPage()+$i+1 }}
                    </td>

                    {{-- NAMA --}}
                    <td style="vertical-align:middle;min-width:160px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="av">{{ strtoupper(substr($u->karyawan->nama??'-',0,2)) }}</div>
                            <div>
                                <div class="td-nama">{{ $u->karyawan->nama??'-' }}</div>
                                <div class="td-nik">{{ $u->karyawan->nik??'-' }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- POSISI AWAL --}}
                    <td style="min-width:200px">
                        <div class="pos-title" style="color:#6b7280">📌 Posisi Awal</div>
                        <div class="pos-row"><span class="pos-lbl">Jabatan</span><span class="pos-val">{{ $u->jabatan_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Departemen</span><span class="pos-val">{{ $u->departemen_saat_ini??$u->karyawan->departemen->nama_departemen??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Kompartemen</span><span class="pos-val">{{ $u->kompartemen_saat_ini??$u->karyawan->kompartemen->nama_kompartemen??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Job Grade</span><span class="pos-val" style="font-weight:700">JG {{ $u->job_grade_saat_ini??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Person Grade</span><span class="pos-val" style="font-weight:700">PG {{ $u->person_grade_saat_ini??'-' }}</span></div>
                    </td>

                    {{-- POSISI BARU --}}
                    <td style="min-width:200px">
                        <div class="pos-title" style="color:#15803d">🎯 Posisi Baru</div>
                        <div class="pos-row"><span class="pos-lbl">Jabatan</span><span class="pos-val" style="font-weight:600;color:#111827">{{ $u->jabatan_tujuan }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Departemen</span><span class="pos-val">{{ $u->karyawan->departemen->nama_departemen??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Kompartemen</span><span class="pos-val">{{ $u->karyawan->kompartemen->nama_kompartemen??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Job Grade</span><span class="pos-val" style="font-weight:700;color:#15803d">JG {{ $u->job_grade_promosi??'-' }}</span></div>
                        <div class="pos-row"><span class="pos-lbl">Person Grade</span><span class="pos-val" style="font-weight:700;color:#15803d">PG {{ $u->person_grade_promosi??'-' }}</span></div>
                    </td>

                    {{-- DIBUAT OLEH --}}
                    <td style="vertical-align:middle;min-width:120px">
                        <div style="font-size:12px;font-weight:600;color:#374151">{{ $u->createdBy->name??'-' }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $u->created_at->format('d M Y') }}</div>
                    </td>

                    {{-- STATUS --}}
                    <td style="vertical-align:middle;white-space:nowrap">
                        <span class="badge" style="background:{{ $cl[0] }};color:{{ $cl[1] }}">{{ $u->status_label }}</span>
                    </td>

                    {{-- TINDAK LANJUT --}}
                    @if($tabKey==='verif_berkas')
                    <td style="vertical-align:middle;min-width:180px">
                        <form method="POST" action="{{ route('usulan_promosi.update_status',$u) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" id="svf{{ $u->id }}" value="verif_berkas">
                            <input type="hidden" name="tindak_lanjut" id="tlh{{ $u->id }}" value="{{ $u->tindak_lanjut }}">
                            <div class="act-col">
                                <select class="act-select" onchange="onTL({{ $u->id }},this.value)">
                                    <option value="">— Pilih —</option>
                                    <option value="sidang"  {{ $u->tindak_lanjut==='sidang' ?'selected':'' }}>Lanjut Sidang</option>
                                    <option value="ditolak" {{ $u->tindak_lanjut==='ditolak'?'selected':'' }}>Ditolak</option>
                                </select>
                                <input type="date" name="tanggal_sidang" id="tsd{{ $u->id }}"
                                    class="act-date {{ $u->tindak_lanjut==='sidang'?'show':'' }}"
                                    value="{{ $u->tanggal_sidang?->format('Y-m-d') }}">
                                <button type="submit" class="btn-s">Simpan</button>
                            </div>
                        </form>
                    </td>
                    @endif

                    {{-- HASIL SIDANG --}}
                    @if($tabKey==='sidang')
                    <td style="vertical-align:middle;min-width:180px">
                        <form method="POST" action="{{ route('usulan_promosi.update_status',$u) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="tindak_lanjut" value="{{ $u->tindak_lanjut }}">
                            <input type="hidden" name="tanggal_sidang" value="{{ $u->tanggal_sidang?->format('Y-m-d') }}">
                            <input type="hidden" name="status" id="ssd{{ $u->id }}" value="{{ $u->status }}">
                            <div class="act-col">
                                <select class="act-select" name="hasil_sidang" onchange="onHS({{ $u->id }},this.value)">
                                    <option value="">— Pilih —</option>
                                    <option value="lulus"       {{ $u->hasil_sidang==='lulus'      ?'selected':'' }}>Lulus</option>
                                    <option value="tidak_lulus" {{ $u->hasil_sidang==='tidak_lulus'?'selected':'' }}>Tidak Lulus</option>
                                    <option value="tanpa_sidang"{{ $u->hasil_sidang==='tanpa_sidang'?'selected':'' }}>Tanpa Sidang</option>
                                </select>
                                <button type="submit" class="btn-s">Simpan</button>
                            </div>
                        </form>
                    </td>
                    @endif

                    {{-- AKSI --}}
                    <td style="vertical-align:middle;min-width:80px">
                        <div class="act-col">
                            @if($tabKey==='draft')
                            <form method="POST" action="{{ route('usulan_promosi.update_status',$u) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="verif_berkas">
                                <button type="submit" class="btn-v">Verif →</button>
                            </form>
                            @endif
                            <div class="icon-row">
                                <a href="{{ route('usulan_promosi.show',$u) }}" class="btn-ic v" title="Detail">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <button type="button" class="btn-ic d" title="Hapus"
                                    onclick="openModal('{{ route('usulan_promosi.destroy',$u) }}','{{ addslashes($u->karyawan->nama??'') }}')">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </div>
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
        <div class="empty-ico"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <h3>Tidak ada data {{ $tab[0] }}</h3>
        <p>Belum ada usulan promosi dengan status ini</p>
    </div>
    @endif
</div>
</div>
@endforeach

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

// Tabs
function switchTab(tab, btn) {
    document.querySelectorAll('[id^="p-"]').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('p-' + tab).style.display = '';
    btn.classList.add('active');
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url.toString());
}

// Tindak lanjut
function onTL(id, val) {
    document.getElementById('tlh'+id).value = val;
    const tgl = document.getElementById('tsd'+id);
    const sv  = document.getElementById('svf'+id);
    if(val==='sidang')       { tgl.classList.add('show');    sv.value='sidang'; }
    else if(val==='ditolak') { tgl.classList.remove('show'); sv.value='ditolak'; }
    else                     { tgl.classList.remove('show'); sv.value='verif_berkas'; }
}

// Hasil sidang
function onHS(id, val) {
    const m = {lulus:'lulus',tidak_lulus:'tidak_lulus',tanpa_sidang:'lulus'};
    document.getElementById('ssd'+id).value = m[val] ?? 'sidang';
}

// Modal
let dUrl = '';
function openModal(url, nama) {
    dUrl = url;
    document.getElementById('mDesc').innerHTML = 'Hapus usulan promosi <strong>'+nama+'</strong>?<br>Data tidak dapat dikembalikan.';
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
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

// Search
let sTimer = null;
const sInp = document.getElementById('sInput');
const clrB = document.getElementById('clrBtn');
const spin = document.getElementById('spin');
sInp.addEventListener('input', function() {
    clrB.classList.toggle('visible', this.value.trim().length > 0);
    clearTimeout(sTimer);
    sTimer = setTimeout(() => doSearch(this.value.trim()), 350);
});
sInp.addEventListener('keydown', e => { if(e.key==='Enter') { clearTimeout(sTimer); doSearch(sInp.value.trim()); } });
function clearSearch() { sInp.value=''; clrB.classList.remove('visible'); doSearch(''); sInp.focus(); }
function doSearch(kw) {
    const url = new URL(window.location.href);
    if(kw) url.searchParams.set('search',kw); else url.searchParams.delete('search');
    spin.classList.add('show');
    window.location.href = url.toString();
}
</script>
@endpush