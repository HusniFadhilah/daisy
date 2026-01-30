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
        return (new MailMessage)
            ->subject('Surat Penerimaan Permohonan Akreditasi - ' . $this->pengajuan->studyProgram->name)
            ->greeting('Kepada Yth. ' . $notifiable->name)
            ->line('Surat penerimaan permohonan akreditasi telah dikirimkan oleh LAM-DEPILaR.')
            ->line('**Detail Permohonan:**')
            ->line('- Program Studi: ' . $this->pengajuan->studyProgram->full_name)
            ->line('- Nomor Permohonan: ' . $this->pengajuan->nomor_pengajuan)
            ->line('- Tanggal Dikirim: ' . $this->dokumen->created_at->format('d M Y H:i'))
            ->action('Lihat & Download Surat Penerimaan', route('pengajuan.show', $this->pengajuan->id))
            ->line('Silakan login ke sistem untuk mengunduh surat penerimaan.')
            ->line('Terima kasih atas perhatian Anda.')
            ->salutation('Hormat kami, LAM-DEPILaR');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'surat_penerimaan_dikirim',
            'pengajuan_id' => $this->pengajuan->id,
            'nomor_pengajuan' => $this->pengajuan->nomor_pengajuan,
            'dokumen_id' => $this->dokumen->id,
            'message' => 'Surat penerimaan permohonan akreditasi telah dikirim',
        ];
    }
}
