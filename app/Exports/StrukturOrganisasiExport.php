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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Borders;
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

    // Semua kolom A-X untuk background full row
    const ALL_COLS = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X'];

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
        return collect();
    }

    private function getData(): Collection
    {
        $q = StrukturOrganisasi::orderBy('id');

        if (!empty($this->filters['bulan']))       $q->where('bulan', $this->filters['bulan']);
        if (!empty($this->filters['tahun']))       $q->where('tahun', $this->filters['tahun']);
        if (!empty($this->filters['direktorat']))  $q->where('direktorat', $this->filters['direktorat']);
        if (!empty($this->filters['kompartemen'])) $q->where('kompartemen', $this->filters['kompartemen']);
        if (!empty($this->filters['core']))        $q->where('core', $this->filters['core']);

        return $q->get();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $data  = $this->getData();

                // Load semua karyawan sekaligus (1 query)
                $karyawanIds = $data->pluck('karyawan_id')->filter()->unique();
                $karyawanMap = Karyawan::with([
                    'direktorat','kompartemen','departemen','jobGrade','personGrade',
                    'historyJabatan' => fn($q) => $q->orderByDesc('tanggal_mulai'),
                ])
                    ->whereIn('id', $karyawanIds)
                    ->get()
                    ->keyBy('id');

                // Filter: hanya posisi yang valid (bukan '-', bukan kosong, bukan null)
                $rows = $data->filter(fn($r) => !empty($r->posisi) && $r->posisi !== '-');

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

                // Header background A-X
                foreach (self::ALL_COLS as $col) {
                    $sheet->getCell($col.'1')->getStyle()->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_HEADER);
                }
                $sheet->getRowDimension(1)->setRowHeight(30);

                // ===== ROW 2: Total =====
                $sheet->getCell('A2')->setValue('TOTAL KESELURUHAN');
                $sheet->getCell('K2')->setValue($totalMc);
                $sheet->getCell('L2')->setValue($totalPeng);
                $sheet->getCell('M2')->setValue($totalDev);

                // Total background A-X
                foreach (self::ALL_COLS as $col) {
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
                // Helper apply background A-X
                $applyBg = function($row, $bg, $bold = false) use ($sheet) {
                    foreach (self::ALL_COLS as $col) {
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

                // ===== FIX: Pastikan __no_komp__ selalu PERTAMA per direktorat =====
                foreach ($tree as $dirLabel => &$dirData) {
                    if (isset($dirData['komps']['__no_komp__'])) {
                        $noKomp = ['__no_komp__' => $dirData['komps']['__no_komp__']];
                        unset($dirData['komps']['__no_komp__']);
                        $dirData['komps'] = $noKomp + $dirData['komps'];
                    }
                }
                unset($dirData);

                foreach ($tree as $dirLabel => $dirData) {
                    $dirRows = collect($dirData['rows']);
                    $r = $currentRow++;
                    $sheet->getCell('A'.$r)->setValue($dirLabel);
                    $sheet->getCell('K'.$r)->setValue($dirRows->sum('mc_tko'));
                    $sheet->getCell('L'.$r)->setValue($dirRows->sum('pengisian'));
                    $dev = $dirRows->sum('pengisian') - $dirRows->sum('mc_tko');
                    $applyBg($r, self::BG_DIR, true);
                    if ($dev != 0) {
                        $sheet->getCell('M'.$r)->setValue($dev);
                        $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG);
                    }

                    foreach ($dirData['komps'] as $kompKey => $kompData) {
                        $kompLabel = $kompKey === '__no_komp__' ? '' : $kompKey;
                        $kompRows  = collect($kompData['rows']);

                        if ($kompLabel) {
                            $r = $currentRow++;
                            $sheet->getCell('B'.$r)->setValue($kompLabel);
                            $sheet->getCell('K'.$r)->setValue($kompRows->sum('mc_tko'));
                            $sheet->getCell('L'.$r)->setValue($kompRows->sum('pengisian'));
                            $dev = $kompRows->sum('pengisian') - $kompRows->sum('mc_tko');
                            $applyBg($r, self::BG_KOMP, true);
                            if ($dev != 0) {
                                $sheet->getCell('M'.$r)->setValue($dev);
                                $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG);
                            }
                        }

                        // __no_dept__ (pimpinan komp) tampil duluan
                        $sortedDepts = [];
                        if (isset($kompData['depts']['__no_dept__'])) {
                            $sortedDepts['__no_dept__'] = $kompData['depts']['__no_dept__'];
                        }
                        foreach ($kompData['depts'] as $dk => $dv) {
                            if ($dk !== '__no_dept__') $sortedDepts[$dk] = $dv;
                        }

                        foreach ($sortedDepts as $deptKey => $deptData) {
                            $deptLabel = $deptKey === '__no_dept__' ? '' : $deptKey;
                            $deptRows  = collect($deptData['rows']);

                            if ($deptLabel) {
                                $r = $currentRow++;
                                $sheet->getCell('C'.$r)->setValue($deptLabel);
                                $sheet->getCell('K'.$r)->setValue($deptRows->sum('mc_tko'));
                                $sheet->getCell('L'.$r)->setValue($deptRows->sum('pengisian'));
                                $dev = $deptRows->sum('pengisian') - $deptRows->sum('mc_tko');
                                $applyBg($r, self::BG_DEPT, false);
                                if ($dev != 0) {
                                    $sheet->getCell('M'.$r)->setValue($dev);
                                    $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG);
                                }
                            }

                            // __no_bag__ tampil duluan
                            $sortedBags = [];
                            if (isset($deptData['bags']['__no_bag__'])) {
                                $sortedBags['__no_bag__'] = $deptData['bags']['__no_bag__'];
                            }
                            foreach ($deptData['bags'] as $bk => $bv) {
                                if ($bk !== '__no_bag__') $sortedBags[$bk] = $bv;
                            }

                            foreach ($sortedBags as $bagKey => $bagData) {
                                $bagLabel = $bagKey === '__no_bag__' ? '' : $bagKey;

                                if ($bagLabel) {
                                    $bagAllRows = collect();
                                    foreach ($bagData['funcs'] as $func) {
                                        $bagAllRows = $bagAllRows->merge(collect($func['rows']));
                                    }
                                    $r = $currentRow++;
                                    $sheet->getCell('D'.$r)->setValue($bagLabel);
                                    $sheet->getCell('K'.$r)->setValue($bagAllRows->sum('mc_tko'));
                                    $sheet->getCell('L'.$r)->setValue($bagAllRows->sum('pengisian'));
                                    $dev = $bagAllRows->sum('pengisian') - $bagAllRows->sum('mc_tko');
                                    $applyBg($r, self::BG_DEPT, false);
                                    if ($dev != 0) {
                                        $sheet->getCell('M'.$r)->setValue($dev);
                                        $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG);
                                    }
                                }

                                foreach ($bagData['funcs'] as $funcKey => $funcData) {
                                    $funcLabel = $funcKey === '__no_func__' ? '' : $funcKey;
                                    $funcRows  = collect($funcData['rows']);

                                    if ($funcLabel) {
                                        $r = $currentRow++;
                                        $sheet->getCell('D'.$r)->setValue($funcLabel);
                                        $sheet->getCell('K'.$r)->setValue($funcRows->sum('mc_tko'));
                                        $sheet->getCell('L'.$r)->setValue($funcRows->sum('pengisian'));
                                        $dev = $funcRows->sum('pengisian') - $funcRows->sum('mc_tko');
                                        $applyBg($r, self::BG_DEPT, false);
                                        if ($dev != 0) {
                                            $sheet->getCell('M'.$r)->setValue($dev);
                                            $sheet->getCell('M'.$r)->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BG_DEV_NEG);
                                        }
                                    }

                                    foreach ($funcData['rows'] as $posRow) {
                                        $r = $currentRow++;

                                        // Tentukan indent kolom posisi
                                        $indent = $funcLabel ? 'E'
                                            : ($bagLabel  ? 'E'
                                            : ($deptLabel ? 'D'
                                            : ($kompLabel ? 'C'
                                            : 'B')));

                                        $sheet->getCell($indent.$r)->setValue($posRow->posisi);
                                        $sheet->getCell('I'.$r)->setValue($posRow->posisi);
                                        $sheet->getCell('J'.$r)->setValue($posRow->job_grade);
                                        $sheet->getCell('K'.$r)->setValue($posRow->mc_tko);
                                        $sheet->getCell('L'.$r)->setValue($posRow->pengisian);
                                        $sheet->getCell('M'.$r)->setValue($posRow->deviasi);
                                        $sheet->getCell('N'.$r)->setValue($posRow->core);

                                        if ($posRow->karyawan_id && isset($karyawanMap[$posRow->karyawan_id])) {
                                            $k       = $karyawanMap[$posRow->karyawan_id];
                                            $history = $k->historyJabatan ?? collect();

                                            // Jabatan baru = is_current, jabatan lama = terbaru sebelum current
                                            $jabatanBaru = $history->where('is_current', 1)->first();
                                            $jabatanLama = $history->where('is_current', 0)->first();

                                            $sheet->getCell('O'.$r)->setValue($k->nama);
                                            $sheet->getCell('P'.$r)->setValue($k->nik);
                                            $sheet->getCell('Q'.$r)->setValue($k->nama);
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

                // ===== BORDER: apply ke seluruh data sekaligus =====
                $lastDataRow = $currentRow - 1;
                if ($lastDataRow >= 1) {
                    $borderStyle = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'BFBFBF'],
                            ],
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color'       => ['rgb' => '999999'],
                            ],
                        ],
                    ];
                    $sheet->getStyle('A1:X'.$lastDataRow)->applyFromArray($borderStyle);
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