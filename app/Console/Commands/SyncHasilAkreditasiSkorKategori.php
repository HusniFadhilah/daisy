<?php
// app/Console/Commands/SyncHasilAkreditasiSkorKategori.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HasilAkreditasiSyncService;

class SyncHasilAkreditasiSkorKategori extends Command
{
    protected $signature = 'hasil-akreditasi:sync-skor-kategori
                            {--id= : Sync specific hasil by ID}
                            {--pengajuan= : Sync by pengajuan ID}
                            {--asesmen= : Sync by asesmen ID}
                            {--all : Sync all hasil akreditasi}';

    protected $description = 'Sync skor_kategori di detail_skor_ak dan detail_skor_al dengan JenjangPenilaian terbaru';

    protected $syncService;

    public function __construct(HasilAkreditasiSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    public function handle()
    {
        $this->info('Starting sync skor_kategori...');

        try {
            if ($this->option('id')) {
                // Sync by hasil ID
                $hasilId = $this->option('id');
                $this->info("Syncing hasil ID: {$hasilId}");

                $this->syncService->syncByHasilId($hasilId);

                $this->info('✅ Successfully synced!');
                return 0;
            }

            if ($this->option('pengajuan')) {
                // Sync by pengajuan ID
                $pengajuanId = $this->option('pengajuan');
                $this->info("Syncing pengajuan ID: {$pengajuanId}");

                $this->syncService->syncByPengajuanId($pengajuanId);

                $this->info('✅ Successfully synced!');
                return 0;
            }

            if ($this->option('asesmen')) {
                // Sync by asesmen ID
                $asesmenId = $this->option('asesmen');
                $this->info("Syncing asesmen ID: {$asesmenId}");

                $this->syncService->syncByAsesmenId($asesmenId);

                $this->info('✅ Successfully synced!');
                return 0;
            }

            if ($this->option('all')) {
                // Sync all
                if (!$this->confirm('This will sync ALL hasil akreditasi. Continue?')) {
                    $this->info('Cancelled.');
                    return 1;
                }

                $this->info('Syncing all hasil akreditasi...');
                $result = $this->syncService->syncAllSkorKategori();

                $this->info("Total: {$result['total']}");
                $this->info("✅ Updated: {$result['updated']}");

                if ($result['failed'] > 0) {
                    $this->error("❌ Failed: {$result['failed']}");

                    foreach ($result['errors'] as $error) {
                        $this->error("  - ID {$error['id']}: {$error['error']}");
                    }
                }

                return 0;
            }

            // No option provided
            $this->error('Please specify an option: --id, --pengajuan, --asesmen, or --all');
            return 1;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
