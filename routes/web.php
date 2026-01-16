<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Prodi\BorangUploadController;
use App\Http\Controllers\Prodi\PengajuanBorangController;
use App\Http\Controllers\Keuangan\ValidasiPembayaranController;
use App\Http\Controllers\Profile\{PasswordResetController, ProfileController};
use App\Http\Controllers\Prodi\{DeskEvaluatorController, PengajuanAkreditasiController, PemetaanAkreditasiController};
use App\Http\Controllers\Asesmen\{AsesmenController, AKController, ALController, ALDocumentController, BorangValidatorController, PenawaranController, ValidasiController};
use App\Http\Controllers\Master\{ElemenStandarController, JenisIndikatorController, IndikatorController, IndikatorPenilaianElemenController, KriteriaController, UniversityController, StudyProgramController};
use App\Http\Controllers\{AuthController, BobotPenilaianController, DashboardController, PenugasanController, BandingController, PedomanController, DokumenController, PanduanController, BantuanController, SettingsController, ActivityController, TaskController, LaporanController, DegreeLevelController, DEController};


// Dashboard (awal)
Route::get('/', function () {
    return view('home');
})->name('home');

// LOGIN / REGISTER
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// CHANGE PASSWORD FIRST TIME
Route::get('/change-password-first', [AuthController::class, 'showChangePasswordFirst'])->name('change.password.first')->middleware('auth');
Route::post('/change-password-first', [AuthController::class, 'changePasswordFirst'])->name('change.password.first.post')->middleware('auth');
Route::post('/change-password-skip', [AuthController::class, 'skipChangePassword'])->name('change.password.skip')->middleware('auth');

// FORGOT PASSWORD
Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->name('password.email');

// RESET PASSWORD
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])
    ->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->name('password.update');

