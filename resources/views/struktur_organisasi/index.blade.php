@extends('layouts.app')
@section('title', 'Struktur Organisasi')
@section('breadcrumb-parent', 'Planning')
@section('breadcrumb', 'Struktur Organisasi')

@php
/** @var int $bulan */
/** @var int $tahun */
/** @var \Illuminate\Support\Collection $allJabatan */
/** @var array $tree */
/** @var array $stats */
/** @var \Illuminate\Support\Collection $karyawans */
/** @var \Illuminate\Support\Collection $periodeList */
/** @var \Illuminate\Support\Collection $direktorats */
/** @var \Illuminate\Support\Collection $kompartemens */
/** @var \Illuminate\Support\Collection $fungsionals */
$namaBulanList = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$periodeSaatIni = $namaBulanList[$bulan] . ' ' . $tahun;
$isUser = auth()->user()->isUser();
@endphp

@section('content')

@if(session('success'))
<div id="toastSuccess" style="position:fixed;top:20px;right:20px;z-index:99999;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 18px;font-size:13px;color:#15803d;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);min-width:280px;max-width:400px;animation:slideInRight .3s ease">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
  <span style="flex:1">{{ session('success') }}</span>
  <button onclick="document.getElementById('toastSuccess').remove()" style="border:none;background:none;color:#15803d;cursor:pointer;padding:0;font-size:18px;line-height:1;flex-shrink:0">×</button>
</div>
<div id="toastProgress" style="position:fixed;top:20px;right:20px;z-index:99998;height:3px;background:#15803d;border-radius:2px;animation:toastBar 3s linear forwards;width:280px;margin-top:52px"></div>
@endif

@if(session('error'))
<div id="toastError" style="position:fixed;top:20px;right:20px;z-index:99999;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 18px;font-size:13px;color:#dc2626;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);min-width:280px;max-width:400px;animation:slideInRight .3s ease">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span style="flex:1">{{ session('error') }}</span>
  <button onclick="document.getElementById('toastError').remove()" style="border:none;background:none;color:#dc2626;cursor:pointer;padding:0;font-size:18px;line-height:1;flex-shrink:0">×</button>
</div>
@endif

<div>

