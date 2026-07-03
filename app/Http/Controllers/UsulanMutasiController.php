<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\UsulanMutasi;
use App\Models\HistoryJabatan;
use App\Models\Jabatan;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\KodeStruktur;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsulanMutasiController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $search = $request->search;
        $with = ['karyawan', 'jabatanTujuan', 'direktoratTujuan', 'kompartemenTujuan',
                 'departemenTujuan', 'karyawan.direktorat', 'karyawan.kompartemen', 'karyawan.departemen', 'createdBy'];

        $build = function ($done) use ($search, $with) {
            $q = UsulanMutasi::with($with)->where('sk_diproses', $done)->orderByDesc('created_at');
            if ($search) {
                $q->whereHas('karyawan', fn($k) =>
                    $k->where('nama', 'like', '%'.$search.'%')->orWhere('nik', 'like', '%'.$search.'%'));
            }
            return $q;
        };

        $statusGroups = [
            'menunggu' => $build(false)->paginate(10, ['*'], 'page_menunggu')->appends(request()->query()),
            'selesai'  => $build(true)->paginate(10, ['*'], 'page_selesai')->appends(request()->query()),
        ];
        $counts = [
            'menunggu' => $build(false)->count(),
            'selesai'  => $build(true)->count(),
        ];
        $activeTab = $request->tab ?? 'menunggu';

        return view('usulan_mutasi.index', compact('statusGroups', 'counts', 'activeTab'));
    }

    public function create()
    {
        return view('usulan_mutasi.create', [
            'jabatans'      => Jabatan::orderBy('nama_jabatan')->get(),
            'direktorats'   => Direktorat::all(),
            'kompartemens'  => Kompartemen::all(),
            'departemens'   => Departemen::all(),
            'kodeStrukturs' => KodeStruktur::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'             => 'required|exists:karyawans,id',
            'jenis'                   => 'required|in:rotasi,mutasi',
            'jabatan_tujuan'          => 'required|string|max:255',   // teks (label)
            'jabatan_tujuan_id'       => 'required|exists:jabatan,id', // master (struktur)
            'direktorat_tujuan_id'    => 'required|exists:direktorat,id',
            'kompartemen_tujuan_id'   => 'required|exists:kompartemen,id',
            'departemen_tujuan_id'    => 'required|exists:departemen,id',
            'kode_struktur_tujuan_id' => 'required|exists:kode_struktur,id',
            'alasan'                  => 'nullable|string|max:1000',
            'tanggal_usulan'          => 'nullable|date',
        ]);

        $karyawan = Karyawan::with(['jabatan', 'direktorat', 'kompartemen', 'departemen', 'jobGrade', 'personGrade'])
            ->findOrFail($request->karyawan_id);

        UsulanMutasi::create([
            'karyawan_id'             => $karyawan->id,
            'jenis'                   => $request->jenis,
            // snapshot posisi awal
            'jabatan_saat_ini'        => $karyawan->jabatan_saat_ini ?: optional($karyawan->jabatan)->nama_jabatan,
            'direktorat_saat_ini'     => optional($karyawan->direktorat)->nama_direktorat ?? optional($karyawan->direktorat)->nama,
            'kompartemen_saat_ini'    => optional($karyawan->kompartemen)->nama_kompartemen,
            'departemen_saat_ini'     => optional($karyawan->departemen)->nama_departemen,
            'job_grade_saat_ini'      => optional($karyawan->jobGrade)->job_grade,
            'person_grade_saat_ini'   => optional($karyawan->personGrade)->person_grade,
            // tujuan: teks + master
            'jabatan_tujuan'          => $request->jabatan_tujuan,      // teks (label)
            'jabatan_tujuan_id'       => $request->jabatan_tujuan_id,   // master (struktur)
            'direktorat_tujuan_id'    => $request->direktorat_tujuan_id,
            'kompartemen_tujuan_id'   => $request->kompartemen_tujuan_id,
            'departemen_tujuan_id'    => $request->departemen_tujuan_id,
            'kode_struktur_tujuan_id' => $request->kode_struktur_tujuan_id,
            'alasan'                  => $request->alasan,
            'tanggal_usulan'          => $request->tanggal_usulan,
            'status'                  => 'diajukan',
            'created_by'              => Auth::id(),
        ]);

        $this->log('tambah', 'Usulan ' . ucfirst($request->jenis), $karyawan->nama,
            'Usulan ' . $request->jenis . ' diajukan');

        return redirect()->route('usulan_mutasi.index')
            ->with('success', 'Usulan ' . ($request->jenis === 'rotasi' ? 'rotasi' : 'mutasi') . ' berhasil ditambahkan!');
    }

    /**
     * Terbitkan SK rotasi/mutasi. Grade TIDAK berubah.
     * jabatan_id  → diambil dari jabatan master tujuan (jabatan_tujuan_id)
     * jabatan_saat_ini (label) → diambil dari teks jabatan_tujuan
     */
    public function terbitkanSk(Request $request, UsulanMutasi $usulanMutasi)
    {
        $request->validate([
            'no_sk'      => 'required|string|max:255',
            'tmt'        => 'required|date',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        if ($usulanMutasi->sk_diproses) {
            return back()->with('error', 'SK untuk usulan ini sudah pernah diterbitkan.');
        }

        $karyawan    = Karyawan::findOrFail($usulanMutasi->karyawan_id);
        $jabatanBaru = Jabatan::find($usulanMutasi->jabatan_tujuan_id);
        // Label = teks jabatan tujuan; fallback ke nama master, lalu snapshot lama
        $namaJabatan = $usulanMutasi->jabatan_tujuan
            ?: ($jabatanBaru->nama_jabatan ?? $usulanMutasi->jabatan_saat_ini);
        $tmt         = $request->tmt;

        if (!$usulanMutasi->jabatan_tujuan_id || !$karyawan->job_grade_id || !$karyawan->person_grade_id) {
            return back()->with('error', 'Data jabatan tujuan / grade karyawan belum lengkap.');
        }

        DB::transaction(function () use ($request, $usulanMutasi, $karyawan, $namaJabatan, $tmt) {

            HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->where('is_current', true)
                ->update(['is_current' => false, 'tanggal_selesai' => $tmt]);

            HistoryJabatan::create([
                'karyawan_id'      => $karyawan->id,
                'jabatan_id'       => $usulanMutasi->jabatan_tujuan_id,   // master (struktur)
                'jabatan_saat_ini' => $namaJabatan,                       // teks (label)
                'direktorat_id'    => $usulanMutasi->direktorat_tujuan_id,
                'kompartemen_id'   => $usulanMutasi->kompartemen_tujuan_id,
                'departemen_id'    => $usulanMutasi->departemen_tujuan_id,
                'job_grade_id'     => $karyawan->job_grade_id,      // tetap
                'person_grade_id'  => $karyawan->person_grade_id,   // tetap
                'kode_struktur_id' => $usulanMutasi->kode_struktur_tujuan_id,
                'tanggal_mulai'    => $tmt,
                'tanggal_selesai'  => null,
                'tipe'             => $usulanMutasi->jenis, // rotasi / mutasi mengikuti jenis usulan
                'keterangan'       => $request->keterangan
                    ?: (ucfirst($usulanMutasi->jenis) . '. No. SK: ' . $request->no_sk),
                'no_sk'            => $request->no_sk,
                'tanggal_sk'       => $tmt,
                'is_current'       => true,
            ]);

            $karyawan->update([
                'jabatan_id'       => $usulanMutasi->jabatan_tujuan_id,
                'jabatan_saat_ini' => $namaJabatan,
                'direktorat_id'    => $usulanMutasi->direktorat_tujuan_id,
                'kompartemen_id'   => $usulanMutasi->kompartemen_tujuan_id,
                'departemen_id'    => $usulanMutasi->departemen_tujuan_id,
                'kode_struktur_id' => $usulanMutasi->kode_struktur_tujuan_id,
            ]);

            $usulanMutasi->update([
                'no_sk'       => $request->no_sk,
                'tmt'         => $tmt,
                'sk_diproses' => true,
                'status'      => 'selesai',
            ]);
        });

        $this->log('edit', 'Usulan ' . ucfirst($usulanMutasi->jenis), $karyawan->nama,
            'Terbit SK ' . $request->no_sk . ' -> ' . $namaJabatan . ' (TMT ' . $tmt . ')');

        return redirect()->route('usulan_mutasi.index', ['tab' => 'selesai'])
            ->with('success', 'SK berhasil diterbitkan. Riwayat jabatan & posisi karyawan diperbarui!');
    }

    public function destroy(UsulanMutasi $usulanMutasi)
    {
        $nama  = $usulanMutasi->karyawan->nama;
        $jenis = $usulanMutasi->jenis;
        $usulanMutasi->delete();

        $this->log('hapus', 'Usulan ' . ucfirst($jenis), $nama, 'Hapus usulan ' . $jenis);

        return redirect()->route('usulan_mutasi.index')
            ->with('success', 'Usulan berhasil dihapus!');
    }
}