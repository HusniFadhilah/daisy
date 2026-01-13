<?php
// app/Mail/BorangRevisionNotification.php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use App\Models\BorangValidation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorangRevisionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public PengajuanAkreditasi $pengajuan;
    public BorangValidation $validation;

    /**
     * Create a new message instance.
     */
    public function __construct(PengajuanAkreditasi $pengajuan, BorangValidation $validation)
    {
        $this->pengajuan = $pengajuan;
        $this->validation = $validation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[LAMDEPILAR] LED Perlu Revisi - ' . $this->pengajuan->nomor_pengajuan,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pengajuan.borang-revision-notification',
            with: [
                'pengajuan' => $this->pengajuan,
                'validation' => $this->validation,
                'studyProgram' => $this->pengajuan->studyProgram,
                'validator' => $this->validation->assignment->user ?? null,
                'revisionPoints' => $this->validation->revision_points ?? [],
                'catatanValidator' => $this->validation->catatan_validator,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
