{{--
    Select dengan pencarian — peningkatan bertahap (progressive enhancement).

    Cara pakai: tambahkan class `select-search` pada <select> mana pun.
        <select name="direktorat_id" class="form-input select-search"> ... </select>

    Prinsipnya: <select> aslinya TIDAK dihapus, hanya disembunyikan. Yang terkirim
    ke server tetap <select> itu, jadi nama field, nilai ID master, aturan
    `required`, dan old() semuanya bekerja tanpa perubahan apa pun di controller.
    Kalau JavaScript gagal jalan, dropdown bawaan peramban muncul kembali seperti
    semula — form tetap bisa dipakai.

    Pengguna hanya bisa MEMILIH dari daftar; mengetik cuma menyaring, tidak
    membuat nilai baru. Jadi isinya dijamin selalu sesuai Master Data.
--}}
<style>
    .ss-native { position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0; }
    .ss { position:relative; }

    /* Tombolnya sengaja memakai class .form-input milik halaman masing-masing
       supaya bentuknya persis sama dengan input lain di halaman itu. */
    .ss-trigger { display:flex;align-items:center;gap:8px;text-align:left;cursor:pointer; }
    .ss-trigger .ss-label { flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .ss-trigger .ss-label.kosong { color:#9ca3af; }
    .ss.open .ss-trigger { border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.08); }

    .ss-panel { position:absolute;z-index:70;top:calc(100% + 5px);left:0;right:0;background:#fff;border:1px solid #e7eaee;border-radius:11px;box-shadow:0 14px 38px rgba(16,24,40,.14);display:none;overflow:hidden; }
    .ss.open .ss-panel { display:block; }
    .ss.drop-up .ss-panel { top:auto;bottom:calc(100% + 5px); }

    .ss-search-wrap { display:flex;align-items:center;gap:8px;padding:9px 11px;border-bottom:1px solid #eef1f4;background:#fbfcfd; }
    .ss-search-wrap svg { width:14px;height:14px;stroke:#98a2b3;fill:none;stroke-width:2;flex-shrink:0; }
    .ss-search { flex:1;border:none;outline:none;background:transparent;font-family:inherit;font-size:13px;color:#101828;min-width:0; }
    .ss-search::placeholder { color:#98a2b3; }

    .ss-list { max-height:230px;overflow-y:auto;padding:5px;overscroll-behavior:contain; }
    .ss-opt { padding:8px 11px;border-radius:8px;cursor:pointer;font-size:13px;color:#344054;line-height:1.4;white-space:normal;word-break:break-word; }
    .ss-opt:hover, .ss-opt.sorot { background:#f0fdf4;color:#15803d; }
    .ss-opt.terpilih { font-weight:700;color:#15803d;background:#f0fdf4; }
    .ss-opt.kosongkan { color:#98a2b3;font-style:italic; }
    .ss-opt mark { background:transparent;color:inherit;font-weight:800;padding:0; }
    .ss-hampa { padding:16px 12px;text-align:center;color:#98a2b3;font-size:12px;display:none; }

    /* Panah bawaan .select-wrap::after milik halaman tetap terpakai; beri ruang. */
    .select-wrap .ss-trigger { padding-right:34px; }
</style>

<script>
(function () {
    'use strict';

    var IKON_CARI = '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';

    function lolos(t) {
        return String(t).replace(/[&<>"]/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c];
        });
    }

    /* Tebalkan bagian teks yang cocok dengan kata kunci. */
    function tandai(teks, kata) {
        if (!kata) return lolos(teks);
        var i = teks.toLowerCase().indexOf(kata);
        if (i < 0) return lolos(teks);
        return lolos(teks.slice(0, i)) + '<mark>' + lolos(teks.slice(i, i + kata.length)) + '</mark>' + lolos(teks.slice(i + kata.length));
    }

    function pasang(select) {
        if (select.dataset.ssSiap || select.disabled || select.multiple) return;
        select.dataset.ssSiap = '1';

        var opsi = Array.prototype.map.call(select.options, function (o) {
            return { nilai: o.value, teks: o.text.trim(), cari: o.text.trim().toLowerCase() };
        });

        var bungkus = document.createElement('div');
        bungkus.className = 'ss';

        var tombol = document.createElement('button');
        tombol.type = 'button';
        // Warisi gaya input halaman ini, buang penanda select-search-nya.
        tombol.className = (select.className.replace(/\bselect-search\b/g, '').trim() + ' ss-trigger').trim();
        tombol.setAttribute('aria-haspopup', 'listbox');
        tombol.setAttribute('aria-expanded', 'false');
        tombol.innerHTML = '<span class="ss-label"></span>';

        var panel = document.createElement('div');
        panel.className = 'ss-panel';
        panel.innerHTML =
            '<div class="ss-search-wrap">' + IKON_CARI +
            '<input type="text" class="ss-search" placeholder="Ketik untuk mencari…" autocomplete="off"></div>' +
            '<div class="ss-list" role="listbox"></div>' +
            '<div class="ss-hampa">Tidak ada yang cocok</div>';

        select.parentNode.insertBefore(bungkus, select);
        bungkus.appendChild(select);
        bungkus.appendChild(tombol);
        bungkus.appendChild(panel);
        select.classList.add('ss-native');
        select.tabIndex = -1;

        var kotakCari = panel.querySelector('.ss-search');
        var daftar    = panel.querySelector('.ss-list');
        var hampa     = panel.querySelector('.ss-hampa');
        var label     = tombol.querySelector('.ss-label');
        var sorot     = -1;

        function segarkanLabel() {
            var o = select.options[select.selectedIndex];
            var kosong = !o || o.value === '';
            label.textContent = o ? o.text.trim() : '';
            label.classList.toggle('kosong', kosong);
        }

        function gambar(kata) {
            var cocok = opsi.filter(function (o) {
                // Baris placeholder ("-- Pilih … --") bukan pilihan sungguhan:
                // fungsinya mengosongkan, jadi tidak ikut hasil pencarian.
                if (o.nilai === '') return !kata;
                return !kata || o.cari.indexOf(kata) >= 0;
            });
            daftar.innerHTML = cocok.map(function (o) {
                var kelas = 'ss-opt' +
                    (o.nilai === select.value ? ' terpilih' : '') +
                    (o.nilai === '' ? ' kosongkan' : '');
                return '<div class="' + kelas + '" role="option" data-nilai="' + lolos(o.nilai) + '">' + tandai(o.teks, kata) + '</div>';
            }).join('');
            hampa.style.display = cocok.length ? 'none' : 'block';
            sorot = -1;
            // Saat tanpa kata kunci, tampilkan pilihan yang sedang aktif.
            var aktif = daftar.querySelector('.terpilih');
            if (aktif && !kata) aktif.scrollIntoView({ block: 'nearest' });
        }

        function geser(arah) {
            var item = daftar.querySelectorAll('.ss-opt');
            if (!item.length) return;
            if (sorot >= 0 && item[sorot]) item[sorot].classList.remove('sorot');
            sorot = (sorot + arah + item.length) % item.length;
            item[sorot].classList.add('sorot');
            item[sorot].scrollIntoView({ block: 'nearest' });
        }

        /* Tentukan panel membuka ke bawah atau ke atas menurut ruang yang tersisa.
           Dihitung ulang saat halaman digulir, sebab kalau hanya dihitung sekali
           arahnya bisa basi dan panel menutupi field di atasnya. */
        function aturArah() {
            var kotak = tombol.getBoundingClientRect();
            var bawah = window.innerHeight - kotak.bottom;
            bungkus.classList.toggle('drop-up', bawah < 300 && kotak.top > bawah);
        }

        function buka() {
            aturArah();
            bungkus.classList.add('open');
            tombol.setAttribute('aria-expanded', 'true');
            kotakCari.value = '';
            gambar('');
            kotakCari.focus();
        }

        function tutup(kembalikanFokus) {
            bungkus.classList.remove('open');
            tombol.setAttribute('aria-expanded', 'false');
            if (kembalikanFokus) tombol.focus();
        }

        function pilih(nilai) {
            select.value = nilai;
            // Beri tahu kode lain (mis. validasi atau logika berantai) bahwa nilai berubah.
            select.dispatchEvent(new Event('change', { bubbles: true }));
            segarkanLabel();
            tutup(true);
        }

        tombol.addEventListener('click', function () {
            bungkus.classList.contains('open') ? tutup(false) : buka();
        });

        tombol.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                buka();
            }
        });

        kotakCari.addEventListener('input', function () {
            gambar(this.value.trim().toLowerCase());
        });

        kotakCari.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown')      { e.preventDefault(); geser(1); }
            else if (e.key === 'ArrowUp')   { e.preventDefault(); geser(-1); }
            else if (e.key === 'Escape')    { e.preventDefault(); tutup(true); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                var item = daftar.querySelectorAll('.ss-opt');
                // Tanpa panah: bila hasil saringan tinggal satu, itu yang dipakai.
                var sasaran = sorot >= 0 ? item[sorot] : (item.length === 1 ? item[0] : null);
                if (sasaran) pilih(sasaran.dataset.nilai);
            }
        });

        daftar.addEventListener('click', function (e) {
            var opt = e.target.closest('.ss-opt');
            if (opt) pilih(opt.dataset.nilai);
        });

        // Perubahan dari luar (mis. reset form) ikut tercermin di tombol.
        select.addEventListener('change', segarkanLabel);

        document.addEventListener('click', function (e) {
            if (!bungkus.contains(e.target)) tutup(false);
        });

        ['scroll', 'resize'].forEach(function (ev) {
            window.addEventListener(ev, function () {
                if (bungkus.classList.contains('open')) aturArah();
            }, { passive: true });
        });

        segarkanLabel();
    }

    function pasangSemua(akar) {
        (akar || document).querySelectorAll('select.select-search').forEach(pasang);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { pasangSemua(); });
    } else {
        pasangSemua();
    }

    // Supaya select yang muncul belakangan (mis. di dalam modal) bisa ikut dipasang.
    window.pasangSelectSearch = pasangSemua;
})();
</script>
