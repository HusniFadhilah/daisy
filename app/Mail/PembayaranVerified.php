<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PembayaranVerified extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $isVerified;

    public function __construct(PengajuanAkreditasi $pengajuan, $isVerified = true)
    {
        $this->pengajuan = $pengajuan;
        $this->isVerified = $isVerified;
    }

    public function build()
    {
        $subject = $this->isVerified
            ? 'Pembayaran Diverifikasi - Upload Borang Final'
            : 'Pembayaran Belum Terverifikasi - Silakan Upload Ulang';

        return $this->subject($subject)
            ->view('emails.pengajuan.pembayaran-verified');
    }
}
