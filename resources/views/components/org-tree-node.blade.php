{{--
    $boxHeight (px): tinggi TETAP .org-box di halaman pemanggil (default 160, cocok dgn
    tree.blade.php & import-lanjutan-preview.blade.php). Compare.blade.php meng-override
    ke 240 krn ada 1 konten TAMBAHAN yg cuma ada di halaman itu (narasi diff ringkas,
    di-inject via CSS ::after ke .org-box-stats, sampai ~78 karakter mentah/3 baris) yg tidak
    ada di 2 konsumen lain — kalau dipaksa pakai budget 160 yg sama, teks itu akan
    overflow ke luar box & bisa nabrak baris tier berikutnya. NILAI INI HARUS SELALU
    SAMA PERSIS dgn nilai `.org-box { height: ... }` di CSS halaman pemanggil, krn
    dipakai utk hitung tinggi .org-tier-spacer (lihat $tierRowHeight di bawah) — kalau
    salah satu diubah tanpa yg lain, box asli & spacer TIDAK akan lagi sama tinggi,
    balik lagi ke bug Masalah A (node se-level lintas cabang beda tinggi di layar).

    $showLevelPrefix (bool, default false): kalau true, judul nama unit di dalam box
    (.org-box-name) diberi prefix level via formatUnitLabel() (mis. "Komersil" ->
    "Direktorat Komersil") — badge level yg SUDAH ADA di atas judul (.org-box-level,
    mis. "DIREKTORAT") TIDAK berubah/dihapus, prefix ini nambah di teks judul, bukan
    gantiin badge. Default false supaya Tree View & Preview Import TIDAK berubah sama
    sekali (di luar scope fitur prefix level) — HANYA compare.blade.php yg pass true.
--}}
@props(['node', 'byParent', 'totals', 'boxHeight' => 160, 'showLevelPrefix' => false])

@php
    $children = $byParent->get($node->unit_organisasi_id, collect());
    // $totals: map unit_organisasi_id => total formasi bawahan, dihitung SEKALI di controller
    // lewat UnitOrganisasiSnapshot::totalFormasiBawahanBatch() — bukan dipanggil per-node di
    // sini lagi (dulu O(n^2) krn tiap panggilan totalFormasiBawahan() me-regroup ulang $units).
    $totalBawahan = $totals[$node->unit_organisasi_id] ?? null;
    // Bentuk "leaf" (dashed) murni berdasar data: unit tanpa anak sama sekali di versi ini,
    // apapun levelnya — hirarki riil tidak selalu 5-7 level berurutan (Bagian bisa langsung
    // ke Fungsional, Foreman bisa punya anak Fungsional atau tidak sama sekali, dst).
    $isLeafLevel = $children->isEmpty();
    // Fungsional SELALU level paling bawah (tidak pernah punya anak) — bentuknya cylinder/drum
    // apapun status leaf-nya, TIDAK pakai style leaf dashed spt level lain.
    $isFungsional = $node->level === 'fungsional';
    $boxShapeClass = $isFungsional ? 'org-box-fungsional' : ($isLeafLevel ? 'org-box-leaf' : '');

    // Perataan tier HARUS berdasar level (enum tetap 7 tingkat), BUKAN kedalaman
    // parent-child — hirarki riil suka "lompat" level (mis. Bagian -> Fungsional
    // langsung, lewat Seksi & Foreman). $tierGap dihitung per-anak di bawah: kalau
    // levelnya lompat >1 tingkat dari level node ini, sisipkan 1 div ".org-tier-spacer"
    // (garis penghubung pass-through, tanpa box) SEBELUM box anak supaya kedalaman DOM
    // node itu tetap sejajar levelnya scr konsisten di seluruh chart — bukan cuma
    // sejajar sesama saudara langsung. Fallback aman ke gap=1 (render spt biasa, tanpa
    // spacer) kalau level node/anak di luar 7 nilai enum yg dikenal (data tak terduga).
    $levelOrder = ['direktorat', 'kompartemen', 'departemen', 'bagian', 'seksi', 'foreman', 'fungsional'];
    $tierOfThisNode = array_search($node->level, $levelOrder, true);

    // Tinggi 1 baris tier = $boxHeight (lihat catatan @props di atas) + DUA gap
    // konektor yg saling menumpuk: 28px dari `.org-children`'s padding-top (trunk dari
    // box parent turun ke baris spine horizontal) + 28px LAGI dari `.org-child-branch`'s
    // padding-top (spine turun ke box anak) = 56px total. Sebelum ini nilainya cuma
    // ditaksir (128px, lalu sempat "dibenarkan" jadi boxHeight+28 yg TERNYATA masih
    // kurang 28px krn ke-lewat 1 gap) — itu akar penyebab kenapa node lintas-cabang
    // masih bisa beda tinggi meski jumlah tier yg dilompati sudah benar.
    $tierRowHeight = $boxHeight + 56;
