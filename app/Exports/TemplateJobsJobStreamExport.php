<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Template import Jobs & Job Stream.
 *
 * MENIRU PERSIS tampilan Job Title Eksisting pada satu periode: setiap baris posisi
 * ditampilkan apa adanya (tidak di-dedup). Urutan baris sudah disiapkan controller
 * lewat buildTree()+flatten yang identik dengan render halaman. Jadi jumlah & urutan
 * baris = sama dengan layar.
 *
 * Kolom unit (direktorat/kompartemen/dept/bagian) hanya konteks; saat import yang
 * dibaca hanya posisi, jobs, job_stream. jobs & job_stream sudah terisi bila ada.
 *
 * Catatan: import tetap per JOB TITLE ke SEMUA periode, sehingga bila satu nama posisi
 * muncul beberapa kali, cukup mengisi salah satunya — nilainya berlaku untuk semuanya.
 */
class TemplateJobsJobStreamExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    /** @param array $rows Baris posisi yang SUDAH terurut sesuai tampilan halaman. */
    public function __construct(private array $rows) {}

    public function title(): string
    {
        return 'Jobs & Job Stream';
    }

    public function headings(): array
    {
        return ['direktorat', 'kompartemen', 'dept', 'bagian', 'posisi', 'jobs', 'job_stream'];
    }

    public function array(): array
    {
        if (empty($this->rows)) {
            return [
                ['Direktorat Utama', 'Sekretaris Perusahaan', 'Dept. Komunikasi & ADM Korporat', '', 'Officer Komunikasi Korporat', 'Officer Corporate Communication', 'Corporate Services'],
            ];
        }

        return array_map(fn ($r) => [
            $r->direktorat  ?? '',
            $r->kompartemen ?? '',
            $r->dept        ?? '',
            $r->bagian      ?? '',
            $r->posisi,
            $r->jobs        ?? '',
            $r->job_stream  ?? '',
        ], $this->rows);
    }

    public function styles(Worksheet $sheet): array
    {
        // Header hijau
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Kolom unit (A–D) = konteks, latar abu lembut agar terlihat "hanya info".
        $lastRow = max(2, $sheet->getHighestRow());
        $sheet->getStyle("A2:D{$lastRow}")->applyFromArray([
            'font' => ['color' => ['rgb' => '6b7280']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        $sheet->freezePane('A2');

        return [];
    }
}
