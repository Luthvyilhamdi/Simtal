<?php

namespace App\Exports;

use App\Models\StrukturOrganisasi;
use App\Models\Karyawan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class StrukturOrganisasiExport implements FromCollection, WithEvents, WithTitle
{
    protected $filters;

    const BG_DIR    = 'A9D08E';
    const BG_KOMP   = 'FFFF00';
    const BG_DEPT   = '00FFFF';
    const BG_POSISI = 'FFF2CC';
    const BG_TOTAL  = '3333CC';
    const BG_HEADER = 'FFC000';
    const BG_DEV_NEG= 'FF0000';
    const FONT_NAME = 'Calibri';

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        $bulan = $this->filters['bulan'] ?? now()->month;
        $tahun = $this->filters['tahun'] ?? now()->year;
        $namaBulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return 'SO ' . $namaBulan[$bulan] . ' ' . $tahun;
    }

    public function collection(): Collection
    {
        // Data dihandle manual di AfterSheet — return kosong supaya tidak auto-dump
        return collect();
    }

    // Method terpisah untuk ambil data
    private function getData(): Collection
    {
        $q = StrukturOrganisasi::orderBy('id');

        if (!empty($this->filters['bulan']))      $q->where('bulan', $this->filters['bulan']);
        if (!empty($this->filters['tahun']))      $q->where('tahun', $this->filters['tahun']);
        if (!empty($this->filters['direktorat'])) $q->where('direktorat', $this->filters['direktorat']);
        if (!empty($this->filters['kompartemen']))$q->where('kompartemen', $this->filters['kompartemen']);
        if (!empty($this->filters['core']))       $q->where('core', $this->filters['core']);

        return $q->get();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $data  = $this->getData();

                // ===== FIX: Load semua karyawan sekaligus (1 query) =====
                $karyawanIds = $data->pluck('karyawan_id')->filter()->unique();
                $karyawanMap = Karyawan::with([
                    'direktorat','kompartemen','departemen','jobGrade','personGrade',
                    'historyJabatan' => fn($q) => $q->orderByDesc('tanggal_mulai'),
                ])
                    ->whereIn('id', $karyawanIds)
                    ->get()
                    ->keyBy('id');

                $rows = $data->filter(fn($r) => $r->posisi !== '-');

                $totalMc   = $data->sum('mc_tko');
                $totalPeng = $data->sum('pengisian');
                $totalDev  = $totalPeng - $totalMc;

                // ===== ROW 1: Header =====
                $headers = [
                    'A'=>'Job Title Eksisting','B'=>'','C'=>'','D'=>'','E'=>'','F'=>'','G'=>'','H'=>'',
                    'I'=>'Posisi','J'=>'Job Grade','K'=>"MC\nTKO",'L'=>'Pengisian',
                    'M'=>'Deviasi MC TKO','N'=>'Core/Non Core','O'=>'Nama','P'=>'NIK',
                    'Q'=>'Nama','R'=>'Jabatan Lama','S'=>'Jabatan Baru',
                    'T'=>'Job Grade','U'=>'Person Grade','V'=>'Direktorat','W'=>'Kompartemen','X'=>'Departemen',
                ];

                foreach ($headers as $col => $val) {
                    $cell = $sheet->getCell($col.'1');
                    $cell->setValue($val);
                    $cell->getStyle()->getFont()->setBold(true)->setName(self::FONT_NAME)->setSize(10);
                    $cell->getStyle()->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                }

                foreach (['J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X'] as $col) {
                    $sheet->getCell($col.'1')->getStyle()->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_HEADER);
                }
                $sheet->getRowDimension(1)->setRowHeight(30);

                // ===== ROW 2: Total =====
                $sheet->getCell('A2')->setValue('TOTAL KESELURUHAN');
                $sheet->getCell('K2')->setValue($totalMc);
                $sheet->getCell('L2')->setValue($totalPeng);
                $sheet->getCell('M2')->setValue($totalDev);

                foreach (['A','B','C','D','E','F','G','H','I','K','L'] as $col) {
                    $sheet->getCell($col.'2')->getStyle()->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_TOTAL);
                    $sheet->getCell($col.'2')->getStyle()->getFont()
                        ->setBold(true)->setName(self::FONT_NAME)->setSize(10)->getColor()->setRGB('FFFFFF');
                }
                $devStyle = $sheet->getCell('M2')->getStyle();
                $devStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG);
                $devStyle->getFont()->setBold(true)->setName(self::FONT_NAME)->setSize(10)->getColor()->setRGB('000000');
                $devStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow = 3;
                $hierCols   = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];

                // ===== FIX: Helper style apply sekaligus =====
                $applyBg = function($row, $bg, $bold = false) use ($sheet, $hierCols) {
                    foreach ($hierCols as $col) {
                        $style = $sheet->getCell($col.$row)->getStyle();
                        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                        $style->getFont()->setName(self::FONT_NAME)->setSize(10)->setBold($bold);
                    }
                };

                // ===== Build tree =====
                $tree = [];
                foreach ($rows as $row) {
                    $dir  = $row->direktorat  ?: '(Tanpa Direktorat)';
                    $komp = $row->kompartemen ?: '__no_komp__';
                    $dept = $row->dept        ?: '__no_dept__';
                    $bag  = $row->bagian      ?: '__no_bag__';
                    $func = $row->fungsional  ?: '__no_func__';

                    $tree[$dir]['rows'][] = $row;
                    $tree[$dir]['komps'][$komp]['rows'][] = $row;
                    $tree[$dir]['komps'][$komp]['depts'][$dept]['rows'][] = $row;
                    $tree[$dir]['komps'][$komp]['depts'][$dept]['bags'][$bag]['funcs'][$func]['rows'][] = $row;
                }

                foreach ($tree as $dirLabel => $dirData) {
                    $dirRows = collect($dirData['rows']);
                    $r = $currentRow++;
                    $sheet->getCell('A'.$r)->setValue($dirLabel);
                    $sheet->getCell('K'.$r)->setValue($dirRows->sum('mc_tko'));
                    $sheet->getCell('L'.$r)->setValue($dirRows->sum('pengisian'));
                    $dev = $dirRows->sum('pengisian') - $dirRows->sum('mc_tko');
                    if ($dev != 0) { $sheet->getCell('M'.$r)->setValue($dev); $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG); }
                    $applyBg($r, self::BG_DIR, true);

                    foreach ($dirData['komps'] as $kompLabel => $kompData) {
                        $kompLabel = $kompLabel === '__no_komp__' ? '' : $kompLabel;
                        $kompRows  = collect($kompData['rows']);

                        if ($kompLabel) {
                            $r = $currentRow++;
                            $sheet->getCell('B'.$r)->setValue($kompLabel);
                            $sheet->getCell('K'.$r)->setValue($kompRows->sum('mc_tko'));
                            $sheet->getCell('L'.$r)->setValue($kompRows->sum('pengisian'));
                            $dev = $kompRows->sum('pengisian') - $kompRows->sum('mc_tko');
                            if ($dev != 0) { $sheet->getCell('M'.$r)->setValue($dev); $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG); }
                            $applyBg($r, self::BG_KOMP, true);
                        }

                        foreach ($kompData['depts'] as $deptLabel => $deptData) {
                            $deptLabel = $deptLabel === '__no_dept__' ? '' : $deptLabel;
                            $deptRows  = collect($deptData['rows']);

                            if ($deptLabel) {
                                $r = $currentRow++;
                                $sheet->getCell('C'.$r)->setValue($deptLabel);
                                $sheet->getCell('K'.$r)->setValue($deptRows->sum('mc_tko'));
                                $sheet->getCell('L'.$r)->setValue($deptRows->sum('pengisian'));
                                $dev = $deptRows->sum('pengisian') - $deptRows->sum('mc_tko');
                                if ($dev != 0) { $sheet->getCell('M'.$r)->setValue($dev); $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG); }
                                $applyBg($r, self::BG_DEPT, false);
                            }

                            foreach ($deptData['bags'] as $bagLabel => $bagData) {
                                $bagLabel = $bagLabel === '__no_bag__' ? '' : $bagLabel;

                                if ($bagLabel) {
                                    $bagAllRows = collect();
                                    foreach ($bagData['funcs'] as $func) { $bagAllRows = $bagAllRows->merge(collect($func['rows'])); }
                                    $r = $currentRow++;
                                    $sheet->getCell('D'.$r)->setValue($bagLabel);
                                    $sheet->getCell('K'.$r)->setValue($bagAllRows->sum('mc_tko'));
                                    $sheet->getCell('L'.$r)->setValue($bagAllRows->sum('pengisian'));
                                    $dev = $bagAllRows->sum('pengisian') - $bagAllRows->sum('mc_tko');
                                    if ($dev != 0) { $sheet->getCell('M'.$r)->setValue($dev); $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG); }
                                    $applyBg($r, self::BG_DEPT, false);
                                }

                                foreach ($bagData['funcs'] as $funcLabel => $funcData) {
                                    $funcLabel = $funcLabel === '__no_func__' ? '' : $funcLabel;
                                    $funcRows  = collect($funcData['rows']);

                                    if ($funcLabel) {
                                        $r = $currentRow++;
                                        $sheet->getCell('D'.$r)->setValue($funcLabel);
                                        $sheet->getCell('K'.$r)->setValue($funcRows->sum('mc_tko'));
                                        $sheet->getCell('L'.$r)->setValue($funcRows->sum('pengisian'));
                                        $dev = $funcRows->sum('pengisian') - $funcRows->sum('mc_tko');
                                        if ($dev != 0) { $sheet->getCell('M'.$r)->setValue($dev); $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG); }
                                        $applyBg($r, self::BG_DEPT, false);
                                    }

                                    foreach ($funcData['rows'] as $posRow) {
                                        $r = $currentRow++;

                                        $indent = $funcLabel ? 'E' : ($bagLabel ? 'E' : ($deptLabel ? 'D' : ($kompLabel ? 'C' : 'B')));
                                        $sheet->getCell($indent.$r)->setValue($posRow->posisi);
                                        $sheet->getCell('I'.$r)->setValue($posRow->posisi);
                                        $sheet->getCell('J'.$r)->setValue($posRow->job_grade);
                                        $sheet->getCell('K'.$r)->setValue($posRow->mc_tko);
                                        $sheet->getCell('L'.$r)->setValue($posRow->pengisian);
                                        $sheet->getCell('M'.$r)->setValue($posRow->deviasi);
                                        $sheet->getCell('N'.$r)->setValue($posRow->core);

                                        // ===== FIX: Ambil dari map, bukan query =====
                                        if ($posRow->karyawan_id && isset($karyawanMap[$posRow->karyawan_id])) {
                                            $k = $karyawanMap[$posRow->karyawan_id];
                                            $sheet->getCell('O'.$r)->setValue($k->nama);
                                            $sheet->getCell('P'.$r)->setValue($k->nik);
                                            $sheet->getCell('Q'.$r)->setValue($k->nama);

                                            // Jabatan dari history: current = jabatan baru, sebelumnya = jabatan lama
                                            $history = $k->historyJabatan ?? collect();
                                            $jabatanBaru  = $history->firstWhere('is_current', 1);
                                            $jabatanLama  = $history->firstWhere('is_current', 0);

                                            $sheet->getCell('R'.$r)->setValue($jabatanLama?->jabatan_saat_ini ?? '');
                                            $sheet->getCell('S'.$r)->setValue($jabatanBaru?->jabatan_saat_ini ?? $k->jabatan_saat_ini ?? '');

                                            $sheet->getCell('T'.$r)->setValue($k->jobGrade?->job_grade ?? '');
                                            $sheet->getCell('U'.$r)->setValue($k->personGrade?->person_grade ?? '');
                                            $sheet->getCell('V'.$r)->setValue($k->direktorat?->nama_direktorat ?? '');
                                            $sheet->getCell('W'.$r)->setValue($k->kompartemen?->nama_kompartemen ?? '');
                                            $sheet->getCell('X'.$r)->setValue($k->departemen?->nama_departemen ?? '');
                                        }

                                        $applyBg($r, self::BG_POSISI, false);

                                        if ($posRow->deviasi < 0) {
                                            $sheet->getCell('M'.$r)->getStyle()->getFill()
                                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG);
                                        }

                                        $sheet->getStyle('A'.$r.':X'.$r)->getFont()->setName(self::FONT_NAME)->setSize(10);
                                        $sheet->getStyle('J'.$r.':M'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                    }
                                }
                            }
                        }
                    }
                }

                // ===== COLUMN WIDTHS =====
                $widths = [
                    'A'=>7.9,'B'=>4.8,'C'=>4.2,'D'=>4.2,'E'=>4.6,'F'=>4.8,'G'=>5.1,
                    'H'=>48.6,'I'=>46.2,'J'=>10.3,'K'=>10.0,'L'=>18.3,'M'=>15.9,
                    'N'=>15.7,'O'=>22.8,'P'=>18.3,'Q'=>41.7,'R'=>51.4,'S'=>74.1,
                    'T'=>18.9,'U'=>18.3,'V'=>30.7,'W'=>30.0,'X'=>47.2,
                ];
                foreach ($widths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                $sheet->freezePane('A3');

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('H1:I'.$lastRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A1:X1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('J3:M'.$lastRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}