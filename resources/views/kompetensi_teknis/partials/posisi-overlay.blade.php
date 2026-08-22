{{--
    Fragment HTML MURNI (TANPA <style>/@push) — hasil fetch() dari openPosisiOverlay(),
    di-inject via innerHTML ke #komtekOverlayBody. REUSE class CSS yg sama persis dgn
    partials/unit-overlay.blade.php (.komtek-jenjang-card, .komtek-item, .komtek-badge-tipe,
    dst — semua sudah dideklarasikan di overlay-shell.blade.php), krn diminta gaya visual
    SAMA PERSIS dgn overlay unit yg sudah ada, cuma isinya di-filter ke 1 jenjang/posisi.
--}}
<div class="komtek-versi-info">
    SK {{ $versi->nomor_sk }} &middot; berlaku {{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
    @if($snapshot)
        &middot; {{ formatUnitLabel($snapshot->nama_unit, $snapshot->level) }}
    @endif
</div>

@if(is_null($posisi))
    <div class="komtek-overlay-empty">Data posisi ini tidak ditemukan (mungkin sudah berubah).</div>
@else
    <div class="komtek-jenjang-card">
        <div class="komtek-jenjang-meta">
            @if($posisi['grade'])
                <span class="komtek-grade-badge">Grade {{ $posisi['grade'] }}</span>
            @endif
            <span>{{ $posisi['nama_jobs'] }}</span>
            <span class="komtek-managerial-badge {{ $posisi['managerial'] ? 'ya' : 'tidak' }}">
                {{ $posisi['managerial'] ? 'Managerial' : 'Non-Managerial' }}
            </span>
        </div>
    </div>

    @forelse($items as $item)
    <div class="komtek-item">
        <span class="nama">{{ $item->kompetensiTeknis->nama_kompetensi }}</span>
        <span class="level-text">Level {{ $item->level }}</span>
        <span class="komtek-badge-tipe {{ $item->prioritas_badge_class }}">{{ $item->prioritas_label }}</span>
    </div>
    @empty
        <div class="komtek-overlay-empty">Belum ada kompetensi teknis untuk posisi ini.</div>
    @endforelse
@endif
