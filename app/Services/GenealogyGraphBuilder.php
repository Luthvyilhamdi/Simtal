<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Membangun graph silsilah (nodes + edges + lane layout) utk 1 unit_organisasi_id,
 * ditelusuri DUA ARAH (maju via pecah/gabung/bubar, mundur via unit_baru_id) sampai
 * seluruh connected component ditemukan & di-dedup. Murni in-memory — TIDAK query
 * database sendiri, dikasih collection final version/snapshot/transisi yg SUDAH
 * di-load controller (reuse dari StrukturOrganisasiVersiController::buildUnitLedger())
 * supaya tidak nambah query di luar yg sudah ada.
 */
class GenealogyGraphBuilder
{
    const JENIS_IDENTITAS_LANJUT = ['rename', 'pindah_induk', 'ganti_level'];
    const JENIS_FORK = ['pecah', 'gabung'];

    private array $tierByVersiId = [];
    private ?int $firstVersiId = null;
    private array $snapshotByUnitVersi = [];
    private Collection $transisiByAsal;
    private Collection $transisiByBaru;

    /** @var array<int, array> unit_organisasi_id => chain node[] (urut tier), memoized */
    private array $unitChains = [];

    private array $nodes = []; // node_key => node array
    private array $edges = []; // list of edge array

    public function build(int $anchorUnitId, Collection $finalVersions, Collection $allSnapshots, Collection $allTransisi): array
    {
        $this->tierByVersiId = array_flip($finalVersions->pluck('id')->all());
        $this->firstVersiId  = $finalVersions->first()?->id;
        $this->snapshotByUnitVersi = $allSnapshots
            ->keyBy(fn ($s) => $s->unit_organisasi_id . '_' . $s->struktur_organisasi_versi_id)
            ->map(fn ($s) => ['nama_unit' => $s->nama_unit, 'level' => $s->level, 'mc_formasi' => $s->mc_formasi])
            ->all();
        $this->transisiByAsal = $allTransisi->groupBy('unit_asal_id');
        $this->transisiByBaru = $allTransisi->groupBy('unit_baru_id');

        $this->unitChains = [];
        $this->nodes = [];
        $this->edges = [];

        $this->buildUnitChain($anchorUnitId);

        if (empty($this->nodes)) {
            return ['nodes' => [], 'edges' => [], 'toggles' => []];
        }

        foreach ($this->nodes as $key => $node) {
            $this->nodes[$key]['is_anchor'] = $node['unit_organisasi_id'] === $anchorUnitId;
        }

        $this->assignLanes();
        $toggles = $this->buildToggles();
        $this->applySafetyCollapse($toggles);

        foreach ($this->nodes as $key => $node) {
            $this->nodes[$key]['x'] = $node['lane'] * 180 + 40;
            $this->nodes[$key]['y'] = $node['tier'] * 120 + 40;
        }

        return [
            'nodes'   => array_values($this->nodes),
            'edges'   => $this->edges,
            'toggles' => array_values($toggles),
        ];
    }

