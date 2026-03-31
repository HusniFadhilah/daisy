<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PenerimaanAkreditasiMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PengajuanAkreditasi $pengajuan;
    public PengajuanDokumen $dokumen;
    public string $actionUrl;

    public function __construct(PengajuanAkreditasi $pengajuan, PengajuanDokumen $dokumen)
    {
        $this->pengajuan = $pengajuan;
        $this->dokumen = $dokumen;
        $this->actionUrl = route('upps.penerimaan-permohonan.show', $pengajuan->id);
    }

    public function build(): self
    {
        $mail = $this->subject('Penerimaan Permohonan Akreditasi - ' . $this->pengajuan->studyProgram->name)
            ->with([
                'title' => 'Penerimaan Permohonan Akreditasi',
                'preheader' => 'Penerimaan permohonan akreditasi telah dikirim oleh LAMDEPILAR',
                'headerTitle' => 'Penerimaan Permohonan Akreditasi',
                'pengajuan' => $this->pengajuan,
                'dokumen' => $this->dokumen,
                'actionUrl' => $this->actionUrl,
            ])
            ->view('emails.pengajuan.penerimaan-akreditasi');

        if (!empty($this->dokumen->path_file) && Storage::disk('public')->exists($this->dokumen->path_file)) {
            $mail->attach(
                Storage::disk('public')->path($this->dokumen->path_file),
                [
                    'as' => $this->dokumen->original_filename ?: $this->dokumen->nama_file,
                    'mime' => $this->dokumen->mime_type ?: 'application/pdf',
                ]
            );
        }

        return $mail;
    }
}
