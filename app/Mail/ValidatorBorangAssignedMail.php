<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ValidatorBorangAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $assignment;
    public $catatanDe;
    public $acceptUrl;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment, $catatanDe = null)
    {
        $this->pengajuan = $pengajuan->load([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'latestBorangImport'
        ]);

        $this->assignment   = $assignment;
        $this->catatanDe    = $catatanDe;
        $this->acceptUrl    = route('penawaran.berkas.cekPenawaran', [
            'idAsesmen'     => $assignment->id,
            'jenisAsesmen'  => 'dokumen',
        ]);
    }

    public function build()
    {
        return $this->subject("Penawaran Validasi Dokumen - {$this->pengajuan->nomor_pengajuan}")
            ->view('emails.validator.borang-assigned');
    }
}
