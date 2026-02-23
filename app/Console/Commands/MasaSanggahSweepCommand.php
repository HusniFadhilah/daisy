<?php

namespace App\Console\Commands;

use App\Jobs\SelesaikanMasaSanggahJob;
use App\Models\PengajuanAkreditasi;
use Illuminate\Console\Command;

class MasaSanggahSweepCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:masa-sanggah-sweep-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        PengajuanAkreditasi::query()
            ->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
            ->whereNotNull('tanggal_masa_sanggah_selesai')
            ->where('tanggal_masa_sanggah_selesai', '<=', now())
            ->chunkById(200, function ($rows) {
                foreach ($rows as $pengajuan) {
                    // dispatch job immediate agar reuse logic + logging
                    SelesaikanMasaSanggahJob::dispatch($pengajuan->id, 'sweep');
                }
            });

        return self::SUCCESS;
    }
}
