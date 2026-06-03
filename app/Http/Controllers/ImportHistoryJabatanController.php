<?php

namespace App\Http\Controllers;

use App\Imports\HistoryJabatanImport;
use App\Exports\TemplateHistoryJabatanExport;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportHistoryJabatanController extends Controller
{
    private function checkSuperAdmin(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
    }

    public function page()
    {
        $this->checkSuperAdmin();
        return view('history_karyawan.import');
    }

    public function import(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'File wajib dipilih.',
            'file.mimes'    => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $import = new HistoryJabatanImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getRowCount();
            $skipped  = $import->getSkippedCount();

            $msg = "Berhasil mengimport {$imported} history jabatan.";
            if ($skipped > 0) $msg .= " {$skipped} data dilewati (NIK tidak ditemukan).";

            return redirect()
                ->route('history_karyawan.index')
                ->with('success', $msg);

        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errMsg   = 'Import gagal: ';
            foreach (array_slice($failures, 0, 3) as $failure) {
                $errMsg .= "Baris {$failure->row()}: " . implode(', ', $failure->errors()) . '. ';
            }
            return back()->with('error', $errMsg);

        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new TemplateHistoryJabatanExport(),
            'template-import-history-jabatan.xlsx'
        );
    }
}