<?php
// app/Jobs/MergeBorangFilesJob.php

namespace App\Jobs;

use App\Models\PengajuanAkreditasi;
use App\Services\BorangMergeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MergeBorangFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pengajuanId;

    public function __construct($pengajuanId)
    {
        $this->pengajuanId = $pengajuanId;
    }

    public function handle(BorangMergeService $mergeService)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($this->pengajuanId);

            Log::info("Starting merge job for pengajuan: {$this->pengajuanId}");

            $result = $mergeService->mergeBorangFiles($pengajuan);

            // Update pengajuan with merged file info
            $pengajuan->update([
                'merged_file_path' => $result['file_path'],
                'merge_status' => 'completed',
            ]);

            Log::info("Merge job completed for pengajuan: {$this->pengajuanId}");
        } catch (\Exception $e) {
            Log::error("Merge job failed: " . $e->getMessage(), [
                'pengajuan_id' => $this->pengajuanId,
                'trace' => $e->getTraceAsString()
            ]);

            // Update status to failed
            PengajuanAkreditasi::where('id', $this->pengajuanId)
                ->update(['merge_status' => 'failed']);

            throw $e;
        }
    }
}
