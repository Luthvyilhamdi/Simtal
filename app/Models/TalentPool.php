<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $periode
 * @property int    $karyawan_id
 * @property string $klasifikasi  longlist|shortlist
 * @property string|null $catatan
 * @property int|null $created_by
 */
class TalentPool extends Model
{
    protected $fillable = [
        'periode', 'karyawan_id', 'klasifikasi', 'catatan', 'created_by',
    ];

    /**
     * Periode acuan: tahun Talent Pool TERBARU yang ada datanya (<= tahun ini).
     *
     * Patokannya keberadaan data tahun itu, BUKAN ada-tidaknya shortlist.
     * Begitu penilaian tahun baru masuk, acuan langsung pindah ke tahun itu
     * — karyawan yang shortlist di tahun sebelumnya tidak lagi dianggap
     * shortlist, walau tahun baru kebetulan belum punya shortlist sama sekali.
     */
    public static function periodeAcuan(): ?int
    {
        return static::where('periode', '<=', now()->year)->max('periode');
    }

    /**
     * Himpunan karyawan yang shortlist pada periode acuan.
     *
     * @return array<int,int> karyawan_id => posisi (dipakai dengan isset())
     */
    public static function shortlistAcuan(): array
    {
        $periode = static::periodeAcuan();
        if (! $periode) {
            return [];
        }

        return array_flip(
            static::where('klasifikasi', 'shortlist')
                ->where('periode', $periode)
                ->pluck('karyawan_id')
                ->all()
        );
    }

    /** Apakah karyawan ini shortlist pada periode acuan? */
    public static function isShortlist(int $karyawanId): bool
    {
        $periode = static::periodeAcuan();

        return $periode !== null
            && static::where('karyawan_id', $karyawanId)
                ->where('klasifikasi', 'shortlist')
                ->where('periode', $periode)
                ->exists();
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getKlasifikasiLabelAttribute(): string
    {
        return match($this->klasifikasi) {
            'longlist'  => 'Longlist',
            'shortlist' => 'Shortlist',
            default     => '-',
        };
    }

    public function getKlasifikasiWarnaAttribute(): array
    {
        return match($this->klasifikasi) {
            'longlist'  => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
            'shortlist' => ['bg' => '#dcfce7', 'text' => '#15803d'],
            default     => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
        };
    }
}