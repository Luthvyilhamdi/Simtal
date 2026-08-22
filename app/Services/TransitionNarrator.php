<?php

namespace App\Services;

/**
 * Generate kalimat narasi otomatis (+ keterangan manual dari kolom
 * unit_organisasi_transisi.keterangan kalau ada) utk 1 "kejadian" transisi
 * (rename/pindah_induk/ganti_level/pecah/gabung/bubar/baru/lanjut).
 *
 * Diekstrak dari GenealogyBandLayout (Fitur A — Silsilah Visual Unit) SEBAGAI REFACTOR
 * MURNI (pindah kode apa adanya, TIDAK ada perubahan perilaku) supaya bisa dipakai ulang
 * di tempat lain (mis. Compare/Diff versi) tanpa duplikasi logic. GenealogyBandLayout
 * tetap jadi pemilik/pembangun $snapshotIndex & $transisiIndex (dibutuhkan juga utk
 * keperluan lain di kelas itu di luar narasi) — kelas ini cuma menerima keduanya lewat
 * constructor.
 */
class TransitionNarrator
{
    private array $snapshotIndex;
    private array $transisiIndex;

    /**
     * @param array $snapshotIndex keyed "{unit_organisasi_id}_{struktur_organisasi_versi_id}" => ['nama_unit','level','mc_formasi','parent_unit_organisasi_id']
     * @param array $transisiIndex keyed "{unit_asal_id}_{unit_baru_id}_{struktur_organisasi_versi_id}_{jenis_transisi}" => keterangan (nullable string)
     */
    public function __construct(array $snapshotIndex, array $transisiIndex)
    {
        $this->snapshotIndex = $snapshotIndex;
        $this->transisiIndex = $transisiIndex;
    }

