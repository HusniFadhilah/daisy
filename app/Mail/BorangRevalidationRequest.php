<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BorangRevalidationRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PengajuanAkreditasi $pengajuan;
    public string $actionUrl;

    public function __construct(PengajuanAkreditasi $pengajuan)
    {
        $this->pengajuan = $pengajuan->loadMissing([
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ]);
        $this->actionUrl = route('upps.validasi-dokumen.show', $pengajuan->id);
    }

    public function build(): self
    {
        return $this->subject('[LAMDEPILAR] Permintaan Review Ulang Dokumen - ' . $this->pengajuan->nomor_pengajuan)
            ->with([
                'title' => 'Permintaan Review Ulang Dokumen',
                'preheader' => 'Dokumen hasil revisi siap untuk direview ulang.',
                'headerTitle' => 'Permintaan Review Ulang Dokumen',
                'pengajuan' => $this->pengajuan,
                'actionUrl' => $this->actionUrl,
            ])
            ->view('emails.pengajuan.borang-revalidation-request');
    }
}
