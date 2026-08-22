{{--
    Duplikat dari partials/riwayat-narasi-list.blade.php, KHUSUS dipakai tab "List"
    halaman Timeline 1 Unit (unit-timeline.blade.php) — bedanya SEKARANG dua hal:
    1. Baris narasi dibaca dari $line['leveled']['html'] (nama unit diberi prefix level,
       via formatUnitLabel()) alih-alih $line['html'] biasa.
    2. Redesign kartu jadi timeline spine + accent warna kategori transisi (8-skema
       PERSIS sama dgn Compare/Fitur B, lihat transisiCategoryColor() di helpers.php)
       + hierarki tipografi (metadata -> nama unit -> narasi -> keterangan).

    SENGAJA diduplikasi, BUKAN modifikasi partial aslinya — partial asli itu juga dipakai
    endpoint AJAX overlay "Riwayat Unit" (Tree View, Detail Versi, Compare) yg TIDAK ikut
    redesign ini (overlay tetap versi lama/polos). $line['category']/$line['headline']
    berasal dari GenealogyBandLayout (field tambahan, additive — overlay & Tab Diagram
    lama tidak membacanya jadi tidak terdampak).
--}}
@php
    $bands = $graph['bands'] ?? [];
    // Urutan SAMA PERSIS dgn yg disepakati: Baseline/Lanjut, Rename, Pindah Induk,
    // Ganti Level, Pecah, Gabung, Bubar, Baru. null = kategori Baseline/Lanjut.
    $legendCategories = [null, 'rename', 'pindah_induk', 'ganti_level', 'pecah', 'gabung', 'bubar', 'baru'];
@endphp

@push('styles')
<style>
    .riwayat-narasi-empty { text-align:center;color:#9ca3af;padding:40px 20px;font-size:13px; }

    .riwayat-legend { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px; }
    .riwayat-legend-chip { display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:20px;font-size:11.5px;font-weight:600;white-space:nowrap; }
    .riwayat-legend-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }

    .riwayat-narasi-wrap { position:relative;padding-left:22px; }
    .riwayat-narasi-wrap::before { content:'';position:absolute;left:5px;top:6px;bottom:6px;width:2px;background:#e5e7eb; }
    .riwayat-narasi-item { position:relative;margin-bottom:18px; }
    .riwayat-narasi-item:last-child { margin-bottom:0; }
    .riwayat-narasi-dot { position:absolute;left:-22px;top:4px;width:12px;height:12px;border-radius:50%;border:3px solid; }
    {{-- border-radius 0 di sisi kiri (kotak, bukan bulat) krn accent border-left 3px
         nempel langsung di tepi kartu — radius normal 3 sisi lain spy tetap terasa
         "kartu" bukan strip lurus. --}}
    .riwayat-narasi-card { background:white;border-radius:0 12px 12px 0;border:1px solid var(--card-border);border-left:3px solid;box-shadow:var(--card-shadow);padding:14px 16px; }
    .riwayat-narasi-versi { font-size:11px;color:#9ca3af;margin-bottom:8px; }
    .riwayat-narasi-versi a { color:inherit;text-decoration:underline; }
    .riwayat-narasi-badge { font-weight:700;color:#15803d;margin-left:4px; }
    {{-- 1 "block" = 1 baris narasi (headline + kalimat + keterangan) — biasanya cuma 1
         per kartu, tapi bisa >1 kalau 1 unit punya beberapa perubahan sekaligus di versi
         yg sama (mis. rename + pindah_induk bareng). --}}
    .riwayat-narasi-block { margin-bottom:12px; }
    .riwayat-narasi-block:last-child { margin-bottom:0; }
    .riwayat-narasi-headline { font-size:14.5px;font-weight:700;color:#111827;margin-bottom:3px; }
    .riwayat-narasi-line { font-size:13px;color:#374151;line-height:1.5; }
    .riwayat-narasi-line strong { color:#111827; }
    .riwayat-narasi-ket { font-size:11px;color:#9ca3af;font-style:italic;margin-top:3px; }
</style>
@endpush

@if(empty($bands))
<div class="riwayat-narasi-empty">Tidak ada data riwayat untuk unit ini.</div>
@else
<div class="riwayat-legend">
    @foreach($legendCategories as $cat)
    @php $c = transisiCategoryColor($cat); @endphp
    <span class="riwayat-legend-chip" style="background:{{ $c['fill'] }};color:{{ $c['text'] }};">
        <span class="riwayat-legend-dot" style="background:{{ $c['border'] }};"></span>
        {{ transisiCategoryLabel($cat) }}
    </span>
    @endforeach
</div>

<div class="riwayat-narasi-wrap">
    @foreach($bands as $band)
    @php
        // Dot & accent kartu ikut kategori baris PERTAMA di band ini (band multi-baris
        // sangat jarang & biasanya masih 1 unit yg sama, jadi representatif) — reuse
        // token warna yg sama dgn legend di atas (transisiCategoryColor()).
        $bandColor = transisiCategoryColor($band['lines'][0]['category'] ?? null);
    @endphp
    <div class="riwayat-narasi-item">
        <div class="riwayat-narasi-dot" style="background:{{ $bandColor['border'] }};border-color:{{ $bandColor['fill'] }};"></div>
        <div class="riwayat-narasi-card" style="border-left-color:{{ $bandColor['border'] }};">
            <div class="riwayat-narasi-versi">
                @if(!empty($band['struktur_organisasi_versi_id']))
                    <a href="{{ route('organisasi.struktur.show', $band['struktur_organisasi_versi_id']) }}">SK {{ $band['nomor_sk'] ?? '-' }}</a>
                @else
                    SK {{ $band['nomor_sk'] ?? '-' }}
                @endif
                &middot; {{ $band['tanggal'] ? $band['tanggal']->translatedFormat('d F Y') : '-' }}
                @if($band['badge'])
                <span class="riwayat-narasi-badge">({{ $band['badge'] }})</span>
                @endif
            </div>
            @foreach($band['lines'] as $line)
            <div class="riwayat-narasi-block">
                @if($line['headline'])
                <div class="riwayat-narasi-headline">{{ $line['headline'] }}</div>
                @endif
                <div class="riwayat-narasi-line">{!! $line['leveled']['html'] !!}</div>
                @if($line['keterangan'])
                <div class="riwayat-narasi-ket">Catatan: {{ $line['keterangan'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endif
