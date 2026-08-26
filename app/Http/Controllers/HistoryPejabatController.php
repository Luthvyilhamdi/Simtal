<?php

namespace App\Http\Controllers;

use App\Models\HistoryJabatan;
use App\Models\HistoryPejabat;
use App\Models\User;
use App\Exports\HistoryPejabatExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryPejabatController extends Controller
{
    /** Urutan tampil pejabat aktif — tingkat tertinggi di atas. */
    private const URUTAN_JABATAN = ['SVP', 'VP', 'SPM', 'PM'];

    /** Hanya perpindahan ini yang ditampilkan di daftar "sudah selesai". */
    private const TIPE_SELESAI = ['promosi', 'rotasi'];
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
        $query = HistoryPejabat::with('karyawan');

        // Filter jabatan
        if ($request->jabatan) {
            $query->where('jabatan', $request->jabatan);
        }

        // Search
        if ($request->search) {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik', 'like', '%'.$request->search.'%');
            });
        }

        // Pisah aktif dan selesai.
        // Aktif diurutkan per tingkat jabatan (SVP paling atas), baru per tanggal.
        $urutan = "'" . implode("','", self::URUTAN_JABATAN) . "'";

        $aktif = (clone $query)
            ->whereNull('tanggal_selesai')
            ->orderByRaw("FIELD(jabatan, {$urutan})")
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(15, ['*'], 'page_aktif');

        // Satu jabatan pejabat ditutup tepat saat karyawan menerima jabatan baru
        // (tanggal_selesai = tanggal_mulai jabatan penggantinya). Jadi ALASAN
        // berakhirnya = tipe jabatan pengganti itu. Diambil lewat subquery agar
        // tidak menimbulkan query per-baris saat ditampilkan.
        $tipePengganti = HistoryJabatan::select('tipe')
            ->whereColumn('history_jabatans.karyawan_id', 'history_pejabats.karyawan_id')
            ->whereColumn('history_jabatans.tanggal_mulai', 'history_pejabats.tanggal_selesai')
            ->orderBy('id')
            ->limit(1);

        $selesai = (clone $query)
            ->whereNotNull('tanggal_selesai')
            ->select('history_pejabats.*')
            ->addSelect(['tipe_selesai' => $tipePengganti])
            // Tampilkan hanya yang berakhir karena promosi atau rotasi.
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                  ->from('history_jabatans')
                  ->whereColumn('history_jabatans.karyawan_id', 'history_pejabats.karyawan_id')
                  ->whereColumn('history_jabatans.tanggal_mulai', 'history_pejabats.tanggal_selesai')
                  ->whereIn('history_jabatans.tipe', self::TIPE_SELESAI);
            })
            ->orderBy('tanggal_selesai', 'desc')
            ->paginate(15, ['*'], 'page_selesai');

        // Stats
        $stats = [
            'total' => HistoryPejabat::whereNull('tanggal_selesai')->count(),
            'svp'   => HistoryPejabat::where('jabatan', 'SVP')->whereNull('tanggal_selesai')->count(),
            'vp'    => HistoryPejabat::where('jabatan', 'VP')->whereNull('tanggal_selesai')->count(),
            'spm'   => HistoryPejabat::where('jabatan', 'SPM')->whereNull('tanggal_selesai')->count(),
            'pm'    => HistoryPejabat::where('jabatan', 'PM')->whereNull('tanggal_selesai')->count(),
        ];

        return view('history_pejabat.index', compact('aktif', 'selesai', 'stats'));
    }

    public function export(Request $request)
    {
        $jabatan  = $request->jabatan;
        $search   = $request->search;
        $filename = 'history-pejabat-' . now()->format('d-m-Y') . '.xlsx';

        return (new HistoryPejabatExport($jabatan, $search))->download($filename);
    }

    /**
     * Hapus satu record History Pejabat — khusus super admin.
     * Catatan: record History Jabatan sumbernya TIDAK ikut terhapus.
     */
    public function destroy(HistoryPejabat $historyPejabat)
    {
        $this->checkSuperAdmin();

        $nama = optional($historyPejabat->karyawan)->nama ?? '-';
        $jab  = $historyPejabat->jabatan;

        $historyPejabat->delete();

        return redirect()->route('history_pejabat.index')
            ->with('success', "History pejabat {$jab} — {$nama} berhasil dihapus.");
    }
}