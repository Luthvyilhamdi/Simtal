<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use Illuminate\Console\Command;

class BackfillTanggalMulaiBand extends Command
{
    protected $signature = 'band:backfill {--dry-run : Hanya menampilkan perubahan tanpa menyimpan}';

    protected $description = 'Isi tanggal_mulai_band semua karyawan secara otomatis dari Riwayat Jabatan (fallback: tanggal_mulai_jg).';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $diperiksa = 0;
        $diperbarui = 0;

        Karyawan::chunkById(200, function ($karyawans) use (&$diperiksa, &$diperbarui, $dry) {
            foreach ($karyawans as $k) {
                $diperiksa++;

                $baru = $k->hitungTanggalMulaiBand();
                $lama = optional($k->tanggal_mulai_band)->format('Y-m-d');
                $baruStr = optional($baru)->format('Y-m-d');

                if ($lama === $baruStr) {
                    continue;
                }

                $diperbarui++;
                $this->line(sprintf('  %-12s %s  →  %s', $k->nik, $lama ?? '(kosong)', $baruStr ?? '(kosong)'));

                if (! $dry) {
                    $k->tanggal_mulai_band = $baru;
                    $k->saveQuietly();
                }
            }
        });

        $this->info(($dry ? '[DRY-RUN] ' : '')."Selesai. Diperiksa: {$diperiksa}, perlu/diperbarui: {$diperbarui}.");

        return self::SUCCESS;
    }
}
