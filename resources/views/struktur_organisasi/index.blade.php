@extends('layouts.app')
@section('title', 'Struktur Organisasi')
@section('breadcrumb-parent', 'Planning')
@section('breadcrumb', 'Struktur Organisasi')

@php
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
    <a href="{{ route('struktur-organisasi.export') }}?bulan={{ $bulan }}&tahun={{ $tahun }}" style="padding:8px 16px;background:#15803d;color:#fff;border-radius:8px;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:5px;white-space:nowrap">
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
        <tr onclick="toggleDir('{{ $dirSlug }}')" style="cursor:pointer" data-type="dir" data-text="{{ strtolower($dir['label']) }}">
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
                        <div style="width:26px;height:26px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ strtoupper(substr($row->nama_karyawan??'?',0,2)) }}</div>
                        <div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel({{ $row->karyawan_id }})">{{ $row->nama_karyawan }}</div><div style="font-size:11px;color:#9ca3af">{{ $row->nik_karyawan }}</div></div>
                      </div>
                    @else<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>@endif
                  </td>
                  @if(!$isUser)
                  <td style="padding:10px 14px;text-align:center;white-space:nowrap">
                    <div style="display:flex;gap:4px;justify-content:center">
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
                      <div style="width:26px;height:26px;border-radius:50%;background:{{ $isFuncReal2?'#15803d':'#185fa5' }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ strtoupper(substr($row->nama_karyawan??'?',0,2)) }}</div>
                      <div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel({{ $row->karyawan_id }})">{{ $row->nama_karyawan }}</div><div style="font-size:11px;color:#9ca3af">{{ $row->nik_karyawan }}</div></div>
                    </div>
                  @else<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>@endif
                </td>
                @if(!$isUser)
                <td style="padding:10px 14px;text-align:center;white-space:nowrap">
                  <div style="display:flex;gap:4px;justify-content:center">
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
                    <div style="width:26px;height:26px;border-radius:50%;background:{{ $isFuncReal3?'#15803d':'#374151' }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ strtoupper(substr($row->nama_karyawan??'?',0,2)) }}</div>
                    <div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel({{ $row->karyawan_id }})">{{ $row->nama_karyawan }}</div><div style="font-size:11px;color:#9ca3af">{{ $row->nik_karyawan }}</div></div>
                  </div>
                @else<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>@endif
              </td>
              @if(!$isUser)
              <td style="padding:10px 14px;text-align:center;white-space:nowrap">
                <div style="display:flex;gap:4px;justify-content:center">
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
                <div style="width:26px;height:26px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">{{ strtoupper(substr($row->nama_karyawan??'?',0,2)) }}</div>
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
  <div style="padding:10px 16px;font-size:12px;color:#9ca3af;border-top:1px solid #f0f0eb;display:flex;align-items:center;justify-content:space-between">
    <span>Total <strong>{{ $allJabatan->count() }}</strong> posisi — Periode <strong>{{ $periodeSaatIni }}</strong></span>
    <span id="searchCount" style="display:none;color:#15803d;font-weight:600"></span>
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
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-top:16px;font-size:12px;color:#185fa5">
        ℹ️ Posisi, struktur, dan data karyawan akan disalin. Perubahan di periode baru tidak mempengaruhi periode lain.
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
      <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">History Jabatan</div><div id="pHistory"></div></div>
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
      <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Pilih Karyawan</label>
      <select id="modalSelKaryawan" onchange="loadKaryawanInfo(this.value)" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:13px;outline:none;background:#fff;color:#111827">
        <option value="">-- Kosong (Lepas Karyawan) --</option>
        @foreach($karyawans as $k)<option value="{{ $k->id }}">{{ $k->nama }} — {{ $k->nik }}</option>@endforeach
      </select>
      <div id="infoCard" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-top:12px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">NIK</div><div id="iNik" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Direktorat</div><div id="iDir" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Kompartemen</div><div id="iKomp" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Job Grade</div><div id="iJg" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Departemen</div><div id="iDept" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Person Grade</div><div id="iPg" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
          <div><div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Jabatan Saat Ini</div><div id="iJab" style="font-size:13px;font-weight:500;color:#111827">-</div></div>
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

