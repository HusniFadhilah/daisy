<?php
// app/Jobs/SyncHasilAkreditasiSkorKategori.php

namespace App\Jobs;

use App\Services\HasilAkreditasiSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncHasilAkreditasiSkorKategori implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(HasilAkreditasiSyncService $syncService)
    {
        try {
            $result = $syncService->syncAllSkorKategori();

            Log::info('Sync skor_kategori job completed', $result);
        } catch (\Exception $e) {
            Log::error('Sync skor_kategori job failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
