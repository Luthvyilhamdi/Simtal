<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\UsulanPromosi;
use App\Models\HistoryAssessment;
use App\Models\TalentPool;
use App\Models\PenilaianKaryawan;
use App\Models\KalibrasiKaryawan;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsulanPromosiController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $search = $request->search;

        $baseQuery = function($status) use ($search) {
            $q = UsulanPromosi::with(['karyawan', 'karyawan.departemen', 'karyawan.kompartemen', 'createdBy'])
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

        $tanpaSidangQuery = function() use ($search) {
            $q = UsulanPromosi::with(['karyawan', 'karyawan.departemen', 'karyawan.kompartemen', 'createdBy'])
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

        return view('usulan_promosi.index', compact('statusGroups', 'counts', 'activeTab'));
    }

    public function create()
    {
        $karyawans = Karyawan::where('status', 'aktif')->orderBy('nama')->get();
        return view('usulan_promosi.create', compact('karyawans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'          => 'required|exists:karyawans,id',
            'jabatan_tujuan'       => 'required|string|max:255',
            'job_grade_promosi'    => 'nullable|string',
            'person_grade_promosi' => 'nullable|string',
            'assessment_id'        => 'nullable|exists:history_assessments,id',
            'tanggal_usulan'       => 'nullable|date',
            'absensi'              => 'nullable|string',
            'kehadiran'            => 'nullable|string',
            'periode_penilaian'    => 'nullable|string',
            'tata_kelola'          => 'nullable|string',
            'mc_tersedia'          => 'nullable|boolean',
            'hasil_evaluasi'       => 'nullable|string',
            'catatan'              => 'nullable|string|max:1000',
        ]);

        $karyawan = Karyawan::with(['jobGrade', 'personGrade', 'departemen', 'kompartemen'])->find($request->karyawan_id);

        // Snapshot MDG
        $mdgBandOk = ($karyawan->mdg_band_bulan ?? 0) >= 36;
        $mdgJgOk   = ($karyawan->mdg_jg_bulan   ?? 0) >= 24;
        $mdgPgOk   = ($karyawan->mdg_pg_bulan   ?? 0) >= 12;

        // Snapshot KPI 3 tahun terakhir
        $tahunIni = now()->year;
        $kpiSnapshot = PenilaianKaryawan::where('karyawan_id', $karyawan->id)
            ->where('tipe', 'KPI')
            ->whereIn('tahun', [$tahunIni, $tahunIni - 1, $tahunIni - 2, $tahunIni - 3])
            ->orderBy('tahun', 'desc')->orderBy('periode')
            ->get(['tahun', 'periode', 'judul', 'nilai'])
            ->toArray();

        // Snapshot Kalibrasi 2 tahun terakhir
        $kalibrasiSnapshot = KalibrasiKaryawan::where('karyawan_id', $karyawan->id)
            ->whereIn('tahun', [$tahunIni, $tahunIni - 1, $tahunIni - 2])
            ->orderBy('tahun', 'desc')
            ->get(['tahun', 'nilai', 'keterangan'])
            ->toArray();

        // Snapshot Talent Pool tahun lalu
        $talentPool = TalentPool::where('karyawan_id', $karyawan->id)
            ->where('periode', $tahunIni - 1)
            ->first();

        // Snapshot assessment
        $assessment = null;
        $hasilAssessment = null;
        $tanggalBerlaku  = null;
        $levelUkur       = null;
        if ($request->assessment_id) {
            $assessment = HistoryAssessment::find($request->assessment_id);
            $hasilAssessment = $assessment->rekomendasi_final;
            $tanggalBerlaku  = $assessment->tanggal_exp_idp;
            $levelUkur       = $assessment->tingkat_pengukuran;
        }

        UsulanPromosi::create([
            'karyawan_id'               => $karyawan->id,
            'jabatan_saat_ini'          => $karyawan->jabatan_saat_ini,
            'departemen_saat_ini'        => $karyawan->departemen->nama_departemen ?? null,
            'kompartemen_saat_ini'       => $karyawan->kompartemen->nama_kompartemen ?? null,
            'job_grade_saat_ini'        => $karyawan->jobGrade->job_grade ?? null,
            'person_grade_saat_ini'     => $karyawan->personGrade->person_grade ?? null,
            'band_saat_ini'             => $karyawan->band,
            'struktural_fungsional'     => $karyawan->struktural_fungsional,
            'jabatan_tujuan'            => $request->jabatan_tujuan,
            'job_grade_promosi'         => $request->job_grade_promosi,
            'person_grade_promosi'      => $request->person_grade_promosi,
            'assessment_id'             => $request->assessment_id,
            'hasil_assessment'          => $hasilAssessment,
            'tanggal_berlaku_assessment' => $tanggalBerlaku,
            'level_ukur'                => $levelUkur,
            'tanggal_usulan'            => $request->tanggal_usulan,
            'mdg_band_ok'               => $mdgBandOk,
            'mdg_jg_ok'                 => $mdgJgOk,
            'mdg_pg_ok'                 => $mdgPgOk,
            'talent_pool_id'            => $talentPool?->id,
            'talent_pool_periode'       => $talentPool?->periode,
            'talent_pool_klasifikasi'   => $talentPool?->klasifikasi,
            'kpi_snapshot'              => $kpiSnapshot,
            'kalibrasi_snapshot'        => $kalibrasiSnapshot,
            'absensi'                   => $request->absensi,
            'kehadiran'                 => $request->kehadiran,
            'periode_penilaian'         => $request->periode_penilaian,
            'tata_kelola'               => $request->tata_kelola,
            'mc_tersedia'               => $request->boolean('mc_tersedia'),
            'hasil_evaluasi'            => $request->hasil_evaluasi,
            'catatan'                   => $request->catatan,
            'status'                    => 'draft',
            'created_by'                => Auth::id(),
        ]);

        $this->log('tambah', 'Usulan Promosi', $karyawan->nama,
            'Jabatan tujuan: ' . $request->jabatan_tujuan);

        return redirect()->route('usulan_promosi.index')
            ->with('success', 'Usulan promosi berhasil ditambahkan!');
    }

    public function show(UsulanPromosi $usulanPromosi)
    {
        $usulanPromosi->load(['karyawan', 'assessment', 'talentPool']);
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
            $statusLama . ' → ' . $usulanPromosi->status_label);

        return redirect()->route('usulan_promosi.show', $usulanPromosi)
            ->with('success', 'Status usulan berhasil diperbarui!');
    }

    public function destroy(UsulanPromosi $usulanPromosi)
    {
        $nama = $usulanPromosi->karyawan->nama;
        $usulanPromosi->delete();

        $this->log('hapus', 'Usulan Promosi', $nama, 'Hapus usulan promosi');

        return redirect()->route('usulan_promosi.index')
            ->with('success', 'Usulan promosi berhasil dihapus!');
    }

    // AJAX: ambil data karyawan + assessment list
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
                   'struktural_fungsional', 'tanggal_mulai_jg', 'tanggal_mulai_pg']);

        return response()->json($karyawan->map(fn($k) => [
            'id'                    => $k->id,
            'nik'                   => $k->nik,
            'nama'                  => $k->nama,
            'jabatan_saat_ini'      => $k->jabatan_saat_ini,
            'job_grade'             => $k->jobGrade->job_grade ?? '-',
            'person_grade'          => $k->personGrade->person_grade ?? '-',
            'band'                  => $k->band,
            'struktural_fungsional' => $k->struktural_fungsional ?? '-',
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

    // API: talent pool + KPI + kalibrasi preview
    public function getTalentKpiPreview(Request $request)
    {
        $karyawan  = Karyawan::find($request->karyawan_id);
        if (!$karyawan) return response()->json([]);

        $tahunIni  = now()->year;

        $talentPool = TalentPool::where('karyawan_id', $karyawan->id)
            ->where('periode', $tahunIni - 1)->first();

        $kpi = PenilaianKaryawan::where('karyawan_id', $karyawan->id)
            ->where('tipe', 'KPI')
            ->whereIn('tahun', [$tahunIni, $tahunIni-1, $tahunIni-2, $tahunIni-3])
            ->orderBy('tahun', 'desc')->orderBy('periode')
            ->get();

        $kalibrasi = KalibrasiKaryawan::where('karyawan_id', $karyawan->id)
            ->whereIn('tahun', [$tahunIni, $tahunIni-1, $tahunIni-2])
            ->orderBy('tahun', 'desc')->get();

        $periodeLabel = ['triwulan_1'=>'Triwulan I','triwulan_2'=>'Triwulan II','triwulan_3'=>'Triwulan III','triwulan_4'=>'Triwulan IV','tahunan'=>'Tahunan'];

        return response()->json([
            'talent_pool' => $talentPool ? [
                'periode'     => $talentPool->periode,
                'klasifikasi' => $talentPool->klasifikasi,
            ] : null,
            'kpi' => $kpi->map(fn($k) => [
                'tahun'        => $k->tahun,
                'periode_label'=> $periodeLabel[$k->periode] ?? $k->periode,
                'nilai_format' => number_format($k->nilai, 2, ',', '.'),
            ]),
            'kalibrasi' => $kalibrasi->map(fn($k) => [
                'tahun' => $k->tahun,
                'nilai' => $k->nilai,
            ]),
        ]);
    }
}