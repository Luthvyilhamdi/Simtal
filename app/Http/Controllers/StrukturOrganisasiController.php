<?php

namespace App\Http\Controllers;

use App\Models\StrukturOrganisasi;
use App\Models\Karyawan;
use App\Exports\StrukturOrganisasiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StrukturOrganisasiController extends Controller
{
    private function getPeriode(Request $request): array
    {
        return [
            'bulan' => (int) ($request->bulan ?? now()->month),
            'tahun' => (int) ($request->tahun ?? now()->year),
        ];
    }

    public function index(Request $request)
    {
        ['bulan' => $bulan, 'tahun' => $tahun] = $this->getPeriode($request);

        $query = StrukturOrganisasi::query()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('id');

        if ($request->direktorat) {
            $query->where('direktorat', $request->direktorat);
        }
        if ($request->kompartemen) {
            $query->where('kompartemen', $request->kompartemen);
        }
        if ($request->core) {
            $query->where('core', $request->core);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('posisi', 'like', '%'.$request->search.'%')
                  ->orWhere('bagian', 'like', '%'.$request->search.'%')
                  ->orWhere('fungsional', 'like', '%'.$request->search.'%')
                  ->orWhere('nama_karyawan', 'like', '%'.$request->search.'%');
            });
        }

        $allJabatan = $query->get();

        $statsRaw = StrukturOrganisasi::where('bulan', $bulan)->where('tahun', $tahun)
            ->where('posisi', '!=', '-')
            ->selectRaw('
                COUNT(*) as total_posisi,
                SUM(mc_tko) as total_mc,
                SUM(pengisian) as total_peng,
                SUM(deviasi) as total_dev,
                SUM(CASE WHEN core = "Core" THEN 1 ELSE 0 END) as total_core,
                SUM(CASE WHEN core = "Non Core" THEN 1 ELSE 0 END) as total_non_core
            ')->first();

        $stats = [
            'total_posisi' => $statsRaw->total_posisi ?? 0,
            'total_mc'     => $statsRaw->total_mc     ?? 0,
            'total_peng'   => $statsRaw->total_peng   ?? 0,
            'total_dev'    => $statsRaw->total_dev    ?? 0,
            'core'         => $statsRaw->total_core     ?? 0,
            'non_core'     => $statsRaw->total_non_core ?? 0,
        ];

        $direktorats  = $allJabatan->pluck('direktorat')->filter()->unique()->sort()->values();
        $kompartemens = $allJabatan->pluck('kompartemen')->filter()->unique()->sort()->values();
        $fungsionals  = $allJabatan->pluck('fungsional')->filter()->unique()->sort()->values();

        $periodeList = StrukturOrganisasi::selectRaw('bulan, tahun, COUNT(*) as total')
            ->groupBy('bulan', 'tahun')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $tree = $this->buildTree($allJabatan);

        $karyawans = Karyawan::where('status', 'aktif')
                             ->orderBy('nama')
                             ->get(['id', 'nik', 'nama']);

        return view('struktur_organisasi.index', compact(
            'allJabatan', 'tree', 'stats', 'direktorats', 'kompartemens', 'fungsionals',
            'karyawans', 'bulan', 'tahun', 'periodeList'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'direktorat'      => 'nullable|string',
            'kompartemen'     => 'nullable|string',
            'dept'            => 'nullable|string',
            'bagian'          => 'nullable|string',
            'fungsional'      => 'nullable|string',
            'fungsional_staff'=> 'nullable|string',
            'posisi'          => 'nullable|string',
            'job_grade'       => 'nullable|integer',
            'mc_tko'          => 'nullable|integer|min:0',
            'core'            => 'nullable|in:Core,Non Core',
            'bulan'           => 'nullable|integer|min:1|max:12',
            'tahun'           => 'nullable|integer|min:2000|max:2100',
        ]);

        $fungsional = $request->fungsional ?? $request->fungsional_staff ?? null;
        $bulan = (int)($request->bulan ?? now()->month);
        $tahun = (int)($request->tahun ?? now()->year);

        // Simpan kompartemen sebagai NULL jika kosong (bukan empty string)
        $kompartemen = $request->kompartemen ?: null;

        StrukturOrganisasi::create([
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'direktorat'  => $request->direktorat,
            'kompartemen' => $kompartemen,
            'dept'        => $request->dept ?: null,
            'bagian'      => $request->bagian ?: null,
            'fungsional'  => $fungsional ?: null,
            'posisi'      => $request->posisi ?? '-',
            'job_grade'   => $request->job_grade,
            'mc_tko'      => $request->mc_tko !== null && $request->mc_tko !== '' ? (int)$request->mc_tko : 0,
            'core'        => $request->core ?? 'Non Core',
            'pengisian'   => 0,
            'deviasi'     => $request->mc_tko ? -(int)$request->mc_tko : 0,
        ]);

        // ===== FIX: Redirect kembali ke direktorat yang sama =====
        $params = ['bulan' => $bulan, 'tahun' => $tahun];
        if ($request->direktorat) {
            $params['direktorat'] = $request->direktorat;
        }

        return redirect()->route('struktur-organisasi.index', $params)
                         ->with('success', 'Posisi berhasil ditambahkan.');
    }

    public function salinPeriode(Request $request)
    {
        $request->validate([
            'dari_bulan' => 'required|integer|min:1|max:12',
            'dari_tahun' => 'required|integer',
            'ke_bulan'   => 'required|integer|min:1|max:12',
            'ke_tahun'   => 'required|integer',
        ]);

        $dariBulan = $request->dari_bulan;
        $dariTahun = $request->dari_tahun;
        $keBulan   = $request->ke_bulan;
        $keTahun   = $request->ke_tahun;

        $sudahAda = StrukturOrganisasi::where('bulan', $keBulan)->where('tahun', $keTahun)->exists();
        if ($sudahAda) {
            return back()->with('error', "Periode {$keBulan}/{$keTahun} sudah ada datanya! Hapus dulu sebelum menyalin.");
        }

        $data = StrukturOrganisasi::where('bulan', $dariBulan)->where('tahun', $dariTahun)->get();

        if ($data->isEmpty()) {
            return back()->with('error', "Tidak ada data di periode {$dariBulan}/{$dariTahun}.");
        }

        DB::transaction(function() use ($data, $keBulan, $keTahun) {
            $chunks = $data->map(fn($row) => [
                'bulan'         => $keBulan,
                'tahun'         => $keTahun,
                'direktorat'    => $row->direktorat,
                'kompartemen'   => $row->kompartemen,
                'dept'          => $row->dept,
                'bagian'        => $row->bagian,
                'fungsional'    => $row->fungsional,
                'posisi'        => $row->posisi,
                'job_grade'     => $row->job_grade,
                'mc_tko'        => $row->mc_tko,
                'core'          => $row->core,
                'karyawan_id'   => $row->karyawan_id,
                'nik_karyawan'  => $row->nik_karyawan,
                'nama_karyawan' => $row->nama_karyawan,
                'pengisian'     => $row->pengisian,
                'deviasi'       => $row->deviasi,
                'created_at'    => now(),
                'updated_at'    => now(),
            ])->toArray();

            foreach (array_chunk($chunks, 100) as $batch) {
                StrukturOrganisasi::insert($batch);
            }
        });

        $namaBulan = Carbon::createFromDate($keTahun, $keBulan, 1)->translatedFormat('F Y');
        return redirect()->route('struktur-organisasi.index', ['bulan' => $keBulan, 'tahun' => $keTahun])
                         ->with('success', "Berhasil menyalin {$data->count()} posisi ke periode {$namaBulan}.");
    }

    public function update(Request $request, StrukturOrganisasi $so)
    {
        if ($request->has('karyawan_id')) {
            $karyawan = null;

            if ($request->karyawan_id) {
                $karyawan = Karyawan::with(['direktorat', 'kompartemen', 'departemen'])
                                    ->find($request->karyawan_id);
            }

            $so->update([
                'karyawan_id'   => $karyawan?->id,
                'nik_karyawan'  => $karyawan?->nik,
                'nama_karyawan' => $karyawan?->nama,
                'pengisian'     => $karyawan ? 1 : 0,
                'deviasi'       => ($karyawan ? 1 : 0) - $so->mc_tko,
            ]);

            return response()->json([
                'success'       => true,
                'pengisian'     => $so->fresh()->pengisian,
                'deviasi'       => $so->fresh()->deviasi,
                'warna'         => $so->fresh()->warnaDeviasi,
                'nama_karyawan' => $so->fresh()->nama_karyawan ?? '',
                'nik_karyawan'  => $so->fresh()->nik_karyawan  ?? '',
                'karyawan_id'   => $so->fresh()->karyawan_id   ?? null,
            ]);
        }

        $request->validate([
            'pengisian' => 'required|integer|min:0',
        ]);

        $so->update([
            'pengisian' => $request->pengisian,
            'deviasi'   => $request->pengisian - $so->mc_tko,
        ]);

        return response()->json([
            'success'   => true,
            'pengisian' => $so->pengisian,
            'deviasi'   => $so->deviasi,
            'warna'     => $so->warnaDeviasi,
        ]);
    }

    public function destroy(StrukturOrganisasi $so)
    {
        if (auth()->user()->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $bulan = $so->bulan;
        $tahun = $so->tahun;
        $so->delete();
        return redirect()->route('struktur-organisasi.index', ['bulan' => $bulan, 'tahun' => $tahun])
                         ->with('success', 'Posisi berhasil dihapus.');
    }

    public function getKaryawanData($id)
    {
        $k = Karyawan::with(['direktorat', 'kompartemen', 'departemen', 'jobGrade', 'personGrade'])
                     ->findOrFail($id);

        return response()->json([
            'id'               => $k->id,
            'nik'              => $k->nik,
            'nama'             => $k->nama,
            'jabatan_saat_ini' => $k->jabatan_saat_ini ?? '-',
            'direktorat'       => $k->direktorat?->nama_direktorat   ?? '-',
            'kompartemen'      => $k->kompartemen?->nama_kompartemen ?? '-',
            'departemen'       => $k->departemen?->nama_departemen   ?? '-',
            'job_grade'        => $k->jobGrade?->job_grade           ?? '-',
            'person_grade'     => $k->personGrade?->person_grade     ?? '-',
        ]);
    }

    public function getKaryawanProfile($id)
    {
        $k = Karyawan::with([
            'direktorat', 'kompartemen', 'departemen',
            'jobGrade', 'personGrade',
        ])->findOrFail($id);

        $history = DB::table('history_jabatans as hj')
            ->leftJoin('direktorat as d',   'd.id',  '=', 'hj.direktorat_id')
            ->leftJoin('kompartemen as ko', 'ko.id', '=', 'hj.kompartemen_id')
            ->leftJoin('departemen as dep', 'dep.id','=', 'hj.departemen_id')
            ->where('hj.karyawan_id', $id)
            ->orderByDesc('hj.tanggal_mulai')
            ->get([
                'hj.id', 'hj.jabatan_saat_ini', 'hj.tipe',
                'hj.tanggal_mulai', 'hj.tanggal_selesai',
                'hj.no_sk', 'hj.is_current', 'hj.keterangan',
                'd.nama_direktorat', 'ko.nama_kompartemen',
                'dep.nama_departemen',
            ]);

        $umur          = $k->tanggal_lahir ? Carbon::parse($k->tanggal_lahir)->age : null;
        $tglPensiun    = $k->tanggal_lahir ? Carbon::parse($k->tanggal_lahir)->addYears(56)->translatedFormat('d F Y') : null;
        $sisaMasaKerja = $k->tanggal_lahir
            ? max(0, (int) Carbon::now()->diffInYears(Carbon::parse($k->tanggal_lahir)->addYears(56), false))
            : null;
        $lamaBekerja   = $k->tanggal_masuk
            ? Carbon::parse($k->tanggal_masuk)->diffForHumans(null, true)
            : null;

        return response()->json([
            'id'              => $k->id,
            'nama'            => $k->nama,
            'nik'             => $k->nik,
            'foto'            => $k->foto ? asset('storage/'.$k->foto) : null,
            'inisial'         => strtoupper(substr($k->nama, 0, 2)),
            'jabatan_saat_ini'=> $k->jabatan_saat_ini ?? '-',
            'direktorat'      => $k->direktorat?->nama_direktorat   ?? '-',
            'kompartemen'     => $k->kompartemen?->nama_kompartemen ?? '-',
            'departemen'      => $k->departemen?->nama_departemen   ?? '-',
            'job_grade'       => $k->jobGrade?->job_grade           ?? '-',
            'person_grade'    => $k->personGrade?->person_grade     ?? '-',
            'tanggal_masuk'   => $k->tanggal_masuk ? Carbon::parse($k->tanggal_masuk)->translatedFormat('d F Y') : '-',
            'tanggal_lahir'   => $k->tanggal_lahir ? Carbon::parse($k->tanggal_lahir)->translatedFormat('d F Y') : '-',
            'umur'            => $umur,
            'pensiun'         => $tglPensiun,
            'sisa_masa_kerja' => $sisaMasaKerja,
            'lama_bekerja'    => $lamaBekerja,
            'status'          => $k->status,
            'history'         => $history,
        ]);
    }

    public function export(Request $request)
    {
        ['bulan' => $bulan, 'tahun' => $tahun] = $this->getPeriode($request);
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F-Y');
        $filename = "struktur-organisasi-{$namaBulan}.xlsx";

        return Excel::download(
            new StrukturOrganisasiExport([
                'direktorat'  => $request->direktorat,
                'kompartemen' => $request->kompartemen,
                'core'        => $request->core,
                'bulan'       => $bulan,
                'tahun'       => $tahun,
            ]),
            $filename
        );
    }

    private function buildTree($data)
    {
        $tree = [];

        foreach ($data as $row) {
            $dir  = $row->direktorat  ?: '(Tanpa Direktorat)';
            $komp = trim($row->kompartemen ?? '') ?: '';
            $dept = trim($row->dept        ?? '') ?: '';
            $bag  = trim($row->bagian      ?? '') ?: '';
            $func = trim($row->fungsional  ?? '') ?: '';

            if (!isset($tree[$dir])) {
                $tree[$dir] = ['label' => $dir, 'mc_tko' => 0, 'pengisian' => 0, 'children' => []];
            }

            $kompKey = $komp ?: '__no_komp__';

            // ===== FIX: __no_komp__ selalu jadi key PERTAMA agar tampil di atas =====
            if (!isset($tree[$dir]['children'][$kompKey])) {
                if ($kompKey === '__no_komp__') {
                    // Sisipkan di depan array
                    $tree[$dir]['children'] = ['__no_komp__' => ['label' => '', 'mc_tko' => 0, 'pengisian' => 0, 'children' => []]]
                                            + $tree[$dir]['children'];
                } else {
                    $tree[$dir]['children'][$kompKey] = ['label' => $komp, 'mc_tko' => 0, 'pengisian' => 0, 'children' => []];
                }
            }

            $deptKey = $dept ?: '__no_dept__';
            if (!isset($tree[$dir]['children'][$kompKey]['children'][$deptKey])) {
                $tree[$dir]['children'][$kompKey]['children'][$deptKey] = ['label' => $dept, 'mc_tko' => 0, 'pengisian' => 0, 'children' => []];
            }

            $bagKey = $bag ?: '__no_bag__';
            if (!isset($tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey])) {
                $tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey] = ['label' => $bag, 'mc_tko' => 0, 'pengisian' => 0, 'children' => []];
            }

            $funcKey = $func ?: '__no_func__';
            if (!isset($tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey]['children'][$funcKey])) {
                $tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey]['children'][$funcKey] = ['label' => $func, 'mc_tko' => 0, 'pengisian' => 0, 'jabatan' => []];
            }

            $tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey]['children'][$funcKey]['jabatan'][] = $row;

            $tree[$dir]['mc_tko']    += $row->mc_tko;
            $tree[$dir]['pengisian'] += $row->pengisian;
            $tree[$dir]['children'][$kompKey]['mc_tko']    += $row->mc_tko;
            $tree[$dir]['children'][$kompKey]['pengisian'] += $row->pengisian;
            $tree[$dir]['children'][$kompKey]['children'][$deptKey]['mc_tko']    += $row->mc_tko;
            $tree[$dir]['children'][$kompKey]['children'][$deptKey]['pengisian'] += $row->pengisian;
            $tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey]['mc_tko']    += $row->mc_tko;
            $tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey]['pengisian'] += $row->pengisian;
            $tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey]['children'][$funcKey]['mc_tko']    += $row->mc_tko;
            $tree[$dir]['children'][$kompKey]['children'][$deptKey]['children'][$bagKey]['children'][$funcKey]['pengisian'] += $row->pengisian;
        }

        return $tree;
    }
}