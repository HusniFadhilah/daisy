<?php
// app/Providers/EventServiceProvider.php

namespace App\Providers;

use App\Models\JenjangPenilaian;
use App\Observers\JenjangPenilaianObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // ✅ Register observer (optional - auto sync)
        // JenjangPenilaian::observe(JenjangPenilaianObserver::class);
    }
}