{{-- PERIODE SELECTOR --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e8e8e3;padding:14px 18px;margin-bottom:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:8px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    <span style="font-size:13px;font-weight:700;color:#111827">Periode:</span>
    <span style="font-size:13px;font-weight:700;color:#15803d">{{ $periodeSaatIni }}</span>
  </div>

  <form method="GET" action="{{ route('struktur-organisasi.index') }}" style="display:flex;align-items:center;gap:6px">
    <select name="bulan" style="border:1px solid #e8e8e3;border-radius:8px;padding:6px 10px;font-size:13px;outline:none;background:#fff;color:#374151">
      @foreach($namaBulanList as $i => $nb)
        @if($i > 0)
          <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>{{ $nb }}</option>
        @endif
      @endforeach
    </select>
    <select name="tahun" style="border:1px solid #e8e8e3;border-radius:8px;padding:6px 10px;font-size:13px;outline:none;background:#fff;color:#374151">
      @for($y = now()->year + 1; $y >= 2020; $y--)
        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
      @endfor
    </select>
    <button type="submit" style="padding:7px 14px;background:#1a1a1a;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer">Lihat</button>
  </form>

  <div style="height:24px;width:1px;background:#e8e8e3"></div>

  @if(!$isUser)
  <button onclick="openSalin()" style="padding:7px 14px;background:#eff6ff;color:#185fa5;border:1px solid #bfdbfe;border-radius:8px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;font-weight:600">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
    Salin ke Periode Baru
  </button>
  @if(auth()->user()->role === 'super_admin')
  <button onclick="openHapusPeriode()" style="padding:7px 14px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;font-weight:600">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
    Hapus Periode Ini
  </button>
  @endif
  @endif

  @if($periodeList->count() > 0)
  <div style="margin-left:auto;display:flex;align-items:center;gap:6px">
    <span style="font-size:11px;color:#9ca3af;white-space:nowrap">Tersedia:</span>
    <select onchange="window.location.href=this.value" style="border:1px solid #e8e8e3;border-radius:8px;padding:5px 10px;font-size:12px;font-weight:600;outline:none;background:#fff;color:#374151;cursor:pointer">
      @foreach($periodeList as $p)
      @php $isActive = $p->bulan==$bulan && $p->tahun==$tahun; @endphp
      <option value="{{ route('struktur-organisasi.index', ['bulan'=>$p->bulan,'tahun'=>$p->tahun]) }}"
        {{ $isActive ? 'selected' : '' }}>
        {{ $namaBulanList[$p->bulan] }} {{ $p->tahun }}
      </option>
      @endforeach
    </select>
  </div>
  @endif
</div>

{{-- STATS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px">
  @foreach([
    ['label'=>'Total Posisi','value'=>$stats['total_posisi'],'color'=>'#185fa5','bg'=>'#dbeafe'],
    ['label'=>'Total MC/TKO','value'=>$stats['total_mc'],'color'=>'#854f0b','bg'=>'#fef3c7'],
    ['label'=>'Terisi','value'=>$stats['total_peng'],'color'=>'#15803d','bg'=>'#dcfce7'],
    ['label'=>'Deviasi','value'=>$stats['total_dev'],'color'=>'#a32d2d','bg'=>'#fee2e2'],
  ] as $s)
  <div style="background:#fff;border-radius:12px;border:1px solid #e8e8e3;padding:14px 18px;display:flex;align-items:center;gap:14px">
    <div style="width:40px;height:40px;border-radius:10px;background:{{ $s['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ $s['color'] }}" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
    </div>
    <div>
      <div style="font-size:20px;font-weight:700;color:{{ $s['label']==='Deviasi'&&$s['value']<0?'#a32d2d':'#1a1a1a' }};line-height:1">{{ $s['value'] }}</div>
      <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $s['label'] }}</div>
    </div>
  </div>
  @endforeach
</div>

</div>

{{-- FILTER --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e8e8e3;padding:14px 18px;margin-bottom:14px">
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <div style="flex:1;min-width:200px;display:flex;align-items:center;gap:8px;background:#f6f4f4;border:1px solid #e8e8e3;border-radius:8px;padding:7px 12px">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="searchInput" placeholder="Cari posisi, bagian, fungsional, karyawan..." value="{{ request('search') }}"
        style="border:none;background:none;outline:none;font-size:13px;color:#1a1a1a;width:100%"
        oninput="filterRealtime(this.value)">
      <span id="searchClear" onclick="clearSearch()" style="cursor:pointer;color:#9ca3af;display:none;font-size:16px;line-height:1">×</span>
    </div>
    <select id="filterDir" onchange="applyDropdownFilter()" style="border:1px solid #e8e8e3;border-radius:8px;padding:7px 12px;font-size:13px;outline:none;background:#fff;color:#374151">
      <option value="">Semua Direktorat</option>
      @foreach($direktorats as $d)
        <option value="{{ $d }}" {{ request('direktorat')==$d?'selected':'' }}>{{ $d }}</option>
      @endforeach
    </select>
    <select id="filterKomp" onchange="applyDropdownFilter()" style="border:1px solid #e8e8e3;border-radius:8px;padding:7px 12px;font-size:13px;outline:none;background:#fff;color:#374151">
      <option value="">Semua Kompartemen</option>
      @foreach($kompartemens as $k)
        <option value="{{ $k }}" {{ request('kompartemen')==$k?'selected':'' }}>{{ $k }}</option>
      @endforeach
    </select>
    <select id="filterCore" onchange="applyDropdownFilter()" style="border:1px solid #e8e8e3;border-radius:8px;padding:7px 12px;font-size:13px;outline:none;background:#fff;color:#374151">
      <option value="">Core & Non Core</option>
      <option value="Core" {{ request('core')=='Core'?'selected':'' }}>Core</option>
      <option value="Non Core" {{ request('core')=='Non Core'?'selected':'' }}>Non Core</option>
    </select>
    <div style="display:flex;gap:4px">
      <button onclick="collapseAll()" title="Collapse Semua" style="padding:8px 10px;background:#f9fafb;color:#6b7280;border:1px solid #e5e7eb;border-radius:8px;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:4px;white-space:nowrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
        Tutup Semua
      </button>
      <button onclick="expandAll()" title="Expand Semua" style="padding:8px 10px;background:#f9fafb;color:#6b7280;border:1px solid #e5e7eb;border-radius:8px;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:4px;white-space:nowrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        Buka Semua
      </button>
    </div>
    <a href="{{ route('struktur-organisasi.export') }}?bulan={{ $bulan }}&tahun={{ $tahun }}" style="margin-left:auto;padding:8px 16px;background:#15803d;color:#fff;border-radius:8px;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:5px;white-space:nowrap">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export {{ $periodeSaatIni }}
    </a>
  </div>
</div>

@if($allJabatan->count() === 0)
<div style="background:#fff;border-radius:12px;border:1px solid #e8e8e3;padding:60px 20px;text-align:center">
  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin:0 auto 16px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
  <div style="font-size:15px;font-weight:600;color:#374151;margin-bottom:6px">Belum ada data untuk periode {{ $periodeSaatIni }}</div>
  <div style="font-size:13px;color:#9ca3af;margin-bottom:16px">Salin dari periode sebelumnya atau tambah posisi baru</div>
  @if(!$isUser)
  <button onclick="openSalin()" style="padding:9px 20px;background:#15803d;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
    Salin dari Periode Lain
  </button>
  @endif
</div>
@else

{{-- TABEL --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e8e8e3;overflow:hidden">
  <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
    <table style="width:max-content;min-width:100%;border-collapse:collapse;font-size:13px" id="mainTable">
      <thead>
        <tr style="background:#f9f9f6;border-bottom:1px solid #e8e8e3">
          <th style="padding:10px 14px;text-align:left;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:340px;position:sticky;left:0;background:#f9f9f6;z-index:2;box-shadow:2px 0 5px rgba(0,0,0,0.05)">JOB TITLE EKSISTING</th>
          <th style="padding:10px 14px;text-align:center;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:60px">JG</th>
          <th style="padding:10px 14px;text-align:center;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:60px">MC</th>
          <th style="padding:10px 14px;text-align:center;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:90px">PENGISIAN</th>
          <th style="padding:10px 14px;text-align:center;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:80px">DEVIASI</th>
          <th style="padding:10px 14px;text-align:center;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:100px">CORE</th>
          <th style="padding:10px 14px;text-align:left;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:220px">KARYAWAN</th>
          @if(!$isUser)<th style="padding:10px 14px;text-align:center;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.5px;white-space:nowrap;min-width:100px">AKSI</th>@endif
        </tr>
      </thead>
      <tbody id="tableBody">

        @foreach($tree as $dirKey => $dir)
        @php $dirSlug = 'dir-'.Str::slug($dirKey); @endphp

        {{-- DIREKTORAT --}}
        <tr onclick="toggleDir('{{ $dirSlug }}')" data-slug="{{ $dirSlug }}" style="cursor:pointer" data-type="dir" data-text="{{ strtolower($dir['label']) }}">
          <td colspan="{{ $isUser ? 7 : 8 }}" style="padding:0;border-bottom:1px solid #d1fae5">
            <div style="padding:9px 14px;display:flex;align-items:center;gap:8px;background:#f0fdf4">
              <svg id="ic-{{ $dirSlug }}" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5" style="flex-shrink:0;transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="1.8" style="flex-shrink:0"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
              <span style="font-weight:700;font-size:13px;color:#15803d;flex:1">{{ $dir['label'] }}</span>
              <span style="padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d">MC: {{ $dir['mc_tko'] }} | Terisi: {{ $dir['pengisian'] }}</span>
              @if(($dir['pengisian']-$dir['mc_tko'])<0)
              <span style="padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#fee2e2;color:#dc2626">Deviasi: {{ $dir['pengisian']-$dir['mc_tko'] }}</span>
              @endif
              @if(!$isUser)
              <div style="display:flex;gap:4px;margin-left:8px" onclick="event.stopPropagation()">
                <button onclick="openTambah('kompartemen','{{ addslashes($dir['label']) }}',null,null,null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#dbeafe;color:#185fa5;border:1px solid #bfdbfe;border-radius:6px;cursor:pointer;white-space:nowrap">+ Kompartemen</button>
                <button onclick="openTambah('staff','{{ addslashes($dir['label']) }}',null,null,null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:6px;cursor:pointer;white-space:nowrap">+ Staff</button>
                <div style="width:1px;height:16px;background:#e5e7eb"></div>
                <button onclick="openEditGroup('direktorat','{{ addslashes($dir['label']) }}',null,null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:3px">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
                </button>
                @if(auth()->user()->role === 'super_admin')
                <button onclick="confirmDeleteGroup('direktorat','{{ addslashes($dir['label']) }}',null,null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#fff;color:#dc2626;border:1px solid #fecaca;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:3px">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>Hapus
                </button>
                @endif
              </div>
              @endif
            </div>
          </td>
        </tr>

        @foreach($dir['children'] as $kompKey => $komp)
        @if($kompKey==='__no_komp__')
          @foreach($komp['children'] as $deptKey => $dept)
            @foreach($dept['children'] as $bagKey => $bag)
              @foreach($bag['children'] as $funcKey => $func)
                @foreach($func['jabatan'] as $row)
                @if($row->posisi === '-') @continue @endif
                <tr class="child-{{ $dirSlug }}" id="row-{{ $row->id }}" style="border-bottom:1px solid #f5f5f0"
                  data-search="{{ strtolower($row->posisi.' '.($row->nama_karyawan??'').' '.($row->fungsional??'')) }}"
                  data-dir="{{ strtolower($row->direktorat??'') }}" data-komp="{{ strtolower($row->kompartemen??'') }}" data-core="{{ strtolower($row->core??'') }}">
                  <td style="padding:10px 14px 10px 30px;position:sticky;left:0;background:#fff;z-index:1;max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;box-shadow:2px 0 5px rgba(0,0,0,0.04)">
                    <span style="font-size:13px;font-weight:500;color:#111827">{{ $row->posisi }}</span>
                    <span style="font-size:10px;color:#9ca3af;margin-left:6px;background:#f3f4f6;padding:1px 6px;border-radius:4px">Langsung</span>
                  </td>
                  <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $row->job_grade }}</span></td>
                  <td style="padding:10px 14px;text-align:center;font-weight:600;color:#111827;white-space:nowrap">{{ $row->mc_tko }}</td>
                  <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="pengisian-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->pengisian?'#dcfce7':'#f3f4f6' }};color:{{ $row->pengisian?'#15803d':'#6b7280' }}">{{ $row->pengisian }}</span></td>
                  <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="deviasi-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->deviasi==0?'#dcfce7':($row->deviasi>0?'#fee2e2':'#fef3c7') }};color:{{ $row->warnaDeviasi }}">{{ $row->deviasi }}</span></td>
                  <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $row->core==='Core'?'#dcfce7':'#dbeafe' }};color:{{ $row->core==='Core'?'#15803d':'#185fa5' }}">{{ $row->core }}</span></td>
                  <td style="padding:10px 14px;white-space:nowrap" id="td-karyawan-{{ $row->id }}">
                    @if($row->karyawan_id)
                      <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:26px;height:26px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ initials($row->nama_karyawan) }}</div>
                        <div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel({{ $row->karyawan_id }})">{{ $row->nama_karyawan }}</div><div style="font-size:11px;color:#9ca3af">{{ $row->nik_karyawan }}</div></div>
                      </div>
                    @else<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>@endif
                  </td>
                  @if(!$isUser)
                  <td style="padding:10px 14px;text-align:center;white-space:nowrap">
                    <div style="display:flex;gap:4px;justify-content:center">
                      <button onclick="openEdit({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->job_grade??'null' }},{{ $row->mc_tko }},'{{ $row->core }}',{{ $row->pengisian??0 }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#f59e0b;display:inline-flex;align-items:center;justify-content:center" title="Edit Posisi">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </button>
                      <button onclick="openModal({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->karyawan_id??'null' }},{{ $row->mc_tko }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#185fa5;display:inline-flex;align-items:center;justify-content:center">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                      </button>
                      @if(auth()->user()->role === 'super_admin')
                        <button onclick="confirmHapus({{ $row->id }},'{{ addslashes($row->posisi) }}')" style="width:28px;height:28px;border:1px solid #fecaca;border-radius:7px;background:#fff;cursor:pointer;color:#dc2626;display:inline-flex;align-items:center;justify-content:center">
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        </button>
                      @endif
                    </div>
                  </td>
                  @endif
                </tr>
                @endforeach
              @endforeach
            @endforeach
          @endforeach
          @continue
        @endif
        @php $kompSlug = $dirSlug.'-k-'.Str::slug($kompKey); @endphp

        {{-- KOMPARTEMEN --}}
        <tr class="child-{{ $dirSlug }}" onclick="toggleKomp('{{ $kompSlug }}')" style="cursor:pointer" data-type="komp" data-text="{{ strtolower($komp['label']) }}">
          <td colspan="{{ $isUser ? 7 : 8 }}" style="padding:0;border-bottom:1px solid #e8e8e3">
            <div style="padding:8px 14px 8px 30px;display:flex;align-items:center;gap:8px;background:#eff6ff">
              <svg id="ic-{{ $kompSlug }}" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#185fa5" stroke-width="2.5" style="flex-shrink:0;transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#185fa5" stroke-width="1.8" style="flex-shrink:0"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
              <span style="font-weight:600;font-size:12px;color:#185fa5;flex:1">{{ $komp['label'] }}</span>
              <span style="padding:2px 8px;border-radius:20px;font-size:11px;background:#dbeafe;color:#185fa5">MC: {{ $komp['mc_tko'] }} | Terisi: {{ $komp['pengisian'] }}</span>
              @if(!$isUser)
              <div style="display:flex;gap:4px;margin-left:8px" onclick="event.stopPropagation()">
                <button onclick="openTambah('departemen','{{ addslashes($dir['label']) }}','{{ addslashes($komp['label']) }}',null,null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#dbeafe;color:#185fa5;border:1px solid #bfdbfe;border-radius:6px;cursor:pointer;white-space:nowrap">+ Departemen</button>
                <button onclick="openTambah('staff','{{ addslashes($dir['label']) }}','{{ addslashes($komp['label']) }}',null,null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:6px;cursor:pointer;white-space:nowrap">+ Staff</button>
                <div style="width:1px;height:16px;background:#e5e7eb"></div>
                <button onclick="openEditGroup('kompartemen','{{ addslashes($komp['label']) }}','{{ addslashes($dir['label']) }}',null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:3px">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
                </button>
                @if(auth()->user()->role === 'super_admin')
                <button onclick="confirmDeleteGroup('kompartemen','{{ addslashes($komp['label']) }}','{{ addslashes($dir['label']) }}',null)" style="padding:3px 8px;font-size:11px;font-weight:600;background:#fff;color:#dc2626;border:1px solid #fecaca;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:3px">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>Hapus
                </button>
                @endif
              </div>
              @endif
            </div>
          </td>
        </tr>

        @if(isset($komp['children']['__no_dept__']))
          @foreach($komp['children']['__no_dept__']['children'] as $bagKey2 => $bag2)
            @foreach($bag2['children'] as $funcKey2 => $func2)
              @php $isFuncReal2 = $funcKey2 !== '__no_func__' && $func2['label']; @endphp
              @if($isFuncReal2)
              <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }}" data-type="func">
                <td colspan="{{ $isUser ? 7 : 8 }}" style="padding:0;border-bottom:1px solid #f5f5f0">
                  <div style="padding:5px 14px 5px 46px;display:flex;align-items:center;gap:6px;background:#fafafa">
                    <span style="font-size:11px;color:#b0b0a8;flex:1">⬦ {{ $func2['label'] }}</span>
                    <span style="font-size:11px;color:#d1d5db">MC: {{ $func2['mc_tko'] }} | Terisi: {{ $func2['pengisian'] }}</span>
                  </div>
                </td>
              </tr>
              @endif
              @foreach($func2['jabatan'] as $row)
              @if($row->posisi === '-') @continue @endif
              <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }}" id="row-{{ $row->id }}" style="border-bottom:1px solid #f5f5f0"
                data-search="{{ strtolower($row->posisi.' '.($row->nama_karyawan??'').' '.($row->fungsional??'')) }}"
                data-dir="{{ strtolower($row->direktorat??'') }}" data-komp="{{ strtolower($row->kompartemen??'') }}" data-core="{{ strtolower($row->core??'') }}">
                @if(!$isFuncReal2)
                <td style="padding:10px 14px 10px 46px;position:sticky;left:0;background:#fff;z-index:1;white-space:nowrap;box-shadow:2px 0 5px rgba(0,0,0,0.04)">
                  <span style="font-size:13px;font-weight:600;color:#185fa5">{{ $row->posisi }}</span>
                  <span style="font-size:10px;color:#185fa5;margin-left:6px;background:#dbeafe;padding:1px 6px;border-radius:4px">Pimpinan</span>
                </td>
                @else
                <td style="padding:10px 14px 10px 60px;position:sticky;left:0;background:#fff;z-index:1;white-space:nowrap;box-shadow:2px 0 5px rgba(0,0,0,0.04)">
                  <span style="font-size:13px;font-weight:500;color:#111827">{{ $row->posisi }}</span>
                </td>
                @endif
                <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $row->job_grade }}</span></td>
                <td style="padding:10px 14px;text-align:center;font-weight:600;color:#111827;white-space:nowrap">{{ $row->mc_tko }}</td>
                <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="pengisian-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->pengisian?'#dcfce7':'#f3f4f6' }};color:{{ $row->pengisian?'#15803d':'#6b7280' }}">{{ $row->pengisian }}</span></td>
                <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="deviasi-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->deviasi==0?'#dcfce7':($row->deviasi>0?'#fee2e2':'#fef3c7') }};color:{{ $row->warnaDeviasi }}">{{ $row->deviasi }}</span></td>
                <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $row->core==='Core'?'#dcfce7':'#dbeafe' }};color:{{ $row->core==='Core'?'#15803d':'#185fa5' }}">{{ $row->core }}</span></td>
                <td style="padding:10px 14px;white-space:nowrap" id="td-karyawan-{{ $row->id }}">
                  @if($row->karyawan_id)
                    <div style="display:flex;align-items:center;gap:8px">
                      <div style="width:26px;height:26px;border-radius:50%;background:{{ $isFuncReal2?'#15803d':'#185fa5' }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ initials($row->nama_karyawan) }}</div>
                      <div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel({{ $row->karyawan_id }})">{{ $row->nama_karyawan }}</div><div style="font-size:11px;color:#9ca3af">{{ $row->nik_karyawan }}</div></div>
                    </div>
                  @else<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>@endif
                </td>
                @if(!$isUser)
                <td style="padding:10px 14px;text-align:center;white-space:nowrap">
                  <div style="display:flex;gap:4px;justify-content:center">
                    <button onclick="openEdit({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->job_grade??'null' }},{{ $row->mc_tko }},'{{ $row->core }}',{{ $row->pengisian??0 }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#f59e0b;display:inline-flex;align-items:center;justify-content:center" title="Edit Posisi">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </button>
                      <button onclick="openModal({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->karyawan_id??'null' }},{{ $row->mc_tko }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#185fa5;display:inline-flex;align-items:center;justify-content:center"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></button>
                    @if(auth()->user()->role === 'super_admin')<button onclick="confirmHapus({{ $row->id }},'{{ addslashes($row->posisi) }}')" style="width:28px;height:28px;border:1px solid #fecaca;border-radius:7px;background:#fff;cursor:pointer;color:#dc2626;display:inline-flex;align-items:center;justify-content:center"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></button>@endif
                  </div>
                </td>
                @endif
              </tr>
              @endforeach
            @endforeach
          @endforeach
        @endif

        @foreach($komp['children'] as $deptKey => $dept)
        @if($deptKey==='__no_dept__') @continue @endif
        @php $isDeptReal = $deptKey!=='__no_dept__' && $dept['label']; @endphp
        @php $deptSlug = $kompSlug.'-d-'.Str::slug($deptKey); @endphp

        @if($isDeptReal)
        <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }}" onclick="toggleDept('{{ $deptSlug }}')" style="cursor:pointer" data-type="dept" data-text="{{ strtolower($dept['label']) }}">
          <td colspan="{{ $isUser ? 7 : 8 }}" style="padding:0;border-bottom:1px solid #f0f0eb">
            <div style="padding:7px 14px 7px 46px;display:flex;align-items:center;gap:6px;background:#fafafa">
              <svg id="ic-{{ $deptSlug }}" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2.5" style="flex-shrink:0;transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
              <span style="font-size:12px;color:#374151;font-weight:500;font-style:italic;flex:1">{{ $dept['label'] }}</span>
              <span style="font-size:11px;color:#9ca3af">MC: {{ $dept['mc_tko'] }} | Terisi: {{ $dept['pengisian'] }}</span>
              @if(!$isUser)
              <div style="display:flex;gap:4px;margin-left:8px" onclick="event.stopPropagation()">
                <button onclick="openTambah('bagian','{{ addslashes($dir['label']) }}','{{ addslashes($komp['label']) }}','{{ addslashes($dept['label']) }}',null)" style="padding:2px 7px;font-size:10px;font-weight:600;background:#dbeafe;color:#185fa5;border:1px solid #bfdbfe;border-radius:5px;cursor:pointer;white-space:nowrap">+ Bagian</button>
                <button onclick="openTambah('staff','{{ addslashes($dir['label']) }}','{{ addslashes($komp['label']) }}','{{ addslashes($dept['label']) }}',null)" style="padding:2px 7px;font-size:10px;font-weight:600;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:5px;cursor:pointer;white-space:nowrap">+ Staff</button>
                <div style="width:1px;height:14px;background:#e5e7eb"></div>
                <button onclick="openEditGroup('departemen','{{ addslashes($dept['label']) }}','{{ addslashes($dir['label']) }}','{{ addslashes($komp['label']) }}')" style="padding:2px 7px;font-size:10px;font-weight:600;background:#fff;color:#374151;border:1px solid #e5e7eb;border-radius:5px;cursor:pointer;display:flex;align-items:center;gap:3px">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit
                </button>
                @if(auth()->user()->role === 'super_admin')
                <button onclick="confirmDeleteGroup('departemen','{{ addslashes($dept['label']) }}','{{ addslashes($dir['label']) }}','{{ addslashes($komp['label']) }}')" style="padding:2px 7px;font-size:10px;font-weight:600;background:#fff;color:#dc2626;border:1px solid #fecaca;border-radius:5px;cursor:pointer;display:flex;align-items:center;gap:3px">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>Hapus
                </button>
                @endif
              </div>
              @endif
            </div>
          </td>
        </tr>
        @endif

        @if($isDeptReal && isset($dept['children']['__no_bag__']))
          @foreach($dept['children']['__no_bag__']['children'] as $funcKey3 => $func3)
            @php $isFuncReal3 = $funcKey3 !== '__no_func__' && $func3['label']; @endphp
            @if($isFuncReal3)
            <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }} child-{{ $deptSlug }}" data-type="func">
              <td colspan="{{ $isUser ? 7 : 8 }}" style="padding:0;border-bottom:1px solid #f5f5f0">
                <div style="padding:5px 14px 5px 62px;display:flex;align-items:center;gap:6px;background:#fafafa">
                  <span style="font-size:11px;color:#b0b0a8;flex:1">⬦ {{ $func3['label'] }}</span>
                  <span style="font-size:11px;color:#d1d5db">MC: {{ $func3['mc_tko'] }} | Terisi: {{ $func3['pengisian'] }}</span>
                </div>
              </td>
            </tr>
            @endif
            @foreach($func3['jabatan'] as $row)
            @if($row->posisi === '-') @continue @endif
            <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }} child-{{ $deptSlug }}" id="row-{{ $row->id }}" style="border-bottom:1px solid #f5f5f0"
              data-search="{{ strtolower($row->posisi.' '.($row->nama_karyawan??'').' '.($row->fungsional??'')) }}"
              data-dir="{{ strtolower($row->direktorat??'') }}" data-komp="{{ strtolower($row->kompartemen??'') }}" data-core="{{ strtolower($row->core??'') }}">
              <td style="padding:10px 14px 10px {{ $isFuncReal3 ? '76' : '62' }}px;position:sticky;left:0;background:#fff;z-index:1;white-space:nowrap;box-shadow:2px 0 5px rgba(0,0,0,0.04)">
                <span style="font-size:13px;font-weight:{{ $isFuncReal3 ? '500' : '600' }};color:{{ $isFuncReal3 ? '#111827' : '#374151' }}">{{ $row->posisi }}</span>
              </td>
              <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $row->job_grade }}</span></td>
              <td style="padding:10px 14px;text-align:center;font-weight:600;color:#111827;white-space:nowrap">{{ $row->mc_tko }}</td>
              <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="pengisian-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->pengisian?'#dcfce7':'#f3f4f6' }};color:{{ $row->pengisian?'#15803d':'#6b7280' }}">{{ $row->pengisian }}</span></td>
              <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="deviasi-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->deviasi==0?'#dcfce7':($row->deviasi>0?'#fee2e2':'#fef3c7') }};color:{{ $row->warnaDeviasi }}">{{ $row->deviasi }}</span></td>
              <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $row->core==='Core'?'#dcfce7':'#dbeafe' }};color:{{ $row->core==='Core'?'#15803d':'#185fa5' }}">{{ $row->core }}</span></td>
              <td style="padding:10px 14px;white-space:nowrap" id="td-karyawan-{{ $row->id }}">
                @if($row->karyawan_id)
                  <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:26px;height:26px;border-radius:50%;background:{{ $isFuncReal3?'#15803d':'#374151' }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ initials($row->nama_karyawan) }}</div>
                    <div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel({{ $row->karyawan_id }})">{{ $row->nama_karyawan }}</div><div style="font-size:11px;color:#9ca3af">{{ $row->nik_karyawan }}</div></div>
                  </div>
                @else<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>@endif
              </td>
              @if(!$isUser)
              <td style="padding:10px 14px;text-align:center;white-space:nowrap">
                <div style="display:flex;gap:4px;justify-content:center">
                  <button onclick="openEdit({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->job_grade??'null' }},{{ $row->mc_tko }},'{{ $row->core }}',{{ $row->pengisian??0 }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#f59e0b;display:inline-flex;align-items:center;justify-content:center" title="Edit Posisi">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </button>
                      <button onclick="openModal({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->karyawan_id??'null' }},{{ $row->mc_tko }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#185fa5;display:inline-flex;align-items:center;justify-content:center"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></button>
                  @if(auth()->user()->role === 'super_admin')<button onclick="confirmHapus({{ $row->id }},'{{ addslashes($row->posisi) }}')" style="width:28px;height:28px;border:1px solid #fecaca;border-radius:7px;background:#fff;cursor:pointer;color:#dc2626;display:inline-flex;align-items:center;justify-content:center"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></button>@endif
                </div>
              </td>
              @endif
            </tr>
            @endforeach
          @endforeach
        @endif

        @foreach($dept['children'] as $bagKey => $bag)
        @if($bagKey==='__no_bag__') @continue @endif
        @php $isBagReal = $bagKey!=='__no_bag__' && $bag['label']; @endphp
        @if($isBagReal)
        <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }} {{ $isDeptReal?'child-'.$deptSlug:'' }}" data-type="bag" data-text="{{ strtolower($bag['label']) }}">
          <td colspan="{{ $isUser ? 7 : 8 }}" style="padding:0;border-bottom:1px solid #f5f5f0">
            <div style="padding:6px 14px 6px 60px;display:flex;align-items:center;gap:6px;background:#fafafa">
              <span style="font-size:11px;color:#9ca3af;flex:1">↳ {{ $bag['label'] }}</span>
              <span style="font-size:11px;color:#d1d5db">MC: {{ $bag['mc_tko'] }} | Terisi: {{ $bag['pengisian'] }}</span>
              @if(!$isUser)
              <div style="display:flex;gap:4px;margin-left:8px">
                <button onclick="openTambah('staff','{{ addslashes($dir['label']) }}','{{ addslashes($komp['label']) }}','{{ $isDeptReal ? addslashes($dept['label']) : '' }}','{{ addslashes($bag['label']) }}')" style="padding:2px 7px;font-size:10px;font-weight:600;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:5px;cursor:pointer;white-space:nowrap">+ Staff</button>
              </div>
              @endif
            </div>
          </td>
        </tr>
        @endif

        @foreach($bag['children'] as $funcKey => $func)
        @php $isFuncReal = $funcKey!=='__no_func__' && $func['label']; @endphp
        @if($isFuncReal)
        <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }} {{ $isDeptReal?'child-'.$deptSlug:'' }}" data-type="func" data-text="{{ strtolower($func['label']) }}">
          <td colspan="{{ $isUser ? 7 : 8 }}" style="padding:0;border-bottom:1px solid #f5f5f0">
            <div style="padding:5px 14px 5px 72px;display:flex;align-items:center;gap:6px;background:#fafafa">
              <span style="font-size:11px;color:#b0b0a8;flex:1">⬦ {{ $func['label'] }}</span>
              <span style="font-size:11px;color:#d1d5db">MC: {{ $func['mc_tko'] }} | Terisi: {{ $func['pengisian'] }}</span>
            </div>
          </td>
        </tr>
        @endif

        @foreach($func['jabatan'] as $row)
        @if($row->posisi === '-') @continue @endif
        <tr class="child-{{ $dirSlug }} child-{{ $kompSlug }} {{ $isDeptReal?'child-'.$deptSlug:'' }}"
          id="row-{{ $row->id }}" style="border-bottom:1px solid #f5f5f0"
          data-search="{{ strtolower($row->posisi.' '.($row->nama_karyawan??'').' '.($row->fungsional??'').' '.($row->bagian??'')) }}"
          data-dir="{{ strtolower($row->direktorat??'') }}" data-komp="{{ strtolower($row->kompartemen??'') }}" data-core="{{ strtolower($row->core??'') }}">
          <td style="padding:10px 14px 10px 86px;position:sticky;left:0;background:#fff;z-index:1;white-space:nowrap;box-shadow:2px 0 5px rgba(0,0,0,0.04)">
            <span style="font-size:13px;font-weight:500;color:#111827">{{ $row->posisi }}</span>
          </td>
          <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $row->job_grade }}</span></td>
          <td style="padding:10px 14px;text-align:center;font-weight:600;color:#111827;white-space:nowrap">{{ $row->mc_tko }}</td>
          <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="pengisian-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->pengisian?'#dcfce7':'#f3f4f6' }};color:{{ $row->pengisian?'#15803d':'#6b7280' }}">{{ $row->pengisian }}</span></td>
          <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span id="deviasi-{{ $row->id }}" style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $row->deviasi==0?'#dcfce7':($row->deviasi>0?'#fee2e2':'#fef3c7') }};color:{{ $row->warnaDeviasi }}">{{ $row->deviasi }}</span></td>
          <td style="padding:10px 14px;text-align:center;white-space:nowrap"><span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $row->core==='Core'?'#dcfce7':'#dbeafe' }};color:{{ $row->core==='Core'?'#15803d':'#185fa5' }}">{{ $row->core }}</span></td>
          <td style="padding:10px 14px;white-space:nowrap" id="td-karyawan-{{ $row->id }}">
            @if($row->karyawan_id)
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:26px;height:26px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ initials($row->nama_karyawan) }}</div>
                <div>
                  <div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel({{ $row->karyawan_id }})">{{ $row->nama_karyawan }}</div>
                  <div style="font-size:11px;color:#9ca3af">{{ $row->nik_karyawan }}</div>
                </div>
              </div>
            @else<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>@endif
          </td>
          @if(!$isUser)
          <td style="padding:10px 14px;text-align:center;white-space:nowrap">
            <div style="display:flex;gap:4px;justify-content:center">
              <button onclick="openEdit({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->job_grade??'null' }},{{ $row->mc_tko }},'{{ $row->core }}',{{ $row->pengisian??0 }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#f59e0b;display:inline-flex;align-items:center;justify-content:center" title="Edit Posisi">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </button>
              <button onclick="openModal({{ $row->id }},'{{ addslashes($row->posisi) }}',{{ $row->karyawan_id??'null' }},{{ $row->mc_tko }})" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;color:#185fa5;display:inline-flex;align-items:center;justify-content:center" title="Assign Karyawan">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
              </button>
              @if(auth()->user()->role === 'super_admin')
                <button onclick="confirmHapus({{ $row->id }},'{{ addslashes($row->posisi) }}')" style="width:28px;height:28px;border:1px solid #fecaca;border-radius:7px;background:#fff;cursor:pointer;color:#dc2626;display:inline-flex;align-items:center;justify-content:center" title="Hapus">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                </button>
              @endif
            </div>
          </td>
          @endif
        </tr>
        @endforeach
        @endforeach
        @endforeach
        @endforeach
        @endforeach
        @endforeach

      </tbody>
    </table>
  </div>
  <div style="padding:10px 16px;font-size:12px;color:#9ca3af;border-top:1px solid #f0f0eb;display:flex;align-items:center;justify-content:space-between;gap:10px">
    <span>Total <strong>{{ $allJabatan->count() }}</strong> posisi — Periode <strong>{{ $periodeSaatIni }}</strong></span>
    <div style="display:flex;align-items:center;gap:8px">
      <span id="searchCount" style="display:none;color:#15803d;font-weight:600"></span>
      @if(!$isUser)
      @endif
    </div>
  </div>
