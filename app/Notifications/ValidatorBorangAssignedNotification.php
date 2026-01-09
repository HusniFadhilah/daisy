<?php
// app/Notifications/ValidatorBorangAssignedNotification.php

namespace App\Notifications;

use App\Models\PengajuanAkreditasi;
use App\Models\AsesmenUserRole;
use App\Mail\PenawaranAsesmenMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ValidatorBorangAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Pengajuan instance
     */
    protected $pengajuan;

    /**
     * Assignment instance
     */
    protected $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment)
    {
        $this->pengajuan = $pengajuan;
        $this->assignment = $assignment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * ✅ REUSE existing PenawaranAsesmenMail
     */
    public function toMail(object $notifiable): PenawaranAsesmenMail
    {
        return new PenawaranAsesmenMail($this->assignment);
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'validator_borang_assigned',
            'title' => 'Penawaran Validasi Borang',
            'message' => "Anda ditugaskan sebagai validator untuk borang {$this->pengajuan->studyProgram->name}",
            'pengajuan_id' => $this->pengajuan->id,
            'pengajuan_nomor' => $this->pengajuan->nomor_pengajuan,
            'assignment_id' => $this->assignment->id,
            'asesmen_id' => $this->assignment->id_asesmen,
            'jenis_asesmen' => $this->assignment->jenis_asesmen,
            'program_studi' => $this->pengajuan->studyProgram->name,
            'university' => $this->pengajuan->studyProgram->university->name ?? null,
            'action_url' => route('penawaran.show', $this->assignment->token),
            'icon' => 'bi-clipboard-check',
            'color' => 'primary',
            'priority' => 'high',
        ];
    }

    /**
     * Get the database representation of the notification (alternative format).
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
