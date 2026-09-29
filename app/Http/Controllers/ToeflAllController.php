<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use App\Exports\ToeflExport;
use App\Exports\TemplateToeflExport;
use App\Imports\ToeflImport;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

/**
 * Halaman GLOBAL nilai TOEFL — 1 baris = 1 karyawan (yang punya data TOEFL).
 * Detail per tes dilihat di halaman karyawan (toefl.index). Plus Export Excel.
 */
class ToeflAllController extends Controller
{
    use LogsActivity;

    /** Impor massal mengubah banyak baris sekaligus - dibatasi super admin. */
    private function checkSuperAdmin(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
    }

    public function index(Request $request)
    {
        $query = Karyawan::query()
            ->has('toefls')
            ->withCount('toefls')
            ->with([
                'jabatan', 'departemen',
                'toefls' => fn ($q) => $q->orderByDesc('tanggal_tes')->orderByDesc('id'),
            ])
            ->orderBy('nama');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        $karyawans = $query->paginate(15)->withQueryString();

        return view('toefl_all.index', compact('karyawans'));
    }

    public function export(Request $request)
    {
        $filename = 'toefl-' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(new ToeflExport($request->search), $filename);
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
            $import = new ToeflImport();
            Excel::import($import, $request->file('file'));

            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();

            $msg = "Import selesai: {$created} data baru, {$updated} diperbarui.";
            if ($skipped > 0) {
                $msg .= " {$skipped} baris dilewati";
                // Sebutkan alasan konkretnya supaya pengguna tahu apa yang perlu
                // diperbaiki, bukan sekadar diberi tahu ada yang gagal.
                if ($alasan = $import->getAlasan()) {
                    $msg .= ' - ' . implode('; ', $alasan);
                    if ($skipped > count($alasan)) {
                        $msg .= '; dan ' . ($skipped - count($alasan)) . ' lainnya';
                    }
                }
                $msg .= '.';
            }

            $this->log('import', 'TOEFL', 'Import Excel',
                "Import: {$created} baru, {$updated} diperbarui, {$skipped} dilewati");

            return redirect()->route('toefl_all.index')->with('success', $msg);

        } catch (ValidationException $e) {
            $errMsg = 'Import gagal karena kesalahan validasi: ';
            foreach (array_slice($e->failures(), 0, 3) as $failure) {
                $errMsg .= "Baris {$failure->row()}: " . implode(', ', $failure->errors()) . '. ';
            }
            return back()->with('error', $errMsg);

        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $this->checkSuperAdmin();

        return Excel::download(new TemplateToeflExport(), 'template-import-toefl.xlsx');
    }
}
