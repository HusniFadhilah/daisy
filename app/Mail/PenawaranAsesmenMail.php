<?php

namespace App\Mail;

use App\Helpers\RouteHelper;
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
    public $acceptUrl;

    public function __construct(AsesmenUserRole $assignment)
    {
        $this->assignment = $assignment;
        $this->asesmen    = $assignment->asesmen;
        $this->user       = $assignment->user;
        $this->role       = $assignment->role;
        $this->acceptUrl  = route('penawaran.show', RouteHelper::encryptId($assignment->id));
    }

    public function build()
    {
        $jenisAsesmen = strtoupper($this->assignment->jenis_asesmen);

        return $this->subject("Penawaran {$this->role->alias} - {$jenisAsesmen} - {$this->asesmen->name}")
            ->view('emails.asesmen.penawaran-assignment')
            ->with(['jenisAsesmen' => $jenisAsesmen]);
    }
}
