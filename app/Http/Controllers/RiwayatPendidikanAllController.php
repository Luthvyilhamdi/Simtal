<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use App\Imports\RiwayatPendidikanImport;
use App\Exports\RiwayatPendidikanExport;
use App\Exports\TemplateRiwayatPendidikanExport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman GLOBAL Riwayat Pendidikan (semua karyawan) — khusus Super Admin.
 * Menyediakan daftar + Import massal (per baris = per jenjang, kunci NIK+Jenjang)
 * + unduh template. Export data dilakukan lewat Export Builder (kolom
 * "Riwayat Pendidikan" pada grup Data Diri).
 */
class RiwayatPendidikanAllController extends Controller
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
        $this->checkSuperAdmin();

        // 1 baris = 1 karyawan (yang punya data pendidikan). Detail per jenjang
        // dilihat di halaman karyawan (riwayat_pendidikan.index).
        $query = Karyawan::query()
            ->has('riwayatPendidikan')
            ->withCount('riwayatPendidikan')
            ->with(['jabatan', 'departemen'])
            ->orderBy('nama');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        $karyawans = $query->paginate(15)->withQueryString();

        return view('riwayat_pendidikan_all.index', compact('karyawans'));
    }

    public function export(Request $request)
    {
        $this->checkSuperAdmin();

        $filename = 'history-pendidikan-' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(new RiwayatPendidikanExport($request->search), $filename);
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
            $import = new RiwayatPendidikanImport();
            Excel::import($import, $request->file('file'));
            $import->refreshAffected();

            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();

            $msg = "Import selesai: {$created} data baru, {$updated} diperbarui.";
            if ($skipped > 0) $msg .= " {$skipped} baris dilewati (NIK tidak ditemukan / jenjang tidak valid / kosong).";

            $this->log('import', 'History Pendidikan', 'Import Excel', "Import: {$created} baru, {$updated} diperbarui");

            return redirect()->route('riwayat_pendidikan_all.index')->with('success', $msg);

        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errMsg   = 'Import gagal karena kesalahan validasi: ';
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
        $this->checkSuperAdmin();

        return Excel::download(
            new TemplateRiwayatPendidikanExport(),
            'template-import-riwayat-pendidikan.xlsx'
        );
    }

    public function destroy(RiwayatPendidikan $riwayatPendidikan)
    {
        $this->checkSuperAdmin();

        $karyawan = $riwayatPendidikan->karyawan;
        $jenjang  = $riwayatPendidikan->jenjang;
        $riwayatPendidikan->delete();
        $karyawan?->refreshPendidikanTerakhir();

        $this->log('hapus', 'History Pendidikan', $karyawan->nama ?? '-', 'Jenjang: ' . $jenjang);

        return redirect()->route('riwayat_pendidikan_all.index')->with('success', 'Data history pendidikan berhasil dihapus!');
    }
}
