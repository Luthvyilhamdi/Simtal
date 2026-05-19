<?php

namespace App\Exports;

use App\Models\HistoryPejabat;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;

class HistoryPejabatExport
{
    protected $jabatan;
    protected $search;

    public function __construct($jabatan = null, $search = null)
    {
        $this->jabatan = $jabatan;
        $this->search  = $search;
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $options = new Options();
            $writer  = new Writer($options);
            $writer->openToFile('php://output');

            // ── Sheet 1: Pejabat Aktif ──────────────────────────────
            $sheet1 = $writer->getCurrentSheet();
            $sheet1->setName('Pejabat Aktif');
            $this->writeAktifSheet($writer);

            // ── Sheet 2: Pejabat Selesai ────────────────────────────
            $writer->addNewSheetAndMakeItCurrent();
            $sheet2 = $writer->getCurrentSheet();
            $sheet2->setName('Pejabat Selesai');
            $this->writeSelesaiSheet($writer);

            $writer->close();
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function writeAktifSheet(Writer $writer): void
    {
        // Header style (hijau)
        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(21, 128, 61))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setShouldWrapText(false);

        $headings = [
            'No', 'NIK', 'Nama Karyawan', 'Jabatan', 'Jabatan Lengkap',
            'Direktorat', 'Kompartemen', 'Departemen', 'Job Grade', 'Person Grade',
            'No. SK', 'Tanggal SK', 'Tanggal Mulai', 'Lama Menjabat', 'Status',
        ];

        $writer->addRow(new Row(array_map(
            fn($h) => Cell::fromValue($h, $headerStyle),
            $headings
        )));

        $query = HistoryPejabat::with('karyawan')
            ->whereNull('tanggal_selesai')
            ->orderBy('jabatan')
            ->orderBy('tanggal_mulai', 'desc');

        if ($this->jabatan) {
            $query->where('jabatan', $this->jabatan);
        }

        if ($this->search) {
            $query->whereHas('karyawan', function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
        }

        $rowNo = 1;
        foreach ($query->get() as $row) {
            $data = [
                $rowNo++,
                $row->karyawan->nik  ?? '-',
                $row->karyawan->nama ?? '-',
                $row->jabatan,
                $row->jabatan_saat_ini ?? '-',
                $row->direktorat       ?? '-',
                $row->kompartemen      ?? '-',
                $row->departemen       ?? '-',
                $row->job_grade        ?? '-',
                $row->person_grade     ?? '-',
                $row->no_sk            ?? '-',
                $row->tanggal_sk ? $row->tanggal_sk->format('d/m/Y') : '-',
                $row->tanggal_mulai->format('d/m/Y'),
                $row->durasi,
                'Aktif',
            ];
            $writer->addRow(new Row(array_map(fn($v) => Cell::fromValue($v), $data)));
        }
    }

    protected function writeSelesaiSheet(Writer $writer): void
    {
        // Header style (abu-abu)
        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(55, 65, 81))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setShouldWrapText(false);

        $headings = [
            'No', 'NIK', 'Nama Karyawan', 'Jabatan', 'Jabatan Lengkap',
            'Direktorat', 'Kompartemen', 'Departemen', 'Job Grade', 'Person Grade',
            'No. SK', 'Tanggal SK', 'Tanggal Mulai', 'Tanggal Selesai', 'Durasi', 'Status',
        ];

        $writer->addRow(new Row(array_map(
            fn($h) => Cell::fromValue($h, $headerStyle),
            $headings
        )));

        $query = HistoryPejabat::with('karyawan')
            ->whereNotNull('tanggal_selesai')
            ->orderBy('jabatan')
            ->orderBy('tanggal_selesai', 'desc');

        if ($this->jabatan) {
            $query->where('jabatan', $this->jabatan);
        }

        if ($this->search) {
            $query->whereHas('karyawan', function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
        }

        $rowNo = 1;
        foreach ($query->get() as $row) {
            $data = [
                $rowNo++,
                $row->karyawan->nik  ?? '-',
                $row->karyawan->nama ?? '-',
                $row->jabatan,
                $row->jabatan_saat_ini ?? '-',
                $row->direktorat       ?? '-',
                $row->kompartemen      ?? '-',
                $row->departemen       ?? '-',
                $row->job_grade        ?? '-',
                $row->person_grade     ?? '-',
                $row->no_sk            ?? '-',
                $row->tanggal_sk ? $row->tanggal_sk->format('d/m/Y') : '-',
                $row->tanggal_mulai->format('d/m/Y'),
                $row->tanggal_selesai->format('d/m/Y'),
                $row->durasi,
                'Selesai',
            ];
            $writer->addRow(new Row(array_map(fn($v) => Cell::fromValue($v), $data)));
        }
    }
}