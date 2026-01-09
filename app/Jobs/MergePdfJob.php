<?php

namespace App\Jobs;

use App\Services\Pdf\PdfMergeService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MergePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public array $files,
        public string $outputFile,
        public array $options = []
    ) {}

    public function handle(PdfMergeService $service): void
    {
        Log::info('MergePdfJob handling', [
            'output' => $this->outputFile,
            'count' => count($this->files),
        ]);

        $service->merge($this->files, $this->outputFile, $this->options);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('MergePdfJob failed', [
            'output' => $this->outputFile,
            'error' => $e->getMessage(),
        ]);
    }
}
