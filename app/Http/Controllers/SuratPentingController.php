<?php

namespace App\Http\Controllers;

use App\Models\SuratPenting;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratPentingController extends Controller
{
    // Index global semua surat
    public function index(Request $request)
    {
        $query = SuratPenting::with(['karyawan', 'uploader'])
            ->orderBy('tanggal_surat', 'desc');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('judul', 'like', '%'.$request->search.'%')
                  ->orWhere('nomor_surat', 'like', '%'.$request->search.'%')
                  ->orWhereHas('karyawan', function($q2) use ($request) {
                      $q2->where('nama', 'like', '%'.$request->search.'%');
                  });
            });
        }

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->karyawan_id) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $surats   = $query->paginate(15);
        $karyawans = Karyawan::orderBy('nama')->get();

        $stats = [
            'total'   => SuratPenting::count(),
            'expire'  => SuratPenting::whereNotNull('tanggal_exp')->where('tanggal_exp', '<', now())->count(),
            'soon'    => SuratPenting::whereNotNull('tanggal_exp')->whereBetween('tanggal_exp', [now(), now()->addDays(30)])->count(),
        ];

        return view('surat_penting.index', compact('surats', 'karyawans', 'stats'));
    }

    // Form upload
    public function create()
    {
        $karyawans = Karyawan::orderBy('nama')->get();
        return view('surat_penting.create', compact('karyawans'));
    }

    // Simpan
    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'   => 'required|exists:karyawans,id',
            'judul'         => 'required|string|max:255',
            'nomor_surat'   => 'nullable|string|max:255',
            'kategori'      => 'required|in:sk_jabatan,sk_promosi,sk_mutasi,sk_pensiun,surat_tugas,surat_peringatan,kontrak,sertifikat,lainnya',
            'tanggal_surat' => 'required|date',
            'tanggal_exp'   => 'nullable|date|after:tanggal_surat',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'keterangan'    => 'nullable|string',
        ]);

        $file     = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('surat-penting', $fileName, 'public');
        $fileSize = $this->formatFileSize($file->getSize());

        SuratPenting::create([
            'karyawan_id'   => $request->karyawan_id,
            'judul'         => $request->judul,
            'nomor_surat'   => $request->nomor_surat,
            'kategori'      => $request->kategori,
            'tanggal_surat' => $request->tanggal_surat,
            'tanggal_exp'   => $request->tanggal_exp,
            'file_path'     => $filePath,
            'file_name'     => $file->getClientOriginalName(),
            'file_size'     => $fileSize,
            'keterangan'    => $request->keterangan,
            'uploaded_by'   => auth()->id(),
        ]);

        return redirect()
            ->route('surat_penting.index')
            ->with('success', 'Surat berhasil diupload!');
    }

    // Preview / Download
    public function show(SuratPenting $suratPenting)
    {
        return response()->file(storage_path('app/public/' . $suratPenting->file_path));
    }

    // Download
    public function download(SuratPenting $suratPenting)
    {
        return Storage::disk('public')->download(
            $suratPenting->file_path,
            $suratPenting->file_name
        );
    }

    // Hapus
    public function destroy(SuratPenting $suratPenting)
    {
        Storage::disk('public')->delete($suratPenting->file_path);
        $suratPenting->delete();

        return redirect()
            ->route('surat_penting.index')
            ->with('success', 'Surat berhasil dihapus!');
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}