{{--
    Tombol "Filter" + panel geser dari kanan.

    Cara pakai:
        <x-filter-drawer :aktif="$jumlahFilterAktif" :reset="route('surat_penting.index')">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <div class="fd-field">
                <span class="fd-label">Tipe</span>
                <select name="tipe" class="select-search" aria-label="Tipe">...</select>
            </div>
        </x-filter-drawer>

    Pembungkusnya <div>, bukan <label>: select-search menyembunyikan <select>
    aslinya, dan klik pada <label> akan ikut membuka dropdown bawaan peramban
    di balik panel. Nama field untuk pembaca layar diambil dari aria-label.

    Halaman yang menyaring lewat AJAX memakai :terapkan="'namaFungsi()'" —
    tombol Terapkan memanggil JS itu lalu menutup panel, tanpa submit form.

    Isi slot menjadi isi <form method="GET">, jadi nama field & request() lama
    tetap berlaku. Hilangkan onchange="this.form.submit()" dari select-nya:
    penyaringan dijalankan tombol Terapkan.
--}}
@props([
    'aksi'     => null,
    'reset'    => null,
    'aktif'    => 0,
    'judul'    => 'Filter Data',
    'id'       => 'filterDrawer',
    'label'    => 'Filter',
    'terapkan' => null,
])

<button type="button" class="fd-trigger{{ $aktif ? ' ada' : '' }}"
        data-fd-buka="{{ $id }}" aria-controls="{{ $id }}" aria-expanded="false">
    <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
    <span>{{ $label }}</span>
    @if($aktif)<span class="fd-badge">{{ $aktif }}</span>@endif
</button>

<div class="fd-tirai" data-fd-tutup="{{ $id }}"></div>

<aside class="fd-panel" id="{{ $id }}" role="dialog" aria-modal="true"
       aria-labelledby="{{ $id }}-judul" inert>
    <form method="GET" action="{{ $aksi ?? url()->current() }}" class="fd-form">
        <div class="fd-head">
            <h2 id="{{ $id }}-judul">{{ $judul }}</h2>
            <button type="button" class="fd-x" data-fd-tutup="{{ $id }}" aria-label="Tutup filter">
                <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="fd-body">{{ $slot }}</div>

        <div class="fd-foot">
            @if($reset)
                <a href="{{ $reset }}" class="fd-reset">Reset</a>
            @endif
            @if($terapkan)
                <button type="button" class="fd-apply" data-fd-tutup="{{ $id }}" onclick="{{ $terapkan }}">Terapkan</button>
            @else
                <button type="submit" class="fd-apply">Terapkan</button>
            @endif
        </div>
    </form>
</aside>

