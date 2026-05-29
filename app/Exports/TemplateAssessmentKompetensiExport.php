<?php

namespace App\Exports;

use App\Models\HistoryAssessmentKompetensi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TemplateAssessmentKompetensiExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected array $competencies;
    protected array $qualifications;
    protected array $allKeys;

    public function __construct()
    {
        $this->competencies   = HistoryAssessmentKompetensi::competencies();
        $this->qualifications = HistoryAssessmentKompetensi::qualifications();
        $this->allKeys        = array_merge(
            ['nik', 'tanggal_assessment', 'periode', 'keterangan'],
            array_keys($this->competencies),
            array_keys($this->qualifications)
        );
    }

    public function title(): string
    {
        return 'Template Kompetensi';
    }

    public function headings(): array
    {
        return $this->allKeys;
    }

    public function array(): array
    {
        // ===== Baris 1: Petunjuk per kolom =====
        $petunjuk = [
            'WAJIB · NIK karyawan',
            'WAJIB · format: dd/mm/yyyy',
            'Opsional · cth: 2024, Q1-2024',
            'Opsional · catatan tambahan',
        ];

        foreach ($this->competencies as $label) {
            $petunjuk[] = 'Kompetensi · nilai 1-4';
        }
        foreach ($this->qualifications as $label) {
            $petunjuk[] = 'Qualification · nilai 1-4';
        }

        // ===== Baris 2: Label kolom (nama asli kompetensi) =====
        $labelRow = [
            'NIK',
            'Tanggal Assessment',
            'Periode',
            'Keterangan',
        ];
        foreach ($this->competencies as $label) {
            $labelRow[] = $label;
        }
        foreach ($this->qualifications as $label) {
            $labelRow[] = $label;
        }

        // ===== Baris 3-4: Contoh data =====
        $contoh1 = [
            '10001',
            '15/03/2024',
            '2024',
            '',
        ];
        foreach ($this->competencies as $key => $label) {
            $contoh1[] = rand(2, 4); // contoh nilai acak 2-4 (QUALIFIED range)
        }
        foreach ($this->qualifications as $key => $label) {
            $contoh1[] = rand(2, 4);
        }

        $contoh2 = [
            '10002',
            '20/04/2024',
            'Q1-2024',
            'Perlu evaluasi ulang',
        ];
        foreach ($this->competencies as $key => $label) {
            $contoh2[] = rand(1, 3); // contoh nilai campuran (NOT QUALIFIED range)
        }
        foreach ($this->qualifications as $key => $label) {
            $contoh2[] = rand(1, 3);
        }

        return [$petunjuk, $labelRow, $contoh1, $contoh2];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 14, // nik
            'B' => 24, // tanggal
            'C' => 14, // periode
            'D' => 28, // keterangan
        ];

        // Kolom kompetesi & qualification: mulai dari E
        $colIndex = 4; // 0=A, 1=B, 2=C, 3=D, 4=E...
        $totalKomp = count($this->competencies) + count($this->qualifications);

        for ($i = 0; $i < $totalKomp; $i++) {
            $col = $this->colLetter($colIndex + $i);
            $widths[$col] = 22;
        }

        return $widths;
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index  = intdiv($index, 26) - 1;
        }
        return $letter;
    }

    public function styles(Worksheet $sheet): array
    {
        $totalCols   = count($this->allKeys);
        $lastCol     = $this->colLetter($totalCols - 1);
        $kompStart   = $this->colLetter(4); // E
        $kompEnd     = $this->colLetter(4 + count($this->competencies) - 1);
        $qualStart   = $this->colLetter(4 + count($this->competencies));
        $qualEnd     = $lastCol;

        // ===== ROW 1: Header field name =====
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803d']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Kolom wajib (A, B) lebih gelap
        $sheet->getStyle('A1:B1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '14532d']],
        ]);

        // Warna header kompetensi (biru)
        if ($kompStart !== $kompEnd || count($this->competencies) > 0) {
            $kompRange = count($this->competencies) > 1
                ? $kompStart . '1:' . $kompEnd . '1'
                : $kompStart . '1';
            $sheet->getStyle($kompRange)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
            ]);
        }

        // Warna header qualification (ungu)
        if (count($this->qualifications) > 0) {
            $qualRange = count($this->qualifications) > 1
                ? $qualStart . '1:' . $qualEnd . '1'
                : $qualStart . '1';
            $sheet->getStyle($qualRange)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
            ]);
        }

        // ===== ROW 2: Petunjuk (kuning) =====
        $sheet->getStyle('A2:' . $lastCol . '2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '92400e'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fef3c7']],
            'alignment' => ['wrapText' => false],
        ]);

        // ===== ROW 3: Label nama asli (abu-biru) =====
        $sheet->getStyle('A3:' . $lastCol . '3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '374151'], 'size' => 10, 'italic' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0f9ff']],
        ]);

        // ===== ROW 4-5: Contoh data (abu muda) =====
        $sheet->getStyle('A4:' . $lastCol . '5')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        // ===== Border semua =====
        $sheet->getStyle('A1:' . $lastCol . '5')->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e5e7eb']],
            ],
        ]);

        // ===== Freeze: data mulai row 4 =====
        $sheet->freezePane('A4');

        // Row heights
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // Auto-size untuk semua kolom
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}