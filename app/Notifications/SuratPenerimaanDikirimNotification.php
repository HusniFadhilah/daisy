<?php
// app/Notifications/SuratPenerimaanDikirimNotification.php

namespace App\Notifications;

use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuratPenerimaanDikirimNotification extends Notification
{
    use Queueable;

    protected $pengajuan;
    protected $dokumen;

    public function __construct(PengajuanAkreditasi $pengajuan, PengajuanDokumen $dokumen)
    {
        $this->pengajuan = $pengajuan;
        $this->dokumen = $dokumen;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $actionUrl = route('pengajuan.show', $this->pengajuan->id);

        return (new MailMessage)
            ->subject('Penerimaan Permohonan Akreditasi - ' . $this->pengajuan->studyProgram->name)
            ->view('emails.pengajuan.penerimaan-akreditasi', [
                'title' => 'Penerimaan Permohonan Akreditasi',
                'preheader' => 'Penerimaan permohonan akreditasi telah dikirim oleh LAMDEPILAR',
                'headerTitle' => 'Penerimaan Permohonan Akreditasi',
                'pengajuan' => $this->pengajuan,
                'dokumen' => $this->dokumen,
                'actionUrl' => $actionUrl,
                'notifiableName' => $notifiable->name,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'surat_penerimaan_dikirim',
            'pengajuan_id' => $this->pengajuan->id,
            'nomor_pengajuan' => $this->pengajuan->nomor_pengajuan,
            'dokumen_id' => $this->dokumen->id,
            'message' => 'Penerimaan permohonan akreditasi telah dikirim',
        ];
    }
}
