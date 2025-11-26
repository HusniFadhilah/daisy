<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, DashboardController, PenawaranController, PenugasanController, AKController, ALController, BandingController, PedomanController, DokumenController, PanduanController, BantuanController, ProfileController, SettingsController, ActivityController, TaskController, PasswordResetController, LaporanController};


// Dashboard (awal)
Route::get('/', function () {
    return view('home');
})->name('home');

// LOGIN / REGISTER
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

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

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::prefix('ak')->name('ak.')->group(function () {
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

    // PROSES AL
    Route::prefix('al')->name('al.')->group(function () {
        Route::get('/jadwal', [ALController::class, 'jadwal'])->name('jadwal');
        Route::get('/jadwal/{id}', [ALController::class, 'showJadwal'])->name('jadwal.show');
        Route::post('/jadwal/{id}/konfirmasi', [ALController::class, 'konfirmasiJadwal'])->name('jadwal.konfirmasi');

        Route::get('/dokumen', [ALController::class, 'dokumen'])->name('dokumen');
        Route::get('/dokumen/{id}/download', [ALController::class, 'downloadDokumen'])->name('dokumen.download');

        Route::get('/upload', [ALController::class, 'upload'])->name('upload');
        Route::post('/upload', [ALController::class, 'storeUpload'])->name('upload.store');
        Route::delete('/upload/{id}', [ALController::class, 'deleteUpload'])->name('upload.delete');

        Route::get('/laporan', [ALController::class, 'laporan'])->name('laporan');
        Route::get('/laporan/{id}', [ALController::class, 'showLaporan'])->name('laporan.show');
        Route::post('/laporan', [ALController::class, 'storeLaporan'])->name('laporan.store');
    });

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