    /**
     * Bangun (memoized) rantai node milik 1 unit_organisasi_id: node kelahiran + tiap
     * milestone rename/pindah_induk/ganti_level + node terminal bubar (kalau ada), lalu
     * rekursi DUA ARAH: mundur ke unit_asal (kelahiran via pecah/gabung), maju ke
     * unit_baru (fork keluar via pecah/gabung). Memoized by unit_organisasi_id supaya
     * BFS dari arah manapun tidak memproses 1 unit dua kali & tidak infinite loop.
     *
     * Node BARU sengaja TIDAK dibuat utk setiap versi (byPass versi "lanjut" tanpa
     * perubahan) — hanya di titik kelahiran, milestone identitas-lanjut, dan bubar.
     * Pecah/gabung sbg unit_asal TIDAK membuat node baru utk unit ini sendiri, cuma jadi
     * edge dari node terakhirnya menuju node kelahiran tiap unit_baru.
     *
     * @return array<int, array> node milik unit ini, urut tier (kosong kalau unit tidak
     *                            punya data sama sekali)
     */
    private function buildUnitChain(int $unitId): array
    {
        if (isset($this->unitChains[$unitId])) {
            return $this->unitChains[$unitId];
        }

        $birthRows = $this->transisiByBaru->get($unitId, collect());

        if ($birthRows->isEmpty()) {
            // Jaring pengaman: unit tanpa baris transisi kelahiran sama sekali (tidak
            // seharusnya terjadi krn baseline import pun menulis baris 'baru', tapi kalau
            // toh data lama tidak lengkap, pakai snapshot paling awal sbg titik kelahiran).
            $prefix = $unitId . '_';
            $earliestVersiId = collect(array_keys($this->snapshotByUnitVersi))
                ->filter(fn ($k) => str_starts_with($k, $prefix))
                ->map(fn ($k) => (int) substr($k, strlen($prefix)))
                ->sortBy(fn ($vId) => $this->tierByVersiId[$vId] ?? PHP_INT_MAX)
                ->first();

            if ($earliestVersiId === null) {
                return $this->unitChains[$unitId] = [];
            }

            $birthVersiId = $earliestVersiId;
            $birthJenis   = $birthVersiId === $this->firstVersiId ? 'baseline' : 'baru';
            $parentIds    = [];
        } else {
            $birthVersiId = $birthRows->first()->struktur_organisasi_versi_id;
            $rawJenis     = $birthRows->first()->jenis_transisi;
            // Baseline import juga menulis jenis_transisi='baru' utk semua unit baris
            // pertama (lihat importConfirm()) — dibedakan dari "baru beneran belakangan"
            // lewat posisi versi, bukan isi kolom.
            $birthJenis   = ($rawJenis === 'baru' && $birthVersiId === $this->firstVersiId) ? 'baseline' : $rawJenis;
            $parentIds    = $birthRows->pluck('unit_asal_id')->filter()->unique()->values()->all();
        }

        $birthNode = $this->makeNode($unitId, $birthVersiId, $birthJenis);
        $chain     = [$birthNode];

        $milestoneRows = $this->transisiByAsal->get($unitId, collect())
            ->whereIn('jenis_transisi', self::JENIS_IDENTITAS_LANJUT)
            ->sortBy(fn ($t) => $this->tierByVersiId[$t->struktur_organisasi_versi_id] ?? PHP_INT_MAX);

        $lastNode = $birthNode;
        foreach ($milestoneRows as $row) {
            $node = $this->makeNode($unitId, $row->struktur_organisasi_versi_id, $row->jenis_transisi);
            $this->addEdge($lastNode['node_key'], $node['node_key'], $row->jenis_transisi);
            $chain[]  = $node;
            $lastNode = $node;
        }

        // Titik terminal bubar: tidak ada snapshot baru di versi bubar (unit sudah tidak
        // ada di roster), jadi data tampil dipakai dari node terakhir yg masih hidup.
        $bubarRow = $this->transisiByAsal->get($unitId, collect())->firstWhere('jenis_transisi', 'bubar');
        if ($bubarRow) {
            $node = $this->makeNode($unitId, $bubarRow->struktur_organisasi_versi_id, 'bubar', [
                'nama_unit'  => $lastNode['nama_unit'],
                'level'      => $lastNode['level'],
                'mc_formasi' => $lastNode['mc_formasi'],
            ]);
            $this->addEdge($lastNode['node_key'], $node['node_key'], 'bubar');
            $chain[]  = $node;
            $lastNode = $node;
        }

        foreach ($chain as $node) {
            $this->nodes[$node['node_key']] = $node;
        }
        $this->unitChains[$unitId] = $chain;

        // Mundur: kelahiran unit ini bisa dari 1 asal (pecah) atau banyak asal (gabung).
        foreach ($parentIds as $parentId) {
            $parentChain = $this->buildUnitChain((int) $parentId);
            if (!empty($parentChain)) {
                $parentLast = end($parentChain);
                $this->addEdge($parentLast['node_key'], $birthNode['node_key'], $birthJenis);
            }
        }

        // Maju: kalau unit ini pernah pecah/gabung sbg asal, lanjutkan ke tiap unit_baru
        // (dikelompokkan per versi supaya 1 event pecah dgn >1 target jadi 1 fan-out).
        $forkGroups = $this->transisiByAsal->get($unitId, collect())
            ->whereIn('jenis_transisi', self::JENIS_FORK)
            ->groupBy('struktur_organisasi_versi_id');

        foreach ($forkGroups as $rowsAtVersi) {
            $jenis   = $rowsAtVersi->first()->jenis_transisi;
            $targets = $rowsAtVersi->pluck('unit_baru_id')->filter()->unique()->values();
            foreach ($targets as $targetId) {
                // Cuma trigger discovery di sini — edge-nya SENDIRI ditambahkan dari sisi
                // anak (langkah "Mundur" di atas, saat anak ini dibangun) supaya tidak
                // dobel ditambahkan dari kedua arah.
                $this->buildUnitChain((int) $targetId);
            }
        }

        return $chain;
    }