</div>
@endif

{{-- ===== MODAL KONFIRMASI HAPUS ===== --}}
<div id="modalHapusBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:400px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
    <div style="padding:24px 24px 16px;text-align:center">
      <div style="width:52px;height:52px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
      </div>
      <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:8px">Hapus Posisi?</div>
      <div style="font-size:13px;color:#6b7280;line-height:1.5">Yakin ingin menghapus posisi <strong id="hapusPosisiLabel" style="color:#111827"></strong>? Tindakan ini tidak dapat dibatalkan.</div>
    </div>
    <div style="padding:16px 24px 20px;display:flex;gap:8px;justify-content:center">
      <button onclick="closeHapus()" style="padding:9px 24px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit;font-weight:500">Batal</button>
      <form id="formHapus" method="POST" style="display:inline">
        @csrf @method('DELETE')
        <button type="submit" style="padding:9px 24px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

{{-- ===== MODAL SALIN PERIODE ===== --}}
<div id="modalSalinBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:460px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
    <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:14px;font-weight:700;color:#111827">Salin ke Periode Baru</div>
        <div style="font-size:12px;color:#9ca3af;margin-top:2px">Posisi, struktur dan karyawan akan disalin</div>
      </div>
      <button onclick="closeSalin()" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form method="POST" action="{{ route('struktur-organisasi.salin-periode') }}">
    @csrf
    <div style="padding:20px 22px">
      @php $namaBulanList2 = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div>
          <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Dari Bulan</label>
          <select name="dari_bulan" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff">
            @foreach($namaBulanList2 as $i => $nb)
              @if($i > 0)<option value="{{ $i }}" {{ $i==$bulan?'selected':'' }}>{{ $nb }}</option>@endif
            @endforeach
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Dari Tahun</label>
          <select name="dari_tahun" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff">
            @for($y = now()->year + 1; $y >= 2020; $y--)<option value="{{ $y }}" {{ $y==$tahun?'selected':'' }}>{{ $y }}</option>@endfor
          </select>
        </div>
      </div>
      <div style="text-align:center;color:#9ca3af;font-size:18px;margin-bottom:16px">↓</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Ke Bulan <span style="color:#dc2626">*</span></label>
          <select name="ke_bulan" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff">
            @foreach($namaBulanList2 as $i => $nb)
              @if($i > 0)
                @php $nextM = $bulan == 12 ? 1 : $bulan + 1; @endphp
                <option value="{{ $i }}" {{ $i==$nextM?'selected':'' }}>{{ $nb }}</option>
              @endif
            @endforeach
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Ke Tahun <span style="color:#dc2626">*</span></label>
          <select name="ke_tahun" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff">
            @php $nextY = $bulan == 12 ? $tahun + 1 : $tahun; @endphp
            @for($y = now()->year + 2; $y >= 2020; $y--)<option value="{{ $y }}" {{ $y==$nextY?'selected':'' }}>{{ $y }}</option>@endfor
          </select>
        </div>
      </div>
      {{-- Opsi karyawan --}}
      <div style="margin-top:16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px">Opsi Salin</div>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
          <div style="position:relative;width:40px;height:22px;flex-shrink:0">
            <input type="checkbox" name="tanpa_karyawan" id="toggleTanpaKaryawan" value="1"
              style="opacity:0;width:0;height:0;position:absolute"
              onchange="updateSalinLabel(this.checked)">
            <div id="toggleTrack" style="position:absolute;inset:0;background:#e5e7eb;border-radius:20px;transition:background .2s"></div>
            <div id="toggleThumb" style="position:absolute;top:3px;left:3px;width:16px;height:16px;background:white;border-radius:50%;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,0.2)"></div>
          </div>
          <div>
            <div style="font-size:13px;font-weight:600;color:#111827" id="salinLabel">Salin dengan karyawan</div>
            <div style="font-size:11px;color:#9ca3af" id="salinDesc">Posisi dan data karyawan akan disalin bersama</div>
          </div>
        </label>
      </div>
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-top:12px;font-size:12px;color:#185fa5">
        ℹ️ Perubahan di periode baru tidak mempengaruhi periode lain.
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;gap:8px;justify-content:flex-end">
      <button type="button" onclick="closeSalin()" style="padding:9px 18px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit">Batal</button>
      <button type="submit" style="padding:9px 20px;background:#15803d;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Salin Sekarang</button>
    </div>
    </form>
  </div>
