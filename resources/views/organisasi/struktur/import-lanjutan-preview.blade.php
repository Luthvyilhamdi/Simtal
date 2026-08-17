@extends('layouts.app')
@section('title', 'Preview Import Versi Lanjutan')
@section('breadcrumb-parent', 'Riwayat Struktur Organisasi')
@section('breadcrumb', 'Preview Import Versi Lanjutan')

@php
    // Depth tiap unit (BFS dari root) — dipakai untuk default expand 2 level teratas,
    // pola sama dgn tree() di controller.
    $defaultExpandedIds = [];
    $queue = $roots->map(fn ($r) => [$r, 0])->all();
    while (!empty($queue)) {
        [$node, $depth] = array_shift($queue);
        if ($depth <= 1) {
            $defaultExpandedIds[] = $node->unit_organisasi_id;
        }
        foreach ($byParent->get($node->unit_organisasi_id, collect()) as $child) {
            $queue[] = [$child, $depth + 1];
        }
    }
    $allIds = $units->pluck('unit_organisasi_id')->values();

    $labelJenis = [
        'lanjut' => 'Lanjut', 'rename' => 'Rename', 'pindah_induk' => 'Pindah Induk',
        'ganti_level' => 'Ganti Level', 'pecah' => 'Pecah', 'gabung' => 'Gabung', 'baru' => 'Unit Baru',
    ];
@endphp

