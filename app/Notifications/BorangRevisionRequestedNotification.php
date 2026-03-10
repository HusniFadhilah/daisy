<?php
// app/Notifications/BorangRevisionRequestedNotification.php

namespace App\Notifications;

use App\Models\PengajuanAkreditasi;
use App\Models\BorangValidation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BorangRevisionRequestedNotification extends Notification implements ShouldQueue
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
        $revisionPoints = $this->validation->revision_points ?? [];
        $totalPoints = count($revisionPoints);

        return (new MailMessage)
            ->subject('Dokumen Perlu Revisi - ' . $this->pengajuan->nomor_pengajuan)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Dokumen LED untuk {$this->pengajuan->studyProgram->name} perlu direvisi.")
            ->line("**Validator:** {$this->validation->assignment->user->name}")
            ->line("**Total Poin Revisi:** {$totalPoints} bagian")
            ->line('')
            ->line('**Catatan Validator:**')
            ->line($this->validation->catatan_validator)
            ->line('')
            ->action('Lihat Detail Revisi', route('pengajuan.revision-notes', $this->pengajuan->id))
            ->line('Silakan lakukan perbaikan sesuai catatan validator dan submit kembali.')
            ->salutation('Salam, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        $revisionPoints = $this->validation->revision_points ?? [];

        return [
            'type' => 'borang_revision_requested',
            'title' => 'Dokumen Perlu Revisi',
            'message' => "Dokumen {$this->pengajuan->nomor_pengajuan} perlu direvisi (" . count($revisionPoints) . " bagian)",
            'pengajuan_id' => $this->pengajuan->id,
            'pengajuan_nomor' => $this->pengajuan->nomor_pengajuan,
            'validation_id' => $this->validation->id,
            'validator_name' => $this->validation->assignment->user->name,
            'total_revision_points' => count($revisionPoints),
            'action_url' => route('pengajuan.revision-notes', $this->pengajuan->id),
            'icon' => 'bi-exclamation-triangle',
            'color' => 'warning',
            'priority' => 'high',
        ];
    }
}