@push('scripts')
<style>
@keyframes panelSpin{to{transform:rotate(360deg)}}
@keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
@keyframes toastBar{from{width:calc(100% - 40px)}to{width:0}}
</style>
<script>
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

function openSalin(){document.getElementById('modalSalinBg').style.display='flex';}
function closeSalin(){document.getElementById('modalSalinBg').style.display='none';}
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
    const noSk     = currentH?.no_sk     || '-';
    const tglSk    = currentH?.tanggal_mulai || '-';
    const rows=[['Direktorat',d.direktorat],['Kompartemen',d.kompartemen],['Departemen',d.departemen],['No. SK',noSk],['Tanggal SK',tglSk],['Tgl Masuk',d.tanggal_masuk],['Tgl Lahir',d.tanggal_lahir],['Pensiun',d.pensiun],['Lama Bekerja',d.lama_bekerja]];
    document.getElementById('pInfoRows').innerHTML=rows.map(([l,v])=>`<div style="display:flex;justify-content:space-between;align-items:flex-start;padding:7px 14px;border-bottom:1px solid #f9fafb;gap:10px"><span style="font-size:11px;color:#9ca3af;flex-shrink:0">${l}</span><span style="font-size:12px;font-weight:500;color:#111827;text-align:right">${v??'-'}</span></div>`).join('');
    const histEl=document.getElementById('pHistory');
    if(!d.history||d.history.length===0){histEl.innerHTML='<div style="text-align:center;padding:20px;color:#d1d5db;font-size:12px">Belum ada history jabatan</div>';}
    else{histEl.innerHTML=d.history.map((h,i)=>`<div style="display:flex;gap:12px;margin-bottom:14px"><div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0"><div style="width:10px;height:10px;border-radius:50%;background:${h.is_current?'#15803d':'#d1d5db'};flex-shrink:0;margin-top:3px"></div>${i<d.history.length-1?'<div style="width:2px;flex:1;background:#f3f4f6;margin-top:4px;min-height:20px"></div>':''}</div><div style="flex:1;padding-bottom:4px"><div style="font-size:12px;font-weight:600;color:${h.is_current?'#15803d':'#111827'}">${h.jabatan_saat_ini??'-'}</div><div style="font-size:11px;color:#6b7280;margin-top:2px">${h.nama_direktorat??''} ${h.nama_kompartemen?'· '+h.nama_kompartemen:''}</div><div style="display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;align-items:center"><span style="font-size:10px;color:#9ca3af">${h.tanggal_mulai??''} ${h.tanggal_selesai?'→ '+h.tanggal_selesai:(h.is_current?'→ Sekarang':'')}</span>${h.tipe?`<span style="font-size:10px;padding:1px 6px;border-radius:4px;background:#f3f4f6;color:#6b7280">${h.tipe}</span>`:''} ${h.no_sk?`<span style="font-size:10px;color:#9ca3af">SK: ${h.no_sk}</span>`:''}</div></div></div>`).join('');}
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
  if(tambahTipe==='kompartemen'){const val=document.getElementById('inputKompartemen').value.trim();if(!val){alert('Nama kompartemen wajib diisi!');return;}document.getElementById('ftKomp').value=val;document.getElementById('ftPosisiHidden').value='-';document.getElementById('ftMcTkoHidden').value='1';}
  else if(tambahTipe==='departemen'){const val=document.getElementById('inputDepartemen').value.trim();if(!val){alert('Nama departemen wajib diisi!');return;}document.getElementById('ftDept').value=val;document.getElementById('ftPosisiHidden').value='-';document.getElementById('ftMcTkoHidden').value='1';}
  else if(tambahTipe==='bagian'){const val=document.getElementById('inputBagian').value.trim();if(!val){alert('Nama bagian wajib diisi!');return;}document.getElementById('ftBag').value=val;document.getElementById('ftPosisiHidden').value='-';document.getElementById('ftMcTkoHidden').value='1';}
  else if(tambahTipe==='staff'){const posisi=document.getElementById('inputPosisi').value.trim();if(!posisi){alert('Job Title / Posisi wajib diisi!');return;}document.getElementById('ftPosisiHidden').value=posisi;document.getElementById('ftMcTkoHidden').value=document.getElementById('inputMcTko').value||'';document.getElementById('ftFunc').value=document.getElementById('inputFungsional').value.trim();}
  form.submit();
}
document.getElementById('modalTambahBg').addEventListener('click',function(e){if(e.target===this)closeTambah();});

