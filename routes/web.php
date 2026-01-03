<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\{PasswordResetController, ProfileController};
use App\Http\Controllers\Prodi\{DeskEvaluatorController, PengajuanAkreditasiController, PemetaanAkreditasiController};
use App\Http\Controllers\Asesmen\{AsesmenController, AKController, ALController, PenawaranController, ValidasiController};
use App\Http\Controllers\Master\{ElemenStandarController, JenisIndikatorController, IndikatorController, IndikatorPenilaianElemenController, KriteriaController, UniversityController, StudyProgramController};
use App\Http\Controllers\{AuthController, BobotPenilaianController, DashboardController, PenugasanController, BandingController, PedomanController, DokumenController, PanduanController, BantuanController, SettingsController, ActivityController, TaskController, LaporanController, DegreeLevelController};


// Dashboard (awal)
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');
Route::get('/under-development', function () {
    return abort(503, '🚧 Fitur Sedang Dalam Pengembangan. Silakan kembali lagi nanti.');
})->name('under.development');
Route::get('/reload-captcha', function () {
    return response()->json([
        'captcha' => captcha_img('flat')
    ]);
});

// LOGIN / REGISTER
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    // Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // Route::post('/register', [AuthController::class, 'register'])->name('register.post');

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
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/select-role', [AuthController::class, 'showRoleSelection'])->name('select.role');
    Route::post('/select-role', [AuthController::class, 'selectRole'])->name('select.role.post');

    Route::middleware('admin')->group(function () {
        // USER MANAGEMENT (Admin Only)
        Route::resource('users', App\Http\Controllers\UserController::class);
        // INDIKATOR MANAGEMENT (Admin Only)
        Route::resource('kriteria', KriteriaController::class);
        Route::resource('elemen-standar', ElemenStandarController::class);
        Route::resource('indikator', IndikatorController::class);
    });

    // PEMETAAN AKREDITASI
    Route::prefix('pemetaan')->name('pemetaan.')->middleware(['auth'])->group(function () {
        Route::get('/', [PemetaanAkreditasiController::class, 'index'])->name('index');
        Route::get('/{id}', [PemetaanAkreditasiController::class, 'show'])->name('show');
        Route::get('/timeline/ajax', [PemetaanAkreditasiController::class, 'getTimelineAjax'])->name('timeline.ajax');
        Route::get('/calendar/ajax', [PemetaanAkreditasiController::class, 'getCalendarAjax'])->name('calendar.ajax');
        Route::get('/table/ajax', [PemetaanAkreditasiController::class, 'getTableAjax'])->name('table.ajax');
        Route::get('/export/excel', [PemetaanAkreditasiController::class, 'export'])->name('export');
    });

    // PENAWARAN ASESMEN
    Route::prefix('penawaran')->name('penawaran')->group(function () {
        Route::get('/', [PenawaranController::class, 'index']);
        Route::post('/{id}/accept', [PenawaranController::class, 'acceptPenawaran'])
            ->name('.accept');
        Route::post('/{id}/reject', [PenawaranController::class, 'rejectPenawaran'])
            ->name('.reject');
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
    Route::prefix('ak')->name('ak.')->group(function () {
        Route::get('/berkas', [AKController::class, 'berkas'])->name('berkas');
        Route::get('/berkas/{idAsesmen}/cek-penawaran', [PenawaranController::class, 'cekPenawaran'])->name('berkas.penawaran');
        Route::middleware('penawaran.accepted')->group(function () {
            Route::get('/berkas/{idAsesmen}', [AKController::class, 'showBerkas'])->name('berkas.show');
            Route::post('/berkas/{idAsesmen}/nilai', [AKController::class, 'simpanNilai'])->name('berkas.nilai');
        });
        Route::get('/berkas/{idAsesmen}/template', [AKController::class, 'downloadTemplate'])
            ->name('berkas.template');
        Route::get('/berkas/{idAsesmen}/export', [AKController::class, 'exportExcel'])
            ->name('berkas.export');
        Route::post('/berkas/{idAsesmen}/import', [AKController::class, 'importExcel'])
            ->name('berkas.import');
        Route::get('/import-status/{idAsesmen}', [AKController::class, 'checkImportStatus'])
            ->name('import.status');
        Route::post('/berkas/{idAsesmen}/submit', [AKController::class, 'submitPenilaian'])
            ->name('berkas.submit');
        Route::post('/berkas/{idAsesmen}/unsubmit', [AKController::class, 'unsubmitPenilaian'])
            ->name('berkas.unsubmit');
        Route::get('/berkas/{idAsesmen}/import-history', [AKController::class, 'importHistory'])
            ->name('berkas.import-history');
        Route::delete('/berkas/{idAsesmen}/reset-all', [AKController::class, 'resetAllPenilaian'])
            ->name('berkas.reset-all');

        Route::get('/berkas/{asesmen}/comparison-data', [AKController::class, 'getComparisonData'])->name('berkas.comparison-data');

        Route::prefix('validasi')->name('validasi.')->group(function () {
            Route::middleware(['auth', 'role:validator'])->group(function () {
                Route::get('/', [ValidasiController::class, 'index'])->name('index');
                Route::middleware('penawaran.accepted')->group(function () {
                    Route::get('/{idAsesmen}/{jenisAsesmen?}', [ValidasiController::class, 'asesor'])->name('asesor')->where('jenisAsesmen', 'ak|al');
                    Route::get('/{asesmen}/detail/{elemen}', [ValidasiController::class, 'getValidasiDetail'])->name('detail');
                });
            });
            Route::get('/{asesmen}/asesor', [ValidasiController::class, 'showAsesorComparison'])
                ->name('asesor.comparison');
            Route::get('/{asesmen}/elemen/{elemen}', [ValidasiController::class, 'getElemenDetail'])
                ->name('elemen.detail');
            Route::get('/{asesmen}/export-comparison', [ValidasiController::class, 'exportComparison'])
                ->name('export.comparison');
            Route::post('/{asesmen}/elemen/{elemen}/validate', [ValidasiController::class, 'validateElemen'])
                ->name('elemen.validate');
            Route::post('/{asesmen}/validate-agreed', [ValidasiController::class, 'validateAllAgreed'])
                ->name('validate-agreed');
            Route::post('/{asesmen}/asesor/approve', [ValidasiController::class, 'approveAllPenilaian'])
                ->name('asesor.approve');
        });
    });

    Route::prefix('al')->name('al.')->group(function () {
        Route::get('/berkas', [ALController::class, 'berkas'])->name('berkas');
        Route::get('/berkas/{idAsesmen}/cek-penawaran', [PenawaranController::class, 'cekPenawaran'])->name('berkas.penawaran');
        Route::middleware('penawaran.accepted')->group(function () {
            Route::get('/berkas/{idAsesmen}', [ALController::class, 'showBerkas'])->name('berkas.show');
            Route::post('/berkas/{idAsesmen}/nilai', [ALController::class, 'simpanNilai'])->name('berkas.nilai');
        });
        Route::get('/berkas/{idAsesmen}/template', [ALController::class, 'downloadTemplate'])
            ->name('berkas.template');
        Route::get('/berkas/{idAsesmen}/export', [ALController::class, 'exportExcel'])
            ->name('berkas.export');
        Route::post('/berkas/{idAsesmen}/import', [ALController::class, 'importExcel'])
            ->name('berkas.import');
        Route::get('/import-status/{idAsesmen}', [ALController::class, 'checkImportStatus'])
            ->name('import.status');
        Route::post('/berkas/{idAsesmen}/submit', [ALController::class, 'submitPenilaian'])
            ->name('berkas.submit');
        Route::post('/berkas/{idAsesmen}/unsubmit', [ALController::class, 'unsubmitPenilaian'])
            ->name('berkas.unsubmit');
        Route::get('/berkas/{idAsesmen}/import-history', [ALController::class, 'importHistory'])
            ->name('berkas.import-history');
        Route::delete('/berkas/{idAsesmen}/reset-all', [ALController::class, 'resetAllPenilaian'])
            ->name('berkas.reset-all');

        Route::get('/berkas/{asesmen}/comparison-data', [AKController::class, 'getComparisonData'])->name('berkas.comparison-data');
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

            // === Draft Borang ===
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

            // === Pembayaran ===
            Route::post('/{id}/upload-pembayaran', [PengajuanAkreditasiController::class, 'uploadBuktiPembayaran'])->name('.upload-pembayaran');

            // === Borang Final ===
            Route::post('/{id}/upload-final', [PengajuanAkreditasiController::class, 'uploadBorangFinal'])->name('.upload-final');

            // === Dokumen Download ===
            Route::get('/dokumen/{id}/download', [PengajuanAkreditasiController::class, 'downloadDokumen'])->name('.dokumen.download');
        });

        Route::middleware(['auth', 'role:admin_prodi,admin_univ,super_admin,asesi'])->group(function () {
            Route::get('/{id}/borang/download-template', [PengajuanAkreditasiController::class, 'downloadBorangTemplate'])
                ->name('.borang.download-template');
            Route::get('/{id}/borang/export-docx', [PengajuanAkreditasiController::class, 'exportBorangDocx'])
                ->name('.borang.export-docx');
        });
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

            // Borang Template
            Route::post('/{id}/kirim-borang', [DeskEvaluatorController::class, 'kirimFormBorang'])
                ->name('.kirim-borang');

            // View Parsed Borang (Read-only for DE)
            Route::get('/{pengajuanId}/borang/{importId}/view', [DeskEvaluatorController::class, 'viewBorangHTML'])
                ->name('.borang-view');

            // Review Kesiapan
            Route::post('/{id}/review', [DeskEvaluatorController::class, 'reviewKesiapan'])
                ->name('.review');

            // Verifikasi Pembayaran
            Route::post('/{id}/verifikasi-pembayaran', [DeskEvaluatorController::class, 'verifikasiPembayaran'])
                ->name('.verifikasi-pembayaran');

            // Approve ke AK
            Route::post('/{id}/approve-ak', [DeskEvaluatorController::class, 'approveLanjutAK'])
                ->name('.approve-ak');
        });
    });

    Route::middleware(['auth', 'role:super_admin,asesi'])->group(function () {
        Route::resource('asesmen', AsesmenController::class);
        Route::resource('kriteria', KriteriaController::class);
        Route::resource('elemen', ElemenStandarController::class);
        Route::resource('jenis-indikator', JenisIndikatorController::class);
        Route::resource('indikator', IndikatorController::class);

        // Dashboard Overview
        Route::prefix('asesmen')->name('asesmen')->group(function () {
            Route::get('/dashboard', [AsesmenController::class, 'dashboard'])->name('.dashboard');
            // Assignment Management (AJAX Endpoints)
            Route::post('/{id}/assign-user', [AsesmenController::class, 'assignUser'])->name('.assign-user');
            Route::post('/{id}/bulk-assign', [AsesmenController::class, 'bulkAssign'])->name('.bulk-assign');
            Route::post('/{id}/reassign-user', [AsesmenController::class, 'reassignUser'])->name('.reassign-user');
            Route::post('/{id}/update-role', [AsesmenController::class, 'updateUserRole'])->name('.update-role');
            Route::delete('/{id}/remove-user/{userId}', [AsesmenController::class, 'removeUser'])->name('.remove-user');
            // Search Users (AJAX)
            Route::get('/search-users', [AsesmenController::class, 'searchUsers'])->name('.search-users');
            Route::post('/{id}/send-documents', [AsesmenController::class, 'sendDocuments'])->name('.send-documents');

            Route::get('/{id}/requirements/{jenisAsesmen}', [AsesmenController::class, 'getRequirementsStatus'])->name('.requirements')->where('jenisAsesmen', 'ak|al');
            Route::get('/{id}/rejected/{jenisAsesmen}', [AsesmenController::class, 'getRejectedAssignments'])->name('.rejected')->where('jenisAsesmen', 'ak|al');
            Route::get('/{id}/assignments/{jenisAsesmen}', [AsesmenController::class, 'getAssignments'])->name('.assignments')->where('jenisAsesmen', 'ak|al');
        });
    });

    Route::middleware('under.dev')->group(function () {
        // PENUGASAN BANDING
        Route::prefix('banding')->group(function () {
            Route::get('/', [BandingController::class, 'index'])
                ->name('banding.index'); // nama resmi
            Route::get('/', [BandingController::class, 'index'])
                ->name('banding'); // alias tanpa .index
            Route::get('/{id}', [BandingController::class, 'show'])->name('banding.show');
            Route::post('/{id}/terima', [BandingController::class, 'terima'])->name('banding.terima');
            Route::post('/{id}/tolak', [BandingController::class, 'tolak'])->name('banding.tolak');
            Route::post('/{id}/submit', [BandingController::class, 'submit'])->name('banding.submit');
        });

        // PEDOMAN AK
        Route::prefix('pedoman')->group(function () {
            Route::get('/', [PedomanController::class, 'index'])->name('pedoman.index');
            Route::get('/', [PedomanController::class, 'index'])->name('pedoman'); // alias
            Route::get('/{kategori}', [PedomanController::class, 'kategori'])->name('pedoman.kategori');
            Route::get('/{kategori}/{id}/download', [PedomanController::class, 'download'])->name('pedoman.download');
        });

        // DOKUMEN ADMINISTRASI AL
        Route::prefix('dokumen')->name('dokumen.')->group(function () {
            Route::get('/panduan', [DokumenController::class, 'panduan'])->name('panduan');
            Route::get('/panduan/{id}/download', [DokumenController::class, 'downloadPanduan'])->name('panduan.download');

            Route::get('/instrumen', [DokumenController::class, 'instrumen'])->name('instrumen');
            Route::get('/instrumen/{id}/download', [DokumenController::class, 'downloadInstrumen'])->name('instrumen.download');

            Route::get('/template', [DokumenController::class, 'template'])->name('template');
            Route::get('/template/{id}/download', [DokumenController::class, 'downloadTemplate'])->name('template.download');

            Route::get('/surat', [DokumenController::class, 'surat'])->name('surat');
            Route::get('/surat/{id}/download', [DokumenController::class, 'downloadSurat'])->name('surat.download');
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
    });

    // PROFIL & PENGATURAN
    Route::prefix('profile')->name('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('.index');
        Route::get('/', [ProfileController::class, 'index']); // alias
        Route::put('/', [ProfileController::class, 'update'])->name('.update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('.avatar');

        Route::get('/password', [ProfileController::class, 'passwordForm'])->name('.password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('.password.update');
        Route::post('/switch-role', [ProfileController::class, 'switchRole'])->name('.switch-role');
        Route::get('/available-roles', [ProfileController::class, 'getAvailableRoles'])->name('.available-roles');
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

Route::get('clearcache', function () {
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    Illuminate\Support\Facades\Artisan::call('route:clear');
    Illuminate\Support\Facades\Artisan::call('view:clear');
    Illuminate\Support\Facades\Artisan::call('config:clear');
    Illuminate\Support\Facades\Artisan::call('config:cache');
});
