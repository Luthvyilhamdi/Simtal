@php
    use App\Services\GenealogyBandLayout;

    $colors = [
        'baseline'     => ['fill' => '#f3f4f6', 'border' => '#9ca3af'],
        'lanjut'       => ['fill' => '#f3f4f6', 'border' => '#9ca3af'],
        'baru'         => ['fill' => '#f0fdf4', 'border' => '#16a34a'],
        'rename'       => ['fill' => '#f5f3ff', 'border' => '#7c3aed'],
        'pindah_induk' => ['fill' => '#f5f3ff', 'border' => '#7c3aed'],
        'ganti_level'  => ['fill' => '#f5f3ff', 'border' => '#7c3aed'],
        'pecah'        => ['fill' => '#ecfeff', 'border' => '#0891b2'],
        'gabung'       => ['fill' => '#fff5f4', 'border' => '#f97066'],
        'bubar'        => ['fill' => '#fef2f2', 'border' => '#dc2626'],
    ];
    $shortLabels = [
        'baseline' => 'baseline', 'lanjut' => 'tetap sama', 'baru' => 'baru', 'rename' => 'rename',
        'pindah_induk' => 'pindah induk', 'ganti_level' => 'ganti level',
        'pecah' => 'pecah', 'gabung' => 'gabung', 'bubar' => 'bubar',
    ];
    $edgeColorKey = [
        'lanjut' => 'gray', 'rename' => 'purple', 'pindah_induk' => 'purple', 'ganti_level' => 'purple',
        'pecah' => 'teal', 'gabung' => 'coral', 'bubar' => 'red',
    ];
    $legendItems = [
        ['label' => 'Baseline / Lanjut', 'c' => $colors['baseline']],
        ['label' => 'Baru', 'c' => $colors['baru']],
        ['label' => 'Rename / Pindah Induk / Ganti Level', 'c' => $colors['rename']],
        ['label' => 'Pecah', 'c' => $colors['pecah']],
        ['label' => 'Gabung', 'c' => $colors['gabung']],
        ['label' => 'Bubar', 'c' => $colors['bubar']],
    ];

    $boxW = GenealogyBandLayout::BOX_W;
    $boxH = GenealogyBandLayout::BOX_H;
    $nodes = collect($graph['nodes'] ?? [])->keyBy('node_key');
    $svgW = $graph['svg_width'] ?? 0;
    $svgH = $graph['svg_height'] ?? 0;
@endphp