@endphp

<div class="org-node">
    <div id="org-node-{{ $node->unit_organisasi_id }}" class="org-box {{ $boxShapeClass }}" x-data="{ get expanded() { return $store.tree.isExpanded({{ $node->unit_organisasi_id }}) } }" :class="{ 'org-box-highlight': $store.tree.matches({{ $node->unit_organisasi_id }}) }">
        <div class="org-box-top">
            <span class="org-box-level">{{ ucfirst($node->level) }}</span>
            <div class="org-box-top-actions">
                {{-- Judul overlay "Riwayat Unit" SELALU dapat prefix level, TERLEPAS dari
                     $showLevelPrefix (yg cuma ngatur judul BOX ini) — itu keputusan
                     terpisah yg berlaku ke overlay itu sendiri di semua entry point. --}}
                <a href="javascript:void(0)" class="org-history-btn" title="Lihat Riwayat Unit"
                   @click.stop="$store.riwayatOverlay.openPanel({{ $node->unit_organisasi_id }}, {{ \Illuminate\Support\Js::from(formatUnitLabel($node->nama_unit, $node->level)) }})">
                    <svg viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </a>
                @if($children->isNotEmpty())
                <button type="button" class="org-toggle" @click="$store.tree.toggle({{ $node->unit_organisasi_id }})" :title="expanded ? 'Ciutkan' : 'Perluas'">
                    <span x-show="expanded" x-cloak>&minus;</span>
                    <span x-show="!expanded" x-cloak>+</span>
                </button>
                @endif
            </div>
        </div>
        <div class="org-box-name">{{ $showLevelPrefix ? formatUnitLabel($node->nama_unit, $node->level) : $node->nama_unit }}</div>
        <div class="org-box-stats">
            <div class="org-stat">
                <span class="org-stat-label">Formasi Unit</span>
                <span class="org-stat-val">{{ $node->mc_formasi }}</span>
            </div>
            <div class="org-stat">
                <span class="org-stat-label">Total Bawahan</span>
                <span class="org-stat-val">{{ is_null($totalBawahan) ? '–' : $totalBawahan }}</span>
            </div>
        </div>
        @if($children->isNotEmpty())
        <div class="org-child-count" x-show="!expanded" x-cloak>
            {{ $children->count() }} unit di bawahnya disembunyikan
        </div>
        @endif
    </div>

    @if($children->isNotEmpty())
    <div class="org-children" x-data="{ get expanded() { return $store.tree.isExpanded({{ $node->unit_organisasi_id }}) } }" x-show="expanded" x-cloak>
        <div class="org-children-inner">
            @foreach($children as $child)
            <div class="org-child-branch">
                @php
                    $tierOfChild = array_search($child->level, $levelOrder, true);
                    $tierGap = ($tierOfChild === false || $tierOfThisNode === false)
                        ? 1
                        : max(1, $tierOfChild - $tierOfThisNode);
                @endphp
                @if($tierGap > 1)
                <div class="org-tier-spacer" style="height: {{ ($tierGap - 1) * $tierRowHeight }}px"></div>
                @endif
                <x-org-tree-node :node="$child" :by-parent="$byParent" :totals="$totals" :box-height="$boxHeight" :show-level-prefix="$showLevelPrefix" />
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
