{{--
    Panel slide-over dari kanan, dipicu dari mana saja di halaman via
    $store.riwayatOverlay.openPanel(unitId, namaUnit) (lihat org-tree-node.blade.php &
    show.blade.php). Include partial ini SEKALI per halaman (root-level, bukan di dalam
    loop) — store Alpine-nya global, dipanggil dari elemen manapun di halaman yg sama.

    Tidak ada pola slide-over lain di project ini utk ditiru (sudah dicek) — backdrop
    blur mengikuti konvensi .modal-backdrop yg sudah ada (rgba(0,0,0,.45) + blur(3px)),
    cuma panel-nya nempel di kanan (bukan di tengah) & slide-in dari kanan.
--}}
@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .riwayat-overlay-backdrop { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:1100;display:flex;justify-content:flex-end; }
    .riwayat-overlay-panel { background:white;width:100%;max-width:480px;height:100%;box-shadow:-20px 0 60px rgba(0,0,0,0.15);display:flex;flex-direction:column;overflow:hidden;animation:riwayatSlideIn .25s cubic-bezier(.4,0,.2,1); }
    @keyframes riwayatSlideIn { from { transform:translateX(100%); } to { transform:translateX(0); } }

    .riwayat-overlay-header { padding:20px 22px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-shrink:0; }
    .riwayat-overlay-label { font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px; }
    .riwayat-overlay-nama { font-size:16px;font-weight:700;color:#111827;line-height:1.3; }
    .riwayat-overlay-close { border:none;background:#f9fafb;color:#6b7280;width:30px;height:30px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:inherit; }
    .riwayat-overlay-close:hover { background:#f3f4f6;color:#111827; }
    .riwayat-overlay-close svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }

    .riwayat-overlay-bagan { display:flex;align-items:center;justify-content:center;gap:7px;margin:16px 22px 0;padding:10px 14px;border-radius:9px;background:#15803d;color:white;font-size:13px;font-weight:600;text-decoration:none;flex-shrink:0; }
    .riwayat-overlay-bagan:hover { background:#166534; }
    .riwayat-overlay-bagan svg { width:14px;height:14px;stroke:white;fill:none;stroke-width:2;flex-shrink:0; }

    .riwayat-overlay-body { flex:1;overflow-y:auto;padding:18px 22px 24px; }
    .riwayat-overlay-loading { text-align:center;color:#9ca3af;font-size:13px;padding:40px 0; }

    @media (max-width:560px) {
        .riwayat-overlay-panel { max-width:100%; }
    }

    /* CSS isi riwayat (.riwayat-narasi-*) DIDUPLIKASI di sini dari
       partials/riwayat-narasi-list.blade.php dgn sengaja: isi overlay di-fetch AJAX &
       di-inject via x-html ke .riwayat-overlay-body (bukan disertakan lewat Blade include
       biasa), jadi push-styles DI DALAM partial itu sendiri tidak pernah ke-render saat
       dipanggil standalone lewat endpoint AJAX (tidak ada stack-styles yg menampungnya di
       response partial-only itu) — CSS-nya harus sudah ada di halaman INDUK (halaman ini)
       SEBELUM konten di-inject. Kelas yg sama ini tetap dipertahankan juga di file partial
       aslinya krn dipakai ulang scr normal (disertakan biasa, bukan AJAX) di Tab List
       halaman Timeline penuh. */
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

<div class="riwayat-overlay-backdrop" x-data x-show="$store.riwayatOverlay.open" x-cloak
     @keydown.escape.window="$store.riwayatOverlay.close()">
    <div class="riwayat-overlay-panel" @click.outside="$store.riwayatOverlay.close()">
        <div class="riwayat-overlay-header">
            <div>
                <div class="riwayat-overlay-label">Riwayat Unit</div>
                <div class="riwayat-overlay-nama" x-text="$store.riwayatOverlay.namaUnit"></div>
            </div>
            <button type="button" class="riwayat-overlay-close" @click="$store.riwayatOverlay.close()" title="Tutup">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <a class="riwayat-overlay-bagan" :href="$store.riwayatOverlay.baganUrl()">
            <svg viewBox="0 0 24 24"><circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="12" cy="18" r="2.5"/><path d="M6 8.5V12a4 4 0 0 0 4 4M18 8.5V12a4 4 0 0 0-4 4"/></svg>
            Lihat sebagai Bagan
        </a>

        <div class="riwayat-overlay-body">
            <template x-if="$store.riwayatOverlay.loading">
                <div class="riwayat-overlay-loading">Memuat riwayat...</div>
            </template>
            <div x-show="!$store.riwayatOverlay.loading" x-html="$store.riwayatOverlay.html"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('riwayatOverlay', {
            open: false,
            loading: false,
            unitId: null,
            namaUnit: '',
            html: '',

            openPanel(unitId, namaUnit) {
                this.open = true;
                this.loading = true;
                this.unitId = unitId;
                this.namaUnit = namaUnit;
                this.html = '';
                document.body.style.overflow = 'hidden';

                fetch('{{ route('organisasi.unit.riwayat-overlay', ['unit' => '__ID__']) }}'.replace('__ID__', unitId), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(r => r.text())
                    .then(html => { this.html = html; this.loading = false; })
                    .catch(() => {
                        this.html = '<p style="color:#9ca3af;font-size:13px;">Gagal memuat data riwayat.</p>';
                        this.loading = false;
                    });
            },
            close() {
                this.open = false;
                document.body.style.overflow = '';
            },
            baganUrl() {
                return this.unitId
                    ? '{{ route('organisasi.unit.timeline', ['unit' => '__ID__']) }}'.replace('__ID__', this.unitId)
                    : '#';
            },
        });
    });
</script>
@endpush
