<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PengingatAkreditasi extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $pesanPengingat;

    public function __construct(PengajuanAkreditasi $pengajuan, $pesanPengingat)
    {
        $this->pengajuan = $pengajuan;
        $this->pesanPengingat = $pesanPengingat;
    }

    public function build()
    {
        return $this->subject('Pengingat: Masa Akreditasi Program Studi')
            ->view('emails.pengingat-akreditasi');
    }
}