    private function makeNode(int $unitId, int $versiId, string $jenisEvent, ?array $displayOverride = null): array
    {
        $display = $displayOverride ?? ($this->snapshotByUnitVersi["{$unitId}_{$versiId}"] ?? [
            'nama_unit'  => '(data tidak ditemukan)',
            'level'      => '-',
            'mc_formasi' => 0,
        ]);

        return [
            'node_key'                     => "{$unitId}_v{$versiId}",
            'unit_organisasi_id'           => $unitId,
            'struktur_organisasi_versi_id' => $versiId,
            'nama_unit'                    => $display['nama_unit'],
            'level'                        => $display['level'],
            'mc_formasi'                   => $display['mc_formasi'],
            'jenis_event'                  => $jenisEvent,
            'tier'                         => $this->tierByVersiId[$versiId] ?? 0,
            'lane'                         => 0.0,
            'is_anchor'                    => false,
        ];
    }

    private function addEdge(string $from, string $to, string $jenisTransisi): void
    {
        $fromUnit = explode('_v', $from)[0];
        $toUnit   = explode('_v', $to)[0];

        $this->edges[] = [
            'from'           => $from,
            'to'             => $to,
            'jenis_transisi' => $jenisTransisi,
            'style'          => $fromUnit === $toUnit ? 'straight' : 'curve',
        ];
    }

    /**
     * Lane (posisi horizontal), mirip Reingold-Tilford sederhana: tiap node dgn >1
     * incoming edge (hasil gabung) dipilih SATU "primary parent" (tier paling awal, tie-
     * break unit_organisasi_id terkecil) utk keperluan hitung lebar subtree — incoming
     * edge lain tidak ikut hitung lebar (mencegah subtree gabung dihitung dobel), tapi
     * tetap dipakai belakangan utk averaging & digambar.
     *
     * Assign lane: leaf (di spanning tree primer) dpt nomor urut global berikutnya (DFS
     * kiri-ke-kanan), node ber-anak = average lane anak-anaknya. Ini otomatis memenuhi
     * "anak tunggal mewarisi lane sama" (average dari 1 angka) DAN "pecah center di atas
     * lebar gabungan anak-anaknya" (average dari beberapa leaf berurutan) sekaligus.
     * Setelah itu, node hasil gabung di-override lane-nya = average lane SEMUA parent
     * asli (bukan cuma primary parent).
     */
    private function assignLanes(): void
    {
        $incomingByTo = [];
        foreach ($this->edges as $edge) {
            $incomingByTo[$edge['to']][] = $edge['from'];
        }

        $primaryParent = [];
        foreach ($incomingByTo as $to => $froms) {
            $froms = array_values(array_unique($froms));
            usort($froms, fn ($a, $b) => $this->nodes[$a]['tier'] <=> $this->nodes[$b]['tier']
                ?: $this->nodes[$a]['unit_organisasi_id'] <=> $this->nodes[$b]['unit_organisasi_id']);
            $primaryParent[$to] = $froms[0];
        }

        $primaryChildren = [];
        foreach ($primaryParent as $to => $from) {
            $primaryChildren[$from][] = $to;
        }

        $roots = array_values(array_filter(
            array_keys($this->nodes),
            fn ($key) => !isset($primaryParent[$key])
        ));
        usort($roots, fn ($a, $b) => $this->nodes[$a]['tier'] <=> $this->nodes[$b]['tier']
            ?: $this->nodes[$a]['unit_organisasi_id'] <=> $this->nodes[$b]['unit_organisasi_id']);

        $nextLane = 0;
        $assign = function (string $key) use (&$assign, &$nextLane, $primaryChildren) {
            $children = $primaryChildren[$key] ?? [];

            if (empty($children)) {
                $lane = $nextLane++;
            } else {
                usort($children, fn ($a, $b) => $this->nodes[$a]['tier'] <=> $this->nodes[$b]['tier']
                    ?: $this->nodes[$a]['unit_organisasi_id'] <=> $this->nodes[$b]['unit_organisasi_id']);
                $childLanes = array_map($assign, $children);
                $lane = array_sum($childLanes) / count($childLanes);
            }

            $this->nodes[$key]['lane'] = $lane;
            return $lane;
        };

        foreach ($roots as $root) {
            $assign($root);
        }

        foreach ($incomingByTo as $to => $froms) {
            $froms = array_values(array_unique($froms));
            if (count($froms) > 1) {
                $lanes = array_map(fn ($f) => $this->nodes[$f]['lane'], $froms);
                $this->nodes[$to]['lane'] = array_sum($lanes) / count($lanes);
            }
        }
    }

