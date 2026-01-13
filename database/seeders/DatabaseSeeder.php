<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->clearStorageFolders();
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DegreeLevelSeeder::class,
            UniversitySeeder::class,
            StudyProgramSeeder::class,
            StudyProgramCategorySeeder::class,
            KriteriaSeeder::class,
            ElemenStandarSeeder::class,
            PernyataanSeeder::class,
            JenisIndikatorSeeder::class,
            IndikatorSeeder::class,
            JenjangPenilaianSeeder::class,
            IndikatorPenilaianElemenSeeder::class,
            AkreditasiSeeder::class,
            StudyProgramUserSeeder::class,
            AsesmenUserRoleSeeder::class,
            // PengajuanAkreditasiSeeder::class,
            DatasetBorangSeeder::class,
            BorangExampleSeeder::class,
        ]);
    }
    /**
     * Clear storage folders
     */
    private function clearStorageFolders(): void
    {
        // Paths to clear
        $paths = [
            storage_path('app/private'),
            storage_path('app/public'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                // Get all files and directories
                $files = File::allFiles($path);
                $directories = File::directories($path);

                // Delete all files
                foreach ($files as $file) {
                    File::delete($file->getPathname());
                }

                // Delete all subdirectories
                foreach ($directories as $directory) {
                    // Keep .gitignore if exists
                    $gitignore = $directory . '/.gitignore';
                    $hasGitignore = File::exists($gitignore);

                    File::deleteDirectory($directory);

                    // Recreate directory with .gitignore
                    if ($hasGitignore) {
                        File::makeDirectory($directory, 0755, true);
                        File::put($gitignore, "*\n!.gitignore\n");
                    }
                }
            } else {
                // Create if not exists
                File::makeDirectory($path, 0755, true);
                File::put($path . '/.gitignore', "*\n!.gitignore\n");
            }
        }
    }
}
