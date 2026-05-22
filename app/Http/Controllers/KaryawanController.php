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
use App\Imports\KaryawanImport;
use App\Exports\TemplateKaryawanExport;
use App\Exports\KaryawanExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
{
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
            'jobGrades'     => JobGrade::all(),
            'personGrades'  => PersonGrade::all(),
            'jabatans'      => Jabatan::all(),
            'kodeStrukturs' => KodeStruktur::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'              => 'required|unique:karyawans,nik',
            'nama'             => 'required',
            'jenis_kelamin'    => 'required|in:L,P',
            'tempat_lahir'     => 'required',
            'tanggal_lahir'    => 'required|date',
            'tanggal_masuk'    => 'required|date',
            'jabatan_id'       => 'required',
            'direktorat_id'    => 'required',
            'kompartemen_id'   => 'required',
            'departemen_id'    => 'required',
            'job_grade_id'     => 'required',
            'person_grade_id'  => 'required',
            'kode_struktur_id' => 'required',
            'status'           => 'required',
            'jabatan_saat_ini' => 'required|string',
            'foto'             => 'nullable|image|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-karyawan', 'public');
        }

        Karyawan::create($data);
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan!');
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load(['direktorat','kompartemen','departemen','jobGrade','personGrade','jabatan','kodeStruktur']);
        return view('karyawan.show', compact('karyawan'));
    }

    public function edit(Karyawan $karyawan)
    {
        return view('karyawan.edit', [
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

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nik'              => 'required|unique:karyawans,nik,'.$karyawan->id,
            'nama'             => 'required',
            'jenis_kelamin'    => 'required|in:L,P',
            'tempat_lahir'     => 'required',
            'tanggal_lahir'    => 'required|date',
            'tanggal_masuk'    => 'required|date',
            'jabatan_id'       => 'required',
            'direktorat_id'    => 'required',
            'kompartemen_id'   => 'required',
            'departemen_id'    => 'required',
            'job_grade_id'     => 'required',
            'person_grade_id'  => 'required',
            'kode_struktur_id' => 'required',
            'status'           => 'required',
            'jabatan_saat_ini' => 'required|string',
            'foto'             => 'nullable|image|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
            $data['foto'] = $request->file('foto')->store('foto-karyawan', 'public');
        }

        $karyawan->update($data);
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diupdate!');
    }

    public function destroy(Karyawan $karyawan)
    {
        if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
        $karyawan->delete();
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
        return view('karyawan.import');
    }

    public function import(Request $request)
    {
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

        $imported = $import->getRowCount();
        $skipped  = $import->getSkippedCount();

        $msg = "Berhasil mengimport {$imported} karyawan.";
        if ($skipped > 0) $msg .= " {$skipped} data dilewati (NIK duplikat).";

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
        return Excel::download(
            new TemplateKaryawanExport(),
            'template-import-karyawan.xlsx'
        );
    }
}