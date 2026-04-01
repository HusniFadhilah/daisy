<?php

namespace App\Mail\Reminder;

use App\Models\AsesmenUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderPelaporanMail extends Mailable
{
    use Queueable, SerializesModels;

    public AsesmenUserRole $assignment;
    public string $pesanReminder;
    public string $actionUrl;

    public function __construct(
        AsesmenUserRole $assignment,
        string $pesanReminder,
        string $subject,
        string $actionUrl
    ) {
        $this->assignment    = $assignment;
        $this->pesanReminder = $pesanReminder;
        $this->actionUrl     = $actionUrl;
        $this->subject($subject);
    }

    public function build(): self
    {
        return $this->view('emails.reminder.reminder-pelaporan-generic');
    }
}
