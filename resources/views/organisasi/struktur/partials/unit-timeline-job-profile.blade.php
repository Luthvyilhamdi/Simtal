{{--
    Tab "Job Profile" di halaman Timeline 1 Unit — histori Job Profile unit ini lintas
    SEMUA versi final di mana unit ini eksis (sama persis sumber $snaps yg dipakai tab
    Diagram/List, lihat unitTimeline()). Urutan kronologis tertua -> terbaru, konsisten
    dgn tab List (partials/riwayat-narasi-list-tab.blade.php).

    Versi di mana unit BELUM LAHIR/SUDAH BUBAR tidak muncul sama sekali (bukan bagian
    $jobProfileTimeline). Versi di mana unit eksis tapi belum ada Job Profile TETAP
    muncul, ditandai netral (abu-abu, bukan warning) krn ini kondisi data wajar.
--}}
@push('styles')
<style>
    .jp-timeline-empty { text-align:center;color:#9ca3af;padding:40px 20px;font-size:13px; }

    .jp-timeline-wrap { position:relative;padding-left:22px; }
    .jp-timeline-wrap::before { content:'';position:absolute;left:5px;top:6px;bottom:6px;width:2px;background:#e5e7eb; }
    .jp-timeline-item { position:relative;margin-bottom:18px; }
    .jp-timeline-item:last-child { margin-bottom:0; }
    .jp-timeline-dot { position:absolute;left:-22px;top:4px;width:12px;height:12px;border-radius:50%;border:3px solid; }
    .jp-timeline-dot.has { background:#dcfce7;border-color:#16a34a; }
    .jp-timeline-dot.none { background:#f3f4f6;border-color:#d1d5db; }
    .jp-timeline-card { background:white;border-radius:0 12px 12px 0;border:1px solid var(--card-border);border-left:3px solid;box-shadow:var(--card-shadow);padding:14px 16px; }
    .jp-timeline-card.has { border-left-color:#16a34a; }
    .jp-timeline-card.none { border-left-color:#d1d5db; }
    .jp-timeline-versi { font-size:11px;color:#9ca3af;margin-bottom:10px; }
    .jp-timeline-versi a { color:inherit;text-decoration:underline; }

    .jp-profile-item { display:flex;align-items:center;gap:10px;padding:9px 12px;background:#fafaf8;border:1px solid #f3f4f6;border-radius:8px;margin-bottom:8px;font-size:12.5px; }
    .jp-profile-item:last-child { margin-bottom:0; }
    .jp-profile-item .jabatan { font-weight:700;color:#111827;min-width:140px; }
    .jp-profile-item a.file-link { color:#1d4ed8;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .jp-profile-item a.file-link:hover { text-decoration:underline; }
    .jp-profile-item a.file-link svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0; }

    .jp-empty-note { font-size:12.5px;color:#9ca3af;font-style:italic; }
</style>
@endpush

@if($jobProfileTimeline->isEmpty())
<div class="jp-timeline-empty">Tidak ada data untuk unit ini.</div>
@else
<div class="jp-timeline-wrap">
    @foreach($jobProfileTimeline as $entry)
    @php
        $versi = $entry['versi'];
        $profiles = $entry['profiles'];
        $hasProfiles = $profiles->isNotEmpty();
    @endphp
    <div class="jp-timeline-item">
        <div class="jp-timeline-dot {{ $hasProfiles ? 'has' : 'none' }}"></div>
        <div class="jp-timeline-card {{ $hasProfiles ? 'has' : 'none' }}">
            <div class="jp-timeline-versi">
                @if($versi)
                    <a href="{{ route('organisasi.struktur.show', $versi) }}">SK {{ $versi->nomor_sk }}</a>
                    &middot; {{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}
                @else
                    Versi tidak ditemukan
                @endif
            </div>

            @if($hasProfiles)
                @foreach($profiles as $profile)
                <div class="jp-profile-item">
                    <span class="jabatan">{{ $profile->nama_jabatan }}</span>
                    <a href="{{ asset('storage/' . $profile->file_path) }}" target="_blank" rel="noopener" class="file-link">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        {{ $profile->file_original_name }}
                    </a>
                </div>
                @endforeach
            @else
                <div class="jp-empty-note">Belum ada Job Profile di versi ini.</div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif
