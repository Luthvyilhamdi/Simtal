<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Lapisan PRESENTASI di atas output GenealogyGraphBuilder::build() — TIDAK menyentuh
 * traversal/lane (itu tetap tanggung jawab GenealogyGraphBuilder, sudah tervalidasi).
 * Kelas ini murni mengubah cara graph yg SAMA itu ditampilkan:
 *   1. Kelompokkan node per tier jadi "band" horizontal (versi terbaru di ATAS).
 *   2. Generate narasi otomatis per band (+ keterangan manual dari kolom
 *      unit_organisasi_transisi.keterangan kalau ada isinya).
 *   3. Hitung tinggi tiap band scr dinamis (estimasi word-wrap berbasis karakter) &
 *      timpa koordinat Y node supaya band di bawahnya ikut bergeser.
 *   4. Kalau node terakhir hasil traversal builder BUKAN di versi terbaru sistem (unit
 *      stabil tanpa event lanjutan), tambahkan 1 band sintetis "tetap sama" di paling
 *      atas mewakili versi terbaru — plus jaring pengaman anomali data (lihat
 *      checkAnomalies()).
 *   5. Hitung rute garis edge ORTOGONAL (Manhattan/L-shape): belok horizontal HANYA di
 *      gutter (celah kosong antar-band), lurus vertikal selama masih di dalam rentang Y
 *      1 band — supaya tidak pernah menabrak teks narasi/header band manapun.
 *
 * Koordinat X (lane) node ASLI dari builder SAMA SEKALI TIDAK diubah — itu murni hasil
 * algoritma lane yg sudah tervalidasi, cuma dipakai apa adanya (kecuali utk node
 * SINTETIS baru di poin 4, yg meniru X unit yg sama krn representasi carry-over murni).
 */
class GenealogyBandLayout
{
    const BOX_W = 150;
    const BOX_H = 76;
    const LANE_SPACING = 180; // HARUS sama dgn formula x di GenealogyGraphBuilder
    const LEFT_MARGIN = 40;   // HARUS sama dgn formula x di GenealogyGraphBuilder
    const RIGHT_MARGIN = 40;

    const TOP_MARGIN = 30;
    const BOTTOM_MARGIN = 30;
    const BAND_PADDING_TOP = 16;
    const BAND_PADDING_BOTTOM = 20;
    const HEADER_HEIGHT = 18;
    const HEADER_GAP = 8;
    const NARRATIVE_LINE_HEIGHT = 15;
    const KETERANGAN_LINE_HEIGHT = 13;
    const BLOCK_GAP = 5;
    const NARRATIVE_TO_NODE_GAP = 16;
    const INTER_BAND_GAP = 44;
    const BAND_TEXT_PADDING = 24; // padding kiri+kanan dashed band tempat teks narasi
    const AVG_CHAR_WIDTH = 6.2;   // estimasi lebar rata2 karakter @ font 11.5px sans-serif
    const GUTTER_BEND_SPACING = 6; // jarak antar garis yg numpuk di gutter yg sama

    private array $snapshotIndex = [];
    private array $transisiIndex = [];
    private TransitionNarrator $narrator;
    private LeveledTransitionNarrator $leveledNarrator;

