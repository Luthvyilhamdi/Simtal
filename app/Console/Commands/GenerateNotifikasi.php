<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use App\Models\HistoryAssessment;
use App\Models\PgsPjs;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateNotifikasi extends Command
{
    protected $signature   = 'notifikasi:generate';
    protected $description = 'Generate notifikasi otomatis untuk sistem SIMTAL';

    /** Urutan tingkat kegentingan, untuk mendeteksi kenaikan. */
    private const URUTAN_LEVEL = ['info' => 0, 'warning' => 1, 'danger' => 2];

    /**
     * Simpan SATU baris notifikasi per subjek (tipe + notifiable), bukan baris
     * baru setiap hari.
     *
     * Status "sudah dibaca" menempel pada baris tertentu (tabel
     * notifikasi_reads). Kalau tiap hari dibuatkan baris baru, tanda baca
     * kemarin tidak berlaku untuk baris hari ini — akibatnya notifikasi yang
     * sudah dibaca muncul lagi keesokan harinya.
     *
     * Isi pesan & level tetap diperbarui agar hitungan sisa harinya akurat.
     * Bila tingkatnya NAIK (mis. warning → danger karena makin mendesak),
     * tanda baca dihapus supaya notifikasi itu muncul kembali — memang layak
     * diperhatikan ulang.
     */
    private function simpanNotifikasi(string $tipe, string $notifiableType, int $notifiableId, array $isi): void
    {
        $kunci = [
            'tipe'            => $tipe,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
        ];

        $lama = Notifikasi::where($kunci)->first();

        if (! $lama) {
            Notifikasi::create($isi + $kunci);
            return;
        }

        $levelBaru = self::URUTAN_LEVEL[$isi['level'] ?? 'info'] ?? 0;
        $levelLama = self::URUTAN_LEVEL[$lama->level ?? 'info'] ?? 0;

        $lama->update($isi);

        if ($levelBaru > $levelLama) {
            DB::table('notifikasi_reads')->where('notifikasi_id', $lama->id)->delete();
            $lama->update(['is_read' => false, 'read_at' => null]);
        }
    }

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

            $this->simpanNotifikasi('idp_expire', HistoryAssessment::class, $a->id, [
                'judul' => 'Assessment Akan Expire',
                'pesan' => "Assessment {$a->karyawan->nama} akan berakhir dalam {$sisaHari} hari ({$a->tanggal_exp_idp->format('d M Y')})",
                'level' => $sisaHari <= 7 ? 'danger' : 'warning',
            ]);
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

            $this->simpanNotifikasi('pensiun', Karyawan::class, $k->id, [
                'judul' => 'Mendekati Pensiun',
                'pesan' => "{$k->nama} akan pensiun dalam {$sisaTahun} tahun lagi (usia {$usia} tahun)",
                'level' => $sisaTahun <= 1 ? 'danger' : 'warning',
            ]);
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

            // FIX: gunakan kolom yang benar (tipe bukan jenis)
            $tipLabel = strtoupper($p->tipe ?? 'PGS/PJS');
            $jabatan  = $p->jabatan_pgs_pjs ?? $p->jabatan ?? '-';

            $this->simpanNotifikasi('pgs_pjs_berakhir', PgsPjs::class, $p->id, [
                'judul' => "{$tipLabel} Akan Berakhir",
                'pesan' => "{$p->karyawan->nama} sebagai {$jabatan} akan berakhir dalam {$sisaHari} hari",
                'level' => $sisaHari <= 3 ? 'danger' : 'warning',
            ]);
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