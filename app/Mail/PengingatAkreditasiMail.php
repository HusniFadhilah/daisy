<?php

namespace App\Mail;

use App\Models\StudyProgram;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PengingatAkreditasiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $studyProgram;
    public $pesanPengingat;

    public function __construct(StudyProgram $studyProgram, $pesanPengingat)
    {
        $this->studyProgram = $studyProgram;
        $this->pesanPengingat = $pesanPengingat;
    }

    public function build()
    {
        return $this->subject('Pengingat Masa Akreditasi Program Studi')
            ->view('emails.pengingat-akreditasi');
    }
}