    public function layout(array $graph, Collection $finalVersions, Collection $allSnapshots, Collection $allTransisi): array
    {
        if (empty($graph['nodes'])) {
            return $graph + ['bands' => [], 'svg_width' => 0, 'svg_height' => 0, 'anomalies' => []];
        }

        $this->snapshotIndex = [];
        foreach ($allSnapshots as $s) {
            $this->snapshotIndex["{$s->unit_organisasi_id}_{$s->struktur_organisasi_versi_id}"] = [
                'nama_unit'                  => $s->nama_unit,
                'level'                      => $s->level,
                'mc_formasi'                 => $s->mc_formasi,
                'parent_unit_organisasi_id'  => $s->parent_unit_organisasi_id,
            ];
        }

        $this->transisiIndex = [];
        foreach ($allTransisi as $t) {
            $this->transisiIndex[$this->transKey($t->unit_asal_id, $t->unit_baru_id, $t->struktur_organisasi_versi_id, $t->jenis_transisi)] = $t->keterangan;
        }

        // Narasi kalimat murni didelegasikan ke TransitionNarrator (diekstrak dari kelas
        // ini persis apa adanya) supaya bisa dipakai ulang di luar Fitur A (mis. Compare).
        $this->narrator = new TransitionNarrator($this->snapshotIndex, $this->transisiIndex);

        // Varian narasi ber-prefix level, HANYA dikonsumsi oleh tab "List" Timeline 1 Unit
        // & (via partial yg sama) endpoint yg dipakainya — key 'leveled' di bawah murni
        // tambahan, tidak mengubah 'html'/'plain' yg sudah ada shg Diagram/Compare/overlay
        // riwayat tetap identik.
        $this->leveledNarrator = new LeveledTransitionNarrator($this->snapshotIndex, $this->transisiIndex);

        $firstVersiId  = $finalVersions->first()->id;
        $latestVersiId = $finalVersions->last()->id;
        $versiById     = $finalVersions->keyBy('id');

        [$nodes, $edges, $anomalies] = $this->augmentWithCurrentAndCheckAnomalies(
            $graph['nodes'], $graph['edges'], $firstVersiId, $latestVersiId
        );

        $nodesByKey = [];
        foreach ($nodes as $node) {
            $nodesByKey[$node['node_key']] = $node;
        }

        $incomingByTo = [];
        foreach ($edges as $edge) {
            $incomingByTo[$edge['to']][] = $edge;
        }

        $maxLane = max(array_column($nodes, 'lane'));
        $svgWidth = $maxLane * self::LANE_SPACING + self::LEFT_MARGIN + self::BOX_W + self::RIGHT_MARGIN;
        $narrativeWidth = max(220, $svgWidth - 2 * self::BAND_TEXT_PADDING - 16);
        $maxCharsPerLine = max(24, (int) floor($narrativeWidth / self::AVG_CHAR_WIDTH));

        $tierGroups = $this->groupNarrativeSources($nodes, $edges, $nodesByKey, $incomingByTo);

        $tierNarratives = [];
        foreach ($tierGroups as $tier => $groups) {
            foreach ($groups as $g) {
                $tierNarratives[$tier][] = $this->narrator->narrate($g) + [
                    'leveled'  => $this->leveledNarrator->narrate($g),
                    // Field TAMBAHAN (additive, tidak mengubah 'html'/'plain'/'leveled' yg
                    // sudah ada) utk konsumen yg butuh info kategori/nama-unit-utama per
                    // baris narasi TANPA parsing ulang HTML-nya — dipakai Tab List (badge
                    // warna kategori 8-skema) & rencana Tab Diagram (redesign panel mini).
                    // Overlay "Riwayat Unit" & Tab Diagram versi lama TETAP tidak berubah
                    // krn tidak membaca field2 baru ini.
                    'category' => $this->resolveNarrativeCategory($g),
                    'headline' => $this->resolveHeadline($g),
                ];
            }
        }

        $tiers = collect(array_unique(array_column($nodes, 'tier')))
            ->sortDesc()
            ->values();
        $newestTier = $tiers->first();

        $bands = [];
        $cumulativeY = self::TOP_MARGIN;

        foreach ($tiers as $tier) {
            $rawLines = $tierNarratives[$tier] ?? [
                $this->narrator->narrate(['kind' => 'fallback']) + [
                    'leveled'  => $this->leveledNarrator->narrate(['kind' => 'fallback']),
                    'category' => null,
                    'headline' => null,
                ],
            ];

            // Precompute posisi & tinggi tiap baris narasi (relatif thd awal blok narasi
            // band ini) supaya Blade tinggal render tanpa perlu hitung ulang word-wrap.
            $lines = [];
            $offset = 0;
            foreach ($rawLines as $line) {
                $mainHeight = $this->wrapLineCount($line['plain'], $maxCharsPerLine) * self::NARRATIVE_LINE_HEIGHT;
                $ketHeight  = $line['keterangan']
                    ? $this->wrapLineCount($line['keterangan'], $maxCharsPerLine) * self::KETERANGAN_LINE_HEIGHT
                    : 0;
                $blockHeight = $mainHeight + $ketHeight;

                $lines[] = $line + [
                    'y_offset'          => $offset,
                    'main_height'       => $mainHeight,
                    'keterangan_height' => $ketHeight,
                    'block_height'      => $blockHeight,
                ];

                $offset += $blockHeight + self::BLOCK_GAP;
            }
            $narrativeHeight = $offset;

            $bandHeight = self::BAND_PADDING_TOP + self::HEADER_HEIGHT + self::HEADER_GAP
                + $narrativeHeight + self::NARRATIVE_TO_NODE_GAP
                + self::BOX_H + self::BAND_PADDING_BOTTOM;

            $anyNodeInTier = null;
            foreach ($nodes as $node) {
                if ($node['tier'] === $tier) {
                    $anyNodeInTier = $node;
                    break;
                }
            }
            $versiId = $anyNodeInTier['struktur_organisasi_versi_id'];
            $versi   = $versiById->get($versiId);

            $nodeRowY = $cumulativeY + self::BAND_PADDING_TOP + self::HEADER_HEIGHT + self::HEADER_GAP
                + $narrativeHeight + self::NARRATIVE_TO_NODE_GAP;

            foreach ($nodesByKey as $key => $node) {
                if ($node['tier'] === $tier) {
                    $nodesByKey[$key]['y'] = $nodeRowY;
                }
            }

            $isLatestVersion = $versiId === $latestVersiId;
            $isTopBand       = $tier === $newestTier;
            $badge = null;
            if ($isTopBand) {
                if ($isLatestVersion) {
                    $badge = 'terbaru';
                } elseif ($anyNodeInTier['jenis_event'] !== 'bubar') {
                    $badge = 'aktif — belum berubah sejak versi ini';
                }
            }

            $bands[] = [
                'tier'                         => $tier,
                'struktur_organisasi_versi_id' => $versiId,
                'y'           => $cumulativeY,
                'height'      => $bandHeight,
                'header_y'    => $cumulativeY + self::BAND_PADDING_TOP,
                'narrative_y' => $cumulativeY + self::BAND_PADDING_TOP + self::HEADER_HEIGHT + self::HEADER_GAP,
                'nomor_sk'    => $versi?->nomor_sk,
                'tanggal'     => $versi?->tanggal_mulai_berlaku,
                'badge'       => $badge,
                'lines'       => $lines,
            ];

            $cumulativeY += $bandHeight + self::INTER_BAND_GAP;
        }

        $svgHeight = $cumulativeY - self::INTER_BAND_GAP + self::BOTTOM_MARGIN;

        $bandsByTier = [];
        foreach ($bands as $b) {
            $bandsByTier[$b['tier']] = $b;
        }
        $routedEdges = $this->routeEdges($edges, $nodesByKey, $bandsByTier);

        return [
            'nodes'              => array_values($nodesByKey),
            'edges'              => $routedEdges,
            'toggles'            => $graph['toggles'],
            'bands'              => $bands,
            'svg_width'          => $svgWidth,
            'svg_height'         => $svgHeight,
            'max_chars_per_line' => $maxCharsPerLine,
            'anomalies'          => $anomalies,
        ];
    }

