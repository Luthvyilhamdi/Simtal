<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\UsulanPromosi;
use App\Models\HistoryAssessment;
use App\Models\HistoryJabatan;
use App\Models\TalentPool;
use App\Models\PenilaianKaryawan;
use App\Models\KalibrasiKaryawan;
use App\Models\Jabatan;
use App\Models\JobGrade;
use App\Models\PersonGrade;
use App\Models\KodeStruktur;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsulanPromosiController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $search = $request->search;

        $with = ['karyawan', 'karyawan.departemen', 'karyawan.kompartemen', 'karyawan.direktorat',
                 'direktoratTujuan', 'kompartemenTujuan', 'departemenTujuan', 'createdBy'];

        $baseQuery = function($status) use ($search, $with) {
            $q = UsulanPromosi::with($with)
                ->where('status', $status)
                ->orderBy('created_at', 'desc');
            if ($search) {
                $q->whereHas('karyawan', fn($kq) =>
                    $kq->where('nama', 'like', '%'.$search.'%')
                       ->orWhere('nik',  'like', '%'.$search.'%')
                );
            }
            return $q;
        };

        $tanpaSidangQuery = function() use ($search, $with) {
            $q = UsulanPromosi::with($with)
                ->where('status', 'lulus')
                ->where('hasil_sidang', 'tanpa_sidang')
                ->orderBy('created_at', 'desc');
            if ($search) {
                $q->whereHas('karyawan', fn($kq) =>
                    $kq->where('nama', 'like', '%'.$search.'%')
                       ->orWhere('nik',  'like', '%'.$search.'%')
                );
            }
            return $q;
        };

        $statusGroups = [
            'draft'        => $baseQuery('draft')->paginate(10, ['*'], 'page_draft')->appends(request()->query()),
            'verif_berkas' => $baseQuery('verif_berkas')->paginate(10, ['*'], 'page_verif')->appends(request()->query()),
            'sidang'       => $baseQuery('sidang')->paginate(10, ['*'], 'page_sidang')->appends(request()->query()),
            'lulus'        => $baseQuery('lulus')->paginate(10, ['*'], 'page_lulus')->appends(request()->query()),
            'tidak_lulus'  => $baseQuery('tidak_lulus')->paginate(10, ['*'], 'page_tidak_lulus')->appends(request()->query()),
            'tanpa_sidang' => $tanpaSidangQuery()->paginate(10, ['*'], 'page_tanpa')->appends(request()->query()),
            'ditolak'      => $baseQuery('ditolak')->paginate(10, ['*'], 'page_ditolak')->appends(request()->query()),
        ];

        $counts = [
            'draft'        => $baseQuery('draft')->count(),
            'verif_berkas' => $baseQuery('verif_berkas')->count(),
            'sidang'       => $baseQuery('sidang')->count(),
            'lulus'        => $baseQuery('lulus')->count(),
            'tidak_lulus'  => $baseQuery('tidak_lulus')->count(),
            'tanpa_sidang' => $tanpaSidangQuery()->count(),
            'ditolak'      => $baseQuery('ditolak')->count(),
        ];

        $activeTab = $request->tab ?? 'draft';

        // Master data untuk form Terbit SK (dropdown)
        $jabatans      = Jabatan::orderBy('nama_jabatan')->get();
        $jobGrades     = JobGrade::orderByRaw('CAST(job_grade AS UNSIGNED)')->get();
        $personGrades  = PersonGrade::orderByRaw('CAST(person_grade AS UNSIGNED)')->get();
        $kodeStrukturs = KodeStruktur::all();
        $direktorats   = Direktorat::all();
        $kompartemens  = Kompartemen::all();
        $departemens   = Departemen::all();

        return view('usulan_promosi.index', compact(
            'statusGroups', 'counts', 'activeTab',
            'jabatans', 'jobGrades', 'personGrades', 'kodeStrukturs',
            'direktorats', 'kompartemens', 'departemens'
        ));
    }

    public function create()
    {
        $karyawans    = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        $jabatans     = Jabatan::orderBy('nama_jabatan')->get();
        $direktorats  = Direktorat::all();
        $kompartemens = Kompartemen::all();
        $departemens  = Departemen::all();

        return view('usulan_promosi.create', compact('karyawans', 'jabatans', 'direktorats', 'kompartemens', 'departemens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'           => 'required|exists:karyawans,id',
            'jabatan_tujuan'        => 'required|string|max:255',
            'jabatan_tujuan_id'     => 'required|exists:jabatan,id',
            'job_grade_promosi'     => 'nullable|string',
            'person_grade_promosi'  => 'nullable|string',
            'direktorat_tujuan_id'  => 'nullable|exists:direktorat,id',
            'kompartemen_tujuan_id' => 'nullable|exists:kompartemen,id',
            'departemen_tujuan_id'  => 'nullable|exists:departemen,id',
            'assessment_id'         => 'nullable|exists:history_assessments,id',
            'tanggal_usulan'        => 'nullable|date',
            'absensi'               => 'nullable|string',
            'kehadiran'             => 'nullable|string',
            'periode_penilaian'     => 'nullable|string',
            'tata_kelola'           => 'nullable|string',
            'mc_tersedia'           => 'nullable|boolean',
            'hasil_evaluasi'        => 'nullable|string',
            'catatan'               => 'nullable|string|max:1000',
        ]);

        $karyawan = Karyawan::with(['jobGrade', 'personGrade', 'departemen', 'kompartemen'])->find($request->karyawan_id);

        $tahunIni = now()->year;

        // Cek shortlist talent pool tahun lalu — menentukan threshold MDG
        $talentPool = TalentPool::where('karyawan_id', $karyawan->id)
            ->where('periode', $tahunIni - 1)
            ->first();

        $isShortlist = $talentPool && $talentPool->klasifikasi === 'shortlist';

        // Threshold MDG: shortlist lebih longgar (Band 24 bln, JG 12 bln), normal (Band 36 bln, JG 24 bln)
        $minBand = $isShortlist ? 24 : 36;
        $minJg   = $isShortlist ? 12 : 24;
        $minPg   = 12;

        $mdgBandOk = ($karyawan->mdg_band_bulan ?? 0) >= $minBand;
        $mdgJgOk   = ($karyawan->mdg_jg_bulan   ?? 0) >= $minJg;
        $mdgPgOk   = ($karyawan->mdg_pg_bulan   ?? 0) >= $minPg;

        // KPI Tahunan saja, 4 tahun terakhir (tidak include tahun berjalan)
        $kpiSnapshot = PenilaianKaryawan::where('karyawan_id', $karyawan->id)
            ->where('tipe', 'KPI')
            ->where('periode', 'tahunan')
            ->whereIn('tahun', [
                $tahunIni - 1,
                $tahunIni - 2,
                $tahunIni - 3,
                $tahunIni - 4,
            ])
            ->orderBy('tahun', 'desc')
            ->get(['tahun', 'periode', 'judul', 'nilai'])
            ->toArray();

        // Kalibrasi 3 tahun sebelum tahun berjalan (tidak include tahun berjalan)
        $kalibrasiSnapshot = KalibrasiKaryawan::where('karyawan_id', $karyawan->id)
            ->whereIn('tahun', [
                $tahunIni - 1,
                $tahunIni - 2,
                $tahunIni - 3,
            ])
            ->orderBy('tahun', 'desc')
            ->get(['tahun', 'nilai', 'keterangan'])
            ->toArray();

        // $talentPool sudah di-query di atas, tidak perlu query ulang

        $assessment      = null;
        $hasilAssessment = null;
        $tanggalBerlaku  = null;
        $levelUkur       = null;
        if ($request->assessment_id) {
            $assessment      = HistoryAssessment::find($request->assessment_id);
            $hasilAssessment = $assessment->rekomendasi_final;
            $tanggalBerlaku  = $assessment->tanggal_exp_idp;
            $levelUkur       = $assessment->tingkat_pengukuran;
        }

        UsulanPromosi::create([
            'karyawan_id'                => $karyawan->id,
            'jabatan_saat_ini'           => $karyawan->jabatan_saat_ini,
            'departemen_saat_ini'        => $karyawan->departemen->nama_departemen ?? null,
            'kompartemen_saat_ini'       => $karyawan->kompartemen->nama_kompartemen ?? null,
            'job_grade_saat_ini'         => $karyawan->jobGrade->job_grade ?? null,
            'person_grade_saat_ini'      => $karyawan->personGrade->person_grade ?? null,
            'band_saat_ini'              => $karyawan->band,
            'struktural_fungsional'      => $karyawan->struktural_fungsional,
            'jabatan_tujuan'             => $request->jabatan_tujuan,
            'jabatan_tujuan_id'          => $request->jabatan_tujuan_id,
            'job_grade_promosi'          => $request->job_grade_promosi,
            'person_grade_promosi'       => $request->person_grade_promosi,
            'direktorat_tujuan_id'       => $request->direktorat_tujuan_id,
            'kompartemen_tujuan_id'      => $request->kompartemen_tujuan_id,
            'departemen_tujuan_id'       => $request->departemen_tujuan_id,
            'assessment_id'              => $request->assessment_id,
            'hasil_assessment'           => $hasilAssessment,
            'tanggal_berlaku_assessment' => $tanggalBerlaku,
            'level_ukur'                 => $levelUkur,
            'tanggal_usulan'             => $request->tanggal_usulan,
            'mdg_band_ok'                => $mdgBandOk,
            'mdg_jg_ok'                  => $mdgJgOk,
            'mdg_pg_ok'                  => $mdgPgOk,
            'talent_pool_id'             => $talentPool?->id,
            'talent_pool_periode'        => $talentPool?->periode,
            'talent_pool_klasifikasi'    => $talentPool?->klasifikasi,
            'kpi_snapshot'               => $kpiSnapshot,
            'kalibrasi_snapshot'         => $kalibrasiSnapshot,
            'absensi'                    => $request->absensi,
            'kehadiran'                  => $request->kehadiran,
            'periode_penilaian'          => $request->periode_penilaian,
            'tata_kelola'                => $request->tata_kelola,
            'mc_tersedia'                => $request->boolean('mc_tersedia'),
            'hasil_evaluasi'             => $request->hasil_evaluasi,
            'catatan'                    => $request->catatan,
            'status'                     => 'draft',
            'created_by'                 => Auth::id(),
        ]);

        $this->log('tambah', 'Usulan Promosi', $karyawan->nama,
            'Jabatan tujuan: ' . $request->jabatan_tujuan);

        return redirect()->route('usulan_promosi.index')
            ->with('success', 'Usulan promosi berhasil ditambahkan!');
    }

    public function show(UsulanPromosi $usulanPromosi)
    {
        $usulanPromosi->load(['karyawan', 'assessment', 'talentPool',
            'direktoratTujuan', 'kompartemenTujuan', 'departemenTujuan']);
        return view('usulan_promosi.show', compact('usulanPromosi'));
    }

    public function updateStatus(Request $request, UsulanPromosi $usulanPromosi)
    {
        $request->validate([
            'status'         => 'required|in:draft,verif_berkas,sidang,lulus,tidak_lulus,ditolak',
            'tindak_lanjut'  => 'nullable|in:sidang,ditolak',
            'tanggal_sidang' => 'nullable|date',
            'hasil_sidang'   => 'nullable|in:lulus,tidak_lulus,tanpa_sidang',
            'catatan'        => 'nullable|string|max:1000',
        ]);

        $statusLama = $usulanPromosi->status_label;

        $usulanPromosi->update([
            'status'         => $request->status,
            'tindak_lanjut'  => $request->tindak_lanjut  ?? $usulanPromosi->tindak_lanjut,
            'tanggal_sidang' => $request->tanggal_sidang ?? $usulanPromosi->tanggal_sidang,
            'hasil_sidang'   => $request->hasil_sidang   ?? $usulanPromosi->hasil_sidang,
            'catatan'        => $request->catatan        ?? $usulanPromosi->catatan,
        ]);

        $this->log('edit', 'Usulan Promosi', $usulanPromosi->karyawan->nama,
            $statusLama . ' -> ' . $usulanPromosi->status_label);

        return redirect()->route('usulan_promosi.show', $usulanPromosi)
            ->with('success', 'Status usulan berhasil diperbarui!');
    }

    /**
     * Terbitkan SK untuk usulan yang sudah LULUS.
     * jabatan_id  → diambil dari jabatan master tujuan (jabatan_tujuan_id) yang dipilih saat create.
     *               (fallback ke jabatan_id form jika usulan lama belum punya jabatan_tujuan_id)
     * jabatan_saat_ini (label) → diambil dari teks jabatan_tujuan.
     * Unit & grade tetap diambil dari form SK.
     */
    public function terbitkanSk(Request $request, UsulanPromosi $usulanPromosi)
    {
        $request->validate([
            'no_sk'            => 'required|string|max:255',
            'tmt'              => 'required|date',
            'jabatan_id'       => 'nullable|exists:jabatan,id',
            'job_grade_id'     => 'required|exists:job_grade,id',
            'person_grade_id'  => 'required|exists:person_grade,id',
            'kode_struktur_id' => 'required|exists:kode_struktur,id',
            'direktorat_id'    => 'required|exists:direktorat,id',
            'kompartemen_id'   => 'required|exists:kompartemen,id',
            'departemen_id'    => 'required|exists:departemen,id',
            'keterangan'       => 'nullable|string|max:1000',
        ]);

        if ($usulanPromosi->status !== 'lulus') {
            return back()->with('error', 'SK hanya bisa diterbitkan untuk usulan berstatus Lulus.');
        }
        if ($usulanPromosi->sk_diproses) {
            return back()->with('error', 'SK untuk usulan ini sudah pernah diterbitkan.');
        }

        $jabatanId = $usulanPromosi->jabatan_tujuan_id ?: $request->jabatan_id;
        if (!$jabatanId) {
            return back()->with('error', 'Jabatan (master) belum ditentukan pada usulan ini.');
        }

        $karyawan    = Karyawan::findOrFail($usulanPromosi->karyawan_id);
        $jabatanBaru = Jabatan::find($jabatanId);
        $namaJabatan = $usulanPromosi->jabatan_tujuan ?: ($jabatanBaru->nama_jabatan ?? '-');
        $tmt         = $request->tmt;

        DB::transaction(function () use ($request, $usulanPromosi, $karyawan, $jabatanId, $namaJabatan, $tmt) {

            // Capture SEBELUM event syncTanggalMulaiBand jalan (dipicu oleh create history).
            $jgLamaId        = (int) $karyawan->job_grade_id;
            $bandDateSebelum = $karyawan->tanggal_mulai_band ?? $karyawan->tanggal_mulai_jg;

            HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->where('is_current', true)
                ->update([
                    'is_current'      => false,
                    'tanggal_selesai' => $tmt,
                ]);

            HistoryJabatan::create([
                'karyawan_id'      => $karyawan->id,
                'jabatan_id'       => $jabatanId,
                'jabatan_saat_ini' => $namaJabatan,
                'direktorat_id'    => $request->direktorat_id,
                'kompartemen_id'   => $request->kompartemen_id,
                'departemen_id'    => $request->departemen_id,
                'job_grade_id'     => $request->job_grade_id,
                'person_grade_id'  => $request->person_grade_id,
                'kode_struktur_id' => $request->kode_struktur_id,
                'tanggal_mulai'    => $tmt,
                'tanggal_selesai'  => null,
                'tipe'             => 'promosi',
                'keterangan'       => $request->keterangan ?: ('Promosi via usulan. No. SK: ' . $request->no_sk),
                'no_sk'            => $request->no_sk,
                'tanggal_sk'       => $tmt,
                'is_current'       => true,
            ]);

            $update = [
                'jabatan_id'       => $jabatanId,
                'jabatan_saat_ini' => $namaJabatan,
                'kode_struktur_id' => $request->kode_struktur_id,
                'direktorat_id'    => $request->direktorat_id,
                'kompartemen_id'   => $request->kompartemen_id,
                'departemen_id'    => $request->departemen_id,
            ];
            if ((int) $karyawan->job_grade_id !== (int) $request->job_grade_id) {
                $update['job_grade_id']     = $request->job_grade_id;
                $update['tanggal_mulai_jg'] = $tmt;
            }
            if ((int) $karyawan->person_grade_id !== (int) $request->person_grade_id) {
                $update['person_grade_id']  = $request->person_grade_id;
                $update['tanggal_mulai_pg'] = $tmt;

                // Ketentuan MDG: saat Person Grade NAIK, TMT Job Grade ikut di-reset.
                $pgLamaVal = (int) optional(PersonGrade::find($karyawan->person_grade_id))->person_grade;
                $pgBaruVal = (int) optional(PersonGrade::find($request->person_grade_id))->person_grade;
                if ($pgBaruVal > $pgLamaVal) {
                    $update['tanggal_mulai_jg'] = $tmt;
                }
            }

            $karyawan->update($update);

            // TMT Band otoritatif: hanya di-reset saat NAIK BAND; band sama/turun →
            // dipertahankan. Ditulis via query builder agar menang atas event sync
            // (yang bisa keliru me-reset saat riwayat jabatan tidak lengkap).
            Karyawan::where('id', $karyawan->id)->update([
                'tanggal_mulai_band' => Karyawan::tmtBandSetelahPromosi(
                    $jgLamaId, (int) $request->job_grade_id, $bandDateSebelum, $tmt
                ),
            ]);

            $usulanPromosi->update([
                'no_sk'       => $request->no_sk,
                'tmt'         => $tmt,
                'sk_diproses' => true,
            ]);
        });

        $this->log('edit', 'Usulan Promosi', $karyawan->nama,
            'Terbit SK ' . $request->no_sk . ' -> ' . $namaJabatan . ' (TMT ' . $tmt . ')');

        return redirect()->route('usulan_promosi.index', ['tab' => 'lulus'])
            ->with('success', 'SK berhasil diterbitkan. Riwayat jabatan & posisi karyawan diperbarui!');
    }

    public function destroy(UsulanPromosi $usulanPromosi)
    {
        $nama = $usulanPromosi->karyawan->nama;
        $usulanPromosi->delete();

        $this->log('hapus', 'Usulan Promosi', $nama, 'Hapus usulan promosi');

        return redirect()->route('usulan_promosi.index')
            ->with('success', 'Usulan promosi berhasil dihapus!');
    }

    public function getKaryawanData(Request $request)
    {
        $karyawan = Karyawan::with(['jobGrade', 'personGrade', 'jabatan'])
            ->where(function($q) use ($request) {
                $q->where('nik', 'like', '%'.$request->q.'%')
                  ->orWhere('nama', 'like', '%'.$request->q.'%');
            })
            ->where('status', 'aktif')
            ->limit(10)
            ->get(['id', 'nik', 'nama', 'jabatan_saat_ini', 'job_grade_id', 'person_grade_id',
                   'direktorat_id', 'kompartemen_id', 'departemen_id',
                   'struktural_fungsional', 'tanggal_mulai_jg', 'tanggal_mulai_pg', 'tanggal_mulai_band']);

        return response()->json($karyawan->map(fn($k) => [
            'id'                    => $k->id,
            'nik'                   => $k->nik,
            'nama'                  => $k->nama,
            'jabatan_saat_ini'      => $k->jabatan_saat_ini,
            'job_grade'             => $k->jobGrade->job_grade ?? '-',
            'person_grade'          => $k->personGrade->person_grade ?? '-',
            'band'                  => $k->band,
            'struktural_fungsional' => $k->struktural_fungsional ?? '-',
            'direktorat_id'         => $k->direktorat_id,
            'kompartemen_id'        => $k->kompartemen_id,
            'departemen_id'         => $k->departemen_id,
            'mdg_jg_bulan'          => $k->mdg_jg_bulan,
            'mdg_pg_bulan'          => $k->mdg_pg_bulan,
            'mdg_band_bulan'        => $k->mdg_band_bulan,
        ]));
    }

    public function getAssessments(Request $request)
    {
        $assessments = HistoryAssessment::where('karyawan_id', $request->karyawan_id)
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->get();

        return response()->json($assessments->map(fn($a) => [
            'id'                  => $a->id,
            'tanggal_pelaksanaan' => $a->tanggal_pelaksanaan->format('d M Y'),
            'rekomendasi_final'   => $a->rekomendasi_final,
            'label'               => $a->rekomendasiFinalLabel,
            'tingkat_pengukuran'  => $a->tingkat_pengukuran ?? '-',
            'tanggal_exp_idp'     => $a->tanggal_exp_idp?->format('d M Y'),
            'lembaga'             => $a->lembaga ?? '-',
        ]));
    }

    public function getTalentKpiPreview(Request $request)
    {
        $karyawan = Karyawan::find($request->karyawan_id);
        if (!$karyawan) return response()->json([]);

        $tahunIni = now()->year;

        $talentPool = TalentPool::where('karyawan_id', $karyawan->id)
            ->where('periode', $tahunIni - 1)
            ->first();

        // KPI Tahunan saja, 4 tahun terakhir (tidak include tahun berjalan)
        $kpi = PenilaianKaryawan::where('karyawan_id', $karyawan->id)
            ->where('tipe', 'KPI')
            ->where('periode', 'tahunan')
            ->whereIn('tahun', [
                $tahunIni - 1,
                $tahunIni - 2,
                $tahunIni - 3,
                $tahunIni - 4,
            ])
            ->orderBy('tahun', 'desc')
            ->get();

        // Kalibrasi 3 tahun sebelum tahun berjalan (tidak include tahun berjalan)
        $kalibrasi = KalibrasiKaryawan::where('karyawan_id', $karyawan->id)
            ->whereIn('tahun', [
                $tahunIni - 1,
                $tahunIni - 2,
                $tahunIni - 3,
            ])
            ->orderBy('tahun', 'desc')
            ->get();

        return response()->json([
            'talent_pool' => $talentPool ? [
                'periode'     => $talentPool->periode,
                'klasifikasi' => $talentPool->klasifikasi,
            ] : null,
            'kpi' => $kpi->map(fn($k) => [
                'tahun'         => $k->tahun,
                'periode_label' => 'Tahunan',
                'nilai_format'  => number_format($k->nilai, 2, ',', '.'),
            ]),
            'kalibrasi' => $kalibrasi->map(fn($k) => [
                'tahun' => $k->tahun,
                'nilai' => $k->nilai,
            ]),
        ]);
    }
}