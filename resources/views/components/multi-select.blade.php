{{--
    Filter banyak-pilihan: tombol seperti <select>, panel centang, chip hasil.

    Dipakai begini:
        <x-multi-select name="direktorat_id" label="Direktorat"
                        :options="$direktorats->pluck('nama_direktorat', 'id')"
                        :selected="old('direktorat_id', [])" />

    Bertingkat (pilihan menyusut mengikuti filter di atasnya):
        <x-multi-select name="departemen_id" label="Departemen"
                        :options="..." :selected="..."
                        induk="kompartemen_id" :peta="$petaDepartemen" />

    `peta` memetakan nilai pilihan -> daftar nilai induk yang memuatnya. Satu
    pilihan boleh punya beberapa induk (mis. departemen yang dipakai di dua
    kompartemen) — ia muncul di keduanya.

    Tidak memakai <select multiple>: select-search sengaja melewatinya, dan
    multi-select bawaan peramban (ctrl+klik) tidak pantas untuk panel sempit.
--}}

@props([
    'name',
    'label',
    'options'     => [],
    'selected'    => [],
    'induk'       => null,
    'peta'        => [],
    'placeholder' => 'Semua',
])

@php
    $options  = is_array($options) ? $options : $options->all();
    $terpilih = array_map('strval', (array) $selected);
    // Kotak cari hanya berguna kalau pilihannya banyak.
    $pakaiCari = count($options) > 10;
@endphp

<div class="field">
    <label>{{ $label }}</label>

    <div class="ms" data-nama="{{ $name }}"
         @if($induk) data-induk="{{ $induk }}" @endif
         data-kosong="{{ $placeholder }}">

        <button type="button" class="ms-trigger kosong" data-trigger>
            <span data-teks>{{ $placeholder }}</span>
        </button>

        <div class="ms-panel" data-panel>
            @if($pakaiCari)
                <div class="ms-cari-wrap">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="ms-cari" data-cari placeholder="Cari...">
                </div>
            @endif

            <div class="ms-list" data-list>
                @foreach($options as $nilai => $teks)
                    <label class="ms-opt" data-opt data-induk-nilai="{{ implode(' ', $peta[$nilai] ?? []) }}">
                        <input type="checkbox" name="{{ $name }}[]" value="{{ $nilai }}"
                               {{ in_array((string) $nilai, $terpilih, true) ? 'checked' : '' }}>
                        <span data-label>{{ $teks }}</span>
                    </label>
                @endforeach
            </div>

            <div class="ms-hampa" data-hampa>Tidak ada pilihan yang cocok.</div>

            <div class="ms-kaki">
                <button type="button" data-semua>Pilih semua</button>
                <button type="button" data-bersih>Bersihkan</button>
            </div>
        </div>

        <div class="ms-cip" data-cip></div>
    </div>
</div>

