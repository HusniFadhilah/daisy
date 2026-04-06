<?php

namespace App\Mail;

use App\Models\BorangValidation;
use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BorangRevisionNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PengajuanAkreditasi $pengajuan;
    public BorangValidation $validation;
    public string $actionUrl;
    public int $revisionCount;

    public function __construct(PengajuanAkreditasi $pengajuan, BorangValidation $validation)
    {
        $this->pengajuan = $pengajuan->loadMissing([
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ]);
        $this->validation = $validation;
        $this->actionUrl = route('upps.validasi-dokumen.show', $pengajuan->id);

        $points = $validation->getNeedsRevisionItems();
        $this->revisionCount = is_array($points) ? collect($points)->sum(fn($items) => count($items)) : 0;
    }

    public function build(): self
    {
        return $this->subject('Dokumen Akreditasi Perlu Revisi')
            ->with([
                'title' => 'Dokumen Akreditasi Perlu Revisi',
                'preheader' => 'Dokumen akreditasi Anda memerlukan revisi.',
                'headerTitle' => 'Dokumen Akreditasi Perlu Revisi',
                'pengajuan' => $this->pengajuan,
                'validation' => $this->validation,
                'actionUrl' => $this->actionUrl,
                'revisionCount' => $this->revisionCount,
            ])
            ->view('emails.pengajuan.borang-decision-revision');
    }
}
