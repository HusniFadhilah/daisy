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

    public function __construct(
        protected int $pengajuanId,
        protected string $mode = 'delay' // delay|sweep
    ) {}

    public function handle()
    {
        $pengajuan = PengajuanAkreditasi::find($this->pengajuanId);
        if (!$pengajuan) return;

        // Guard status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI) return;

        // Kalau mode delay, job seharusnya dieksekusi saat endAt.
        // Tapi tetap cek deadline agar aman (misal job kepanggil cepat karena waktu server berubah).
        if ($pengajuan->tanggal_masa_sanggah_selesai && now()->lt($pengajuan->tanggal_masa_sanggah_selesai)) {
            return;
        }

        $statusFrom = $pengajuan->status;

        // Aman dari race condition
        $updated = PengajuanAkreditasi::where('id', $pengajuan->id)
            ->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
            ->update(['status' => PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI]);

        if (!$updated) return;

        $pengajuan->refresh();
        $pengajuan->statusLog()->create([
            'status_from' => $statusFrom,
            'status_to'   => PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            'changed_by'  => $pengajuan->id_de_assigned ?? $pengajuan->id_user_pengaju,
            'keterangan'  => $this->mode === 'sweep'
                ? 'Masa sanggah otomatis selesai (scheduler).'
                : 'Masa sanggah otomatis selesai (delayed job).',
            'changed_at'  => now(),
        ]);
    }
}
