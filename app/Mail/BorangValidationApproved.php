<?php

namespace App\Mail;

use App\Models\BorangValidation;
use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BorangValidationApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PengajuanAkreditasi $pengajuan;
    public BorangValidation $validation;
    public string $actionUrl;

    public function __construct(PengajuanAkreditasi $pengajuan, BorangValidation $validation)
    {
        $this->pengajuan = $pengajuan->loadMissing([
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ]);
        $this->validation = $validation;
        $this->actionUrl = route('upps.validasi-dokumen.show', $pengajuan->id);
    }

    public function build(): self
    {
        return $this->subject('Dokumen Akreditasi Disetujui')
            ->with([
                'title' => 'Dokumen Akreditasi Disetujui',
                'preheader' => 'Dokumen akreditasi Anda telah disetujui.',
                'headerTitle' => 'Dokumen Akreditasi Disetujui',
                'pengajuan' => $this->pengajuan,
                'validation' => $this->validation,
                'actionUrl' => $this->actionUrl,
            ])
            ->view('emails.pengajuan.borang-decision-approved');
    }
}