</div>

{{-- ===== PANEL SLIDE KARYAWAN ===== --}}
<div id="panelOverlay" onclick="closePanel()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:8888;backdrop-filter:blur(2px)"></div>
<div id="karyawanPanel" style="position:fixed;top:0;right:-440px;width:420px;height:100vh;background:#fff;z-index:8889;border-left:1px solid #e5e7eb;box-shadow:-8px 0 32px rgba(0,0,0,0.12);transition:right .3s cubic-bezier(0.4,0,0.2,1);display:flex;flex-direction:column;overflow:hidden">
  <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
    <div style="font-size:14px;font-weight:700;color:#111827">Profil Karyawan</div>
    <button onclick="closePanel()" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
  </div>
  <div id="panelBody" style="flex:1;overflow-y:auto;padding:20px">
    <div id="panelLoading" style="display:flex;align-items:center;justify-content:center;height:200px;color:#9ca3af;flex-direction:column;gap:10px">
      <div style="width:28px;height:28px;border:3px solid #e5e7eb;border-top-color:#15803d;border-radius:50%;animation:panelSpin 0.8s linear infinite"></div>
      <div style="font-size:13px">Memuat data...</div>
    </div>
    <div id="panelContent" style="display:none">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid #f3f4f6">
        <div id="pAvatar" style="width:56px;height:56px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;font-weight:700;flex-shrink:0;overflow:hidden;border:3px solid #bbf7d0"></div>
        <div style="flex:1;min-width:0">
          <div id="pNama" style="font-size:15px;font-weight:700;color:#111827;margin-bottom:2px"></div>
          <div id="pJabatan" style="font-size:11px;color:#6b7280;margin-bottom:6px;line-height:1.4"></div>
          <div style="display:flex;gap:5px;flex-wrap:wrap"><span id="pNik" style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d"></span><span id="pStatus" style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600"></span></div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px">
        <div style="background:#f9fafb;border-radius:10px;padding:12px;text-align:center"><div style="font-size:22px;font-weight:700;color:#111827" id="pUmur">-</div><div style="font-size:10px;color:#9ca3af;margin-top:2px">Usia (tahun)</div></div>
        <div style="background:#f0fdf4;border-radius:10px;padding:12px;text-align:center"><div style="font-size:22px;font-weight:700;color:#15803d" id="pSisaMasaKerja">-</div><div style="font-size:10px;color:#9ca3af;margin-top:2px">Sisa Masa Kerja (thn)</div></div>
        <div style="background:#eff6ff;border-radius:10px;padding:12px;text-align:center"><div style="font-size:16px;font-weight:700;color:#185fa5" id="pJG">-</div><div style="font-size:10px;color:#9ca3af;margin-top:2px">Job Grade</div></div>
        <div style="background:#f5f3ff;border-radius:10px;padding:12px;text-align:center"><div style="font-size:16px;font-weight:700;color:#7c3aed" id="pPG">-</div><div style="font-size:10px;color:#9ca3af;margin-top:2px">Person Grade</div></div>
      </div>
      <div style="border:1px solid #f3f4f6;border-radius:10px;overflow:hidden;margin-bottom:18px">
        <div style="padding:8px 14px;background:#f9fafb;font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px">Informasi</div>
        <div id="pInfoRows"></div>
      </div>
      <div style="margin-top:0">
        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">Riwayat Penugasan SO</div>
        <div id="pSoHistory"></div>
      </div>
    </div>
  </div>
