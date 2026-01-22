<?php

namespace App\Providers;

use App\Models\PengajuanAkreditasi;
use App\Policies\PengajuanAkreditasiPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    }
}
