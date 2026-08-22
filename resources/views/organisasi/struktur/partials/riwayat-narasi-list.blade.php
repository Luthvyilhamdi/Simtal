@php
    // Sumber data: GenealogyBandLayout::layout() — PERSIS sama dgn yg dipakai Tab Diagram
    // (lihat unit-timeline-diagram.blade.php) & endpoint overlay riwayat, cuma beda cara
    // render (list vs kartu vs graph). Band sudah urut TERBARU -> TERLAMA dari layer itu.
    //
    // SATU-SATUNYA consumer partial ini SEKARANG adalah endpoint overlay "Riwayat Unit"
    // (unitRiwayatOverlay()) — duplikatnya, riwayat-narasi-list-tab.blade.php, dipakai
    // Tab List Timeline 1 Unit. Dulu partial ini SENGAJA dibiarkan pakai $line['html']
    // polos (tanpa prefix level) krn overlay belum masuk scope fitur prefix; keputusan itu
    // DIBALIK — overlay sekarang JUGA pakai prefix, jadi partial ini di-switch ke baca
    // $line['leveled']['html'] (persis spt duplikatnya). Krn cuma 1 consumer yg tersisa &
    // keduanya skrg identik perilakunya, TIDAK perlu duplikasi lagi — cukup partial ini yg
    // diubah langsung (bukan bikin -tab lagi/parameter on-off).
    $bands = $graph['bands'] ?? [];
@endphp

@push('styles')
<style>
    .riwayat-narasi-empty { text-align:center;color:#9ca3af;padding:40px 20px;font-size:13px; }
    .riwayat-narasi-wrap { position:relative;padding-left:22px; }
    .riwayat-narasi-wrap::before { content:'';position:absolute;left:5px;top:6px;bottom:6px;width:2px;background:#e5e7eb; }
    .riwayat-narasi-item { position:relative;margin-bottom:18px; }
    .riwayat-narasi-item:last-child { margin-bottom:0; }
    .riwayat-narasi-dot { position:absolute;left:-22px;top:4px;width:12px;height:12px;border-radius:50%;background:#15803d;border:3px solid #dcfce7; }
    .riwayat-narasi-card { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:14px 16px; }
    .riwayat-narasi-versi { font-size:11px;color:#9ca3af;margin-bottom:6px; }
    .riwayat-narasi-versi a { color:inherit;text-decoration:underline; }
    .riwayat-narasi-badge { font-weight:700;color:#15803d;margin-left:4px; }
    .riwayat-narasi-line { font-size:13px;color:#374151;line-height:1.5;margin-bottom:4px; }
    .riwayat-narasi-line:last-child { margin-bottom:0; }
    .riwayat-narasi-line strong { color:#111827; }
    .riwayat-narasi-ket { font-size:11px;color:#9ca3af;font-style:italic;margin-top:2px; }
</style>
@endpush

@if(empty($bands))
<div class="riwayat-narasi-empty">Tidak ada data riwayat untuk unit ini.</div>
@else
<div class="riwayat-narasi-wrap">
    @foreach($bands as $band)
    <div class="riwayat-narasi-item">
        <div class="riwayat-narasi-dot"></div>
        <div class="riwayat-narasi-card">
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
            <div class="riwayat-narasi-line">{!! $line['leveled']['html'] !!}</div>
            @if($line['keterangan'])
            <div class="riwayat-narasi-ket">Catatan: {{ $line['keterangan'] }}</div>
            @endif
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endif
