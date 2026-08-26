<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    use LogsActivity;

    public function __construct(private DatabaseBackupService $service)
    {
    }

    public function index()
    {
        $backups = $this->service->files();
        return view('backup.index', compact('backups'));
    }

    public function store()
    {
        try {
            $res = $this->service->run();
        } catch (\Throwable $e) {
            $this->log('gagal', 'Backup', '-', 'Backup database gagal: ' . $e->getMessage());
            return back()->with('error', 'Backup gagal: ' . $e->getMessage());
        }

        $this->log('tambah', 'Backup', $res['file'],
            'Membuat backup database (' . $this->humanSize($res['size']) . ')');

        return back()->with('success', "Backup berhasil dibuat: {$res['file']} (" . $this->humanSize($res['size']) . ').');
    }

    public function download(string $file): StreamedResponse
    {
        $path = $this->service->safePath($file);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        // Berkas backup memuat SELURUH isi database — pengunduhannya dicatat.
        $this->log('export', 'Backup', $file, 'Mengunduh berkas backup database');

        return Storage::disk('local')->download($path);
    }

    public function destroy(string $file)
    {
        $path = $this->service->safePath($file);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        Storage::disk('local')->delete($path);
        $this->log('hapus', 'Backup', $file, 'Menghapus berkas backup database');

        return back()->with('success', 'Backup dihapus.');
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        return number_format($bytes / 1024, 1) . ' KB';
    }
}
