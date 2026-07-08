<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawans';

    protected $fillable = [
        'nik', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'tanggal_masuk', 'no_hp', 'email', 'jenjang_pendidikan', 'jurusan',
        'jabatan_id', 'jabatan_saat_ini', 'struktural_fungsional',
        'direktorat_id', 'kompartemen_id', 'departemen_id',
        'job_grade_id', 'person_grade_id', 'kode_struktur_id',
        'status', 'status_kepegawaian', 'foto',
        'tanggal_mulai_pg', 'tanggal_mulai_jg', 'tanggal_mulai_band',
    ];

    /** Pilihan status kepegawaian. */
    public const STATUS_KEPEGAWAIAN = [
        'Organik',
        'PKWT',
        'Penugasan',
    ];

    /** Jenjang pendidikan, urut dari terendah → tertinggi (untuk menentukan pendidikan terakhir). */
    public const JENJANG_PENDIDIKAN = [
        'SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3',
    ];

    protected $casts = [
        'tanggal_lahir'     => 'date',
        'tanggal_masuk'     => 'date',
        'tanggal_mulai_pg'  => 'date',
        'tanggal_mulai_jg'  => 'date',
        'tanggal_mulai_band'=> 'date',
    ];

    /** Usia pensiun (tahun). */
    public const USIA_PENSIUN = 56;

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
    public function strukturAssignments() { return $this->hasMany(StrukturOrganisasi::class, 'karyawan_id'); }
    public function riwayatPendidikan()   { return $this->hasMany(RiwayatPendidikan::class); }

    /**
     * Hitung ulang "Pendidikan Terakhir" (kolom jenjang_pendidikan/jurusan) dari
     * riwayatPendidikan yang ADA saat ini = entri dengan jenjang TERTINGGI.
     * Dipakai saat riwayat dikelola satu-per-satu di halaman tersendiri.
     */
    public function refreshPendidikanTerakhir(): void
    {
        $terakhir = $this->riwayatPendidikan()->get()
            ->sortByDesc(fn ($e) => array_search($e->jenjang, self::JENJANG_PENDIDIKAN))
            ->first();

        self::whereKey($this->getKey())->update([
            'jenjang_pendidikan' => $terakhir->jenjang ?? null,
            'jurusan'            => $terakhir->jurusan ?? null,
        ]);
    }

    /**
     * Riwayat pendidikan → satu string untuk EXPORT (dan bisa dipakai IMPORT lagi).
     * Format: entri dipisah "; ", field dipisah "|" → "Jenjang|Jurusan|Institusi".
     * Contoh: "SMA/SMK|IPA|SMAN 1; S1|Teknik Mesin|UGM". Urut jenjang terendah→tertinggi.
     */
    public function getRiwayatPendidikanStringAttribute(): string
    {
        return $this->riwayatPendidikan
            ->sortBy(fn ($e) => array_search($e->jenjang, self::JENJANG_PENDIDIKAN))
            ->map(function ($e) {
                $parts = [trim((string) $e->jenjang), trim((string) $e->jurusan), trim((string) $e->institusi)];
                // Buang field kosong di ujung agar ringkas (mis. tanpa institusi).
                while (count($parts) > 1 && end($parts) === '') array_pop($parts);
                return implode('|', $parts);
            })
            ->implode('; ');
    }

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

    // MDG format lengkap "X tahun, Y bulan, Z hari" dari TMT grade sampai sekarang.
    public static function formatMdgLengkap($mulai): string
    {
        if (! $mulai) return '-';
        $mulai = $mulai instanceof Carbon ? $mulai : Carbon::parse($mulai);
        $now = now();
        if ($mulai->greaterThan($now)) return '-';

        $d = $mulai->diff($now);
        $parts = [];
        if ($d->y > 0) $parts[] = $d->y . ' tahun';
        if ($d->m > 0) $parts[] = $d->m . ' bulan';
        $parts[] = $d->d . ' hari';
        return implode(', ', $parts);
    }

    /** TRUE jika perubahan Job Grade merupakan kenaikan BAND (Band 1 teratas). */
    public static function isNaikBand(?int $jgLamaId, ?int $jgBaruId): bool
    {
        $jgLamaVal = (int) optional(JobGrade::find($jgLamaId))->job_grade;
        $jgBaruVal = (int) optional(JobGrade::find($jgBaruId))->job_grade;

        $bandKeys = array_keys(self::bandConfig());
        $idxLama  = array_search(self::getBandFromGrade($jgLamaVal), $bandKeys, true);
        $idxBaru  = array_search(self::getBandFromGrade($jgBaruVal), $bandKeys, true);

        // Index kecil = band lebih tinggi. Naik band = index mengecil.
        return $idxLama !== false && $idxBaru !== false && $idxBaru < $idxLama;
    }

    /**
     * TMT Band setelah perubahan grade (dipakai alur promosi):
     * - NAIK band  → reset ke $tmt.
     * - Band sama / turun band → dipertahankan ($bandDateSebelum = tanggal_mulai_band
     *   lama, fallback tanggal_mulai_jg lama; DI-CAPTURE sebelum event sync jalan).
     */
    public static function tmtBandSetelahPromosi(?int $jgLamaId, ?int $jgBaruId, $bandDateSebelum, $tmt)
    {
        return self::isNaikBand($jgLamaId, $jgBaruId) ? $tmt : $bandDateSebelum;
    }

    public function getMdgPgLengkapAttribute(): string   { return self::formatMdgLengkap($this->tanggal_mulai_pg); }
    public function getMdgJgLengkapAttribute(): string   { return self::formatMdgLengkap($this->tanggal_mulai_jg); }
    public function getMdgBandLengkapAttribute(): string { return self::formatMdgLengkap($this->tanggal_mulai_band ?? $this->tanggal_mulai_jg); }

    // ===== PENSIUN =====
    /** Perkiraan tanggal pensiun = tanggal lahir + USIA_PENSIUN tahun. */
    public function getTanggalPensiunAttribute(): ?Carbon
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->copy()->addYears(self::USIA_PENSIUN) : null;
    }

    /**
     * Sisa waktu menuju pensiun secara spesifik.
     * @return array{tahun:int,bulan:int,total_bulan:int,lewat:bool}|null (null bila tgl lahir kosong)
     */
    public function getSisaPensiunAttribute(): ?array
    {
        if (!$this->tanggal_lahir) return null;

        $now     = now()->startOfDay();
        $pensiun = $this->tanggal_pensiun;

        if ($pensiun->lessThanOrEqualTo($now)) {
            return ['tahun' => 0, 'bulan' => 0, 'total_bulan' => 0, 'lewat' => true];
        }

        $diff = $now->diff($pensiun);
        return [
            'tahun'       => $diff->y,
            'bulan'       => $diff->m,
            'total_bulan' => $diff->y * 12 + $diff->m,
            'lewat'       => false,
        ];
    }

    /** Label ringkas sisa masa kerja, mis. "2 th 5 bln" / "< 1 bln" / "Sudah pensiun". */
    public function getSisaPensiunLabelAttribute(): string
    {
        $s = $this->sisa_pensiun;
        if (!$s) return '-';
        if ($s['lewat']) return 'Sudah pensiun';

        $parts = [];
        if ($s['tahun'] > 0) $parts[] = $s['tahun'] . ' th';
        if ($s['bulan'] > 0) $parts[] = $s['bulan'] . ' bln';
        return $parts ? implode(' ', $parts) : '< 1 bln';
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
        // Default = normal track (PG 12 bln, JG 24 bln, Band 36 bln).
        return $this->statusKenaikan();
    }

    /**
     * Status kenaikan dengan ambang MDG yang bisa disesuaikan.
     * Dipakai Reminder Promosi agar SELARAS dengan keringanan shortlist Talent Pool
     * (shortlist: Band 24 bln, JG 12 bln). Bila argumen dikosongkan → normal track,
     * sehingga perilaku atribut status_kenaikan & unit test tetap sama.
     */
    public function statusKenaikan(int $minPg = 12, int $minJg = 24, int $minBand = 36): array
    {
        $thn = fn (int $m) => (int) round($m / 12); // bulan → tahun untuk label

        $pg    = (int) ($this->personGrade->person_grade ?? 0);
        $jg    = (int) ($this->jobGrade->job_grade ?? 0);
        $band  = $this->band;
        $maxJg = self::getMaxGradeInBand($band);

        $mdgPgBulan   = $this->mdg_pg_bulan;
        $mdgJgBulan   = $this->mdg_jg_bulan;
        $mdgBandBulan = $this->mdg_band_bulan;

        $pgMemenuhi   = $mdgPgBulan >= $minPg;
        $jgMemenuhi   = $mdgJgBulan >= $minJg;
        $bandMemenuhi = $mdgBandBulan >= $minBand;

        if ($pg < $jg) {
            return [
                'status'      => 'naik_pg',
                'eligible'    => $pgMemenuhi,
                'label'       => "Naik Person Grade → PG " . ($pg + 1),
                'sisa_bulan'  => max(0, $minPg - $mdgPgBulan),
                'syarat'      => [
                    'pg' => [
                        'terpenuhi' => $pgMemenuhi,
                        'mdg'       => $mdgPgBulan,
                        'min'       => $minPg,
                        'label'     => 'MDG PG min ' . $thn($minPg) . ' tahun',
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
            if (!$jgMemenuhi) $sisaBulan = max($sisaBulan, $minJg - $mdgJgBulan);
            if (!$pgMemenuhi) $sisaBulan = max($sisaBulan, $minPg - $mdgPgBulan);

            return [
                'status'      => 'naik_jg',
                'eligible'    => $eligible,
                'label'       => "Naik Job Grade → JG " . ($jg + 1),
                'sisa_bulan'  => $sisaBulan,
                'syarat'      => [
                    'jg' => [
                        'terpenuhi' => $jgMemenuhi,
                        'mdg'       => $mdgJgBulan,
                        'min'       => $minJg,
                        'label'     => 'MDG JG min ' . $thn($minJg) . ' tahun',
                    ],
                    'pg' => [
                        'terpenuhi' => $pgMemenuhi,
                        'mdg'       => $mdgPgBulan,
                        'min'       => $minPg,
                        'label'     => 'MDG PG min ' . $thn($minPg) . ' tahun',
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
            if (!$jgMemenuhi)   $sisaBulan = max($sisaBulan, $minJg - $mdgJgBulan);
            if (!$pgMemenuhi)   $sisaBulan = max($sisaBulan, $minPg - $mdgPgBulan);
            if (!$bandMemenuhi) $sisaBulan = max($sisaBulan, $minBand - $mdgBandBulan);

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
                        'min'       => $minJg,
                        'label'     => 'MDG JG min ' . $thn($minJg) . ' tahun',
                    ],
                    'pg'   => [
                        'terpenuhi' => $pgMemenuhi,
                        'mdg'       => $mdgPgBulan,
                        'min'       => $minPg,
                        'label'     => 'MDG PG min ' . $thn($minPg) . ' tahun',
                    ],
                    'band' => [
                        'terpenuhi' => $bandMemenuhi,
                        'mdg'       => $mdgBandBulan,
                        'min'       => $minBand,
                        'label'     => 'MDG Band min ' . $thn($minBand) . ' tahun',
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

    // ===== JOBS & JOB STREAM (mengikuti posisi di Struktur Organisasi) =====
    // Diambil dari baris Struktur Organisasi tempat karyawan ini di-assign,
    // pada periode (tahun/bulan) TERBARU. Jadi jobs & job stream otomatis
    // mengikuti "job title eksisting" yang sedang diduduki.
    public function getStrukturAktifAttribute(): ?StrukturOrganisasi
    {
        return $this->strukturAssignments
            ->where('posisi', '!=', '-')
            ->sortByDesc(fn ($s) => ((int) $s->tahun) * 100 + (int) $s->bulan)
            ->first();
    }

    public function getJobsAttribute(): ?string
    {
        return $this->struktur_aktif?->jobs;
    }

    public function getJobStreamAttribute(): ?string
    {
        return $this->struktur_aktif?->job_stream;
    }

    public function getCoreAttribute(): ?string
    {
        // 'Core' / 'Non Core' dari posisi Struktur Organisasi (bisa '' bila belum diisi).
        $c = $this->struktur_aktif?->core;
        return $c !== '' ? $c : null;
    }

    public function getRouteKeyName(): string
    {
        return 'nik';
    }
}