    /**
     * (a) Kalau node PALING ATAS hasil traversal builder bukan di versi terbaru sistem
     *     dan bukan node bubar (unit-nya masih hidup, cuma kebetulan tidak ada event
     *     lanjutan tercatat) — tambahkan 1 node+edge SINTETIS ("lanjut") mewakili versi
     *     terbaru, datanya diambil dari snapshot ASLI unit itu di versi terbaru (bukan
     *     cuma di-copy dari node lama, supaya kalau ternyata ada drift data ketahuan).
     *     Kalau snapshot itu TIDAK ADA (padahal harusnya ada krn tidak ada event bubar/
     *     pecah/gabung tercatat) — itu ANOMALI, dilaporkan, TIDAK memaksakan node palsu.
     * (b) Cross-check simetris: node berlabel 'baseline' seharusnya SELALU persis di versi
     *     paling awal sistem (ini seharusnya otomatis benar krn constructionnya di
     *     GenealogyGraphBuilder, tapi tetap divalidasi eksplisit di sini sbg jaring
     *     pengaman independen).
     */
    private function augmentWithCurrentAndCheckAnomalies(array $nodes, array $edges, int $firstVersiId, int $latestVersiId): array
    {
        $anomalies = [];

        foreach ($nodes as $node) {
            if ($node['jenis_event'] === 'baseline' && $node['struktur_organisasi_versi_id'] !== $firstVersiId) {
                $anomalies[] = "Node '{$node['nama_unit']}' (unit_organisasi_id={$node['unit_organisasi_id']}) berlabel "
                    . "'baseline' tapi struktur_organisasi_versi_id-nya ({$node['struktur_organisasi_versi_id']}) bukan "
                    . "versi paling awal sistem ({$firstVersiId}).";
            }
        }

        $topTier = max(array_column($nodes, 'tier'));
        $topTierNodes = array_values(array_filter($nodes, fn ($n) => $n['tier'] === $topTier));
        $topBandVersiId = $topTierNodes[0]['struktur_organisasi_versi_id'];

        if ($topBandVersiId === $latestVersiId) {
            return [$nodes, $edges, $anomalies];
        }

        $syntheticTier = $topTier + 1;

        foreach ($topTierNodes as $node) {
            if ($node['jenis_event'] === 'bubar') {
                continue; // unit sudah mati, tidak ada "versi terkini" utk digambar
            }

            $unitId = $node['unit_organisasi_id'];
            $snap = $this->snapshotIndex["{$unitId}_{$latestVersiId}"] ?? null;

            if (!$snap) {
                $anomalies[] = "Unit '{$node['nama_unit']}' (unit_organisasi_id={$unitId}) tidak ditemukan di snapshot "
                    . "versi terbaru sistem (struktur_organisasi_versi_id={$latestVersiId}) meski node terakhir "
                    . "tercatatnya (versi {$node['struktur_organisasi_versi_id']}) bukan event bubar — kemungkinan "
                    . 'data tidak lengkap, band "versi terkini" TIDAK ditambahkan utk unit ini.';
                continue;
            }

            $syntheticKey = "{$unitId}_v{$latestVersiId}";
            $nodes[] = [
                'node_key'                     => $syntheticKey,
                'unit_organisasi_id'           => $unitId,
                'struktur_organisasi_versi_id' => $latestVersiId,
                'nama_unit'                    => $snap['nama_unit'],
                'level'                        => $snap['level'],
                'mc_formasi'                   => $snap['mc_formasi'],
                'jenis_event'                  => 'lanjut',
                'tier'                         => $syntheticTier,
                'lane'                         => $node['lane'],
                'x'                            => $node['x'],
                'y'                            => 0,
                'is_anchor'                    => $node['is_anchor'],
            ];
            $edges[] = [
                'from'           => $node['node_key'],
                'to'             => $syntheticKey,
                'jenis_transisi' => 'lanjut',
            ];
        }

        return [$nodes, $edges, $anomalies];
    }

