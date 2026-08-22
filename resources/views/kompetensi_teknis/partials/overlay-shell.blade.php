{{--
    Panel slide-over dari kanan utk Kompetensi Teknis — 1 shell, DIPAKAI BERSAMA oleh 2
    trigger yg terpisah:
    1) icon award di Tree View (org-tree-node.blade.php) lewat openKomtekOverlay() ->
       unitOverlay() (SEMUA jenjang di 1 unit).
    2) tombol "Detail" di halaman list Kompetensi Teknis (kompetensi_teknis/index.blade.php)
       lewat openPosisiOverlay() -> posisiOverlay() (1 kombinasi unit+jenjang/"posisi" saja).
    Keduanya tidak pernah dipicu di halaman yg sama, jadi aman reuse 1 set DOM id/CSS. Include
    partial ini SEKALI per halaman pemanggil (root-level) — persis pola riwayat-overlay-shell.
    blade.php di modul Struktur Organisasi (backdrop + panel nempel kanan + slide-in), TAPI
    interaktivitasnya VANILLA JS MURNI (fetch + classList + innerHTML), BUKAN Alpine store
    — keputusan final project ini (Alpine sudah beberapa kali gagal utk kebutuhan slide-over
    serupa, lihat catatan sejenis di organisasi/job-profile/show.blade.php).

    CSS isi overlay (.komtek-jenjang-*, .komtek-badge-*) SENGAJA ditaruh di SINI (shell yg
    di-include normal via Blade, bukan di-fetch AJAX) — bukan di partials/unit-overlay.blade.php
    yg jadi target innerHTML injection. Alasan SAMA PERSIS dgn kenapa riwayat-overlay-shell
    menduplikasi CSS .riwayat-narasi-* di dalam dirinya sendiri alih2 di partial isi: partial
    yg di-fetch AJAX & di-inject lewat innerHTML tidak pernah melewati siklus @push('styles')
    Blade saat dipanggil standalone dari endpoint AJAX, jadi CSS-nya harus SUDAH ADA di
    halaman induk SEBELUM konten di-inject.
