<?php

namespace App\Exports;

use App\Models\PgsPjs;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Writer\XLSX\Entity\SheetView;

class PgsPjsExport
{
    protected $tipe;
    protected $search;

    public function __construct($tipe = null, $search = null)
    {
        $this->tipe   = $tipe;
        $this->search = $search;
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $options = new Options();
            $writer  = new Writer($options);
            $writer->openToFile('php://output');

            // ── Sheet 1: Aktif ──────────────────────────────────────────
            $sheet1 = $writer->getCurrentSheet();
            $sheet1->setName('PGS & PJS Aktif');
            $this->writeSheet($writer, $sheet1, true);

            // ── Sheet 2: History ────────────────────────────────────────
            $writer->addNewSheetAndMakeItCurrent();
            $sheet2 = $writer->getCurrentSheet();
            $sheet2->setName('History PGS & PJS');
            $this->writeSheet($writer, $sheet2, false);

            $writer->close();
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function writeSheet(Writer $writer, $sheet, bool $isAktif): void
    {
        // Header style
        $headerColor = $isAktif ? Color::rgb(29, 78, 216) : Color::rgb(55, 65, 81);
        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor($headerColor)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setShouldWrapText(false);

        // Heading row
        $headings = [
            'No', 'Tipe', 'NIK', 'Nama Karyawan', 'Jabatan PGS/PJS',
            'Direktorat', 'Departemen', 'No. SK', 'Tanggal SK',
            'Tanggal Mulai', 'Tanggal Berakhir', 'Keterangan', 'Status',
        ];

        $headerCells = array_map(
            fn($h) => Cell::fromValue($h, $headerStyle),
            $headings
        );
        $writer->addRow(new Row($headerCells));

        // Query
        $query = PgsPjs::with('karyawan')
            ->where('is_active', $isAktif)
            ->orderBy('tipe')
            ->orderBy($isAktif ? 'tanggal_mulai' : 'tanggal_berakhir', 'desc');

        if ($this->tipe) {
            $query->where('tipe', $this->tipe);
        }

        if ($this->search) {
            $query->whereHas('karyawan', function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
        }

        $rows  = $query->get();
        $rowNo = 1;

        foreach ($rows as $row) {
            $data = [
                $rowNo++,
                strtoupper($row->tipe),
                $row->karyawan->nik  ?? '-',
                $row->karyawan->nama ?? '-',
                $row->jabatan_pgs_pjs,
                $row->direktorat ?? '-',
                $row->departemen ?? '-',
                $row->no_sk      ?? '-',
                $row->tanggal_sk      ? $row->tanggal_sk->format('d/m/Y')      : '-',
                $row->tanggal_mulai->format('d/m/Y'),
                $row->tanggal_berakhir ? $row->tanggal_berakhir->format('d/m/Y') : ($isAktif ? 'Belum ditentukan' : '-'),
                $row->keterangan ?? '-',
                $isAktif ? 'Aktif' : 'Selesai',
            ];

            $cells = array_map(fn($v) => Cell::fromValue($v), $data);
            $writer->addRow(new Row($cells));
        }
    }
}