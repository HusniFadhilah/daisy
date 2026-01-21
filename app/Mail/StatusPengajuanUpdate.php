<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StatusPengajuanUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $statusBaru;
    public $pesan;

    public function __construct(PengajuanAkreditasi $pengajuan, $statusBaru, $pesan = null)
    {
        $this->pengajuan = $pengajuan;
        $this->statusBaru = $statusBaru;
        $this->pesan = $pesan;
    }

    public function build()
    {
        return $this->subject('Update Status Permohonan Akreditasi - ' . $this->pengajuan->nomor_pengajuan)
            ->view('emails.status-pengajuan-update');
    }
}
