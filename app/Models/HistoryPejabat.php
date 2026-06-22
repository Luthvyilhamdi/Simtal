<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryPejabat extends Model
{
    // Jabatan yang dipantau
    const JABATAN_DIPANTAU = ['SVP', 'VP', 'SPM', 'PM'];

    protected $fillable = [
        'karyawan_id', 'history_jabatan_id',
        'jabatan', 'jabatan_saat_ini',
        'direktorat', 'kompartemen', 'departemen',
        'job_grade', 'person_grade',
        'no_sk', 'tanggal_sk',
        'tanggal_mulai', 'tanggal_selesai',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_sk'      => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function historyJabatan()
    {
        return $this->belongsTo(HistoryJabatan::class);
    }

    // Cek apakah masih aktif
    public function getIsAktifAttribute()
    {
        return is_null($this->tanggal_selesai);
    }

    // Durasi menjabat
    public function getDurasiAttribute()
    {
        $end = $this->tanggal_selesai ?? now();
        $diff = $this->tanggal_mulai->diff($end);
        $result = '';
        if ($diff->y > 0) $result .= $diff->y . ' thn ';
        if ($diff->m > 0) $result .= $diff->m . ' bln';
        if ($diff->y == 0 && $diff->m == 0) $result = $diff->d . ' hari';
        return trim($result);
    }

    // Warna badge jabatan
    public function getWarnaJabatanAttribute()
    {
        return match(strtoupper($this->jabatan)) {
            'SVP' => ['bg' => '#fef3c7', 'text' => '#d97706'],
            'VP'  => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'SPM' => ['bg' => '#f5f3ff', 'text' => '#7c3aed'],
            'PM'  => ['bg' => '#f0fdf4', 'text' => '#15803d'],
            default => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
        };
    }

    /**
     * Resolusi tingkat pejabat dari sebuah jabatan.
     * Prioritas: kolom level_pejabat (master jabatan) → parsing nama_jabatan → parsing label teks.
     * Mengembalikan SVP/VP/SPM/PM atau null jika bukan pejabat.
     */
    public static function resolveTier(?Jabatan $jabatan, ?string $labelText = null): ?string
    {
        // 1) Sumber paling akurat: kolom level_pejabat di master jabatan
        if ($jabatan && !empty($jabatan->level_pejabat)) {
            return strtoupper($jabatan->level_pejabat);
        }
        // 2) Fallback: parsing nama master jabatan (perilaku lama)
        if ($jabatan && !empty($jabatan->nama_jabatan)) {
            if ($t = self::ekstrakTipe($jabatan->nama_jabatan)) {
                return $t;
            }
        }
        // 3) Fallback terakhir: parsing label teks (berguna untuk mutasi jabatan teks bebas)
        if (!empty($labelText)) {
            if ($t = self::ekstrakTipe($labelText)) {
                return $t;
            }
        }
        return null;
    }

    // Cek apakah jabatan ini dipantau (berbasis teks — dipertahankan untuk kompatibilitas)
    public static function isDipantau(string $namaJabatan): bool
    {
        foreach (self::JABATAN_DIPANTAU as $j) {
            if (stripos($namaJabatan, $j) !== false) {
                return true;
            }
        }
        return false;
    }

    // Ekstrak tipe jabatan (SVP/VP/SPM/PM) dari teks
    public static function ekstrakTipe(string $namaJabatan): ?string
    {
        foreach (self::JABATAN_DIPANTAU as $j) {
            if (stripos($namaJabatan, $j) !== false) {
                return $j;
            }
        }
        return null;
    }
}