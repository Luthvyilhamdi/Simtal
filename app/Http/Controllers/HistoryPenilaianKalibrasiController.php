<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\PenilaianKaryawan;
use App\Models\KalibrasiKaryawan;
use App\Models\User;
use App\Imports\PenilaianImport;
use App\Imports\KalibrasiImport;
use App\Exports\PenilaianExport;
use App\Exports\KalibrasiExport;
use App\Exports\TemplatePenilaianExport;
use App\Exports\TemplateKalibrasiExport;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class HistoryPenilaianKalibrasiController extends Controller
{
    use LogsActivity;

    private function checkSuperAdmin(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
    }

    public function index(Request $request)
    {
        // ===== PENILAIAN — dikelompokkan per karyawan =====
        $qP = Karyawan::query()
            ->whereHas('penilaians', function ($q) use ($request) {
                if ($request->filled('tahun')) $q->where('tahun', $request->tahun);
            })
            ->with(['penilaians' => function ($q) use ($request) {
                if ($request->filled('tahun')) $q->where('tahun', $request->tahun);
                $q->orderBy('tahun', 'desc')->orderBy('periode');
            }]);

        if ($request->filled('search')) {
            $s = $request->search;
            $qP->where(fn($k) => $k->where('nama', 'like', "%$s%")->orWhere('nik', 'like', "%$s%"));
        }
        $penilaianKaryawans = $qP->orderBy('nama')->paginate(15, ['*'], 'page_penilaian')->appends($request->query());

        // ===== KALIBRASI — dikelompokkan per karyawan =====
        $qK = Karyawan::query()
            ->whereHas('kalibrasis', function ($q) use ($request) {
                if ($request->filled('tahun_kalibrasi')) $q->where('tahun', $request->tahun_kalibrasi);
            })
            ->with(['kalibrasis' => function ($q) use ($request) {
                if ($request->filled('tahun_kalibrasi')) $q->where('tahun', $request->tahun_kalibrasi);
                $q->orderBy('tahun', 'desc');
            }]);

        if ($request->filled('search_kalibrasi')) {
            $s = $request->search_kalibrasi;
            $qK->where(fn($k) => $k->where('nama', 'like', "%$s%")->orWhere('nik', 'like', "%$s%"));
        }
        $kalibrasiKaryawans = $qK->orderBy('nama')->paginate(15, ['*'], 'page_kalibrasi')->appends($request->query());

        // ===== Filter tahun =====
        $tahunsPenilaian = PenilaianKaryawan::distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $tahunsKalibrasi = KalibrasiKaryawan::distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        // ===== Stats =====
        $stats = [
            'total_penilaian'    => PenilaianKaryawan::count(),
            'karyawan_penilaian' => PenilaianKaryawan::distinct('karyawan_id')->count('karyawan_id'),
            'total_kalibrasi'    => KalibrasiKaryawan::count(),
            'karyawan_kalibrasi' => KalibrasiKaryawan::distinct('karyawan_id')->count('karyawan_id'),
        ];

        return view('history_penilaian_kalibrasi.index', compact(
            'penilaianKaryawans', 'kalibrasiKaryawans',
            'tahunsPenilaian', 'tahunsKalibrasi', 'stats'
        ));
    }

    // ===================== EXPORT =====================
    public function exportPenilaian(Request $request)
    {
        return Excel::download(
            new PenilaianExport($request->search, $request->tahun),
            'penilaian-karyawan-' . now()->format('d-m-Y') . '.xlsx'
        );
    }

    public function exportKalibrasi(Request $request)
    {
        return Excel::download(
            new KalibrasiExport($request->search_kalibrasi, $request->tahun_kalibrasi),
            'kalibrasi-karyawan-' . now()->format('d-m-Y') . '.xlsx'
        );
    }

    // ===================== IMPORT: PENILAIAN =====================
    public function importPenilaian(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls,csv|max:10240'],
            [
                'file.required' => 'File wajib dipilih.',
                'file.mimes'    => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
                'file.max'      => 'Ukuran file maksimal 10MB.',
            ]
        );

        try {
            $import = new PenilaianImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getRowCount();
            $skipped  = $import->getSkippedCount();

            $msg = "Berhasil import {$imported} data penilaian.";
            if ($skipped > 0) $msg .= " {$skipped} baris dilewati (NIK tidak ditemukan / data tidak valid).";

            $this->log('import', 'Penilaian Karyawan', 'Import Excel', "Import {$imported} data penilaian");

            return redirect()->route('history_penilaian_kalibrasi.index', ['tab' => 'penilaian'])->with('success', $msg);

        } catch (ValidationException $e) {
            return back()->with('error', 'Import gagal karena kesalahan validasi pada file.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function templatePenilaian()
    {
        $this->checkSuperAdmin();
        return Excel::download(new TemplatePenilaianExport(), 'template-import-penilaian.xlsx');
    }

    // ===================== IMPORT: KALIBRASI =====================
    public function importKalibrasi(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls,csv|max:10240'],
            [
                'file.required' => 'File wajib dipilih.',
                'file.mimes'    => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
                'file.max'      => 'Ukuran file maksimal 10MB.',
            ]
        );

        try {
            $import = new KalibrasiImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getRowCount();
            $skipped  = $import->getSkippedCount();

            $msg = "Berhasil import {$imported} data kalibrasi.";
            if ($skipped > 0) {
                $msg .= " {$skipped} baris dilewati. Contoh: " . implode(' | ', $import->getSkipReasons());
            }

            $this->log('import', 'Kalibrasi', 'Import Excel', "Import {$imported} data kalibrasi");

            return redirect()->route('history_penilaian_kalibrasi.index', ['tab' => 'kalibrasi'])->with('success', $msg);

        } catch (ValidationException $e) {
            return back()->with('error', 'Import gagal karena kesalahan validasi pada file.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function templateKalibrasi()
    {
        $this->checkSuperAdmin();
        return Excel::download(new TemplateKalibrasiExport(), 'template-import-kalibrasi.xlsx');
    }

    // ===================== DELETE (per record) =====================
    public function destroyPenilaian(PenilaianKaryawan $penilaian)
    {
        $info = optional($penilaian->karyawan)->nama . ' · ' . $penilaian->tipe . ' ' . $penilaian->tahun . ' ' . $penilaian->periode_label;
        $penilaian->delete();
        $this->log('hapus', 'Penilaian Karyawan', $info, '');

        return redirect()->route('history_penilaian_kalibrasi.index', ['tab' => 'penilaian'])
            ->with('success', 'Data penilaian berhasil dihapus.');
    }

    public function destroyKalibrasi(KalibrasiKaryawan $kalibrasi)
    {
        $info = optional($kalibrasi->karyawan)->nama . ' · ' . $kalibrasi->tahun . ' · ' . $kalibrasi->nilai;
        $kalibrasi->delete();
        $this->log('hapus', 'Kalibrasi', $info, '');

        return redirect()->route('history_penilaian_kalibrasi.index', ['tab' => 'kalibrasi'])
            ->with('success', 'Data kalibrasi berhasil dihapus.');
    }
}