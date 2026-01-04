<?php

namespace App\Jobs;

use App\Mail\PenawaranAsesmenMail;
use App\Models\AsesmenUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPenawaranAsesmenEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $assignment;
    public $tries = 3; // Retry 3 kali jika gagal
    public $timeout = 60; // Timeout 60 detik

    /**
     * Create a new job instance.
     */
    public function __construct(AsesmenUserRole $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $user = $this->assignment->user;

            if (!$user || !$user->email) {
                Log::warning("User tidak memiliki email", [
                    'assignment_id' => $this->assignment->id,
                    'user_id' => $this->assignment->id_user,
                ]);
                return;
            }

            Mail::to($user->email)
                ->send(new PenawaranAsesmenMail($this->assignment));
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email penawaran asesmen", [
                'assignment_id' => $this->assignment->id,
                'user_id' => $this->assignment->id_user,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // ✅ TIDAK throw exception agar process tetap lanjut
            // Process assignment tetap berhasil meskipun email gagal
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Job SendPenawaranAsesmenEmail failed setelah {$this->tries} percobaan", [
            'assignment_id' => $this->assignment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
