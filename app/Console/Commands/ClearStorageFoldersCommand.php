<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearStorageFoldersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:clear-uploads
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all files in storage/app/private and storage/app/public';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete all files in storage/app/private and storage/app/public. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Clearing storage folders...');

        $paths = [
            storage_path('app/private'),
            storage_path('app/public'),
        ];

        $totalDeleted = 0;

        foreach ($paths as $path) {
            if (File::exists($path)) {
                $files = File::allFiles($path);
                $directories = File::directories($path);

                // Delete files
                foreach ($files as $file) {
                    File::delete($file->getPathname());
                    $totalDeleted++;
                }

                // Delete directories
                foreach ($directories as $directory) {
                    File::deleteDirectory($directory);
                }

                $this->info("✓ Cleared: {$path}");
            }
        }

        $this->info("✅ Successfully deleted {$totalDeleted} files");

        return 0;
    }
}
