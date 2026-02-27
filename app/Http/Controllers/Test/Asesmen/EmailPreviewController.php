<?php

namespace App\Http\Controllers\Test\Asesmen;

use App\Http\Controllers\Controller;
use App\Mail\PenawaranAcceptedMail;
use App\Mail\PenawaranRejectedMail;
use App\Mail\PengingatAkreditasiMail;
use App\Mail\ValidatorBorangAssignedMail;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use App\Models\StudyProgram;

class EmailPreviewController extends Controller
{
    public function penawaranBorang(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment)
    {
        return new ValidatorBorangAssignedMail($pengajuan, $assignment);
    }

    public function penawaranAccepted(AsesmenUserRole $assignment)
    {
        return new PenawaranAcceptedMail($assignment);
    }

    public function penawaranRejected(AsesmenUserRole $assignment)
    {
        return new PenawaranRejectedMail($assignment);
    }

    public function pengingatAkreditasi(StudyProgram $studyProgram)
    {
        return new PengingatAkreditasiMail(
            $studyProgram,
            'Masa akreditasi Anda akan berakhir dalam 30 hari.'
        );
    }
}
