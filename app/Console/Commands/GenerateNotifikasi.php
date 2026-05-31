<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use App\Models\HistoryAssessment;
use App\Models\PgsPjs;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateNotifikasi extends Command
{
    protected $signature   = 'notifikasi:generate';
    protected $description = 'Generate notifikasi otomatis untuk sistem SIMTAL';

    public function handle()
    {
        $this->cekIdpExpire();
        $this->cekPensiun();
        $this->cekMasaKerja();
        $this->cekPgsPjsBerakhir();
        $this->cekEligibleKenaikanGrade();

        $this->info('Notifikasi berhasil digenerate!');
    }

    // 1. Cek IDP Assessment yang akan expire (30 hari)
    private function cekIdpExpire()
    {
        $assessments = HistoryAssessment::with('karyawan')
            ->whereNotNull('tanggal_exp_idp')
            ->whereBetween('tanggal_exp_idp', [now(), now()->addDays(30)])
            ->get();

        foreach ($assessments as $a) {
            // Skip jika karyawan tidak ditemukan
            if (!$a->karyawan) continue;

            $sisaHari = (int) now()->diffInDays($a->tanggal_exp_idp);

            $exists = Notifikasi::where('tipe', 'idp_expire')
                ->where('notifiable_type', HistoryAssessment::class)
                ->where('notifiable_id', $a->id)
                ->whereDate('created_at', today())
                ->exists();

            if (!$exists) {
                $level = $sisaHari <= 7 ? 'danger' : 'warning';
                Notifikasi::create([
                    'judul'           => 'IDP Akan Expire',
                    'pesan'           => "IDP {$a->karyawan->nama} akan berakhir dalam {$sisaHari} hari ({$a->tanggal_exp_idp->format('d M Y')})",
                    'tipe'            => 'idp_expire',
                    'level'           => $level,
                    'notifiable_type' => HistoryAssessment::class,
                    'notifiable_id'   => $a->id,
                ]);
            }
        }

        $this->info("✓ IDP expire: {$assessments->count()} dicek");
    }

    // 2. Cek karyawan mendekati pensiun (>= 53 tahun)
    private function cekPensiun()
    {
        $karyawans = Karyawan::where('status', 'aktif')
            ->whereNotNull('tanggal_lahir')
            ->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 53')
            ->get();

        foreach ($karyawans as $k) {
            $usia      = Carbon::parse($k->tanggal_lahir)->age;
            $sisaTahun = 56 - $usia;

            // Sudah lewat pensiun
            if ($sisaTahun < 0) continue;

            $exists = Notifikasi::where('tipe', 'pensiun')
                ->where('notifiable_type', Karyawan::class)
                ->where('notifiable_id', $k->id)
                ->whereDate('created_at', today())
                ->exists();

            if (!$exists) {
                $level = $sisaTahun <= 1 ? 'danger' : 'warning';
                Notifikasi::create([
                    'judul'           => 'Mendekati Pensiun',
                    'pesan'           => "{$k->nama} akan pensiun dalam {$sisaTahun} tahun lagi (usia {$usia} tahun)",
                    'tipe'            => 'pensiun',
                    'level'           => $level,
                    'notifiable_type' => Karyawan::class,
                    'notifiable_id'   => $k->id,
                ]);
            }
        }

        $this->info("✓ Pensiun: {$karyawans->count()} dicek");
    }

    // 3. Cek milestone masa kerja (5, 10, 15, 20, 25 tahun)
    private function cekMasaKerja()
    {
        $milestones = [5, 10, 15, 20, 25];
        $total = 0;

        foreach ($milestones as $tahun) {
            $karyawans = Karyawan::where('status', 'aktif')
                ->whereNotNull('tanggal_masuk')
                ->whereRaw("TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) = ?", [$tahun])
                ->get();

            foreach ($karyawans as $k) {
                $exists = Notifikasi::where('tipe', 'masa_kerja')
                    ->where('notifiable_type', Karyawan::class)
                    ->where('notifiable_id', $k->id)
                    ->whereYear('created_at', now()->year)
                    ->exists();

                if (!$exists) {
                    Notifikasi::create([
                        'judul'           => "Milestone {$tahun} Tahun",
                        'pesan'           => "{$k->nama} telah bekerja selama {$tahun} tahun!",
                        'tipe'            => 'masa_kerja',
                        'level'           => 'info',
                        'notifiable_type' => Karyawan::class,
                        'notifiable_id'   => $k->id,
                    ]);
                    $total++;
                }
            }
        }

        $this->info("✓ Masa kerja: {$total} milestone ditemukan");
    }

    // 4. Cek PGS/PJS yang akan berakhir (7 hari)
    private function cekPgsPjsBerakhir()
    {
        $pgsPjs = PgsPjs::with('karyawan')
            ->where('is_active', true)
            ->whereNotNull('tanggal_berakhir')
            ->whereBetween('tanggal_berakhir', [now(), now()->addDays(7)])
            ->get();

        foreach ($pgsPjs as $p) {
            // Skip jika karyawan tidak ditemukan
            if (!$p->karyawan) continue;

            $sisaHari = (int) now()->diffInDays($p->tanggal_berakhir);

            $exists = Notifikasi::where('tipe', 'pgs_pjs_berakhir')
                ->where('notifiable_type', PgsPjs::class)
                ->where('notifiable_id', $p->id)
                ->whereDate('created_at', today())
                ->exists();

            if (!$exists) {
                // FIX: gunakan kolom yang benar (tipe bukan jenis)
                $tipLabel = strtoupper($p->tipe ?? 'PGS/PJS');
                $jabatan  = $p->jabatan_pgs_pjs ?? $p->jabatan ?? '-';

                Notifikasi::create([
                    'judul'           => "{$tipLabel} Akan Berakhir",
                    'pesan'           => "{$p->karyawan->nama} sebagai {$jabatan} akan berakhir dalam {$sisaHari} hari",
                    'tipe'            => 'pgs_pjs_berakhir',
                    'level'           => $sisaHari <= 3 ? 'danger' : 'warning',
                    'notifiable_type' => PgsPjs::class,
                    'notifiable_id'   => $p->id,
                ]);
            }
        }

        $this->info("✓ PGS/PJS berakhir: {$pgsPjs->count()} dicek");
    }

    // 5. Cek karyawan eligible kenaikan grade
    private function cekEligibleKenaikanGrade()
    {
        $karyawans = Karyawan::with(['jobGrade', 'personGrade'])
            ->where('status', 'aktif')
            ->whereNotNull('tanggal_mulai_pg')
            ->get();

        $total = 0;

        foreach ($karyawans as $k) {
            // Skip jika model tidak punya accessor statusKenaikan
            if (!method_exists($k, 'getStatusKenaikanAttribute')) continue;

            $sk = $k->statusKenaikan;

            if (!$sk || !$sk['eligible']) continue;

            $exists = Notifikasi::where('tipe', 'eligible_grade')
                ->where('notifiable_type', Karyawan::class)
                ->where('notifiable_id', $k->id)
                ->where('is_read', false)
                ->exists();

            if ($exists) continue;

            $icon = match($sk['status'] ?? '') {
                'naik_pg'   => '⬆️',
                'naik_jg'   => '🚀',
                'naik_band' => '🏆',
                default     => '✅',
            };

            Notifikasi::create([
                'judul'           => "{$icon} Eligible Kenaikan Grade",
                'pesan'           => "{$k->nama} sudah eligible untuk {$sk['label']} (MDG terpenuhi)",
                'tipe'            => 'eligible_grade',
                'level'           => 'info',
                'notifiable_type' => Karyawan::class,
                'notifiable_id'   => $k->id,
            ]);

            $total++;
        }

        $this->info("✓ Eligible grade: {$total} karyawan eligible");
    }
}