@pushOnce('styles', 'multi-select-css')
<style>
    .ms { position:relative; }

    /* Tombolnya sengaja meniru .field select halaman ini agar panel filter seragam */
    .ms-trigger { width:100%;display:flex;align-items:center;text-align:left;cursor:pointer;
                  border:1px solid #d1d5db;border-radius:8px;padding:8px 30px 8px 10px;font-size:13px;
                  font-family:inherit;color:#374151;background:#fff;position:relative; }
    .ms-trigger::after { content:'';position:absolute;right:11px;top:50%;transform:translateY(-50%);
                         width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;
                         border-top:5px solid #98a2b3;pointer-events:none; }
    .ms-trigger > span { flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .ms-trigger.kosong > span { color:#9ca3af; }
    .ms.buka .ms-trigger { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08); }
    .ms.buka .ms-trigger::after { border-top-color:#15803d; }

    .ms-panel { position:absolute;z-index:70;top:calc(100% + 5px);left:0;right:0;background:#fff;
                border:1px solid #e7eaee;border-radius:11px;box-shadow:0 14px 38px rgba(16,24,40,.14);
                display:none;overflow:hidden; }
    .ms.buka .ms-panel { display:block; }
    .ms.atas .ms-panel { top:auto;bottom:calc(100% + 5px); }

    .ms-cari-wrap { display:flex;align-items:center;gap:8px;padding:9px 11px;
                    border-bottom:1px solid #eef1f4;background:#fbfcfd; }
    .ms-cari-wrap svg { width:14px;height:14px;stroke:#98a2b3;fill:none;stroke-width:2;flex-shrink:0; }
    .ms-cari { flex:1;min-width:0;border:none;outline:none;background:transparent;
               font-family:inherit;font-size:13px;color:#101828; }
    .ms-cari::placeholder { color:#98a2b3; }

    .ms-list { max-height:208px;overflow-y:auto;padding:5px;overscroll-behavior:contain; }
    .ms-opt { display:flex;align-items:flex-start;gap:8px;padding:6px 7px;border-radius:7px;cursor:pointer;
              font-size:12.5px;color:#344054;line-height:1.35; }
    .ms-opt:hover { background:#f0fdf4;color:#15803d; }
    .ms-opt input { width:14px;height:14px;margin-top:1px;flex-shrink:0;accent-color:#15803d;cursor:pointer; }

    .ms-hampa { display:none;padding:16px 12px;text-align:center;color:#98a2b3;font-size:12px;line-height:1.5; }

    .ms-kaki { display:flex;gap:12px;padding:7px 11px;border-top:1px solid #eef1f4;background:#fbfcfd; }
    .ms-kaki button { border:0;background:none;padding:0;font:inherit;font-size:11px;font-weight:600;
                      color:#15803d;cursor:pointer; }
    .ms-kaki button:hover { text-decoration:underline; }

    .ms-cip { display:flex;flex-wrap:wrap;gap:5px;margin-top:6px; }
    .ms-cip:empty { margin-top:0; }
    .ms-cip button { display:inline-flex;align-items:center;gap:5px;max-width:100%;
                     background:#f0fdf4;border:1px solid #bbf7d0;border-radius:999px;
                     padding:3px 7px 3px 9px;font:inherit;font-size:11px;font-weight:600;color:#15803d;
                     cursor:pointer;line-height:1.4; }
    .ms-cip button:hover { background:#dcfce7; }
    .ms-cip button span { overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .ms-cip button i { font-style:normal;font-size:13px;line-height:1;opacity:.65;flex-shrink:0; }
</style>
@endPushOnce

@pushOnce('scripts', 'multi-select-js')
<script>
/**
 * Filter banyak-pilihan. Satu pengendali untuk seluruh .ms di halaman.
 *
 * Yang bertingkat memakai data-induk: pilihan yang induknya tidak terpilih
 * disembunyikan DAN centangnya dilepas — saringan yang tak terlihat tidak
 * boleh ikut terkirim saat export. Mengetik di kotak cari juga menyembunyikan,
 * tapi TIDAK pernah melepas centang: menyaring tampilan bukan membatalkan
 * pilihan.
 */
(function () {
    'use strict';

    function semuaMs()     { return Array.from(document.querySelectorAll('.ms')); }
    function cariMs(nama)  { return document.querySelector('.ms[data-nama="' + nama + '"]'); }
    function opsi(ms)      { return Array.from(ms.querySelectorAll('[data-opt]')); }
    function kotak(opt)    { return opt.querySelector('input[type=checkbox]'); }
    function terpilih(ms)  { return opsi(ms).filter(function (o) { return kotak(o).checked; }); }

    function segarkan(ms) {
        var dipilih = terpilih(ms);
        var kosong  = ms.dataset.kosong || 'Semua';
        var cip     = ms.querySelector('[data-cip]');

        ms.querySelector('[data-teks]').textContent = dipilih.length ? dipilih.length + ' dipilih' : kosong;
        ms.querySelector('[data-trigger]').classList.toggle('kosong', dipilih.length === 0);

        cip.innerHTML = '';
        dipilih.forEach(function (o) {
            var nama = o.querySelector('[data-label]').textContent.trim();
            var b = document.createElement('button');
            b.type  = 'button';
            b.title = 'Hapus ' + nama;
            b.innerHTML = '<span></span><i>&times;</i>';
            b.querySelector('span').textContent = nama;
            b.addEventListener('click', function () {
                kotak(o).checked = false;
                segarkan(ms);
                rantai(ms.dataset.nama);
            });
            cip.appendChild(b);
        });
    }

    /* Satu-satunya tempat yang menentukan pilihan mana yang tampak. */
    function terapkan(ms) {
        var induk     = ms.dataset.induk ? cariMs(ms.dataset.induk) : null;
        var kotakCari = ms.querySelector('[data-cari]');
        var q         = kotakCari ? kotakCari.value.trim().toLowerCase() : '';
        var tampil    = 0;

        // Induk yang menentukan: kalau ia punya pilihan, itu yang dipakai;
        // kalau belum memilih tapi dirinya sendiri sudah disusutkan kakeknya,
        // dipakai pilihan induk yang MASIH TAMPAK. Tanpa ini, memilih
        // Direktorat menyusutkan Kompartemen tapi Departemen tetap utuh.
        var nilaiInduk = [], indukTerbatas = false;
        if (induk) {
            var dipilihInduk = terpilih(induk).map(function (o) { return kotak(o).value; });
            var tampakInduk  = opsi(induk)
                .filter(function (o) { return o.style.display !== 'none'; })
                .map(function (o) { return kotak(o).value; });

            if (dipilihInduk.length) {
                nilaiInduk = dipilihInduk; indukTerbatas = true;
            } else if (tampakInduk.length < opsi(induk).length) {
                nilaiInduk = tampakInduk;  indukTerbatas = true;
            }
        }

        opsi(ms).forEach(function (o) {
            var milik = (o.dataset.indukNilai || '').split(' ').filter(Boolean);
            var lolosInduk = !indukTerbatas
                             || milik.some(function (v) { return nilaiInduk.indexOf(v) >= 0; });
            var lolosCari  = !q || o.querySelector('[data-label]').textContent.toLowerCase().indexOf(q) >= 0;

            o.style.display = (lolosInduk && lolosCari) ? 'flex' : 'none';
            if (lolosInduk && lolosCari) tampil++;
            if (!lolosInduk) kotak(o).checked = false;
        });

        var hampa = ms.querySelector('[data-hampa]');
        if (hampa) hampa.style.display = tampil ? 'none' : 'block';
        segarkan(ms);
    }

    /* Rantai Direktorat -> Kompartemen -> Departemen. */
    function rantai(nama) {
        semuaMs().forEach(function (anak) {
            if (anak.dataset.induk !== nama) return;
            terapkan(anak);
            rantai(anak.dataset.nama);
        });
    }

    function tutupSemua(kecuali) {
        semuaMs().forEach(function (ms) { if (ms !== kecuali) ms.classList.remove('buka'); });
    }

    /*
     * Dropdown select-search (partials/select-search) menutup diri lewat klik
     * yang menggelembung ke document. Trigger .ms sengaja menghentikan
     * gelembung itu, jadi tanpa ini dua dropdown bisa terbuka bersamaan.
     */
    function tutupSelectSearch() {
        document.querySelectorAll('.ss.open').forEach(function (ss) {
            ss.classList.remove('open');
            var tombol = ss.querySelector('[aria-expanded]');
            if (tombol) tombol.setAttribute('aria-expanded', 'false');
        });
    }

    semuaMs().forEach(function (ms) {
        var trigger = ms.querySelector('[data-trigger]');

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var buka = !ms.classList.contains('buka');
            tutupSemua(ms);
            tutupSelectSearch();
            ms.classList.toggle('buka', buka);
            if (buka) {
                // Panel dibalik ke atas kalau ruang di bawah tidak cukup.
                ms.classList.toggle('atas',
                    window.innerHeight - trigger.getBoundingClientRect().bottom < 300);
                var c = ms.querySelector('[data-cari]');
                if (c) { c.value = ''; terapkan(ms); c.focus(); }
            }
        });

        ms.querySelector('[data-panel]').addEventListener('click', function (e) { e.stopPropagation(); });

        ms.addEventListener('change', function (e) {
            if (e.target.type !== 'checkbox') return;
            segarkan(ms);
            rantai(ms.dataset.nama);
        });

        var c = ms.querySelector('[data-cari]');
        if (c) c.addEventListener('input', function () { terapkan(ms); });

        ms.querySelector('[data-semua]').addEventListener('click', function () {
            opsi(ms).forEach(function (o) { if (o.style.display !== 'none') kotak(o).checked = true; });
            segarkan(ms);
            rantai(ms.dataset.nama);
        });

        ms.querySelector('[data-bersih]').addEventListener('click', function () {
            opsi(ms).forEach(function (o) { kotak(o).checked = false; });
            segarkan(ms);
            rantai(ms.dataset.nama);
        });
    });

    // Keadaan awal: induk dulu (chip & hitungannya), lalu anaknya menyesuaikan.
    semuaMs().forEach(function (ms) { if (!ms.dataset.induk) segarkan(ms); });
    semuaMs().forEach(function (ms) { if (ms.dataset.induk) terapkan(ms); });

    document.addEventListener('click', function (e) {
        var di = (e.target instanceof Element) ? e.target.closest('.ms') : null;
        if (!di) tutupSemua(null);
    }, true);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupSemua(null); });
})();
</script>
@endPushOnce
