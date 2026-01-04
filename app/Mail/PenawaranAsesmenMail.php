<?php

namespace App\Mail;

use App\Models\Asesmen;
use App\Models\AsesmenUserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PenawaranAsesmenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;
    public $asesmen;
    public $user;
    public $role;

    /**
     * Create a new message instance.
     */
    public function __construct(AsesmenUserRole $assignment)
    {
        $this->assignment = $assignment;
        $this->asesmen = $assignment->asesmen;
        $this->user = $assignment->user;
        $this->role = $assignment->role;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $jenisAsesmen = strtoupper($this->assignment->jenis_asesmen);

        return $this->subject("Penawaran {$this->role->alias} - {$jenisAsesmen} - {$this->asesmen->name}")
            ->markdown('emails.asesmen.penawaran-assignment', [
                'assignment' => $this->assignment,
                'asesmen' => $this->asesmen,
                'user' => $this->user,
                'role' => $this->role,
                'jenisAsesmen' => $jenisAsesmen,
                'acceptUrl' => route('ak.penawaran.detail', $this->asesmen->id),
            ]);
    }
}
