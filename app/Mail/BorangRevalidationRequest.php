<?php
// app/Mail/BorangRevalidationRequest.php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorangRevalidationRequest extends Mailable
{
    use Queueable, SerializesModels;

    public PengajuanAkreditasi $pengajuan;

    /**
     * Create a new message instance.
     */
    public function __construct(PengajuanAkreditasi $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[LAMDEPILAR] Permintaan Review Ulang LED - ' . $this->pengajuan->nomor_pengajuan,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pengajuan.borang-revalidation-request',
            with: [
                'pengajuan' => $this->pengajuan,
                'studyProgram' => $this->pengajuan->studyProgram,
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
