<?php

namespace App\Http\Controllers\DE\Concerns;

use App\Mail\Reminder\ReminderContextMail;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasReminderLHA
{
    // =========================================================
    // ✅ BARU: Reminder Berita Acara ke Asesor AL
    // =========================================================

    /**
     * Reminder ke Asesor AL: segera upload Berita Acara.
     * Kondisi: berita_acara_al belum ada di asesmen_documents.
     */
    public function kirimReminderBeritaAcara(Request $request, $id)
    {
        $request->validate([
            'pesan_reminder' => 'nullable|string',
        ]);

        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'asesmen.asesmenUserRoles' => fn($q) => $q
                ->where('jenis_asesmen', 'al')
                ->where('status_penawaran', 'accepted')
                ->whereHas('role_selected', fn($r) => $r->whereIn('name', ['asesor', 'validator']))
                ->with('user.activeEmails'),
            'asesmen.documents' => fn($q) => $q
                ->where('type', 'berita_acara_al')
                ->where('is_active', true),
        ])->findOrFail($id);

        DB::beginTransaction();
        try {
            $namaProdi   = $pengajuan->studyProgram->name ?? '-';
            $namaUniv    = $pengajuan->studyProgram->university->name ?? '-';
            $contextInfo = "{$namaProdi} | {$namaUniv}";

            // Cek apakah BA sudah ada
            $sudahAdaBA = $pengajuan->asesmen->documents->isNotEmpty();

            if ($sudahAdaBA) {
                return back()->with('error', 'Berita Acara AL sudah diupload, tidak perlu reminder ke asesor.');
            }

            $pesanReminder = $request->filled('pesan_reminder')
                ? $request->pesan_reminder
                : "Kami mengingatkan untuk segera mengunggah Berita Acara Asesmen Lapangan untuk program studi {$namaProdi}.\n\nBerita Acara merupakan dokumen penting yang diperlukan untuk melanjutkan proses pelaporan AL. Mohon segera upload dokumen tersebut melalui sistem.";

            $asesorUsers = $pengajuan->asesmen->asesmenUserRoles->map->user->filter();

            if ($asesorUsers->isEmpty()) {
                return back()->with('error', 'Tidak ada asesor AL yang terdaftar.');
            }

            /** @var RecipientResolverService $resolver */
            $resolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $delivery */
            $delivery = app(MailDeliveryService::class);

            $asesorEmails = $resolver->emailsForUsers($asesorUsers);

            if (empty($asesorEmails)) {
                return back()->with('error', 'Tidak ada email asesor yang ditemukan.');
            }

            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to'   => $pengajuan->status,
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => 'Pengingat upload Berita Acara AL dikirim oleh DE ke tim asesor AL ' . $namaProdi,
            ]);

            $delivery->sendToEmails(
                $asesorEmails,
                new ReminderContextMail(
                    recipientName: 'Tim Asesor AL',
                    pesanReminder: $pesanReminder,
                    subject: "Pengingat Upload Berita Acara AL — {$namaProdi}",
                    actionUrl: route('al.berkas', $pengajuan->asesmen->id),
                    actionLabel: 'Upload Berita Acara Sekarang',
                    contextInfo: $contextInfo,
                    headerTitle: 'Pengingat Upload Berita Acara AL',
                    preheader: "Segera upload Berita Acara AL untuk {$namaProdi} melalui sistem.",
                ),
                [],
                [],
                true
            );

            DB::commit();

            return back()->with('success', 'Pengingat upload Berita Acara AL berhasil dikirim ke tim asesor.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal kirim reminder Berita Acara AL ke asesor', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }

    // =========================================================
    // Reminder LHA ke Asesor — tidak berubah
    // =========================================================

    public function kirimReminderAsesor(Request $request, $id)
    {
        $request->validate([
            'pesan_reminder' => 'nullable|string',
        ]);

        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'asesmen.asesmenUserRoles' => fn($q) => $q
                ->where('jenis_asesmen', 'al')
                ->where('status_penawaran', 'accepted')
                ->whereHas('role_selected', fn($r) => $r->where('name', 'asesor'))
                ->with('user.activeEmails'),
            'asesmen.documents' => fn($q) => $q
                ->where('type', 'lha_asesor')
                ->where('is_active', true)
                ->latest('uploaded_at'),
        ])->findOrFail($id);

        DB::beginTransaction();
        try {
            $namaProdi   = $pengajuan->studyProgram->name ?? '-';
            $namaUniv    = $pengajuan->studyProgram->university->name ?? '-';

            $lhaApproved = $pengajuan->asesmen->documents
                ->firstWhere('status_persetujuan_prodi', 'approved');

            if ($lhaApproved) {
                return back()->with('error', 'LHA sudah disetujui, tidak perlu reminder ke asesor.');
            }

            $lhaDraft = $pengajuan->asesmen->documents->first();
            $isRevisi  = $lhaDraft?->status_persetujuan_prodi === 'revision_required';

            $defaultPesan = $isRevisi
                ? "Kami mengingatkan bahwa terdapat permintaan revisi pada Laporan Hasil Asesmen Lapangan (LHA) untuk program studi {$namaProdi}.\n\nMohon segera lakukan perbaikan sesuai catatan yang diberikan oleh Program Studi, kemudian finalisasi ulang dokumen."
                : "Kami mengingatkan untuk segera menyelesaikan dan memfinalisasi Laporan Hasil Asesmen Lapangan (LHA) untuk program studi {$namaProdi}.\n\nLHA yang telah difinalisasi akan dikirimkan ke Program Studi untuk mendapat persetujuan.";

            $pesanReminder = $request->filled('pesan_reminder')
                ? $request->pesan_reminder
                : $defaultPesan;

            $asesorUsers = $pengajuan->asesmen->asesmenUserRoles->map->user->filter();

            if ($asesorUsers->isEmpty()) {
                return back()->with('error', 'Tidak ada asesor AL yang terdaftar.');
            }

            /** @var RecipientResolverService $resolver */
            $resolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $delivery */
            $delivery = app(MailDeliveryService::class);

            $asesorEmails = $resolver->emailsForUsers($asesorUsers);

            if (empty($asesorEmails)) {
                return back()->with('error', 'Tidak ada email asesor yang ditemukan.');
            }

            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to'   => $pengajuan->status,
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => 'Pengingat ' . ($isRevisi ? 'revisi' : 'finalisasi') .
                    ' LHA dikirim oleh DE ke tim asesor AL ' . $namaProdi,
            ]);

            $delivery->sendToEmails(
                $asesorEmails,
                new ReminderContextMail(
                    recipientName: 'Tim Asesor AL',
                    pesanReminder: $pesanReminder,
                    subject: $isRevisi
                        ? "Pengingat Revisi LHA — {$namaProdi}"
                        : "Pengingat Finalisasi LHA — {$namaProdi}",
                    actionUrl: route('al.berkas.lha-asesor.page', $pengajuan->asesmen->id),
                    actionLabel: $isRevisi ? 'Perbaiki LHA Sekarang' : 'Finalisasi LHA Sekarang',
                    contextInfo: $isRevisi ? 'Berikut adalah detail permintaan revisi LHA: ' : 'Berikut adalah detail proses persetujuan LHA: ',
                    headerTitle: $isRevisi ? 'Pengingat Revisi LHA' : 'Pengingat Finalisasi LHA',
                    preheader: $isRevisi
                        ? "Segera perbaiki LHA untuk {$namaProdi} sesuai catatan revisi."
                        : "Segera finalisasi LHA untuk {$namaProdi}.",
                ),
                [],
                [],
                true
            );

            DB::commit();

            return back()->with('success', 'Pengingat berhasil dikirim ke tim asesor.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal kirim reminder LHA ke asesor', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }

    // =========================================================
    // Reminder LHA ke UPPS — tidak berubah
    // =========================================================

    public function kirimReminderUPPS(Request $request, $id)
    {
        $request->validate([
            'pesan_reminder' => 'nullable|string',
        ]);

        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.users.activeEmails',
            'pengaju.activeEmails',
            'asesmen.documents' => fn($q) => $q
                ->where('type', 'lha_asesor')
                ->where('is_active', true)
                ->latest('uploaded_at'),
        ])->findOrFail($id);

        DB::beginTransaction();
        try {
            $namaProdi = $pengajuan->studyProgram->name ?? '-';
            $namaUniv  = $pengajuan->studyProgram->university->name ?? '-';

            $lhaPending = $pengajuan->asesmen->documents
                ->firstWhere('status_persetujuan_prodi', 'pending');

            if (!$lhaPending) {
                return back()->with('error', 'Tidak ada LHA yang menunggu peninjauan dari Program Studi.');
            }

            $pesanReminder = $request->filled('pesan_reminder')
                ? $request->pesan_reminder
                : "Tim asesor lapangan telah menyelesaikan Laporan Hasil Asesmen Lapangan (LHA) untuk program studi Anda.\n\nLHA ini masih menunggu peninjauan dari Program Studi. Mohon segera:\n1. Buka dan baca LHA yang telah disiapkan\n2. Berikan persetujuan jika laporan sudah sesuai\n3. Ajukan permintaan revisi jika ada yang perlu diperbaiki";

            /** @var RecipientResolverService $resolver */
            $resolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $delivery */
            $delivery = app(MailDeliveryService::class);

            $prodUsers = $pengajuan->studyProgram?->users;
            $emails    = $prodUsers && $prodUsers->isNotEmpty()
                ? $resolver->emailsForUsers($prodUsers)
                : $resolver->emailsForUser($pengajuan->pengaju);

            if (empty($emails)) {
                return back()->with('error', 'Tidak ada email UPPS yang ditemukan.');
            }

            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to'   => $pengajuan->status,
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => 'Pengingat peninjauan LHA dikirim oleh DE ke UPPS ' . $namaProdi,
            ]);

            $delivery->sendToEmails(
                $emails,
                new ReminderContextMail(
                    recipientName: 'Tim Akreditasi Program Studi <strong>' . $namaProdi . '</strong>',
                    pesanReminder: $pesanReminder,
                    subject: "Pengingat Peninjauan LHA — {$namaProdi}",
                    actionUrl: route('upps.pelaksanaan-al.show', $pengajuan->id),
                    actionLabel: 'Tinjau LHA Sekarang',
                    contextInfo: 'Berikut adalah detail proses penyusunan LHA: ',
                    headerTitle: 'Pengingat Peninjauan LHA',
                    preheader: 'LHA untuk program studi Anda masih menunggu peninjauan, segera ditindaklanjuti.',
                ),
                [],
                [],
                true
            );

            DB::commit();

            return back()->with('success', 'Pengingat berhasil dikirim ke UPPS.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal kirim reminder LHA ke UPPS', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }
}
