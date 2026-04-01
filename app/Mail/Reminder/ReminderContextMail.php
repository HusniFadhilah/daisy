<?php

namespace App\Mail\Reminder;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderContextMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $pesanReminder;
    public string $actionUrl;
    public string $actionLabel;
    public ?string $contextInfo;
    public string $headerTitle;
    public string $preheader;

    public function __construct(
        string  $recipientName,
        string  $pesanReminder,
        string  $subject,
        string  $actionUrl,
        string  $actionLabel   = 'Lihat Detail',
        ?string $contextInfo   = null,
        ?string $headerTitle   = null,  // default ke subject jika tidak diisi
        ?string $preheader     = null,  // default ke subject jika tidak diisi
    ) {
        $this->recipientName = $recipientName;
        $this->pesanReminder = $pesanReminder;
        $this->actionUrl     = $actionUrl;
        $this->actionLabel   = $actionLabel;
        $this->contextInfo   = $contextInfo;
        $this->headerTitle   = $headerTitle ?? $subject;
        $this->preheader     = $preheader   ?? $subject;
        $this->subject($subject);
    }

    public function build(): self
    {
        return $this->view('emails.reminder.reminder-context');
    }
}
