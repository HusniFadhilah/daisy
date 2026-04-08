<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanPembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoicePembayaranMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PengajuanPembayaran $pembayaran;
    public PengajuanAkreditasi $pengajuan;
    public ?PengajuanDokumen $templateFormulirPembayaran;
    public string $actionUrl;
    public bool $isBanding;
    public string $jenisLabel;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        PengajuanPembayaran $pembayaran,
        ?PengajuanDokumen $templateFormulirPembayaran = null
    ) {
        $this->pembayaran = $pembayaran->loadMissing([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
        ]);

        $this->pengajuan = $this->pembayaran->pengajuan;
        $this->templateFormulirPembayaran = $templateFormulirPembayaran;
        $this->isBanding = $this->pembayaran->jenis_pembayaran == 'banding';
        $route = $this->isBanding ? 'upps.pelaksanaan-banding.show' : 'upps.validasi-pembayaran.show';
        $this->actionUrl = route($route, $this->isBanding ? $this->pengajuan->id : $pembayaran->id);
        $this->jenisLabel = $this->isBanding ? 'Banding' : 'Akreditasi';
    }

    public function build(): self
    {
        $subject = $this->isBanding
            ? 'Invoice Pembayaran Banding - ' . $this->pembayaran->nomor_invoice
            : 'Invoice Pembayaran Akreditasi - ' . $this->pembayaran->nomor_invoice;

        $mail = $this->subject($subject)
            ->view('emails.pengajuan.invoice-pembayaran')
            ->with([
                'title' => 'Invoice Pembayaran ' . $this->jenisLabel,
                'preheader' => $this->isBanding
                    ? 'Invoice pembayaran banding telah diterbitkan'
                    : 'Invoice pembayaran akreditasi telah diterbitkan',
                'headerTitle' => 'Invoice Pembayaran ' . $this->jenisLabel,
                'pembayaran' => $this->pembayaran,
                'pengajuan' => $this->pengajuan,
                'templateFormulirPembayaran' => $this->templateFormulirPembayaran,
                'actionUrl' => $this->actionUrl,
                'isBanding' => $this->isBanding,
                'jenisLabel' => $this->jenisLabel,
            ]);

        if (
            $this->templateFormulirPembayaran &&
            !empty($this->templateFormulirPembayaran->path_file) &&
            Storage::disk('public')->exists($this->templateFormulirPembayaran->path_file)
        ) {
            $mail->attach(
                Storage::disk('public')->path($this->templateFormulirPembayaran->path_file),
                [
                    'as' => $this->templateFormulirPembayaran->original_filename
                        ?: $this->templateFormulirPembayaran->nama_file,
                    'mime' => $this->templateFormulirPembayaran->mime_type ?: 'application/octet-stream',
                ]
            );
        }

        return $mail;
    }
}
