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
            $sisaHari = now()->diffInDays($a->tanggal_exp_idp);

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
    }

    // 2. Cek karyawan mendekati pensiun
    private function cekPensiun()
    {
        $karyawans = Karyawan::where('status', 'aktif')
            ->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 53')
            ->get();

        foreach ($karyawans as $k) {
            $usia      = Carbon::parse($k->tanggal_lahir)->age;
            $sisaTahun = 56 - $usia;

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
    }

    // 3. Cek milestone masa kerja (5, 10, 15, 20, 25 tahun)
    private function cekMasaKerja()
    {
        $milestones = [5, 10, 15, 20, 25];

        foreach ($milestones as $tahun) {
            $karyawans = Karyawan::where('status', 'aktif')
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
                        'pesan'           => "🏆 {$k->nama} telah bekerja selama {$tahun} tahun!",
                        'tipe'            => 'masa_kerja',
                        'level'           => 'info',
                        'notifiable_type' => Karyawan::class,
                        'notifiable_id'   => $k->id,
                    ]);
                }
            }
        }
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
            $sisaHari = now()->diffInDays($p->tanggal_berakhir);

            $exists = Notifikasi::where('tipe', 'pgs_pjs_berakhir')
                ->where('notifiable_type', PgsPjs::class)
                ->where('notifiable_id', $p->id)
                ->whereDate('created_at', today())
                ->exists();

            if (!$exists) {
                Notifikasi::create([
                    'judul'           => strtoupper($p->tipe) . ' Akan Berakhir',
                    'pesan'           => "{$p->karyawan->nama} sebagai {$p->jabatan_pgs_pjs} akan berakhir dalam {$sisaHari} hari",
                    'tipe'            => 'pgs_pjs_berakhir',
                    'level'           => $sisaHari <= 3 ? 'danger' : 'warning',
                    'notifiable_type' => PgsPjs::class,
                    'notifiable_id'   => $p->id,
                ]);
            }
        }
    }

    // 5. Cek karyawan eligible kenaikan grade
    private function cekEligibleKenaikanGrade()
    {
        $karyawans = Karyawan::with(['jobGrade', 'personGrade'])
            ->where('status', 'aktif')
            ->whereNotNull('tanggal_mulai_pg')
            ->get();

        foreach ($karyawans as $k) {
            $sk = $k->statusKenaikan;

            if (!$sk['eligible']) continue;

            // Cek notifikasi belum dibaca hari ini
            $exists = Notifikasi::where('tipe', 'eligible_grade')
                ->where('notifiable_type', Karyawan::class)
                ->where('notifiable_id', $k->id)
                ->where('is_read', false)
                ->exists();

            if ($exists) continue;

            // Tentukan label & pesan berdasarkan status
            $icon = match($sk['status']) {
                'naik_pg'   => '⬆️',
                'naik_jg'   => '🚀',
                'naik_band' => '🏆',
                default     => '✅',
            };

            Notifikasi::create([
                'judul'           => $icon . ' Eligible Kenaikan Grade',
                'pesan'           => "{$k->nama} sudah eligible untuk {$sk['label']} (MDG terpenuhi)",
                'tipe'            => 'eligible_grade',
                'level'           => 'info',
                'notifiable_type' => Karyawan::class,
                'notifiable_id'   => $k->id,
            ]);
        }
    }
}