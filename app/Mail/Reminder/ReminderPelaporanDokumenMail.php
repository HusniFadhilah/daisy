<?php

namespace App\Mail\Reminder;

use App\Models\AsesmenUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderPelaporanDokumenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;
    public $pesanReminder;
    public $actionUrl;

    public function __construct(AsesmenUserRole $assignment, $pesanReminder)
    {
        $this->assignment = $assignment;
        $this->pesanReminder = $pesanReminder;
        $this->actionUrl = route('pelaporan.borang.show', $assignment->id);
    }

    public function build()
    {
        return $this->subject('Pengingat Pelaporan Validasi Dokumen')
            ->view('emails.reminder.reminder-pelaporan-dokumen');
    }
}
