<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawans';

    protected $fillable = [
        'nik', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'tanggal_masuk', 'jabatan_id', 'jabatan_saat_ini', 'struktural_fungsional',
        'direktorat_id', 'kompartemen_id', 'departemen_id',
        'job_grade_id', 'person_grade_id', 'kode_struktur_id',
        'status', 'foto',
        'tanggal_mulai_pg', 'tanggal_mulai_jg',
    ];

    protected $casts = [
        'tanggal_lahir'     => 'date',
        'tanggal_masuk'     => 'date',
        'tanggal_mulai_pg'  => 'date',
        'tanggal_mulai_jg'  => 'date',
        'tanggal_mulai_band'=> 'date',
    ];

    // ===== RELASI =====
    public function jabatan()          { return $this->belongsTo(Jabatan::class); }
    public function direktorat()       { return $this->belongsTo(Direktorat::class); }
    public function kompartemen()      { return $this->belongsTo(Kompartemen::class); }
    public function departemen()       { return $this->belongsTo(Departemen::class); }
    public function jobGrade()         { return $this->belongsTo(JobGrade::class); }
    public function personGrade()      { return $this->belongsTo(PersonGrade::class); }
    public function kodeStruktur()     { return $this->belongsTo(KodeStruktur::class); }
    public function historyJabatan()   { return $this->hasMany(HistoryJabatan::class); }
    public function historyAssessmentKompetensi() {return $this->hasMany(HistoryAssessmentKompetensi::class);}
    public function historyAssessment(){ return $this->hasMany(HistoryAssessment::class); }
    public function pgsPjs()           { return $this->hasMany(PgsPjs::class); }
    public function suratPenting()     { return $this->hasMany(SuratPenting::class); }
    public function penilaians()       { return $this->hasMany(PenilaianKaryawan::class); }
    public function kalibrasis()       { return $this->hasMany(KalibrasiKaryawan::class); }
    public function talentPools()      { return $this->hasMany(TalentPool::class); }
    public function pejabatAktif()
    {
        return $this->hasOne(\App\Models\HistoryPejabat::class)
            ->whereNull('tanggal_selesai')
            ->latest();
    }

    // ===== BAND =====
    public static function bandConfig(): array
    {
        return [
            'Band 1' => ['grades' => [22, 21, 20], 'min_pg' => 1, 'min_jg' => 2, 'min_band' => 3],
            'Band 2' => ['grades' => [19, 18, 17], 'min_pg' => 1, 'min_jg' => 2, 'min_band' => 3],
            'Band 3' => ['grades' => [16, 15],     'min_pg' => 1, 'min_jg' => 2, 'min_band' => 3],
            'Band 4' => ['grades' => [14, 13],     'min_pg' => 1, 'min_jg' => 2, 'min_band' => 3],
            'Band 5' => ['grades' => [12, 11, 10], 'min_pg' => 1, 'min_jg' => 2, 'min_band' => 3],
            'Band 6' => ['grades' => [9, 8, 7],    'min_pg' => 1, 'min_jg' => 2, 'min_band' => 3],
        ];
    }

    public function getBandAttribute(): string
    {
        $jg = (int) ($this->jobGrade->job_grade ?? 0);
        return self::getBandFromGrade($jg);
    }

    public static function getBandFromGrade(int $grade): string
    {
        foreach (self::bandConfig() as $band => $config) {
            if (in_array($grade, $config['grades'])) return $band;
        }
        return '-';
    }

    public static function getMaxGradeInBand(string $band): int
    {
        return max(self::bandConfig()[$band]['grades'] ?? [0]);
    }

    public static function getMinGradeNextBand(string $band): ?int
    {
        $bands = array_keys(self::bandConfig());
        $idx   = array_search($band, $bands);
        if ($idx === false || $idx === 0) return null;
        $nextBand = $bands[$idx - 1];
        return min(self::bandConfig()[$nextBand]['grades']);
    }

    // ===== MDG (Masa Dinas Grade) =====
    public function getMdgPgAttribute(): ?int
    {
        if (!$this->tanggal_mulai_pg) return null;
        return (int) $this->tanggal_mulai_pg->diffInYears(now());
    }

    public function getMdgPgBulanAttribute(): int
    {
        if (!$this->tanggal_mulai_pg) return 0;
        return (int) $this->tanggal_mulai_pg->diffInMonths(now());
    }

    public function getMdgJgAttribute(): ?int
    {
        if (!$this->tanggal_mulai_jg) return null;
        return (int) $this->tanggal_mulai_jg->diffInYears(now());
    }

    public function getMdgJgBulanAttribute(): int
    {
        if (!$this->tanggal_mulai_jg) return 0;
        return (int) $this->tanggal_mulai_jg->diffInMonths(now());
    }

    public function getMdgBandBulanAttribute(): int
    {
        // MDG-Band = lama di BAND saat ini, dihitung dari tanggal_mulai_band
        // (diisi otomatis dari Riwayat Jabatan). Berbeda dari MDG-JG: kenaikan
        // JG di dalam band yang sama TIDAK mereset MDG-Band.
        // Fallback ke tanggal_mulai_jg bila tanggal_mulai_band belum terisi.
        $mulai = $this->tanggal_mulai_band ?? $this->tanggal_mulai_jg;
        if (!$mulai) return 0;
        return (int) $mulai->diffInMonths(now());
    }

    public function getMdgBandAttribute(): int
    {
        return (int) floor($this->mdg_band_bulan / 12);
    }

    /**
     * Hitung tanggal masuk Band saat ini secara OTOMATIS dari Riwayat Jabatan.
     *
     * Band ditentukan dari Job Grade; satu band berisi beberapa JG, sehingga
     * kenaikan JG di dalam band yang sama TIDAK mengganti tanggal ini. Yang
     * dikembalikan adalah tanggal mulai dari rentetan (streak) band terakhir.
     * Bila riwayat tak memadai, jatuh ke tanggal_mulai_jg.
     */
    public function hitungTanggalMulaiBand(): ?Carbon
    {
        $items = $this->historyJabatan()
            ->with('jobGrade')
            ->whereNotNull('tanggal_mulai')
            ->orderBy('tanggal_mulai')
            ->orderBy('id')
            ->get()
            ->map(function ($h) {
                $grade = (int) ($h->jobGrade->job_grade ?? 0);
                return [
                    'band'    => $grade ? self::getBandFromGrade($grade) : '-',
                    'tanggal' => $h->tanggal_mulai,
                ];
            })
            ->filter(fn ($x) => $x['band'] !== '-')
            ->values();

        if ($items->isEmpty()) {
            return $this->tanggal_mulai_jg; // fallback: belum ada riwayat
        }

        // Band saat ini = band dari riwayat paling baru.
        $bandSekarang = $items->last()['band'];

        // Cari awal dari streak band saat ini yang TERAKHIR (kalau pernah keluar
        // lalu masuk lagi ke band yang sama, ambil saat masuk terbarunya).
        $tanggalMasuk = null;
        foreach ($items as $it) {
            if ($it['band'] === $bandSekarang) {
                $tanggalMasuk ??= $it['tanggal'];
            } else {
                $tanggalMasuk = null; // streak putus
            }
        }

        return $tanggalMasuk ?? $this->tanggal_mulai_jg;
    }

    // ===== STATUS KENAIKAN =====
    public function getStatusKenaikanAttribute(): array
    {
        $pg    = (int) ($this->personGrade->person_grade ?? 0);
        $jg    = (int) ($this->jobGrade->job_grade ?? 0);
        $band  = $this->band;
        $maxJg = self::getMaxGradeInBand($band);

        $mdgPgBulan   = $this->mdg_pg_bulan;
        $mdgJgBulan   = $this->mdg_jg_bulan;
        $mdgBandBulan = $this->mdg_band_bulan;

        $pgMemenuhi   = $mdgPgBulan >= 12;
        $jgMemenuhi   = $mdgJgBulan >= 24;
        $bandMemenuhi = $mdgBandBulan >= 36;

        if ($pg < $jg) {
            return [
                'status'      => 'naik_pg',
                'eligible'    => $pgMemenuhi,
                'label'       => "Naik Person Grade → PG " . ($pg + 1),
                'sisa_bulan'  => max(0, 12 - $mdgPgBulan),
                'syarat'      => [
                    'pg' => [
                        'terpenuhi' => $pgMemenuhi,
                        'mdg'       => $mdgPgBulan,
                        'min'       => 12,
                        'label'     => 'MDG PG min 1 tahun',
                    ],
                ],
                'blokir_info' => 'Selesaikan kenaikan PG terlebih dahulu.',
                'mdg_pg'      => $this->mdg_pg ?? 0,
                'mdg_jg'      => $this->mdg_jg ?? 0,
                'mdg_band'    => $this->mdg_band,
            ];
        }

        if ($jg < $maxJg) {
            $eligible  = $jgMemenuhi && $pgMemenuhi;
            $sisaBulan = 0;
            if (!$jgMemenuhi) $sisaBulan = max($sisaBulan, 24 - $mdgJgBulan);
            if (!$pgMemenuhi) $sisaBulan = max($sisaBulan, 12 - $mdgPgBulan);

            return [
                'status'      => 'naik_jg',
                'eligible'    => $eligible,
                'label'       => "Naik Job Grade → JG " . ($jg + 1),
                'sisa_bulan'  => $sisaBulan,
                'syarat'      => [
                    'jg' => [
                        'terpenuhi' => $jgMemenuhi,
                        'mdg'       => $mdgJgBulan,
                        'min'       => 24,
                        'label'     => 'MDG JG min 2 tahun',
                    ],
                    'pg' => [
                        'terpenuhi' => $pgMemenuhi,
                        'mdg'       => $mdgPgBulan,
                        'min'       => 12,
                        'label'     => 'MDG PG min 1 tahun',
                    ],
                ],
                'blokir_info' => !$eligible ? 'Semua syarat harus terpenuhi untuk naik JG.' : null,
                'mdg_pg'      => $this->mdg_pg ?? 0,
                'mdg_jg'      => $this->mdg_jg ?? 0,
                'mdg_band'    => $this->mdg_band,
            ];
        }

        if ($band !== 'Band 1') {
            $eligible  = $jgMemenuhi && $pgMemenuhi && $bandMemenuhi;
            $sisaBulan = 0;
            if (!$jgMemenuhi)   $sisaBulan = max($sisaBulan, 24 - $mdgJgBulan);
            if (!$pgMemenuhi)   $sisaBulan = max($sisaBulan, 12 - $mdgPgBulan);
            if (!$bandMemenuhi) $sisaBulan = max($sisaBulan, 36 - $mdgBandBulan);

            $nextBandMinGrade = self::getMinGradeNextBand($band);
            $nextBand         = self::getBandFromGrade($nextBandMinGrade ?? 0);

            return [
                'status'      => 'naik_band',
                'eligible'    => $eligible,
                'label'       => "Naik Band → {$nextBand} (JG {$nextBandMinGrade})",
                'sisa_bulan'  => $sisaBulan,
                'syarat'      => [
                    'jg'   => [
                        'terpenuhi' => $jgMemenuhi,
                        'mdg'       => $mdgJgBulan,
                        'min'       => 24,
                        'label'     => 'MDG JG min 2 tahun',
                    ],
                    'pg'   => [
                        'terpenuhi' => $pgMemenuhi,
                        'mdg'       => $mdgPgBulan,
                        'min'       => 12,
                        'label'     => 'MDG PG min 1 tahun',
                    ],
                    'band' => [
                        'terpenuhi' => $bandMemenuhi,
                        'mdg'       => $mdgBandBulan,
                        'min'       => 36,
                        'label'     => 'MDG Band min 3 tahun',
                    ],
                ],
                'blokir_info' => !$eligible ? 'Semua syarat harus terpenuhi untuk naik Band.' : null,
                'mdg_pg'      => $this->mdg_pg ?? 0,
                'mdg_jg'      => $this->mdg_jg ?? 0,
                'mdg_band'    => $this->mdg_band,
            ];
        }

        return [
            'status'      => 'puncak',
            'eligible'    => false,
            'label'       => 'Grade Tertinggi (Band 1)',
            'sisa_bulan'  => 0,
            'syarat'      => [],
            'blokir_info' => null,
            'mdg_pg'      => $this->mdg_pg ?? 0,
            'mdg_jg'      => $this->mdg_jg ?? 0,
            'mdg_band'    => $this->mdg_band,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'nik';
    }
}