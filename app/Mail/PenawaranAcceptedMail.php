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
    public $dashboardUrl;

    public function __construct(AsesmenUserRole $assignment, $adminEmail = null)
    {
        $this->assignment   = $assignment;
        $this->asesmen      = $assignment->asesmen;
        $this->user         = $assignment->user;
        $this->role         = $assignment->role;
        $this->adminEmail   = $adminEmail ?? config('mail.admin_email', 'admin@lamdepilar.or.id');
        $this->dashboardUrl = route('ak.berkas.show', $assignment->asesmen->id);
    }

    public function build()
    {
        $jenisAsesmen = strtoupper($this->assignment->jenis_asesmen);

        return $this->subject("Penawaran Diterima - {$this->user->name} - {$jenisAsesmen}")
            ->view('emails.asesmen.penawaran-accepted')
            ->with(['jenisAsesmen' => $jenisAsesmen]);
    }
}
