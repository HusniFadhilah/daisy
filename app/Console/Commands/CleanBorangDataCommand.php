<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BorangData;
use Illuminate\Support\Facades\DB;

class CleanBorangDataCommand extends Command
{
    protected $signature = 'borang:clean {pengajuan_id?}';
    protected $description = 'Clean empty table templates and duplicate borang_data';

    public function handle()
    {
        $pengajuanId = $this->argument('pengajuan_id');

        $this->info('🧹 Cleaning borang_data...');

        $this->cleanEmptyTemplates($pengajuanId);
        $this->cleanDuplicates($pengajuanId);

        $this->info('✅ Cleaning completed');
    }

    /* ============================================================
     | 1️⃣ Hapus template tabel kosong
     * ============================================================ */
    private function cleanEmptyTemplates($pengajuanId = null)
    {
        $query = BorangData::whereNotNull('nilai');

        if ($pengajuanId) {
            $query->where('id_pengajuan', $pengajuanId);
        }

        $cleaned = 0;

        foreach ($query->get() as $data) {
            $value = $data->nilai;

            if (strpos($value, '<table') !== false && !$this->hasRealData($value)) {
                $this->line("🗑 Empty template: {$data->dataset_id}");
                $data->delete();
                $cleaned++;
            }
        }

        $this->info("✅ Removed {$cleaned} empty templates");
    }

    private function hasRealData($tableHtml)
    {
        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $tableHtml, $cells);

        if (empty($cells[1])) {
            return false;
        }

        $filled = 0;

        foreach ($cells[1] as $cellHtml) {
            $text = trim(html_entity_decode(strip_tags($cellHtml)));
            $text = str_replace(['&nbsp;', ' '], '', $text);

            // Abaikan nomor urut
            if (is_numeric($text) && (int)$text <= 10) {
                continue;
            }

            if ($text !== '') {
                $filled++;
            }
        }

        return $filled >= 3;
    }

    /* ============================================================
     | 2️⃣ Hapus data duplikat (keep terbaru)
     * ============================================================ */
    private function cleanDuplicates($pengajuanId = null)
    {
        $this->info('🔍 Checking duplicates...');

        $duplicates = DB::table('borang_data')
            ->select('id_pengajuan', 'dataset_id', DB::raw('COUNT(*) as total'))
            ->when(
                $pengajuanId,
                fn($q) =>
                $q->where('id_pengajuan', $pengajuanId)
            )
            ->groupBy('id_pengajuan', 'dataset_id')
            ->having('total', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicates found');
            return;
        }

        $deleted = 0;

        foreach ($duplicates as $dup) {
            $records = BorangData::where('id_pengajuan', $dup->id_pengajuan)
                ->where('dataset_id', $dup->dataset_id)
                ->orderByDesc('updated_at')
                ->get();

            $keepId = $records->first()->id;

            foreach ($records->skip(1) as $record) {
                $this->line("🗑 Duplicate {$record->id} (keep {$keepId})");
                $record->delete();
                $deleted++;
            }
        }

        $this->info("✅ Removed {$deleted} duplicate records");
    }
}