    /**
     * Kelompokkan "sumber narasi" per tier: node root (baseline/baru, tanpa incoming
     * edge) masing2 jadi 1 baris; carry-over (rename/pindah_induk/ganti_level/bubar/
     * lanjut) masing2 edge jadi 1 baris; pecah digabung per sumber yg SAMA (1 baris utk
     * semua target dari 1 unit_asal_id), gabung digabung per target yg SAMA (1 baris utk
     * semua asal ke 1 unit_baru_id) — sesuai instruksi "beberapa event yg BUKAN dari
     * kejadian yg sama = baris terpisah".
     */
    private function groupNarrativeSources(array $nodes, array $edges, array $nodesByKey, array $incomingByTo): array
    {
        $tierGroups = [];

        foreach ($nodes as $node) {
            if (empty($incomingByTo[$node['node_key']] ?? [])) {
                $tierGroups[$node['tier']][] = ['kind' => 'root', 'node' => $node];
            }
        }

        $handledPecah  = [];
        $handledGabung = [];

        foreach ($edges as $edge) {
            $jenis   = $edge['jenis_transisi'];
            $toNode  = $nodesByKey[$edge['to']];
            $fromNode = $nodesByKey[$edge['from']];

            if (in_array($jenis, ['rename', 'pindah_induk', 'ganti_level', 'bubar', 'lanjut'], true)) {
                $tierGroups[$toNode['tier']][] = ['kind' => 'carryover', 'jenis' => $jenis, 'from' => $fromNode, 'to' => $toNode];
                continue;
            }

            if ($jenis === 'pecah') {
                if (isset($handledPecah[$edge['from']])) {
                    continue;
                }
                $handledPecah[$edge['from']] = true;
                $targets = [];
                foreach ($edges as $e2) {
                    if ($e2['from'] === $edge['from'] && $e2['jenis_transisi'] === 'pecah') {
                        $targets[] = $nodesByKey[$e2['to']];
                    }
                }
                $tierGroups[$toNode['tier']][] = ['kind' => 'pecah', 'from' => $fromNode, 'targets' => $targets];
                continue;
            }

            if ($jenis === 'gabung') {
                if (isset($handledGabung[$edge['to']])) {
                    continue;
                }
                $handledGabung[$edge['to']] = true;
                $sources = [];
                foreach ($edges as $e2) {
                    if ($e2['to'] === $edge['to'] && $e2['jenis_transisi'] === 'gabung') {
                        $sources[] = $nodesByKey[$e2['from']];
                    }
                }
                $tierGroups[$toNode['tier']][] = ['kind' => 'gabung', 'sources' => $sources, 'to' => $toNode];
            }
        }

        return $tierGroups;
    }

