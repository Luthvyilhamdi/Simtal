{{--
    Fragment HTML MURNI (TANPA <style>/@push) — hasil fetch() dari openKomtekOverlay(),
    di-inject via innerHTML ke #komtekOverlayBody (lihat kompetensi_teknis/partials/
    overlay-shell.blade.php utk CSS & alasannya). Data dikelompokkan per jenjang_jabatan,
    urutan grup ASC berdasar urutan_jenjang (dihitung di KompetensiTeknisController::
    unitOverlay(), bukan di sini).
--}}
<div class="komtek-versi-info">
    SK {{ $versi->nomor_sk }} &middot; berlaku {{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
    @if($snapshot)
        &middot; {{ ucfirst($snapshot->level) }}
    @endif
</div>

@if(empty($groups))
    <div class="komtek-overlay-empty">Belum ada data kompetensi teknis untuk unit ini.</div>
@else
    @foreach($groups as $group)
    <div class="komtek-jenjang-group">
        <div class="komtek-jenjang-card">
            <div class="komtek-jenjang-title">{{ $group['jenjang_jabatan'] }}</div>
            <div class="komtek-jenjang-meta">
                @if($group['grade'])
                    <span class="komtek-grade-badge">Grade {{ $group['grade'] }}</span>
                @endif
                <span>{{ $group['nama_jobs'] }}</span>
                <span class="komtek-managerial-badge {{ $group['managerial'] ? 'ya' : 'tidak' }}">
                    {{ $group['managerial'] ? 'Managerial' : 'Non-Managerial' }}
                </span>
            </div>
        </div>

        @foreach($group['items'] as $item)
        <div class="komtek-item">
            <span class="nama">{{ $item->kompetensiTeknis->nama_kompetensi }}</span>
            <span class="level-text">Level {{ $item->level }}</span>
            <span class="komtek-badge-tipe {{ $item->prioritas_badge_class }}">{{ $item->prioritas_label }}</span>
        </div>
        @endforeach
    </div>
    @endforeach
@endif