@push('styles')
<style>
    [x-cloak] { display:none !important; }

    .genealogy-layout { display:flex;align-items:flex-start;gap:20px; }
    .genealogy-main { flex:1;min-width:0; }
    .genealogy-legend-sidebar { flex-shrink:0;width:172px;position:sticky;top:20px;background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);padding:16px; }
    .genealogy-legend-title { font-size:11px;font-weight:700;color:#111827;text-transform:uppercase;letter-spacing:.4px;margin-bottom:12px; }
    .genealogy-legend { display:flex;flex-direction:column;gap:11px;font-size:11.5px;color:#4b5563; }
    .genealogy-legend-item { display:flex;align-items:center;gap:8px; }
    .genealogy-legend-swatch { width:14px;height:14px;border-radius:4px;border-width:1.5px;border-style:solid;flex-shrink:0; }

    .genealogy-scroll { background:white;border-radius:var(--radius);border:1px solid var(--card-border);box-shadow:var(--card-shadow);overflow:auto;padding:24px; }
    .genealogy-empty { text-align:center;color:#9ca3af;padding:40px;font-size:13px; }

    .genealogy-edge { stroke-width:1.6;fill:none; }
    .genealogy-edge-lanjut { stroke:#c7cbd1; }
    .genealogy-edge-rename, .genealogy-edge-pindah_induk, .genealogy-edge-ganti_level { stroke:#c4b5fd; }
    .genealogy-edge-pecah { stroke:#67e8f9; }
    .genealogy-edge-gabung { stroke:#fdba9d; }
    .genealogy-edge-bubar { stroke:#fca5a5; }
    .genealogy-summary-connector { stroke:#d1d5db;stroke-width:1.5;stroke-dasharray:3 3; }

    /* fill:none dgn sengaja — band cuma garis batas putus-putus, TIDAK menutupi apapun.
       Yang meng-occlude garis edge yg lewat di belakangnya adalah latar opak
       .genealogy-band-header/.genealogy-narrative-block di bawah (lihat catatan z-order
       di file .blade.php: edge digambar duluan/paling belakang, baru band+teks). Celah
       antara narasi & baris kotak node sengaja dibiarkan transparan supaya garis edge
       tetap kelihatan penuh persis di area itu (termasuk ujung anak panah). Rute garis
       sendiri ORTOGONAL (dihitung di GenealogyBandLayout::routeEdges()) — belok cuma di
       gutter kosong antar-band, jadi scr struktural memang tidak pernah masuk ke rentang
       Y band manapun secara diagonal/miring. */
    .genealogy-band-rect { fill:none;stroke:#d1d5db;stroke-width:1.3;stroke-dasharray:5 4; }
    .genealogy-band-header { font-family:inherit;font-size:11.5px;font-weight:700;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;background:#fafafa;box-sizing:border-box;padding:1px 4px;border-radius:4px; }
    .genealogy-band-badge { font-weight:700;color:#15803d;margin-left:4px; }
    .genealogy-narrative-block { font-family:inherit;background:#fafafa;box-sizing:border-box;padding:2px 4px;border-radius:4px; }
    .genealogy-narrative { font-size:11.5px;line-height:15px;color:#374151; }
    .genealogy-narrative strong { color:#111827; }
    .genealogy-keterangan { font-size:10px;line-height:13px;color:#9ca3af;font-style:italic;margin-top:1px; }

    .genealogy-node-rect { cursor:pointer;transition:filter .12s; }
    a:hover .genealogy-node-rect { filter:brightness(0.97); }
    .genealogy-node-body { width:100%;height:100%;padding:7px 9px;box-sizing:border-box;font-family:inherit;display:flex;flex-direction:column;justify-content:center;gap:2px;overflow:hidden;pointer-events:none; }
    .genealogy-node-level { font-size:9px;font-weight:600;color:#9ca3af;line-height:1.2; }
    .genealogy-node-name { font-size:11.5px;font-weight:700;color:#111827;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical; }
    .genealogy-node-subtitle { font-size:9.5px;color:#6b7280;line-height:1.3; }

    .genealogy-toggle-btn { fill:white;stroke:#9ca3af;stroke-width:1.5;cursor:pointer; }
    .genealogy-toggle-btn:hover { stroke:#15803d; }
    .genealogy-toggle-icon { font-size:13px;font-weight:700;fill:#374151;pointer-events:none;user-select:none;font-family:inherit; }
    .genealogy-summary { cursor:pointer; }
    .genealogy-summary rect { fill:#f9fafb;stroke:#9ca3af;stroke-width:1.5;stroke-dasharray:4 3; }
    .genealogy-summary-body { width:100%;height:100%;display:flex;align-items:center;justify-content:center;text-align:center;font-size:10.5px;font-weight:600;color:#374151;padding:6px;box-sizing:border-box; }

    @media (max-width:900px) {
        .genealogy-layout { flex-direction:column; }
        .genealogy-legend-sidebar { width:100%;position:static; }
        .genealogy-legend { flex-direction:row;flex-wrap:wrap;gap:12px; }
    }
</style>
@endpush

<div class="genealogy-layout">
<div class="genealogy-main">
@if(empty($graph['nodes']))
<div class="genealogy-empty">Tidak ada data silsilah untuk unit ini.</div>
@else
<div class="genealogy-scroll" x-cloak>
    <svg width="{{ $svgW }}" height="{{ $svgH }}" viewBox="0 0 {{ $svgW }} {{ $svgH }}">
        <defs>
            @foreach(['gray' => '#c7cbd1', 'purple' => '#c4b5fd', 'teal' => '#67e8f9', 'coral' => '#fdba9d', 'red' => '#fca5a5'] as $key => $fill)
            <marker id="genealogy-arrow-{{ $key }}" viewBox="0 0 10 10" refX="8.5" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                <path d="M0,0 L10,5 L0,10 z" fill="{{ $fill }}"></path>
            </marker>
            @endforeach
        </defs>

        {{-- ===== Layer 1 (paling belakang): garis edge (rute ortogonal, sudah dihitung
             di GenealogyBandLayout) + garis toggle-collapse. Ditaruh DI BAWAH band supaya
             segmen yg lewat di belakang header/narasi band manapun ke-occlude latar
             opaknya (Layer 2), bukan menabrak teksnya. ===== --}}
        @foreach($graph['edges'] as $edge)
            @php
                $markerKey = $edgeColorKey[$edge['jenis_transisi']] ?? 'purple';
                $edgeVisible = "!\$store.genealogy.isHidden('{$edge['from']}') && !\$store.genealogy.isHidden('{$edge['to']}')";
            @endphp
            <path d="{{ $edge['d'] }}"
                  class="genealogy-edge genealogy-edge-{{ $edge['jenis_transisi'] }}"
                  marker-end="url(#genealogy-arrow-{{ $markerKey }})"
                  x-show="{{ $edgeVisible }}" x-cloak></path>
        @endforeach

        @php
            $toggleGeom = [];
            foreach ($graph['toggles'] as $toggle) {
                $tn = $nodes[$toggle['node_key']];
                $cx = $tn['x'] + $boxW / 2;

                if ($toggle['type'] === 'pecah') {
                    // Anak (hasil pecah) lebih BARU -> band lebih atas (y lebih kecil).
                    $cy = $tn['y'];
                    $connected = collect($graph['edges'])->where('from', $toggle['node_key'])->where('jenis_transisi', 'pecah')
                        ->map(fn ($e) => $nodes[$e['to']]);
                } else {
                    // Induk (sebelum gabung) lebih LAMA -> band lebih bawah (y lebih besar).
                    $cy = $tn['y'] + $boxH;
                    $connected = collect($graph['edges'])->where('to', $toggle['node_key'])->where('jenis_transisi', 'gabung')
                        ->map(fn ($e) => $nodes[$e['from']]);
                }

                $toggleGeom[] = $toggle + [
                    'cx' => $cx, 'cy' => $cy,
                    'summary_y' => $connected->isEmpty() ? $cy : $connected->avg('y'),
                ];
            }
        @endphp
        @foreach($toggleGeom as $toggle)
            @php $collapsedExpr = "\$store.genealogy.collapsed['{$toggle['id']}']"; @endphp
            <line x1="{{ $toggle['cx'] }}" y1="{{ $toggle['cy'] }}" x2="{{ $toggle['cx'] }}" y2="{{ $toggle['summary_y'] }}"
                  class="genealogy-summary-connector"
                  x-show="{{ $collapsedExpr }} && !$store.genealogy.isHidden('{{ $toggle['node_key'] }}')" x-cloak></line>
        @endforeach

        {{-- ===== Layer 2: band (dashed outline TANPA fill + kartu header/narasi ber-latar
             opak) — bagian "kartu teks" ini yg meng-occlude Layer 1 di atas, sisanya
             (celah gap-ke-node & baris kotak node) dibiarkan transparan. ===== --}}
        @foreach($graph['bands'] as $band)
            <rect class="genealogy-band-rect" x="8" y="{{ $band['y'] }}" width="{{ $svgW - 16 }}" height="{{ $band['height'] }}" rx="12"></rect>

            <foreignObject x="24" y="{{ $band['header_y'] }}" width="{{ $svgW - 48 }}" height="20">
                <div xmlns="http://www.w3.org/1999/xhtml" class="genealogy-band-header">
                    SK {{ $band['nomor_sk'] ?? '-' }} &middot; {{ $band['tanggal'] ? $band['tanggal']->translatedFormat('d F Y') : '-' }}
                    @if($band['badge'])
                    <span class="genealogy-band-badge">({{ $band['badge'] }})</span>
                    @endif
                </div>
            </foreignObject>

            @foreach($band['lines'] as $line)
            <foreignObject x="24" y="{{ $band['narrative_y'] + $line['y_offset'] }}" width="{{ $svgW - 48 }}" height="{{ $line['block_height'] }}">
                <div xmlns="http://www.w3.org/1999/xhtml" class="genealogy-narrative-block">
                    <div class="genealogy-narrative">{!! $line['html'] !!}</div>
                    @if($line['keterangan'])
                    <div class="genealogy-keterangan">Catatan: {{ $line['keterangan'] }}</div>
                    @endif
                </div>
            </foreignObject>
            @endforeach
        @endforeach

        {{-- ===== Layer 3: node (selalu penuh terlihat), 3 baris: level / nama / subtitle ===== --}}
        @foreach($graph['nodes'] as $node)
            @php
                $c = $colors[$node['jenis_event']] ?? $colors['baseline'];
                $subtitle = 'Formasi ' . $node['mc_formasi'] . ' &middot; ' . ($shortLabels[$node['jenis_event']] ?? $node['jenis_event']);
                if ($node['is_anchor']) {
                    $subtitle .= ' &middot; Anda di sini';
                }
            @endphp
            <g x-show="!$store.genealogy.isHidden('{{ $node['node_key'] }}')" x-cloak>
                <a href="{{ route('organisasi.struktur.show', $node['struktur_organisasi_versi_id']) }}">
                    <rect x="{{ $node['x'] }}" y="{{ $node['y'] }}" width="{{ $boxW }}" height="{{ $boxH }}" rx="10"
                          fill="{{ $c['fill'] }}" stroke="{{ $c['border'] }}"
                          stroke-width="{{ $node['is_anchor'] ? 3.5 : 1.5 }}" class="genealogy-node-rect"></rect>
                    <foreignObject x="{{ $node['x'] }}" y="{{ $node['y'] }}" width="{{ $boxW }}" height="{{ $boxH }}">
                        <div xmlns="http://www.w3.org/1999/xhtml" class="genealogy-node-body">
                            <div class="genealogy-node-level">{{ ucfirst($node['level']) }}</div>
                            <div class="genealogy-node-name">{{ $node['nama_unit'] }}</div>
                            <div class="genealogy-node-subtitle">{!! $subtitle !!}</div>
                        </div>
                    </foreignObject>
                </a>
            </g>
        @endforeach

        {{-- ===== Layer 4 (paling depan): tombol toggle + kartu ringkasan collapse ===== --}}
        @foreach($toggleGeom as $toggle)
            @php $collapsedExpr = "\$store.genealogy.collapsed['{$toggle['id']}']"; @endphp
            <g x-show="!$store.genealogy.isHidden('{{ $toggle['node_key'] }}')" x-cloak>
                <g class="genealogy-summary" x-show="{{ $collapsedExpr }}" x-cloak
                   @click="$store.genealogy.toggle('{{ $toggle['id'] }}')">
                    <rect x="{{ $toggle['cx'] - $boxW / 2 }}" y="{{ $toggle['summary_y'] }}" width="{{ $boxW }}" height="{{ $boxH }}" rx="10"></rect>
                    <foreignObject x="{{ $toggle['cx'] - $boxW / 2 }}" y="{{ $toggle['summary_y'] }}" width="{{ $boxW }}" height="{{ $boxH }}">
                        <div xmlns="http://www.w3.org/1999/xhtml" class="genealogy-summary-body">
                            +{{ $toggle['hidden_count'] }} versi tersembunyi
                        </div>
                    </foreignObject>
                </g>

                <circle cx="{{ $toggle['cx'] }}" cy="{{ $toggle['cy'] }}" r="10" class="genealogy-toggle-btn"
                        @click="$store.genealogy.toggle('{{ $toggle['id'] }}')"></circle>
                <text x="{{ $toggle['cx'] }}" y="{{ $toggle['cy'] + 4 }}" text-anchor="middle" class="genealogy-toggle-icon"
                      x-text="{{ $collapsedExpr }} ? '+' : '−'" @click="$store.genealogy.toggle('{{ $toggle['id'] }}')"></text>
            </g>
        @endforeach
    </svg>
</div>
@endif
</div>

<div class="genealogy-legend-sidebar">
    <div class="genealogy-legend-title">Keterangan Warna</div>
    <div class="genealogy-legend">
        @foreach($legendItems as $item)
        <div class="genealogy-legend-item">
            <span class="genealogy-legend-swatch" style="background:{{ $item['c']['fill'] }};border-color:{{ $item['c']['border'] }};"></span>
            {{ $item['label'] }}
        </div>
        @endforeach
    </div>
</div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('genealogy', {
            collapsed: @json(collect($graph['toggles'] ?? [])->pluck('default_collapsed', 'id')),
            hiddenByToggle: @json(collect($graph['toggles'] ?? [])->pluck('hidden_node_keys', 'id')),

            isHidden(nodeKey) {
                for (const id in this.hiddenByToggle) {
                    if (this.collapsed[id] && this.hiddenByToggle[id].includes(nodeKey)) return true;
                }
                return false;
            },
            toggle(id) {
                this.collapsed[id] = !this.collapsed[id];
            },
        });
    });
</script>
@endpush