// ===== TESTING ROUTES (NO AUTH) =====
// Route khusus untuk testing di Postman tanpa authentication
Route::prefix('api/test')->name('api.test.')->group(function () {
    Route::get('/pemetaan/stats', [PemetaanAkreditasiController::class, 'getStatsForTesting'])->name('pemetaan.stats');
    Route::get('/pemetaan/timeline/{periode?}', [PemetaanAkreditasiController::class, 'getTimelineForTesting'])->name('pemetaan.timeline');
    Route::get('/pemetaan/calendar', [PemetaanAkreditasiController::class, 'getCalendarForTesting'])->name('pemetaan.calendar');
    Route::get('/pemetaan/programs', [PemetaanAkreditasiController::class, 'getProgramsForTesting'])->name('pemetaan.programs');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // PEMETAAN AKREDITASI
    Route::prefix('pemetaan')->name('pemetaan.')->group(function () {
        Route::get('/', [PemetaanAkreditasiController::class, 'index'])->name('index');
        Route::get('/timeline/ajax', [PemetaanAkreditasiController::class, 'getTimelineAjax'])->name('timeline.ajax');
        Route::get('/calendar/ajax', [PemetaanAkreditasiController::class, 'getCalendarAjax'])->name('calendar.ajax');
        Route::get('/table/ajax', [PemetaanAkreditasiController::class, 'getTableAjax'])->name('table.ajax');
        Route::get('/export', [PemetaanAkreditasiController::class, 'export'])->name('export');
        Route::get('/{id}', [PemetaanAkreditasiController::class, 'show'])->name('show');
    });

    // PENGAJUAN AKREDITASI (DE)
    Route::prefix('de')->name('de.')->group(function () {
        Route::get('/pengajuan', [DEController::class, 'pengajuan'])->name('pengajuan');
    });

    // USER MANAGEMENT (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/users/export', [App\Http\Controllers\UserController::class, 'export'])->name('users.export');
        Route::get('/users/template', [App\Http\Controllers\UserController::class, 'downloadTemplate'])->name('users.template');
        Route::post('/users/import', [App\Http\Controllers\UserController::class, 'import'])->name('users.import');
        Route::resource('users', App\Http\Controllers\UserController::class);
        Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });

    // INDIKATOR MANAGEMENT (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::resource('kriteria', KriteriaController::class);
        Route::resource('elemen-standar', ElemenStandarController::class);
        Route::resource('indikator', IndikatorController::class);
    });

    // PENAWARAN ASESMEN
    Route::prefix('penawaran')->name('penawaran.')->group(function () {
        Route::get('/baru', [PenawaranController::class, 'baru'])->name('baru');
        Route::get('/riwayat', [PenawaranController::class, 'riwayat'])->name('riwayat');
        Route::get('/{id}', [PenawaranController::class, 'show'])->name('show');
        Route::post('/{id}/terima', [PenawaranController::class, 'terima'])->name('terima');
        Route::post('/{id}/tolak', [PenawaranController::class, 'tolak'])->name('tolak');
    });

    // PENUGASAN ASESMEN
    Route::prefix('penugasan')->name('penugasan.')->group(function () {
        Route::get('/aktif', [PenugasanController::class, 'aktif'])->name('aktif');
        Route::get('/selesai', [PenugasanController::class, 'selesai'])->name('selesai');
        Route::get('/riwayat', [PenugasanController::class, 'riwayat'])->name('riwayat');
        Route::get('/{id}', [PenugasanController::class, 'show'])->name('show');
        Route::post('/{id}/update-status', [PenugasanController::class, 'updateStatus'])->name('updateStatus');
    });

    // PROSES AK
    Route::prefix('ak')->name('ak.')->middleware(['auth'])->group(function () {

        Route::get('/berkas', [AKController::class, 'berkas'])->name('berkas');
        Route::get('/berkas/{id}', [AKController::class, 'showBerkas'])->name('berkas.show');
        Route::post('/berkas/{id}/nilai', [AKController::class, 'simpanNilai'])->name('berkas.nilai');

        Route::get('/split', [AKController::class, 'split'])->name('split');
        Route::get('/split/{id}', [AKController::class, 'showSplit'])->name('split.show');
        Route::post('/split/{id}/rekonsiliasi', [AKController::class, 'rekonsiliasi'])->name('split.rekonsiliasi');

        Route::get('/upload', [AKController::class, 'upload'])->name('upload');
        Route::post('/upload', [AKController::class, 'storeUpload'])->name('upload.store');
        Route::delete('/upload/{id}', [AKController::class, 'deleteUpload'])->name('upload.delete');

        Route::get('/validasi', [AKController::class, 'validasi'])->name('validasi');
        Route::get('/validasi/{id}', [AKController::class, 'showValidasi'])->name('validasi.show');
    });

    Route::resource('asesmen', AsesmenController::class);
    Route::resource('kriteria', KriteriaController::class);
    Route::resource('elemen', ElemenStandarController::class);
    Route::resource('jenis-indikator', JenisIndikatorController::class);
    Route::resource('indikator', IndikatorController::class);

    // Dashboard Overview
    Route::get('/asesmen/dashboard', [AsesmenController::class, 'dashboard'])
        ->name('asesmen.dashboard');

    // CRUD Assessment
    Route::resource('asesmen', AsesmenController::class);

    // Assignment Management (AJAX Endpoints)
    Route::post('/asesmen/{id}/assign-user', [AsesmenController::class, 'assignUser'])
        ->name('asesmen.assign-user');

    Route::post('/asesmen/{id}/bulk-assign', [AsesmenController::class, 'bulkAssign'])
        ->name('asesmen.bulk-assign');

    Route::post('/asesmen/{id}/update-role', [AsesmenController::class, 'updateUserRole'])
        ->name('asesmen.update-role');

    Route::delete('/asesmen/{id}/remove-user/{userId}', [AsesmenController::class, 'removeUser'])
        ->name('asesmen.remove-user');

    // Search Users (AJAX)
    Route::get('/asesmen/search-users', [AsesmenController::class, 'searchUsers'])
        ->name('asesmen.search-users');

    // PROSES AL
    Route::prefix('al')->name('al.')->group(function () {
        Route::get('/berkas', [ALController::class, 'berkas'])->name('berkas');
        Route::get('/jadwal', [ALController::class, 'jadwal'])->name('jadwal');
        Route::get('/jadwal/{id}', [ALController::class, 'showJadwal'])->name('jadwal.show');
        Route::post('/jadwal/{id}/konfirmasi', [ALController::class, 'konfirmasiJadwal'])->name('jadwal.konfirmasi');

        Route::prefix('/berkas/{id}/documents')->name('berkas.documents.')->group(function () {
            Route::get('/', [ALDocumentController::class, 'index'])->name('index');
            Route::post('/upload', [ALDocumentController::class, 'uploadDocument'])->name('upload');
            Route::get('/list', [ALDocumentController::class, 'getFiles'])->name('list');
            Route::get('/page', [ALDocumentController::class, 'page'])->name('berkas.page');
            Route::post('/finalize', [ALDocumentController::class, 'finalize'])->name('finalize');
            Route::post('/unfinalize', [ALDocumentController::class, 'unfinalize'])->name('unfinalize');

            Route::get('/upload', [ALController::class, 'upload'])->name('upload');
            Route::post('/upload', [ALController::class, 'storeUpload'])->name('upload.store');
            Route::delete('/upload/{id}', [ALController::class, 'deleteUpload'])->name('upload.delete');

            Route::get('/{docId}/download', [ALDocumentController::class, 'download'])->name('download');
            Route::delete('/{docId}', [ALDocumentController::class, 'destroy'])->name('delete');
        });
    });

    // ========== PRODI ROUTES - Pengajuan Akreditasi ==========
    Route::prefix('pengajuan')->name('pengajuan')->group(function () {
        Route::middleware(['auth', 'role:admin_prodi,admin_univ'])->group(function () {
            // List & CRUD
            Route::get('/', [PengajuanAkreditasiController::class, 'index']);
            Route::get('/create', [PengajuanAkreditasiController::class, 'create'])->name('.create');
            Route::post('/', [PengajuanAkreditasiController::class, 'store'])->name('.store');

            // Template Download (must be before /{id} to avoid conflict)
            Route::get('/template/download', [PengajuanAkreditasiController::class, 'downloadTemplateBorang'])->name('.template.download');

            // Show detail
            Route::get('/{id}', [PengajuanAkreditasiController::class, 'show'])->name('.show');

            // === Draft LED ===
            Route::post('/{id}/upload-draft', [PengajuanAkreditasiController::class, 'uploadDraftBorang'])->name('.upload-draft');
            Route::post('/{id}/process-borang', [PengajuanAkreditasiController::class, 'processBorangDOCX'])->name('.process-borang');

            Route::post('/{id}/borang-online/save', [PengajuanAkreditasiController::class, 'saveBorangOnline'])
                ->name('.borang-online.save');
            Route::post('/{id}/borang-online/save-field', [PengajuanAkreditasiController::class, 'saveBorangField'])
                ->name('.borang-online.save-field');
            Route::post('/{id}/borang-online/upload', [PengajuanAkreditasiController::class, 'uploadBorangFile'])
                ->name('.borang-online.upload');
            Route::post('/{id}/borang-online/save-all', [PengajuanAkreditasiController::class, 'saveBorangOnline'])
                ->name('.borang-online.save-all');
            Route::post('/{id}/borang-online/submit', [PengajuanAkreditasiController::class, 'submitBorangOnline'])->name('.submit');
            Route::get('/{id}/borang-preview', [PengajuanAkreditasiController::class, 'showBorangHTML'])->name('.borang-preview');

            // === Borang Online (Alternative) ===
            Route::get('/{id}/borang-online', [PengajuanAkreditasiController::class, 'showBorangOnline'])
                ->name('.borang-online');
            Route::get('/{id}/borang-online/data', [PengajuanAkreditasiController::class, 'getBorangData'])
                ->name('.borang-online.data');
            Route::get('/pengesahan/preview', [PengajuanAkreditasiController::class, 'previewLembarPengesahan'])->name('.pengesahan.preview');
            Route::post('/{id}/borang/import-docx', [PengajuanAkreditasiController::class, 'importBorangDocx'])
                ->name('.borang.import-docx');
            Route::get('/{id}/borang/import-status/{importId}', [PengajuanAkreditasiController::class, 'checkImportStatus'])
                ->name('.borang.import-status');
            Route::post('/{id}/borang/reset', [PengajuanAkreditasiController::class, 'resetBorang'])
                ->name('.borang.reset');
            Route::get('/{id}/borang/stats', [PengajuanAkreditasiController::class, 'getBorangStats'])
                ->name('.borang.stats');

            Route::get('/{id}/revision-notes', [PengajuanAkreditasiController::class, 'showRevisionNotes'])
                ->name('.revision-notes');
            Route::post('/{id}/submit-revision', [PengajuanAkreditasiController::class, 'submitRevision'])
                ->name('.submit-revision');
            Route::get('/{id}/revision-history', [PengajuanAkreditasiController::class, 'getRevisionHistory'])
                ->name('.revision-history');

            // === Pembayaran ===
            Route::post('/{id}/upload-pembayaran', [PengajuanAkreditasiController::class, 'uploadBuktiPembayaran'])->name('.upload-pembayaran');

            // === Borang Final ===
            Route::post('/{id}/upload-final', [PengajuanAkreditasiController::class, 'uploadBorangFinal'])->name('.upload-final');

            // ========================================
            // UPLOAD FILES (3 Jenis)
            // ========================================

            // Upload dokumen
            Route::post('/{id}/upload-pengesahan', [BorangUploadController::class, 'uploadPengesahan'])->name('.upload-pengesahan');
            Route::post('/{id}/upload-suplemen',   [BorangUploadController::class, 'uploadSuplemen'])->name('.upload-suplemen');
            Route::post('/{id}/upload-kualitatif', [BorangUploadController::class, 'uploadKualitatif']);
            Route::post('/{id}/upload-kuantitatif', [BorangUploadController::class, 'uploadKuantitatif'])->name('.upload-kuantitatif');

            // Dokumen action
            Route::get('/{id}/dokumen/{dokumenId}/download', [BorangUploadController::class, 'downloadDokumen'])->name('.download-dokumen');
            Route::delete('/{id}/dokumen/{dokumenId}',       [BorangUploadController::class, 'deleteDokumen'])->name('.delete-dokumen');

            // Borang online
            Route::get('/{id}/borang-online/check-files', [PengajuanAkreditasiController::class, 'checkBorangFiles'])
                ->name('.borang.check-files');

            Route::get('/{id}/validation-summary', [PengajuanBorangController::class, 'validationSummary'])
                ->name('.borang.validation-summary');

            Route::get('/{id}/validation-details', [PengajuanBorangController::class, 'validationDetails'])
                ->name('.borang.validation-details');
        });

        Route::middleware(['auth', 'role:admin_prodi,admin_univ,super_admin,asesi'])->group(function () {
            Route::get('/{id}/borang/download-template', [PengajuanAkreditasiController::class, 'downloadBorangTemplate'])
                ->name('.borang.download-template');
            Route::get('/{id}/borang/export-docx', [PengajuanAkreditasiController::class, 'exportBorangDocx'])
                ->name('.borang.export-docx');
        });
        // === Dokumen Download ===
        Route::get('/dokumen/{id}/download', [PengajuanAkreditasiController::class, 'downloadDokumen'])->name('.dokumen.download');
    });

    // ========== DE ROUTES - Desk Evaluator ==========
    Route::prefix('de')->name('de')->middleware(['auth', 'role:asesi,super_admin'])->group(function () {
        Route::prefix('pengajuan')->name('.pengajuan')->group(function () {
            // List & Show
            Route::get('/', [DeskEvaluatorController::class, 'index']);
            Route::get('/{id}', [DeskEvaluatorController::class, 'show'])->name('.show');

            // === Actions ===
            // Pengingat
            Route::post('/kirim-pengingat', [DeskEvaluatorController::class, 'kirimPengingat'])
                ->name('.kirim-pengingat');

            // Template LED
            Route::post('/{id}/kirim-borang', [DeskEvaluatorController::class, 'kirimFormBorang'])
                ->name('.kirim-borang');

            // View Parsed Borang (Read-only for DE)
            Route::get('/{pengajuanId}/borang/{importId}/view', [DeskEvaluatorController::class, 'viewBorangHTML'])
                ->name('.borang-view');

            // Review Kesiapan
            Route::post('/{id}/lapor-validasi', [DeskEvaluatorController::class, 'laporHasilValidasi'])->name('.lapor-validasi');

            Route::post('/{id}/kirim-formulir-pembayaran', [DeskEvaluatorController::class, 'kirimFormulirPembayaran'])
                ->name('.kirim-formulir-pembayaran');

            // Verifikasi Pembayaran
            Route::post('/{id}/verifikasi-pembayaran', [DeskEvaluatorController::class, 'verifikasiPembayaran'])
                ->name('.verifikasi-pembayaran');

            // Approve ke AK
            Route::post('/{id}/approve-ak', [DeskEvaluatorController::class, 'approveLanjutAK'])
                ->name('.approve-ak');

            Route::get('/{id}/assign-validator', [DeskEvaluatorController::class, 'showAssignValidatorForm'])
                ->name('.assign-validator.form');
            Route::post('/{id}/assign-validator', [DeskEvaluatorController::class, 'assignValidatorBorang'])
                ->name('.assign-validator');
            Route::post('/validation/{validationId}/reassign', [DeskEvaluatorController::class, 'reassignValidator'])
                ->name('.reassign-validator');
        });
    });

    Route::prefix('keuangan')
        ->name('keuangan.')
        ->group(function () {

            Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
                Route::get('/', [ValidasiPembayaranController::class, 'index'])->name('index');
                Route::get('/{id}', [ValidasiPembayaranController::class, 'show'])->name('show');

                Route::get('/{id}/download-bukti', [ValidasiPembayaranController::class, 'downloadBukti'])
                    ->name('download-bukti');

                Route::post('/{id}/verify', [ValidasiPembayaranController::class, 'verify'])
                    ->name('verify');
            });
        });

    // routes/web.php

    Route::middleware(['auth'])->prefix('validator')->name('validator.')->group(function () {
        Route::prefix('borang')->name('borang.')->group(function () {
            Route::get('/', [BorangValidatorController::class, 'index'])->name('index');
            Route::get('/{assignment}', [BorangValidatorController::class, 'show'])->name('show');

            // ✅ NEW: Auto-save review per item
            Route::post('/{assignment}/update-review', [BorangValidatorController::class, 'updateReview'])->name('update-review');

            Route::post('/{assignment}/submit', [BorangValidatorController::class, 'submit'])->name('submit');
            Route::get('/{assignment}/stats', [BorangValidatorController::class, 'getValidationStats'])->name('stats');

            // ✅ NEW: Accept/Reject offer
            Route::post('/{assignment}/respond-offer', [BorangValidatorController::class, 'respondOffer'])->name('respond-offer');
            Route::get('/{assignment}/download-template', [BorangValidatorController::class, 'downloadTemplate'])->name('download-template');
            Route::get('/{assignment}/download-review', [BorangValidatorController::class, 'downloadReview'])->name('download-review');
            Route::post('/{assignment}/upload-review', [BorangValidatorController::class, 'uploadReview'])->name('upload-review');
            Route::post('/{assignment}/reset-review', [BorangValidatorController::class, 'resetReview'])->name('reset-review');
            Route::post('/{assignment}/laporkan-validasi', [BorangValidatorController::class, 'laporkanValidasi'])->name('laporkan-validasi');
        });
    });

    // PANDUAN PENGGUNAAN DAISY
    Route::prefix('panduan')->group(function () {
        Route::get('/', [PanduanController::class, 'index'])->name('panduan.index');
        Route::get('/', [PanduanController::class, 'index'])->name('panduan'); // alias
        Route::get('/{slug}', [PanduanController::class, 'show'])->name('panduan.show');
    });

    // BANTUAN LAYANAN
    Route::prefix('bantuan')->group(function () {
        Route::get('/', [BantuanController::class, 'index'])->name('bantuan.index');
        Route::get('/', [BantuanController::class, 'index'])->name('bantuan'); // alias
        Route::post('/tiket', [BantuanController::class, 'createTicket'])->name('bantuan.tiket.create');
        Route::get('/tiket/{id}', [BantuanController::class, 'showTicket'])->name('bantuan.tiket.show');
        Route::post('/tiket/{id}/reply', [BantuanController::class, 'replyTicket'])->name('bantuan.tiket.reply');
    });

    // PROFIL & PENGATURAN
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::get('/', [ProfileController::class, 'index'])->name('profile'); // alias
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

        Route::get('/password', [ProfileController::class, 'passwordForm'])->name('profile.password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });

    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/', [SettingsController::class, 'index'])->name('settings'); // alias
        Route::put('/', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/notification', [SettingsController::class, 'updateNotification'])->name('settings.notification');
    });

    // AKTIVITAS & TUGAS
    Route::get('/aktivitas', [ActivityController::class, 'index'])->name('aktivitas');
    Route::get('/aktivitas/{id}', [ActivityController::class, 'show'])->name('aktivitas.show');

    Route::get('/tugas', [TaskController::class, 'index'])->name('tugas');
    Route::get('/tugas/{id}', [TaskController::class, 'show'])->name('tugas.show');
    Route::post('/tugas/{id}/complete', [TaskController::class, 'complete'])->name('tugas.complete');

    // LAPORAN
    Route::prefix('laporan')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/', [LaporanController::class, 'index'])->name('laporan'); // alias
        Route::get('/statistik', [LaporanController::class, 'statistik'])->name('laporan.statistik');
        Route::get('/kinerja', [LaporanController::class, 'kinerja'])->name('laporan.kinerja');
        Route::get('/export', [LaporanController::class, 'export'])->name('laporan.export');
    });

    // INDIKATOR MANAGEMENT (Kriteria, Elemen Standar, Indikator)
    Route::resource('kriteria', KriteriaController::class);
    Route::resource('elemen-standar', ElemenStandarController::class);
    Route::resource('indikator', IndikatorController::class);

    // MASTER DATA (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/master-data', function () {
            return redirect()->route('master-data.index', ['tab' => 'universities']);
        });
        Route::get('/master-data/{tab?}', [UniversityController::class, 'masterData'])->name('master-data.index');
        Route::resource('universities', UniversityController::class);
        Route::resource('study-programs', StudyProgramController::class);
        Route::resource('indikator-penilaian', IndikatorPenilaianElemenController::class);

        // BOBOT PENILAIAN
        Route::resource('bobot-penilaian', BobotPenilaianController::class);
        Route::post('/bobot-penilaian/{id}/toggle', [BobotPenilaianController::class, 'toggleActive'])
            ->name('bobot-penilaian.toggle');
        Route::get('/bobot-penilaian/hitung/{asesmenId}/{categoryId}', [BobotPenilaianController::class, 'calculate'])
            ->name('bobot-penilaian.calculate');
    });

    // NOTIFIKASI
    Route::prefix('notifications')->group(function () {
        Route::get('/', function () {
            return view('notifications.index');
        })->name('notifications.index');
        Route::get('/', function () {
            return view('notifications.index');
        })->name('notifications'); // alias
        Route::post('/{id}/read', function ($id) {
            return response()->json(['success' => true]);
        })->name('notifications.read');
        Route::post('/read-all', function () {
            return response()->json(['success' => true]);
        })->name('notifications.readAll');
    });
});