--}}
@push('styles')
<style>
    .komtek-overlay-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1100;display:none;justify-content:flex-end; }
    .komtek-overlay-backdrop.open { display:flex; }
    .komtek-overlay-panel { background:white;width:100%;max-width:480px;height:100%;box-shadow:-20px 0 60px rgba(0,0,0,0.15);display:flex;flex-direction:column;overflow:hidden;animation:komtekSlideIn .25s cubic-bezier(.4,0,.2,1); }
    @keyframes komtekSlideIn { from { transform:translateX(100%); } to { transform:translateX(0); } }

    .komtek-overlay-header { padding:20px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-shrink:0; }
    .komtek-overlay-label { font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px; }
    .komtek-overlay-nama { font-size:16px;font-weight:700;color:#111827;line-height:1.3; }
    .komtek-overlay-versi { font-size:11.5px;color:#9ca3af;margin-top:4px; }
    .komtek-overlay-close { border:none;background:#f9fafb;color:#6b7280;width:30px;height:30px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:inherit; }
    .komtek-overlay-close:hover { background:#f3f4f6;color:#111827; }
    .komtek-overlay-close svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .komtek-overlay-body { flex:1;overflow-y:auto;padding:18px 22px 24px; }
    .komtek-overlay-loading, .komtek-overlay-empty { text-align:center;color:#9ca3af;font-size:13px;padding:40px 0; }

    @media (max-width:560px) {
        .komtek-overlay-panel { max-width:100%; }
    }

    /* ===== Isi overlay (di-inject via innerHTML, lihat catatan di atas) ===== */
    .komtek-versi-info { font-size:11.5px;color:#9ca3af;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #f3f4f6; }
    .komtek-versi-info a { color:inherit;text-decoration:underline; }
    .komtek-jenjang-group { margin-bottom:18px; }
    .komtek-jenjang-group:last-child { margin-bottom:0; }
    .komtek-jenjang-card { background:#fbfbfa;border:1px solid var(--card-border);border-radius:12px;padding:12px 14px;margin-bottom:8px; }
    .komtek-jenjang-title { font-size:13.5px;font-weight:700;color:#111827;margin-bottom:6px; }
    .komtek-jenjang-meta { display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:11.5px;color:#6b7280; }
    .komtek-grade-badge { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:6px;background:#f3f4f6;color:#374151; }
    .komtek-managerial-badge { display:inline-block;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:6px; }
    .komtek-managerial-badge.ya { background:#dcfce7;color:#15803d; }
    .komtek-managerial-badge.tidak { background:#f3f4f6;color:#6b7280; }

    .komtek-item { display:flex;align-items:center;gap:10px;padding:9px 12px;background:white;border:1px solid #f3f4f6;border-radius:8px;margin-bottom:6px;font-size:12.5px; }
    .komtek-item:last-child { margin-bottom:0; }
    .komtek-item .nama { font-weight:600;color:#111827;flex:1; }
    .komtek-item .level-text { color:#6b7280;font-size:12px;white-space:nowrap; }
    /* Label badge TUNGGAL (bukan lagi "Native · Primary" dst) — kolom DB asal/prioritas
       TIDAK berubah, ini murni presentasi (lihat UnitKompetensiTeknis::getPrioritasLabel
       Attribute()/getPrioritasBadgeClassAttribute()):
       - native+primary   -> "Primary" (hijau — selection criteria rumpun sendiri)
       - native+secondary -> "Secondary" (biru — rumpun sendiri, bukan selection criteria)
       - generic+secondary-> "Generic" (kuning — pinjaman rumpun lain, kombinasi paling umum)
       - generic+primary  -> "Primary (Generic)" (oranye — kasus khusus, kompetensi
         pinjaman rumpun lain tapi levelnya tetap primary; oranye BUKAN hijau biasa supaya
         tetap kelihatan ini pengecualian, bukan Primary native biasa). */
    .komtek-badge-tipe { display:inline-block;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;white-space:nowrap; }
    .komtek-badge-tipe.primary { background:#dcfce7;color:#15803d; }
    .komtek-badge-tipe.secondary { background:#dbeafe;color:#1d4ed8; }
    .komtek-badge-tipe.generic { background:#fef9c3;color:#854d0e; }
    .komtek-badge-tipe.primary-generic { background:#ffedd5;color:#c2410c; }
</style>
@endpush

<div class="komtek-overlay-backdrop" id="komtekOverlayBackdrop" onclick="if (event.target === this) closeKomtekOverlay();">
    <div class="komtek-overlay-panel">
        <div class="komtek-overlay-header">
            <div>
                <div class="komtek-overlay-label">Kompetensi Teknis</div>
                <div class="komtek-overlay-nama" id="komtekOverlayNama"></div>
            </div>
            <button type="button" class="komtek-overlay-close" onclick="closeKomtekOverlay()" title="Tutup">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="komtek-overlay-body" id="komtekOverlayBody">
            <div class="komtek-overlay-loading">Memuat data...</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // VANILLA JS MURNI — tanpa Alpine sama sekali (lihat catatan di atas file).
    function openKomtekOverlay(unitId, namaUnit, versiId) {
        const backdrop = document.getElementById('komtekOverlayBackdrop');
        const body     = document.getElementById('komtekOverlayBody');
        const nama     = document.getElementById('komtekOverlayNama');

        nama.textContent = namaUnit;
        body.innerHTML = '<div class="komtek-overlay-loading">Memuat data...</div>';
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';

        const url = '{{ route('organisasi.unit.kompetensi-teknis-overlay', ['unit' => '__ID__']) }}'.replace('__ID__', unitId)
            + '?versi=' + encodeURIComponent(versiId);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => { body.innerHTML = html; })
            .catch(() => {
                body.innerHTML = '<div class="komtek-overlay-empty">Gagal memuat data kompetensi teknis.</div>';
            });
    }

    // Dipicu dari tombol "Detail" di halaman list Kompetensi Teknis (bukan Tree View) —
    // REUSE backdrop/panel/CSS yg sama persis dgn openKomtekOverlay() di atas (yg TIDAK
    // disentuh sama sekali), cuma beda endpoint (posisiOverlay(), difilter ke 1 jenjang)
    // & isi header (nama posisi, bukan nama unit). Aman reuse 1 set DOM id krn 2 fungsi
    // ini tidak pernah dipicu di halaman yg sama (Tree View vs halaman List Kompetensi
    // Teknis terpisah).
    function openPosisiOverlay(unitId, jenjang, namaPosisi, versiId) {
        const backdrop = document.getElementById('komtekOverlayBackdrop');
        const body     = document.getElementById('komtekOverlayBody');
        const nama     = document.getElementById('komtekOverlayNama');

        nama.textContent = namaPosisi;
        body.innerHTML = '<div class="komtek-overlay-loading">Memuat data...</div>';
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';

        const url = '{{ route('organisasi.kompetensi-teknis.posisi-overlay', ['unit' => '__ID__']) }}'.replace('__ID__', unitId)
            + '?versi=' + encodeURIComponent(versiId) + '&jenjang=' + encodeURIComponent(jenjang);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => { body.innerHTML = html; })
            .catch(() => {
                body.innerHTML = '<div class="komtek-overlay-empty">Gagal memuat data kompetensi teknis.</div>';
            });
    }

    function closeKomtekOverlay() {
        document.getElementById('komtekOverlayBackdrop').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeKomtekOverlay();
    });
</script>
@endpush
