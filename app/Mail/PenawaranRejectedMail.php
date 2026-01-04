<?php

namespace App\Mail;

use App\Models\AsesmenUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PenawaranRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;
    public $asesmen;
    public $user;
    public $role;
    public $adminEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(AsesmenUserRole $assignment, $adminEmail = null)
    {
        $this->assignment = $assignment;
        $this->asesmen = $assignment->asesmen;
        $this->user = $assignment->user;
        $this->role = $assignment->role;
        $this->adminEmail = $adminEmail ?? config('mail.admin_email', 'admin@lamdepilar.or.id');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $jenisAsesmen = strtoupper($this->assignment->jenis_asesmen);

        return $this->subject("❌ Penawaran Ditolak - {$this->user->name} - {$jenisAsesmen}")
            ->markdown('emails.asesmen.penawaran-rejected', [
                'assignment' => $this->assignment,
                'asesmen' => $this->asesmen,
                'user' => $this->user,
                'role' => $this->role,
                'jenisAsesmen' => $jenisAsesmen,
                'reassignUrl' => route('asesmen.show', $this->asesmen->id),
            ]);
    }
}