    /** @return array{html:string,plain:string,keterangan:?string} */
    public function narrate(array $g): array
    {
        $h = fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        $ket = null;

        switch ($g['kind']) {
            case 'root':
                $node = $g['node'];
                if ($node['jenis_event'] === 'baseline') {
                    $plain = "{$node['nama_unit']} — bagian dari struktur baseline (versi paling awal yang tercatat)";
                    $html  = "<strong>{$h($node['nama_unit'])}</strong> — bagian dari struktur baseline (versi paling awal yang tercatat)";
                } else {
                    $plain = "{$node['nama_unit']} — unit baru, tidak ada penerus dari versi sebelumnya";
                    $html  = "<strong>{$h($node['nama_unit'])}</strong> — unit baru, tidak ada penerus dari versi sebelumnya";
                }
                $ket = $this->lookupKeterangan(null, $node['unit_organisasi_id'], $node['struktur_organisasi_versi_id'], 'baru');
                break;

            case 'carryover':
                $from = $g['from'];
                $to   = $g['to'];
                $jenis = $g['jenis'];

                if ($jenis === 'rename') {
                    $plain = "Berganti nama dari {$from['nama_unit']} menjadi {$to['nama_unit']}";
                    $html  = "Berganti nama dari <strong>{$h($from['nama_unit'])}</strong> menjadi <strong>{$h($to['nama_unit'])}</strong>";
                } elseif ($jenis === 'pindah_induk') {
                    $oldParent = $this->parentNameAt($from['unit_organisasi_id'], $from['struktur_organisasi_versi_id']);
                    $newParent = $this->parentNameAt($to['unit_organisasi_id'], $to['struktur_organisasi_versi_id']);
                    $plain = "{$to['nama_unit']} berpindah induk dari {$oldParent} ke {$newParent}";
                    $html  = "<strong>{$h($to['nama_unit'])}</strong> berpindah induk dari <strong>{$h($oldParent)}</strong> ke <strong>{$h($newParent)}</strong>";
                } elseif ($jenis === 'ganti_level') {
                    $plain = "{$to['nama_unit']}: level berubah dari {$from['level']} menjadi {$to['level']}";
                    $html  = "<strong>{$h($to['nama_unit'])}</strong>: level berubah dari <strong>" . ucfirst($h($from['level']))
                        . "</strong> menjadi <strong>" . ucfirst($h($to['level'])) . '</strong>';
                } elseif ($jenis === 'lanjut') {
                    $plain = "{$to['nama_unit']} — tidak ada perubahan sejak versi sebelumnya";
                    $html  = "<strong>{$h($to['nama_unit'])}</strong> — tidak ada perubahan sejak versi sebelumnya";
                } else { // bubar
                    $plain = "{$from['nama_unit']} — unit dibubarkan, tidak ada penerus di versi berikutnya";
                    $html  = "<strong>{$h($from['nama_unit'])}</strong> — unit dibubarkan, tidak ada penerus di versi berikutnya";
                }
                $ket = $this->lookupKeterangan($from['unit_organisasi_id'], null, $to['struktur_organisasi_versi_id'], $jenis);
                break;

            case 'pecah':
                $from = $g['from'];
                $targets = $g['targets'];
                $names = array_map(fn ($t) => $t['nama_unit'], $targets);
                $plain = "{$from['nama_unit']} pecah menjadi " . count($targets) . ' unit: ' . implode(', ', $names);
                $html  = "<strong>{$h($from['nama_unit'])}</strong> pecah menjadi " . count($targets) . ' unit: <strong>'
                    . implode('</strong>, <strong>', array_map($h, $names)) . '</strong>';
                $kets = [];
                foreach ($targets as $t) {
                    $k = $this->lookupKeterangan($from['unit_organisasi_id'], $t['unit_organisasi_id'], $t['struktur_organisasi_versi_id'], 'pecah');
                    if ($k) {
                        $kets[] = $k;
                    }
                }
                $ket = empty($kets) ? null : implode(' / ', array_unique($kets));
                break;

            case 'gabung':
                $sources = $g['sources'];
                $to = $g['to'];
                $names = array_map(fn ($s) => $s['nama_unit'], $sources);
                $plain = implode(', ', $names) . " digabung menjadi {$to['nama_unit']}";
                $html  = '<strong>' . implode('</strong>, <strong>', array_map($h, $names)) . "</strong> digabung menjadi <strong>{$h($to['nama_unit'])}</strong>";
                $kets = [];
                foreach ($sources as $s) {
                    $k = $this->lookupKeterangan($s['unit_organisasi_id'], $to['unit_organisasi_id'], $to['struktur_organisasi_versi_id'], 'gabung');
                    if ($k) {
                        $kets[] = $k;
                    }
                }
                $ket = empty($kets) ? null : implode(' / ', array_unique($kets));
                break;

            default: // fallback — defensif, hanya terpicu kalau ada tier tanpa node sumber sama sekali
                $plain = 'Tidak ada perubahan dari versi sebelumnya';
                $html  = $plain;
        }

        return ['html' => $html, 'plain' => $plain, 'keterangan' => $ket];
    }

    private function parentNameAt(int $unitId, int $versiId): string
    {
        $snap = $this->snapshotIndex["{$unitId}_{$versiId}"] ?? null;
        if (!$snap || !$snap['parent_unit_organisasi_id']) {
            return '(tidak ada induk)';
        }

        $parentSnap = $this->snapshotIndex["{$snap['parent_unit_organisasi_id']}_{$versiId}"] ?? null;
        return $parentSnap['nama_unit'] ?? '(tidak diketahui)';
    }

    private function lookupKeterangan(?int $asalId, ?int $baruId, int $versiId, string $jenis): ?string
    {
        $val = $this->transisiIndex[$this->transKey($asalId, $baruId, $versiId, $jenis)] ?? null;
        return ($val !== null && trim($val) !== '') ? $val : null;
    }

    private function transKey(?int $asalId, ?int $baruId, int $versiId, string $jenis): string
    {
        return ($asalId ?? '') . '_' . ($baruId ?? '') . '_' . $versiId . '_' . $jenis;
    }
}
