<?php

namespace Tests\Unit;

use App\Models\Karyawan;
use Tests\TestCase;

/**
 * Unit test untuk Karyawan::jabatanSamaDengan() — penjaga sebelum SK terbit
 * di Usulan Promosi & Usulan Rotasi/Mutasi.
 *
 * Model dikonstruksi di memori (tanpa DB) mengikuti gaya PromosiLogicTest.
 */
class JabatanSamaTest extends TestCase
{
    private function karyawan(
        string $jabatan,
        ?int $direktorat = 3,
        ?int $kompartemen = 30,
        ?int $departemen = 71,
    ): Karyawan {
        $k = new Karyawan();
        $k->jabatan_saat_ini = $jabatan;
        $k->direktorat_id    = $direktorat;
        $k->kompartemen_id   = $kompartemen;
        $k->departemen_id    = $departemen;

        return $k;
    }

    public function test_jabatan_dan_unit_sama_persis_terdeteksi(): void
    {
        $k = $this->karyawan('Vice President Pelayanan Umum');

        $this->assertTrue($k->jabatanSamaDengan('Vice President Pelayanan Umum', 3, 30, 71));
    }

    public function test_jabatan_berbeda_tidak_terdeteksi(): void
    {
        $k = $this->karyawan('Associate Officer Rumah Tangga & Representasi');

        $this->assertFalse($k->jabatanSamaDengan('Officer Rumah Tangga & Representasi', 3, 30, 71));
    }

    public function test_posisi_sama_tetapi_unit_pindah_bukan_jabatan_sama(): void
    {
        $k = $this->karyawan('Vice President Pelayanan Umum');

        // Nama posisinya sama, departemennya berbeda — itu rotasi sungguhan.
        $this->assertFalse($k->jabatanSamaDengan('Vice President Pelayanan Umum', 3, 30, 99));
    }

    public function test_spasi_berlebih_dan_besar_kecil_huruf_diabaikan(): void
    {
        // Data asli memang mengandung spasi ganda, mis. "Rumah Tangga &  Representasi".
        $k = $this->karyawan('Associate Officer Rumah Tangga &  Representasi');

        $this->assertTrue($k->jabatanSamaDengan('associate officer rumah tangga & representasi', 3, 30, 71));
    }

    public function test_unit_yang_tidak_diisi_usulan_dilewati(): void
    {
        $k = $this->karyawan('Vice President Pelayanan Umum');

        // null = tidak diketahui, bukan berarti berbeda.
        $this->assertTrue($k->jabatanSamaDengan('Vice President Pelayanan Umum', null, null, null));
    }

    public function test_jabatan_tujuan_kosong_tidak_pernah_dianggap_sama(): void
    {
        $k = $this->karyawan('Vice President Pelayanan Umum');

        $this->assertFalse($k->jabatanSamaDengan(null));
        $this->assertFalse($k->jabatanSamaDengan('   '));
    }
}
