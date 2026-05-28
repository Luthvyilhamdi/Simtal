<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryAssessmentKompetensi extends Model
{
    protected $table = 'history_assessment_kompetensi';

    protected $fillable = [
        'karyawan_id', 'tanggal_assessment', 'periode',
        // Competencies
        'digital_leadership', 'global_business_savvy', 'customer_focus',
        'building_strategic_partnership', 'strategic_orientation',
        'driving_execution', 'driving_innovation',
        'developing_organizational_capabilities', 'leading_change',
        'managing_diversity',
        // Professional Qualification
        'financial', 'commercial', 'people', 'operation', 'technology',
        // Hasil
        'total_competency_under', 'total_qualification_under',
        'kesimpulan', 'keterangan',
    ];

    protected $casts = [
        'tanggal_assessment' => 'date',
    ];

    // Relasi
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // Daftar kompetensi
    public static function competencies(): array
    {
        return [
            'digital_leadership'                    => 'Digital Leadership',
            'global_business_savvy'                 => 'Global Business Savvy',
            'customer_focus'                        => 'Customer Focus',
            'building_strategic_partnership'        => 'Building Strategic Partnership',
            'strategic_orientation'                 => 'Strategic Orientation',
            'driving_execution'                     => 'Driving Execution',
            'driving_innovation'                    => 'Driving Innovation',
            'developing_organizational_capabilities'=> 'Developing Organizational Capabilities',
            'leading_change'                        => 'Leading Change',
            'managing_diversity'                    => 'Managing Diversity',
        ];
    }

    // Daftar professional qualification
    public static function qualifications(): array
    {
        return [
            'financial'  => 'Financial',
            'commercial' => 'Commercial',
            'people'     => 'People',
            'operation'  => 'Operation',
            'technology' => 'Technology',
        ];
    }

    // Hitung total under requirement (nilai < 3)
    public static function hitungUnderCompetency(array $data): int
    {
        $count = 0;
        foreach (array_keys(self::competencies()) as $key) {
            if (isset($data[$key]) && (int)$data[$key] < 3) $count++;
        }
        return $count;
    }

    public static function hitungUnderQualification(array $data): int
    {
        $count = 0;
        foreach (array_keys(self::qualifications()) as $key) {
            if (isset($data[$key]) && (int)$data[$key] < 3) $count++;
        }
        return $count;
    }

    // Kesimpulan otomatis
    public static function hitungKesimpulan(int $underComp, int $underQual): string
    {
        return ($underComp === 0 && $underQual === 0) ? 'QUALIFIED' : 'NOT QUALIFIED';
    }
}