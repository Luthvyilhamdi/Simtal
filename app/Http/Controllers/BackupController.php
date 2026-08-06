<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
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
            return back()->with('error', 'Backup gagal: ' . $e->getMessage());
        }

        return back()->with('success', "Backup berhasil dibuat: {$res['file']} (" . $this->humanSize($res['size']) . ').');
    }

    public function download(string $file): StreamedResponse
    {
        $path = $this->service->safePath($file);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function destroy(string $file)
    {
        $path = $this->service->safePath($file);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        Storage::disk('local')->delete($path);
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
