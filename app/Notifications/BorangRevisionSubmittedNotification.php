<?php
// app/Notifications/BorangRevisionSubmittedNotification.php

namespace App\Notifications;

use App\Models\PengajuanAkreditasi;
use App\Models\BorangValidation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BorangRevisionSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pengajuan;
    protected $validation;

    public function __construct(PengajuanAkreditasi $pengajuan, BorangValidation $validation)
    {
        $this->pengajuan = $pengajuan;
        $this->validation = $validation;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $latestRevision = $this->pengajuan->getLatestBorangRevision();
        $revisionNumber = $latestRevision ? $latestRevision->revision_number : 1;

        return (new MailMessage)
            ->subject('Revisi Dokumen Diterima - ' . $this->pengajuan->nomor_pengajuan)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Prodi telah submit revisi dokumen {$this->pengajuan->studyProgram->name}.")
            ->line("**Revisi ke:** #{$revisionNumber}")
            ->line("**Tanggal Submit:** {$latestRevision->revised_at->locale('id')->translatedFormat('d M Y H:i')}")
            ->line('')
            ->line('**Catatan dari Prodi:**')
            ->line($latestRevision->revision_notes ?? 'Revisi sesuai catatan validator.')
            ->line('')
            ->action('Review Revisi', route('validator.borang.show', $this->validation->assignment->id))
            ->line('Silakan mereview kembali revisi yang telah dilakukan.')
            ->salutation('Salam, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        $latestRevision = $this->pengajuan->getLatestBorangRevision();

        return [
            'type' => 'borang_revision_submitted',
            'title' => 'Revisi dokumen Diterima',
            'message' => "Prodi telah submit revisi #{$latestRevision->revision_number} untuk borang {$this->pengajuan->nomor_pengajuan}",
            'pengajuan_id' => $this->pengajuan->id,
            'pengajuan_nomor' => $this->pengajuan->nomor_pengajuan,
            'validation_id' => $this->validation->id,
            'revision_number' => $latestRevision->revision_number,
            'action_url' => route('validator.borang.show', $this->validation->assignment->id),
            'icon' => 'bi-arrow-repeat',
            'color' => 'info',
            'priority' => 'high',
        ];
    }
}
