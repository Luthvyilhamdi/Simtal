@extends('layouts.app')
@section('title', 'Laporan Bulanan')
@section('breadcrumb', 'Laporan Bulanan')

@push('styles')
<style>
* { box-sizing: border-box; }

/* ===== HEADER ===== */
.lap-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 14px; flex-wrap: wrap; }
.lap-title { font-size: 18px; font-weight: 700; color: #111827; }
.lap-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }

.lap-filter { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.lap-select { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: inherit; color: #374151; background: white; outline: none; cursor: pointer; transition: border-color .15s; }
.lap-select:focus { border-color: #15803d; }
.lap-btn { padding: 8px 18px; background: #15803d; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; transition: background .15s; }
.lap-btn:hover { background: #166534; }

/* ===== STATS ===== */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; margin-bottom: 20px; }
.stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 12px; min-width: 0; }
.stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-icon svg { width: 19px; height: 19px; fill: none; stroke-width: 1.8; }
.stat-num { font-size: 22px; font-weight: 800; line-height: 1; }
.stat-label { font-size: 11px; color: #9ca3af; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ===== REPORT GRID ===== */
.report-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
@media (min-width: 880px) { .report-grid { grid-template-columns: 1fr 1fr; } }

.report-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; min-width: 0; }
.report-card-header { padding: 14px 18px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 10px; }
.report-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.report-icon svg { width: 14px; height: 14px; fill: none; stroke-width: 2; }
.report-card-title { font-size: 13px; font-weight: 700; color: #111827; }
.report-count { padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-left: auto; flex-shrink: 0; }
.report-body { padding: 0 18px; }

.report-section-label { font-size: 10.5px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .5px; margin: 10px 0 6px; }
.report-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f9fafb; }
.report-body > .report-section-label:first-child,
.report-body > .report-row:first-child { margin-top: 0; }
.report-row:last-child { border-bottom: none; }
.report-avatar { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 700; flex-shrink: 0; }
.report-name { font-size: 12.5px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.report-sub { font-size: 11px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.report-meta { text-align: right; flex-shrink: 0; }
.report-meta-val { font-size: 11px; font-weight: 600; }
.report-meta-sub { font-size: 10px; color: #9ca3af; margin-top: 2px; }

.badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 20px; font-size: 10.5px; font-weight: 700; white-space: nowrap; flex-shrink: 0; }

.report-empty { padding: 28px 0; text-align: center; color: #d1d5db; font-size: 12px; }

/* ===== PENSIUN ===== */
.pensiun-card { background: white; border-radius: 12px; border: 1px solid #fde68a; overflow: hidden; }
@media (min-width: 880px) { .pensiun-card { grid-column: span 2; } }
.pensiun-header { padding: 14px 18px; border-bottom: 1px solid #fef3c7; background: #fffbeb; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.pensiun-title { font-size: 13px; font-weight: 700; color: #92400e; }
.pensiun-grid { padding: 14px 18px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
.pensiun-item { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px; display: flex; align-items: center; gap: 8px; min-width: 0; }
.pensiun-avatar { width: 32px; height: 32px; border-radius: 50%; background: #d97706; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 700; flex-shrink: 0; }
.pensiun-name { font-size: 12px; font-weight: 600; color: #92400e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pensiun-sub { font-size: 11px; color: #d97706; }

@media (max-width: 640px) {
    .lap-header { flex-direction: column; align-items: stretch; }
    .lap-filter { width: 100%; }
    .lap-select { flex: 1; min-width: 0; }
}
</style>
@endpush

@section('content')

@php
$namaBulanList = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$icoUserPlus  = '<svg viewBox="0 0 24 24" stroke="#15803d"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>';
$icoUserMinus = '<svg viewBox="0 0 24 24" stroke="#dc2626"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
$icoTrend     = '<svg viewBox="0 0 24 24" stroke="#185fa5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>';
$icoArrowUp   = '<svg viewBox="0 0 24 24" stroke="#15803d"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>';
$icoShuffle   = '<svg viewBox="0 0 24 24" stroke="#185fa5"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/></svg>';
$icoArrowDown = '<svg viewBox="0 0 24 24" stroke="#dc2626"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>';
$icoClipboard = '<svg viewBox="0 0 24 24" stroke="#7c3aed"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>';
$icoStar      = '<svg viewBox="0 0 24 24" stroke="#0891b2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
$icoCalendar  = '<svg viewBox="0 0 24 24" stroke="#d97706"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
@endphp

{{-- HEADER --}}
<div class="lap-header">
    <div>
        <div class="lap-title">Laporan Bulanan</div>
        <div class="lap-sub">Ringkasan aktivitas SDM — {{ $namaBulanList[$bulan] }} {{ $tahun }}</div>
    </div>
    <form method="GET" class="lap-filter">
        <select name="bulan" class="lap-select">
            @foreach($namaBulanList as $i => $nb)
                @if($i > 0)<option value="{{ $i }}" {{ $bulan==$i?'selected':'' }}>{{ $nb }}</option>@endif
            @endforeach
        </select>
        <select name="tahun" class="lap-select">
            @for($y = now()->year; $y >= 2020; $y--)
                <option value="{{ $y }}" {{ $tahun==$y?'selected':'' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="lap-btn">Lihat</button>
    </form>
</div>

{{-- SUMMARY CARDS --}}
<div class="stats-grid">
    @foreach([
        ['label'=>'Karyawan Masuk',   'val'=>$stats['karyawan_masuk'],  'color'=>'#15803d','bg'=>'#dcfce7','icon'=>$icoUserPlus],
        ['label'=>'Karyawan Keluar',  'val'=>$stats['karyawan_keluar'], 'color'=>'#dc2626','bg'=>'#fee2e2','icon'=>$icoUserMinus],
        ['label'=>'Total Pergerakan', 'val'=>$stats['total_pergerakan'],'color'=>'#185fa5','bg'=>'#dbeafe','icon'=>$icoTrend],
        ['label'=>'Promosi',          'val'=>$stats['promosi'],         'color'=>'#15803d','bg'=>'#dcfce7','icon'=>$icoArrowUp],
        ['label'=>'Mutasi',           'val'=>$stats['mutasi'],          'color'=>'#185fa5','bg'=>'#dbeafe','icon'=>$icoShuffle],
        ['label'=>'Demosi',           'val'=>$stats['demosi'],          'color'=>'#dc2626','bg'=>'#fee2e2','icon'=>$icoArrowDown],
        ['label'=>'Assessment Rek.',  'val'=>$stats['assessment'],      'color'=>'#7c3aed','bg'=>'#f5f3ff','icon'=>$icoClipboard],
        ['label'=>'Assessment Komp.', 'val'=>$stats['kompetensi'],      'color'=>'#0891b2','bg'=>'#ecfeff','icon'=>$icoStar],
        ['label'=>'Pensiun Bulan Ini','val'=>$stats['pensiun'],         'color'=>'#d97706','bg'=>'#fef3c7','icon'=>$icoCalendar],
    ] as $s)
    <div class="stat-card">
        <div class="stat-icon" style="background:{{ $s['bg'] }}">{!! $s['icon'] !!}</div>
        <div style="min-width:0">
            <div class="stat-num" style="color:{{ $s['color'] }}">{{ $s['val'] }}</div>
            <div class="stat-label">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="report-grid">

    {{-- KARYAWAN MASUK --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:#dcfce7">{!! $icoUserPlus !!}</div>
            <span class="report-card-title">Karyawan Masuk</span>
            <span class="report-count" style="background:#dcfce7;color:#15803d">{{ $karyawanMasuk->count() }}</span>
        </div>
        <div class="report-body">
            @forelse($karyawanMasuk as $k)
            <div class="report-row">
                <div class="report-avatar" style="background:#15803d">{{ initials($k->nama) }}</div>
                <div style="flex:1;min-width:0">
                    <div class="report-name">{{ $k->nama }}</div>
                    <div class="report-sub">{{ $k->direktorat?->nama_direktorat ?? '-' }}</div>
                </div>
                <div class="report-meta">
                    <div class="report-meta-val" style="color:#15803d">{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d M') }}</div>
                    <div class="report-meta-sub">{{ $k->nik }}</div>
                </div>
            </div>
            @empty
            <div class="report-empty">Tidak ada</div>
            @endforelse
        </div>
    </div>

    {{-- KARYAWAN KELUAR --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:#fee2e2">{!! $icoUserMinus !!}</div>
            <span class="report-card-title">Karyawan Keluar / Tidak Aktif</span>
            <span class="report-count" style="background:#fee2e2;color:#dc2626">{{ $karyawanKeluar->count() }}</span>
        </div>
        <div class="report-body">
            @forelse($karyawanKeluar as $k)
            <div class="report-row">
                <div class="report-avatar" style="background:#dc2626">{{ initials($k->nama) }}</div>
                <div style="flex:1;min-width:0">
                    <div class="report-name">{{ $k->nama }}</div>
                    <div class="report-sub">{{ $k->direktorat?->nama_direktorat ?? '-' }}</div>
                </div>
                <div class="report-meta">
                    <span class="badge" style="background:#fee2e2;color:#dc2626">Tidak Aktif</span>
                    <div class="report-meta-sub">{{ $k->nik }}</div>
                </div>
            </div>
            @empty
            <div class="report-empty">Tidak ada</div>
            @endforelse
        </div>
    </div>

    {{-- PERGERAKAN JABATAN --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:#dbeafe">{!! $icoTrend !!}</div>
            <span class="report-card-title">Pergerakan Jabatan</span>
            <span class="report-count" style="background:#dbeafe;color:#185fa5">{{ $stats['total_pergerakan'] }}</span>
        </div>
        <div class="report-body">
            @foreach([
                ['promosi', $promosi, '#dcfce7', '#15803d', $icoArrowUp,   'Promosi'],
                ['mutasi',  $mutasi,  '#dbeafe', '#185fa5', $icoShuffle,   'Mutasi'],
                ['demosi',  $demosi,  '#fee2e2', '#dc2626', $icoArrowDown, 'Demosi'],
            ] as [$tipe, $list, $bg, $color, $ico, $label])
            @if($list->count() > 0)
            <div class="report-section-label">{{ $label }} ({{ $list->count() }})</div>
            @foreach($list as $h)
            <div class="report-row">
                <div class="report-avatar" style="background:{{ $bg }};color:{{ $color }}">{{ initials($h->karyawan->nama) }}</div>
                <div style="flex:1;min-width:0">
                    <div class="report-name">{{ $h->karyawan->nama ?? '-' }}</div>
                    <div class="report-sub">{{ $h->jabatan_saat_ini ?? '-' }}</div>
                </div>
                <div class="report-meta-sub" style="flex-shrink:0">{{ \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M') }}</div>
            </div>
            @endforeach
            @endif
            @endforeach
            @if($stats['total_pergerakan'] === 0)
            <div class="report-empty">Tidak ada</div>
            @endif
        </div>
    </div>

    {{-- ASSESSMENT --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:#f5f3ff">{!! $icoClipboard !!}</div>
            <span class="report-card-title">Assessment</span>
            <span class="report-count" style="background:#f5f3ff;color:#7c3aed">{{ $stats['assessment'] + $stats['kompetensi'] }}</span>
        </div>
        <div class="report-body">
            @if($assessments->count() > 0)
            <div class="report-section-label">Rekomendasi ({{ $assessments->count() }})</div>
            @foreach($assessments->take(5) as $a)
            @php
                $rekColor = ['ready'=>'#dcfce7','ready_with_development'=>'#fef3c7','not_ready'=>'#fee2e2'];
                $rekText  = ['ready'=>'#15803d','ready_with_development'=>'#d97706','not_ready'=>'#dc2626'];
                $rekLabel = ['ready'=>'Ready','ready_with_development'=>'RWD','not_ready'=>'Not Ready'];
            @endphp
            <div class="report-row">
                <div class="report-avatar" style="background:#f5f3ff;color:#7c3aed">{{ initials($a->karyawan->nama) }}</div>
                <div style="flex:1;min-width:0">
                    <div class="report-name">{{ $a->karyawan->nama ?? '-' }}</div>
                </div>
                <span class="badge" style="background:{{ $rekColor[$a->rekomendasi_final]??'#f3f4f6' }};color:{{ $rekText[$a->rekomendasi_final]??'#6b7280' }}">{{ $rekLabel[$a->rekomendasi_final]??$a->rekomendasi_final }}</span>
            </div>
            @endforeach
            @endif
            @if($kompetensi->count() > 0)
            <div class="report-section-label">Kompetensi ({{ $kompetensi->count() }})</div>
            @foreach($kompetensi->take(5) as $k)
            <div class="report-row">
                <div style="flex:1;min-width:0" class="report-name">{{ $k->karyawan->nama ?? '-' }}</div>
                <span class="badge" style="background:{{ $k->kesimpulan==='QUALIFIED'?'#dcfce7':'#fee2e2' }};color:{{ $k->kesimpulan==='QUALIFIED'?'#15803d':'#dc2626' }}">{{ $k->kesimpulan }}</span>
            </div>
            @endforeach
            @endif
            @if($stats['assessment'] + $stats['kompetensi'] === 0)
            <div class="report-empty">Tidak ada</div>
            @endif
        </div>
    </div>

    {{-- AKAN PENSIUN --}}
    @if($akanPensiun->count() > 0)
    <div class="pensiun-card">
        <div class="pensiun-header">
            <div class="report-icon" style="background:#fef3c7">{!! $icoCalendar !!}</div>
            <span class="pensiun-title">Pensiun Bulan {{ $namaBulanList[$bulan] }} {{ $tahun }}</span>
            <span class="report-count" style="background:#fef3c7;color:#d97706;margin-left:auto">{{ $akanPensiun->count() }} karyawan</span>
        </div>
        <div class="pensiun-grid">
            @foreach($akanPensiun as $k)
            <div class="pensiun-item">
                <div class="pensiun-avatar">{{ initials($k->nama) }}</div>
                <div style="min-width:0">
                    <div class="pensiun-name">{{ $k->nama }}</div>
                    <div class="pensiun-sub">{{ $k->direktorat?->nama_direktorat ?? '-' }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection
