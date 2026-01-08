<?php
// app/Mail/BorangTemplateSentMail.php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // ✅ Add this
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BorangTemplateSentMail extends Mailable implements ShouldQueue // ✅ Implement ShouldQueue
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $dokumen;
    public $metode;

    // ✅ Add retry settings
    public $tries = 3;
    public $timeout = 60;

    public function __construct(PengajuanAkreditasi $pengajuan, PengajuanDokumen $dokumen, $metode)
    {
        // ✅ Load all relationships needed
        $this->pengajuan = $pengajuan->load([
            'pengaju',
            'studyProgram.degreeLevel',
            'studyProgram.university'
        ]);

        $this->dokumen = $dokumen;
        $this->metode = $metode;
    }

    public function build()
    {
        $subject = "Template Borang LED - {$this->pengajuan->nomor_pengajuan}";

        return $this->subject($subject)
            ->view('emails.pengajuan.borang-template-sent')
            ->with([
                'pengajuan' => $this->pengajuan,
                'dokumen' => $this->dokumen,
                'metode' => $this->metode,
            ]);
    }
}