@once
@push('styles')
<style>
    .fd-trigger { display:inline-flex;align-items:center;gap:7px;padding:7px 13px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;flex-shrink:0;transition:all .13s; }
    .fd-trigger svg { width:13px;height:13px;fill:currentColor;stroke:none; }
    .fd-trigger:hover { background:#f9fafb;border-color:#d1d5db; }
    .fd-trigger.ada { border-color:#86efac;background:#f0fdf4;color:#15803d; }
    .fd-badge { display:inline-flex;align-items:center;justify-content:center;min-width:17px;height:17px;padding:0 5px;border-radius:9px;background:var(--brand);color:#fff;font-size:10px;font-weight:700; }

    .fd-tirai { position:fixed;inset:0;z-index:300;background:rgba(16,24,40,.45);opacity:0;visibility:hidden;transition:opacity .22s ease,visibility .22s ease; }
    .fd-tirai.buka { opacity:1;visibility:visible; }

    .fd-panel { position:fixed;top:0;right:0;bottom:0;z-index:301;width:min(380px,100%);background:#fff;border-radius:16px 0 0 16px;box-shadow:-12px 0 40px rgba(16,24,40,.16);transform:translateX(100%);visibility:hidden;transition:transform .24s cubic-bezier(.4,0,.2,1),visibility .24s; }
    .fd-panel.buka { transform:none;visibility:visible; }
    .fd-form { display:flex;flex-direction:column;height:100%; }

    .fd-head { display:flex;align-items:center;gap:12px;padding:20px 22px 14px; }
    .fd-head h2 { margin:0;font-size:18px;font-weight:700;color:var(--text-strong);flex:1; }
    .fd-x { display:flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #e5e7eb;border-radius:50%;background:#fff;color:#667085;cursor:pointer;flex-shrink:0;transition:all .13s; }
    .fd-x svg { width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2.2;stroke-linecap:round; }
    .fd-x:hover { background:#f9fafb;color:var(--text-strong); }

    .fd-body { flex:1;overflow-y:auto;padding:4px 22px 18px;display:flex;flex-direction:column;gap:11px; }
    .fd-label { display:block;font-size:11px;color:var(--text-muted);margin-bottom:1px; }

    /* Dipatok ke .fd-panel supaya menang atas gaya toolbar halaman (.toolbar
       select, .filter-row select, dll) — laci ikut berada di dalam toolbar itu. */
    .fd-panel .fd-field { display:block;background:#f4f5f7;border:0;border-radius:12px;padding:9px 14px;cursor:pointer;box-shadow:none; }
    .fd-panel .fd-field select,
    .fd-panel .fd-field input:not([type="hidden"]),
    .fd-panel .fd-field .ss-trigger { display:block;width:100%;border:0;outline:none;background:transparent;padding:0;font-family:inherit;font-size:13.5px;font-weight:600;color:var(--text-strong);cursor:pointer;box-shadow:none;height:auto; }
    .fd-panel .fd-field select { appearance:none;-webkit-appearance:none;padding-right:18px; }
    .fd-panel .fd-field .ss-trigger { padding-right:18px;min-height:19px; }
    .fd-panel .fd-field .ss-trigger::after { right:0; }
    .fd-panel .fd-field .ss { position:relative; }
    .fd-panel .fd-field .ss.open .ss-trigger { border:0;box-shadow:none; }
    .fd-panel .fd-field .ss-panel { left:-14px;right:-14px; }
    .fd-panel .fd-field:focus-within { box-shadow:0 0 0 2px rgba(21,128,61,.18); }

    .fd-foot { display:flex;gap:10px;padding:14px 22px 20px;border-top:1px solid var(--divider); }
    .fd-reset, .fd-apply { flex:1;display:inline-flex;align-items:center;justify-content:center;height:42px;border-radius:10px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;border:0;text-decoration:none;transition:all .13s; }
    .fd-reset { background:#f2f4f7;color:var(--text); }
    .fd-reset:hover { background:#e9ecf1; }
    .fd-apply { background:var(--brand);color:#fff; }
    .fd-apply:hover { background:var(--brand-600); }

    @media (max-width:520px) {
        .fd-panel { width:100%;border-radius:0; }
    }
    @media (prefers-reduced-motion:reduce) {
        .fd-panel, .fd-tirai { transition:none; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    var terbuka = null;

    function panel(id) { return document.getElementById(id); }
    function tirai(id) { return document.querySelector('.fd-tirai[data-fd-tutup="' + id + '"]'); }
    function tombol(id) { return document.querySelector('.fd-trigger[data-fd-buka="' + id + '"]'); }

    function buka(id) {
        var p = panel(id); if (!p) return;
        p.classList.add('buka');
        p.removeAttribute('inert');
        if (tirai(id)) tirai(id).classList.add('buka');
        if (tombol(id)) tombol(id).setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        terbuka = id;
        var awal = p.querySelector('.fd-body select, .fd-body .ss-trigger, .fd-body input:not([type=hidden])');
        if (awal) { try { awal.focus({ preventScroll: true }); } catch (e) { awal.focus(); } }
    }

    function tutup(id) {
        var p = panel(id); if (!p) return;
        p.classList.remove('buka');
        p.setAttribute('inert', '');
        if (tirai(id)) tirai(id).classList.remove('buka');
        if (tombol(id)) { tombol(id).setAttribute('aria-expanded', 'false'); tombol(id).focus(); }
        document.body.style.overflow = '';
        terbuka = null;
    }

    // Untuk halaman yang menyaring di sisi klien: server tidak tahu berapa
    // filter yang menyala, jadi lencananya diperbarui dari halaman.
    window.fdTandai = function (id, jumlah) {
        var t = tombol(id); if (!t) return;
        var b = t.querySelector('.fd-badge');
        t.classList.toggle('ada', !!jumlah);
        if (!jumlah) { if (b) b.remove(); return; }
        if (!b) { b = document.createElement('span'); b.className = 'fd-badge'; t.appendChild(b); }
        b.textContent = jumlah;
    };

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-fd-buka]');
        if (b) { buka(b.dataset.fdBuka); return; }
        var t = e.target.closest('[data-fd-tutup]');
        if (t) { tutup(t.dataset.fdTutup); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !terbuka) return;
        // Esc pertama menutup dropdown select-search, bukan seluruh panel.
        if (panel(terbuka).querySelector('.ss.open')) return;
        tutup(terbuka);
    });
})();
</script>
@endpush
@endonce
