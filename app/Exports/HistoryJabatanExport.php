<?php

namespace App\Exports;

use App\Models\HistoryJabatan;
use Carbon\Carbon;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;

class HistoryJabatanExport
{
    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $options = new Options();
            $writer  = new Writer($options);
            $writer->openToFile('php://output');

            $sheet = $writer->getCurrentSheet();
            $sheet->setName('History Jabatan');

            // Header style (hijau)
            $headerStyle = (new Style())
                ->setFontBold()
                ->setFontSize(11)
                ->setFontColor(Color::WHITE)
                ->setBackgroundColor(Color::rgb(21, 128, 61))
                ->setCellAlignment(CellAlignment::CENTER)
                ->setShouldWrapText(false);

            $headings = [
                'No', 'NIK', 'Nama Karyawan', 'Tipe', 'Jabatan',
                'Jabatan Saat Ini', 'Direktorat', 'Kompartemen', 'Departemen',
                'Job Grade', 'Person Grade', 'Kode Struktur', 'No. SK',
                'Tanggal SK', 'Tanggal Mulai', 'Tanggal Selesai', 'Status', 'Keterangan',
            ];

            $headerCells = array_map(
                fn($h) => Cell::fromValue($h, $headerStyle),
                $headings
            );
            $writer->addRow(new Row($headerCells));

            $rows = HistoryJabatan::with([
                'karyawan', 'jabatan', 'direktorat',
                'kompartemen', 'departemen', 'jobGrade',
                'personGrade', 'kodeStruktur',
            ])
            ->orderBy('karyawan_id')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

            $rowNo = 1;
            foreach ($rows as $row) {
                $data = [
                    $rowNo++,
                    $row->karyawan->nik  ?? '-',
                    $row->karyawan->nama ?? '-',
                    ucfirst($row->tipe),
                    $row->jabatan->nama_jabatan         ?? '-',
                    $row->jabatan_saat_ini              ?? '-',
                    $row->direktorat->nama_direktorat   ?? '-',
                    $row->kompartemen->nama_kompartemen ?? '-',
                    $row->departemen->nama_departemen   ?? '-',
                    $row->jobGrade->job_grade           ?? '-',
                    $row->personGrade->person_grade     ?? '-',
                    $row->kodeStruktur->kode_struktur   ?? '-',
                    $row->no_sk ?? '-',
                    $row->tanggal_sk
                        ? Carbon::parse($row->tanggal_sk)->format('d/m/Y')
                        : '-',
                    Carbon::parse($row->tanggal_mulai)->format('d/m/Y'),
                    $row->tanggal_selesai
                        ? Carbon::parse($row->tanggal_selesai)->format('d/m/Y')
                        : 'Sekarang',
                    $row->is_current ? 'Aktif' : 'Selesai',
                    $row->keterangan ?? '-',
                ];

                $cells = array_map(fn($v) => Cell::fromValue($v), $data);
                $writer->addRow(new Row($cells));
            }

            $writer->close();
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}