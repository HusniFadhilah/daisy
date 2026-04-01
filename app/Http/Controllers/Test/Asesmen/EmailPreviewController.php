<?php

namespace App\Http\Controllers\Test\Asesmen;

use App\Http\Controllers\Controller;
use App\Mail\BorangRevisionNotification;
use App\Mail\BorangTemplateSentMail;
use App\Mail\BorangValidationApproved;
use App\Mail\InvoicePembayaranMail;
use App\Mail\PembayaranVerifiedMail;
use App\Mail\PenawaranAcceptedMail;
use App\Mail\PenawaranRejectedMail;
use App\Mail\PenerimaanAkreditasiMail;
use App\Mail\PengingatAkreditasiMail;
use App\Mail\Reminder\ReminderPelaporanDokumenMail;
use App\Mail\Reminder\ReminderPenawaranAsesmenMail;
use App\Mail\Reminder\ReminderProgressAsesmenMail;
use App\Mail\Reminder\ReminderValidasiDokumenMail;
use App\Mail\ValidatorBorangAssignedMail;
use App\Models\AsesmenUserRole;
use App\Models\BorangValidation;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanPembayaran;
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

    public function suratPenerimaan(PengajuanAkreditasi $pengajuan)
    {
        // ambil dokumen terbaru surat penerimaan
        $dokumen = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'surat_penerimaan_de')
            ->where('is_latest', true)
            ->first();

        // fallback dummy kalau belum ada dokumen
        if (!$dokumen) {
            $dokumen = new PengajuanDokumen([
                'nama_file' => 'surat_penerimaan_dummy.pdf',
                'original_filename' => 'Surat Penerimaan (Preview).pdf',
                'mime_type' => 'application/pdf',
                'path_file' => null, // tidak attach saat preview
            ]);
        }

        return new PenerimaanAkreditasiMail($pengajuan, $dokumen);
    }

    public function borangTemplateSent(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing([
            'pengaju',
            'studyProgram.degreeLevel',
            'studyProgram.university',
        ]);

        $dokumen = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'borang_template')
            ->where('is_latest', true)
            ->first();

        if (!$dokumen) {
            $dokumen = new PengajuanDokumen([
                'nama_file' => 'template_borang_dummy.pdf',
                'original_filename' => 'Template Borang Dummy.pdf',
                'mime_type' => 'application/pdf',
                'template_link' => route('pengajuan.show', $pengajuan->id),
                'path_file' => null,
                'keterangan' => 'Contoh preview email template borang.',
            ]);
        }

        $metode = !empty($dokumen->template_link) ? 'link' : 'upload';

        return new BorangTemplateSentMail($pengajuan, $dokumen, $metode);
    }

    public function invoicePembayaran(PengajuanPembayaran $pembayaran)
    {
        $templateFormulirPembayaran = PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
            ->where('jenis_dokumen', 'template_formulir_pembayaran')
            ->where('is_latest', true)
            ->latest('id')
            ->first();

        return new InvoicePembayaranMail($pembayaran, $templateFormulirPembayaran);
    }

    public function pembayaranVerified(PengajuanPembayaran $pembayaran)
    {
        return new PembayaranVerifiedMail($pembayaran, true);
    }

    public function pembayaranRejected(PengajuanPembayaran $pembayaran)
    {
        return new PembayaranVerifiedMail($pembayaran, false);
    }

    public function borangApproved(PengajuanAkreditasi $pengajuan)
    {
        $validation = BorangValidation::where('id_pengajuan', $pengajuan->id)
            ->latest('id')
            ->first();

        if (!$validation) {
            abort(404, 'Validation belum tersedia');
        }

        return new BorangValidationApproved($pengajuan, $validation);
    }

    public function borangRevision(PengajuanAkreditasi $pengajuan)
    {
        $validation = BorangValidation::where('id_pengajuan', $pengajuan->id)
            ->latest('id')
            ->first();

        if (!$validation) {
            abort(404, 'Validation belum tersedia');
        }

        return new BorangRevisionNotification($pengajuan, $validation);
    }

    public function reminderValidasiDokumen(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'asesmen.pengajuan.studyProgram.degreeLevel',
            'asesmen.pengajuan.studyProgram.university',
        ]);

        return new ReminderValidasiDokumenMail(
            $assignment,
            'Kami mengingatkan untuk segera menyelesaikan validasi dokumen yang telah ditugaskan kepada Anda. Mohon segera melakukan review dan memberikan feedback kepada program studi.'
        );
    }

    public function reminderPelaporanDokumen(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'asesmen.pengajuan.studyProgram.degreeLevel',
            'asesmen.pengajuan.studyProgram.university',
        ]);

        return new ReminderPelaporanDokumenMail(
            $assignment,
            'Kami mengingatkan untuk segera mengunggah pelaporan validasi dokumen yang menjadi penugasan Anda agar proses dapat dilanjutkan.'
        );
    }

    public function reminderPenawaranAsesmen(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'role_selected',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
        ]);

        $asesmenLabel = $assignment->jenis_asesmen_label;
        $asesmenRoleLabel = $assignment->jenis_asesmen_role_label;
        $asesmenActionLabel = $assignment->jenis_asesmen_action_label;

        return new ReminderPenawaranAsesmenMail(
            $assignment,
            'Kami mengingatkan bahwa Anda telah mendapatkan penawaran penugasan ' . $asesmenActionLabel . ', namun belum memberikan konfirmasi. Mohon segera melakukan konfirmasi melalui sistem.'
        );
    }

    public function reminderProgressAsesmen(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'role_selected',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
        ]);

        $role = $assignment->role_selected?->name;
        $status = $assignment->status_pekerjaan;
        $asesmenLabel = $assignment->jenis_asesmen_label;
        $asesmenRoleLabel = $assignment->jenis_asesmen_role_label;
        $asesmenActionLabel = $assignment->jenis_asesmen_action_label;

        // 🔥 logic pesan biar sesuai kondisi real
        if ($assignment->status_penawaran === 'pending') {
            $pesan = 'Anda belum memberikan konfirmasi atas penawaran penugasan ' . $asesmenActionLabel . ' Mohon segera melakukan konfirmasi.';
        } elseif ($role === 'asesor') {
            $pesan = in_array($status, ['pending', 'not_started'])
                ? 'Kami mengingatkan agar Anda segera memulai ' . $asesmenActionLabel
                : 'Kami mengingatkan agar Anda segera menyelesaikan ' . $asesmenActionLabel;
        } else {
            $pesan = 'Kami mengingatkan agar Anda segera melakukan ' . $asesmenActionLabel;
        }

        return new ReminderProgressAsesmenMail($assignment, $pesan);
    }
}