    private function transKey(?int $asalId, ?int $baruId, int $versiId, string $jenis): string
    {
        return ($asalId ?? '') . '_' . ($baruId ?? '') . '_' . $versiId . '_' . $jenis;
    }

    /**
     * Kategori transisi (skema 8-kategori: baseline/lanjut, baru, rename, pindah_induk,
     * ganti_level, pecah, gabung, bubar — sama persis dgn Compare/Fitur B) utk 1 baris
     * narasi, dipetakan dari $g['kind']/$g['jenis'] yg sudah ada (bukan hitung ulang apa2).
     * null = "baseline/lanjut" (tidak ada perubahan berarti) — dikonsumsi bareng
     * transisiCategoryColor() di helpers.php (null -> abu-abu).
     */
    private function resolveNarrativeCategory(array $g): ?string
    {
        return match (true) {
            $g['kind'] === 'root' && ($g['node']['jenis_event'] ?? null) === 'baru' => 'baru',
            $g['kind'] === 'carryover' && in_array($g['jenis'], ['rename', 'pindah_induk', 'ganti_level', 'bubar'], true) => $g['jenis'],
            $g['kind'] === 'pecah' => 'pecah',
            $g['kind'] === 'gabung' => 'gabung',
            default => null, // root/baseline, carryover/lanjut, fallback
        };
    }

    /**
     * Nama unit "utama" (sudah diberi prefix level via formatUnitLabel()) utk 1 baris
     * narasi — dipakai sbg headline terpisah dari kalimat narasi lengkap (baris judul
     * bold di atas kalimat, lihat riwayat-narasi-list-tab.blade.php). Node yg dipilih
     * per $g['kind']: root->node, carryover->to (identitas TERKINI stlh event),
     * pecah->from (sumber yg di-trace masuk ke event ini), gabung->to (hasil gabungan).
     */
    private function resolveHeadline(array $g): ?string
    {
        $node = match (true) {
            $g['kind'] === 'root' => $g['node'] ?? null,
            $g['kind'] === 'carryover' => $g['to'] ?? null,
            $g['kind'] === 'pecah' => $g['from'] ?? null,
            $g['kind'] === 'gabung' => $g['to'] ?? null,
            default => null,
        };

        return $node ? formatUnitLabel($node['nama_unit'], $node['level'] ?? null) : null;
    }

