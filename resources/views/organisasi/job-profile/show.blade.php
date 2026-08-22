@extends('layouts.app')
@section('title', 'Job Profile — ' . $versi->nomor_sk)
@section('breadcrumb-parent', 'Job Profile')
@section('breadcrumb', $versi->nomor_sk)

{{--
    TIDAK ADA collapse/expand di halaman ini SAMA SEKALI — semua unit & isi Job
    Profile-nya langsung tampil terbuka dari render awal, tanpa interaksi klik
    apa pun.

    Search + filter level SENGAJA VANILLA JS MURNI (BUKAN Alpine) — setelah 4x
    percobaan berbagai pendekatan Alpine (x-show+$el.dataset, nested x-data per
    card) dilaporkan gagal jalan di browser meski verifikasi server-side selalu
    "benar", diputuskan pindah ke DOM API standar (querySelectorAll + data-*
    attribute + inline style.display) yg tidak bergantung pada reaktivitas
    framework apa pun — lebih gampang dipastikan & di-debug lewat DevTools
    Console langsung. Tidak ada x-data/x-show/x-model di halaman ini SAMA SEKALI.
--}}

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .btn-kelola { display:inline-flex;align-items:center;gap:6px;background:#15803d;color:white;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap; }
    .btn-kelola:hover { background:#166534; }
    .btn-kelola svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2; }

    .progress-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:18px 22px;margin-bottom:16px; }
    .progress-text { font-size:13px;font-weight:600;color:#374151;margin-bottom:8px; }
    .progress-text .num { color:#15803d;font-weight:800;font-size:15px; }
    .progress-bar-track { height:8px;border-radius:5px;background:#f3f4f6;overflow:hidden; }
    .progress-bar-fill { height:100%;border-radius:5px;background:#16a34a;transition:width .2s; }
    .progress-bar-fill.empty { background:#e5e7eb; }

    .search-box { display:flex;align-items:center;gap:8px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:11px 16px;margin-bottom:12px; }
    .search-box:focus-within { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .search-box svg { width:15px;height:15px;stroke:#9ca3af;fill:none;flex-shrink:0; }
    .search-box input { border:none;outline:none;font-size:13px;font-family:inherit;color:#111827;background:transparent;width:100%; }
    .search-box input::placeholder { color:#9ca3af; }

    .filter-row { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px; }
    .filter-select { flex:1;min-width:180px;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:12.5px;font-family:inherit;color:#111827;background:white; }
    .filter-select:focus { border-color:#16a34a;outline:none; }
    .result-count { font-size:11.5px;color:#6b7280;margin-bottom:10px; }

    .unit-list { display:flex;flex-direction:column;gap:8px; }
    .unit-card { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:hidden; }
    .unit-row { display:flex;align-items:center;gap:12px;padding:13px 18px; }
    .unit-name { font-weight:700;color:#111827;font-size:13.5px; }
    .unit-depth-indent { display:inline-block; }
    .level-badge { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:6px;background:#f3f4f6;color:#374151;text-transform:capitalize;margin-left:8px; }
    .profile-count-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;margin-left:auto;white-space:nowrap; }
    .profile-count-badge.has { background:#dcfce7;color:#15803d; }
    .profile-count-badge.none { background:#f3f4f6;color:#9ca3af; }

    {{-- Highlight sementara utk unit yg dituju dari ?highlight= (mis. dari icon Job Profile
         di Tree View) — warna amber SAMA PERSIS dgn .org-box-highlight di tree.blade.php,
         fade balik ke tampilan normal murni pakai CSS @keyframes. --}}
    .unit-card-highlight { animation: unitHighlightFade 2.2s ease-out forwards; }
    @keyframes unitHighlightFade {
        0%   { box-shadow: 0 0 0 3px rgba(245,158,11,.35); border-color: #f59e0b; }
        100% { box-shadow: var(--card-shadow); border-color: var(--card-border); }
    }

    .unit-panel { border-top:1px solid #f3f4f6;padding:14px 18px;background:#fbfbfa; }
    .profile-item { display:flex;align-items:center;gap:10px;padding:9px 12px;background:white;border:1px solid #f3f4f6;border-radius:8px;margin-bottom:8px;font-size:12.5px; }
    .profile-item:last-child { margin-bottom:0; }
    .profile-item .jabatan { font-weight:700;color:#111827;min-width:160px; }
    .profile-item .keterangan-text { color:#6b7280;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .profile-item a.file-link { color:#1d4ed8;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px; }
    .profile-item a.file-link:hover { text-decoration:underline; }
    .profile-item a.file-link svg { width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2; }

    .empty-profiles { font-size:12px;color:#9ca3af; }

    [x-cloak] { display:none !important; }

    .toast-wrap { position:fixed;top:20px;right:20px;z-index:9999;pointer-events:none; }
    .toast { display:flex;align-items:center;gap:10px;background:white;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,0.12);font-size:13px;color:#15803d;font-weight:500;min-width:280px;position:relative;overflow:hidden;pointer-events:all;animation:toastIn 0.35s cubic-bezier(0.4,0,0.2,1) forwards; }
    .toast.error { border-color:#fecaca;border-left-color:#ef4444;color:#dc2626; }
    .toast.hiding { animation:toastOut 0.3s forwards; }
    .toast-icon { width:22px;height:22px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .toast.error .toast-icon { background:#fef2f2; }
    .toast-icon svg { width:12px;height:12px;stroke:#16a34a;fill:none;stroke-width:2.5; }
    .toast.error .toast-icon svg { stroke:#ef4444; }
    .toast-close { border:none;background:transparent;color:#9ca3af;cursor:pointer;font-size:18px;padding:0;margin-left:auto; }
    .toast-progress { position:absolute;bottom:0;left:0;height:3px;background:#16a34a;animation:toastProgress 4s linear forwards; }
    .toast.error .toast-progress { background:#ef4444; }
    @keyframes toastIn { from{opacity:0;transform:translateX(110%);}to{opacity:1;transform:translateX(0);} }
    @keyframes toastOut { from{opacity:1;}to{opacity:0;transform:translateX(110%);} }
    @keyframes toastProgress { from{width:100%;}to{width:0%;} }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div>{{ session('success') }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

@if(session('error'))
<div class="toast-wrap" id="toastWrap">
    <div class="toast error" id="toast">
        <div class="toast-icon"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
        <div>{{ session('error') }}</div>
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>
@endif

<a href="{{ route('organisasi.job-profile.index') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Job Profile
</a>

<div class="page-header">
    <div>
        <div class="page-title">Job Profile — SK {{ $versi->nomor_sk }}</div>
        <div class="page-sub">{{ $totalUnit }} unit organisasi pada versi ini · berlaku {{ $versi->tanggal_mulai_berlaku->translatedFormat('d F Y') }}</div>
    </div>
    <a href="{{ route('organisasi.job-profile.edit', $versi) }}" class="btn-kelola">
        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Kelola Job Profile
    </a>
</div>

@php $pct = $totalUnit > 0 ? round(($unitWithProfileCount / $totalUnit) * 100) : 0; @endphp
<div class="progress-card">
    <div class="progress-text"><span class="num">{{ $unitWithProfileCount }}</span> dari {{ $totalUnit }} unit sudah punya minimal 1 Job Profile</div>
    <div class="progress-bar-track">
        <div class="progress-bar-fill {{ $unitWithProfileCount === 0 ? 'empty' : '' }}" style="width:{{ $pct }}%;"></div>
    </div>
</div>

@php
    // ?highlight={unit_organisasi_id} — dipakai icon Job Profile di Tree View.
    // Kalau id-nya tidak match unit manapun di halaman ini, $isHighlighted per-unit di
    // bawah otomatis selalu false — render normal, tanpa error.
    $highlightUnitId = request()->filled('highlight') ? (int) request()->query('highlight') : null;
@endphp

<div class="search-box">
    <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="jp-search" oninput="filterJobProfiles()" placeholder="Cari nama unit atau nama jabatan...">
</div>

<div class="filter-row">
    <select class="filter-select" id="jp-filter-direktorat" onchange="filterJobProfiles()">
        <option value="">Semua Direktorat</option>
        @foreach($direktoratOptions as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
    </select>
    <select class="filter-select" id="jp-filter-kompartemen" onchange="filterJobProfiles()">
        <option value="">Semua Kompartemen</option>
        @foreach($kompartemenOptions as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
    </select>
    <select class="filter-select" id="jp-filter-departemen" onchange="filterJobProfiles()">
        <option value="">Semua Departemen</option>
        @foreach($departemenOptions as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
    </select>
    <select class="filter-select" id="jp-filter-status" onchange="filterJobProfiles()">
        <option value="">Semua Status</option>
        <option value="ada">Sudah Ada Job Profile</option>
        <option value="belum">Belum Ada Job Profile</option>
    </select>
</div>

<div class="unit-list" id="jp-unit-list">
    @forelse($unitsOrdered as $item)
        @php
            $unit = $item['node'];
            $depth = $item['depth'];
            $unitProfiles = $profiles->get($unit->unit_organisasi_id, collect());
            $isHighlighted = !is_null($highlightUnitId) && $unit->unit_organisasi_id === $highlightUnitId;
            // Teks pencarian: nama unit + semua nama jabatan JP-nya, lowercase.
            $searchText = mb_strtolower($unit->nama_unit . ' ' . $unitProfiles->pluck('nama_jabatan')->implode(' '));
            $ancestors = $ancestorByUnitId[$unit->unit_organisasi_id] ?? ['direktorat' => null, 'kompartemen' => null, 'departemen' => null];
        @endphp
        {{-- Data pencarian & leluhur (utk filter dropdown) ditaruh sbg atribut data-*
             POLOS (bukan lewat Alpine) — dibaca via card.dataset di vanilla JS
             filterJobProfiles() di bawah. --}}
        <div class="unit-card {{ $isHighlighted ? 'unit-card-highlight' : '' }}"
             id="unit-card-{{ $unit->unit_organisasi_id }}"
             data-search-text="{{ $searchText }}"
             data-direktorat="{{ $ancestors['direktorat'] }}"
             data-kompartemen="{{ $ancestors['kompartemen'] }}"
             data-departemen="{{ $ancestors['departemen'] }}"
             data-jp-status="{{ $unitProfiles->count() > 0 ? 'ada' : 'belum' }}">
            <div class="unit-row">
                <span class="unit-depth-indent" style="width:{{ $depth * 18 }}px;"></span>
                <span class="unit-name">{{ $unit->nama_unit }}</span>
                <span class="level-badge">{{ $unit->level }}</span>
                @if($unitProfiles->count() > 0)
                    <span class="profile-count-badge has">{{ $unitProfiles->count() }} Job Profile</span>
                @else
                    <span class="profile-count-badge none">Belum ada</span>
                @endif
            </div>
            <div class="unit-panel">
                @if($unitProfiles->isEmpty())
                    <div class="empty-profiles">Belum ada Job Profile untuk unit ini.</div>
                @else
                    @foreach($unitProfiles as $profile)
                    <div class="profile-item">
                        <span class="jabatan">{{ $profile->nama_jabatan }}</span>
                        <a href="{{ asset('storage/' . $profile->file_path) }}" target="_blank" rel="noopener" class="file-link">
                            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            {{ $profile->file_original_name }}
                        </a>
                        <span class="keterangan-text">{{ $profile->keterangan }}</span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    @empty
        <div class="unit-card"><div class="unit-row">Tidak ada unit pada versi ini.</div></div>
    @endforelse
</div>

@endsection

@push('scripts')
<script>
    // Search & filter level — VANILLA JS MURNI, tanpa Alpine sama sekali (lihat catatan
    // di atas file). Dipanggil via oninput/onchange langsung dari HTML.
    function filterJobProfiles() {
        const search = document.getElementById('jp-search').value.toLowerCase().trim();
        const direktorat = document.getElementById('jp-filter-direktorat').value;
        const kompartemen = document.getElementById('jp-filter-kompartemen').value;
        const departemen = document.getElementById('jp-filter-departemen').value;
        const status = document.getElementById('jp-filter-status').value;
        document.querySelectorAll('.unit-card').forEach(card => {
            const matchSearch = search === '' || card.dataset.searchText.includes(search);
            const matchDirektorat = direktorat === '' || card.dataset.direktorat === direktorat;
            const matchKompartemen = kompartemen === '' || card.dataset.kompartemen === kompartemen;
            const matchDepartemen = departemen === '' || card.dataset.departemen === departemen;
            const matchStatus = status === '' || card.dataset.jpStatus === status;
            card.style.display = (matchSearch && matchDirektorat && matchKompartemen && matchDepartemen && matchStatus)
                ? '' : 'none';
        });
    }

    function closeToast() {
        const t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('hiding');
        setTimeout(() => document.getElementById('toastWrap')?.remove(), 300);
    }
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('toast')) setTimeout(() => closeToast(), 4000);

        const highlightId = {{ \Illuminate\Support\Js::from($highlightUnitId) }};
        if (highlightId) {
            document.getElementById('unit-card-' + highlightId)
                ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
@endpush