@push('styles')
<style>
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#6b7280;text-decoration:none;margin-bottom:20px;transition:color .12s; }
    .back-link:hover { color:#15803d; }
    .back-link svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2; }
    .page-header { margin-bottom:16px; }
    .page-title { font-size:20px;font-weight:700;color:#111827; }
    .page-sub { font-size:13px;color:#6b7280;margin-top:4px; }

    .mode-banner { display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0; }
    .warn-banner { display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#fffbeb;color:#92400e;border:1px solid #fde68a; }
    .danger-banner { display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }

    .info-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 26px;margin-bottom:16px; }
    .info-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:16px; }
    .info-item { display:flex;flex-direction:column;gap:3px; }
    .info-label { font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px; }
    .info-val { font-size:14px;font-weight:700;color:#111827; }

    .ringkasan-grid { display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap; }
    .ringkasan-item { background:white;border-radius:12px;border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:12px 18px;text-align:center;min-width:100px; }
    .ringkasan-num { font-size:20px;font-weight:800;color:#111827; }
    .ringkasan-label { font-size:10.5px;color:#6b7280;margin-top:2px; }

    .section-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 24px;margin-bottom:16px; }
    .section-card.danger { border-color:#fecaca; }
    .section-card.warn { border-color:#fde68a; }
    .section-card-title { font-size:14px;font-weight:700;color:#111827;margin-bottom:4px;display:flex;align-items:center;gap:8px; }
    .section-card-title.danger { color:#dc2626; }
    .section-card-title.warn { color:#92400e; }
    .section-card-sub { font-size:12px;color:#9ca3af;margin-bottom:14px; }

    .review-row { display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid #fde68a;background:#fffbeb;border-radius:9px;margin-bottom:8px;font-size:13px; }
    .review-row:last-child { margin-bottom:0; }
    .review-row label { display:flex;align-items:center;gap:8px;margin-left:auto;font-size:12px;font-weight:600;color:#92400e;white-space:nowrap;cursor:pointer; }
    .review-row input[type=checkbox] { width:16px;height:16px;accent-color:#d97706;cursor:pointer; }
    .review-badge { font-size:10.5px;font-weight:700;color:#92400e;background:#fef3c7;padding:2px 8px;border-radius:20px;text-transform:uppercase; }

    .bubar-row { display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid #fecaca;background:#fef2f2;border-radius:9px;margin-bottom:8px;font-size:13px; }
    .bubar-row:last-child { margin-bottom:0; }
    .bubar-row label { display:flex;align-items:center;gap:8px;margin-left:auto;font-size:12px;font-weight:600;color:#dc2626;white-space:nowrap;cursor:pointer; }
    .bubar-row input[type=checkbox] { width:16px;height:16px;accent-color:#dc2626;cursor:pointer; }

    .ringkasan-pill { display:inline-block;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;background:#f3f4f6;color:#374151;text-transform:capitalize;margin-right:4px; }

    .form-actions-card { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px; }
    .btn-cancel { display:inline-flex;align-items:center;gap:8px;background:white;color:#374151;padding:10px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e5e7eb;text-decoration:none;transition:all .15s; }
    .btn-cancel:hover { background:#f9fafb; }
    .btn-confirm { display:inline-flex;align-items:center;gap:8px;background:#15803d;color:white;padding:10px 24px;border-radius:9px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .15s; }
    .btn-confirm:hover { background:#166534; }
    .btn-confirm:disabled { background:#d1d5db;cursor:not-allowed; }
    .btn-confirm svg,.btn-cancel svg { width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2; }
    .btn-confirm svg { stroke:white; }
    .block-hint { font-size:11.5px;color:#dc2626;margin-top:8px; }

    /* ===== Tree (sama dgn tree.blade.php) ===== */
    .tree-scroll-wrap { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow-x:auto;overflow-y:hidden;padding:36px 24px;margin-bottom:16px; }
    .tree-scroll-inner { display:inline-flex;justify-content:center;min-width:100%; }
    .org-node { display:flex;flex-direction:column;align-items:center; }
    /* height:160px TETAP (bukan min-height) — lihat catatan panjang di tree.blade.php
       soal kenapa ini WAJIB utk perataan tier lintas cabang (Masalah A). */
    .org-box { width:190px;height:160px;background:white;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 12px;box-shadow:0 1px 2px rgba(16,24,40,.04);position:relative;flex-shrink:0; }
    .org-box-leaf { border-radius:22px;border-style:dashed;border-color:#c4b5fd;background:#faf8ff; }
    /* Level Fungsional = cylinder/drum — lihat catatan sama di tree.blade.php. */
    .org-box-fungsional { border-radius:50%/24px; padding:26px 12px 10px; }
    .org-box-highlight { border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.25);background:#fffbeb; }
    .org-box-top { display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:4px; }
    .org-box-level { font-size:9.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px; }
    .org-toggle { width:18px;height:18px;border-radius:5px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:12px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:inherit;flex-shrink:0; }
    .org-toggle:hover { background:#f0fdf4;border-color:#bbf7d0;color:#15803d; }
    /* line-clamp 3 baris — pasangan dari height:160px tetap di .org-box. Lihat catatan
       sama di tree.blade.php. */
    .org-box-name { font-size:12.5px;font-weight:700;color:#111827;line-height:1.3;margin-bottom:8px;min-height:32px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;line-clamp:3;overflow:hidden; }
    .org-box-stats { display:flex;flex-direction:column;gap:3px;border-top:1px solid #f3f4f6;padding-top:6px; }
    .org-stat { display:flex;align-items:center;justify-content:space-between;font-size:11px; }
    .org-stat-label { color:#9ca3af; }
    .org-stat-val { font-weight:700;color:#111827; }
    .org-child-count { position:absolute;bottom:-9px;left:50%;transform:translateX(-50%);background:#111827;color:white;font-size:9.5px;font-weight:600;padding:2px 8px;border-radius:20px;white-space:nowrap; }
    .org-children { padding-top:28px;position:relative; }
    .org-children::before { content:'';position:absolute;top:0;left:50%;width:0;border-left:1.5px solid #d1d5db;height:28px; }
    .org-children-inner { display:flex;justify-content:center; }
    .org-child-branch { position:relative;padding:28px 16px 0 16px; }
    .org-child-branch::before,
    .org-child-branch::after { content:'';position:absolute;top:0;right:50%;border-top:1.5px solid #d1d5db;width:50%;height:28px; }
    .org-child-branch::after { right:auto;left:50%;border-left:1.5px solid #d1d5db; }
    .org-child-branch:first-child::before { border:0 none; }
    /* FIX (garis buntu di node paling kanan) — lihat catatan sama di tree.blade.php. */
    .org-child-branch:last-child::after { border-top:0 none; }
    .org-child-branch:only-child { padding-top:0; }
    .org-child-branch:only-child::before,
    .org-child-branch:only-child::after { display:none; }

    /* Tier-spacer (perataan by level) — lihat catatan sama di tree.blade.php &
       org-tree-node.blade.php. */
    .org-tier-spacer { position:relative; }
    .org-tier-spacer::before { content:'';position:absolute;top:0;left:50%;width:0;border-left:1.5px solid #d1d5db;height:100%; }
    [x-cloak] { display:none !important; }

    @media (max-width:900px) {
        .info-grid { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('organisasi.struktur.import-lanjutan') }}" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Upload
</a>

<div class="page-header">
    <div class="page-title">Preview Import Versi Lanjutan</div>
    <div class="page-sub">Periksa hierarki, ringkasan transisi, &amp; kandidat bubar di bawah ini sebelum konfirmasi. Belum ada yang tersimpan ke database.</div>
</div>

<div class="mode-banner">
    ✓ File tervalidasi — {{ $totalUnit }} unit siap disimpan sebagai versi <strong>draft</strong> relatif terhadap Versi Dasar {{ $versiDasar->nomor_sk ?? '-' }}.
</div>

@if($warnings->isNotEmpty())
<div class="warn-banner">
    <svg viewBox="0 0 24 24" width="16" height="16" stroke="#92400e" fill="none" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
    <div>
        <div>{{ $warnings->count() }} peringatan (tidak memblokir commit) — mohon diperiksa manual:</div>
        <ul style="margin:6px 0 0 18px;font-weight:400;">
            @foreach($warnings as $w)
            <li>Baris Excel {{ $w['baris'] }}: {{ $w['pesan'] }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@if($hierarkiErrors)
<div class="danger-banner">
    <svg viewBox="0 0 24 24" width="16" height="16" stroke="#dc2626" fill="none" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        <div>Hierarki parent belum valid — commit diblokir sampai ini diperbaiki di file, upload ulang:</div>
        <ul style="margin:6px 0 0 18px;font-weight:400;">
            @foreach($hierarkiErrors as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="info-card">
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Nomor SK</div>
            <div class="info-val">{{ $header['nomor_sk'] }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal SK</div>
            <div class="info-val">{{ \Carbon\Carbon::parse($header['tanggal_sk'])->translatedFormat('d F Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Mulai Berlaku</div>
            <div class="info-val">{{ \Carbon\Carbon::parse($header['tanggal_mulai_berlaku'])->translatedFormat('d F Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Versi Dasar</div>
            <div class="info-val">{{ $versiDasar->nomor_sk ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Unit</div>
            <div class="info-val">{{ $totalUnit }}</div>
        </div>
    </div>
    @if(!empty($header['keterangan']))
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f3f4f6;">
        <div class="info-label" style="margin-bottom:4px;">Keterangan</div>
        <div style="font-size:13px;color:#374151;">{{ $header['keterangan'] }}</div>
    </div>
    @endif
</div>

<div class="ringkasan-grid">
    @foreach($labelJenis as $key => $label)
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ $ringkasanTransisi[$key] ?? 0 }}</div>
        <div class="ringkasan-label">{{ $label }}</div>
    </div>
    @endforeach
    <div class="ringkasan-item">
        <div class="ringkasan-num">{{ $bubarCandidates->count() }}</div>
        <div class="ringkasan-label">Kandidat Bubar</div>
    </div>
</div>

<form method="POST" action="{{ route('organisasi.struktur.import-lanjutan.confirm') }}" id="confirmForm">
    @csrf

    @if($perluReview->isNotEmpty())
    <div class="section-card warn">
        <div class="section-card-title warn">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="#92400e" fill="none" stroke-width="2"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            PERLU_REVIEW — {{ $perluReview->count() }} baris kosong tidak ketemu padanannya di Versi Dasar
        </div>
        <div class="section-card-sub">Baris ini jenis_transisi-nya kosong (kandidat lanjut) tapi nama+level-nya tidak cocok tepat 1 unit di Versi Dasar. Commit diblokir sampai semua baris ini diselesaikan — centang "Anggap Baru" kalau memang ini unit baru, atau batalkan &amp; upload ulang dengan jenis_transisi dijelaskan eksplisit.</div>
        @foreach($perluReview as $row)
        <div class="review-row">
            <span class="review-badge">{{ $row['perlu_review_reason'] === 'ambigu' ? 'Ambigu' : 'Tidak Ada Match' }}</span>
            <div>
                <strong>{{ $row['nama_unit'] }}</strong> ({{ ucfirst($row['level']) }}) — baris Excel {{ $row['baris'] }}
            </div>
            <label>
                <input type="checkbox" name="anggap_baru[{{ $row['kode_sementara'] }}]" value="1" class="js-review-checkbox">
                Anggap Baru
            </label>
        </div>
        @endforeach
    </div>
    @endif

    <div class="section-card danger">
        <div class="section-card-title danger">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="#dc2626" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Kandidat Bubar — {{ $bubarCandidates->count() }} unit di Versi Dasar tidak muncul di file ini
        </div>
        <div class="section-card-sub">Unit ini tidak ketemu sebagai hasil lanjut otomatis maupun sebagai unit_versi_sebelumnya di baris manapun. Default tercentang (akan dicatat bubar). Un-check kalau ternyata kelewat harus ditandai transisi lain (batalkan &amp; upload ulang).</div>
        @forelse($bubarCandidates as $b)
        <div class="bubar-row">
            <div><strong>{{ $b['nama_unit'] }}</strong> ({{ ucfirst($b['level']) }})</div>
            <label>
                <input type="checkbox" name="bubar[]" value="{{ $b['unit_organisasi_id'] }}" checked>
                Tandai Bubar
            </label>
        </div>
        @empty
        <div style="font-size:13px;color:#9ca3af;">Tidak ada kandidat bubar — seluruh unit Versi Dasar ter-cover oleh baris lanjut/rename/pindah_induk/ganti_level/pecah/gabung di file ini.</div>
        @endforelse
    </div>

    <div class="section-card">
        <div class="section-card-title">Preview Pohon Struktur Versi Baru</div>
        <div class="section-card-sub">{{ $units->count() }} unit — termasuk baris PERLU_REVIEW yang belum terselesaikan (ditampilkan sementara sebagai node biasa).</div>
    </div>

    <div x-data>
    <div class="tree-scroll-wrap">
        <div class="tree-scroll-inner">
            @if($roots->isEmpty())
                <div style="text-align:center;color:#9ca3af;padding:40px;font-size:13px;">Belum ada unit di roster ini.</div>
            @else
                @foreach($roots as $root)
                    <x-org-tree-node :node="$root" :by-parent="$byParent" :totals="$totals" />
                @endforeach
            @endif
        </div>
    </div>
    </div>

    <div class="form-actions-card">
        <a href="{{ route('organisasi.struktur.import-lanjutan') }}" class="btn-cancel">Batal &amp; Upload Ulang</a>
        <div style="text-align:right;">
            <button type="submit" class="btn-confirm" id="btnConfirm" {{ ($perluReview->isNotEmpty() || $hierarkiErrors) ? 'disabled' : '' }}>
                <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                Konfirmasi &amp; Simpan sebagai Draft
            </button>
            @if($hierarkiErrors)
            <div class="block-hint">Perbaiki hierarki parent di file terlebih dahulu.</div>
            @elseif($perluReview->isNotEmpty())
            <div class="block-hint">Selesaikan semua baris PERLU_REVIEW terlebih dahulu.</div>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('tree', {
            expanded: Object.fromEntries(@json($defaultExpandedIds).map(id => [id, true])),
            allIds: @json($allIds),
            parentMap: @json($units->pluck('parent_unit_organisasi_id', 'unit_organisasi_id')),
            namesMap: @json($units->pluck('nama_unit', 'unit_organisasi_id')),
            searchQuery: '',
            isExpanded(id) { return !!this.expanded[id]; },
            toggle(id) { this.expanded[id] = !this.expanded[id]; },
            expandAll() { this.allIds.forEach(id => { this.expanded[id] = true; }); },
            collapseAll() { this.expanded = {}; },
            matches() { return false; },
        });
    });

    @if($hierarkiErrors)
    // Hierarki tidak bisa diperbaiki lewat checkbox — tombol tetap terkunci apapun state checkbox.
    @else
    (function () {
        const btn = document.getElementById('btnConfirm');
        const reviewBoxes = Array.from(document.querySelectorAll('.js-review-checkbox'));
        if (!btn || reviewBoxes.length === 0) return;

        function refresh() {
            const allChecked = reviewBoxes.every(cb => cb.checked);
            btn.disabled = !allChecked;
        }
        reviewBoxes.forEach(cb => cb.addEventListener('change', refresh));
        refresh();
    })();
    @endif
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
