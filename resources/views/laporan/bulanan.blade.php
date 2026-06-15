@extends('layouts.app')
@section('title', 'Laporan Bulanan')
@section('breadcrumb', 'Laporan Bulanan')

@section('content')

@php
$namaBulanList = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp

{{-- HEADER --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div>
    <div style="font-size:20px;font-weight:700;color:#111827">Laporan Bulanan</div>
    <div style="font-size:13px;color:#6b7280;margin-top:2px">Ringkasan aktivitas SDM — {{ $namaBulan }}</div>
  </div>
  <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <select name="bulan" style="border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;background:#fff">
      @foreach($namaBulanList as $i => $nb)
        @if($i > 0)<option value="{{ $i }}" {{ $bulan==$i?'selected':'' }}>{{ $nb }}</option>@endif
      @endforeach
    </select>
    <select name="tahun" style="border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;background:#fff">
      @for($y = now()->year; $y >= 2020; $y--)
        <option value="{{ $y }}" {{ $tahun==$y?'selected':'' }}>{{ $y }}</option>
      @endfor
    </select>
    <button type="submit" style="padding:8px 18px;background:#15803d;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Lihat</button>
  </form>
</div>

{{-- SUMMARY CARDS --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
  @foreach([
    ['label'=>'Karyawan Masuk',  'val'=>$stats['karyawan_masuk'],  'color'=>'#15803d','bg'=>'#dcfce7','icon'=>'👋'],
    ['label'=>'Karyawan Keluar', 'val'=>$stats['karyawan_keluar'], 'color'=>'#dc2626','bg'=>'#fee2e2','icon'=>'🚪'],
    ['label'=>'Total Pergerakan','val'=>$stats['total_pergerakan'],'color'=>'#185fa5','bg'=>'#dbeafe','icon'=>'📈'],
    ['label'=>'Promosi',         'val'=>$stats['promosi'],         'color'=>'#15803d','bg'=>'#dcfce7','icon'=>'⬆️'],
    ['label'=>'Mutasi',          'val'=>$stats['mutasi'],          'color'=>'#185fa5','bg'=>'#dbeafe','icon'=>'↔️'],
    ['label'=>'Demosi',          'val'=>$stats['demosi'],          'color'=>'#dc2626','bg'=>'#fee2e2','icon'=>'⬇️'],
    ['label'=>'Assessment Rek.', 'val'=>$stats['assessment'],      'color'=>'#7c3aed','bg'=>'#f5f3ff','icon'=>'📋'],
    ['label'=>'Assessment Komp.','val'=>$stats['kompetensi'],      'color'=>'#0891b2','bg'=>'#ecfeff','icon'=>'⭐'],
    ['label'=>'Pensiun Bulan Ini','val'=>$stats['pensiun'],         'color'=>'#d97706','bg'=>'#fef3c7','icon'=>'🎂'],
  ] as $s)
  <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:16px;display:flex;align-items:center;gap:12px">
    <div style="width:42px;height:42px;border-radius:10px;background:{{ $s['bg'] }};display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">{{ $s['icon'] }}</div>
    <div>
      <div style="font-size:24px;font-weight:800;color:{{ $s['color'] }};line-height:1">{{ $s['val'] }}</div>
      <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $s['label'] }}</div>
    </div>
  </div>
  @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

{{-- KARYAWAN MASUK --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden">
  <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px">
    <span style="font-size:14px">👋</span>
    <span style="font-size:13px;font-weight:700;color:#111827">Karyawan Masuk</span>
    <span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d;margin-left:auto">{{ $karyawanMasuk->count() }}</span>
  </div>
  <div style="padding:0 16px">
    @forelse($karyawanMasuk as $k)
    <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f9fafb">
      <div style="width:30px;height:30px;border-radius:50%;background:#15803d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;flex-shrink:0">{{ initials($k->nama) }}</div>
      <div style="flex:1;min-width:0">
        <div style="font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $k->nama }}</div>
        <div style="font-size:11px;color:#9ca3af">{{ $k->direktorat?->nama_direktorat ?? '-' }}</div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="font-size:11px;color:#15803d;font-weight:600">{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d M') }}</div>
        <div style="font-size:10px;color:#9ca3af">{{ $k->nik }}</div>
      </div>
    </div>
    @empty
    <div style="padding:20px;text-align:center;color:#d1d5db;font-size:12px">Tidak ada</div>
    @endforelse
  </div>
</div>

{{-- KARYAWAN KELUAR --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden">
  <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px">
    <span style="font-size:14px">🚪</span>
    <span style="font-size:13px;font-weight:700;color:#111827">Karyawan Keluar / Tidak Aktif</span>
    <span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#fee2e2;color:#dc2626;margin-left:auto">{{ $karyawanKeluar->count() }}</span>
  </div>
  <div style="padding:0 16px">
    @forelse($karyawanKeluar as $k)
    <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f9fafb">
      <div style="width:30px;height:30px;border-radius:50%;background:#dc2626;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;flex-shrink:0">{{ initials($k->nama) }}</div>
      <div style="flex:1;min-width:0">
        <div style="font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $k->nama }}</div>
        <div style="font-size:11px;color:#9ca3af">{{ $k->direktorat?->nama_direktorat ?? '-' }}</div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <span style="font-size:10px;padding:2px 7px;border-radius:20px;background:#fee2e2;color:#dc2626;font-weight:600">Tidak Aktif</span>
        <div style="font-size:10px;color:#9ca3af;margin-top:2px">{{ $k->nik }}</div>
      </div>
    </div>
    @empty
    <div style="padding:20px;text-align:center;color:#d1d5db;font-size:12px">Tidak ada</div>
    @endforelse
  </div>
</div>

{{-- PERGERAKAN JABATAN --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden">
  <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px">
    <span style="font-size:14px">📈</span>
    <span style="font-size:13px;font-weight:700;color:#111827">Pergerakan Jabatan</span>
    <span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#185fa5;margin-left:auto">{{ $stats['total_pergerakan'] }}</span>
  </div>
  <div style="padding:0 16px">
    @foreach([['promosi',$promosi,'#dcfce7','#15803d','⬆️ Promosi'],['mutasi',$mutasi,'#dbeafe','#185fa5','↔️ Mutasi'],['demosi',$demosi,'#fee2e2','#dc2626','⬇️ Demosi']] as [$tipe,$list,$bg,$color,$label])
    @if($list->count() > 0)
    <div style="padding:8px 0">
      <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">{{ $label }} ({{ $list->count() }})</div>
      @foreach($list as $h)
      <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f9fafb">
        <div style="width:26px;height:26px;border-radius:50%;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:{{ $color }};flex-shrink:0">{{ initials($h->karyawan->nama) }}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $h->karyawan->nama ?? '-' }}</div>
          <div style="font-size:11px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $h->jabatan_saat_ini ?? '-' }}</div>
        </div>
        <div style="font-size:11px;color:#9ca3af;flex-shrink:0">{{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M') }}</div>
      </div>
      @endforeach
    </div>
    @endif
    @endforeach
    @if($stats['total_pergerakan'] === 0)
    <div style="padding:20px;text-align:center;color:#d1d5db;font-size:12px">Tidak ada</div>
    @endif
  </div>
</div>

{{-- ASSESSMENT --}}
<div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden">
  <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px">
    <span style="font-size:14px">📋</span>
    <span style="font-size:13px;font-weight:700;color:#111827">Assessment</span>
    <span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#f5f3ff;color:#7c3aed;margin-left:auto">{{ $stats['assessment'] + $stats['kompetensi'] }}</span>
  </div>
  <div style="padding:0 16px">
    @if($assessments->count() > 0)
    <div style="padding:8px 0">
      <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Rekomendasi ({{ $assessments->count() }})</div>
      @foreach($assessments->take(5) as $a)
      <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f9fafb">
        <div style="width:26px;height:26px;border-radius:50%;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#7c3aed;flex-shrink:0">{{ initials($a->karyawan->nama) }}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $a->karyawan->nama ?? '-' }}</div>
        </div>
        @php $rekColor=['ready'=>'#dcfce7','ready_with_development'=>'#fef3c7','not_ready'=>'#fee2e2'];$rekText=['ready'=>'#15803d','ready_with_development'=>'#d97706','not_ready'=>'#dc2626'];$rekLabel=['ready'=>'Ready','ready_with_development'=>'RWD','not_ready'=>'Not Ready']; @endphp
        <span style="font-size:10px;padding:2px 7px;border-radius:20px;background:{{ $rekColor[$a->rekomendasi_final]??'#f3f4f6' }};color:{{ $rekText[$a->rekomendasi_final]??'#6b7280' }};font-weight:600;flex-shrink:0">{{ $rekLabel[$a->rekomendasi_final]??$a->rekomendasi_final }}</span>
      </div>
      @endforeach
    </div>
    @endif
    @if($kompetensi->count() > 0)
    <div style="padding:8px 0">
      <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Kompetensi ({{ $kompetensi->count() }})</div>
      @foreach($kompetensi->take(5) as $k)
      <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f9fafb">
        <div style="flex:1;min-width:0;font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $k->karyawan->nama ?? '-' }}</div>
        <span style="font-size:10px;padding:2px 7px;border-radius:20px;background:{{ $k->kesimpulan==='QUALIFIED'?'#dcfce7':'#fee2e2' }};color:{{ $k->kesimpulan==='QUALIFIED'?'#15803d':'#dc2626' }};font-weight:600;flex-shrink:0">{{ $k->kesimpulan }}</span>
      </div>
      @endforeach
    </div>
    @endif
    @if($stats['assessment'] + $stats['kompetensi'] === 0)
    <div style="padding:20px;text-align:center;color:#d1d5db;font-size:12px">Tidak ada</div>
    @endif
  </div>
</div>

{{-- AKAN PENSIUN --}}
@if($akanPensiun->count() > 0)
<div style="background:#fff;border-radius:12px;border:1px solid #fde68a;overflow:hidden;grid-column:span 2">
  <div style="padding:14px 18px;border-bottom:1px solid #fef3c7;display:flex;align-items:center;gap:8px;background:#fffbeb">
    <span style="font-size:14px">🎂</span>
    <span style="font-size:13px;font-weight:700;color:#92400e">Pensiun Bulan {{ $namaBulanList[$bulan] }} {{ $tahun }}</span>
    <span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;background:#fef3c7;color:#d97706;margin-left:auto">{{ $akanPensiun->count() }} karyawan</span>
  </div>
  <div style="padding:14px 18px;display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
    @foreach($akanPensiun as $k)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px;display:flex;align-items:center;gap:8px">
      <div style="width:32px;height:32px;border-radius:50%;background:#d97706;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700;flex-shrink:0">{{ initials($k->nama) }}</div>
      <div style="min-width:0">
        <div style="font-size:12px;font-weight:600;color:#92400e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $k->nama }}</div>
        <div style="font-size:11px;color:#d97706">{{ $k->direktorat?->nama_direktorat ?? '-' }}</div>
      </div>
    </div>
    @endforeach
  </div>
</div>
@endif

</div>

@endsection