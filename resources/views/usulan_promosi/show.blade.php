@extends('layouts.app')
@section('title', 'Detail Usulan Promosi')
@section('breadcrumb-parent', 'Usulan Promosi')
@section('breadcrumb', $usulanPromosi->karyawan->nama ?? 'Detail')

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .detail-card { background:white;border-radius:14px;border:1px solid #e5e7eb;padding:20px;margin-bottom:16px; }
    .detail-card-title { font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.6px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px; }
    .detail-row { display:flex;justify-content:space-between;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid #f9fafb; }
    .detail-row:last-child { border-bottom:none;padding-bottom:0; }
    .detail-label { font-size:12px;color:#9ca3af;font-weight:500;flex-shrink:0; }
    .detail-value { font-size:13px;color:#111827;font-weight:600;text-align:right; }
    .badge { display:inline-flex;align-items:center;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700; }
    .mdg-check { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px; }
    .mdg-ok { background:#dcfce7;color:#15803d; }
    .mdg-no { background:#fee2e2;color:#dc2626; }

    /* Status timeline */
    .status-timeline { display:flex;align-items:center;gap:0;margin-bottom:20px;overflow-x:auto;padding-bottom:4px; }
    .timeline-step { display:flex;flex-direction:column;align-items:center;flex:1;min-width:90px; }
    .timeline-dot { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;border:2px solid #e5e7eb;background:white;z-index:1;position:relative; }
    .timeline-dot.done  { background:#15803d;border-color:#15803d;color:white; }
    .timeline-dot.active { background:#1d4ed8;border-color:#1d4ed8;color:white; }
    .timeline-dot.fail  { background:#dc2626;border-color:#dc2626;color:white; }
    .timeline-label { font-size:10px;font-weight:600;color:#9ca3af;margin-top:5px;text-align:center; }
    .timeline-label.done { color:#15803d; }
    .timeline-label.active { color:#1d4ed8; }
    .timeline-label.fail { color:#dc2626; }
    .timeline-line { flex:1;height:2px;background:#e5e7eb;margin-top:-16px; }
    .timeline-line.done { background:#15803d; }

    /* Update status form */
    .status-card { display:flex;flex-direction:column;gap:8px; }
    .status-option { display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;cursor:pointer;transition:all .15s;background:#fafafa; }
    .status-option input { display:none; }
    .status-option:hover { border-color:#d1d5db; }
    .status-option.sel { border-color:var(--c);background:var(--bg); }
    .status-dot { width:10px;height:10px;border-radius:50%;background:#d1d5db;flex-shrink:0; }
    .status-option.sel .status-dot { background:var(--c); }
    .status-name { font-size:13px;font-weight:600;color:#374151; }
    .status-option.sel .status-name { color:var(--c); }

    .form-group { display:flex;flex-direction:column;gap:6px;margin-bottom:14px; }
    .form-label { font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px; }
    .form-input { padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fafafa;outline:none;transition:all .15s;width:100%; }
    .form-input:focus { border-color:#16a34a;background:white;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .select-wrap { position:relative; }
    .select-wrap::after { content:'';position:absolute;right:14px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid #9ca3af;pointer-events:none; }
    .select-wrap select { appearance:none;padding-right:36px;cursor:pointer;width:100%; }

    .btn-update { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-update:hover { background:#166534; }
    .btn-update svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }

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

    .grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    @media (max-width:768px) { .grid-2 { grid-template-columns:1fr; } }

    /* Badge warna status (menggantikan style inline Blade) */
    .badge-shortlist { background:#dcfce7; color:#15803d; }
    .badge-longlist  { background:#dbeafe; color:#1d4ed8; }
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

<a href="{{ route('usulan_promosi.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Usulan Promosi
</a>

@php
    $u     = $usulanPromosi;
    $color = $u->status_color;
    $steps = [
        'draft'        => ['label'=>'Draft',         'icon'=>'📝'],
        'verif_berkas' => ['label'=>'Verif Berkas',  'icon'=>'🔍'],
        'sidang'       => ['label'=>'Sidang',        'icon'=>'⚖️'],
        'lulus'        => ['label'=>'Lulus',         'icon'=>'✅'],
        'tidak_lulus'  => ['label'=>'Tidak Lulus',   'icon'=>'❌'],
        'ditolak'      => ['label'=>'Ditolak',       'icon'=>'🚫'],
    ];
    $mainFlow   = ['draft','verif_berkas','sidang','lulus'];
    $statusIdx  = array_search($u->status, $mainFlow);
@endphp

{{-- HEADER --}}
<div style="background:white;border-radius:16px;border:1px solid #e5e7eb;padding:24px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
    <div style="display:flex;align-items:center;gap:14px">
        <div style="width:48px;height:48px;border-radius:50%;background:#15803d;color:white;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0">
            {{ initials($u->karyawan->nama) }}
        </div>
        <div>
            <div style="font-size:18px;font-weight:700;color:#111827">{{ $u->karyawan->nama ?? '-' }}</div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px">NIK {{ $u->karyawan->nik ?? '-' }} · {{ $u->jabatan_saat_ini ?? '-' }}</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        {{-- FIX Ln 121: Blade {{ }} di style dipindah ke data-* lalu diapply JS --}}
        <span class="badge" data-bg="{{ $color['bg'] }}" data-color="{{ $color['text'] }}" style="font-size:13px;padding:6px 16px">
            {{ $u->status_label }}
        </span>
        <span style="font-size:12px;color:#9ca3af">{{ $u->created_at->format('d M Y') }}</span>
    </div>
</div>

{{-- STATUS TIMELINE --}}
<div class="detail-card">
    <div class="detail-card-title">📊 Alur Status</div>
    <div class="status-timeline">
        @foreach($mainFlow as $idx => $step)
        @php
            $isDone   = $statusIdx !== false && array_search($step, $mainFlow) < $statusIdx;
            $isActive = $u->status === $step;
            $isFail   = in_array($u->status, ['tidak_lulus','ditolak']) && $step === 'lulus';
            $cls      = $isDone ? 'done' : ($isActive ? 'active' : '');
        @endphp
        @if($idx > 0)
            <div class="timeline-line {{ $isDone ? 'done' : '' }}"></div>
        @endif
        <div class="timeline-step">
            <div class="timeline-dot {{ $cls }}">{{ $steps[$step]['icon'] }}</div>
            <div class="timeline-label {{ $cls }}">{{ $steps[$step]['label'] }}</div>
        </div>
        @endforeach
        @if(in_array($u->status, ['tidak_lulus','ditolak']))
        <div class="timeline-line"></div>
        <div class="timeline-step">
            <div class="timeline-dot fail">{{ $steps[$u->status]['icon'] }}</div>
            <div class="timeline-label fail">{{ $steps[$u->status]['label'] }}</div>
        </div>
        @endif
    </div>
</div>

<div class="grid-2">
    {{-- DATA KARYAWAN --}}
    <div class="detail-card">
        <div class="detail-card-title">👤 Data Karyawan (Saat Usulan)</div>
        <div class="detail-row">
            <span class="detail-label">Jabatan Saat Ini</span>
            <span class="detail-value">{{ $u->jabatan_saat_ini ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Job Grade</span>
            <span class="detail-value">JG {{ $u->job_grade_saat_ini ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Person Grade</span>
            <span class="detail-value">PG {{ $u->person_grade_saat_ini ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Band</span>
            <span class="detail-value">{{ $u->band_saat_ini ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Struktural/Fungsional</span>
            <span class="detail-value">{{ $u->struktural_fungsional ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status MDG</span>
            <span class="detail-value" style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end">
                <span class="mdg-check {{ $u->mdg_band_ok ? 'mdg-ok' : 'mdg-no' }}">{{ $u->mdg_band_ok ? '✅' : '❌' }} Band</span>
                <span class="mdg-check {{ $u->mdg_jg_ok   ? 'mdg-ok' : 'mdg-no' }}">{{ $u->mdg_jg_ok   ? '✅' : '❌' }} JG</span>
                <span class="mdg-check {{ $u->mdg_pg_ok   ? 'mdg-ok' : 'mdg-no' }}">{{ $u->mdg_pg_ok   ? '✅' : '❌' }} PG</span>
            </span>
        </div>
    </div>

    {{-- USULAN --}}
    <div class="detail-card">
        <div class="detail-card-title">🏆 Data Usulan Promosi</div>
        <div class="detail-row">
            <span class="detail-label">Jabatan Tujuan</span>
            <span class="detail-value">{{ $u->jabatan_tujuan }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">JG Promosi</span>
            <span class="detail-value">{{ $u->job_grade_promosi ? 'JG '.$u->job_grade_promosi : '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">PG Promosi</span>
            <span class="detail-value">{{ $u->person_grade_promosi ? 'PG '.$u->person_grade_promosi : '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tanggal Usulan</span>
            <span class="detail-value">{{ $u->tanggal_usulan ? $u->tanggal_usulan->format('d M Y') : '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tindak Lanjut</span>
            <span class="detail-value">{{ $u->tindak_lanjut ? ucfirst($u->tindak_lanjut) : '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tanggal Sidang</span>
            <span class="detail-value">{{ $u->tanggal_sidang ? $u->tanggal_sidang->format('d M Y') : '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Hasil Sidang</span>
            <span class="detail-value">{{ $u->hasil_sidang ? ucwords(str_replace('_',' ',$u->hasil_sidang)) : '-' }}</span>
        </div>
    </div>
</div>

<div class="grid-2">
    {{-- ASSESSMENT --}}
    <div class="detail-card">
        <div class="detail-card-title">📋 Assessment Rekomendasi</div>
        @if($u->assessment)
        <div class="detail-row">
            <span class="detail-label">Tanggal Pelaksanaan</span>
            <span class="detail-value">{{ $u->assessment->tanggal_pelaksanaan->format('d M Y') }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Hasil Assessment</span>
            <span class="detail-value">{{ $u->hasil_assessment_label }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Berlaku s/d</span>
            <span class="detail-value">{{ $u->tanggal_berlaku_assessment ? $u->tanggal_berlaku_assessment->format('d M Y') : '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Level Ukur</span>
            <span class="detail-value">{{ $u->level_ukur ?? '-' }}</span>
        </div>
    </div>

    {{-- TALENT POOL --}}
    <div class="detail-card">
        <div class="detail-card-title">🎯 Talent Pool</div>
        <div class="detail-row">
            <span class="detail-label">Periode</span>
            <span class="detail-value">{{ $u->talent_pool_periode ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Klasifikasi</span>
            <span class="detail-value">
                {{-- FIX Ln 260: Blade {{ }} di style diganti class statis --}}
                @if($u->talent_pool_klasifikasi)
                    <span class="badge badge-{{ $u->talent_pool_klasifikasi }}">
                        {{ ucfirst($u->talent_pool_klasifikasi) }}
                    </span>
                @else
                    -
                @endif
            </span>
        </div>
    </div>
</div>

{{-- KPI & KALIBRASI --}}
<div class="grid-2">
    <div class="detail-card">
        <div class="detail-card-title">📊 KPI 3 Tahun Terakhir</div>
        @if($u->kpi_snapshot && count($u->kpi_snapshot))
            @foreach($u->kpi_snapshot as $kpi)
            <div class="detail-row">
                <span class="detail-label">{{ $kpi['tahun'] }} · {{ $kpi['periode'] }}</span>
                <span class="detail-value" style="font-size:15px;font-weight:800">{{ number_format($kpi['nilai'], 2, ',', '.') }}</span>
            </div>
            @endforeach
        @else
            <div style="text-align:center;padding:16px;color:#9ca3af;font-size:13px">Tidak ada data KPI</div>
        @endif
    </div>

    <div class="detail-card">
        <div class="detail-card-title">🎯 Kalibrasi 2 Tahun Terakhir</div>
        @if($u->kalibrasi_snapshot && count($u->kalibrasi_snapshot))
            @php
                $kalibrasiColor = ['FEE'=>['#15803d','#dcfce7'],'EXE'=>['#1d4ed8','#dbeafe'],'PEE'=>['#0e7490','#ecfeff'],'MEE'=>['#374151','#f3f4f6'],'ME'=>['#4b5563','#f3f4f6'],'SME'=>['#ca8a04','#fef9c3'],'PME'=>['#ea580c','#ffedd5'],'BEE'=>['#d97706','#fef3c7'],'NME'=>['#b91c1c','#fee2e2'],'FBE'=>['#dc2626','#fee2e2']];
            @endphp
            @foreach($u->kalibrasi_snapshot as $k)
            @php $kc = $kalibrasiColor[$k['nilai']] ?? ['#374151','#f3f4f6']; @endphp
            {{-- FIX Ln 297: Blade {{ }} di style dipindah ke data-* lalu diapply JS --}}
            <div class="detail-row">
                <span class="detail-label">{{ $k['tahun'] }}</span>
                <span class="badge" data-bg="{{ $kc[1] }}" data-color="{{ $kc[0] }}">{{ $k['nilai'] }}</span>
            </div>
            @endforeach
        @else
            <div style="text-align:center;padding:16px;color:#9ca3af;font-size:13px">Tidak ada data kalibrasi</div>
        @endif
    </div>
</div>

{{-- PENILAIAN LAINNYA --}}
<div class="detail-card">
    <div class="detail-card-title">📝 Penilaian & Evaluasi</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
        <div class="detail-row">
            <span class="detail-label">Absensi</span>
            <span class="detail-value">{{ $u->absensi ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Kehadiran</span>
            <span class="detail-value">{{ $u->kehadiran ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Periode Penilaian</span>
            <span class="detail-value">{{ $u->periode_penilaian ?? '-' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Tata Kelola</span>
            <span class="detail-value">{{ $u->tata_kelola ?? '-' }}</span>
        </div>
        <div class="detail-row" style="grid-column:1/-1">
            <span class="detail-label">MC (Man Count)</span>
            <span class="detail-value">
                <span class="badge {{ $u->mc_tersedia ? 'mdg-ok' : 'mdg-no' }}">{{ $u->mc_tersedia ? '✅ Tersedia' : '❌ Tidak Tersedia' }}</span>
            </span>
        </div>
        @if($u->hasil_evaluasi)
        <div class="detail-row" style="grid-column:1/-1;flex-direction:column;align-items:flex-start;gap:6px">
            <span class="detail-label">Hasil Evaluasi</span>
            <span style="font-size:13px;color:#374151">{{ $u->hasil_evaluasi }}</span>
        </div>
        @endif
        @if($u->catatan)
        <div class="detail-row" style="grid-column:1/-1;flex-direction:column;align-items:flex-start;gap:6px">
            <span class="detail-label">Catatan</span>
            <span style="font-size:13px;color:#6b7280;font-style:italic">{{ $u->catatan }}</span>
        </div>
        @endif
    </div>
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

    // Apply warna dari data-* attribute ke style property
    document.querySelectorAll('[data-bg]').forEach(el => {
        if (el.dataset.bg)    el.style.background = el.dataset.bg;
        if (el.dataset.color) el.style.color = el.dataset.color;
    });
});
</script>
@endpush