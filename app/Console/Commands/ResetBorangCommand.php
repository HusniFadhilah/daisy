<?php
// app/Console/Commands/ResetBorangCommand.php

namespace App\Console\Commands;

use App\Models\BorangData;
use App\Models\BorangImport;
use Illuminate\Console\Command;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResetBorangCommand extends Command
{
    protected $signature = 'borang:reset {pengajuan_id} {--force}';
    protected $description = 'Reset borang data for a pengajuan';

    public function handle()
    {
        $pengajuanId = $this->argument('pengajuan_id');
        $force = $this->option('force');

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($pengajuanId);

            $this->info("Pengajuan: {$pengajuan->nomor_pengajuan}");
            $this->info("Status: {$pengajuan->status}");

            // Count data
            $totalData = BorangData::where('id_pengajuan', $pengajuanId)->count();
            $totalImports = BorangImport::where('id_pengajuan', $pengajuanId)->count();

            $this->warn("Will delete:");
            $this->line("  - {$totalData} borang data entries");
            $this->line("  - {$totalImports} import records");

            if (!$force && !$this->confirm('Continue?')) {
                $this->info('Cancelled.');
                return 0;
            }

            DB::beginTransaction();

            // Delete data
            $deletedData = BorangData::where('id_pengajuan', $pengajuanId)->delete();
            $this->info("✅ Deleted {$deletedData} data entries");

            // Delete imports
            $imports = BorangImport::where('id_pengajuan', $pengajuanId)->get();
            foreach ($imports as $import) {
                if ($import->stored_path && Storage::disk('local')->exists($import->stored_path)) {
                    Storage::disk('local')->delete($import->stored_path);
                }
                $import->delete();
            }
            $this->info("✅ Deleted {$imports->count()} imports");

            // Mark drafts as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuanId)
                ->where('jenis_dokumen', 'draft_borang')
                ->update(['is_latest' => false]);

            // Update status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'borang_dikirim',
                'tanggal_draft_borang' => null,
                'tanggal_borang_final' => null,
            ]);

            // Log
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuanId,
                'status_from' => $oldStatus,
                'status_to' => 'borang_dikirim',
                'changed_by' => 1, // System
                'changed_at' => now(),
                'keterangan' => "Borang direset via command. Data: {$deletedData}, Imports: {$imports->count()}",
            ]);

            DB::commit();

            $this->info('✅ Borang reset successfully!');
            return 0;
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            $this->error('Failed: ' . $e->getMessage());
            return 1;
        }
    }
}
