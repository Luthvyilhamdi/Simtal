{{--
    Pengingat PJS — muncul sekali tiap login.

    Datanya dibawa middleware PengingatPjs lewat View::share('pengingatPjs').
    Hanya diisi kalau ADA yang syaratnya sudah lengkap; kalau tidak, berkas ini
    tidak menghasilkan apa pun.
--}}
@if(!empty($pengingatPjs))
@php $p = $pengingatPjs; @endphp

<div class="ppjs-latar" id="ppjsLatar" role="dialog" aria-modal="true" aria-labelledby="ppjsJudul">
    <div class="ppjs-kartu">
        <div class="ppjs-kepala">
            <span class="ppjs-ikon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div style="flex:1;min-width:0">
                <div class="ppjs-judul" id="ppjsJudul">Pengingat PJS</div>
                <div class="ppjs-sub">
                    <b>{{ count($p['orang']) + $p['sisa'] }}</b> siap diangkat tetap
                    <span style="color:#9ca3af">· dari {{ $p['total'] }} PJS berjalan</span>
                </div>
            </div>
            <button type="button" class="ppjs-tutup" id="ppjsTutup" aria-label="Tutup">&times;</button>
        </div>

        <div class="ppjs-daftar">
            @foreach($p['orang'] as $o)
                <div class="ppjs-baris">
                    <div class="ppjs-nama">{{ $o['nama'] }}</div>
                    <div class="ppjs-posisi">{{ $o['posisi'] }}</div>

                    <dl class="ppjs-rinci">
                        @if($o['lama'])
                            <dt>Memangku</dt>
                            <dd class="{{ $o['terlalu_lama'] ? 'awas' : '' }}">{{ $o['lama'] }}</dd>
                        @endif
                        <dt>MDG JG</dt><dd>{{ $o['mdg_jg'] }}</dd>
                        <dt>MDG PG</dt><dd>{{ $o['mdg_pg'] }}</dd>
                        <dt>Assessment</dt><dd>{{ $o['assessment'] }}</dd>
                    </dl>
                </div>
            @endforeach
            @if($p['sisa'] > 0)
                <div class="ppjs-sisa">dan {{ $p['sisa'] }} lainnya yang juga siap</div>
            @endif
        </div>

    </div>
</div>

<style>
    /* overflow-y + margin:auto pada kartu: kalau isinya kebetulan lebih tinggi
       dari layar, latarnya yang bisa digulir — bukan kartunya terpotong di
       atas, yang membuat tombol tutup ikut hilang dan pengguna terkunci. */
    .ppjs-latar { position:fixed;inset:0;background:rgba(17,24,39,.45);backdrop-filter:blur(3px);
                  z-index:1200;display:flex;align-items:center;justify-content:center;
                  padding:24px 16px;overflow-y:auto; }
    .ppjs-latar.tutup { display:none; }
    .ppjs-kartu { background:#fff;border-radius:16px;width:100%;max-width:440px;padding:20px;
                  box-shadow:0 24px 64px rgba(0,0,0,.2);animation:ppjsMasuk .22s cubic-bezier(.4,0,.2,1);
                  margin:auto;box-sizing:border-box;max-height:100%;
                  display:flex;flex-direction:column;min-height:0; }
    @keyframes ppjsMasuk { from{opacity:0;transform:translateY(8px) scale(.97)} to{opacity:1;transform:none} }

    /* Kepala tetap di tempat; yang panjang cuma daftarnya, jadi tombol tutup
       selalu terlihat berapa pun jumlah orangnya. */
    .ppjs-kepala { display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;flex-shrink:0; }
    .ppjs-ikon { width:40px;height:40px;border-radius:11px;background:#fef3c7;display:flex;
                 align-items:center;justify-content:center;flex-shrink:0; }
    .ppjs-ikon svg { width:20px;height:20px;stroke:#b45309;fill:none;stroke-width:1.9; }
    .ppjs-judul { font-size:16px;font-weight:700;color:#111827;line-height:1.3; }
    .ppjs-sub { font-size:12.5px;color:#6b7280;margin-top:2px; }
    .ppjs-sub b { color:#15803d;font-weight:700; }
    .ppjs-tutup { border:0;background:none;font-size:22px;line-height:1;color:#9ca3af;cursor:pointer;
                  padding:0 2px;flex-shrink:0; }
    .ppjs-tutup:hover { color:#374151; }

    /* Tanpa bingkai sendiri: dengan satu-dua orang, kotak di dalam kotak
       justru terlihat berantakan. Antar-orang dipisah garis tipis saja. */
    .ppjs-daftar { border-top:1px solid #f1f3f5;padding-top:14px;
                   overflow-y:auto;min-height:0;overscroll-behavior:contain; }
    .ppjs-baris + .ppjs-baris { margin-top:14px;padding-top:14px;border-top:1px solid #f1f3f5; }

    .ppjs-nama { font-size:14px;font-weight:700;color:#111827;line-height:1.35; }
    .ppjs-posisi { font-size:12px;color:#9ca3af;line-height:1.4;margin-top:1px;
                   overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }

    /* Label kiri, nilai kanan — sejajar ke bawah, muat teks sepanjang apa pun. */
    .ppjs-rinci { display:grid;grid-template-columns:86px minmax(0,1fr);gap:5px 12px;
                  margin:10px 0 0;background:#f9fafb;border-radius:9px;padding:10px 12px; }
    .ppjs-rinci dt { font-size:11.5px;color:#9ca3af;font-weight:500;line-height:1.45; }
    .ppjs-rinci dd { font-size:12.5px;color:#374151;font-weight:600;line-height:1.45;margin:0;
                     overflow-wrap:anywhere; }
    .ppjs-rinci dd.awas { color:#c2410c; }
    .ppjs-sisa { margin-top:12px;font-size:11.5px;color:#9ca3af; }

</style>

<script>
(function () {
    var latar = document.getElementById('ppjsLatar');
    if (!latar) return;

    function tutup() {
        latar.classList.add('tutup');
        document.body.style.overflow = '';
    }

    document.body.style.overflow = 'hidden';
    document.getElementById('ppjsTutup').addEventListener('click', tutup);
    // Klik di luar kartu menutup; klik di dalamnya tidak.
    latar.addEventListener('click', function (e) { if (e.target === latar) tutup(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutup(); });
})();
</script>
@endif