</div>

{{-- ===== MODAL TAMBAH ===== --}}
<div id="modalTambahBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:540px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15);max-height:90vh;overflow-y:auto">
    <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:#fff;z-index:1">
      <div><div id="modalTambahTitle" style="font-size:14px;font-weight:700;color:#111827">Tambah</div></div>
      <button onclick="closeTambah()" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="POST" action="{{ route('struktur-organisasi.store') }}" id="formTambah">
    @csrf
    <input type="hidden" name="bulan" value="{{ $bulan }}">
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <div style="padding:18px 22px">
      <input type="hidden" name="direktorat"  id="ftDir">
      <input type="hidden" name="kompartemen" id="ftKomp">
      <input type="hidden" name="dept"        id="ftDept">
      <input type="hidden" name="bagian"      id="ftBag">
      <input type="hidden" name="fungsional"  id="ftFunc">
      <input type="hidden" name="posisi"   id="ftPosisiHidden">
      <input type="hidden" name="mc_tko"   id="ftMcTkoHidden" value="1">
      <div id="konteksInfo" style="background:#f9fafb;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:#374151;flex-direction:column;gap:6px;display:none"></div>
      <div id="fieldKompartemen" style="display:none;margin-bottom:14px"><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Nama Kompartemen <span style="color:#dc2626">*</span></label><input type="text" id="inputKompartemen" placeholder="Contoh: Kompartemen Operasi" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:#111827"></div>
      <div id="fieldDepartemen" style="display:none;margin-bottom:14px"><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Nama Departemen <span style="color:#dc2626">*</span></label><input type="text" id="inputDepartemen" placeholder="Contoh: Dept. Komunikasi" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:#111827"></div>
      <div id="fieldBagian" style="display:none;margin-bottom:14px"><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Nama Bagian <span style="color:#dc2626">*</span></label><input type="text" id="inputBagian" placeholder="Contoh: Bagian Operasi Amoniak" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:#111827"></div>
      <div id="fieldStaff" style="display:none">
        <div style="margin-bottom:14px"><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Fungsional <span style="font-size:11px;color:#9ca3af">(opsional)</span></label><input list="listFunc" id="inputFungsional" name="fungsional_staff" placeholder="Kosongkan jika tidak ada fungsional" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:#111827"><datalist id="listFunc">@foreach($fungsionals as $f)<option value="{{ $f }}">@endforeach</datalist></div>
        <div style="margin-bottom:14px"><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Job Title / Posisi <span style="color:#dc2626">*</span></label><input type="text" id="inputPosisi" placeholder="Contoh: Officer Komunikasi Korporat" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:#111827"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
          <div><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Job Grade</label><input type="number" name="job_grade" placeholder="Contoh: 16" min="1" max="30" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none"></div>
          <div><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">MC / TKO <span style="font-size:11px;color:#9ca3af;font-weight:400">(opsional)</span></label><input type="number" id="inputMcTko" value="" placeholder="Contoh: 1" min="0" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none"></div>
        </div>
        <div style="margin-bottom:14px"><label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Core / Non Core <span style="color:#dc2626">*</span></label><select name="core" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff"><option value="Non Core">Non Core</option><option value="Core">Core</option></select></div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;gap:8px;justify-content:flex-end;position:sticky;bottom:0;background:#fff">
      <button type="button" onclick="closeTambah()" style="padding:9px 18px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit">Batal</button>
      <button type="button" onclick="submitTambah()" style="padding:9px 20px;background:#15803d;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Simpan</button>
    </div>
    </form>
  </div>
</div>

{{-- ===== MODAL ASSIGN ===== --}}
<div id="modalBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:500px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
    <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between">
      <div><div style="font-size:14px;font-weight:700;color:#111827">Assign Karyawan</div><div id="modalPosisi" style="font-size:12px;color:#9ca3af;margin-top:2px"></div></div>
      <button onclick="closeModal()" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div style="padding:18px 22px">
      {{-- Search NIK / Nama --}}
      <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Cari NIK / Nama Karyawan</label>
      <div style="position:relative">
        <div style="display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;margin-bottom:6px">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="assignSearch" oninput="onAssignSearch(this.value)" onkeydown="onAssignKeydown(event)"
            placeholder="Ketik atau paste NIK / nama..." autocomplete="off"
            style="border:none;background:none;outline:none;font-size:13px;color:#111827;width:100%">
          <span id="assignSearchClear" onclick="clearAssign()" style="cursor:pointer;color:#9ca3af;font-size:18px;line-height:1;display:none">×</span>
        </div>
        {{-- Dropdown hasil --}}
        <div id="assignDropdown" style="display:none;position:absolute;left:0;right:0;top:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.1);z-index:100;max-height:200px;overflow-y:auto;margin-top:-4px">
          <div id="assignDropdownList"></div>
        </div>
      </div>
      {{-- Karyawan terpilih --}}
      <div id="assignSelected" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;margin-bottom:6px">
        <div style="display:flex;align-items:center;gap:10px">
          <div id="assignSelAvatar" style="width:30px;height:30px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;flex-shrink:0"></div>
          <div style="flex:1">
            <div id="assignSelNama" style="font-size:13px;font-weight:600;color:#15803d"></div>
            <div id="assignSelNik" style="font-size:11px;color:#6b7280"></div>
          </div>
          <button onclick="clearAssign()" style="border:none;background:none;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;line-height:1">×</button>
        </div>
      </div>
      {{-- Tombol lepas karyawan --}}
      <button onclick="clearAssign(true)" style="font-size:11px;color:#9ca3af;border:none;background:none;cursor:pointer;padding:0;margin-bottom:8px;text-decoration:underline">Lepas karyawan dari posisi ini</button>
      {{-- Hidden select untuk kompatibilitas --}}
      <select id="modalSelKaryawan" style="display:none" onchange="loadKaryawanInfo(this.value)">
        <option value="">-- Kosong --</option>
        @foreach($karyawans as $k)<option value="{{ $k->id }}" data-nama="{{ strtolower($k->nama) }}" data-nik="{{ strtolower($k->nik) }}" data-display="{{ $k->nama }} — {{ $k->nik }}">{{ $k->nama }} — {{ $k->nik }}</option>@endforeach
      </select>
      <div id="infoCard" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-top:12px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">NIK</div><div id="iNik" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Jabatan Saat Ini</div><div id="iJab" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Direktorat</div><div id="iDir" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Kompartemen</div><div id="iKomp" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Departemen</div><div id="iDept" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Job Grade</div><div id="iJg" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Person Grade</div><div id="iPg" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid #bbf7d0">
          <div style="flex:1;background:#dcfce7;border-radius:8px;padding:10px;text-align:center"><div style="font-size:10px;font-weight:700;color:#15803d;letter-spacing:.5px;margin-bottom:4px">PENGISIAN</div><div style="font-size:22px;font-weight:700;color:#15803d">1</div></div>
          <div id="deviasiBox" style="flex:1;border-radius:8px;padding:10px;text-align:center;background:#fef3c7"><div style="font-size:10px;font-weight:700;letter-spacing:.5px;margin-bottom:4px;color:#854f0b">DEVIASI</div><div id="deviasiVal" style="font-size:22px;font-weight:700;color:#854f0b">-</div></div>
        </div>
      </div>
      <div id="kosongCard" style="display:none;background:#fafafa;border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin-top:12px">
        <div style="display:flex;gap:8px">
          <div style="flex:1;background:#f3f4f6;border-radius:8px;padding:10px;text-align:center"><div style="font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.5px;margin-bottom:4px">PENGISIAN</div><div style="font-size:22px;font-weight:700;color:#6b7280">0</div></div>
          <div style="flex:1;background:#fef3c7;border-radius:8px;padding:10px;text-align:center"><div style="font-size:10px;font-weight:700;color:#854f0b;letter-spacing:.5px;margin-bottom:4px">DEVIASI</div><div id="deviasiKosong" style="font-size:22px;font-weight:700;color:#854f0b">-</div></div>
        </div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;gap:8px;justify-content:flex-end">
      <button onclick="closeModal()" style="padding:9px 18px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit">Batal</button>
      <button onclick="saveAssign()" style="padding:9px 20px;background:#15803d;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Simpan</button>
    </div>
  </div>
</div>



{{-- ===== MODAL BULK ASSIGN ===== --}}
{{-- ===== MODAL EDIT POSISI ===== --}}
<div id="modalEditBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:460px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
    <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:14px;font-weight:700;color:#111827">Edit Posisi</div>
        <div id="editPosisiLabel" style="font-size:12px;color:#9ca3af;margin-top:2px"></div>
      </div>
      <button onclick="closeEdit()" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div style="padding:20px 22px">
      <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Job Title / Posisi <span style="color:#dc2626">*</span></label>
        <input type="text" id="editInputPosisi" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:#111827">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
          <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Job Grade</label>
          <input type="number" id="editInputJg" placeholder="Contoh: 16" min="1" max="30" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none">
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">MC / TKO <span style="font-size:11px;color:#9ca3af">(opsional)</span></label>
          <input type="number" id="editInputMc" min="0" placeholder="Contoh: 1" oninput="updateDeviasiPreview()" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none">
        </div>
      </div>
      <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Core / Non Core</label>
        <select id="editInputCore" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff">
          <option value="Non Core">Non Core</option>
          <option value="Core">Core</option>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Keterisian (Pengisian)</label>
          <input type="number" id="editInputPengisian" min="0" placeholder="0" oninput="updateDeviasiPreview()" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none">
          <div style="font-size:11px;color:#9ca3af;margin-top:4px">Jumlah slot yang terisi</div>
        </div>
        <div style="background:#f9fafb;border-radius:8px;padding:10px;display:flex;flex-direction:column;justify-content:center;align-items:center">
          <div style="font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Deviasi Preview</div>
          <div id="editDeviasiPreview" style="font-size:20px;font-weight:700;color:#374151">—</div>
        </div>
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;gap:8px;justify-content:flex-end">
      <button onclick="closeEdit()" style="padding:9px 18px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit">Batal</button>
      <button onclick="saveEdit()" style="padding:9px 20px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Simpan Perubahan</button>
    </div>
  </div>
</div>


{{-- ===== MODAL EDIT GROUP ===== --}}
<div id="modalEditGroupBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:420px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
    <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between">
      <div>
        <div id="editGroupTitle" style="font-size:14px;font-weight:700;color:#111827">Edit Nama</div>
        <div id="editGroupSubtitle" style="font-size:12px;color:#9ca3af;margin-top:2px"></div>
      </div>
      <button onclick="closeEditGroup()" style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div style="padding:20px 22px">
      <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Nama Baru <span style="color:#dc2626">*</span></label>
      <input type="text" id="editGroupInput" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:#111827" placeholder="Masukkan nama baru...">
      <div style="background:#fff8c2;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;margin-top:12px;font-size:12px;color:#854f0b">
        ⚠️ Perubahan ini akan mempengaruhi semua posisi dalam grup ini di periode yang sama.
      </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;gap:8px;justify-content:flex-end">
      <button onclick="closeEditGroup()" style="padding:9px 18px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit">Batal</button>
      <button onclick="saveEditGroup()" style="padding:9px 20px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Simpan</button>
    </div>
  </div>
