<?php

use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistoryJabatanController;
use App\Http\Controllers\HistoryKaryawanController;
use App\Http\Controllers\HistoryAssessmentController;
use App\Http\Controllers\PgsPjsController;
use App\Http\Controllers\HistoryPejabatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryAssessmentAllController;
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
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Route;


Schedule::command('notifikasi:generate')->dailyAt('07:00');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    // CRUD Karyawan
    Route::resource('karyawan', KaryawanController::class);

    // History Jabatan
    Route::prefix('karyawan/{karyawan}/history-jabatan')
        ->name('history_jabatan.')
        ->group(function () {
            Route::get('/',                    [HistoryJabatanController::class, 'index'])->name('index');
            Route::get('/create',              [HistoryJabatanController::class, 'create'])->name('create');
            Route::post('/',                   [HistoryJabatanController::class, 'store'])->name('store');
            Route::delete('/{historyJabatan}', [HistoryJabatanController::class, 'destroy'])->name('destroy');
            // Route::get('/export',              [HistoryJabatanController::class, 'export'])->name('export');
        });

    // History Karyawan (view only)
    Route::prefix('history-karyawan')
        ->name('history_karyawan.')
        ->group(function () {
            Route::get('/',           [HistoryKaryawanController::class, 'index'])->name('index');
            Route::get('/export',     [HistoryKaryawanController::class, 'export'])->name('export'); // ← harus sebelum /{karyawan}
            Route::get('/{karyawan}', [HistoryKaryawanController::class, 'show'])->name('show');
        });

    // History Assessment
    Route::prefix('karyawan/{karyawan}/history-assessment')
        ->name('history_assessment.')
        ->group(function () {
            Route::get('/',                       [HistoryAssessmentController::class, 'index'])->name('index');
            Route::get('/create',                 [HistoryAssessmentController::class, 'create'])->name('create');
            Route::post('/',                      [HistoryAssessmentController::class, 'store'])->name('store');
            Route::delete('/{historyAssessment}', [HistoryAssessmentController::class, 'destroy'])->name('destroy');
        });

    Route::prefix('history-assessment')
    ->name('history_assessment_all.')
    ->group(function () {
        Route::get('/',        [HistoryAssessmentAllController::class, 'index'])->name('index');
        Route::get('/export',  [HistoryAssessmentAllController::class, 'export'])->name('export');
    });

    // PGS & PJS
    Route::prefix('pgs-pjs')->name('pgs_pjs.')->group(function () {
        Route::get('/',                    [PgsPjsController::class, 'index'])->name('index');
        Route::get('/create',              [PgsPjsController::class, 'create'])->name('create');
        Route::post('/',                   [PgsPjsController::class, 'store'])->name('store');
        Route::get('/export',              [PgsPjsController::class, 'export'])->name('export');
        Route::patch('/{pgsPjs}/akhiri',   [PgsPjsController::class, 'akhiri'])->name('akhiri');
        Route::delete('/{pgsPjs}',         [PgsPjsController::class, 'destroy'])->name('destroy');
    });

    // History Pejabat
    Route::prefix('history-pejabat')->name('history_pejabat.')->group(function () {
        Route::get('/',        [HistoryPejabatController::class, 'index'])->name('index');
        Route::get('/export',  [HistoryPejabatController::class, 'export'])->name('export');
    });

    Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::prefix('akun')->name('akun.')->group(function () {
        Route::get('/',            [AkunController::class, 'index'])->name('index');
        Route::post('/',           [AkunController::class, 'store'])->name('store');
        Route::put('/{akun}',      [AkunController::class, 'update'])->name('update');
        Route::delete('/{akun}',   [AkunController::class, 'destroy'])->name('destroy');
    });
    });

    Route::middleware('auth')->group(function () {
    // ... route lainnya ...

    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/',           [NotifikasiController::class, 'index'])->name('index');
        Route::get('/fetch',      [NotifikasiController::class, 'fetch'])->name('fetch');
        Route::post('/read-all',  [NotifikasiController::class, 'readAll'])->name('readAll');
        Route::post('/{notifikasi}/read',    [NotifikasiController::class, 'read'])->name('read');
        Route::delete('/{notifikasi}',       [NotifikasiController::class, 'destroy'])->name('destroy');
    });
    });

    Route::prefix('surat-penting')->name('surat_penting.')->group(function () {
    Route::get('/',              [SuratPentingController::class, 'index'])->name('index');
    Route::get('/create',        [SuratPentingController::class, 'create'])->name('create');
    Route::post('/',             [SuratPentingController::class, 'store'])->name('store');
    Route::get('/{suratPenting}',          [SuratPentingController::class, 'show'])->name('show');
    Route::get('/{suratPenting}/download', [SuratPentingController::class, 'download'])->name('download');
    Route::delete('/{suratPenting}',       [SuratPentingController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['auth', 'super_admin'])->prefix('master')->name('master.')->group(function () {
    Route::resource('jabatan',       MasterJabatanController::class)->except(['show']);
    Route::resource('direktorat',    MasterDirektoratController::class)->except(['show']);
    Route::resource('kompartemen',   MasterKompartemenController::class)->except(['show']);
    Route::resource('departemen',    MasterDepartemenController::class)->except(['show']);
    Route::resource('job-grade',     MasterJobGradeController::class)->except(['show']);
    Route::resource('person-grade',  MasterPersonGradeController::class)->except(['show']);
    Route::resource('kode-struktur', MasterKodeStrukturController::class)->except(['show']);
    });
    
});

require __DIR__.'/auth.php';