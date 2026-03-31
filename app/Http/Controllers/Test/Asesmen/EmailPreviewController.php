<?php

namespace App\Http\Controllers\Test\Asesmen;

use App\Http\Controllers\Controller;
use App\Mail\BorangTemplateSentMail;
use App\Mail\InvoicePembayaranMail;
use App\Mail\PembayaranVerifiedMail;
use App\Mail\PenawaranAcceptedMail;
use App\Mail\PenawaranRejectedMail;
use App\Mail\PenerimaanAkreditasiMail;
use App\Mail\PengingatAkreditasiMail;
use App\Mail\ValidatorBorangAssignedMail;
use App\Models\AsesmenUserRole;
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
}