    /**
     * Titik toggle collapse/expand: 'pecah' ditaruh di node SUMBER (collapse ke bawah/
     * masa depan — subtree hasil pecah), 'gabung' ditaruh di node TARGET (collapse ke
     * atas/riwayat — cabang-cabang sebelum merge). Key toggle disuffix jenis krn 1 node
     * scr teori bisa jadi sumber pecah SEKALIGUS target gabung lain (chain berbeda).
     */
    private function buildToggles(): array
    {
        $toggles = [];
        $byFrom  = [];
        $byTo    = [];

        foreach ($this->edges as $edge) {
            $byFrom[$edge['from']][] = $edge;
            $byTo[$edge['to']][]     = $edge;
        }

        foreach ($byFrom as $fromKey => $outEdges) {
            $pecahEdges = array_values(array_filter($outEdges, fn ($e) => $e['jenis_transisi'] === 'pecah'));
            if (empty($pecahEdges)) {
                continue;
            }
            $hidden = $this->collectReachable(array_map(fn ($e) => $e['to'], $pecahEdges), $byFrom, 'to');
            $toggles[$fromKey . '_pecah'] = [
                'id'                => $fromKey . '_pecah',
                'node_key'          => $fromKey,
                'type'              => 'pecah',
                'hidden_node_keys'  => $hidden,
                'hidden_count'      => count($hidden),
                'default_collapsed' => false,
            ];
        }

        foreach ($byTo as $toKey => $inEdges) {
            $gabungEdges = array_values(array_filter($inEdges, fn ($e) => $e['jenis_transisi'] === 'gabung'));
            if (empty($gabungEdges)) {
                continue;
            }
            $hidden = $this->collectReachable(array_map(fn ($e) => $e['from'], $gabungEdges), $byTo, 'from');
            $toggles[$toKey . '_gabung'] = [
                'id'                => $toKey . '_gabung',
                'node_key'          => $toKey,
                'type'              => 'gabung',
                'hidden_node_keys'  => $hidden,
                'hidden_count'      => count($hidden),
                'default_collapsed' => false,
            ];
        }

        return $toggles;
    }

    /** BFS dari $startKeys mengikuti $adjacency ('to' utk maju via byFrom / 'from' utk mundur via byTo). */
    private function collectReachable(array $startKeys, array $adjacency, string $direction): array
    {
        $visited = [];
        $queue   = $startKeys;

        while (!empty($queue)) {
            $key = array_shift($queue);
            if (isset($visited[$key])) {
                continue;
            }
            $visited[$key] = true;

            foreach ($adjacency[$key] ?? [] as $edge) {
                $queue[] = $direction === 'to' ? $edge['to'] : $edge['from'];
            }
        }

        return array_keys($visited);
    }

    /**
     * Safety: kalau 1 tier akhirnya punya >5 node aktif, default-kan toggle 'pecah'
     * TERLUAR (tier paling awal) yg subtree-nya menyumbang node ke-6 dst di tier itu jadi
     * collapsed. Heuristik best-effort (greedy, bukan solusi exact-cover) — starting point
     * yg bisa disesuaikan setelah lihat hasil render.
     */
    private function applySafetyCollapse(array &$toggles): void
    {
        $byTier = [];
        foreach ($this->nodes as $node) {
            $byTier[$node['tier']][] = $node['node_key'];
        }

        $excessKeys = [];
        foreach ($byTier as $nodeKeys) {
            if (count($nodeKeys) <= 5) {
                continue;
            }
            usort($nodeKeys, fn ($a, $b) => $this->nodes[$a]['lane'] <=> $this->nodes[$b]['lane']);
            foreach (array_slice($nodeKeys, 5) as $key) {
                $excessKeys[$key] = true;
            }
        }

        if (empty($excessKeys)) {
            return;
        }

        $pecahToggles = array_values(array_filter($toggles, fn ($t) => $t['type'] === 'pecah'));
        usort($pecahToggles, fn ($a, $b) => $this->nodes[$a['node_key']]['tier'] <=> $this->nodes[$b['node_key']]['tier']);

        foreach ($pecahToggles as $toggle) {
            if (empty($excessKeys)) {
                break;
            }
            $covers = array_intersect_key($excessKeys, array_flip($toggle['hidden_node_keys']));
            if (!empty($covers)) {
                $toggles[$toggle['node_key'] . '_pecah']['default_collapsed'] = true;
                foreach ($toggle['hidden_node_keys'] as $hiddenKey) {
                    unset($excessKeys[$hiddenKey]);
                }
            }
        }
    }
}
