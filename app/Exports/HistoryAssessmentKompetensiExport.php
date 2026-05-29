<?php

namespace App\Exports;

use App\Models\HistoryAssessmentKompetensi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color as XlColor;

class HistoryAssessmentKompetensiExport implements WithEvents
{
    protected ?string $search;
    protected array $competencies;
    protected array $qualifications;

    public function __construct(?string $search = null)
    {
        $this->search         = $search;
        $this->competencies   = HistoryAssessmentKompetensi::competencies();
        $this->qualifications = HistoryAssessmentKompetensi::qualifications();
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return \Maatwebsite\Excel\Facades\Excel::download($this, $filename);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->buildSheet($sheet);
            },
        ];
    }

    private function buildSheet(Worksheet $sheet): void
    {
        $kompKeys   = array_keys($this->competencies);
        $qualKeys   = array_keys($this->qualifications);
        $kompLabels = array_values($this->competencies);
        $qualLabels = array_values($this->qualifications);

        $nKomp = count($kompKeys);
        $nQual = count($qualKeys);

        // ===== KOLOM LAYOUT =====
        // A=No, B=No Tes, C=Nama, D=Posisi Terakhir
        // E...(E+nKomp-1) = Competencies
        // (E+nKomp)...(E+nKomp+nQual-1) = Qualifications
        // Setelah itu: Total Comp Under, Total Qual Under, Kesimpulan

        $colStart   = 4;  // 0-indexed, A=0, D=3, E=4
        $kompStart  = $colStart;           // kolom E
        $qualStart  = $kompStart + $nKomp; // setelah kompetensi
        $sumCol     = $qualStart + $nQual; // total comp under
        $sumQCol    = $sumCol + 1;         // total qual under
        $kesCol     = $sumQCol + 1;        // kesimpulan

        $lastColIdx = $kesCol;
        $lastColLtr = $this->col($lastColIdx);

        // =================================================
        // ROW 1: Merged headers
        // =================================================
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NO TES');
        $sheet->setCellValue('C1', 'NAMA');
        $sheet->setCellValue('D1', 'POSISI TERAKHIR');

        // COMPETENCIES span
        $kompStartLtr = $this->col($kompStart);
        $kompEndLtr   = $this->col($kompStart + $nKomp - 1);
        $sheet->setCellValue($kompStartLtr . '1', 'COMPETENCIES');
        $sheet->mergeCells($kompStartLtr . '1:' . $kompEndLtr . '1');

        // PROFESSIONAL QUALIFICATION span
        $qualStartLtr = $this->col($qualStart);
        $qualEndLtr   = $this->col($qualStart + $nQual - 1);
        $sheet->setCellValue($qualStartLtr . '1', 'PROFESSIONAL QUALIFICATION');
        $sheet->mergeCells($qualStartLtr . '1:' . $qualEndLtr . '1');

        // Summary headers (single cells, merged row1 & row2)
        $sumLtr  = $this->col($sumCol);
        $sumQLtr = $this->col($sumQCol);
        $kesLtr  = $this->col($kesCol);

        $sheet->setCellValue($sumLtr  . '1', 'TOTAL COMPETENCY UNDER REQUIREMENT');
        $sheet->mergeCells($sumLtr  . '1:' . $sumLtr  . '2');

        $sheet->setCellValue($sumQLtr . '1', 'TOTAL PROFESSIONAL QUALIFICATION UNDER REQUIREMENT');
        $sheet->mergeCells($sumQLtr . '1:' . $sumQLtr . '2');

        $sheet->setCellValue($kesLtr  . '1', 'KESIMPULAN');
        $sheet->mergeCells($kesLtr   . '1:' . $kesLtr  . '2');

        // Merge NO, NO TES, NAMA, POSISI (row 1 & 2)
        foreach (['A', 'B', 'C', 'D'] as $c) {
            $sheet->mergeCells($c . '1:' . $c . '2');
        }

        // =================================================
        // ROW 2: Individual column names (rotated 90°)
        // =================================================
        for ($i = 0; $i < $nKomp; $i++) {
            $col = $this->col($kompStart + $i);
            $sheet->setCellValue($col . '2', $kompLabels[$i]);
        }
        for ($i = 0; $i < $nQual; $i++) {
            $col = $this->col($qualStart + $i);
            $sheet->setCellValue($col . '2', $qualLabels[$i]);
        }

        // =================================================
        // STYLE ROW 1
        // =================================================
        // NO, NO TES, NAMA, POSISI — hijau tua
        $sheet->getStyle('A1:D2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '166534']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        // COMPETENCIES header — biru
        $sheet->getStyle($kompStartLtr . '1:' . $kompEndLtr . '1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // PROFESSIONAL QUALIFICATION header — ungu
        $sheet->getStyle($qualStartLtr . '1:' . $qualEndLtr . '1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Summary + Kesimpulan headers — hijau tua
        $sheet->getStyle($sumLtr . '1:' . $kesLtr . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '166534']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        // =================================================
        // STYLE ROW 2: Competency labels — biru muda, rotated
        // =================================================
        $sheet->getStyle($kompStartLtr . '2:' . $kompEndLtr . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e40af']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_BOTTOM,
                'textRotation' => 90,
                'wrapText'   => false,
            ],
        ]);

        // Qualification labels — ungu muda, rotated
        $sheet->getStyle($qualStartLtr . '2:' . $qualEndLtr . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6d28d9']],
            'alignment' => [
                'horizontal'   => Alignment::HORIZONTAL_CENTER,
                'vertical'     => Alignment::VERTICAL_BOTTOM,
                'textRotation' => 90,
                'wrapText'     => false,
            ],
        ]);

        // =================================================
        // DATA ROWS — mulai baris 3
        // =================================================
        $query = HistoryAssessmentKompetensi::with('karyawan')
            ->orderBy('tanggal_assessment', 'desc');

        if ($this->search) {
            $query->whereHas('karyawan', function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nik',  'like', '%' . $this->search . '%');
            });
        }

        $rows  = $query->get();
        $rowNo = 1;
        $dataRow = 3;

        foreach ($rows as $row) {
            // Hitung ulang untuk display
            $compR1    = 0;
            $compR2    = 0;
            $compUnder = 0;
            foreach ($kompKeys as $key) {
                $v = (int)($row->$key ?? 0);
                if ($v === 1) { $compR1++; $compUnder++; }
                if ($v === 2) { $compR2++; $compUnder++; }
            }
            $qualUnder = $row->total_qualification_under;

            $isQualified = $row->kesimpulan === 'QUALIFIED';

            // Warna baris: bg kolom fixed
            $bgRow = $isQualified
                ? ['rgb' => 'f0fdf4']  // hijau sangat muda
                : ['rgb' => 'fff1f2']; // merah sangat muda

            // Kolom fixed
            $sheet->setCellValue('A' . $dataRow, $rowNo++);
            $sheet->setCellValue('B' . $dataRow, $row->karyawan->nik ?? '-');
            $sheet->setCellValue('C' . $dataRow, $row->karyawan->nama ?? '-');
            // Ambil jabatan sebagai string: coba jabatan_saat_ini, lalu nama_jabatan dari relasi
            $jabatanStr = $row->karyawan->jabatan_saat_ini
                ?? (is_object($row->karyawan->jabatan)
                    ? ($row->karyawan->jabatan->nama_jabatan ?? '-')
                    : ($row->karyawan->jabatan ?? '-'));
            $sheet->setCellValue('D' . $dataRow, $jabatanStr);

            $sheet->getStyle('A' . $dataRow . ':D' . $dataRow)->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => $bgRow],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);

            // Nilai kompetensi
            for ($i = 0; $i < $nKomp; $i++) {
                $col = $this->col($kompStart + $i);
                $key = $kompKeys[$i];
                $val = $row->$key ?? null;
                $sheet->setCellValue($col . $dataRow, $val);

                // Warna per nilai
                $valInt = (int)$val;
                if ($valInt === 1) {
                    $bg = ['rgb' => 'fecaca']; // merah
                } elseif ($valInt === 2) {
                    $bg = ['rgb' => 'fde68a']; // kuning/amber
                } else {
                    $bg = $bgRow;
                }

                $sheet->getStyle($col . $dataRow)->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => $bg],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'font'      => ['bold' => $valInt < 3],
                ]);
            }

            // Nilai qualification
            for ($i = 0; $i < $nQual; $i++) {
                $col = $this->col($qualStart + $i);
                $key = $qualKeys[$i];
                $val = $row->$key ?? null;
                $sheet->setCellValue($col . $dataRow, $val);

                $valInt = (int)$val;
                if ($valInt < 2) {
                    $bg = ['rgb' => 'fecaca'];
                } else {
                    $bg = $bgRow;
                }

                $sheet->getStyle($col . $dataRow)->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => $bg],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'font'      => ['bold' => $valInt < 2],
                ]);
            }

            // Summary
            $sheet->setCellValue($sumLtr  . $dataRow, $compUnder);
            $sheet->setCellValue($sumQLtr . $dataRow, $qualUnder);
            $sheet->setCellValue($kesLtr  . $dataRow,
                $isQualified ? 'QUALIFIED (Memenuhi Persyaratan)' : 'NOT QUALIFIED (Belum Memenuhi Persyaratan)');

            $sheet->getStyle($sumLtr . $dataRow . ':' . $kesLtr . $dataRow)->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => $bgRow],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'font'      => ['bold' => true, 'color' => ['rgb' => $isQualified ? '15803d' : 'dc2626']],
            ]);

            $dataRow++;
        }

        // =================================================
        // BORDER seluruh tabel
        // =================================================
        $lastDataRow = $dataRow - 1;
        if ($lastDataRow >= 3) {
            $sheet->getStyle('A1:' . $lastColLtr . $lastDataRow)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'd1d5db']],
                ],
            ]);
        }

        // =================================================
        // KOLOM WIDTH & ROW HEIGHT
        // =================================================
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(32);

        // Kompetensi & qual columns: sempit (rotated)
        for ($i = 0; $i < $nKomp + $nQual; $i++) {
            $sheet->getColumnDimension($this->col($kompStart + $i))->setWidth(6);
        }

        $sheet->getColumnDimension($sumLtr)->setWidth(14);
        $sheet->getColumnDimension($sumQLtr)->setWidth(14);
        $sheet->getColumnDimension($kesLtr)->setWidth(30);

        // Row heights
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(90); // tinggi untuk rotated text
        for ($r = 3; $r < $dataRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(40);
        }

        // Freeze pane
        $sheet->freezePane('E3');
        $sheet->setTitle('Assessment Kompetensi');
    }

    private function col(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index  = intdiv($index, 26) - 1;
        }
        return $letter;
    }
}