    /** Estimasi jumlah baris setelah word-wrap greedy per kata (bukan pengukuran font asli). */
    private function wrapLineCount(string $plainText, int $maxCharsPerLine): int
    {
        $plainText = trim($plainText);
        if ($plainText === '') {
            return 1;
        }

        $words = preg_split('/\s+/', $plainText);
        $lines = 1;
        $lineLen = 0;

        foreach ($words as $word) {
            $wordLen = mb_strlen($word);
            $candidateLen = $lineLen === 0 ? $wordLen : $lineLen + 1 + $wordLen;

            if ($candidateLen > $maxCharsPerLine && $lineLen > 0) {
                $lines++;
                $lineLen = $wordLen;
            } else {
                $lineLen = $candidateLen;
            }
        }

        return $lines;
    }

    /**
     * Rute tiap edge scr ORTOGONAL (Manhattan/L-shape): kalau X sumber & tujuan SAMA,
     * 1 garis vertikal lurus saja. Kalau BEDA, garis WAJIB: (1) vertikal lurus dari
     * node sumber naik sampai batas atas band-nya sendiri, (2) belok horizontal PERSIS
     * di tengah gutter (celah kosong antara band sumber & band tujuan — gutter ini
     * dijamin kosong krn bukan bagian dari band manapun), (3) vertikal lurus turun ke
     * node tujuan. Karena belokan CUMA terjadi di gutter & garis di dalam band manapun
     * SELALU lurus vertikal di X final (tidak pernah menyimpang horizontal), garis tidak
     * pernah menabrak teks header/narasi — segmen vertikal yg lewat di belakang teks
     * band-nya sendiri di-occlude scr visual oleh latar opak header/narasi (lihat CSS di
     * partial Blade), sisanya (celah antar-band & baris kotak node) memang kosong.
     *
     * Kalau >1 edge numpuk di gutter yg sama (band asal & tujuan sama persis) dgn X
     * sumber/tujuan beda, tiap garis digeser dikit (GUTTER_BEND_SPACING) biar gak
     * menyatu jadi 1 garis tebal.
     */
    private function routeEdges(array $edges, array $nodesByKey, array $bandsByTier): array
    {
        // Kelompokkan dulu edge yg BUTUH belok (X beda) per pasangan tier (gutter yg sama).
        $bendGroups = [];
        foreach ($edges as $idx => $edge) {
            $from = $nodesByKey[$edge['from']];
            $to   = $nodesByKey[$edge['to']];
            $x1 = $from['x'] + self::BOX_W / 2;
            $x2 = $to['x'] + self::BOX_W / 2;
            if (abs($x1 - $x2) >= 0.5) {
                $bendGroups["{$from['tier']}_{$to['tier']}"][] = $idx;
            }
        }

        foreach ($edges as $idx => $edge) {
            $from = $nodesByKey[$edge['from']];
            $to   = $nodesByKey[$edge['to']];
            $x1 = $from['x'] + self::BOX_W / 2; $y1 = $from['y'];
            $x2 = $to['x'] + self::BOX_W / 2;   $y2 = $to['y'] + self::BOX_H;

            if (abs($x1 - $x2) < 0.5) {
                $edges[$idx]['d'] = "M {$x1} {$y1} L {$x2} {$y2}";
                continue;
            }

            $fromBand = $bandsByTier[$from['tier']];
            $toBand   = $bandsByTier[$to['tier']];
            $gutterTop    = $toBand['y'] + $toBand['height']; // batas bawah band tujuan
            $gutterBottom = $fromBand['y'];                   // batas atas band sumber
            $bendY = ($gutterTop + $gutterBottom) / 2;

            $gutterKey = "{$from['tier']}_{$to['tier']}";
            $siblings = $bendGroups[$gutterKey] ?? [$idx];
            if (count($siblings) > 1) {
                $position = array_search($idx, $siblings, true);
                $offset = ($position - (count($siblings) - 1) / 2) * self::GUTTER_BEND_SPACING;
                $bendY += $offset;
            }

            $edges[$idx]['d'] = "M {$x1} {$y1} L {$x1} {$bendY} L {$x2} {$bendY} L {$x2} {$y2}";
        }

        return $edges;
    }
}
