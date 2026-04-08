<?php

namespace App\Mail;

use App\Models\PengajuanPembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PembayaranVerifiedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PengajuanPembayaran $pembayaran;
    public $pengajuan;
    public bool $isVerified;
    public bool $isBanding;
    public string $jenisLabel;
    public string $actionUrl;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(PengajuanPembayaran $pembayaran, bool $isVerified = true)
    {
        $this->pembayaran = $pembayaran->loadMissing([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
        ]);

        $this->pengajuan = $this->pembayaran->pengajuan;
        $this->isVerified = $isVerified;
        $this->isBanding = $this->pembayaran->jenis_pembayaran == 'banding';
        $this->jenisLabel = $this->isBanding ? 'Banding' : 'Akreditasi';
        $route = $this->isBanding ? 'upps.pelaksanaan-banding.show' : 'upps.validasi-pembayaran.show';
        $this->actionUrl = route($route, $this->isBanding ? $this->pengajuan->id : $pembayaran->id);
    }

    public function build(): self
    {
        if ($this->isVerified) {
            $subject = $this->isBanding
                ? 'Pembayaran Banding Tervalidasi'
                : 'Pembayaran Akreditasi Tervalidasi';
        } else {
            $subject = $this->isBanding
                ? 'Pembayaran Banding Belum Tervalidasi - Silakan Upload Ulang'
                : 'Pembayaran Belum Tervalidasi - Silakan Upload Ulang';
        }

        return $this->subject($subject)
            ->with([
                'title' => $this->isVerified
                    ? 'Pembayaran ' . $this->jenisLabel . ' Tervalidasi'
                    : 'Bukti Pembayaran ' . $this->jenisLabel . ' Perlu Upload Ulang',
                'preheader' => $this->isVerified
                    ? 'Pembayaran ' . strtolower($this->jenisLabel) . ' Anda telah divalidasi.'
                    : 'Bukti pembayaran ' . strtolower($this->jenisLabel) . ' Anda perlu diupload ulang.',
                'headerTitle' => $this->isVerified
                    ? 'Pembayaran ' . $this->jenisLabel . ' Tervalidasi'
                    : 'Bukti Pembayaran ' . $this->jenisLabel . ' Perlu Upload Ulang',
                'pembayaran' => $this->pembayaran,
                'pengajuan' => $this->pengajuan,
                'isVerified' => $this->isVerified,
                'isBanding' => $this->isBanding,
                'jenisLabel' => $this->jenisLabel,
                'actionUrl' => $this->actionUrl,
            ])
            ->view('emails.pengajuan.pembayaran-verified');
    }
}
