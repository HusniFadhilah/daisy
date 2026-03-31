<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BorangTemplateSentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PengajuanAkreditasi $pengajuan;
    public PengajuanDokumen $dokumen;
    public string $metode;
    public string $actionUrl;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        PengajuanAkreditasi $pengajuan,
        PengajuanDokumen $dokumen,
        string $metode
    ) {
        $this->pengajuan = $pengajuan->loadMissing([
            'pengaju',
            'studyProgram.degreeLevel',
            'studyProgram.university',
        ]);

        $this->dokumen = $dokumen;
        $this->metode = $metode;
        $this->actionUrl = route('pengajuan.show', $pengajuan->id);
    }

    public function build(): self
    {
        $subject = 'Templat Dokumen Akreditasi - ' . $this->pengajuan->nomor_pengajuan;

        $mail = $this->subject($subject)
            ->view('emails.pengajuan.borang-template-sent')
            ->with([
                'title' => 'Templat Dokumen Akreditasi',
                'preheader' => 'Templat dokumen akreditasi telah dikirim oleh LAMDEPILAR',
                'headerTitle' => 'Templat Dokumen Akreditasi',
                'pengajuan' => $this->pengajuan,
                'dokumen' => $this->dokumen,
                'metode' => $this->metode,
                'actionUrl' => $this->actionUrl,
            ]);

        if (
            $this->metode !== 'link' &&
            !empty($this->dokumen->path_file) &&
            Storage::disk('public')->exists($this->dokumen->path_file)
        ) {
            $mail->attach(
                Storage::disk('public')->path($this->dokumen->path_file),
                [
                    'as' => $this->dokumen->original_filename ?: $this->dokumen->nama_file,
                    'mime' => $this->dokumen->mime_type ?: 'application/octet-stream',
                ]
            );
        }

        return $mail;
    }
}