</div>

{{-- ===== MODAL HAPUS GROUP ===== --}}
<div id="modalDeleteGroupBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:420px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
    <div style="padding:24px 24px 16px;text-align:center">
      <div style="width:52px;height:52px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
      </div>
      <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:8px">Hapus Grup?</div>
      <div style="font-size:13px;color:#6b7280;line-height:1.6">Semua posisi dalam <strong id="deleteGroupLabel" style="color:#111827"></strong> di periode ini akan dihapus permanen.</div>
    </div>
    <div style="padding:16px 24px 20px;display:flex;gap:8px;justify-content:center">
      <button onclick="closeDeleteGroup()" style="padding:9px 24px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit">Batal</button>
      <button onclick="doDeleteGroup()" style="padding:9px 24px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Ya, Hapus</button>
    </div>
  </div>
</div>


{{-- Data untuk JavaScript --}}
<script id="soPageData" type="application/json">
{
    "bulan": {{ $bulan }},
    "tahun": {{ $tahun }},
    "csrf": "{{ csrf_token() }}"
}
</script>

@endsection

{{-- ===== MODAL HAPUS PERIODE ===== --}}
<div id="modalHapusPeriodeBg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:16px;width:420px;max-width:95vw;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
    <div style="padding:24px 24px 16px;text-align:center">
      <div style="width:52px;height:52px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
      </div>
      <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:8px">Hapus Seluruh Periode?</div>
      <div style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:8px">Yakin ingin menghapus <strong style="color:#dc2626">semua data SO periode {{ $periodeSaatIni }}</strong>?</div>
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px;font-size:12px;color:#dc2626;text-align:left">
        ⚠️ Tindakan ini akan menghapus <strong>{{ $allJabatan->count() }} posisi</strong> secara permanen dan tidak dapat dibatalkan.
      </div>
    </div>
    <div style="padding:16px 24px 20px;display:flex;gap:8px;justify-content:center">
      <button onclick="closeHapusPeriode()" style="padding:9px 24px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;font-family:inherit;font-weight:500">Batal</button>
      <form id="formHapusPeriode" method="POST" action="{{ route('struktur-organisasi.hapus-periode') }}" style="display:inline">
        @csrf @method('DELETE')
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <button type="submit" style="padding:9px 24px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">Ya, Hapus Periode</button>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<style>
@keyframes panelSpin{to{transform:rotate(360deg)}}
@keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
@keyframes toastBar{from{width:calc(100% - 40px)}to{width:0}}
</style>
<script>
const _pd = JSON.parse(document.getElementById('soPageData').textContent);

document.addEventListener('DOMContentLoaded', function() {
  const toast = document.getElementById('toastSuccess');
  if (toast) { setTimeout(() => { toast.style.opacity='0'; toast.style.transition='opacity .3s'; setTimeout(()=>toast.remove(), 300); }, 3000); }
  const toastErr = document.getElementById('toastError');
  if (toastErr) { setTimeout(() => { toastErr.style.opacity='0'; toastErr.style.transition='opacity .3s'; setTimeout(()=>toastErr.remove(), 300); }, 4000); }
  const prog = document.getElementById('toastProgress');
  if (prog) setTimeout(()=>prog.remove(), 3300);
});

const stateDir={},stateKomp={},stateDept={};
function setRows(cls,show){document.querySelectorAll('.'+cls).forEach(r=>r.style.display=show?'':'none');}
function rotateIcon(id,c){const ic=document.getElementById('ic-'+id);if(ic)ic.style.transform=c?'rotate(-90deg)':'rotate(0deg)';}
function toggleDir(slug){const wc=stateDir[slug]!==false;stateDir[slug]=!wc;setRows('child-'+slug,!wc);rotateIcon(slug,wc);if(wc){Object.keys(stateKomp).forEach(k=>{if(k.startsWith(slug)){stateKomp[k]=false;rotateIcon(k,false);}});Object.keys(stateDept).forEach(k=>{if(k.startsWith(slug)){stateDept[k]=false;rotateIcon(k,false);}});}}
function toggleKomp(slug){const wc=stateKomp[slug]!==false;stateKomp[slug]=!wc;setRows('child-'+slug,!wc);rotateIcon(slug,wc);if(wc){Object.keys(stateDept).forEach(k=>{if(k.startsWith(slug)){stateDept[k]=false;rotateIcon(k,false);}});}}
function toggleDept(slug){const wc=stateDept[slug]!==false;stateDept[slug]=!wc;setRows('child-'+slug,!wc);rotateIcon(slug,wc);}

let searchTimer=null;
function filterRealtime(val){clearTimeout(searchTimer);searchTimer=setTimeout(()=>doFilter(val),150);document.getElementById('searchClear').style.display=val?'block':'none';}
function clearSearch(){document.getElementById('searchInput').value='';document.getElementById('searchClear').style.display='none';doFilter('');}
function applyDropdownFilter(){doFilter(document.getElementById('searchInput').value);}
function doFilter(keyword){
  const kw=keyword.toLowerCase().trim();
  const dir=document.getElementById('filterDir').value.toLowerCase();
  const komp=document.getElementById('filterKomp').value.toLowerCase();
  const core=document.getElementById('filterCore').value.toLowerCase();
  const rows=document.querySelectorAll('tr[data-search]');
  let visible=0;
  rows.forEach(row=>{
    const text=row.getAttribute('data-search')||'';
    const rowDir=row.getAttribute('data-dir')||'';
    const rowKomp=row.getAttribute('data-komp')||'';
    const rowCore=row.getAttribute('data-core')||'';
    const match=(!kw||text.includes(kw))&&(!dir||rowDir.includes(dir))&&(!komp||rowKomp.includes(komp))&&(!core||rowCore.includes(core));
    row.style.display=match?'':'none';
    if(match)visible++;
  });
  const countEl=document.getElementById('searchCount');
  if(kw||dir||komp||core){countEl.textContent=visible+' posisi ditemukan';countEl.style.display='block';}
  else{countEl.style.display='none';rows.forEach(r=>r.style.display='');}
}

let hapusId=null;
function confirmHapus(id,posisi){hapusId=id;document.getElementById('hapusPosisiLabel').textContent=posisi;document.getElementById('formHapus').action='/struktur-organisasi/'+id;document.getElementById('modalHapusBg').style.display='flex';}
function closeHapus(){document.getElementById('modalHapusBg').style.display='none';hapusId=null;}
document.getElementById('modalHapusBg').addEventListener('click',function(e){if(e.target===this)closeHapus();});

function openSalin(){
  document.getElementById('modalSalinBg').style.display='flex';
  // Reset toggle
  const cb=document.getElementById('toggleTanpaKaryawan');
  if(cb){cb.checked=false;updateSalinLabel(false);}
}
function updateSalinLabel(checked){
  document.getElementById('toggleTrack').style.background=checked?'#15803d':'#e5e7eb';
  document.getElementById('toggleThumb').style.left=checked?'21px':'3px';
  document.getElementById('salinLabel').textContent=checked?'Salin tanpa karyawan':'Salin dengan karyawan';
  document.getElementById('salinDesc').textContent=checked?'Hanya posisi dan struktur yang disalin':'Posisi dan data karyawan akan disalin bersama';
}
function closeSalin(){document.getElementById('modalSalinBg').style.display='none';}
function openHapusPeriode(){document.getElementById('modalHapusPeriodeBg').style.display='flex';}
function closeHapusPeriode(){document.getElementById('modalHapusPeriodeBg').style.display='none';}
document.getElementById('modalSalinBg').addEventListener('click',function(e){if(e.target===this)closeSalin();});

function openPanel(karyawanId){
  document.getElementById('panelOverlay').style.display='block';
  document.getElementById('karyawanPanel').style.right='0';
  document.getElementById('panelLoading').style.display='flex';
  document.getElementById('panelContent').style.display='none';
  fetch('/api/karyawan/'+karyawanId+'/profile').then(r=>r.json()).then(d=>{
    const av=document.getElementById('pAvatar');
    if(d.foto){av.innerHTML=`<img src="${d.foto}" style="width:100%;height:100%;object-fit:cover">`;}
    else{av.textContent=d.inisial;av.style.background='#15803d';av.style.color='#fff';}
    document.getElementById('pNama').textContent=d.nama;
    document.getElementById('pJabatan').textContent=d.jabatan_saat_ini;
    document.getElementById('pNik').textContent='NIK '+d.nik;
    const st=document.getElementById('pStatus');
    st.textContent=d.status==='aktif'?'● Aktif':'● Nonaktif';
    st.style.background=d.status==='aktif'?'#dcfce7':'#fee2e2';
    st.style.color=d.status==='aktif'?'#15803d':'#dc2626';
    document.getElementById('pUmur').textContent=d.umur??'-';
    document.getElementById('pSisaMasaKerja').textContent=d.sisa_masa_kerja??'-';
    document.getElementById('pJG').textContent='JG '+d.job_grade;
    document.getElementById('pPG').textContent='PG '+d.person_grade;
    const currentH = d.history ? d.history.find(h=>h.is_current) : null;
    const noSk = currentH?.no_sk || '-';
    const tglSk = currentH?.tanggal_mulai || '-';
    const rows=[['Direktorat',d.direktorat],['Kompartemen',d.kompartemen],['Departemen',d.departemen],['No. SK',noSk],['Tanggal SK',tglSk],['Tgl Masuk',d.tanggal_masuk],['Tgl Lahir',d.tanggal_lahir],['Pensiun',d.pensiun],['Lama Bekerja',d.lama_bekerja]];
    document.getElementById('pInfoRows').innerHTML=rows.map(([l,v])=>`<div style="display:flex;justify-content:space-between;align-items:flex-start;padding:7px 14px;border-bottom:1px solid #f9fafb;gap:10px"><span style="font-size:11px;color:#9ca3af;flex-shrink:0">${l}</span><span style="font-size:12px;font-weight:500;color:#111827;text-align:right">${v??'-'}</span></div>`).join('');
    const soEl=document.getElementById('pSoHistory');
    const namaBulan=['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    if(!d.so_assignments||d.so_assignments.length===0){
      soEl.innerHTML='<div style="text-align:center;padding:16px;color:#d1d5db;font-size:12px">Belum ada penugasan SO</div>';
    }else{
      soEl.innerHTML=d.so_assignments.map(s=>`
        <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;padding:10px;background:#f9fafb;border-radius:8px;border:1px solid #f3f4f6">
          <div style="flex-shrink:0;margin-top:2px"><div style="width:8px;height:8px;border-radius:50%;background:#15803d"></div></div>
          <div style="flex:1;min-width:0">
            <div style="font-size:12px;font-weight:600;color:#111827">${s.posisi}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">${s.direktorat||''} ${s.kompartemen?'· '+s.kompartemen:''}</div>
            <div style="display:flex;gap:6px;margin-top:4px;flex-wrap:wrap">
              <span style="font-size:10px;color:#9ca3af">📅 ${namaBulan[s.bulan]} ${s.tahun}</span>
              ${s.job_grade?`<span style="font-size:10px;padding:1px 6px;border-radius:4px;background:#dbeafe;color:#1d4ed8">JG ${s.job_grade}</span>`:''}
              ${s.core?`<span style="font-size:10px;padding:1px 6px;border-radius:4px;background:${s.core==='Core'?'#dcfce7':'#f3f4f6'};color:${s.core==='Core'?'#15803d':'#6b7280'}">${s.core}</span>`:''}
            </div>
          </div>
        </div>`).join('');
    }
    if(d.assign_logs&&d.assign_logs.length>0){
      soEl.innerHTML+='<div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin:12px 0 8px">Log Aktivitas Assign</div>';
      soEl.innerHTML+=d.assign_logs.map(l=>`
        <div style="font-size:11px;color:#6b7280;padding:6px 0;border-bottom:1px solid #f9fafb">
          <span style="color:#374151;font-weight:500">${l.target}</span>
          <span style="color:#9ca3af"> · ${l.user_name} · ${new Date(l.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})}</span>
        </div>`).join('');
    }
    document.getElementById('panelLoading').style.display='none';
    document.getElementById('panelContent').style.display='block';
  }).catch(()=>{document.getElementById('panelLoading').innerHTML='<div style="color:#dc2626;font-size:13px">Gagal memuat data</div>';});
}
function closePanel(){document.getElementById('karyawanPanel').style.right='-440px';document.getElementById('panelOverlay').style.display='none';}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closePanel();closeHapus();}});

