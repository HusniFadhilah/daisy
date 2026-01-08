<?php

namespace App\Console\Commands;

use App\Models\DegreeLevel;
use Illuminate\Console\Command;
use Database\Seeders\BorangExampleSeeder;

class BorangGenerateTemplate extends Command
{
    protected $signature = 'borang:generate-template {--degree_level=}';
    protected $description = 'Generate template LED evaluasi diri per degree level';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $degreeOpt = $this->option('degree_level');

        $codes = filled($degreeOpt)
            ? collect(explode(',', $degreeOpt))
            ->map(fn($s) => strtolower(trim($s)))
            ->filter()
            ->values()
            ->all()
            : DegreeLevel::pluck('code')->all();

        $seeder = new BorangExampleSeeder();
        $seeder->setCommand($this); // supaya $this->command?->info() jalan
        $seeder->runWithCodes($codes);

        return self::SUCCESS;
    }
}
