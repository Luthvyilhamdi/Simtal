<?php

namespace App\Console\Commands;

use App\Models\HistoryAssessmentKompetensi;
use Illuminate\Console\Command;

class RecalculateKompetensiUnder extends Command
{
    protected $signature   = 'kompetensi:recalculate';
    protected $description = 'Recalculate total_competency_under dan kesimpulan untuk semua data assessment kompetensi';

    public function handle(): void
    {
        $kompKeys = array_keys(HistoryAssessmentKompetensi::competencies());
        $qualKeys = array_keys(HistoryAssessmentKompetensi::qualifications());

        $rows    = HistoryAssessmentKompetensi::all();
        $updated = 0;

        $this->info("Memproses {$rows->count()} data...");
        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $row) {
            $compR1    = 0;
            $compR2    = 0;
            $compUnder = 0;
            $qualUnder = 0;

            foreach ($kompKeys as $key) {
                $val = (int)($row->$key ?? 0);
                if ($val === 1) { $compR1++; $compUnder++; }
                if ($val === 2) { $compR2++; $compUnder++; }
            }

            foreach ($qualKeys as $key) {
                $val = (int)($row->$key ?? 0);
                if ($val < 2) $qualUnder++;
            }

            $kesimpulan = ($compR1 === 0 && $compR2 <= 3 && $qualUnder === 0)
                ? 'QUALIFIED'
                : 'NOT QUALIFIED';

            $row->update([
                'total_competency_under'    => $compUnder,
                'total_qualification_under' => $qualUnder,
                'kesimpulan'                => $kesimpulan,
            ]);

            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Selesai! {$updated} data berhasil diperbarui.");
    }
}