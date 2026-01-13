<?php
// app/Mail/ValidatorBorangAssignedMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ValidatorBorangAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $assignment;
    public $catatanDe;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment, $catatanDe = null)
    {
        $this->pengajuan = $pengajuan->load([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'latestBorangImport'
        ]);

        $this->assignment = $assignment;
        $this->catatanDe = $catatanDe;
    }

    public function build()
    {
        $subject = "Penawaran Validasi LED - {$this->pengajuan->nomor_pengajuan}";

        return $this->subject($subject)
            ->markdown('emails.validator.borang-assigned', [
                'pengajuan' => $this->pengajuan,
                'assignment' => $this->assignment,
                'catatanDe' => $this->catatanDe,
                'acceptUrl' => route('penawaran.berkas.cekPenawaran', ['idAsesmen' => $this->assignment->id, 'jenisAsesmen' => 'dokumen']),
            ]);
    }
}
