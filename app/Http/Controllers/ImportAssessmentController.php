<?php

namespace App\Http\Controllers;

use App\Imports\AssessmentImport;
use App\Exports\TemplateAssessmentExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Http\Request;

class ImportAssessmentController extends Controller
{
    public function page()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
        return view('history_assessment_all.import');
    }

    public function import(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }

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

            $msg = "Berhasil mengimport {$imported} data assessment.";
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

    public function downloadTemplate()
    {
        return Excel::download(
            new TemplateAssessmentExport(),
            'template-import-assessment.xlsx'
        );
    }
}