<?php

namespace App\Http\Controllers\DE\Concerns;

use App\Mail\Reminder\ReminderContextMail;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanPembayaran;
use App\Models\User;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasReminderPembayaran
{
    /**
     * Reminder ke Keuangan: segera validasi bukti pembayaran.
     * Target: semua pembayaran berstatus 'menunggu_verifikasi'.
     */
    protected function processKirimReminderKeuangan(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'id_pembayaran'   => 'required|array',
            'id_pembayaran.*' => 'exists:pengajuan_pembayaran,id',
            'pesan_reminder'  => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $pembayarans = PengajuanPembayaran::with([
                'pengajuan.studyProgram.university',
                'pengajuan.statusLog',
            ])
                ->whereIn('id', $validated['id_pembayaran'])
                ->where('status_pembayaran', 'menunggu_verifikasi')
                ->get();

            /** @var RecipientResolverService $resolver */
            $resolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $delivery */
            $delivery = app(MailDeliveryService::class);

            // Ambil semua user dengan role keuangan
            $keuanganUsers = User::role('keuangan')->with('activeEmails')->get();
            $keuanganEmails = $resolver->emailsForUsers($keuanganUsers);

            if (empty($keuanganEmails)) {
                return back()->with('error', 'Tidak ada akun keuangan yang ditemukan.');
            }

            $sent = 0;

            foreach ($pembayarans as $pembayaran) {
                $pengajuan   = $pembayaran->pengajuan;
                $namaProdi   = $pengajuan->studyProgram->name ?? '-';
                $namaUniv    = $pengajuan->studyProgram->university->name ?? '-';
                $contextInfo = "{$namaProdi} | {$namaUniv} | Invoice: {$pembayaran->nomor_invoice}";

                // Log ke pengajuan
                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to'   => $pengajuan->status,
                    'changed_by'  => auth()->id(),
                    'changed_at'  => now(),
                    'keterangan'  => 'Pengingat validasi pembayaran dikirim ke Keuangan. Invoice: ' .
                        $pembayaran->nomor_invoice,
                ]);

                $delivery->sendToEmails(
                    $keuanganEmails,
                    new ReminderContextMail(
                        recipientName: 'Bagian Keuangan LAMDEPILAR',
                        pesanReminder: $validated['pesan_reminder'],
                        subject: 'Pengingat Validasi Pembayaran Akreditasi',
                        actionUrl: route('keuangan.pembayaran.show', $pembayaran->id),
                        actionLabel: 'Validasi Pembayaran',
                        contextInfo: $contextInfo,
                        headerTitle: 'Pengingat Validasi Pembayaran',
                        preheader: 'Terdapat bukti pembayaran yang menunggu validasi Anda.',
                    ),
                    [],
                    [],
                    true
                );

                $sent++;
            }

            DB::commit();
            return back()->with('success', "Pengingat validasi berhasil dikirim ke Keuangan untuk {$sent} pembayaran.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Kirim reminder keuangan gagal', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }

    /**
     * Reminder ke UPPS: segera bayar invoice.
     * Target: pembayaran berstatus 'menunggu_pembayaran' atau 'upload_ulang'.
     */
    protected function processKirimReminderUPPS(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'id_pembayaran'   => 'required|array',
            'id_pembayaran.*' => 'exists:pengajuan_pembayaran,id',
            'pesan_reminder'  => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $pembayarans = PengajuanPembayaran::with([
                'pengajuan.studyProgram.university',
                'pengajuan.studyProgram.users.activeEmails',
                'pengajuan.pengaju.activeEmails',
            ])
                ->whereIn('id', $validated['id_pembayaran'])
                ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'upload_ulang'])
                ->get();

            /** @var RecipientResolverService $resolver */
            $resolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $delivery */
            $delivery = app(MailDeliveryService::class);

            $sent = 0;

            foreach ($pembayarans as $pembayaran) {
                $pengajuan   = $pembayaran->pengajuan;
                $namaProdi   = $pengajuan->studyProgram->name ?? '-';
                $namaUniv    = $pengajuan->studyProgram->university->name ?? '-';
                $contextInfo = implode(' | ', array_filter([
                    $namaProdi,
                    $namaUniv,
                    'Invoice: ' . $pembayaran->nomor_invoice,
                    'Jatuh Tempo: ' . ($pembayaran->tanggal_jatuh_tempo
                        ? \Carbon\Carbon::parse($pembayaran->tanggal_jatuh_tempo)->translatedFormat('d M Y')
                        : null),
                ]));

                // Resolve penerima: prodi users atau pengaju
                $prodUsers = $pengajuan->studyProgram?->users;
                $emails = $prodUsers && $prodUsers->isNotEmpty()
                    ? $resolver->emailsForUsers($prodUsers)
                    : $resolver->emailsForUser($pengajuan->pengaju);

                if (empty($emails)) continue;

                // Log ke pengajuan
                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to'   => $pengajuan->status,
                    'changed_by'  => auth()->id(),
                    'changed_at'  => now(),
                    'keterangan'  => 'Pengingat pembayaran invoice dikirim ke UPPS ' . $namaProdi,
                ]);

                $isUploadUlang = $pembayaran->status_pembayaran === 'upload_ulang';

                $delivery->sendToEmails(
                    $emails,
                    new ReminderContextMail(
                        recipientName: 'Tim akreditasi program studi <strong>' . $namaProdi . '</strong>',
                        pesanReminder: $validated['pesan_reminder'],
                        subject: $isUploadUlang
                            ? 'Pengingat Upload Ulang Bukti Pembayaran'
                            : 'Pengingat Pembayaran Invoice Akreditasi',
                        actionUrl: route('upps.validasi-pembayaran.show', $pembayaran->id),
                        actionLabel: $isUploadUlang ? 'Upload Ulang Bukti' : 'Bayar Sekarang',
                        contextInfo: $contextInfo,
                        headerTitle: 'Pengingat Upload Ulang Bukti Pembayaran',
                        preheader: 'Segera upload ulang bukti pembayaran akreditasi melalui sistem DAISY.',
                    ),
                    [],
                    [],
                    true
                );

                $sent++;
            }

            DB::commit();
            return back()->with('success', "Pengingat pembayaran berhasil dikirim ke {$sent} program studi.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Kirim reminder UPPS gagal', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }
}
