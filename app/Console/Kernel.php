<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\PengajuanAkreditasi;
use App\Jobs\SelesaikanMasaSanggahJob;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /*
        |--------------------------------------------------------------------------
        | MASA SANGGAH SCHEDULER (MODE: sweep)
        |--------------------------------------------------------------------------
        | Jika mode = sweep:
        | - Scheduler akan setiap menit mengecek pengajuan yang sudah
        |   melewati tanggal_masa_sanggah_selesai
        | - Lalu dispatch job untuk menyelesaikan statusnya
        |
        | Jika mode = delay:
        | - Tidak perlu scheduler
        | - Penyelesaian dilakukan oleh delayed job langsung
        |--------------------------------------------------------------------------
        */

        // BAN-PT sync harian jam 02:00
        $schedule->command('banpt:sync-accreditation')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onOneServer();

        if (config('akreditasi.masa_sanggah_mode') === 'sweep') {

            $schedule->call(function () {
                PengajuanAkreditasi::query()
                    ->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
                    ->whereNotNull('tanggal_masa_sanggah_selesai')
                    ->where('tanggal_masa_sanggah_selesai', '<=', now())
                    ->chunkById(200, function ($rows) {
                        foreach ($rows as $pengajuan) {
                            $statusFrom = $pengajuan->status;

                            $updated = PengajuanAkreditasi::where('id', $pengajuan->id)
                                ->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
                                ->update(['status' => PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI]);

                            if (!$updated) continue;

                            $pengajuan->refresh();
                            $pengajuan->statusLog()->create([
                                'status_from' => $statusFrom,
                                'status_to'   => PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                                'changed_by'  => $pengajuan->id_de_assigned ?? $pengajuan->id_user_pengaju,
                                'keterangan'  => 'Masa sanggah selesai otomatis (scheduler).',
                                'changed_at'  => now(),
                            ]);
                        }
                    });
            })->everyMinute()->name('masa_sanggah_sweep')->withoutOverlapping();
        }
    }
}