let currentSoId=null,currentMc=1;
function openModal(soId,posisi,karyawanId,mc){currentSoId=soId;currentMc=mc||1;document.getElementById('modalPosisi').textContent=posisi;document.getElementById('modalSelKaryawan').value=karyawanId??'';document.getElementById('infoCard').style.display='none';document.getElementById('kosongCard').style.display='none';karyawanId?loadKaryawanInfo(karyawanId):showKosong();document.getElementById('modalBg').style.display='flex';}
function closeModal(){document.getElementById('modalBg').style.display='none';currentSoId=null;}
function loadKaryawanInfo(id){document.getElementById('infoCard').style.display='none';document.getElementById('kosongCard').style.display='none';if(!id){showKosong();return;}fetch('/api/karyawan/'+id+'/detail').then(r=>r.json()).then(d=>{document.getElementById('iNik').textContent=d.nik??'-';document.getElementById('iJab').textContent=d.jabatan_saat_ini??'-';document.getElementById('iDir').textContent=d.direktorat??'-';document.getElementById('iKomp').textContent=d.kompartemen??'-';document.getElementById('iDept').textContent=d.departemen??'-';document.getElementById('iJg').textContent=d.job_grade??'-';document.getElementById('iPg').textContent=d.person_grade??'-';const dev=1-currentMc;document.getElementById('deviasiVal').textContent=dev;document.getElementById('deviasiVal').style.color=dev<0?'#d97706':(dev>0?'#dc2626':'#15803d');document.getElementById('deviasiBox').style.background=dev<0?'#fef3c7':(dev>0?'#fee2e2':'#dcfce7');document.getElementById('infoCard').style.display='grid';});}
function showKosong(){document.getElementById('deviasiKosong').textContent=0-currentMc;document.getElementById('kosongCard').style.display='block';}
function saveAssign(){const karyawanId=document.getElementById('modalSelKaryawan').value;fetch('/struktur-organisasi/'+currentSoId,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({karyawan_id:karyawanId||null}),}).then(r=>r.json()).then(d=>{if(!d.success)return;const td=document.getElementById('td-karyawan-'+currentSoId);if(d.nama_karyawan){td.innerHTML='<div style="display:flex;align-items:center;gap:8px"><div style="width:26px;height:26px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:700;flex-shrink:0">'+d.nama_karyawan.substring(0,2).toUpperCase()+'</div><div><div style="font-size:12px;font-weight:600;color:#15803d;cursor:pointer;text-decoration:underline;text-underline-offset:2px" onclick="openPanel('+d.karyawan_id+')">'+d.nama_karyawan+'</div><div style="font-size:11px;color:#9ca3af">'+(d.nik_karyawan??'')+'</div></div></div>';}else{td.innerHTML='<span style="color:#d1d5db;font-size:12px;font-style:italic">Belum diisi</span>';}const pEl=document.getElementById('pengisian-'+currentSoId);const dEl=document.getElementById('deviasi-'+currentSoId);pEl.textContent=d.pengisian;pEl.style.background=d.pengisian?'#dcfce7':'#f3f4f6';pEl.style.color=d.pengisian?'#15803d':'#6b7280';dEl.textContent=d.deviasi;dEl.style.color=d.warna;dEl.style.background=d.deviasi==0?'#dcfce7':(d.deviasi>0?'#fee2e2':'#fef3c7');closeModal();});}
document.getElementById('modalBg').addEventListener('click',function(e){if(e.target===this)closeModal();});
</script>
@endpush
@endsection