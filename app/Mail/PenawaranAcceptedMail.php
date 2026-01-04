<?php

namespace App\Mail;

use App\Models\AsesmenUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PenawaranAcceptedMail extends Mailable
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

        return $this->subject("✅ Penawaran Diterima - {$this->user->name} - {$jenisAsesmen}")
            ->markdown('emails.asesmen.penawaran-accepted', [
                'assignment' => $this->assignment,
                'asesmen' => $this->asesmen,
                'user' => $this->user,
                'role' => $this->role,
                'jenisAsesmen' => $jenisAsesmen,
                'dashboardUrl' => route('ak.berkas.show', $this->asesmen->id),
            ]);
    }
}