let tambahTipe='';
function openTambah(tipe,dir,komp,dept,bag){
  tambahTipe=tipe;
  const titles={kompartemen:'Tambah Kompartemen',departemen:'Tambah Departemen',bagian:'Tambah Bagian',staff:'Tambah Posisi / Staff'};
  document.getElementById('modalTambahTitle').textContent=titles[tipe]||'Tambah';
  document.getElementById('ftDir').value=dir||'';
  document.getElementById('ftKomp').value=komp||'';
  document.getElementById('ftDept').value=dept||'';
  document.getElementById('ftBag').value=bag||'';
  document.getElementById('ftFunc').value='';
  let info='';
  if(dir)  info+=`<div><span style="color:#9ca3af;font-size:11px">Direktorat: </span><strong>${dir}</strong></div>`;
  if(komp) info+=`<div><span style="color:#9ca3af;font-size:11px">Kompartemen: </span><strong>${komp}</strong></div>`;
  if(dept) info+=`<div><span style="color:#9ca3af;font-size:11px">Departemen: </span><strong>${dept}</strong></div>`;
  if(bag)  info+=`<div><span style="color:#9ca3af;font-size:11px">Bagian: </span><strong>${bag}</strong></div>`;
  const ki=document.getElementById('konteksInfo');ki.innerHTML=info;ki.style.display=info?'flex':'none';ki.style.flexDirection='column';
  ['fieldKompartemen','fieldDepartemen','fieldBagian','fieldStaff'].forEach(id=>document.getElementById(id).style.display='none');
  if(tipe==='kompartemen')document.getElementById('fieldKompartemen').style.display='block';
  if(tipe==='departemen') document.getElementById('fieldDepartemen').style.display='block';
  if(tipe==='bagian')     document.getElementById('fieldBagian').style.display='block';
  if(tipe==='staff')      document.getElementById('fieldStaff').style.display='block';
  ['inputKompartemen','inputDepartemen','inputBagian','inputFungsional','inputPosisi'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('modalTambahBg').style.display='flex';
}
function closeTambah(){document.getElementById('modalTambahBg').style.display='none';}
function submitTambah(){
  const form=document.getElementById('formTambah');
  if(tambahTipe==='kompartemen'){const val=document.getElementById('inputKompartemen').value.trim();if(!val){alert('Nama kompartemen wajib diisi!');return;}document.getElementById('ftKomp').value=val;document.getElementById('ftPosisiHidden').value='-';document.getElementById('ftMcTkoHidden').value='0';}
  else if(tambahTipe==='departemen'){const val=document.getElementById('inputDepartemen').value.trim();if(!val){alert('Nama departemen wajib diisi!');return;}document.getElementById('ftDept').value=val;document.getElementById('ftPosisiHidden').value='-';document.getElementById('ftMcTkoHidden').value='0';}
  else if(tambahTipe==='bagian'){const val=document.getElementById('inputBagian').value.trim();if(!val){alert('Nama bagian wajib diisi!');return;}document.getElementById('ftBag').value=val;document.getElementById('ftPosisiHidden').value='-';document.getElementById('ftMcTkoHidden').value='0';}
  else if(tambahTipe==='staff'){const posisi=document.getElementById('inputPosisi').value.trim();if(!posisi){alert('Job Title / Posisi wajib diisi!');return;}document.getElementById('ftPosisiHidden').value=posisi;document.getElementById('ftMcTkoHidden').value=document.getElementById('inputMcTko').value||'';document.getElementById('ftFunc').value=document.getElementById('inputFungsional').value.trim();}
  form.submit();
}
document.getElementById('modalTambahBg').addEventListener('click',function(e){if(e.target===this)closeTambah();});

let currentSoId=null,currentMc=1;
const currentAssignments={};
function openModal(soId,posisi,karyawanId,mc){
  currentSoId=soId;currentMc=mc||1;
  document.getElementById('modalPosisi').textContent=posisi;
  document.getElementById('assignSearch').value='';
  document.getElementById('assignSearchClear').style.display='none';
  document.getElementById('assignDropdown').style.display='none';
  document.getElementById('assignSelected').style.display='none';
  document.getElementById('infoCard').style.display='none';
  document.getElementById('kosongCard').style.display='none';
  const effectiveId = currentAssignments.hasOwnProperty(soId) ? currentAssignments[soId] : (karyawanId??null);
  document.getElementById('modalSelKaryawan').value=effectiveId||'';
  if(effectiveId){
    const opt=document.querySelector('#modalSelKaryawan option[value="'+effectiveId+'"]');
    if(opt){ selectAssignKaryawan(effectiveId, opt.dataset.nama||'', opt.dataset.nik||''); }
    else { loadKaryawanInfo(effectiveId); }
  } else { showKosong(); }
  document.getElementById('modalBg').style.display='flex';
  setTimeout(()=>document.getElementById('assignSearch').focus(),100);
}
function closeModal(){document.getElementById('modalBg').style.display='none';currentSoId=null;}
function loadKaryawanInfo(id){
  document.getElementById('infoCard').style.display='none';
  document.getElementById('kosongCard').style.display='none';
  if(!id){showKosong();return;}
  fetch('/api/karyawan/'+id+'/detail').then(r=>r.json()).then(d=>{
    document.getElementById('iNik').textContent=d.nik??'-';
    document.getElementById('iJab').textContent=d.jabatan_saat_ini??'-';
    document.getElementById('iDir').textContent=d.direktorat??'-';
    document.getElementById('iKomp').textContent=d.kompartemen??'-';
    document.getElementById('iDept').textContent=d.departemen??'-';
    document.getElementById('iJg').textContent=d.job_grade??'-';
    document.getElementById('iPg').textContent=d.person_grade??'-';
    const dev=1-currentMc;
    document.getElementById('deviasiVal').textContent=dev;
    document.getElementById('deviasiVal').style.color=dev<0?'#d97706':(dev>0?'#dc2626':'#15803d');
    document.getElementById('deviasiBox').style.background=dev<0?'#fef3c7':(dev>0?'#fee2e2':'#dcfce7');
    document.getElementById('infoCard').style.display='grid';
  });
}
function showKosong(){document.getElementById('deviasiKosong').textContent=0-currentMc;document.getElementById('kosongCard').style.display='block';}
function saveAssign(){
  const karyawanId=document.getElementById('modalSelKaryawan').value;
  fetch('/struktur-organisasi/'+currentSoId,{
    method:'PUT',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':_pd.csrf},
    body:JSON.stringify({karyawan_id:karyawanId||null}),
  }).then(r=>r.json()).then(d=>{
    if(!d.success)return;
    currentAssignments[currentSoId]=d.karyawan_id||null;
    const td=document.getElementById('td-karyawan-'+currentSoId);
    if(d.nama_karyawan){
      td.innerHTML='<div style="display:flex;align-items:center;gap:8px"><div style="width:26px;height:26px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">'+initials(d.nama_karyawan)+'</div><div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel('+d.karyawan_id+')">'+d.nama_karyawan+'</div><div style="font-size:11px;color:#9ca3af">'+(d.nik_karyawan??'')+'</div></div></div>';
    }else{
      td.innerHTML='<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>';
    }
    const pEl=document.getElementById('pengisian-'+currentSoId);
    const dEl=document.getElementById('deviasi-'+currentSoId);
    pEl.textContent=d.pengisian;pEl.style.background=d.pengisian?'#dcfce7':'#f3f4f6';pEl.style.color=d.pengisian?'#15803d':'#6b7280';
    dEl.textContent=d.deviasi;dEl.style.color=d.warna;dEl.style.background=d.deviasi==0?'#dcfce7':(d.deviasi>0?'#fee2e2':'#fef3c7');
    closeModal();
  });
}
document.getElementById('modalBg').addEventListener('click',function(e){if(e.target===this)closeModal();});

function collapseAll(){
  document.querySelectorAll('tr[data-slug]').forEach(tr=>{
    const slug=tr.dataset.slug;
    if(stateDir[slug]!==false){stateDir[slug]=false;setRows('child-'+slug,false);rotateIcon(slug,true);
    Object.keys(stateKomp).forEach(k=>{if(k.startsWith(slug)){stateKomp[k]=false;rotateIcon(k,false);}});
    Object.keys(stateDept).forEach(k=>{if(k.startsWith(slug)){stateDept[k]=false;rotateIcon(k,false);}});}
  });
}
function expandAll(){
  document.querySelectorAll('tr[data-slug]').forEach(tr=>{
    const slug=tr.dataset.slug;
    if(stateDir[slug]===false){stateDir[slug]=true;setRows('child-'+slug,true);rotateIcon(slug,false);}
  });
}

function avatarColor(str){
  const colors=['#15803d','#185fa5','#7c3aed','#d97706','#0891b2','#be185d','#dc2626','#374151'];
  let hash=0;
  for(let i=0;i<str.length;i++) hash=str.charCodeAt(i)+((hash<<5)-hash);
  return colors[Math.abs(hash)%colors.length];
}

function initials(nama, max = 3) {
  nama = (nama || '').trim();
  if (nama === '') return '?';
  const kata = nama.split(/\s+/).filter(Boolean);
  if (kata.length === 1) return kata[0].substring(0, 2).toUpperCase();
  return kata.map(k => k.charAt(0)).join('').substring(0, max).toUpperCase();
}

let currentEditId=null;
function openEdit(soId,posisi,jg,mc,core,pengisian){
  currentEditId=soId;
  document.getElementById('editPosisiLabel').textContent=posisi;
  document.getElementById('editInputPosisi').value=posisi;
  document.getElementById('editInputJg').value=jg||'';
  document.getElementById('editInputMc').value=mc||'';
  document.getElementById('editInputCore').value=core||'Non Core';
  document.getElementById('editInputPengisian').value=pengisian??0;
  updateDeviasiPreview();
  document.getElementById('modalEditBg').style.display='flex';
}
function updateDeviasiPreview(){
  const mc=parseInt(document.getElementById('editInputMc').value)||0;
  const peng=parseInt(document.getElementById('editInputPengisian').value)||0;
  const dev=peng-mc;
  const el=document.getElementById('editDeviasiPreview');
  el.textContent=dev;
  el.style.color=dev===0?'#15803d':(dev>0?'#dc2626':'#d97706');
}
function closeEdit(){document.getElementById('modalEditBg').style.display='none';currentEditId=null;}
function saveEdit(){
  const posisi=document.getElementById('editInputPosisi').value.trim();
  if(!posisi){alert('Posisi wajib diisi!');return;}
  const jg=document.getElementById('editInputJg').value;
  const mc=document.getElementById('editInputMc').value;
  const core=document.getElementById('editInputCore').value;
  const pengisian=document.getElementById('editInputPengisian').value;
  fetch('/struktur-organisasi/'+currentEditId+'/posisi',{
    method:'PATCH',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':_pd.csrf},
    body:JSON.stringify({posisi,job_grade:jg||null,mc_tko:mc!==''?mc:null,core,pengisian:pengisian!==''?parseInt(pengisian):null}),
  }).then(r=>r.json()).then(d=>{
    if(!d.success){alert('Gagal menyimpan');return;}
    const row=document.getElementById('row-'+currentEditId);
    if(row){
      const posisiSpan=row.querySelector('td:first-child span:first-child');
      if(posisiSpan)posisiSpan.textContent=d.posisi;
      const jgCell=row.cells[1];
      if(jgCell&&d.job_grade)jgCell.querySelector('span').textContent=d.job_grade;
      const mcCell=row.cells[2];
      if(mcCell)mcCell.textContent=d.mc_tko;
      const pengEl=document.getElementById('pengisian-'+currentEditId);
      if(pengEl&&d.pengisian!==undefined){pengEl.textContent=d.pengisian;pengEl.style.background=d.pengisian?'#dcfce7':'#f3f4f6';pengEl.style.color=d.pengisian?'#15803d':'#6b7280';}
      const devEl=document.getElementById('deviasi-'+currentEditId);
      if(devEl){devEl.textContent=d.deviasi;devEl.style.color=d.warna;devEl.style.background=d.deviasi==0?'#dcfce7':(d.deviasi>0?'#fee2e2':'#fef3c7');}
      const coreCell=row.cells[5];
      if(coreCell){const s=coreCell.querySelector('span');if(s){s.textContent=d.core;s.style.background=d.core==='Core'?'#dcfce7':'#dbeafe';s.style.color=d.core==='Core'?'#15803d':'#185fa5';}}
      row.style.transition='background-color 0.5s';
      row.style.backgroundColor='#fef9c3';
      setTimeout(()=>{row.style.backgroundColor='';},2000);
    }
    closeEdit();
  }).catch(()=>alert('Terjadi kesalahan'));
}
document.getElementById('modalEditBg').addEventListener('click',function(e){if(e.target===this)closeEdit();});

@if(session('new_row_id'))
document.addEventListener('DOMContentLoaded',function(){
  const newRow=document.getElementById('row-{{ session("new_row_id") }}');
  if(newRow){
    setTimeout(()=>{
      newRow.scrollIntoView({behavior:'smooth',block:'center'});
      newRow.style.transition='background-color 0.5s';
      newRow.style.backgroundColor='#fef9c3';
      setTimeout(()=>{newRow.style.backgroundColor='';},2500);
    },300);
  }
});
@endif

let assignHighlight=-1;
document.addEventListener('DOMContentLoaded',function(){
  document.getElementById('assignDropdownList').addEventListener('click',function(e){
    const item=e.target.closest('[data-assign-id]');
    if(item) selectAssignKaryawan(item.dataset.assignId, item.dataset.assignNama, item.dataset.assignNik);
  });
  document.getElementById('assignDropdownList').addEventListener('mouseover',function(e){
    const item=e.target.closest('[data-assign-id]');
    if(!item)return;
    document.querySelectorAll('[data-assign-id]').forEach((el,i)=>{
      const isHover=el===item;
      el.style.background=isHover?'#f0fdf4':'';
      el.style.color=isHover?'#15803d':'#111827';
      if(isHover)assignHighlight=i;
    });
  });
});

function onAssignSearch(val){
  const clr=document.getElementById('assignSearchClear');
  if(clr)clr.style.display=val?'block':'none';
  document.getElementById('assignSelected').style.display='none';
  if(val.length<1){document.getElementById('assignDropdown').style.display='none';return;}
  const kw=val.toLowerCase();
  const list=document.getElementById('assignDropdownList');
  const results=[];
  document.querySelectorAll('#modalSelKaryawan option').forEach(function(opt){
    if(!opt.value)return;
    if((opt.dataset.nama||'').includes(kw)||(opt.dataset.nik||'').includes(kw))
      results.push({id:opt.value,nama:opt.dataset.nama,nik:opt.dataset.nik,display:opt.textContent.trim()});
  });
  if(results.length===0){
    list.innerHTML='<div style="padding:12px 14px;font-size:13px;color:#9ca3af">Tidak ditemukan</div>';
  }else{
    list.innerHTML=results.slice(0,20).map(function(r){
      const ini=initials(r.display.split(' \u2014 ')[0]);
      const color=avatarColor(r.display);
      return '<div data-assign-id="'+r.id+'" data-assign-nama="'+r.nama+'" data-assign-nik="'+r.nik+'" style="padding:8px 14px;font-size:13px;cursor:pointer;border-bottom:1px solid #f3f4f6;color:#111827;display:flex;align-items:center;gap:10px">'
        +'<div style="width:30px;height:30px;border-radius:50%;background:'+color+';display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;flex-shrink:0">'+ini+'</div>'
        +'<div><div style="font-size:13px;font-weight:500;color:#111827">'+r.display.split(' \u2014 ')[0]+'</div>'
        +'<div style="font-size:11px;color:#9ca3af">'+r.display.split(' \u2014 ')[1]+'</div></div>'
        +'</div>';
    }).join('');
  }
  assignHighlight=-1;
  document.getElementById('assignDropdown').style.display='block';
}
function onAssignKeydown(e){
  const items=document.querySelectorAll('[data-assign-id]');
  if(!items.length)return;
  if(e.key==='ArrowDown'){assignHighlight=Math.min(assignHighlight+1,items.length-1);items[assignHighlight].dispatchEvent(new Event('mouseover',{bubbles:true}));e.preventDefault();}
  else if(e.key==='ArrowUp'){assignHighlight=Math.max(assignHighlight-1,0);items[assignHighlight].dispatchEvent(new Event('mouseover',{bubbles:true}));e.preventDefault();}
  else if(e.key==='Enter'&&assignHighlight>=0&&items[assignHighlight]){items[assignHighlight].click();e.preventDefault();}
  else if(e.key==='Escape'){document.getElementById('assignDropdown').style.display='none';}
}
function selectAssignKaryawan(id,nama,nik){
  document.getElementById('assignDropdown').style.display='none';
  const opt=document.querySelector('#modalSelKaryawan option[value="'+id+'"]');
  const display=opt?opt.textContent.trim():'';
  document.getElementById('assignSearch').value=display;
  const clr=document.getElementById('assignSearchClear');
  if(clr)clr.style.display=id?'block':'none';
  if(id){
    const ini=initials(display.split(' \u2014 ')[0]||display);
    document.getElementById('assignSelAvatar').textContent=ini;
    document.getElementById('assignSelAvatar').style.background=avatarColor(display||'');
    document.getElementById('assignSelNama').textContent=display.split(' \u2014 ')[0]||display;
    document.getElementById('assignSelNik').textContent='NIK: '+(display.split(' \u2014 ')[1]||nik||'');
    document.getElementById('assignSelected').style.display='block';
  }else{
    document.getElementById('assignSelected').style.display='none';
    document.getElementById('assignSearch').value='';
    if(clr)clr.style.display='none';
  }
  document.getElementById('modalSelKaryawan').value=id||'';
  if(id)loadKaryawanInfo(id);else showKosong();
}
function clearAssign(lepas){
  selectAssignKaryawan('','','');
  if(lepas)showKosong();
}

let _groupState = {};
function openEditGroup(tipe, nama, dir, komp) {
  _groupState = {tipe, nama, dir, komp};
  const labels = {direktorat:'Direktorat', kompartemen:'Kompartemen', departemen:'Departemen'};
  document.getElementById('editGroupTitle').textContent = 'Edit ' + labels[tipe];
  document.getElementById('editGroupSubtitle').textContent = nama;
  document.getElementById('editGroupInput').value = nama;
  document.getElementById('modalEditGroupBg').style.display = 'flex';
  setTimeout(()=>document.getElementById('editGroupInput').focus(), 100);
}
function closeEditGroup() { document.getElementById('modalEditGroupBg').style.display = 'none'; }
function saveEditGroup() {
  const baru = document.getElementById('editGroupInput').value.trim();
  if (!baru) { alert('Nama tidak boleh kosong!'); return; }
  if (baru === _groupState.nama) { closeEditGroup(); return; }
  fetch('/struktur-organisasi/rename-group', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_pd.csrf},
    body: JSON.stringify({tipe:_groupState.tipe, lama:_groupState.nama, baru, direktorat:_groupState.dir, kompartemen:_groupState.komp, bulan:_pd.bulan, tahun:_pd.tahun})
  }).then(r=>r.json()).then(d=>{
    if (d.success) { closeEditGroup(); location.reload(); }
    else alert('Gagal menyimpan');
  }).catch(()=>alert('Terjadi kesalahan'));
}
document.getElementById('modalEditGroupBg').addEventListener('click', function(e){if(e.target===this)closeEditGroup();});
document.getElementById('editGroupInput').addEventListener('keydown', function(e){if(e.key==='Enter')saveEditGroup();});

function confirmDeleteGroup(tipe, nama, dir, komp) {
  _groupState = {tipe, nama, dir, komp};
  const labels = {direktorat:'Direktorat', kompartemen:'Kompartemen', departemen:'Departemen'};
  document.getElementById('deleteGroupLabel').textContent = labels[tipe] + ' "' + nama + '"';
  document.getElementById('modalDeleteGroupBg').style.display = 'flex';
}
function closeDeleteGroup() { document.getElementById('modalDeleteGroupBg').style.display = 'none'; }
function doDeleteGroup() {
  fetch('/struktur-organisasi/delete-group', {
    method: 'DELETE',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_pd.csrf},
    body: JSON.stringify({tipe:_groupState.tipe, nama:_groupState.nama, direktorat:_groupState.dir, kompartemen:_groupState.komp, bulan:_pd.bulan, tahun:_pd.tahun})
  }).then(r=>r.json()).then(d=>{
    if (d.success) { closeDeleteGroup(); location.reload(); }
    else alert('Gagal menghapus: ' + (d.message||''));
  }).catch(()=>alert('Terjadi kesalahan'));
}
document.getElementById('modalDeleteGroupBg').addEventListener('click', function(e){if(e.target===this)closeDeleteGroup();});
</script>
@endpush