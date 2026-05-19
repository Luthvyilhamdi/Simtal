<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\HistoryPejabat;
use App\Models\Jabatan;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobGrade;
use App\Models\PersonGrade;
use App\Models\KodeStruktur;
use App\Exports\HistoryJabatanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryJabatanController extends Controller
{
    public function index(Karyawan $karyawan)
    {
        $karyawan->load(['jabatan', 'departemen', 'direktorat', 'jobGrade']);
        $histories = $karyawan->historyJabatan()
            ->with(['jabatan', 'direktorat', 'kompartemen', 'departemen', 'jobGrade', 'personGrade', 'kodeStruktur'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        return view('history_jabatan.index', compact('karyawan', 'histories'));
    }

    public function create(Karyawan $karyawan)
    {
        return view('history_jabatan.create', [
            'karyawan'      => $karyawan,
            'direktorats'   => Direktorat::all(),
            'kompartemens'  => Kompartemen::all(),
            'departemens'   => Departemen::all(),
            'jobGrades'     => JobGrade::all(),
            'personGrades'  => PersonGrade::all(),
            'jabatans'      => Jabatan::all(),
            'kodeStrukturs' => KodeStruktur::all(),
        ]);
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'jabatan_id'       => 'required',
            'direktorat_id'    => 'required',
            'kompartemen_id'   => 'required',
            'departemen_id'    => 'required',
            'job_grade_id'     => 'required',
            'person_grade_id'  => 'required',
            'kode_struktur_id' => 'required',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after:tanggal_mulai',
            'tipe'             => 'required|in:mutasi,promosi,demosi,onboarding',
            'keterangan'       => 'nullable|string',
            'no_sk'            => 'nullable|string',
            'tanggal_sk'       => 'nullable|date',
            'jabatan_saat_ini' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $karyawan) {

            // === 1. Ambil jabatan lama sebelum diubah ===
            $historyLama = HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->where('is_current', true)
                ->first();

            $jabatanLamaModel = $historyLama
                ? Jabatan::find($historyLama->jabatan_id)
                : null;

            // === 2. Tutup history jabatan lama ===
            HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->where('is_current', true)
                ->update([
                    'is_current'      => false,
                    'tanggal_selesai' => $request->tanggal_mulai,
                ]);

            // === 3. Buat history jabatan baru ===
            $historyBaru = HistoryJabatan::create([
                'karyawan_id'      => $karyawan->id,
                'jabatan_id'       => $request->jabatan_id,
                'jabatan_saat_ini' => $request->jabatan_saat_ini,
                'direktorat_id'    => $request->direktorat_id,
                'kompartemen_id'   => $request->kompartemen_id,
                'departemen_id'    => $request->departemen_id,
                'job_grade_id'     => $request->job_grade_id,
                'person_grade_id'  => $request->person_grade_id,
                'kode_struktur_id' => $request->kode_struktur_id,
                'tanggal_mulai'    => $request->tanggal_mulai,
                'tanggal_selesai'  => $request->tanggal_selesai,
                'tipe'             => $request->tipe,
                'keterangan'       => $request->keterangan,
                'no_sk'            => $request->no_sk,
                'tanggal_sk'       => $request->tanggal_sk,
                'is_current'       => true,
            ]);

            // === 4. Update profil karyawan ===
            $karyawan->update([
                'jabatan_id'       => $request->jabatan_id,
                'direktorat_id'    => $request->direktorat_id,
                'kompartemen_id'   => $request->kompartemen_id,
                'departemen_id'    => $request->departemen_id,
                'job_grade_id'     => $request->job_grade_id,
                'person_grade_id'  => $request->person_grade_id,
                'kode_struktur_id' => $request->kode_struktur_id,
                'jabatan_saat_ini' => $request->jabatan_saat_ini,
            ]);

            // === 5. Load relasi untuk history pejabat ===
            $karyawan->load(['direktorat', 'kompartemen', 'departemen', 'jobGrade', 'personGrade']);
            $jabatanBaru = Jabatan::find($request->jabatan_id);

            // === 6. Jika jabatan LAMA adalah SVP/VP/SPM/PM → tutup history pejabat ===
            if ($jabatanLamaModel && HistoryPejabat::isDipantau($jabatanLamaModel->nama_jabatan)) {
                HistoryPejabat::where('karyawan_id', $karyawan->id)
                    ->whereNull('tanggal_selesai')
                    ->update(['tanggal_selesai' => $request->tanggal_mulai]);
            }

            // === 7. Jika jabatan BARU adalah SVP/VP/SPM/PM → buat history pejabat baru ===
            if ($jabatanBaru && HistoryPejabat::isDipantau($jabatanBaru->nama_jabatan)) {
                HistoryPejabat::create([
                    'karyawan_id'        => $karyawan->id,
                    'history_jabatan_id' => $historyBaru->id,
                    'jabatan'            => HistoryPejabat::ekstrakTipe($jabatanBaru->nama_jabatan),
                    'jabatan_saat_ini'   => $request->jabatan_saat_ini,
                    'direktorat'         => $karyawan->direktorat->nama_direktorat ?? null,
                    'kompartemen'        => $karyawan->kompartemen->nama_kompartemen ?? null,
                    'departemen'         => $karyawan->departemen->nama_departemen ?? null,
                    'job_grade'          => $karyawan->jobGrade->job_grade ?? null,
                    'person_grade'       => $karyawan->personGrade->person_grade ?? null,
                    'no_sk'              => $request->no_sk,
                    'tanggal_sk'         => $request->tanggal_sk,
                    'tanggal_mulai'      => $request->tanggal_mulai,
                    'tanggal_selesai'    => null,
                    'keterangan'         => $request->keterangan,
                ]);
            }
        });

        return redirect()
            ->route('history_jabatan.index', $karyawan)
            ->with('success', 'History jabatan berhasil ditambahkan & profil karyawan diperbarui!');
    }

    public function destroy(Karyawan $karyawan, HistoryJabatan $historyJabatan)
    {
        $wasCurrent = $historyJabatan->is_current;

        // Cek apakah jabatan yang dihapus adalah jabatan dipantau
        $jabatanModel = Jabatan::find($historyJabatan->jabatan_id);
        $isDipantau   = $jabatanModel && HistoryPejabat::isDipantau($jabatanModel->nama_jabatan);

        $historyJabatan->delete();

        if ($wasCurrent) {
            // Kembalikan jabatan sebelumnya sebagai current
            $prev = HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->orderBy('tanggal_mulai', 'desc')
                ->first();

            if ($prev) {
                $prev->update(['is_current' => true, 'tanggal_selesai' => null]);
                $karyawan->update([
                    'jabatan_id'       => $prev->jabatan_id,
                    'direktorat_id'    => $prev->direktorat_id,
                    'kompartemen_id'   => $prev->kompartemen_id,
                    'departemen_id'    => $prev->departemen_id,
                    'job_grade_id'     => $prev->job_grade_id,
                    'person_grade_id'  => $prev->person_grade_id,
                    'kode_struktur_id' => $prev->kode_struktur_id,
                ]);
            }

            // Jika jabatan yang dihapus adalah jabatan dipantau
            // → hapus juga history pejabat yang terkait
            if ($isDipantau) {
                HistoryPejabat::where('karyawan_id', $karyawan->id)
                    ->whereNull('tanggal_selesai')
                    ->delete();
            }
        }

        return redirect()
            ->route('history_jabatan.index', $karyawan)
            ->with('success', 'History jabatan berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $search   = $request->search;
        $tipe     = $request->tipe;
        $filename = 'history-jabatan-' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(
            new HistoryJabatanExport($search, $tipe),
            $filename
        );
    }
}