<?php

namespace App\Exports;

use App\Models\HistoryAssessment;
use Carbon\Carbon;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;

class HistoryAssessmentExport
{
    protected $search;
    protected $rekomendasi;
    protected $tahun;

    public function __construct($search = null, $rekomendasi = null, $tahun = null)
    {
        $this->search      = $search;
        $this->rekomendasi = $rekomendasi;
        $this->tahun       = $tahun;
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $options = new Options();
            $writer  = new Writer($options);
            $writer->openToFile('php://output');

            $sheet = $writer->getCurrentSheet();
            $sheet->setName('History Assessment');

            // Header style (ungu)
            $headerStyle = (new Style())
                ->setFontBold()
                ->setFontSize(11)
                ->setFontColor(Color::WHITE)
                ->setBackgroundColor(Color::rgb(124, 58, 237))
                ->setCellAlignment(CellAlignment::CENTER)
                ->setShouldWrapText(false);

            $headings = [
                'No', 'NIK', 'Nama Karyawan', 'Jabatan Saat Ini',
                'Job Grade', 'Person Grade', 'Jenis Kelamin', 'Usia',
                'Job Stream', 'Tanggal Pelaksanaan', 'Tingkat Pengukuran',
                'Rekomendasi Inti (%)', 'Rekomendasi Primer (%)',
                'Rekomendasi Sekunder (%)', 'Rekomendasi Final',
                'Tanggal Exp Assessment', 'Status Assessment', 'Keterangan',
            ];

            $headerCells = array_map(
                fn($h) => Cell::fromValue($h, $headerStyle),
                $headings
            );
            $writer->addRow(new Row($headerCells));

            // Query
            $query = HistoryAssessment::with('karyawan')
                ->orderBy('tanggal_pelaksanaan', 'desc');

            if ($this->search) {
                $query->whereHas('karyawan', function ($q) {
                    $q->where('nama', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->rekomendasi) {
                $query->where('rekomendasi_final', $this->rekomendasi);
            }

            if ($this->tahun) {
                $query->whereYear('tanggal_pelaksanaan', $this->tahun);
            }

            $rows  = $query->get();
            $rowNo = 1;

            foreach ($rows as $row) {
                $rekomendasiLabel = match ($row->rekomendasi_final) {
                    'ready'                  => 'Ready',
                    'ready_with_development' => 'Ready with Development',
                    'not_ready'              => 'Not Ready',
                    default                  => '-',
                };

                $statusIdp = '-';
                if ($row->tanggal_exp_idp) {
                    $statusIdp = Carbon::parse($row->tanggal_exp_idp)->isPast()
                        ? 'Expired'
                        : 'Aktif';
                }

                $data = [
                    $rowNo++,
                    $row->karyawan->nik  ?? '-',
                    $row->karyawan->nama ?? '-',
                    $row->jabatan_saat_ini ?? '-',
                    $row->job_grade        ?? '-',
                    $row->person_grade     ?? '-',
                    $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                    $row->usia             ?? '-',
                    $row->job_stream       ?? '-',
                    Carbon::parse($row->tanggal_pelaksanaan)->format('d/m/Y'),
                    $row->tingkat_pengukuran  ?? '-',
                    $row->rekomendasi_inti    ?? '-',
                    $row->rekomendasi_primer  ?? '-',
                    $row->rekomendasi_skunder ?? '-',
                    $rekomendasiLabel,
                    $row->tanggal_exp_idp
                        ? Carbon::parse($row->tanggal_exp_idp)->format('d/m/Y')
                        : '-',
                    $statusIdp,
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