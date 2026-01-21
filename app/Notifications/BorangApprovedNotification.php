<?php
// app/Notifications/BorangApprovedNotification.php

namespace App\Notifications;

use App\Models\PengajuanAkreditasi;
use App\Models\BorangValidation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BorangApprovedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Borang Disetujui - ' . $this->pengajuan->nomor_pengajuan)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Selamat! Borang LED untuk {$this->pengajuan->studyProgram->name} telah disetujui oleh validator.")
            ->line("**Validator:** {$this->validation->assignment->user->name}")
            ->line("**Tanggal Persetujuan:** {$this->validation->assignment->approved_at->format('d M Y H:i')}")
            ->line('')
            ->line('**Catatan Validator:**')
            ->line($this->validation->catatan_validator ?? 'Borang memenuhi standar dan siap untuk proses selanjutnya.')
            ->line('')
            ->action('Lihat Detail', route('pengajuan.show', $this->pengajuan->id))
            ->line('Permohonan akreditasi akan dilanjutkan ke tahap berikutnya.')
            ->salutation('Salam, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borang_approved',
            'title' => 'Borang Disetujui',
            'message' => "Borang {$this->pengajuan->nomor_pengajuan} telah disetujui validator",
            'pengajuan_id' => $this->pengajuan->id,
            'pengajuan_nomor' => $this->pengajuan->nomor_pengajuan,
            'validation_id' => $this->validation->id,
            'validator_name' => $this->validation->assignment->user->name,
            'approved_at' => $this->validation->assignment->approved_at,
            'action_url' => route('pengajuan.show', $this->pengajuan->id),
            'icon' => 'bi-check-circle',
            'color' => 'success',
            'priority' => 'high',
        ];
    }
}
