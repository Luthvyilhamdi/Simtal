<?php

namespace App\Http\Controllers;

use App\Imports\AssessmentImport;
use App\Imports\AssessmentKompetensiImport;
use App\Exports\TemplateAssessmentExport;
use App\Exports\TemplateAssessmentKompetensiExport;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportAssessmentController extends Controller
{
    // ===== CEK SUPER ADMIN =====
    private function checkSuperAdmin(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
    }

    // ===== PAGE IMPORT =====
    public function page()
    {
        $this->checkSuperAdmin();
        return view('history_assessment_all.import');
    }

    // ===== IMPORT REKOMENDASI =====
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
            $import = new AssessmentImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getRowCount();
            $skipped  = $import->getSkippedCount();

            $msg = "Berhasil mengimport {$imported} data assessment rekomendasi.";
            if ($skipped > 0) $msg .= " {$skipped} data dilewati (NIK tidak ditemukan).";

            return redirect()
                ->route('history_assessment_all.index')
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

    // ===== IMPORT KOMPETENSI =====
    public function importKompetensi(Request $request)
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
            $import = new AssessmentKompetensiImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getRowCount();
            $skipped  = $import->getSkippedCount();

            $msg = "Berhasil mengimport {$imported} data assessment kompetensi.";
            if ($skipped > 0) $msg .= " {$skipped} data dilewati (NIK tidak ditemukan atau nilai tidak valid).";

            return redirect()
                ->route('history_assessment_all.index', ['tab' => 'komp'])
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

    // ===== DOWNLOAD TEMPLATE REKOMENDASI =====
    public function downloadTemplate()
    {
        return Excel::download(
            new TemplateAssessmentExport(),
            'template-import-assessment-rekomendasi.xlsx'
        );
    }

    // ===== DOWNLOAD TEMPLATE KOMPETENSI =====
    public function downloadTemplateKompetensi()
    {
        return Excel::download(
            new TemplateAssessmentKompetensiExport(),
            'template-import-assessment-kompetensi.xlsx'
        );
    }
}