<?php

namespace App\Providers;

use App\Http\Controllers\Asesmen\ALController;
use App\Models\PengajuanAkreditasi;
use App\Policies\PengajuanAkreditasiPolicy;
use App\Repositories\SyaratAkreditasiRepository;
use App\Services\BorangImport\BorangExcelImportService;
use App\Services\BorangImport\BorangExcelSheetParser;
use App\Services\BorangImport\DatasetIdResolver;
use App\Services\HasilAkreditasiService;
use App\Services\LkpsDataReaderService;
use App\View\Components\Akreditasi\StatCard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        PengajuanAkreditasi::class => PengajuanAkreditasiPolicy::class,
    ];

    public function register(): void
    {
        $this->ensureAlControllerAutoload();

        // SyaratAkreditasiRepository — singleton agar cache hanya dimuat sekali per request
        $this->app->singleton(SyaratAkreditasiRepository::class);

        // LkpsDataReaderService — inject SyaratAkreditasiRepository
        $this->app->bind(LkpsDataReaderService::class, function ($app) {
            return new LkpsDataReaderService(
                $app->make(SyaratAkreditasiRepository::class)
            );
        });

        // HasilAkreditasiService — inject LkpsDataReaderService + SyaratAkreditasiRepository
        $this->app->bind(HasilAkreditasiService::class, function ($app) {
            return new HasilAkreditasiService(
                $app->make(LkpsDataReaderService::class),
                $app->make(SyaratAkreditasiRepository::class),
            );
        });
    }

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();
        Blade::component('asesmen.pelaporan._card', 'pelaporan-card');
        Blade::component('stat-card', StatCard::class);

        // Ensure image directories exist
        $dirs = [
            storage_path('app/public/permohonan-akreditasi'),
            storage_path('app/tmp'),
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }
        Paginator::useBootstrapFive();
        $this->app->bind(BorangExcelImportService::class, function () {
            return new BorangExcelImportService(
                new BorangExcelSheetParser(),
                new DatasetIdResolver()
            );
        });

        $this->app->booted(function () {
            $this->ensureAlHasilRoutes();
        });
    }

    /**
     * PSR-4 can miss ALController after a partial SFTP upload or stale classmap.
     */
    private function ensureAlControllerAutoload(): void
    {
        if (class_exists(ALController::class)) {
            return;
        }

        $path = app_path('Http/Controllers/Asesmen/ALController.php');
        if (! is_file($path)) {
            return;
        }

        try {
            require_once $path;
        } catch (\Throwable) {
            // Keep the rest of the app booting if this file is mid-deploy.
        }
    }

    /**
     * Register hasil routes if a stale route cache omitted them.
     */
    private function ensureAlHasilRoutes(): void
    {
        $middleware = ['web', 'auth', 'verified', 'penawaran.accepted:al'];

        if (! Route::has('al.berkas.hasil')) {
            Route::middleware($middleware)
                ->get('/al/berkas/{idAsesmen}/hasil', [ALController::class, 'showHasil'])
                ->name('al.berkas.hasil');
        }

        if (! Route::has('al.berkas.hasil.save-resume')) {
            Route::middleware($middleware)
                ->post('/al/berkas/{idAsesmen}/hasil/resume', [ALController::class, 'saveResume'])
                ->name('al.berkas.hasil.save-resume');
        }

        if (! Route::has('al.berkas.submit')) {
            Route::middleware($middleware)
                ->post('/al/berkas/{idAsesmen}/submit', [ALController::class, 'submitPenilaian'])
                ->name('al.berkas.submit');
        }

        if (! Route::has('al.berkas.unsubmit')) {
            Route::middleware($middleware)
                ->post('/al/berkas/{idAsesmen}/unsubmit', [ALController::class, 'unsubmitPenilaian'])
                ->name('al.berkas.unsubmit');
        }
    }
}
