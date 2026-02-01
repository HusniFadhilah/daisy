<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailDeliveryService
{
    /**
     * Kirim Mailable ke banyak email (unik).
     *
     * @param array<int,string> $to
     * @param Mailable $mailable
     * @param array<int,string> $cc
     * @param array<int,string> $bcc
     * @param bool $useQueue
     * @return array{sent_to: array<int,string>, skipped: int}
     */
    public function sendToEmails(array $to, Mailable $mailable, array $cc = [], array $bcc = [], bool $useQueue = false): array
    {
        $to = $this->uniqueEmails($to);
        $cc = $this->uniqueEmails($cc);
        $bcc = $this->uniqueEmails($bcc);

        if (empty($to)) {
            return ['sent_to' => [], 'skipped' => 1];
        }

        try {
            $mail = Mail::to($to);

            if (!empty($cc)) {
                $mail->cc($cc);
            }

            if (!empty($bcc)) {
                $mail->bcc($bcc);
            }

            if ($useQueue) {
                $mail->queue($mailable);
            } else {
                $mail->send($mailable);
            }

            return ['sent_to' => $to, 'skipped' => 0];
        } catch (\Exception $e) {
            Log::error("MailDeliveryService sendToEmails failed: " . $e->getMessage());
            return ['sent_to' => [], 'skipped' => 1];
        }
    }

    /**
     * Normalisasi + dedup email.
     *
     * @param array<int,string> $emails
     * @return array<int,string>
     */
    public function uniqueEmails(array $emails): array
    {
        $clean = [];

        foreach ($emails as $email) {
            $email = is_string($email) ? strtolower(trim($email)) : '';
            if ($email !== '') {
                $clean[] = $email;
            }
        }

        return array_values(array_unique($clean));
    }
}
