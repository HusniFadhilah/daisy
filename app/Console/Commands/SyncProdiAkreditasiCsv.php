<?php

namespace App\Console\Commands;

use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use App\Models\StudyProgramCategory;
use App\Models\University;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncProdiAkreditasiCsv extends Command
{
    protected $signature = 'prodi:sync-akreditasi-csv
        {--file=database/seeders/data/data_akreditasi_lengkap.csv : Path CSV relatif ke base path atau path absolut}
        {--dry-run : Tampilkan rencana perubahan tanpa menyimpan}
        {--set-not-active : Nonaktifkan prodi non-contoh yang tidak ditemukan di CSV dan tidak punya pengajuan}
        {--set_not_active : Alias untuk --set-not-active}
        {--keep-missing : Jangan nonaktifkan prodi existing yang tidak ada di CSV}';

    protected $description = 'Sinkronisasi study_programs dari CSV akreditasi tanpa truncate agar relasi lama tetap aman.';

    private array $categoryByDegree = [
        'S1' => 'AK',
        'S2' => 'MAG',
        'S3' => 'DOK',
        'D1' => 'VOK',
        'D2' => 'VOK',
        'D3' => 'VOK',
        'D4' => 'VOK',
        'D-I' => 'VOK',
        'D-II' => 'VOK',
        'D-III' => 'VOK',
        'D-IV' => 'VOK',
        'S2 TERAPAN' => 'MT',
        'S3 TERAPAN' => 'MT',
        'PROFESI' => 'PRO',
    ];

    public function handle(): int
    {
        $path = $this->resolvePath((string) $this->option('file'));
        $dryRun = (bool) $this->option('dry-run');
        $keepMissing = (bool) $this->option('keep-missing');
        $setNotActive = (bool) $this->option('set-not-active') || (bool) $this->option('set_not_active');

        if (!is_file($path)) {
            $this->error("File CSV tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $this->info('Membaca CSV: ' . $path);
        if ($dryRun) {
            $this->warn('Mode dry-run aktif: tidak ada data yang disimpan.');
        }

        $degreeLevels = $this->degreeLevelMap();
        $categories = StudyProgramCategory::query()->pluck('id', 'code')->all();

        $metrics = [
            'rows' => 0,
            'created_universities' => 0,
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'reactivated' => 0,
            'deactivated' => 0,
            'locked_by_pengajuan' => 0,
            'skipped' => 0,
            'duplicates' => 0,
        ];
        $errors = [];
        $seenKeys = [];

        $handle = $this->openCsvStream($path);
        if ($handle === false) {
            $this->error("Gagal membuka CSV: {$path}");
            return self::FAILURE;
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            $this->error('CSV kosong atau header tidak terbaca.');
            return self::FAILURE;
        }

        $header = array_map(fn($value) => $this->normalizeHeader((string) $value), $header);

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $metrics['rows']++;

                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), null);
                }

                $data = array_combine($header, array_slice($row, 0, count($header)));
                if ($data === false) {
                    $metrics['skipped']++;
                    continue;
                }

                $universityName = $this->normalizeUniversityName($this->csvValue($data, 'Universitas'));
                $programName = $this->normalizeText($this->csvValue($data, 'Program Studi'));
                $degreeAlias = $this->normalizeDegreeAlias($this->csvValue($data, 'Jenjang'));

                if ($universityName === '' || $programName === '' || $degreeAlias === '') {
                    $metrics['skipped']++;
                    $errors[] = "Baris {$metrics['rows']}: universitas/prodi/jenjang kosong.";
                    continue;
                }

                $degreeLevel = $degreeLevels[$degreeAlias] ?? null;
                if (!$degreeLevel) {
                    $metrics['skipped']++;
                    $errors[] = "Baris {$metrics['rows']}: jenjang tidak ditemukan: {$degreeAlias} ({$programName} - {$universityName}).";
                    continue;
                }

                $key = $this->syncKey($universityName, $programName, $degreeLevel->id);
                if (isset($seenKeys[$key])) {
                    $metrics['duplicates']++;
                }
                $seenKeys[$key] = true;

                $university = University::query()->where('name', $universityName)->first();
                if (!$university) {
                    $metrics['created_universities']++;
                    $university = new University([
                        'code' => $this->generateUniversityCode($universityName),
                        'name' => $universityName,
                        'is_active' => true,
                        'is_example' => false,
                    ]);

                    if (!$dryRun) {
                        $university->save();
                    }
                }

                $categoryCode = $this->categoryByDegree[strtoupper($degreeAlias)] ?? 'AK';
                $tanggal = $this->parseDate(
                    $this->csvValue($data, 'Tanggal_Kedaluwarsa')
                        ?: $this->csvValue($data, 'Tanggal_Kadaluarsa')
                );
                $status = $this->normalizeStatus(
                    $this->csvValue($data, 'Status_Kedaluwarsa')
                        ?: $this->csvValue($data, 'Status_Kadaluarsa'),
                    $tanggal
                );
                $peringkat = $this->nullableDash($this->csvValue($data, 'Peringkat_Akreditasi'));
                $noSk = $this->nullableDash($this->csvValue($data, 'Nomor SK'));
                $email = $this->nullableDash($this->csvValue($data, 'email') ?: $this->csvValue($data, 'Email'));

                $attributes = [
                    'name' => $programName,
                    'full_name' => $degreeAlias . ' - ' . $programName . ' ' . $universityName,
                    'id_university' => $university->id,
                    'id_degree_level' => $degreeLevel->id,
                    'id_category' => $categories[$categoryCode] ?? null,
                    'bentuk_pt' => $this->detectBentukPt($universityName),
                    'email' => $email,
                    'peringkat_akreditasi' => $peringkat,
                    'no_sk' => $noSk,
                    'tanggal_kedaluwarsa' => $tanggal,
                    'status_kedaluwarsa' => $status,
                    'akreditasi_source' => 'csv',
                    'is_active' => true,
                    'is_example' => false,
                ];

                $studyProgram = StudyProgram::query()
                    ->where('id_university', $university->id)
                    ->where('id_degree_level', $degreeLevel->id)
                    ->where('name', $programName)
                    ->first();

                if (!$studyProgram) {
                    $metrics['created']++;
                    if (!$dryRun) {
                        StudyProgram::query()->create(array_merge([
                            'code' => $this->generateProgramCode($programName),
                        ], $attributes));
                    }
                    continue;
                }

                if ($this->hasPengajuanAkreditasi($studyProgram)) {
                    $metrics['locked_by_pengajuan']++;
                    continue;
                }

                $wasInactive = !$studyProgram->is_active;
                $studyProgram->fill($attributes);

                if ($studyProgram->isDirty()) {
                    $metrics['updated']++;
                    if ($wasInactive) {
                        $metrics['reactivated']++;
                    }

                    if (!$dryRun) {
                        $studyProgram->save();
                    }
                } else {
                    $metrics['unchanged']++;
                }
            }

            fclose($handle);

            if ($setNotActive && !$keepMissing) {
                $query = StudyProgram::query()->where('is_example', false);
                $missingIds = [];

                $query->with(['university', 'degreeLevel'])->chunkById(500, function ($programs) use (&$missingIds, $seenKeys) {
                    foreach ($programs as $program) {
                        $key = $this->syncKey(
                            $program->university?->name ?? '',
                            $program->name,
                            (int) $program->id_degree_level
                        );

                        if (!isset($seenKeys[$key]) && $program->is_active && !$this->hasPengajuanAkreditasi($program)) {
                            $missingIds[] = $program->id;
                        }
                    }
                });

                $metrics['deactivated'] = count($missingIds);

                if (!$dryRun && $missingIds) {
                    StudyProgram::query()
                        ->whereIn('id', $missingIds)
                        ->update(['is_active' => false, 'updated_at' => now()]);
                }
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            DB::rollBack();
            $this->error('Sync gagal: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->table(['Metrik', 'Nilai'], [
            ['Baris CSV', $metrics['rows']],
            ['Universitas dibuat', $metrics['created_universities']],
            ['Prodi dibuat', $metrics['created']],
            ['Prodi diperbarui', $metrics['updated']],
            ['Prodi reaktif', $metrics['reactivated']],
            ['Prodi tidak berubah', $metrics['unchanged']],
            ['Prodi dilewati karena punya pengajuan', $metrics['locked_by_pengajuan']],
            ['Prodi dinonaktifkan karena tidak ada di CSV', $this->missingActionLabel($setNotActive, $keepMissing, $metrics['deactivated'])],
            ['Baris duplikat key', $metrics['duplicates']],
            ['Baris dilewati', $metrics['skipped']],
        ]);

        if ($errors) {
            $this->warn('Catatan error/skip:');
            foreach (array_slice($errors, 0, 25) as $error) {
                $this->line('- ' . $error);
            }
            if (count($errors) > 25) {
                $this->line('- ... ' . (count($errors) - 25) . ' catatan lain.');
            }
        }

        $this->info($dryRun ? 'Dry-run selesai.' : 'Sync prodi dari CSV selesai.');
        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    private function missingActionLabel(bool $setNotActive, bool $keepMissing, int $deactivated): string|int
    {
        if ($keepMissing) {
            return 'dilewati (--keep-missing)';
        }

        if (!$setNotActive) {
            return 'dilewati (pakai --set-not-active untuk menonaktifkan)';
        }

        return $deactivated;
    }

    /**
     * @return resource|false
     */
    private function openCsvStream(string $path)
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            return false;
        }

        if (str_starts_with($contents, "\xFF\xFE")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($contents, "\xFE\xFF")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16BE');
        } elseif (substr_count(substr($contents, 0, 512), "\0") > 10) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-16LE');
        } else {
            $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
        }

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            return false;
        }

        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }

    private function normalizeHeader(string $value): string
    {
        $value = str_replace("\0", '', $value);
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        return trim($value);
    }

    private function degreeLevelMap(): array
    {
        $map = [];

        foreach (DegreeLevel::query()->get() as $level) {
            foreach ([$level->alias, $level->code, $level->name] as $value) {
                $alias = $this->normalizeDegreeAlias((string) $value);
                if ($alias !== '') {
                    $map[$alias] = $level;
                }
            }
        }

        return $map;
    }

    private function csvValue(array $data, string $key): string
    {
        return trim((string) ($data[$key] ?? ''));
    }

    private function normalizeText(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function normalizeUniversityName(string $name): string
    {
        $name = $this->normalizeText($name);
        $name = preg_replace('/\s*\([^)]*\)/', '', $name) ?? $name;
        $name = str_replace(["'", 'AIi'], ['', 'Ali'], $name);

        $mappings = [
            'Universitas Islam Negeri Syekh AIi Hasan Ahmad Addary Padangsidimpuan' => 'Universitas Islam Negeri Syekh Ali Hasan Ahmad Addary Padangsidimpuan',
            'Universitas \'Aisyiyah Bandung' => 'Universitas Aisyiyah Bandung',
        ];

        return $mappings[$name] ?? trim($name);
    }

    private function normalizeDegreeAlias(string $degree): string
    {
        $degree = $this->normalizeText($degree);
        $upper = strtoupper($degree);

        return match ($upper) {
            'D-I' => 'D1',
            'D-II' => 'D2',
            'D-III' => 'D3',
            'D-IV', 'STR' => 'D4',
            'PROFESI' => 'Profesi',
            default => $degree,
        };
    }

    private function nullableDash(string $value): ?string
    {
        $value = $this->normalizeText($value);
        return $value === '' || $value === '-' ? null : $value;
    }

    private function parseDate(string $value): ?string
    {
        $value = $this->nullableDash($value);
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeStatus(string $value, ?string $date): string
    {
        $status = strtolower($this->nullableDash($value) ?? '');

        if ($status === 'masih berlaku' || str_contains($status, 'hari lagi')) {
            return 'Aktif';
        }

        if (str_contains($status, 'kedaluwarsa') || str_contains($status, 'kadaluarsa')) {
            return 'Kedaluwarsa';
        }

        if ($date) {
            return Carbon::parse($date)->isPast() ? 'Kedaluwarsa' : 'Aktif';
        }

        return 'Belum Terakreditasi';
    }

    private function detectBentukPt(string $universityName): ?string
    {
        foreach (['Universitas', 'Institut', 'Sekolah Tinggi', 'Politeknik', 'Akademi'] as $prefix) {
            if (Str::startsWith($universityName, $prefix)) {
                return $prefix;
            }
        }

        if (Str::startsWith($universityName, ['STMIK', 'STIKI', 'STKIP'])) {
            return 'Sekolah Tinggi';
        }

        return null;
    }

    private function generateUniversityCode(string $name): string
    {
        $words = preg_split('/\s+/', $name) ?: [];
        $code = '';

        foreach ($words as $word) {
            if (strlen($code) >= 10) {
                break;
            }
            $code .= strtoupper(substr($word, 0, 1));
        }

        return $code ?: strtoupper(substr($name, 0, 10));
    }

    private function generateProgramCode(string $programName): string
    {
        $slug = strtoupper(Str::slug($programName, ''));
        return substr($slug ?: 'N/A', 0, 20);
    }

    private function syncKey(string $universityName, string $programName, int $degreeLevelId): string
    {
        return mb_strtolower($this->normalizeText($universityName))
            . '|'
            . mb_strtolower($this->normalizeText($programName))
            . '|'
            . $degreeLevelId;
    }

    private function hasPengajuanAkreditasi(StudyProgram $studyProgram): bool
    {
        return DB::table('pengajuan_akreditasi')
            ->where('id_program_studi', $studyProgram->id)
            ->exists();
    }
}
