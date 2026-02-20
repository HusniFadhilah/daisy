<?php

namespace App\Providers;

use App\Models\PengajuanAkreditasi;
use App\Policies\PengajuanAkreditasiPolicy;
use App\Services\BorangImport\BorangExcelImportService;
use App\Services\BorangImport\BorangExcelSheetParser;
use App\Services\BorangImport\DatasetIdResolver;
use App\View\Components\Akreditasi\StatCard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;

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

        $this->app->bind(BorangExcelImportService::class, function () {
            return new BorangExcelImportService(
                new BorangExcelSheetParser(),
                new DatasetIdResolver()
            );
        });
    }
}
