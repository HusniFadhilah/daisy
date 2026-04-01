<?php

namespace App\Mail\Reminder;

use App\Models\AsesmenUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderProgressAsesmenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;
    public $pesanReminder;
    public $actionUrl;
    public $asesmenLabel;
    public $asesmenRoleLabel;
    public $asesmenActionLabel;

    public function __construct(AsesmenUserRole $assignment, string $pesanReminder)
    {
        $assignment->loadMissing([
            'user',
            'role_selected',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
        ]);

        $this->assignment = $assignment;
        $this->pesanReminder = $pesanReminder;
        $this->asesmenLabel = $assignment->jenis_asesmen_label;
        $this->asesmenRoleLabel = $assignment->jenis_asesmen_role_label;
        $this->asesmenActionLabel = $assignment->jenis_asesmen_action_label;
        $this->actionUrl = $assignment->route_penawaran;
    }

    public function build()
    {
        return $this->subject("Pengingat Tindak Lanjut {$this->asesmenActionLabel}")
            ->view('emails.reminder.reminder-progress-asesmen');
    }
}
