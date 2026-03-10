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
    public $jenisAsesmen;

    public function __construct(AsesmenUserRole $assignment, $adminEmail = null)
    {
        $this->assignment   = $assignment;
        $this->jenisAsesmen = $this->assignment->jenis_asesmen;
        $route = $this->assignment->route_penawaran;
        $this->asesmen      = $assignment->asesmen;
        $this->user         = $assignment->user;
        $this->role         = $assignment->role;
        $this->adminEmail   = $adminEmail ?? config('mail.admin_email', 'admin@lamdepilar.or.id');
        $this->dashboardUrl = route($route, $assignment->asesmen->id);
    }

    public function build()
    {
        $jenisAsesmen = $this->jenisAsesmen;
        $jenisAsesmen = $jenisAsesmen != 'dokumen' ? strtoupper($jenisAsesmen) : $jenisAsesmen;

        return $this->subject("Penawaran {$this->role->alias} {$jenisAsesmen} Diterima")
            ->view('emails.asesmen.penawaran-accepted')
            ->with(['jenisAsesmen' => $jenisAsesmen]);
    }
}
