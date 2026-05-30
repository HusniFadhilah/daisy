<?php

namespace App\Console\Commands;

use App\Models\BanptSyncRun;
use App\Services\Banpt\BanptAccreditationSyncService;
use Illuminate\Console\Command;

class SyncBanptAccreditation extends Command
{
    protected $signature   = 'banpt:sync-accreditation';
    protected $description = 'Sinkronisasi data akreditasi dari BAN-PT Bianglala ke staging perubahan.';

    public function handle(BanptAccreditationSyncService $service): int
    {
        $this->info('Memulai sinkronisasi BAN-PT...');

        try {
            $run = $service->syncAll();
        } catch (\Throwable $e) {
            $this->error("Sync gagal total: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Status',         $run->status],
                ['Diperiksa',      $run->checked_count],
                ['Ada Perubahan',  $run->changed_count],
                ['Error',          $run->error_count],
                ['Durasi',         $run->duration ?? '-'],
                ['Pesan',          $run->message ?? '-'],
            ]
        );

        if ($run->status === BanptSyncRun::STATUS_FAILED) {
            $this->warn('Sync selesai dengan beberapa error. Cek log untuk detail.');
            return self::FAILURE;
        }

        $this->info('Sinkronisasi BAN-PT selesai.');
        return self::SUCCESS;
    }
}
