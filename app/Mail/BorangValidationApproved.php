<?php
// app/Mail/BorangValidationApproved.php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use App\Models\BorangValidation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorangValidationApproved extends Mailable
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
            subject: '[LAMDEPILAR] LED Disetujui - ' . $this->pengajuan->nomor_pengajuan,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.validator.borang-validation-approved',
            with: [
                'pengajuan' => $this->pengajuan,
                'validation' => $this->validation,
                'studyProgram' => $this->pengajuan->studyProgram,
                'validator' => $this->validation->assignment->user ?? null,
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
