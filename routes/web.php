<?php

use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistoryJabatanController;
use App\Http\Controllers\HistoryKaryawanController;
use App\Http\Controllers\HistoryAssessmentController;
use App\Http\Controllers\HistoryAssessmentAllController;
use App\Http\Controllers\HistoryAssessmentKompetensiController;
use App\Http\Controllers\ImportAssessmentController;
use App\Http\Controllers\ImportHistoryJabatanController;
use App\Http\Controllers\PgsPjsController;
use App\Http\Controllers\HistoryPejabatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\SuratPentingController;
use App\Http\Controllers\MasterJabatanController;
use App\Http\Controllers\MasterDirektoratController;
use App\Http\Controllers\MasterKompartemenController;
use App\Http\Controllers\MasterDepartemenController;
use App\Http\Controllers\MasterJobGradeController;
use App\Http\Controllers\MasterPersonGradeController;
use App\Http\Controllers\MasterKodeStrukturController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\TalentPoolController;
use App\Http\Controllers\PenilaianKaryawanController;
use App\Http\Controllers\KalibrasiKaryawanController;
use App\Http\Controllers\UsulanPromosiController;
use App\Http\Controllers\UsulanMutasiController;
use App\Http\Controllers\StrukturOrganisasiController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {

    // ===== PROFILE (semua role) =====
    Route::get('/profile',           [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',         [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password',[ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',        [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== DASHBOARD (redirect user → struktur organisasi) =====
    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = Auth::user();
        if ($user->isUser()) {
            return redirect()->route('struktur-organisasi.index');
        }
        return app(DashboardController::class)->index();
    })->middleware('verified')->name('dashboard');

    // ===== STRUKTUR ORGANISASI (semua role bisa akses) =====
    Route::prefix('struktur-organisasi')->name('struktur-organisasi.')->group(function () {
        Route::get('/',       [StrukturOrganisasiController::class, 'index'])->name('index');
        Route::get('/export', [StrukturOrganisasiController::class, 'export'])->name('export');

        Route::middleware('not_user_role')->group(function () {
            Route::post('/',               [StrukturOrganisasiController::class, 'store'])->name('store');
            Route::post('/salin-periode',   [StrukturOrganisasiController::class, 'salinPeriode'])->name('salin-periode');
            Route::delete('/hapus-periode',  [StrukturOrganisasiController::class, 'hapusPeriode'])->name('hapus-periode');
            Route::post('/rename-group',    [StrukturOrganisasiController::class, 'renameGroup'])->name('rename-group');
            Route::delete('/delete-group',  [StrukturOrganisasiController::class, 'deleteGroup'])->name('delete-group');
            Route::patch('/{so}',          [StrukturOrganisasiController::class, 'update'])->name('update');
            Route::put('/{so}',            [StrukturOrganisasiController::class, 'update'])->name('update.put');
            Route::delete('/{so}',         [StrukturOrganisasiController::class, 'destroy'])->name('destroy');
            Route::patch('/{so}/posisi',   [StrukturOrganisasiController::class, 'editPosisi'])->name('editPosisi');
        });
    });

    // ===== NOTIFIKASI (semua role) =====
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/',                   [NotifikasiController::class, 'index'])->name('index');
        Route::get('/fetch',              [NotifikasiController::class, 'fetch'])->name('fetch');
        Route::post('/read-all',          [NotifikasiController::class, 'readAll'])->name('readAll');
        Route::post('/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('read');
        Route::delete('/{notifikasi}',    [NotifikasiController::class, 'destroy'])->name('destroy');
    });

    // ===== SEMUA ROUTE BERIKUT: HANYA ADMIN & SUPER ADMIN =====
    Route::middleware('not_user_role')->group(function () {

        // Karyawan
        Route::get('karyawan/export',            [KaryawanController::class, 'export'])->name('karyawan.export');
        Route::get('karyawan/import',            [KaryawanController::class, 'importPage'])->name('karyawan.import');
        Route::post('karyawan/import',           [KaryawanController::class, 'import'])->name('karyawan.import.store');
        Route::get('karyawan/template-download', [KaryawanController::class, 'downloadTemplate'])->name('karyawan.template');
        Route::resource('karyawan', KaryawanController::class);

        // History Jabatan
        Route::prefix('karyawan/{karyawan}/history-jabatan')->name('history_jabatan.')->group(function () {
            Route::get('/',                    [HistoryJabatanController::class, 'index'])->name('index');
            Route::get('/create',              [HistoryJabatanController::class, 'create'])->name('create');
            Route::post('/',                   [HistoryJabatanController::class, 'store'])->name('store');
            Route::delete('/{historyJabatan}', [HistoryJabatanController::class, 'destroy'])->name('destroy');
        });

        // History Karyawan
        Route::prefix('history-karyawan')->name('history_karyawan.')->group(function () {
            Route::get('/',       [HistoryKaryawanController::class, 'index'])->name('index');
            Route::get('/export', [HistoryKaryawanController::class, 'export'])->name('export');
            Route::middleware('super_admin')->group(function () {
                Route::get('/import',          [ImportHistoryJabatanController::class, 'page'])->name('import');
                Route::post('/import',         [ImportHistoryJabatanController::class, 'import'])->name('import.store');
                Route::get('/import/template', [ImportHistoryJabatanController::class, 'downloadTemplate'])->name('import.template');
            });
            Route::get('/{karyawan}', [HistoryKaryawanController::class, 'show'])->name('show');
        });

        // History Assessment per Karyawan
        Route::prefix('karyawan/{karyawan}/history-assessment')->name('history_assessment.')->group(function () {
            Route::get('/',                       [HistoryAssessmentController::class, 'index'])->name('index');
            Route::get('/create',                 [HistoryAssessmentController::class, 'create'])->name('create');
            Route::post('/',                      [HistoryAssessmentController::class, 'store'])->name('store');
            Route::delete('/{historyAssessment}', [HistoryAssessmentController::class, 'destroy'])->name('destroy');
        });

        // Assessment Kompetensi per Karyawan
        Route::prefix('karyawan/{karyawan}/assessment-kompetensi')->name('assessment_kompetensi.')->group(function () {
            Route::get('/create',          [HistoryAssessmentKompetensiController::class, 'create'])->name('create');
            Route::post('/',               [HistoryAssessmentKompetensiController::class, 'store'])->name('store');
            Route::delete('/{kompetensi}', [HistoryAssessmentKompetensiController::class, 'destroy'])->name('destroy');
        });

        // History Assessment All
        Route::prefix('history-assessment')->name('history_assessment_all.')->group(function () {
            Route::get('/',                           [HistoryAssessmentAllController::class, 'index'])->name('index');
            Route::get('/export',                     [HistoryAssessmentAllController::class, 'export'])->name('export');
            Route::get('/export/kompetensi',          [HistoryAssessmentAllController::class, 'exportKompetensi'])->name('export.kompetensi');
            Route::delete('/{assessment}',            [HistoryAssessmentAllController::class, 'destroy'])->name('destroy');
            Route::get('/import',                     [ImportAssessmentController::class, 'page'])->name('import');
            Route::post('/import',                    [ImportAssessmentController::class, 'import'])->name('import.store');
            Route::get('/import/template',            [ImportAssessmentController::class, 'downloadTemplate'])->name('import.template');
            Route::post('/import/kompetensi',         [ImportAssessmentController::class, 'importKompetensi'])->name('import.store.kompetensi');
            Route::get('/import/template/kompetensi', [ImportAssessmentController::class, 'downloadTemplateKompetensi'])->name('import.template.kompetensi');
        });

        // Assessment Kompetensi All
        Route::delete('/history-assessment/kompetensi/{kompetensi}',
            [HistoryAssessmentAllController::class, 'destroyKompetensi'])
            ->name('assessment_kompetensi_all.destroy');

        // PGS & PJS
        Route::prefix('pgs-pjs')->name('pgs_pjs.')->group(function () {
            Route::get('/',                  [PgsPjsController::class, 'index'])->name('index');
            Route::get('/create',            [PgsPjsController::class, 'create'])->name('create');
            Route::post('/',                 [PgsPjsController::class, 'store'])->name('store');
            Route::get('/export',            [PgsPjsController::class, 'export'])->name('export');
            Route::patch('/{pgsPjs}/akhiri', [PgsPjsController::class, 'akhiri'])->name('akhiri');
            Route::delete('/{pgsPjs}',       [PgsPjsController::class, 'destroy'])->name('destroy');
        });

        // History Pejabat
        Route::prefix('history-pejabat')->name('history_pejabat.')->group(function () {
            Route::get('/',       [HistoryPejabatController::class, 'index'])->name('index');
            Route::get('/export', [HistoryPejabatController::class, 'export'])->name('export');
            Route::delete('/{historyPejabat}', [HistoryPejabatController::class, 'destroy'])->name('destroy');
        });

        // Surat Penting
        Route::prefix('surat-penting')->name('surat_penting.')->group(function () {
            Route::get('/',                        [SuratPentingController::class, 'index'])->name('index');
            Route::get('/create',                  [SuratPentingController::class, 'create'])->name('create');
            Route::post('/',                       [SuratPentingController::class, 'store'])->name('store');
            Route::get('/{suratPenting}',          [SuratPentingController::class, 'show'])->name('show');
            Route::get('/{suratPenting}/download', [SuratPentingController::class, 'download'])->name('download');
            Route::delete('/{suratPenting}',       [SuratPentingController::class, 'destroy'])->name('destroy');
        });

        // Talent Pool
        Route::prefix('talent-pool')->name('talent_pool.')->group(function () {
            Route::get('/',                [TalentPoolController::class, 'index'])->name('index');
            Route::get('/create',          [TalentPoolController::class, 'create'])->name('create');
            Route::post('/',               [TalentPoolController::class, 'store'])->name('store');
            Route::put('/{talentPool}',    [TalentPoolController::class, 'update'])->name('update');
            Route::delete('/{talentPool}', [TalentPoolController::class, 'destroy'])->name('destroy');
        });

        // Penilaian Karyawan
        Route::prefix('karyawan/{karyawan}/penilaian')->name('penilaian_karyawan.')->group(function () {
            Route::get('/',               [PenilaianKaryawanController::class, 'index'])->name('index');
            Route::get('/create',         [PenilaianKaryawanController::class, 'create'])->name('create');
            Route::post('/',              [PenilaianKaryawanController::class, 'store'])->name('store');
            Route::delete('/{penilaian}', [PenilaianKaryawanController::class, 'destroy'])->name('destroy');
        });

        // Kalibrasi Karyawan
        Route::prefix('karyawan/{karyawan}/kalibrasi')->name('kalibrasi_karyawan.')->group(function () {
            Route::get('/',               [KalibrasiKaryawanController::class, 'index'])->name('index');
            Route::get('/create',         [KalibrasiKaryawanController::class, 'create'])->name('create');
            Route::post('/',              [KalibrasiKaryawanController::class, 'store'])->name('store');
            Route::get('/{kalibrasi}/edit', [KalibrasiKaryawanController::class, 'edit'])->name('edit');
            Route::put('/{kalibrasi}',    [KalibrasiKaryawanController::class, 'update'])->name('update');
            Route::delete('/{kalibrasi}', [KalibrasiKaryawanController::class, 'destroy'])->name('destroy');
        });

        // Usulan Promosi
        Route::prefix('usulan-promosi')->name('usulan_promosi.')->group(function () {
            Route::get('/',                        [UsulanPromosiController::class, 'index'])->name('index');
            Route::get('/create',                  [UsulanPromosiController::class, 'create'])->name('create');
            Route::post('/',                       [UsulanPromosiController::class, 'store'])->name('store');
            Route::get('/{usulanPromosi}',         [UsulanPromosiController::class, 'show'])->name('show');
            Route::patch('/{usulanPromosi}/status',[UsulanPromosiController::class, 'updateStatus'])->name('update_status');
            Route::patch('/{usulanPromosi}/terbitkan-sk',  [UsulanPromosiController::class, 'terbitkanSk'])->name('terbitkan_sk');
            Route::delete('/{usulanPromosi}',      [UsulanPromosiController::class, 'destroy'])->name('destroy');
        });
        Route::get('api/usulan-promosi/karyawan',  [UsulanPromosiController::class, 'getKaryawanData'])->name('usulan_promosi.karyawan_data');
        Route::get('api/usulan-promosi/assessments',[UsulanPromosiController::class, 'getAssessments'])->name('usulan_promosi.assessments');
        Route::get('api/karyawan/{karyawan_id}/talent-kpi-preview',[UsulanPromosiController::class, 'getTalentKpiPreview'])->name('usulan_promosi.talent_kpi_preview');

        Route::prefix('usulan-mutasi')->name('usulan_mutasi.')->group(function () {
            Route::get('/',                             [UsulanMutasiController::class, 'index'])->name('index');
            Route::get('/create',                       [UsulanMutasiController::class, 'create'])->name('create');
            Route::post('/',                            [UsulanMutasiController::class, 'store'])->name('store');
            Route::patch('/{usulanMutasi}/terbitkan-sk',[UsulanMutasiController::class, 'terbitkanSk'])->name('terbitkan_sk');
            Route::delete('/{usulanMutasi}',            [UsulanMutasiController::class, 'destroy'])->name('destroy');
        });
        // API AJAX
        Route::get('api/karyawan/{id}/detail',  [StrukturOrganisasiController::class, 'getKaryawanData'])->name('api.karyawan.detail');
        Route::get('api/karyawan/{id}/profile', [StrukturOrganisasiController::class, 'getKaryawanProfile'])->name('api.karyawan.profile');

        // FAQ
        Route::get('/faq', fn() => view('faq'))->name('faq');

        // Laporan
        Route::get('/laporan/bulanan', [LaporanController::class, 'bulanan'])->name('laporan.bulanan');

        // ===== SUPER ADMIN ONLY =====
        Route::middleware('super_admin')->group(function () {

            Route::prefix('activity-log')->name('activity_log.')->group(function () {
                Route::get('/',    [ActivityLogController::class, 'index'])->name('index');
                Route::delete('/', [ActivityLogController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('akun')->name('akun.')->group(function () {
                Route::get('/',          [AkunController::class, 'index'])->name('index');
                Route::post('/',         [AkunController::class, 'store'])->name('store');
                Route::put('/{akun}',    [AkunController::class, 'update'])->name('update');
                Route::delete('/{akun}', [AkunController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('master')->name('master.')->group(function () {
                Route::get('jabatan',               [MasterJabatanController::class, 'index'])->name('jabatan.index');
                Route::post('jabatan',              [MasterJabatanController::class, 'store'])->name('jabatan.store');
                Route::put('jabatan/{id}',          [MasterJabatanController::class, 'update'])->name('jabatan.update');
                Route::delete('jabatan/{id}',       [MasterJabatanController::class, 'destroy'])->name('jabatan.destroy');
                Route::get('direktorat',            [MasterDirektoratController::class, 'index'])->name('direktorat.index');
                Route::post('direktorat',           [MasterDirektoratController::class, 'store'])->name('direktorat.store');
                Route::put('direktorat/{id}',       [MasterDirektoratController::class, 'update'])->name('direktorat.update');
                Route::delete('direktorat/{id}',    [MasterDirektoratController::class, 'destroy'])->name('direktorat.destroy');
                Route::get('kompartemen',           [MasterKompartemenController::class, 'index'])->name('kompartemen.index');
                Route::post('kompartemen',          [MasterKompartemenController::class, 'store'])->name('kompartemen.store');
                Route::put('kompartemen/{id}',      [MasterKompartemenController::class, 'update'])->name('kompartemen.update');
                Route::delete('kompartemen/{id}',   [MasterKompartemenController::class, 'destroy'])->name('kompartemen.destroy');
                Route::get('departemen',            [MasterDepartemenController::class, 'index'])->name('departemen.index');
                Route::post('departemen',           [MasterDepartemenController::class, 'store'])->name('departemen.store');
                Route::put('departemen/{id}',       [MasterDepartemenController::class, 'update'])->name('departemen.update');
                Route::delete('departemen/{id}',    [MasterDepartemenController::class, 'destroy'])->name('departemen.destroy');
                Route::get('job-grade',             [MasterJobGradeController::class, 'index'])->name('job-grade.index');
                Route::post('job-grade',            [MasterJobGradeController::class, 'store'])->name('job-grade.store');
                Route::put('job-grade/{id}',        [MasterJobGradeController::class, 'update'])->name('job-grade.update');
                Route::delete('job-grade/{id}',     [MasterJobGradeController::class, 'destroy'])->name('job-grade.destroy');
                Route::get('person-grade',          [MasterPersonGradeController::class, 'index'])->name('person-grade.index');
                Route::post('person-grade',         [MasterPersonGradeController::class, 'store'])->name('person-grade.store');
                Route::put('person-grade/{id}',     [MasterPersonGradeController::class, 'update'])->name('person-grade.update');
                Route::delete('person-grade/{id}',  [MasterPersonGradeController::class, 'destroy'])->name('person-grade.destroy');
                Route::get('kode-struktur',         [MasterKodeStrukturController::class, 'index'])->name('kode-struktur.index');
                Route::post('kode-struktur',        [MasterKodeStrukturController::class, 'store'])->name('kode-struktur.store');
                Route::put('kode-struktur/{id}',    [MasterKodeStrukturController::class, 'update'])->name('kode-struktur.update');
                Route::delete('kode-struktur/{id}', [MasterKodeStrukturController::class, 'destroy'])->name('kode-struktur.destroy');
            });
        });
    });
});

require __DIR__.'/auth.php';