<?php

namespace App\Services;

/**
 * Varian TransitionNarrator KHUSUS utk tab "List" Timeline 1 Unit & halaman "Cari
 * Unit" — kalimat sama persis, tapi tiap nama unit yg disebut dibungkus
 * formatUnitLabel() (prefix level: "Bagian X", "Fungsional Y", dst) memakai level
 * unit YANG DIRUJUK itu sendiri (dari snapshot row masing-masing), bukan level unit
 * utama yg sedang dilihat.
 *
 * Sengaja class TERPISAH (bukan modifikasi TransitionNarrator) krn TransitionNarrator
 * dipakai juga oleh Fitur A (diagram silsilah) & Fitur B (compare) yg TIDAK boleh
 * kena prefix ini. Lookup `keterangan` (catatan manual) di-delegasikan ke
 * TransitionNarrator asli supaya logic itu tidak perlu diduplikasi (tidak menyentuh
 * nama unit sama sekali, jadi aman dipakai apa adanya).
 */
class LeveledTransitionNarrator
{
    private array $snapshotIndex;
    private TransitionNarrator $base;

    /**
     * @param array $snapshotIndex keyed "{unit_organisasi_id}_{struktur_organisasi_versi_id}" => ['nama_unit','level','mc_formasi','parent_unit_organisasi_id']
     * @param array $transisiIndex keyed "{unit_asal_id}_{unit_baru_id}_{struktur_organisasi_versi_id}_{jenis_transisi}" => keterangan (nullable string)
     */
    public function __construct(array $snapshotIndex, array $transisiIndex)
    {
        $this->snapshotIndex = $snapshotIndex;
        $this->base = new TransitionNarrator($snapshotIndex, $transisiIndex);
    }

    /** @return array{html:string,plain:string,keterangan:?string} */
    public function narrate(array $g): array
    {
        $h = fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        $label = fn ($nama, $level) => formatUnitLabel($nama, $level);
        $labelHtml = fn ($nama, $level) => '<strong>' . $h($label($nama, $level)) . '</strong>';
        $keterangan = $this->base->narrate($g)['keterangan'];

        switch ($g['kind']) {
            case 'root':
                $node = $g['node'];
                $lbl = $label($node['nama_unit'], $node['level']);
                $lblHtml = $labelHtml($node['nama_unit'], $node['level']);
                if ($node['jenis_event'] === 'baseline') {
                    $plain = "{$lbl} — bagian dari struktur baseline (versi paling awal yang tercatat)";
                    $html  = "{$lblHtml} — bagian dari struktur baseline (versi paling awal yang tercatat)";
                } else {
                    $plain = "{$lbl} — unit baru, tidak ada penerus dari versi sebelumnya";
                    $html  = "{$lblHtml} — unit baru, tidak ada penerus dari versi sebelumnya";
                }
                break;

            case 'carryover':
                $from = $g['from'];
                $to   = $g['to'];
                $jenis = $g['jenis'];

                if ($jenis === 'rename') {
                    $plain = "Berganti nama dari {$label($from['nama_unit'], $from['level'])} menjadi {$label($to['nama_unit'], $to['level'])}";
                    $html  = "Berganti nama dari {$labelHtml($from['nama_unit'], $from['level'])} menjadi {$labelHtml($to['nama_unit'], $to['level'])}";
                } elseif ($jenis === 'pindah_induk') {
                    $oldParent = $this->parentAt($from['unit_organisasi_id'], $from['struktur_organisasi_versi_id']);
                    $newParent = $this->parentAt($to['unit_organisasi_id'], $to['struktur_organisasi_versi_id']);
                    $plain = "{$label($to['nama_unit'], $to['level'])} berpindah induk dari {$label($oldParent['nama'], $oldParent['level'])} ke {$label($newParent['nama'], $newParent['level'])}";
                    $html  = "{$labelHtml($to['nama_unit'], $to['level'])} berpindah induk dari {$labelHtml($oldParent['nama'], $oldParent['level'])} ke {$labelHtml($newParent['nama'], $newParent['level'])}";
                } elseif ($jenis === 'ganti_level') {
                    $plain = "{$label($to['nama_unit'], $to['level'])}: level berubah dari {$from['level']} menjadi {$to['level']}";
                    $html  = "{$labelHtml($to['nama_unit'], $to['level'])}: level berubah dari <strong>" . ucfirst($h($from['level']))
                        . '</strong> menjadi <strong>' . ucfirst($h($to['level'])) . '</strong>';
                } elseif ($jenis === 'lanjut') {
                    $plain = "{$label($to['nama_unit'], $to['level'])} — tidak ada perubahan sejak versi sebelumnya";
                    $html  = "{$labelHtml($to['nama_unit'], $to['level'])} — tidak ada perubahan sejak versi sebelumnya";
                } else { // bubar
                    $plain = "{$label($from['nama_unit'], $from['level'])} — unit dibubarkan, tidak ada penerus di versi berikutnya";
                    $html  = "{$labelHtml($from['nama_unit'], $from['level'])} — unit dibubarkan, tidak ada penerus di versi berikutnya";
                }
                break;

            case 'pecah':
                $from = $g['from'];
                $targets = $g['targets'];
                $names = array_map(fn ($t) => $label($t['nama_unit'], $t['level']), $targets);
                $namesHtml = array_map(fn ($t) => $labelHtml($t['nama_unit'], $t['level']), $targets);
                $plain = "{$label($from['nama_unit'], $from['level'])} pecah menjadi " . count($targets) . ' unit: ' . implode(', ', $names);
                $html  = "{$labelHtml($from['nama_unit'], $from['level'])} pecah menjadi " . count($targets) . ' unit: ' . implode(', ', $namesHtml);
                break;

            case 'gabung':
                $sources = $g['sources'];
                $to = $g['to'];
                $names = array_map(fn ($s) => $label($s['nama_unit'], $s['level']), $sources);
                $namesHtml = array_map(fn ($s) => $labelHtml($s['nama_unit'], $s['level']), $sources);
                $plain = implode(', ', $names) . " digabung menjadi {$label($to['nama_unit'], $to['level'])}";
                $html  = implode(', ', $namesHtml) . " digabung menjadi {$labelHtml($to['nama_unit'], $to['level'])}";
                break;

            default: // fallback — defensif, hanya terpicu kalau ada tier tanpa node sumber sama sekali
                $plain = 'Tidak ada perubahan dari versi sebelumnya';
                $html  = $plain;
        }

        return ['html' => $html, 'plain' => $plain, 'keterangan' => $keterangan];
    }

    /** @return array{nama:string,level:?string} */
    private function parentAt(int $unitId, int $versiId): array
    {
        $snap = $this->snapshotIndex["{$unitId}_{$versiId}"] ?? null;
        if (!$snap || !$snap['parent_unit_organisasi_id']) {
            return ['nama' => '(tidak ada induk)', 'level' => null];
        }

        $parentSnap = $this->snapshotIndex["{$snap['parent_unit_organisasi_id']}_{$versiId}"] ?? null;

        return [
            'nama'  => $parentSnap['nama_unit'] ?? '(tidak diketahui)',
            'level' => $parentSnap['level'] ?? null,
        ];
    }
}
