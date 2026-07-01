<?php

namespace Tests\Unit;

use App\Models\JobGrade;
use App\Models\Karyawan;
use App\Models\PersonGrade;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Unit test untuk logika promosi & MDG (Masa Dinas Grade).
 * Semua model dikonstruksi di memori (tanpa DB) agar cepat & tidak
 * bergantung pada migrasi/lingkungan database.
 */
class PromosiLogicTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Bekukan waktu agar perhitungan MDG deterministik (tanggal mid-month
        // untuk menghindari edge akhir bulan).
        Carbon::setTestNow(Carbon::parse('2026-06-15'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Buat Karyawan in-memory dengan grade & MDG (dalam bulan) tertentu. */
    private function karyawan(
        int $jg,
        int $pg,
        ?int $mdgPgBulan = null,
        ?int $mdgJgBulan = null,
        ?int $mdgBandBulan = null,
    ): Karyawan {
        $jobGrade = new JobGrade();
        $jobGrade->job_grade = $jg;

        $personGrade = new PersonGrade();
        $personGrade->person_grade = $pg;

        $k = new Karyawan();
        $k->setRelation('jobGrade', $jobGrade);
        $k->setRelation('personGrade', $personGrade);

        if ($mdgPgBulan !== null)   $k->tanggal_mulai_pg   = now()->subMonths($mdgPgBulan);
        if ($mdgJgBulan !== null)   $k->tanggal_mulai_jg   = now()->subMonths($mdgJgBulan);
        if ($mdgBandBulan !== null) $k->tanggal_mulai_band = now()->subMonths($mdgBandBulan);

        return $k;
    }

    // ─────────────────────────────────────────────
    // BAND DERIVATION (sesuai dokumen grading PI Group)
    // ─────────────────────────────────────────────

    public function test_band_dari_grade_sesuai_dokumen(): void
    {
        $this->assertSame('Band 1', Karyawan::getBandFromGrade(22));
        $this->assertSame('Band 1', Karyawan::getBandFromGrade(20));
        $this->assertSame('Band 2', Karyawan::getBandFromGrade(19));
        $this->assertSame('Band 2', Karyawan::getBandFromGrade(17));
        $this->assertSame('Band 3', Karyawan::getBandFromGrade(16));
        $this->assertSame('Band 3', Karyawan::getBandFromGrade(15));
        $this->assertSame('Band 4', Karyawan::getBandFromGrade(13));
        $this->assertSame('Band 5', Karyawan::getBandFromGrade(12));
        $this->assertSame('Band 6', Karyawan::getBandFromGrade(7));
        $this->assertSame('-', Karyawan::getBandFromGrade(99)); // grade tak dikenal
    }

    public function test_max_grade_dan_min_grade_band_berikutnya(): void
    {
        $this->assertSame(16, Karyawan::getMaxGradeInBand('Band 3')); // [16,15]
        $this->assertSame(22, Karyawan::getMaxGradeInBand('Band 1')); // [22,21,20]
        // Naik dari Band 3 → Band 2, grade minimum Band 2 = 17
        $this->assertSame(17, Karyawan::getMinGradeNextBand('Band 3'));
        $this->assertNull(Karyawan::getMinGradeNextBand('Band 1')); // sudah teratas
    }

    // ─────────────────────────────────────────────
    // MDG-BAND (perbaikan bug: dari tanggal_mulai_band, BUKAN JG)
    // ─────────────────────────────────────────────

    public function test_mdg_band_dari_tanggal_band_bukan_jg(): void
    {
        // JG baru 12 bulan lalu, tapi sudah di band ini 40 bulan.
        $k = $this->karyawan(jg: 15, pg: 15, mdgJgBulan: 12, mdgBandBulan: 40);

        $this->assertSame(40, $k->mdg_band_bulan, 'MDG-Band harus dari tanggal_mulai_band (40 bln)');
        $this->assertSame(12, $k->mdg_jg_bulan, 'MDG-JG tetap dari tanggal_mulai_jg (12 bln)');
    }

    public function test_mdg_band_fallback_ke_jg_bila_band_kosong(): void
    {
        // Tanpa tanggal_mulai_band → fallback ke tanggal_mulai_jg.
        $k = $this->karyawan(jg: 15, pg: 15, mdgJgBulan: 24);

        $this->assertSame(24, $k->mdg_band_bulan);
    }

    // ─────────────────────────────────────────────
    // STATUS KENAIKAN
    // ─────────────────────────────────────────────

    public function test_naik_pg_saat_pg_lebih_rendah_dari_jg(): void
    {
        $k = $this->karyawan(jg: 15, pg: 14, mdgPgBulan: 12);
        $status = $k->status_kenaikan;

        $this->assertSame('naik_pg', $status['status']);
        $this->assertTrue($status['eligible']); // MDG PG 12 ≥ 12
    }

    public function test_naik_pg_belum_eligible_bila_mdg_pg_kurang(): void
    {
        $k = $this->karyawan(jg: 15, pg: 14, mdgPgBulan: 6);
        $status = $k->status_kenaikan;

        $this->assertSame('naik_pg', $status['status']);
        $this->assertFalse($status['eligible']); // 6 < 12
    }

    public function test_naik_jg_eligible_saat_syarat_terpenuhi(): void
    {
        // PG = JG = 15 (Band 3, max 16) → bisa naik JG.
        $k = $this->karyawan(jg: 15, pg: 15, mdgPgBulan: 12, mdgJgBulan: 24);
        $status = $k->status_kenaikan;

        $this->assertSame('naik_jg', $status['status']);
        $this->assertTrue($status['eligible']); // JG 24≥24 & PG 12≥12
    }

    public function test_naik_jg_belum_eligible_bila_mdg_jg_kurang(): void
    {
        $k = $this->karyawan(jg: 15, pg: 15, mdgPgBulan: 12, mdgJgBulan: 12);
        $status = $k->status_kenaikan;

        $this->assertSame('naik_jg', $status['status']);
        $this->assertFalse($status['eligible']); // JG 12 < 24
    }

    public function test_naik_band_eligible_saat_semua_syarat_terpenuhi(): void
    {
        // JG 16 (top Band 3) → jalur naik Band. Butuh MDG Band ≥ 36.
        $k = $this->karyawan(jg: 16, pg: 16, mdgPgBulan: 12, mdgJgBulan: 24, mdgBandBulan: 36);
        $status = $k->status_kenaikan;

        $this->assertSame('naik_band', $status['status']);
        $this->assertTrue($status['eligible']);
    }

    public function test_naik_band_belum_eligible_bila_mdg_band_kurang(): void
    {
        // Ini membuktikan MDG-Band benar-benar jadi gerbang promosi band.
        $k = $this->karyawan(jg: 16, pg: 16, mdgPgBulan: 12, mdgJgBulan: 24, mdgBandBulan: 24);
        $status = $k->status_kenaikan;

        $this->assertSame('naik_band', $status['status']);
        $this->assertFalse($status['eligible']); // Band 24 < 36
    }

    public function test_grade_puncak_di_band_1(): void
    {
        // JG 22 = puncak Band 1.
        $k = $this->karyawan(jg: 22, pg: 22, mdgPgBulan: 24, mdgJgBulan: 48, mdgBandBulan: 60);
        $status = $k->status_kenaikan;

        $this->assertSame('puncak', $status['status']);
        $this->assertFalse($status['eligible']);
    }
}
