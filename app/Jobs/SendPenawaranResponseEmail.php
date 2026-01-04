<?php

namespace App\Jobs;

use App\Mail\PenawaranAcceptedMail;
use App\Mail\PenawaranRejectedMail;
use App\Models\AsesmenUserRole;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPenawaranResponseEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $assignment;
    public $responseType; // 'accepted' or 'rejected'
    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(AsesmenUserRole $assignment, string $responseType)
    {
        $this->assignment = $assignment;
        $this->responseType = $responseType;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // ✅ 1. Email ke User (konfirmasi)
            $this->sendToUser();

            // ✅ 2. Email ke Admin (notifikasi)
            $this->sendToAdmin();
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email response penawaran", [
                'assignment_id' => $this->assignment->id,
                'response_type' => $this->responseType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // ✅ TIDAK throw exception
        }
    }

    /**
     * Send confirmation email to user
     */
    private function sendToUser()
    {
        try {
            $user = $this->assignment->user;

            if (!$user || !$user->email) {
                Log::warning("User tidak memiliki email untuk konfirmasi", [
                    'assignment_id' => $this->assignment->id,
                ]);
                return;
            }

            if ($this->responseType === 'accepted') {
                $mailClass = new PenawaranAcceptedMail($this->assignment);
                $subject = "Konfirmasi: Penawaran Diterima";
            } else {
                $mailClass = new PenawaranRejectedMail($this->assignment);
                $subject = "Konfirmasi: Penawaran Ditolak";
            }

            Mail::to($user->email)
                ->send($mailClass);

            Log::info("Email konfirmasi {$this->responseType} berhasil dikirim ke user", [
                'user_email' => $user->email,
                'assignment_id' => $this->assignment->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email ke user", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification email to admin
     */
    private function sendToAdmin()
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@lamdepilar.or.id');

            if ($this->responseType === 'accepted') {
                $mailClass = new PenawaranAcceptedMail($this->assignment, $adminEmail);
            } else {
                $mailClass = new PenawaranRejectedMail($this->assignment, $adminEmail);
            }

            Mail::to($adminEmail)
                ->send($mailClass);

            Log::info("Email notifikasi {$this->responseType} berhasil dikirim ke admin", [
                'admin_email' => $adminEmail,
                'assignment_id' => $this->assignment->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email ke admin", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Job SendPenawaranResponseEmail failed", [
            'assignment_id' => $this->assignment->id,
            'response_type' => $this->responseType,
            'error' => $exception->getMessage(),
        ]);
    }
}
