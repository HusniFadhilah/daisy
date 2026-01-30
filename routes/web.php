<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DE\PaymentSummaryController;
use App\Http\Controllers\Prodi\PenerimaanProdiController;
use App\Http\Controllers\Profile\{PasswordResetController, ProfileController, ProdiDataController};
use App\Http\Controllers\Prodi\{DeskEvaluatorController, PengajuanAkreditasiController, PemetaanAkreditasiController, PengajuanBorangController, BorangUploadController};
use App\Http\Controllers\Master\{ElemenStandarController, JenisIndikatorController, IndikatorController, IndikatorPenilaianElemenController, KriteriaController, UniversityController, StudyProgramController};
use App\Http\Controllers\Asesmen\{AsesmenController, AKController, ALController, ALDocumentController, BorangValidatorController, HasilAkreditasiController, PenawaranController, PelaporanController, ValidasiController};
use App\Http\Controllers\{AuthController, BobotPenilaianController, DashboardController, PenugasanController, BandingController, PedomanController, DokumenController, PanduanController, BantuanController, SettingsController, ActivityController, TaskController, LaporanController, TinyMceImageController, UserController};
use App\Http\Controllers\DE\{ValidasiAKController, MasaSanggahController, PelaporanAKController, PelaporanALController, PenugasanAKController, PenugasanALController, PelaksanaanALController, SuratPermohonanController, ValidasiDokumenController, PelaporanBandingController, PelaporanDokumenController, PenerimaanDokumenController, PelaksanaanBandingController, ValidasiPembayaranController, FormulirPembayaranController, PenyampaianTemplateController, PelaporanHasilAkreditasiController, PenerimaanPermohonanController, PenetapanHasilAkreditasiController, PenyampaianHasilAkreditasiController, PenyimpananArsipAkreditasiController};


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

    // ===== TESTING ROUTES (NO AUTH) =====
    // Route khusus untuk testing di Postman tanpa authentication
    Route::prefix('api/test')->name('api.test.')->group(function () {
        Route::get('/pemetaan/stats', [PemetaanAkreditasiController::class, 'getStatsForTesting'])->name('pemetaan.stats');
        Route::get('/pemetaan/timeline/{periode?}', [PemetaanAkreditasiController::class, 'getTimelineForTesting'])->name('pemetaan.timeline');
        Route::get('/pemetaan/calendar', [PemetaanAkreditasiController::class, 'getCalendarForTesting'])->name('pemetaan.calendar');
        Route::get('/pemetaan/programs', [PemetaanAkreditasiController::class, 'getProgramsForTesting'])->name('pemetaan.programs');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/select-role', [AuthController::class, 'showRoleSelection'])->name('select.role');
    Route::post('/select-role', [AuthController::class, 'selectRole'])->name('select.role.post');

    // CHANGE PASSWORD FIRST TIME
    Route::get('/change-password-first', [AuthController::class, 'showChangePasswordFirst'])->name('change.password.first');
    Route::post('/change-password-first', [AuthController::class, 'changePasswordFirst'])->name('change.password.first.post');
    Route::post('/change-password-skip', [AuthController::class, 'skipChangePassword'])->name('change.password.skip');

    Route::post('/tinymce/{folder}/{id}/image-upload', [TinyMceImageController::class, 'upload'])->name('tinymce.image.upload');
    Route::delete('/tinymce/{folder}/{id}/image-delete', [TinyMceImageController::class, 'delete'])->name('tinymce.image.delete');

    // PENAWARAN ASESMEN
    Route::prefix('penawaran')->name('penawaran')->group(function () {
        Route::get('/', [PenawaranController::class, 'index']);
        Route::get('/{idAsesmen}/berkas/{jenisAsesmen}/cek-penawaran', [PenawaranController::class, 'cekPenawaran'])->name('.berkas.cekPenawaran');
        Route::get('/{token}', [PenawaranController::class, 'show'])->name('.show');
        Route::post('/{token}/accept', [PenawaranController::class, 'acceptPenawaran'])
            ->name('.accept');
        Route::post('/{token}/reject', [PenawaranController::class, 'rejectPenawaran'])
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
        Route::middleware('penawaran.accepted:ak')->group(function () {
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
            Route::middleware(['role:validator'])->group(function () {
                Route::get('/', [ValidasiController::class, 'index'])->name('index');
                Route::middleware('penawaran.accepted:ak')->group(function () {
                    Route::get('/{idAsesmen}/{jenisAsesmen?}', [ValidasiController::class, 'asesor'])->name('asesor')->where('jenisAsesmen', 'ak|al');
                    Route::get('/{asesmen}/detail/{elemen}', [ValidasiController::class, 'getValidasiDetail'])->name('detail');
                });
            });
            Route::get('/{asesmen}/asesor', [ValidasiController::class, 'showAsesorComparison'])->name('asesor.comparison');
            Route::get('/{asesmen}/elemen/{elemen}', [ValidasiController::class, 'getElemenDetail'])->name('elemen.detail');
            Route::get('/{asesmen}/export-comparison', [ValidasiController::class, 'exportComparison'])->name('export.comparison');
            Route::post('/{asesmen}/elemen/{elemen}/validate', [ValidasiController::class, 'validateElemen'])->name('elemen.validate');
            Route::post('/{asesmen}/validate-agreed', [ValidasiController::class, 'validateAllAgreed'])->name('validate-agreed');
            Route::post('/{asesmen}/asesor/approve', [ValidasiController::class, 'approveAllPenilaian'])->name('asesor.approve');
        });
    });

    Route::prefix('al')->name('al.')->group(function () {
        Route::get('/berkas', [ALController::class, 'berkas'])->name('berkas');
        Route::middleware('penawaran.accepted:al')->group(function () {
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
        Route::get('/berkas/{asesmen}/comparison-data', [ALController::class, 'getComparisonData'])->name('berkas.comparison-data');
        Route::get('/berkas/{id}/laporan-pdf', [ALController::class, 'exportLaporanPdf'])->name('berkas.laporanPdf');

        Route::prefix('/berkas/{id}/documents')->name('berkas.documents.')->group(function () {
            Route::get('/', [ALDocumentController::class, 'index'])->name('index');
            Route::post('/upload', [ALDocumentController::class, 'uploadDocument'])->name('upload');
            Route::get('/list', [ALDocumentController::class, 'getFiles'])->name('list');
            Route::get('/page', [ALDocumentController::class, 'page'])->name('berkas.page');
            Route::post('/finalize', [ALDocumentController::class, 'finalize'])->name('finalize');
            Route::post('/unfinalize', [ALDocumentController::class, 'unfinalize'])->name('unfinalize');

            Route::patch('/reorder', [ALDocumentController::class, 'reorder'])->name('reorder');
            Route::patch('/{docId}/toggle', [ALDocumentController::class, 'toggleActive'])->name('toggle');

            Route::get('/{docId}/download', [ALDocumentController::class, 'download'])->name('download');
            Route::delete('/{docId}', [ALDocumentController::class, 'destroy'])->name('delete');
        });
    });

    // ========== PRODI ROUTES - Permohonan Akreditasi ==========
    Route::prefix('permohonan-akreditasi')->name('pengajuan')->group(function () {
        Route::middleware(['role:admin_prodi,admin_univ'])->group(function () {
            // List & CRUD
            Route::get('/', [PengajuanAkreditasiController::class, 'index']);
            Route::get('/create', [PengajuanAkreditasiController::class, 'create'])->name('.create');
            Route::post('/', [PengajuanAkreditasiController::class, 'store'])->name('.store');
            Route::post('/respond-pengingat/{pengingat}', [PengajuanAkreditasiController::class, 'respondPengingat'])->name('.respond-pengingat');
            // Template Download (must be before /{id} to avoid conflict)
            Route::get('/template/download', [PengajuanAkreditasiController::class, 'downloadTemplateBorang'])->name('.template.download');

            // Show detail
            Route::get('/{id}', [PengajuanAkreditasiController::class, 'show'])->name('.show');

            // === Draft LED ===
            Route::post('/{id}/upload-draft', [PengajuanAkreditasiController::class, 'uploadDraftBorang'])->name('.upload-draft');
            Route::post('/{id}/process-borang', [PengajuanAkreditasiController::class, 'processBorangDOCX'])->name('.process-borang');

            Route::post('/{id}/borang-online/save', [PengajuanAkreditasiController::class, 'saveBorangOnline'])->name('.borang-online.save');
            Route::post('/{id}/borang-online/save-field', [PengajuanAkreditasiController::class, 'saveBorangField'])->name('.borang-online.save-field');
            Route::post('/{id}/borang-online/upload', [PengajuanAkreditasiController::class, 'uploadBorangFile'])->name('.borang-online.upload');
            Route::post('/{id}/borang-online/save-all', [PengajuanAkreditasiController::class, 'saveBorangOnline'])->name('.borang-online.save-all');
            Route::post('/{id}/borang-online/submit', [PengajuanAkreditasiController::class, 'submitBorangOnline'])->name('.submit');
            Route::post('/{id}/borang-online/unsubmit', [PengajuanAkreditasiController::class, 'unsubmitBorangOnline'])->name('.unsubmit');
            Route::get('/{id}/borang-preview', [PengajuanAkreditasiController::class, 'showBorangHTML'])->name('.borang-preview');

            // === Borang Online (Alternative) ===
            Route::get('/{id}/borang-online', [PengajuanAkreditasiController::class, 'showBorangOnline'])->name('.borang-online');
            Route::get('/{id}/borang-online/data', [PengajuanAkreditasiController::class, 'getBorangData'])->name('.borang-online.data');
            Route::get('/pengesahan/preview', [PengajuanAkreditasiController::class, 'previewLembarPengesahan'])->name('.pengesahan.preview');
            Route::post('/{id}/borang/import-docx', [PengajuanAkreditasiController::class, 'importBorangDocx'])->name('.borang.import-docx');
            Route::get('/{id}/borang/import-status/{importId}', [PengajuanAkreditasiController::class, 'checkImportStatus'])->name('.borang.import-status');
            Route::post('/{id}/borang/reset', [PengajuanAkreditasiController::class, 'resetBorang'])->name('.borang.reset');
            Route::get('/{id}/borang/stats', [PengajuanAkreditasiController::class, 'getBorangStats'])->name('.borang.stats');

            Route::get('/{id}/revision-notes', [PengajuanAkreditasiController::class, 'showRevisionNotes'])->name('.revision-notes');
            Route::post('/{id}/submit-revision', [PengajuanAkreditasiController::class, 'submitRevision'])->name('.submit-revision');
            Route::get('/{id}/revision-history', [PengajuanAkreditasiController::class, 'getRevisionHistory'])->name('.revision-history');

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
            Route::get('/{id}/borang-online/check-files', [PengajuanAkreditasiController::class, 'checkBorangFiles'])->name('.borang.check-files');
            Route::get('/{pengajuan}/validation-summary', [PengajuanBorangController::class, 'validationSummary'])->name('.borang.validation-summary');
            Route::get('/{id}/validation-details', [PengajuanBorangController::class, 'validationDetails'])->name('.borang.validation-details');
        });

        Route::middleware(['role:admin_prodi,admin_univ,super_admin,asesi'])->group(function () {
            Route::get('/{id}/borang/download-template', [PengajuanAkreditasiController::class, 'downloadBorangTemplate'])->name('.borang.download-template');
            Route::get('/{id}/borang/export-docx', [PengajuanAkreditasiController::class, 'exportBorangDocx'])->name('.borang.export-docx');
        });
        // === Dokumen Download ===
        Route::get('/dokumen/{id}/download', [PengajuanAkreditasiController::class, 'downloadDokumen'])->name('.dokumen.download');
    });

    Route::prefix('prodi')->name('prodi')->middleware(['role:admin_prodi'])->group(function () {
        // ✅ NEW: Penerimaan Permohonan (Surat Penerimaan dari DE)
        Route::prefix('penerimaan-permohonan')->name('.penerimaan-permohonan')->group(function () {
            Route::get('/', [PenerimaanProdiController::class, 'index']);
            Route::get('/{id}', [PenerimaanProdiController::class, 'show'])->name('.show');
            Route::get('/{id}/download', [PenerimaanProdiController::class, 'download'])->name('.download');
            Route::get('/{id}/preview', [PenerimaanProdiController::class, 'preview'])->name('.preview');
        });
    });

    // ========== DE ROUTES - Desk Evaluator ==========
    Route::prefix('de')->name('de')->middleware(['role:asesi,super_admin'])->group(function () {
        Route::prefix('permohonan-akreditasi')->name('.pengajuan')->group(function () {
            // List & Show
            Route::get('/', [DeskEvaluatorController::class, 'index']);
            Route::get('/{id}', [DeskEvaluatorController::class, 'show'])->name('.show');
            Route::delete('/{id}', [DeskEvaluatorController::class, 'destroy'])->name('.destroy');

            // === Actions ===
            // Pengingat
            Route::post('/kirim-pengingat', [DeskEvaluatorController::class, 'kirimPengingat'])->name('.kirim-pengingat');

            // Template LED
            Route::post('/{id}/kirim-borang', [DeskEvaluatorController::class, 'kirimFormBorang'])->name('.kirim-borang');

            // View Parsed Borang (Read-only for DE)
            Route::get('/{pengajuanId}/borang/{importId}/view', [DeskEvaluatorController::class, 'viewBorangHTML'])->name('.borang-view');

            // Review Kesiapan
            Route::post('/{id}/lapor-validasi', [DeskEvaluatorController::class, 'laporHasilValidasi'])->name('.lapor-validasi');

            Route::post('/{id}/kirim-formulir-pembayaran', [DeskEvaluatorController::class, 'kirimFormulirPembayaran'])->name('.kirim-formulir-pembayaran');

            // Verifikasi Pembayaran
            Route::post('/{id}/verifikasi-pembayaran', [DeskEvaluatorController::class, 'verifikasiPembayaran'])->name('.verifikasi-pembayaran');

            // Approve ke AK
            Route::post('/{id}/approve-ak', [DeskEvaluatorController::class, 'approveLanjutAK'])->name('.approve-ak');

            Route::get('/{id}/assign-validator', [DeskEvaluatorController::class, 'showAssignValidatorForm'])->name('.assign-validator.form');
            Route::post('/{id}/assign-validator', [DeskEvaluatorController::class, 'assignValidatorBorang'])->name('.assign-validator');
            Route::post('/validation/{validationId}/reassign', [DeskEvaluatorController::class, 'reassignValidator'])->name('.reassign-validator');
        });
    });

    Route::prefix('de')->name('de')->middleware(['role:asesi,super_admin'])->group(function () {
        // PEMETAAN AKREDITASI
        Route::prefix('pengingat-masa-akreditasi')->name('.pemetaan.')->group(function () {
            Route::get('/', [PemetaanAkreditasiController::class, 'index'])->name('index');
            Route::get('/datatable/ajax', [PemetaanAkreditasiController::class, 'getDataTableAjax'])->name('datatable.ajax');
            Route::get('/urgent/ajax', [PemetaanAkreditasiController::class, 'getUrgentProgramsAjax'])->name('urgent.ajax');
            Route::get('/timeline/ajax', [PemetaanAkreditasiController::class, 'getTimelineAjax'])->name('timeline.ajax');
            Route::get('/calendar/ajax', [PemetaanAkreditasiController::class, 'getCalendarAjax'])->name('calendar.ajax');
            Route::get('/table/ajax', [PemetaanAkreditasiController::class, 'getTableAjax'])->name('table.ajax');
            Route::get('/reminder-detail/ajax', [PemetaanAkreditasiController::class, 'getReminderDetailAjax'])->name('reminder.detail.ajax');
            Route::get('/export/excel', [PemetaanAkreditasiController::class, 'export'])->name('export');
            Route::get('/prodi/search', [PemetaanAkreditasiController::class, 'searchProdiAjax'])->name('prodi.search.ajax');
            Route::get('/export/excel', [PemetaanAkreditasiController::class, 'export'])->name('export');
            Route::get('/{id}', [PemetaanAkreditasiController::class, 'show'])->name('show');
        });
        Route::prefix('surat-permohonan')->name('.surat-permohonan')->group(function () {
            Route::get('/', [SuratPermohonanController::class, 'index']);
            Route::get('/{id}', [SuratPermohonanController::class, 'show'])->name('.show');
            Route::post('/{id}/terima', [SuratPermohonanController::class, 'terima'])->name('.terima');
            Route::post('/{id}/tolak', [SuratPermohonanController::class, 'tolak'])->name('.tolak');
            Route::get('/{id}/download', [SuratPermohonanController::class, 'download'])->name('.download');
        });
        Route::prefix('penerimaan-permohonan')->name('.penerimaan-permohonan')->group(function () {
            Route::get('/', [PenerimaanPermohonanController::class, 'index']);
            Route::get('/{id}', [PenerimaanPermohonanController::class, 'show'])->name('.show');
            Route::post('/{id}/kirim', [PenerimaanPermohonanController::class, 'kirimSuratPenerimaan'])->name('.kirim');
            Route::get('/{id}/download', [PenerimaanPermohonanController::class, 'download'])->name('.download');
            Route::delete('/{id}', [PenerimaanPermohonanController::class, 'destroy'])->name('.destroy');
        });
        Route::prefix('penyampaian-template')->name('.penyampaian-template')->group(function () {
            Route::get('/', [PenyampaianTemplateController::class, 'index']);
            Route::get('/{id}', [PenyampaianTemplateController::class, 'show'])->name('.show');
            Route::post('/{id}/kirim-link', [PenyampaianTemplateController::class, 'kirimTemplateLink'])->name('.kirim-link');
            Route::post('/{id}/kirim-upload', [PenyampaianTemplateController::class, 'kirimTemplateUpload'])->name('.kirim-upload');
            Route::get('/{id}/download', [PenyampaianTemplateController::class, 'download'])->name('.download');
        });
        Route::prefix('validasi-pembayaran')->name('.validasi-pembayaran')->group(function () {
            Route::get('/', [ValidasiPembayaranController::class, 'index']);
            Route::get('/{id}', [ValidasiPembayaranController::class, 'show'])->name('.show');
            Route::post('/kirim-invoice', [ValidasiPembayaranController::class, 'kirimInvoice'])->name('.kirim-invoice');
            Route::put('/{id}/validasi', [ValidasiPembayaranController::class, 'validasi'])->name('.validasi');
        });
        Route::prefix('penerimaan-dokumen')->name('.penerimaan-dokumen')->group(function () {
            Route::get('/', [PenerimaanDokumenController::class, 'index']);
            Route::get('/{id}', [PenerimaanDokumenController::class, 'show'])->name('.show');
            Route::post('/kirim-reminder', [PenerimaanDokumenController::class, 'kirimReminder'])->name('.kirim-reminder');
            Route::get('/{id}/assign-validator', [PenerimaanDokumenController::class, 'showAssignValidatorForm'])->name('.assign-validator.form');
            Route::post('/{id}/assign-validator', [PenerimaanDokumenController::class, 'assignValidator'])->name('.assign-validator');
            Route::delete('/{id}/cancel-validator', [PenerimaanDokumenController::class, 'cancelValidator'])->name('.cancel-validator');
        });
        Route::prefix('validasi-dokumen')->name('.validasi-dokumen')->group(function () {
            Route::get('/', [ValidasiDokumenController::class, 'index']);
            Route::get('/{id}', [ValidasiDokumenController::class, 'show'])->name('.show');
            Route::post('/kirim-reminder', [ValidasiDokumenController::class, 'kirimReminder'])->name('.kirim-reminder');
        });
        Route::prefix('pelaporan-dokumen')->name('.pelaporan-dokumen')->group(function () {
            Route::get('/', [PelaporanDokumenController::class, 'index']);
            Route::get('/{id}', [PelaporanDokumenController::class, 'show'])->name('.show');
            Route::get('/table/ajax', [PelaporanDokumenController::class, 'getTableAjax'])->name('.table.ajax');
        });
        Route::prefix('penugasan-ak')->name('.penugasan-ak')->group(function () {
            Route::get('/', [\App\Http\Controllers\DE\PenugasanAKController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\DE\PenugasanAKController::class, 'show'])->name('.show');
            Route::post('/{id}/mark-ready', [\App\Http\Controllers\DE\PenugasanAKController::class, 'markReadyForAK'])->name('.mark-ready');
            Route::post('/{id}/assign-user', [\App\Http\Controllers\DE\PenugasanAKController::class, 'assignUser'])->name('.assign-user');
            Route::delete('/{id}/remove-user/{userId}', [\App\Http\Controllers\DE\PenugasanAKController::class, 'removeUser'])->name('.remove-user');
            Route::get('/{id}/assignments', [\App\Http\Controllers\DE\PenugasanAKController::class, 'getAssignments'])->name('.assignments');
            Route::get('/{id}/requirements', [\App\Http\Controllers\DE\PenugasanAKController::class, 'getRequirementsStatusAjax'])->name('.requirements');
        });
        Route::prefix('validasi-ak')->name('.validasi-ak')->group(function () {
            Route::get('/', [ValidasiAKController::class, 'index']);
            Route::get('/{id}', [ValidasiAKController::class, 'show'])->name('.show');
            Route::get('/{id}/timeline', [ValidasiAKController::class, 'getValidationTimeline'])->name('.timeline');
        });
        Route::prefix('pelaporan-ak')->name('.pelaporan-ak')->group(function () {
            Route::get('/', [PelaporanAKController::class, 'index']);
            Route::get('/{id}', [PelaporanAKController::class, 'show'])->name('.show');
            Route::get('/{id}/timeline', [PelaporanAKController::class, 'getTimeline'])->name('.timeline');
            Route::get('/{id}/document/{documentId}', [PelaporanAKController::class, 'previewDocument'])->name('.document.preview');
        });
        Route::prefix('penugasan-al')->name('.penugasan-al')->group(function () {
            Route::get('/', [PenugasanALController::class, 'index']);
            Route::get('/{id}', [PenugasanALController::class, 'show'])->name('.show');
            Route::post('/{id}/assign-asesor', [PenugasanALController::class, 'assignAsesor'])->name('.assign-asesor');
            Route::delete('/{id}/remove-asesor/{userId}', [PenugasanALController::class, 'removeAsesor'])->name('.remove-asesor');
            Route::post('/{id}/update-schedule', [PenugasanALController::class, 'updateSchedule'])->name('.update-schedule');
        });
        Route::prefix('pelaksanaan-al')->name('.pelaksanaan-al')->group(function () {
            Route::get('/', [PelaksanaanALController::class, 'index']);
            Route::get('/{id}', [PelaksanaanALController::class, 'show'])->name('.show');
            Route::post('/{id}/assign-validator', [PelaksanaanALController::class, 'assignValidator'])->name('.assign-validator');
            Route::delete('/{id}/remove-validator/{userId}', [PelaksanaanALController::class, 'removeValidator'])->name('.remove-validator');
        });
        Route::prefix('pelaporan-al')->name('.pelaporan-al')->group(function () {
            Route::get('/', [PelaporanALController::class, 'index']);
            Route::get('/{id}', [PelaporanALController::class, 'show'])->name('.show');
            Route::get('/{id}/timeline', [PelaporanALController::class, 'getTimeline'])->name('.timeline');
            Route::get('/{id}/document/{documentId}', [PelaporanALController::class, 'previewDocument'])->name('.document.preview');
        });
        Route::prefix('penyampaian-hasil-akreditasi')->name('.penyampaian-hasil-akreditasi')->group(function () {
            Route::get('/', [PenyampaianHasilAkreditasiController::class, 'index']);
            Route::get('/{id}', [PenyampaianHasilAkreditasiController::class, 'show'])->name('.show');
            Route::post('/{id}/calculate', [PenyampaianHasilAkreditasiController::class, 'calculate'])->name('.calculate');
            Route::post('/{id}/finalize', [PenyampaianHasilAkreditasiController::class, 'finalize'])->name('.finalize');
            Route::get('/{id}/download-summary', [PenyampaianHasilAkreditasiController::class, 'downloadSummary'])->name('.download-summary');
        });
        Route::prefix('masa-sanggah')->name('.masa-sanggah')->group(function () {
            Route::get('/', [MasaSanggahController::class, 'index']);
            Route::get('/{id}', [MasaSanggahController::class, 'show'])->name('.show');
            Route::post('/{id}/start', [MasaSanggahController::class, 'startMasaSanggah'])->name('.start');
            Route::post('/{id}/end', [MasaSanggahController::class, 'endMasaSanggah'])->name('.end');
        });
        Route::prefix('pelaksanaan-banding')->name('.pelaksanaan-banding')->group(function () {
            Route::get('/', [PelaksanaanBandingController::class, 'index']);
            Route::get('/{id}', [PelaksanaanBandingController::class, 'show'])->name('.show');
            Route::post('/{id}/mulai-pelaksanaan', [PelaksanaanBandingController::class, 'mulaiPelaksanaan'])->name('.mulai-pelaksanaan');
            Route::post('/{id}/selesaikan', [PelaksanaanBandingController::class, 'selesaikanPelaksanaan'])->name('.selesaikan');
        });
        Route::prefix('pelaporan-banding')->name('.pelaporan-banding')->group(function () {
            Route::get('/', [PelaporanBandingController::class, 'index']);
            Route::get('/{id}', [PelaporanBandingController::class, 'show'])->name('.show');
            Route::post('/{id}/upload-laporan', [PelaporanBandingController::class, 'uploadLaporan'])->name('.upload-laporan');
            Route::get('/{id}/download-laporan', [PelaporanBandingController::class, 'downloadLaporan'])->name('.download-laporan');
            Route::get('/{id}/timeline', [PelaporanBandingController::class, 'getTimeline'])->name('.timeline');
        });
        Route::prefix('penetapan-hasil-akreditasi')->name('.penetapan-hasil-akreditasi')->group(function () {
            Route::get('/', [PenetapanHasilAkreditasiController::class, 'index']);
            Route::get('/{id}', [PenetapanHasilAkreditasiController::class, 'show'])->name('.show');
            Route::post('/{id}/tetapkan', [PenetapanHasilAkreditasiController::class, 'tetapkanHasil'])->name('.tetapkan');
            Route::post('/{id}/batalkan', [PenetapanHasilAkreditasiController::class, 'batalkanPenetapan'])->name('.batalkan');
        });
        Route::prefix('pelaporan-hasil-akreditasi')->name('.pelaporan-hasil-akreditasi')->group(function () {
            Route::get('/', [PelaporanHasilAkreditasiController::class, 'index']);
            Route::get('/{id}', [PelaporanHasilAkreditasiController::class, 'show'])->name('.show');
            Route::post('/{id}/upload-laporan', [PelaporanHasilAkreditasiController::class, 'uploadLaporan'])->name('.upload-laporan');
            Route::post('/{id}/upload-sertifikat', [PelaporanHasilAkreditasiController::class, 'uploadSertifikat'])->name('.upload-sertifikat');
            Route::post('/{id}/selesaikan', [PelaporanHasilAkreditasiController::class, 'selesaikanPelaporan'])->name('.selesaikan');
            Route::get('/{id}/download/{jenis}', [PelaporanHasilAkreditasiController::class, 'downloadDokumen'])->name('.download');
            Route::get('/{id}/timeline', [PelaporanHasilAkreditasiController::class, 'getTimeline'])->name('.timeline');
        });
        Route::prefix('penyimpanan-arsip-pelaksanaan-akreditasi')->name('.penyimpanan-arsip-pelaksanaan-akreditasi')->group(function () {
            Route::get('/', [PenyimpananArsipAkreditasiController::class, 'index']);
            Route::get('/{id}', [PenyimpananArsipAkreditasiController::class, 'show'])->name('.show');
            Route::post('/{id}/simpan', [PenyimpananArsipAkreditasiController::class, 'simpanArsip'])->name('.simpan');
            Route::post('/{id}/selesaikan', [PenyimpananArsipAkreditasiController::class, 'selesaikanProses'])->name('.selesaikan');
            Route::get('/{id}/download-all', [PenyimpananArsipAkreditasiController::class, 'downloadAllDocuments'])->name('.download-all');
        });
        Route::prefix('payment-summary')->name('.payment-summary')->group(function () {
            Route::post('/verify', [PaymentSummaryController::class, 'verifyPassword'])->name('.verify');
            Route::get('/data', [PaymentSummaryController::class, 'getSummary'])->name('.data');
            Route::post('/logout', [PaymentSummaryController::class, 'logout'])->name('.logout');
        });
    });

    // ========================================
    // UPPS/PRODI ROUTES
    // ========================================
    Route::prefix('upps')->name('upps')->middleware(['auth', 'role:admin_prodi'])->group(function () {

        // Step 1: Pengingat Masa Akreditasi
        Route::prefix('pengingat-akreditasi')->name('.pengingat-akreditasi')->group(function () {
            Route::get('/', [\App\Http\Controllers\UPPS\PengingatAkreditasiController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\UPPS\PengingatAkreditasiController::class, 'show'])->name('.show');
            Route::get('/{id}/respond', [\App\Http\Controllers\UPPS\PengingatAkreditasiController::class, 'showResponseForm'])->name('.respond.form');
            Route::post('/{id}/respond', [\App\Http\Controllers\UPPS\PengingatAkreditasiController::class, 'respond'])->name('.respond');
        });

        Route::prefix('surat-permohonan')->name('.surat-permohonan')->group(function () {
            Route::get('/', [\App\Http\Controllers\UPPS\SuratPermohonanController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\UPPS\SuratPermohonanController::class, 'show'])->name('.show');
            Route::get('/{id}/download', [\App\Http\Controllers\UPPS\SuratPermohonanController::class, 'download'])->name('.download');
        });

        Route::prefix('penerimaan-permohonan')->name('.penerimaan-permohonan')->group(function () {
            Route::get('/', [\App\Http\Controllers\UPPS\PenerimaanPermohonanController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\UPPS\PenerimaanPermohonanController::class, 'show'])->name('.show');
            Route::get('/{id}/download', [\App\Http\Controllers\UPPS\PenerimaanPermohonanController::class, 'download'])->name('.download');
        });

        Route::prefix('penyampaian-template')->name('.penyampaian-template')->group(function () {
            Route::get('/', [\App\Http\Controllers\UPPS\PenyampaianTemplateController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\UPPS\PenyampaianTemplateController::class, 'show'])->name('.show');
            Route::get('/{id}/download/{jenisDokumen}', [\App\Http\Controllers\UPPS\PenyampaianTemplateController::class, 'download'])->name('.download');

            // Request Upload Ulang
            Route::get('/{id}/request-upload/{jenisDokumen}', [\App\Http\Controllers\UPPS\PenyampaianTemplateController::class, 'showRequestForm'])->name('.request.form');
            Route::post('/{id}/request-upload/{jenisDokumen}', [\App\Http\Controllers\UPPS\PenyampaianTemplateController::class, 'requestUploadUlang'])->name('.request.submit');
        });

        Route::prefix('validasi-pembayaran')->name('.validasi-pembayaran')->group(function () {
            Route::get('/', [\App\Http\Controllers\UPPS\ValidasiPembayaranController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\UPPS\ValidasiPembayaranController::class, 'show'])->name('.show');
            Route::get('/{id}/upload', [\App\Http\Controllers\UPPS\ValidasiPembayaranController::class, 'showUploadForm'])->name('.upload.form');
            Route::post('/{id}/upload', [\App\Http\Controllers\UPPS\ValidasiPembayaranController::class, 'uploadBukti'])->name('.upload');
            Route::get('/dokumen/{id}/download', [\App\Http\Controllers\UPPS\ValidasiPembayaranController::class, 'download'])->name('.dokumen.download');
        });

        Route::prefix('penerimaan-dokumen')->name('.penerimaan-dokumen')->group(function () {
            Route::get('/', [App\Http\Controllers\UPPS\PenerimaanDokumenController::class, 'index']);
        });

        Route::middleware('under.dev')->group(function () {
            Route::prefix('validasi-dokumen')->name('.validasi-dokumen')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\ValidasiDokumenController::class, 'index']);
            });

            Route::prefix('pelaporan-dokumen')->name('.pelaporan-dokumen')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PelaporanDokumenController::class, 'index']);
            });

            Route::prefix('penugasan-ak')->name('.penugasan-ak')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PenugasanAKController::class, 'index']);
            });

            Route::prefix('validasi-ak')->name('.validasi-ak')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\ValidasiAKController::class, 'index']);
            });

            Route::prefix('pelaporan-ak')->name('.pelaporan-ak')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PelaporanAKController::class, 'index']);
            });

            Route::prefix('penugasan-al')->name('.penugasan-al')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PenugasanALController::class, 'index']);
            });

            Route::prefix('pelaksanaan-al')->name('.pelaksanaan-al')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PelaksanaanALController::class, 'index']);
            });

            Route::prefix('pelaporan-al')->name('.pelaporan-al')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PelaporanALController::class, 'index']);
            });

            Route::prefix('penyampaian-hasil-akreditasi')->name('.penyampaian-hasil-akreditasi')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PenyampaianHasilAkreditasiController::class, 'index']);
            });

            Route::prefix('masa-sanggah')->name('.masa-sanggah')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\MasaSanggahController::class, 'index']);
            });

            Route::prefix('pelaksanaan-banding')->name('.pelaksanaan-banding')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PelaksanaanBandingController::class, 'index']);
            });

            Route::prefix('pelaporan-banding')->name('.pelaporan-banding')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PelaporanBandingController::class, 'index']);
            });

            Route::prefix('penetapan-hasil-akreditasi')->name('.penetapan-hasil-akreditasi')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PenetapanHasilAkreditasiController::class, 'index']);
            });

            Route::prefix('pelaporan-hasil-akreditasi')->name('.pelaporan-hasil-akreditasi')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PelaporanHasilAkreditasiController::class, 'index']);
            });

            Route::prefix('penyimpanan-arsip-pelaksanaan-akreditasi')->name('.penyimpanan-arsip-pelaksanaan-akreditasi')->group(function () {
                Route::get('/', [App\Http\Controllers\UPPS\PenyimpananArsipAkreditasiController::class, 'index']);
            });
        });
    });

    Route::prefix('keuangan')
        ->name('keuangan.')
        ->group(function () {

            Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Keuangan\ValidasiPembayaranController::class, 'index'])->name('index');
                Route::get('/{id}', [\App\Http\Controllers\Keuangan\ValidasiPembayaranController::class, 'show'])->name('show');
                Route::get('/{id}/download-bukti', [\App\Http\Controllers\Keuangan\ValidasiPembayaranController::class, 'downloadBukti'])->name('download-bukti');
                Route::post('/{id}/verify', [\App\Http\Controllers\Keuangan\ValidasiPembayaranController::class, 'verify'])->name('verify');
            });
            Route::prefix('formulir')->name('formulir.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Keuangan\FormulirPembayaranController::class, 'index'])->name('index');
                Route::get('/{id}', [\App\Http\Controllers\Keuangan\FormulirPembayaranController::class, 'show'])->name('show');
                Route::get('/{id}/download/{dokumenId}', [\App\Http\Controllers\Keuangan\FormulirPembayaranController::class, 'download'])->name('download');
            });
        });

    // routes/web.php

    Route::prefix('validator')->name('validator.')->group(function () {
        Route::prefix('dokumen')->name('borang.')->group(function () {
            Route::get('/', [BorangValidatorController::class, 'index'])->name('index');

            // ✅ NEW: Auto-save review per item
            Route::post('/{assignment}/update-review', [BorangValidatorController::class, 'updateReview'])->name('update-review');

            Route::post('/{assignment}/submit', [BorangValidatorController::class, 'submit'])->name('submit');
            Route::get('/{assignment}/stats', [BorangValidatorController::class, 'getValidationStats'])->name('stats');
            Route::get('/{assignment}/summary', [BorangValidatorController::class, 'getValidationSummary'])->name('summary');

            // ✅ NEW: Accept/Reject offer
            Route::post('/{assignment}/respond-offer', [BorangValidatorController::class, 'respondOffer'])->name('respond-offer');
            Route::get('/{assignment}/download-template', [BorangValidatorController::class, 'downloadTemplate'])->name('download-template');
            Route::get('/{assignment}/download-review', [BorangValidatorController::class, 'downloadReview'])->name('download-review');
            Route::post('/{assignment}/upload-review', [BorangValidatorController::class, 'uploadReview'])->name('upload-review');
            Route::post('/{assignment}/reset-review', [BorangValidatorController::class, 'resetReview'])->name('reset-review');
            Route::post('/{assignment}/laporkan-validasi', [BorangValidatorController::class, 'laporkanValidasi'])->name('laporkan-validasi');

            Route::get('/{assignment}', [BorangValidatorController::class, 'show'])->name('show');
        });
    });

    Route::prefix('/pelaporan')->name('pelaporan.')->group(function () {
        Route::get('/', [PelaporanController::class, 'index'])->name('index');
        Route::get('/dokumen', [PelaporanController::class, 'indexDokumen'])->name('indexDokumen');
        Route::get('/validasi-ak', [PelaporanController::class, 'indexValidasiAK'])->name('indexValidasiAK');
        Route::get('/ak', [PelaporanController::class, 'indexAK'])->name('indexAK');
        Route::get('/al', [PelaporanController::class, 'indexAL'])->name('indexAL');

        Route::prefix('/{assignment}/dokumen')->name('borang.')->group(function () {
            Route::post('/upload', [PelaporanController::class, 'uploadLaporanValidasi'])->name('upload');
            Route::post('/finalize', [PelaporanController::class, 'finalizePelaporanValidasi'])->name('finalize');
        });

        Route::prefix('/{assignment}/validasi-ak')->name('validasiAk.')->group(function () {
            Route::post('/upload',   [PelaporanController::class, 'uploadLaporanValidasiAK'])->name('upload');
            Route::post('/finalize', [PelaporanController::class, 'finalizeValidasiAK'])->name('finalize');
        });

        Route::prefix('/{assignment}/ak')->name('ak.')->group(function () {
            Route::post('/upload',   [PelaporanController::class, 'uploadLaporanAK'])->name('upload');
            Route::post('/finalize', [PelaporanController::class, 'finalizePelaporanAK'])->name('finalize');
        });

        Route::prefix('/{assignment}/al')->name('al.')->group(function () {
            Route::post('/upload',   [PelaporanController::class, 'uploadLaporanAL'])->name('upload');
            Route::post('/finalize', [PelaporanController::class, 'finalizePelaporanAL'])->name('finalize');
        });
    });

    Route::middleware(['role:super_admin,asesi'])->prefix('hasil-akreditasi')->name('hasil-akreditasi.')->group(function () {
        // View hasil
        Route::get('/asesmen/{id}', [HasilAkreditasiController::class, 'show'])->name('show');

        // Calculate & Finalize AK
        Route::post('/asesmen/{id}/hitung-ak', [HasilAkreditasiController::class, 'hitungAK'])->name('hitung-ak');
        Route::post('/asesmen/{id}/finalize-ak', [HasilAkreditasiController::class, 'finalizeAK'])->name('finalize-ak');

        // Calculate & Finalize AL
        Route::post('/asesmen/{id}/hitung-al', [HasilAkreditasiController::class, 'hitungAL'])->name('hitung-al');
        Route::post('/asesmen/{id}/finalize-al', [HasilAkreditasiController::class, 'finalizeAL'])->name('finalize-al');

        // Publish to prodi
        Route::post('/asesmen/{id}/publish', [HasilAkreditasiController::class, 'publishHasil'])->name('publish');

        // Download laporan
        Route::get('/asesmen/{id}/download/{format}', [HasilAkreditasiController::class, 'downloadLaporan'])->name('download');

        // Step 14: Penyampaian Hasil
        Route::get('/{id}/form', [HasilAkreditasiController::class, 'showFormHasilAkreditasi'])->name('form');
        Route::post('/{id}/submit', [HasilAkreditasiController::class, 'submitHasilAkreditasi'])->name('submit');

        Route::get('/asesmen/{id}/validation-summary', [HasilAkreditasiController::class, 'getValidationSummary'])->name('validation-summary');

        // ✅ NEW: Manual override (admin only)
        Route::post('/asesmen/{id}/override-peringkat', [HasilAkreditasiController::class, 'overridePeringkat'])->name('override-peringkat');

        // Step 15: Masa Sanggah
        Route::post('/{id}/start-masa-sanggah', [HasilAkreditasiController::class, 'startMasaSanggah'])->name('start-masa-sanggah');

        // Step 16-17: Banding
        Route::post('/{id}/submit-banding', [HasilAkreditasiController::class, 'submitBanding'])->name('submit-banding');
        Route::post('/{id}/laporkan-banding', [HasilAkreditasiController::class, 'laporkanBanding'])->name('laporkan-banding');

        // Step 18-20: Penetapan, Pengumuman, Pelaporan
        Route::post('/{id}/tetapkan', [HasilAkreditasiController::class, 'tetapkanHasil'])->name('tetapkan');
        Route::post('/{id}/umumkan', [HasilAkreditasiController::class, 'umumkanHasil'])->name('umumkan');
        Route::post('/{id}/laporkan', [HasilAkreditasiController::class, 'laporkanHasil'])->name('laporkan');
        Route::post('/{id}/simpan-arsip', [HasilAkreditasiController::class, 'simpanArsip'])->name('simpan-arsip');
    });

    Route::middleware(['role:super_admin,asesi'])->group(function () {
        Route::resource('asesmen', AsesmenController::class);

        // INDIKATOR MANAGEMENT (Kriteria, Elemen Standar, Indikator)
        Route::resource('kriteria', KriteriaController::class);
        Route::resource('elemen', ElemenStandarController::class);
        Route::resource('elemen-standar', ElemenStandarController::class);
        Route::resource('jenis-indikator', JenisIndikatorController::class);
        Route::resource('indikator', IndikatorController::class);

        // USER MANAGEMENT (Admin Only)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('export', [UserController::class, 'export'])->name('export');
            Route::get('template', [UserController::class, 'downloadTemplate'])->name('template');
            Route::post('import', [UserController::class, 'import'])->name('import');
            Route::resource('/', UserController::class)->parameters(['' => 'user']);
        });

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
            Route::post('/{id}/reorder-asesor', [AsesmenController::class, 'reorderAsesor'])->name('reorder-asesor');
        });
    });

    Route::middleware('under.dev')->group(function () {
        // PENUGASAN BANDING
        Route::prefix('banding')->group(function () {
            Route::get('/', [BandingController::class, 'index'])->name('banding.index'); // nama resmi
            Route::get('/', [BandingController::class, 'index'])->name('banding'); // alias tanpa .index
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
        Route::get('/', [ProfileController::class, 'index']);
        Route::put('/', [ProfileController::class, 'update'])->name('.update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('.avatar');

        Route::get('/password', [ProfileController::class, 'passwordForm'])->name('.password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('.password.update');
        Route::post('/switch-role', [ProfileController::class, 'switchRole'])->name('.switch-role');
        Route::get('/available-roles', [ProfileController::class, 'getAvailableRoles'])->name('.available-roles');

        Route::get('/prodi-data', [ProdiDataController::class, 'index'])->name('.prodi-data');
        Route::post('/prodi-data/university', [ProdiDataController::class, 'updateUniversity'])->name('.prodi-data.university'); // POST untuk AJAX
        Route::post('/prodi-data/study-program/{id}', [ProdiDataController::class, 'updateStudyProgram'])->name('.prodi-data.study-program'); // POST untuk AJAX
        Route::post('/prodi-data/logo', [ProdiDataController::class, 'updateLogo'])->name('.prodi-data.logo'); // Fix typo
    });

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

// Route::get('/preview/email/penawaran/{assignment}', function (\App\Models\AsesmenUserRole $assignment) {
//     return new \App\Mail\PenawaranAsesmenMail($assignment);
// })->name('email.preview.penawaran');

Route::get('clearcache', function () {
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    Illuminate\Support\Facades\Artisan::call('route:clear');
    Illuminate\Support\Facades\Artisan::call('view:clear');
    Illuminate\Support\Facades\Artisan::call('config:clear');
    Illuminate\Support\Facades\Artisan::call('config:cache');
});

Route::get('migrateseed', function () {
    Illuminate\Support\Facades\Artisan::call('migrate:fresh');
    Illuminate\Support\Facades\Artisan::call('db:seed');
});

Route::get('/debug/dataset-borang', [\App\Http\Controllers\DatasetBorangController::class, 'index'])->name('debug.dataset-borang.index');
