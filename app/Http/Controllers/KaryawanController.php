<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobGrade;
use App\Models\PersonGrade;
use App\Models\Jabatan;
use App\Models\KodeStruktur;
use App\Models\TalentPool;
use App\Models\User;
use App\Imports\KaryawanImport;
use App\Exports\TemplateKaryawanExport;
use App\Exports\KaryawanExport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
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
        $query = Karyawan::with(['direktorat','kompartemen','departemen','jobGrade','personGrade','jabatan','kodeStruktur']);

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik', 'like', '%'.$request->search.'%');
        }

        $karyawans = $query->latest()->paginate(10);
        return view('karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        return view('karyawan.create', [
            'direktorats'   => Direktorat::all(),
            'kompartemens'  => Kompartemen::all(),
            'departemens'   => Departemen::all(),
            'jobGrades'     => JobGrade::orderByRaw('CAST(job_grade AS UNSIGNED)')->get(),
            'personGrades'  => PersonGrade::orderByRaw('CAST(person_grade AS UNSIGNED)')->get(),
            'jabatans'      => Jabatan::all(),
            'kodeStrukturs' => KodeStruktur::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'                => 'required|unique:karyawans,nik',
            'nama'               => 'required',
            'jenis_kelamin'      => 'required|in:L,P',
            'tempat_lahir'       => 'required',
            'tanggal_lahir'      => 'required|date',
            'tanggal_masuk'      => 'required|date',
            'no_hp'              => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:255',
            'pend_jenjang'       => 'nullable|array',
            'pend_jenjang.*'     => 'nullable|string|max:20',
            'pend_jurusan.*'     => 'nullable|string|max:255',
            'pend_institusi.*'   => 'nullable|string|max:255',
            'jabatan_id'         => 'required',
            'direktorat_id'      => 'required',
            'kompartemen_id'     => 'required',
            'departemen_id'      => 'required',
            'job_grade_id'       => 'required',
            'person_grade_id'    => 'required',
            'kode_struktur_id'   => 'required',
            'status'             => 'required',
            'status_kepegawaian' => 'nullable|in:'.implode(',', \App\Models\Karyawan::STATUS_KEPEGAWAIAN),
            'jabatan_saat_ini'        => 'required|string',
            'struktural_fungsional'  => 'nullable|in:Struktural,Fungsional',
            'foto'               => 'nullable|image|max:2048',
            'tanggal_mulai_pg'   => 'nullable|date',
            'tanggal_mulai_jg'   => 'nullable|date',
            'tanggal_mulai_band' => 'nullable|date',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-karyawan', 'public');
        }

        $karyawan = Karyawan::create($data);

        // Riwayat pendidikan + set Pendidikan Terakhir (jenjang tertinggi).
        $karyawan->update($karyawan->syncRiwayatPendidikan(
            (array) $request->input('pend_jenjang', []),
            (array) $request->input('pend_jurusan', []),
            (array) $request->input('pend_institusi', [])
        ));

        $this->log('tambah', 'Karyawan', $request->nama, 'NIK: ' . $request->nik);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan!');
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load(['direktorat','kompartemen','departemen','jobGrade','personGrade','jabatan','kodeStruktur','strukturAssignments','riwayatPendidikan']);

        // Cek shortlist — prioritas tahun ini, fallback tahun lalu
        $talentShortlist = TalentPool::where('karyawan_id', $karyawan->id)
            ->where('klasifikasi', 'shortlist')
            ->whereIn('periode', [now()->year, now()->year - 1])
            ->orderBy('periode', 'desc')
            ->first();

        $isShortlist      = $talentShortlist !== null;
        $shortlistPeriode = $talentShortlist?->periode;

        return view('karyawan.show', compact('karyawan', 'isShortlist', 'shortlistPeriode'));
    }

    public function edit(Karyawan $karyawan)
    {
        return view('karyawan.edit', [
            'karyawan'      => $karyawan,
            'direktorats'   => Direktorat::all(),
            'kompartemens'  => Kompartemen::all(),
            'departemens'   => Departemen::all(),
            'jobGrades'     => JobGrade::orderByRaw('CAST(job_grade AS UNSIGNED)')->get(),
            'personGrades'  => PersonGrade::orderByRaw('CAST(person_grade AS UNSIGNED)')->get(),
            'jabatans'      => Jabatan::all(),
            'kodeStrukturs' => KodeStruktur::all(),
        ]);
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nik'                => 'required|unique:karyawans,nik,'.$karyawan->id,
            'nama'               => 'required',
            'jenis_kelamin'      => 'required|in:L,P',
            'tempat_lahir'       => 'required',
            'tanggal_lahir'      => 'required|date',
            'tanggal_masuk'      => 'required|date',
            'no_hp'              => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:255',
            'pend_jenjang'       => 'nullable|array',
            'pend_jenjang.*'     => 'nullable|string|max:20',
            'pend_jurusan.*'     => 'nullable|string|max:255',
            'pend_institusi.*'   => 'nullable|string|max:255',
            'jabatan_id'         => 'required',
            'direktorat_id'      => 'required',
            'kompartemen_id'     => 'required',
            'departemen_id'      => 'required',
            'job_grade_id'       => 'required',
            'person_grade_id'    => 'required',
            'kode_struktur_id'   => 'required',
            'status'             => 'required',
            'status_kepegawaian' => 'nullable|in:'.implode(',', \App\Models\Karyawan::STATUS_KEPEGAWAIAN),
            'jabatan_saat_ini'        => 'required|string',
            'struktural_fungsional'  => 'nullable|in:Struktural,Fungsional',
            'foto'               => 'nullable|image|max:2048',
            'tanggal_mulai_pg'   => 'nullable|date',
            'tanggal_mulai_jg'   => 'nullable|date',
            'tanggal_mulai_band' => 'nullable|date',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
            $data['foto'] = $request->file('foto')->store('foto-karyawan', 'public');
        }

        $karyawan->update($data);

        // Riwayat pendidikan + set Pendidikan Terakhir (jenjang tertinggi).
        $karyawan->update($karyawan->syncRiwayatPendidikan(
            (array) $request->input('pend_jenjang', []),
            (array) $request->input('pend_jurusan', []),
            (array) $request->input('pend_institusi', [])
        ));

        $this->log('edit', 'Karyawan', $karyawan->nama, 'NIK: ' . $karyawan->nik);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diupdate!');
    }

    public function destroy(Karyawan $karyawan)
    {
        $nama = $karyawan->nama;
        $nik  = $karyawan->nik;

        if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
        $karyawan->delete();

        $this->log('hapus', 'Karyawan', $nama, 'NIK: ' . $nik);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $filename = 'data-karyawan-' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(
            new KaryawanExport($request->search, $request->status),
            $filename
        );
    }

    // ===== IMPORT =====
    public function importPage()
    {
        $this->checkSuperAdmin();
        return view('karyawan.import');
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
            $import = new KaryawanImport();
            Excel::import($import, $request->file('file'));

            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();

            $msg = "Import selesai: {$created} karyawan baru, {$updated} diperbarui.";
            if ($skipped > 0) $msg .= " {$skipped} baris dilewati (NIK kosong atau data baru tanpa nama).";

            $this->log('import', 'Karyawan', 'Import Excel', "Import: {$created} baru, {$updated} diperbarui");

            return redirect()->route('karyawan.index')->with('success', $msg);

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
            new TemplateKaryawanExport(),
            'template-import-karyawan.xlsx'
        );
    }
}