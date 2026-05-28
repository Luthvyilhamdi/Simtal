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
use App\Http\Controllers\StrukturOrganisasiController;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Route;

Schedule::command('notifikasi:generate')->dailyAt('07:00');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {

    // ===== PROFILE =====
    Route::get('/profile',           [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',         [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password',[ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',        [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== DASHBOARD =====
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    // ===== KARYAWAN =====
    Route::get('karyawan/export',            [KaryawanController::class, 'export'])->name('karyawan.export');
    Route::get('karyawan/import',            [KaryawanController::class, 'importPage'])->name('karyawan.import');
    Route::post('karyawan/import',           [KaryawanController::class, 'import'])->name('karyawan.import.store');
    Route::get('karyawan/template-download', [KaryawanController::class, 'downloadTemplate'])->name('karyawan.template');
    Route::resource('karyawan', KaryawanController::class);

    // ===== HISTORY JABATAN =====
    Route::prefix('karyawan/{karyawan}/history-jabatan')->name('history_jabatan.')->group(function () {
        Route::get('/',                    [HistoryJabatanController::class, 'index'])->name('index');
        Route::get('/create',              [HistoryJabatanController::class, 'create'])->name('create');
        Route::post('/',                   [HistoryJabatanController::class, 'store'])->name('store');
        Route::delete('/{historyJabatan}', [HistoryJabatanController::class, 'destroy'])->name('destroy');
    });

    // ===== HISTORY KARYAWAN =====
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

    // ===== HISTORY ASSESSMENT PER KARYAWAN =====
    Route::prefix('karyawan/{karyawan}/history-assessment')->name('history_assessment.')->group(function () {
        Route::get('/',                       [HistoryAssessmentController::class, 'index'])->name('index');
        Route::get('/create',                 [HistoryAssessmentController::class, 'create'])->name('create');
        Route::post('/',                      [HistoryAssessmentController::class, 'store'])->name('store');
        Route::delete('/{historyAssessment}', [HistoryAssessmentController::class, 'destroy'])->name('destroy');
    });

    // ===== ASSESSMENT KOMPETENSI PER KARYAWAN =====
    Route::prefix('karyawan/{karyawan}/assessment-kompetensi')->name('assessment_kompetensi.')->group(function () {
        Route::get('/create',         [HistoryAssessmentKompetensiController::class, 'create'])->name('create');
        Route::post('/',              [HistoryAssessmentKompetensiController::class, 'store'])->name('store');
        Route::delete('/{kompetensi}',[HistoryAssessmentKompetensiController::class, 'destroy'])->name('destroy');
    });

    // ===== HISTORY ASSESSMENT ALL (GLOBAL) =====
    Route::prefix('history-assessment')->name('history_assessment_all.')->group(function () {
        Route::get('/',                [HistoryAssessmentAllController::class, 'index'])->name('index');
        Route::get('/export',          [HistoryAssessmentAllController::class, 'export'])->name('export');
        Route::delete('/{assessment}', [HistoryAssessmentAllController::class, 'destroy'])->name('destroy');
        Route::get('/import',          [ImportAssessmentController::class, 'page'])->name('import');
        Route::post('/import',         [ImportAssessmentController::class, 'import'])->name('import.store');
        Route::get('/import/template', [ImportAssessmentController::class, 'downloadTemplate'])->name('import.template');
    });

    // ===== ASSESSMENT KOMPETENSI ALL (GLOBAL) =====
    Route::delete('/history-assessment/kompetensi/{kompetensi}',
        [HistoryAssessmentAllController::class, 'destroyKompetensi'])
        ->name('assessment_kompetensi_all.destroy');

    // ===== PGS & PJS =====
    Route::prefix('pgs-pjs')->name('pgs_pjs.')->group(function () {
        Route::get('/',                  [PgsPjsController::class, 'index'])->name('index');
        Route::get('/create',            [PgsPjsController::class, 'create'])->name('create');
        Route::post('/',                 [PgsPjsController::class, 'store'])->name('store');
        Route::get('/export',            [PgsPjsController::class, 'export'])->name('export');
        Route::patch('/{pgsPjs}/akhiri', [PgsPjsController::class, 'akhiri'])->name('akhiri');
        Route::delete('/{pgsPjs}',       [PgsPjsController::class, 'destroy'])->name('destroy');
    });

    // ===== HISTORY PEJABAT =====
    Route::prefix('history-pejabat')->name('history_pejabat.')->group(function () {
        Route::get('/',       [HistoryPejabatController::class, 'index'])->name('index');
        Route::get('/export', [HistoryPejabatController::class, 'export'])->name('export');
    });

    // ===== NOTIFIKASI =====
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/',                   [NotifikasiController::class, 'index'])->name('index');
        Route::get('/fetch',              [NotifikasiController::class, 'fetch'])->name('fetch');
        Route::post('/read-all',          [NotifikasiController::class, 'readAll'])->name('readAll');
        Route::post('/{notifikasi}/read', [NotifikasiController::class, 'read'])->name('read');
        Route::delete('/{notifikasi}',    [NotifikasiController::class, 'destroy'])->name('destroy');
    });

    // ===== SURAT PENTING =====
    Route::prefix('surat-penting')->name('surat_penting.')->group(function () {
        Route::get('/',                        [SuratPentingController::class, 'index'])->name('index');
        Route::get('/create',                  [SuratPentingController::class, 'create'])->name('create');
        Route::post('/',                       [SuratPentingController::class, 'store'])->name('store');
        Route::get('/{suratPenting}',          [SuratPentingController::class, 'show'])->name('show');
        Route::get('/{suratPenting}/download', [SuratPentingController::class, 'download'])->name('download');
        Route::delete('/{suratPenting}',       [SuratPentingController::class, 'destroy'])->name('destroy');
    });

    // ===== STRUKTUR ORGANISASI =====
    Route::prefix('struktur-organisasi')->name('struktur-organisasi.')->group(function () {
        Route::get('/',              [StrukturOrganisasiController::class, 'index'])->name('index');
        Route::get('/export',        [StrukturOrganisasiController::class, 'export'])->name('export');
        Route::post('/',             [StrukturOrganisasiController::class, 'store'])->name('store');
        Route::post('/salin-periode',[StrukturOrganisasiController::class, 'salinPeriode'])->name('salin-periode');
        Route::patch('/{so}',        [StrukturOrganisasiController::class, 'update'])->name('update');
        Route::put('/{so}',          [StrukturOrganisasiController::class, 'update'])->name('update.put');
        Route::delete('/{so}',       [StrukturOrganisasiController::class, 'destroy'])->name('destroy');
    });

    // ===== API AJAX =====
    Route::get('api/karyawan/{id}/detail',  [StrukturOrganisasiController::class, 'getKaryawanData'])->name('api.karyawan.detail');
    Route::get('api/karyawan/{id}/profile', [StrukturOrganisasiController::class, 'getKaryawanProfile'])->name('api.karyawan.profile');

    // ===== SUPER ADMIN ONLY =====
    Route::middleware('super_admin')->group(function () {

        // Activity Log
        Route::prefix('activity-log')->name('activity_log.')->group(function () {
            Route::get('/',    [ActivityLogController::class, 'index'])->name('index');
            Route::delete('/', [ActivityLogController::class, 'destroy'])->name('destroy');
        });

        // Akun
        Route::prefix('akun')->name('akun.')->group(function () {
            Route::get('/',          [AkunController::class, 'index'])->name('index');
            Route::post('/',         [AkunController::class, 'store'])->name('store');
            Route::put('/{akun}',    [AkunController::class, 'update'])->name('update');
            Route::delete('/{akun}', [AkunController::class, 'destroy'])->name('destroy');
        });

        // Master Data
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

require __DIR__.'/auth.php';