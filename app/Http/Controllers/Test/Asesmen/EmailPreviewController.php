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
use App\Mail\PenerimaanBandingMail;
use App\Mail\PengingatAkreditasiMail;
use App\Mail\Reminder\ReminderContextMail;
use App\Mail\Reminder\ReminderPelaporanDokumenMail;
use App\Mail\Reminder\ReminderPelaporanMail;
use App\Mail\Reminder\ReminderPenawaranAsesmenMail;
use App\Mail\Reminder\ReminderProgressAsesmenMail;
use App\Mail\Reminder\ReminderValidasiDokumenMail;
use App\Mail\ValidatorBorangAssignedMail;
use App\Models\Asesmen;
use App\Models\AsesmenUserRole;
use App\Models\BorangValidation;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanPembayaran;
use App\Models\StudyProgram;
use Carbon\Carbon;

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

    // =========================================================
    // REMINDER PELAPORAN — ReminderPelaporanMail
    // =========================================================

    public function reminderPelaporanAK(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
        ]);

        return new ReminderPelaporanMail(
            assignment: $assignment,
            pesanReminder: 'Kami mengingatkan untuk segera mengunggah laporan validasi AK yang menjadi penugasan Anda agar proses akreditasi dapat dilanjutkan.',
            subject: 'Pengingat Pelaporan Validasi AK',
            actionUrl: route('pelaporan.validasiAk.show', $assignment->id),
            headerTitle: 'Pengingat Pelaporan Validasi AK',
            preheader: 'Segera upload laporan validasi AK Anda melalui sistem.',
        );
    }

    public function reminderPelaporanAL(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
        ]);

        return new ReminderPelaporanMail(
            assignment: $assignment,
            pesanReminder: 'Kami mengingatkan untuk segera mengunggah laporan AL yang menjadi penugasan Anda agar proses akreditasi dapat dilanjutkan.',
            subject: 'Pengingat Pelaporan Validasi AL',
            actionUrl: route('pelaporan.al.show', $assignment->id),
            headerTitle: 'Pengingat Pelaporan Validasi AL',
            preheader: 'Segera upload laporan validasi AL Anda melalui sistem.',
        );
    }

    public function reminderPelaporanAKBanding(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
        ]);

        return new ReminderPelaporanMail(
            assignment: $assignment,
            pesanReminder: 'Kami mengingatkan untuk segera mengunggah laporan validasi AK Banding yang menjadi penugasan Anda agar proses banding dapat dilanjutkan.',
            subject: 'Pengingat Pelaporan Validasi AK Banding',
            actionUrl: route('pelaporan.banding.validasiAk.show', $assignment->id),
            headerTitle: 'Pengingat Pelaporan Validasi AK Banding',
            preheader: 'Segera upload laporan validasi AK Banding Anda melalui sistem.',
        );
    }

    public function reminderPelaporanALBanding(AsesmenUserRole $assignment)
    {
        $assignment->loadMissing([
            'user.activeEmails',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
        ]);

        return new ReminderPelaporanMail(
            assignment: $assignment,
            pesanReminder: 'Kami mengingatkan untuk segera mengunggah laporan AL Banding yang menjadi penugasan Anda agar proses banding dapat dilanjutkan.',
            subject: 'Pengingat Pelaporan Validasi AL Banding',
            actionUrl: route('pelaporan.al-banding.show', $assignment->id),
            headerTitle: 'Pengingat Pelaporan Validasi AL Banding',
            preheader: 'Segera upload laporan validasi AL Banding Anda melalui sistem.',
        );
    }

    // =========================================================
    // REMINDER CONTEXT — ReminderContextMail
    // =========================================================

    public function reminderUploadDokumen(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing([
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ]);

        $contextInfo = implode(' | ', array_filter([
            $pengajuan->studyProgram->name ?? null,
            $pengajuan->studyProgram->university->name ?? null,
            $pengajuan->nomor_pengajuan ?? null,
        ]));

        return new ReminderContextMail(
            recipientName: $pengajuan->studyProgram->name ?? 'UPPS',
            pesanReminder: "Pembayaran Anda telah tervalidasi. Kami mengingatkan untuk segera mengunggah dokumen akreditasi melalui sistem DAISY LAMDEPILAR.\n\nDokumen yang perlu diunggah:\n1. Laporan Evaluasi Diri (LED) + Suplemen\n2. Laporan Kinerja Program Studi (LKPS)",
            subject: 'Pengingat Upload Dokumen Akreditasi',
            actionUrl: route('upps.penerimaan-dokumen.show', $pengajuan->id),
            actionLabel: 'Upload Dokumen Sekarang',
            contextInfo: $contextInfo,
            headerTitle: 'Pengingat Upload Dokumen Akreditasi',
            preheader: 'Segera upload dokumen akreditasi Anda melalui sistem DAISY.',
        );
    }

    public function reminderValidasiPembayaran(PengajuanPembayaran $pembayaran)
    {
        $pembayaran->loadMissing([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
        ]);

        $contextInfo = implode(' | ', array_filter([
            $pembayaran->pengajuan->studyProgram->name ?? null,
            $pembayaran->pengajuan->studyProgram->university->name ?? null,
            'Invoice: ' . $pembayaran->nomor_invoice,
        ]));

        return new ReminderContextMail(
            recipientName: 'Bagian Keuangan LAMDEPILAR',
            pesanReminder: 'Terdapat bukti pembayaran akreditasi yang masih menunggu validasi. Mohon segera diproses agar tidak menghambat proses akreditasi program studi terkait.',
            subject: 'Pengingat Validasi Pembayaran Akreditasi',
            actionUrl: route('keuangan.pembayaran.show', $pembayaran->id),
            actionLabel: 'Validasi Pembayaran',
            contextInfo: $contextInfo,
            headerTitle: 'Pengingat Validasi Pembayaran',
            preheader: 'Terdapat bukti pembayaran yang menunggu validasi Anda.',
        );
    }

    public function reminderBayarInvoice(PengajuanPembayaran $pembayaran)
    {
        $pembayaran->loadMissing([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
        ]);

        $namaProdi   = $pembayaran->pengajuan->studyProgram->name ?? '-';
        $namaUniv    = $pembayaran->pengajuan->studyProgram->university->name ?? '-';
        $contextInfo = implode(' | ', array_filter([
            $namaProdi,
            $namaUniv,
            'Invoice: ' . $pembayaran->nomor_invoice,
            $pembayaran->tanggal_jatuh_tempo
                ? 'Jatuh Tempo: ' . Carbon::parse($pembayaran->tanggal_jatuh_tempo)->translatedFormat('d M Y')
                : null,
        ]));

        return new ReminderContextMail(
            recipientName: $namaProdi,
            pesanReminder: 'Kami mengingatkan bahwa invoice akreditasi Anda masih belum dibayarkan. Mohon segera lakukan pembayaran sebelum melewati tanggal jatuh tempo.',
            subject: 'Pengingat Pembayaran Invoice Akreditasi',
            actionUrl: route('upps.validasi-pembayaran.show', $pembayaran->id),
            actionLabel: 'Bayar Sekarang',
            contextInfo: $contextInfo,
            headerTitle: 'Pengingat Pembayaran Invoice Akreditasi',
            preheader: 'Invoice akreditasi Anda belum dibayarkan, segera selesaikan sebelum jatuh tempo.',
        );
    }

    public function reminderUploadUlangBukti(PengajuanPembayaran $pembayaran)
    {
        $pembayaran->loadMissing([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
        ]);

        $namaProdi   = $pembayaran->pengajuan->studyProgram->name ?? '-';
        $namaUniv    = $pembayaran->pengajuan->studyProgram->university->name ?? '-';
        $contextInfo = implode(' | ', array_filter([
            $namaProdi,
            $namaUniv,
            'Invoice: ' . $pembayaran->nomor_invoice,
        ]));

        return new ReminderContextMail(
            recipientName: $namaProdi,
            pesanReminder: 'Bukti pembayaran yang Anda upload sebelumnya perlu diperbaiki. Mohon segera upload ulang bukti pembayaran yang lebih jelas agar dapat diverifikasi oleh bagian keuangan.',
            subject: 'Pengingat Upload Ulang Bukti Pembayaran',
            actionUrl: route('upps.validasi-pembayaran.show', $pembayaran->id),
            actionLabel: 'Upload Ulang Bukti',
            contextInfo: $contextInfo,
            headerTitle: 'Upload Ulang Bukti Pembayaran',
            preheader: 'Bukti pembayaran Anda perlu diupload ulang agar dapat diverifikasi.',
        );
    }

    public function notifikasiLhaFinalized(Asesmen $asesmen)
    {
        $asesmen->loadMissing([
            'pengajuan.studyProgram.university',
        ]);

        $pengajuan   = $asesmen->pengajuan;
        $contextInfo = 'Berikut adalah detail proses penyusunan LHA: ';

        return new ReminderContextMail(
            recipientName: 'Tim Akreditasi Program Studi <strong>' . $pengajuan->studyProgram->name . '</strong>',
            pesanReminder: "Laporan Hasil Asesmen Lapangan (LHA) untuk program studi Anda telah selesai disusun oleh tim asesor dan siap untuk ditinjau.\n\nMohon segera melakukan peninjauan dan memberikan persetujuan atau permintaan revisi melalui sistem.",
            subject: 'Laporan Hasil Asesmen Lapangan (LHA) Siap Ditinjau',
            actionUrl: route('upps.pelaksanaan-al.show', $pengajuan->id),
            actionLabel: 'Tinjau LHA Sekarang',
            contextInfo: $contextInfo,
            headerTitle: 'Laporan Hasil Asesmen Lapangan (LHA) Siap Ditinjau',
            preheader: 'Tim asesor telah menyelesaikan LHA, segera lakukan peninjauan.',
        );
    }

    public function notifikasiLhaApproved(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing([
            'studyProgram.university',
        ]);

        $namaProdi = $pengajuan->studyProgram->name ?? '-';
        $namaUniv  = $pengajuan->studyProgram->university->name ?? '-';
        $contextInfo = 'Berikut adalah detail proses persetujuan LHA: ';

        return new ReminderContextMail(
            recipientName: 'Tim Asesor AL',
            pesanReminder: "Program studi {$namaProdi} ({$namaUniv}) telah meninjau dan menyetujui Laporan Hasil Asesmen Lapangan (LHA) yang Anda susun.\n\nTerima kasih atas dedikasi Anda dalam proses asesmen ini.\n\nCatatan dari Program Studi:\n[Contoh catatan persetujuan dari program studi]",
            subject: 'LHA Disetujui oleh Program Studi — ' . $namaProdi,
            actionUrl: route('al.berkas.lha-asesor.page', $pengajuan->asesmen->id),
            actionLabel: 'Lihat LHA',
            contextInfo: $contextInfo,
            headerTitle: 'Laporan Hasil Asesmen Lapangan (LHA) Disetujui',
            preheader: "{$namaProdi} telah menyetujui LHA yang Anda susun.",
        );
    }

    public function notifikasiLhaRevision(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing([
            'studyProgram.university',
        ]);

        $namaProdi = $pengajuan->studyProgram->name ?? '-';
        $namaUniv  = $pengajuan->studyProgram->university->name ?? '-';
        $contextInfo = 'Berikut adalah detail permintaan revisi LHA: ';

        return new ReminderContextMail(
            recipientName: 'Tim Asesor AL',
            pesanReminder: "Program studi {$namaProdi} ({$namaUniv}) mengajukan permintaan revisi terhadap Laporan Hasil Asesmen Lapangan (LHA) yang Anda susun.\n\nMohon segera lakukan perbaikan sesuai catatan yang diberikan, kemudian finalisasi ulang dokumen.\n\nCatatan Revisi dari Program Studi:\n[Contoh catatan revisi: Mohon perbaiki bagian hasil AL pada poin 3 mengenai capaian pembelajaran]",
            subject: 'Permintaan Revisi LHA dari Program Studi — ' . $namaProdi,
            actionUrl: route('al.berkas.lha-asesor.page', $pengajuan->asesmen->id),
            actionLabel: 'Perbaiki LHA Sekarang',
            contextInfo: $contextInfo,
            headerTitle: 'Permintaan Revisi Laporan Hasil Asesmen Lapangan (LHA)',
            preheader: "{$namaProdi} meminta revisi pada LHA Anda, segera lakukan perbaikan.",
        );
    }

    public function penerimaanBanding(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing([
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ]);

        $dokumen = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
            ->where('is_latest', true)
            ->first();

        // Fallback dummy jika belum ada dokumen
        if (!$dokumen) {
            $dokumen = new PengajuanDokumen([
                'nama_file'         => 'surat_penerimaan_banding_dummy.pdf',
                'original_filename' => 'Surat Penerimaan Banding (Preview).pdf',
                'mime_type'         => 'application/pdf',
                'path_file'         => null,
            ]);
        }

        return new PenerimaanBandingMail($pengajuan, $dokumen);
    }

    public function reminderBeritaAcaraAL(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing(['studyProgram.university']);

        $namaProdi = $pengajuan->studyProgram->name ?? '-';
        $namaUniv  = $pengajuan->studyProgram->university->name ?? '-';

        return new ReminderContextMail(
            recipientName: 'Tim Asesor AL',
            pesanReminder: "Kami mengingatkan untuk segera mengunggah Berita Acara Asesmen Lapangan untuk program studi {$namaProdi}.\n\nBerita Acara merupakan dokumen penting yang diperlukan untuk melanjutkan proses pelaporan AL. Mohon segera upload dokumen tersebut melalui sistem.",
            subject: "Pengingat Upload Berita Acara AL — {$namaProdi}",
            actionUrl: route('al.berkas', $pengajuan->asesmen->id),
            actionLabel: 'Upload Berita Acara Sekarang',
            contextInfo: "{$namaProdi} | {$namaUniv}",
            headerTitle: 'Pengingat Upload Berita Acara AL',
            preheader: "Segera upload Berita Acara AL untuk {$namaProdi} melalui sistem.",
        );
    }

    public function reminderLhaAsesor(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing(['studyProgram.university', 'asesmen']);

        $namaProdi = $pengajuan->studyProgram->name ?? '-';
        $namaUniv  = $pengajuan->studyProgram->university->name ?? '-';

        return new ReminderContextMail(
            recipientName: 'Tim Asesor AL',
            pesanReminder: "Kami mengingatkan untuk segera menyelesaikan dan memfinalisasi Laporan Hasil Asesmen Lapangan (LHA) untuk program studi {$namaProdi}.\n\nLHA yang telah difinalisasi akan dikirimkan ke Program Studi untuk mendapat persetujuan.",
            subject: "Pengingat Finalisasi LHA — {$namaProdi}",
            actionUrl: route('al.berkas.lha-asesor.index', $pengajuan->asesmen->id),
            actionLabel: 'Finalisasi LHA Sekarang',
            contextInfo: "{$namaProdi} | {$namaUniv}",
            headerTitle: 'Pengingat Finalisasi LHA',
            preheader: "Segera finalisasi LHA untuk {$namaProdi}.",
        );
    }

    public function notifikasiHasilDisampaikan(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing(['studyProgram.university', 'asesmen.hasil']);

        $namaProdi = $pengajuan->studyProgram->name ?? '-';
        $hasil     = $pengajuan->asesmen->hasil;
        $peringkat = $hasil->peringkat_akreditasi_hasil ?? '-';

        return new ReminderContextMail(
            recipientName: 'Tim Akreditasi Program Studi <strong>' . $namaProdi . '</strong>',
            pesanReminder: "Hasil Asesmen Lapangan (AL) untuk program studi Anda telah difinalisasi dan disampaikan secara resmi.\n\nPeringkat yang diperoleh: {$peringkat}\n\nMasa sanggah atas hasil ini akan berlangsung hingga:\n[Tanggal Masa Sanggah]\n\nJika Anda keberatan atas hasil tersebut, Anda dapat mengajukan sanggahan melalui sistem sebelum masa sanggah berakhir.",
            subject: 'Hasil Asesmen Lapangan Telah Disampaikan — ' . $namaProdi,
            actionUrl: route('upps.penyampaian-hasil-akreditasi.show', $pengajuan->id),
            actionLabel: 'Lihat Hasil Akreditasi',
            contextInfo: null,
            headerTitle: 'Hasil Asesmen Lapangan Telah Disampaikan',
            preheader: "Hasil AL program studi Anda telah difinalisasi. Peringkat: {$peringkat}.",
        );
    }

    public function notifikasiHasilDitetapkan(PengajuanAkreditasi $pengajuan)
    {
        $pengajuan->loadMissing(['studyProgram.university', 'asesmen.hasil']);

        $namaProdi  = $pengajuan->studyProgram->name ?? '-';
        $hasil      = $pengajuan->asesmen->hasil;
        $peringkat  = $hasil->peringkat_akreditasi_final ?? '-';
        $skor       = $hasil->skor_final ?? '-';
        $tanggalPenetapan  = $pengajuan->tanggal_penetapan
            ? \Carbon\Carbon::parse($pengajuan->tanggal_penetapan)->locale('id')->translatedFormat('d F Y')
            : '[Tanggal Penetapan]';
        $tanggalKedaluwarsa = $pengajuan->tanggal_kedaluwarsa_akhir
            ? \Carbon\Carbon::parse($pengajuan->tanggal_kedaluwarsa_akhir)->locale('id')->translatedFormat('d F Y')
            : '[Tanggal Kedaluwarsa]';

        return new ReminderContextMail(
            recipientName: 'Tim Akreditasi Program Studi <strong>' . $namaProdi . '</strong>',
            pesanReminder: "Hasil akreditasi program studi Anda telah resmi ditetapkan oleh LAMDEPILAR.\n\nPeringkat Akreditasi : {$peringkat}\nSkor Final           : {$skor}\nTanggal Penetapan   : {$tanggalPenetapan}\nBerlaku Hingga      : {$tanggalKedaluwarsa}\n\nSertifikat akreditasi akan segera diterbitkan dan disampaikan kepada program studi. Mohon pantau sistem untuk informasi lebih lanjut.",
            subject: 'Hasil Akreditasi Resmi Ditetapkan — ' . $namaProdi,
            actionUrl: route('upps.penetapan-hasil-akreditasi.show', $pengajuan->id),
            actionLabel: 'Lihat Hasil Penetapan',
            contextInfo: null,
            headerTitle: 'Hasil Akreditasi Resmi Ditetapkan',
            preheader: "Selamat! Hasil akreditasi {$namaProdi} telah resmi ditetapkan. Peringkat: {$peringkat}.",
        );
    }
}
