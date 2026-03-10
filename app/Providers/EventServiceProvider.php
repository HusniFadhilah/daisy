<?php
// app/Providers/EventServiceProvider.php

namespace App\Providers;

use App\Models\JenjangPenilaian;
use App\Validators\JenjangPenilaianValidator;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // ✅ Register validator (optional - auto sync)
        // JenjangPenilaian::observe(JenjangPenilaianValidator::class);
    }
}
