@props(['node', 'byParent', 'totals'])

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
@endphp

<div class="org-node">
    <div id="org-node-{{ $node->unit_organisasi_id }}" class="org-box {{ $isLeafLevel ? 'org-box-leaf' : '' }}" x-data="{ get expanded() { return $store.tree.isExpanded({{ $node->unit_organisasi_id }}) } }" :class="{ 'org-box-highlight': $store.tree.matches({{ $node->unit_organisasi_id }}) }">
        <div class="org-box-top">
            <span class="org-box-level">{{ ucfirst($node->level) }}</span>
            @if($children->isNotEmpty())
            <button type="button" class="org-toggle" @click="$store.tree.toggle({{ $node->unit_organisasi_id }})" :title="expanded ? 'Ciutkan' : 'Perluas'">
                <span x-show="expanded" x-cloak>&minus;</span>
                <span x-show="!expanded" x-cloak>+</span>
            </button>
            @endif
        </div>
        <div class="org-box-name">{{ $node->nama_unit }}</div>
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
                <x-org-tree-node :node="$child" :by-parent="$byParent" :totals="$totals" />
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
