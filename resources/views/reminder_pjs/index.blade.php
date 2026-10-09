@extends('layouts.app')
@section('title', 'Reminder PJS')
@section('breadcrumb-parent', 'Manajemen Talenta')
@section('breadcrumb', 'Reminder PJS')

@php
    use App\Services\ReminderPjsService;

    $keadaanLabel = [
        ReminderPjsService::SIAP             => 'Siap diangkat',
        ReminderPjsService::MENUNGGU_MDG     => 'Menunggu MDG',
        ReminderPjsService::PERLU_ASSESSMENT => 'Perlu assessment',
    ];
    $rekomendasiLabel = [
        'ready'                  => 'Ready',
        'ready_with_development' => 'Ready with Development',
        'not_ready'              => 'Not Ready',
    ];
    $bulanKe = fn ($n) => $n === null ? '-' : ($n < 1 ? 'kurang dari sebulan' : $n . ' bulan');
@endphp

@push('styles')
<style>
    .page-header { margin-bottom:20px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }


    .summary-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px; }
    .sum-card { background:white;border-radius:14px;border:1px solid var(--card-border);padding:18px 20px;box-shadow:var(--card-shadow);display:flex;align-items:center;gap:14px; }
    .sum-ico { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .sum-ico svg { width:22px;height:22px;fill:none;stroke-width:1.9; }
    .sum-ico.green { background:#dcfce7; } .sum-ico.green svg { stroke:#15803d; }
    .sum-ico.amber { background:#fef3c7; } .sum-ico.amber svg { stroke:#b45309; }
    .sum-ico.blue  { background:#dbeafe; } .sum-ico.blue svg  { stroke:#1d4ed8; }
    .sum-ico.gray  { background:#f3f4f6; } .sum-ico.gray svg  { stroke:#6b7280; }
    .sum-num { font-size:26px;font-weight:800;color:#111827;line-height:1; }
    .sum-label { font-size:12px;color:#6b7280;margin-top:3px;font-weight:500; }

    .toolbar { background:white;border-radius:12px;border:1px solid var(--card-border);padding:12px 16px;margin-bottom:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;box-shadow:var(--card-shadow); }
    .toolbar select, .toolbar .ss-trigger { border:1px solid #e4e7ec;border-radius:9px;padding:8px 12px;font-size:13px;outline:none;background:#fcfcfd;color:#374151;cursor:pointer; }
    .search-mini { position:relative;display:flex;align-items:center;flex:1;min-width:210px;max-width:320px; }
    .search-mini svg { position:absolute;left:11px;width:15px;height:15px;stroke:#9ca3af;fill:none; }
    .search-mini input { width:100%;border:1px solid #e4e7ec;border-radius:9px;padding:8px 30px 8px 32px;font-size:13px;outline:none;background:#fcfcfd; }
    .search-mini .clear-btn { position:absolute;right:8px;border:none;background:none;color:#9ca3af;font-size:16px;cursor:pointer;display:none;line-height:1; }
    .search-mini .clear-btn.visible { display:block; }

    /* Satu kartu per pemegang PJS */
    .pcard { background:white;border:1px solid var(--card-border);border-radius:14px;box-shadow:var(--card-shadow);
             padding:16px 18px;margin-bottom:12px;display:flex;gap:14px;align-items:flex-start; }
    .pcard-ava { width:40px;height:40px;border-radius:50%;background:#ede9fe;color:#6d28d9;display:flex;
                 align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0; }
    .pcard-isi { flex:1;min-width:0; }
    .pcard-baris1 { display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
    .pcard-nama { font-size:14.5px;font-weight:700;color:#111827; }
    .pcard-nama a { color:inherit;text-decoration:none; }
    .pcard-nama a:hover { text-decoration:underline;text-underline-offset:2px; }
    .pcard-meta { font-size:11.5px;color:#9ca3af;font-weight:600; }
    .pcard-pangku { font-size:12.5px;color:#4b5563;margin-top:6px;line-height:1.45; }
    .pcard-pangku b { color:#111827;font-weight:600; }
    .pcard-sejak { font-size:11.5px;color:#9ca3af;margin-top:2px; }

    /* Dua syarat ditampilkan berdampingan, lulus/belum terbaca dari warnanya */
    .syarat { display:flex;gap:9px;flex-wrap:wrap;margin-top:11px; }
    .sy { display:inline-flex;align-items:center;gap:7px;padding:6px 12px;border-radius:10px;font-size:12px;font-weight:600;border:1px solid transparent; }
    .sy svg { width:13px;height:13px;fill:none;stroke-width:2.6;flex-shrink:0; }
    .sy.ok    { background:#f0fdf4;color:#15803d;border-color:#bbf7d0; } .sy.ok svg    { stroke:#15803d; }
    .sy.belum { background:#fff7ed;color:#c2410c;border-color:#fed7aa; } .sy.belum svg { stroke:#c2410c; }
    .sy .sy-sub { font-weight:500;opacity:.8; }

    .lencana { padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap; }
    .lencana.siap  { background:#dcfce7;color:#15803d; }
    .lencana.mdg   { background:#fef3c7;color:#b45309; }
    .lencana.asmt  { background:#dbeafe;color:#1d4ed8; }
    .lencana.lama  { background:#fee2e2;color:#dc2626; }

    .rp-kosong { background:white;border:1px solid var(--card-border);border-radius:14px;padding:46px 20px;text-align:center;box-shadow:var(--card-shadow); }
    .rp-kosong-ico { width:52px;height:52px;border-radius:14px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin:0 auto 14px; }
    .rp-kosong-ico svg { width:26px;height:26px;stroke:#9ca3af;fill:none;stroke-width:1.7; }
    .rp-kosong-judul { font-size:14px;font-weight:600;color:#374151; }
    .rp-kosong-sub { font-size:12.5px;color:#9ca3af;margin-top:5px; }

    @media (max-width:980px) { .summary-grid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:560px) { .summary-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-title">Reminder PJS</div>
</div>

{{-- RINGKASAN --}}
<div class="summary-grid">
    <div class="sum-card">
        <div class="sum-ico green"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div>
            <div class="sum-num">{{ $siap }}</div>
            <div class="sum-label">Siap diangkat tetap</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-ico amber"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div>
            <div class="sum-num">{{ $menungguMdg }}</div>
            <div class="sum-label">Menunggu MDG</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-ico blue"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg></div>
        <div>
            <div class="sum-num">{{ $perluAssessment }}</div>
            <div class="sum-label">Perlu assessment</div>
        </div>
    </div>
    <div class="sum-card">
        <div class="sum-ico gray"><svg viewBox="0 0 24 24"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div>
            <div class="sum-num">{{ $lama }}</div>
            <div class="sum-label">Memangku &gt; {{ $batasLama }} bulan</div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="toolbar">
    <div class="search-mini">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="cariPjs" placeholder="Cari nama / NIK..." autocomplete="off">
        <button type="button" class="clear-btn" id="hapusCari" onclick="bersihkanCariPjs()">×</button>
    </div>
    <x-filter-drawer terapkan="terapkanFilterPjs()"
                     :aktif="(int) (bool) $direktoratFilter + (int) (bool) $keadaanFilter"
                     :reset="route('reminder_pjs.index')">
        <div class="fd-field">
            <span class="fd-label">Direktorat</span>
            <select id="filterDir" class="select-search" aria-label="Direktorat">
                <option value="">Semua Direktorat</option>
                @foreach($direktorats as $d)
                    <option value="{{ $d->nama_direktorat }}" {{ $direktoratFilter === $d->nama_direktorat ? 'selected' : '' }}>{{ $d->nama_direktorat }}</option>
                @endforeach
            </select>
        </div>
        <div class="fd-field">
            <span class="fd-label">Keadaan</span>
            <select id="filterKeadaan" class="select-search" aria-label="Keadaan">
                <option value="">Semua Keadaan</option>
                @foreach($keadaanLabel as $nilai => $label)
                    <option value="{{ $nilai }}" {{ $keadaanFilter === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-drawer>
    <span style="margin-left:auto;font-size:12px;color:#9ca3af" id="jumlahHasil">{{ count($items) }} hasil</span>
</div>

{{-- DAFTAR --}}
<div id="daftarPjs">
@forelse($items as $i)
    @php
        $k   = $i['karyawan'];
        $lnc = [
            ReminderPjsService::SIAP             => 'siap',
            ReminderPjsService::MENUNGGU_MDG     => 'mdg',
            ReminderPjsService::PERLU_ASSESSMENT => 'asmt',
        ][$i['keadaan']];
    @endphp
    <div class="pcard" data-cari="{{ strtolower($k->nama . ' ' . $k->nik) }}">
        <div class="pcard-ava">{{ initials($k->nama) }}</div>
        <div class="pcard-isi">
            <div class="pcard-baris1">
                <span class="pcard-nama"><a href="{{ route('karyawan.show', $k) }}">{{ $k->nama }}</a></span>
                <span class="pcard-meta">NIK {{ $k->nik }} · {{ $k->band }} · JG {{ $k->jobGrade->job_grade ?? '-' }}</span>
                <span style="margin-left:auto;display:flex;gap:7px;flex-wrap:wrap">
                    @if($i['terlalu_lama'])
                        <span class="lencana lama">{{ $i['lama_bulan'] }} bulan</span>
                    @endif
                    <span class="lencana {{ $lnc }}">{{ $keadaanLabel[$i['keadaan']] }}</span>
                </span>
            </div>

            <div class="pcard-pangku">Memangku <b>{{ $i['posisi_pjs'] ?: '-' }}</b></div>
            <div class="pcard-sejak">
                @if($i['pjs']->tanggal_mulai)
                    Sejak {{ \Carbon\Carbon::parse($i['pjs']->tanggal_mulai)->translatedFormat('d M Y') }} · {{ $bulanKe($i['lama_bulan']) }}
                @else
                    Tanggal mulai belum diisi
                @endif
                @if($i['pjs']->no_sk) · SK {{ $i['pjs']->no_sk }} @endif
            </div>

            <div class="syarat">
                {{-- Syarat 1: MDG --}}
                <span class="sy {{ $i['mdg_ok'] ? 'ok' : 'belum' }}">
                    @if($i['mdg_ok'])
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        MDG terpenuhi
                    @else
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        MDG <span class="sy-sub">kurang {{ $i['sisa_mdg'] }} bulan</span>
                    @endif
                </span>

                {{-- Syarat 2: assessment — dua sumber, salah satu memenuhi sudah cukup --}}
                <span class="sy {{ $i['assessment_ok'] ? 'ok' : 'belum' }}">
                    @if($i['assessment_sumber'])
                        @if($i['assessment_ok'])
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @endif
                        @if($i['assessment_sumber'] === 'kompetensi')
                            {{ ucwords(strtolower($i['kompetensi']->kesimpulan)) }}
                            <span class="sy-sub">· kompetensi · {{ $bulanKe($i['assessment_umur']) }} lalu</span>
                        @else
                            {{ $rekomendasiLabel[$i['rekomendasi']->rekomendasi_final] ?? $i['rekomendasi']->rekomendasi_final }}
                            <span class="sy-sub">· rekomendasi · {{ $bulanKe($i['assessment_umur']) }} lalu</span>
                        @endif
                    @else
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Belum pernah assessment
                    @endif
                </span>
            </div>
        </div>
    </div>
@empty
    <div class="rp-kosong">
        <div class="rp-kosong-ico"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
        <div class="rp-kosong-judul">Tidak ada PJS yang cocok</div>
        <div class="rp-kosong-sub">Coba ubah filternya, atau memang tidak ada PJS aktif saat ini.</div>
    </div>
@endforelse
</div>

@endsection

@push('scripts')
<script>
// Pencarian nama/NIK dilakukan di sisi klien: daftarnya pendek (hanya PJS
// yang sedang berjalan), jadi tidak perlu bolak-balik ke server.
(function () {
    const input  = document.getElementById('cariPjs');
    const hapus  = document.getElementById('hapusCari');
    const jumlah = document.getElementById('jumlahHasil');
    const kartu  = Array.from(document.querySelectorAll('#daftarPjs .pcard'));

    function saring() {
        const q = input.value.trim().toLowerCase();
        hapus.classList.toggle('visible', q.length > 0);
        let tampil = 0;
        kartu.forEach(function (el) {
            const cocok = !q || el.dataset.cari.includes(q);
            el.style.display = cocok ? '' : 'none';
            if (cocok) tampil++;
        });
        jumlah.textContent = tampil + ' hasil';
    }

    input.addEventListener('input', saring);
    window.bersihkanCariPjs = function () { input.value = ''; saring(); input.focus(); };
})();

function terapkanFilterPjs() {
    const url = new URL(window.location.href);
    const dir = document.getElementById('filterDir').value;
    const kea = document.getElementById('filterKeadaan').value;
    dir ? url.searchParams.set('direktorat', dir) : url.searchParams.delete('direktorat');
    kea ? url.searchParams.set('keadaan', kea)    : url.searchParams.delete('keadaan');
    window.location.href = url.toString();
}
</script>
@endpush
