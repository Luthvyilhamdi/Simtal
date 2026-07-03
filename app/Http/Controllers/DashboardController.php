<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\HistoryAssessment;
use App\Models\HistoryAssessmentKompetensi;
use App\Models\HistoryPejabat;
use App\Models\PgsPjs;
use App\Models\Direktorat;
use App\Models\StrukturOrganisasi;
use App\Models\TalentPool;
use App\Services\ReminderPromosiService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // === STATS UTAMA === (3 count → 1 query grouped)
        $kStat = Karyawan::selectRaw("
            COUNT(*) as total,
            SUM(status = 'aktif') as aktif,
            SUM(status = 'tidak aktif') as tidak_aktif
        ")->first();
        $totalKaryawan      = (int) $kStat->total;
        $karyawanAktif      = (int) $kStat->aktif;
        $karyawanTidakAktif = (int) $kStat->tidak_aktif;
        $karyawanBaru       = Karyawan::whereMonth('tanggal_masuk', now()->month)
                                ->whereYear('tanggal_masuk', now()->year)->count();

        // === HISTORY JABATAN === (3 count tahun ini → 1 query grouped)
        $totalHistoryJabatan = HistoryJabatan::count();
        $hjYear = HistoryJabatan::whereYear('tanggal_mulai', now()->year)->selectRaw("
            SUM(tipe = 'promosi') as promosi,
            SUM(tipe = 'mutasi') as mutasi,
            SUM(tipe = 'rotasi') as rotasi,
            SUM(tipe = 'demosi') as demosi
        ")->first();
        $promosiThisYear = (int) $hjYear->promosi;
        $mutasiThisYear  = (int) $hjYear->mutasi;
        $rotasiThisYear  = (int) $hjYear->rotasi;
        $demosiThisYear  = (int) $hjYear->demosi;

        // === ASSESSMENT REKOMENDASI === (4 count → 1 query grouped)
        $aStat = HistoryAssessment::selectRaw("
            COUNT(*) as total,
            SUM(rekomendasi_final = 'ready') as ready,
            SUM(rekomendasi_final = 'ready_with_development') as rwd,
            SUM(rekomendasi_final = 'not_ready') as nr
        ")->first();
        $totalAssessment = (int) $aStat->total;
        $assessmentReady = (int) $aStat->ready;
        $assessmentRWD   = (int) $aStat->rwd;
        $assessmentNR    = (int) $aStat->nr;

        // === ASSESSMENT KOMPETENSI === (3 count → 1 query grouped)
        $kmpStat = HistoryAssessmentKompetensi::selectRaw("
            COUNT(*) as total,
            SUM(kesimpulan = 'QUALIFIED') as qualified,
            SUM(kesimpulan = 'NOT QUALIFIED') as not_qualified
        ")->first();
        $totalKompetensi   = (int) $kmpStat->total;
        $totalQualified    = (int) $kmpStat->qualified;
        $totalNotQualified = (int) $kmpStat->not_qualified;

        // === PEJABAT === (5 count → 1 query grouped)
        $pjStat = HistoryPejabat::whereNull('tanggal_selesai')->selectRaw("
            COUNT(*) as total,
            SUM(jabatan = 'SVP') as svp,
            SUM(jabatan = 'VP') as vp,
            SUM(jabatan = 'SPM') as spm,
            SUM(jabatan = 'PM') as pm
        ")->first();
        $pejabatAktif = (int) $pjStat->total;
        $pejabatSVP   = (int) $pjStat->svp;
        $pejabatVP    = (int) $pjStat->vp;
        $pejabatSPM   = (int) $pjStat->spm;
        $pejabatPM    = (int) $pjStat->pm;

        // === PGS & PJS === (2 count → 1 query grouped)
        PgsPjs::where('is_active', true)
            ->where('tanggal_berakhir', '<', now())
            ->whereNotNull('tanggal_berakhir')
            ->update(['is_active' => false]);
        $pgsStat = PgsPjs::where('is_active', true)->selectRaw("
            SUM(tipe = 'pgs') as pgs,
            SUM(tipe = 'pjs') as pjs
        ")->first();
        $pgsAktif = (int) $pgsStat->pgs;
        $pjsAktif = (int) $pgsStat->pjs;

        // === TALENT POOL === tampilkan 2 periode TERBARU yang ADA datanya (otomatis
        // ikut tahun; periode kosong tidak ditampilkan).
        $tpPeriodes = TalentPool::select('periode')->distinct()
            ->orderByDesc('periode')->pluck('periode')->take(2)->values();

        $tpAgg = function ($periode) {
            if ($periode === null) return null;
            $r = TalentPool::where('periode', $periode)
                ->selectRaw('COUNT(*) as total, SUM(klasifikasi="longlist") as longlist, SUM(klasifikasi="shortlist") as shortlist')
                ->first();
            return [
                'periode'   => (int) $periode,
                'total'     => (int) ($r->total     ?? 0),
                'longlist'  => (int) ($r->longlist  ?? 0),
                'shortlist' => (int) ($r->shortlist ?? 0),
            ];
        };

        $talentPool = [
            'utama' => $tpAgg($tpPeriodes[0] ?? null), // periode terbaru
            'kedua' => $tpAgg($tpPeriodes[1] ?? null), // periode sebelumnya (bisa null)
        ];

        // === STRUKTUR ORGANISASI ===
        // Pakai periode SO TERBARU yang ada datanya (bukan bulan berjalan yang
        // mungkin belum diisi), agar seluruh ringkasan SO tidak kosong.
        $soPeriode = StrukturOrganisasi::orderByDesc('tahun')->orderByDesc('bulan')
            ->first(['bulan', 'tahun']);
        $soBulan = $soPeriode->bulan ?? now()->month;
        $soTahun = $soPeriode->tahun ?? now()->year;
        $soStats = StrukturOrganisasi::where('bulan', $soBulan)
            ->where('tahun', $soTahun)
            ->where('posisi', '!=', '-')
            ->selectRaw('
                COUNT(*) as total_posisi,
                SUM(mc_tko) as total_mc,
                SUM(pengisian) as total_terisi,
                SUM(CASE WHEN core = "Core" THEN 1 ELSE 0 END) as total_core,
                SUM(CASE WHEN core = "Non Core" THEN 1 ELSE 0 END) as total_non_core,
                SUM(CASE WHEN core = "Core" AND pengisian > 0 THEN pengisian ELSE 0 END) as core_terisi,
                SUM(CASE WHEN core = "Non Core" AND pengisian > 0 THEN pengisian ELSE 0 END) as non_core_terisi,
                SUM(CASE WHEN core = "Core" THEN mc_tko ELSE 0 END) as core_mc,
                SUM(CASE WHEN core = "Non Core" THEN mc_tko ELSE 0 END) as non_core_mc
            ')->first();

        $soTotalPosisi   = $soStats->total_posisi   ?? 0;
        $soTotalMc       = $soStats->total_mc       ?? 0;
        $soTerisi        = $soStats->total_terisi   ?? 0;
        $soCore          = $soStats->total_core     ?? 0;
        $soNonCore       = $soStats->total_non_core ?? 0;
        $soDeviasi       = $soTerisi - $soTotalMc;
        $soCoreTerisi    = $soStats->core_terisi    ?? 0;
        $soNonCoreTerisi = $soStats->non_core_terisi ?? 0;
        $soCoreMc        = $soStats->core_mc        ?? 0;
        $soNonCoreMc     = $soStats->non_core_mc    ?? 0;

        // Status pengisian: per posisi yang MC/TKO-nya tersedia (mc_tko > 0),
        // dihitung terisi atau belum terisi (bukan selisih angka mc vs pengisian).
        $soStatusQuery = fn ($kolom) => StrukturOrganisasi::where('bulan', $soBulan)
            ->where('tahun', $soTahun)
            ->where('posisi', '!=', '-')
            ->where('mc_tko', '>', 0)
            ->whereNotNull($kolom)
            ->where($kolom, '!=', '')
            ->selectRaw("
                {$kolom} as nama,
                COUNT(*) as tersedia,
                SUM(CASE WHEN pengisian > 0 THEN 1 ELSE 0 END) as terisi,
                SUM(CASE WHEN pengisian = 0 THEN 1 ELSE 0 END) as belum_terisi
            ")
            ->groupBy($kolom)
            ->orderByDesc('belum_terisi');

        $soPerDirektorat  = $soStatusQuery('direktorat')->get();
        $soPerKompartemen = $soStatusQuery('kompartemen')->get();
        $soPerDepartemen  = $soStatusQuery('dept')->get();

        // Total pengisian (headcount) yang terisi per unit — SUM(pengisian) vs SUM(mc_tko).
        // Beda dari status di atas (yang menghitung JUMLAH POSISI terisi/kosong).
        $soTerisiQuery = fn ($kolom) => StrukturOrganisasi::where('bulan', $soBulan)
            ->where('tahun', $soTahun)
            ->where('posisi', '!=', '-')
            ->whereNotNull($kolom)
            ->where($kolom, '!=', '')
            ->selectRaw("
                {$kolom} as nama,
                SUM(pengisian) as total_terisi,
                SUM(mc_tko) as total_mc
            ")
            ->groupBy($kolom)
            ->orderByDesc('total_terisi');

        $soTerisiDirektorat  = $soTerisiQuery('direktorat')->get();
        $soTerisiKompartemen = $soTerisiQuery('kompartemen')->get();
        $soTerisiDepartemen  = $soTerisiQuery('dept')->get();

        // Data drill-down untuk popup: tiap unit induk (parent) diuraikan ke unit anak
        // yang ada namanya — Direktorat→Kompartemen, Kompartemen→Dept (Dept tidak diuraikan).
        // Mengikuti hierarki halaman Struktur Organisasi (kolom direktorat/kompartemen/dept).
        // mode 'status' = hitung posisi (terisi/tersedia/belum); 'terisi' = jumlah headcount.
        $soHierarki = function (string $parentCol, string $childCol, string $mode) use ($soBulan, $soTahun) {
            $q = StrukturOrganisasi::where('bulan', $soBulan)
                ->where('tahun', $soTahun)
                ->where('posisi', '!=', '-')
                ->whereNotNull($parentCol)->where($parentCol, '!=', '');
            if ($mode === 'status') {
                $q->where('mc_tko', '>', 0);
                $sel = 'SUM(CASE WHEN pengisian > 0 THEN 1 ELSE 0 END) as terisi,
                        SUM(CASE WHEN pengisian = 0 THEN 1 ELSE 0 END) as belum,
                        COUNT(*) as tersedia';
            } else {
                $sel = 'SUM(pengisian) as total_terisi, SUM(mc_tko) as total_mc';
            }

            $rows = $q->selectRaw("{$parentCol} as parent, {$childCol} as child, {$sel}")
                ->groupBy($parentCol, $childCol)->get();

            $keys = $mode === 'status' ? ['terisi', 'belum', 'tersedia'] : ['total_terisi', 'total_mc'];
            $tree = [];
            foreach ($rows as $r) {
                $p = $r->parent;
                if (! isset($tree[$p])) {
                    $tree[$p] = ['nama' => $p, 'children' => []];
                    foreach ($keys as $k) $tree[$p][$k] = 0;
                }
                // Total induk mencakup semua posisi (agar cocok dengan angka di kartu).
                foreach ($keys as $k) $tree[$p][$k] += (int) $r->{$k};

                // Hanya tampilkan anak yang punya nama unit (lewati posisi tanpa unit anak).
                if ($r->child !== null && $r->child !== '') {
                    $child = ['nama' => $r->child];
                    foreach ($keys as $k) $child[$k] = (int) $r->{$k};
                    $tree[$p]['children'][] = $child;
                }
            }

            // Urutkan: mode status → belum terbanyak dulu; mode terisi → terisi terbanyak dulu.
            $sortKey = $mode === 'status' ? 'belum' : 'total_terisi';
            foreach ($tree as &$p) {
                usort($p['children'], fn ($a, $b) => $b[$sortKey] <=> $a[$sortKey]);
            }
            unset($p);
            usort($tree, fn ($a, $b) => $b[$sortKey] <=> $a[$sortKey]);

            return $tree;
        };

        // Hanya Direktorat & Kompartemen yang punya drill-down; Departemen tampil flat.
        $soDrill = [
            'status-dir'  => $soHierarki('direktorat', 'kompartemen', 'status'),
            'status-komp' => $soHierarki('kompartemen', 'dept', 'status'),
            'terisi-dir'  => $soHierarki('direktorat', 'kompartemen', 'terisi'),
            'terisi-komp' => $soHierarki('kompartemen', 'dept', 'terisi'),
        ];

        // === CHART: Tren Jabatan === (12 bln x 3 = 36 query → 1 query grouped)
        $awalTren = now()->subMonths(11)->startOfMonth();
        $trenIdx  = HistoryJabatan::whereBetween('tanggal_mulai', [$awalTren, now()->endOfMonth()])
            ->selectRaw("DATE_FORMAT(tanggal_mulai, '%Y-%m') as ym, tipe, COUNT(*) as c")
            ->groupBy('ym', 'tipe')
            ->get()
            ->groupBy('ym');

        // Detail SIAPA saja per bulan+tipe (untuk popup saat batang chart diklik).
        $trenDetailIdx = HistoryJabatan::with('karyawan:id,nama')
            ->whereBetween('tanggal_mulai', [$awalTren, now()->endOfMonth()])
            ->whereIn('tipe', ['promosi', 'mutasi', 'rotasi', 'demosi'])
            ->orderBy('tanggal_mulai')
            ->get(['id', 'karyawan_id', 'jabatan_saat_ini', 'tipe', 'tanggal_mulai'])
            ->groupBy(fn ($h) => \Carbon\Carbon::parse($h->tanggal_mulai)->format('Y-m'));

        $trenBulan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $ym    = $bulan->format('Y-m');
            $rows  = $trenIdx[$ym] ?? collect();
            $recs  = $trenDetailIdx[$ym] ?? collect();
            $orang = fn ($t) => $recs->where('tipe', $t)->map(fn ($h) => [
                'nama'    => $h->karyawan->nama ?? '-',
                'jabatan' => $h->jabatan_saat_ini ?? '-',
                'tgl'     => \Carbon\Carbon::parse($h->tanggal_mulai)->format('d M Y'),
            ])->values();
            $trenBulan[] = [
                'bulan'   => $bulan->translatedFormat('M Y'),
                'promosi' => (int) ($rows->firstWhere('tipe', 'promosi')?->c ?? 0),
                'mutasi'  => (int) ($rows->firstWhere('tipe', 'mutasi')?->c ?? 0),
                'rotasi'  => (int) ($rows->firstWhere('tipe', 'rotasi')?->c ?? 0),
                'demosi'  => (int) ($rows->firstWhere('tipe', 'demosi')?->c ?? 0),
                'detail'  => [
                    'promosi' => $orang('promosi'),
                    'mutasi'  => $orang('mutasi'),
                    'rotasi'  => $orang('rotasi'),
                    'demosi'  => $orang('demosi'),
                ],
            ];
        }

        // === CHART: Tren Kompetensi === (12 bln x 2 = 24 query → 1 query grouped)
        $kompIdx = HistoryAssessmentKompetensi::whereBetween('tanggal_assessment', [$awalTren, now()->endOfMonth()])
            ->selectRaw("DATE_FORMAT(tanggal_assessment, '%Y-%m') as ym, kesimpulan, COUNT(*) as c")
            ->groupBy('ym', 'kesimpulan')
            ->get()
            ->groupBy('ym');

        $trenKompetensi = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $ym    = $bulan->format('Y-m');
            $rows  = $kompIdx[$ym] ?? collect();
            $trenKompetensi[] = [
                'bulan'         => $bulan->translatedFormat('M Y'),
                'qualified'     => (int) ($rows->firstWhere('kesimpulan', 'QUALIFIED')?->c ?? 0),
                'not_qualified' => (int) ($rows->firstWhere('kesimpulan', 'NOT QUALIFIED')?->c ?? 0),
            ];
        }

        // === CHART: Assessment Pie ===
        $assessmentChart = [
            ['label' => 'Ready',                  'value' => $assessmentReady, 'color' => '#16a34a'],
            ['label' => 'Ready with Development',  'value' => $assessmentRWD,  'color' => '#f59e0b'],
            ['label' => 'Not Ready',               'value' => $assessmentNR,   'color' => '#ef4444'],
        ];

        // === CHART: Job Grade === (urut grade tertinggi → terendah, tampil semua)
        $distribusiJobGrade = Karyawan::where('status', 'aktif')
            ->select('job_grade_id', DB::raw('count(*) as total'))
            ->with('jobGrade')->groupBy('job_grade_id')->get()
            ->map(function ($k) {
                $g = (int) ($k->jobGrade->job_grade ?? 0);
                return ['grade' => $g, 'nama' => $g > 0 ? 'JG ' . $k->jobGrade->job_grade : 'Tanpa JG', 'total' => $k->total];
            })->sortByDesc('grade')->values();

        // === CHART: Person Grade === (urut grade tertinggi → terendah, tampil semua)
        $distribusiPersonGrade = Karyawan::where('status', 'aktif')
            ->select('person_grade_id', DB::raw('count(*) as total'))
            ->with('personGrade')->groupBy('person_grade_id')->get()
            ->map(function ($k) {
                $g = (int) ($k->personGrade->person_grade ?? 0);
                return ['grade' => $g, 'nama' => $g > 0 ? 'PG ' . $k->personGrade->person_grade : 'Tanpa PG', 'total' => $k->total];
            })->sortByDesc('grade')->values();

        // === TABEL: Ringkasan Direktorat ===
        // Sebelumnya N+1 (1 + N×6 query). Kini pra-hitung agregat per direktorat
        // dengan JOIN + GROUP BY (5 query), lalu di-lookup per direktorat.
        $tahunIni = now()->year;

        $promosiDir = HistoryJabatan::join('karyawans', 'karyawans.id', '=', 'history_jabatans.karyawan_id')
            ->where('history_jabatans.tipe', 'promosi')
            ->whereYear('history_jabatans.tanggal_mulai', $tahunIni)
            ->groupBy('karyawans.direktorat_id')
            ->selectRaw('karyawans.direktorat_id as did, COUNT(*) as c')->pluck('c', 'did');

        $mutasiDir = HistoryJabatan::join('karyawans', 'karyawans.id', '=', 'history_jabatans.karyawan_id')
            ->where('history_jabatans.tipe', 'mutasi')
            ->whereYear('history_jabatans.tanggal_mulai', $tahunIni)
            ->groupBy('karyawans.direktorat_id')
            ->selectRaw('karyawans.direktorat_id as did, COUNT(*) as c')->pluck('c', 'did');

        $rotasiDir = HistoryJabatan::join('karyawans', 'karyawans.id', '=', 'history_jabatans.karyawan_id')
            ->where('history_jabatans.tipe', 'rotasi')
            ->whereYear('history_jabatans.tanggal_mulai', $tahunIni)
            ->groupBy('karyawans.direktorat_id')
            ->selectRaw('karyawans.direktorat_id as did, COUNT(*) as c')->pluck('c', 'did');

        $assessmentDir = HistoryAssessment::join('karyawans', 'karyawans.id', '=', 'history_assessments.karyawan_id')
            ->groupBy('karyawans.direktorat_id')
            ->selectRaw('karyawans.direktorat_id as did, COUNT(*) as c')->pluck('c', 'did');

        $readyDir = HistoryAssessment::join('karyawans', 'karyawans.id', '=', 'history_assessments.karyawan_id')
            ->where('history_assessments.rekomendasi_final', 'ready')
            ->groupBy('karyawans.direktorat_id')
            ->selectRaw('karyawans.direktorat_id as did, COUNT(*) as c')->pluck('c', 'did');

        $qualifiedDir = HistoryAssessmentKompetensi::join('karyawans', 'karyawans.id', '=', 'history_assessment_kompetensi.karyawan_id')
            ->where('history_assessment_kompetensi.kesimpulan', 'QUALIFIED')
            ->groupBy('karyawans.direktorat_id')
            ->selectRaw('karyawans.direktorat_id as did, COUNT(*) as c')->pluck('c', 'did');

        $ringkasanDirektorat = Direktorat::withCount([
            'karyawans as total_karyawan',
            'karyawans as karyawan_aktif' => fn($q) => $q->where('status', 'aktif'),
        ])->orderBy('total_karyawan', 'desc')->get()->map(fn($d) => [
            'nama'       => $d->nama_direktorat,
            'total'      => $d->total_karyawan,
            'aktif'      => $d->karyawan_aktif,
            'promosi'    => (int) ($promosiDir[$d->id]    ?? 0),
            'mutasi'     => (int) ($mutasiDir[$d->id]     ?? 0),
            'rotasi'     => (int) ($rotasiDir[$d->id]     ?? 0),
            'assessment' => (int) ($assessmentDir[$d->id] ?? 0),
            'ready'      => (int) ($readyDir[$d->id]      ?? 0),
            'qualified'  => (int) ($qualifiedDir[$d->id]  ?? 0),
        ]);

        // === AKAN PENSIUN ===
        $akanPensiun = Karyawan::where('status', 'aktif')
            ->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 53')
            ->orderByRaw('tanggal_lahir ASC')->take(5)->get();

        $aktivitasTerbaru = HistoryJabatan::with(['karyawan', 'jabatan'])->latest()->take(5)->get();

        $assessmentExpire = HistoryAssessment::with('karyawan')
            ->whereNotNull('tanggal_exp_idp')
            ->whereBetween('tanggal_exp_idp', [now(), now()->addDays(30)])
            ->orderBy('tanggal_exp_idp')->take(5)->get();

        $karyawanTerbaru = Karyawan::with(['jabatan', 'departemen'])->latest('tanggal_masuk')->take(5)->get();

        // === DEMOGRAFI === (gender 2 + usia 4 = 6 query → 1 query grouped)
        $demo = Karyawan::where('status', 'aktif')->selectRaw("
            SUM(jenis_kelamin = 'L') as l,
            SUM(jenis_kelamin = 'P') as p,
            SUM(TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 30) as u1,
            SUM(TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 30 AND 39) as u2,
            SUM(TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 40 AND 49) as u3,
            SUM(TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 50) as u4
        ")->first();

        $genderChart = ['L' => (int) $demo->l, 'P' => (int) $demo->p];
        $usiaChart = [
            '< 30'  => (int) $demo->u1,
            '30-39' => (int) $demo->u2,
            '40-49' => (int) $demo->u3,
            '50+'   => (int) $demo->u4,
        ];

        // === REMINDER PROMOSI (ringkasan) === selaras dengan halaman Reminder Promosi
        $reminder = app(ReminderPromosiService::class)->build();
        $reminderEligibleNow = count(array_filter($reminder['items'], fn ($i) => $i['eligible_now']));
        $reminderSoon        = count($reminder['items']) - $reminderEligibleNow;

        // === ULANG TAHUN BULAN INI ===
        $ulangTahunBulanIni = Karyawan::where('status', 'aktif')
            ->whereMonth('tanggal_lahir', now()->month)
            ->orderByRaw('DAY(tanggal_lahir) ASC')
            ->take(8)
            ->get(['id', 'nik', 'nama', 'tanggal_lahir', 'jabatan_saat_ini']);

        // === DISTRIBUSI BAND === (dari Job Grade → Band)
        $jgCounts = Karyawan::where('status', 'aktif')
            ->join('job_grade', 'job_grade.id', '=', 'karyawans.job_grade_id')
            ->groupBy('job_grade.job_grade')
            ->selectRaw('job_grade.job_grade as jg, COUNT(*) as total')
            ->pluck('total', 'jg');
        $bandTmp = [];
        foreach ($jgCounts as $jg => $total) {
            $band = Karyawan::getBandFromGrade((int) $jg);
            $bandTmp[$band] = ($bandTmp[$band] ?? 0) + (int) $total;
        }
        $distribusiBand = [];
        foreach (array_keys(Karyawan::bandConfig()) as $band) {
            $distribusiBand[] = ['nama' => $band, 'total' => $bandTmp[$band] ?? 0];
        }
        if (!empty($bandTmp['-'])) {
            $distribusiBand[] = ['nama' => 'Tanpa Band', 'total' => $bandTmp['-']];
        }

        // === DISTRIBUSI PENDIDIKAN ===
        $pendCounts = Karyawan::where('status', 'aktif')
            ->selectRaw("COALESCE(NULLIF(TRIM(jenjang_pendidikan), ''), 'Belum diisi') as jp, COUNT(*) as total")
            ->groupBy('jp')
            ->pluck('total', 'jp');
        $urutPend = ['SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];
        $distribusiPendidikan = [];
        foreach ($urutPend as $u) {
            if (isset($pendCounts[$u])) {
                $distribusiPendidikan[] = ['nama' => $u, 'total' => (int) $pendCounts[$u]];
            }
        }
        foreach ($pendCounts as $jp => $total) {
            if (!in_array($jp, $urutPend, true)) {
                $distribusiPendidikan[] = ['nama' => $jp, 'total' => (int) $total];
            }
        }

        return view('dashboard', compact(
            'totalKaryawan', 'karyawanAktif', 'karyawanTidakAktif', 'karyawanBaru',
            'totalHistoryJabatan', 'promosiThisYear', 'mutasiThisYear', 'rotasiThisYear', 'demosiThisYear',
            'totalAssessment', 'assessmentReady', 'assessmentRWD', 'assessmentNR',
            'totalKompetensi', 'totalQualified', 'totalNotQualified', 'trenKompetensi',
            'pejabatAktif', 'pejabatSVP', 'pejabatVP', 'pejabatSPM', 'pejabatPM',
            'pgsAktif', 'pjsAktif', 'talentPool',
            'trenBulan', 'assessmentChart', 'distribusiJobGrade', 'distribusiPersonGrade',
            'ringkasanDirektorat', 'akanPensiun', 'aktivitasTerbaru', 'assessmentExpire',
            'karyawanTerbaru', 'genderChart', 'usiaChart',
            'soTotalPosisi', 'soTotalMc', 'soTerisi', 'soCore', 'soNonCore', 'soDeviasi', 'soBulan', 'soTahun',
            'soCoreTerisi', 'soNonCoreTerisi', 'soCoreMc', 'soNonCoreMc',
            'soPerDirektorat', 'soPerKompartemen', 'soPerDepartemen',
            'soTerisiDirektorat', 'soTerisiKompartemen', 'soTerisiDepartemen',
            'soDrill',
            'reminderEligibleNow', 'reminderSoon',
            'ulangTahunBulanIni', 'distribusiBand', 'distribusiPendidikan'
        ));
    }
}
