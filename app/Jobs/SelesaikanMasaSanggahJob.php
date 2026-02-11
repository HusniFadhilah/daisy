<?php

namespace App\Jobs;

use App\Models\PengajuanAkreditasi;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SelesaikanMasaSanggahJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pengajuanId;

    public function __construct($pengajuanId)
    {
        $this->pengajuanId = $pengajuanId;
    }

    public function handle()
    {
        $pengajuan = PengajuanAkreditasi::find($this->pengajuanId);

        if (!$pengajuan) return;

        if (
            $pengajuan->status === PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI
        ) {

            $statusFrom = $pengajuan->status;

            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            ]);

            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                'changed_by'  => $pengajuan->id_de_assigned ?? $pengajuan->id_user_pengaju,
                'keterangan'  => 'Masa sanggah otomatis selesai (1 menit).',
                'changed_at'  => now(),
            ]);
        }
    }
}
