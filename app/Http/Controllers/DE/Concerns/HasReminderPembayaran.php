<?php

namespace App\Http\Controllers\DE\Concerns;

use App\Mail\Reminder\ReminderContextMail;
use App\Models\PengajuanPembayaran;
use App\Models\User;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasReminderPembayaran
{
    /**
     * Reminder ke Keuangan: segera validasi bukti pembayaran.
     * Mendukung jenis_pembayaran: 'akreditasi' | 'banding'
     */
    protected function processKirimReminderKeuangan(
        Request $request,
        string $jenisPembayaran = 'akreditasi',
    ): \Illuminate\Http\RedirectResponse {
        $validated = $request->validate([
            'id_pembayaran'   => 'required|array',
            'id_pembayaran.*' => 'exists:pengajuan_pembayaran,id',
            'pesan_reminder'  => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $pembayarans = PengajuanPembayaran::with([
                'pengajuan.studyProgram.university',
            ])
                ->whereIn('id', $validated['id_pembayaran'])
                ->where('jenis_pembayaran', $jenisPembayaran)
                ->where('status_pembayaran', 'menunggu_verifikasi')
                ->get();

            /** @var RecipientResolverService $resolver */
            $resolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $delivery */
            $delivery = app(MailDeliveryService::class);

            $keuanganUsers  = User::where('role_selected', 'keuangan_lamdepilar')->with('activeEmails')->get();
            $keuanganEmails = $resolver->emailsForUsers($keuanganUsers);

            if (empty($keuanganEmails)) {
                return back()->with('error', 'Tidak ada akun keuangan yang ditemukan.');
            }

            $isBanding   = $jenisPembayaran === 'banding';
            $labelJenis  = $isBanding ? 'Banding' : 'Akreditasi';
            $sent        = 0;

            foreach ($pembayarans as $pembayaran) {
                $pengajuan   = $pembayaran->pengajuan;
                $namaProdi   = $pembayaran->pengajuan->studyProgram->name ?? '-';
                $namaUniv    = $pembayaran->pengajuan->studyProgram->university->name ?? '-';
                $contextInfo = 'Berikut adalah pesan dari LAMDEPILAR mengenai proses pembayaran akreditasi untuk ';
                $contextInfo .= implode(' | ', array_filter([
                    $namaProdi,
                    $namaUniv,
                    'Invoice: ' . $pembayaran->nomor_invoice,
                ]));

                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to'   => $pengajuan->status,
                    'changed_by'  => auth()->id(),
                    'changed_at'  => now(),
                    'keterangan'  => "Pengingat validasi pembayaran {$jenisPembayaran} dikirim ke Keuangan. Invoice: {$pembayaran->nomor_invoice}",
                ]);

                $actionUrl = $isBanding
                    ? route('keuangan.pembayaran.show', $pembayaran->id)
                    : route('keuangan.pembayaran.show', $pembayaran->id);

                $delivery->sendToEmails(
                    $keuanganEmails,
                    new ReminderContextMail(
                        recipientName: 'Bagian Keuangan LAMDEPILAR',
                        pesanReminder: $validated['pesan_reminder'],
                        subject: "Pengingat Validasi Pembayaran {$labelJenis}",
                        actionUrl: $actionUrl,
                        actionLabel: 'Validasi Pembayaran',
                        contextInfo: $contextInfo,
                        headerTitle: "Pengingat Validasi Pembayaran {$labelJenis}",
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
            Log::error("Kirim reminder keuangan {$jenisPembayaran} gagal", ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }

    /**
     * Reminder ke UPPS: segera bayar invoice.
     * Mendukung jenis_pembayaran: 'akreditasi' | 'banding'
     */
    protected function processKirimReminderUPPS(
        Request $request,
        string $jenisPembayaran = 'akreditasi',
    ): \Illuminate\Http\RedirectResponse {
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
                ->where('jenis_pembayaran', $jenisPembayaran)
                ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'upload_ulang'])
                ->get();

            /** @var RecipientResolverService $resolver */
            $resolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $delivery */
            $delivery = app(MailDeliveryService::class);

            $isBanding  = $jenisPembayaran === 'banding';
            $labelJenis = $isBanding ? 'Banding' : 'Akreditasi';
            $sent       = 0;

            foreach ($pembayarans as $pembayaran) {
                $pengajuan     = $pembayaran->pengajuan;
                $namaProdi   = $pembayaran->pengajuan->studyProgram->name ?? '-';
                $namaUniv    = $pembayaran->pengajuan->studyProgram->university->name ?? '-';
                $contextInfo = 'Berikut adalah pesan dari LAMDEPILAR mengenai proses pembayaran akreditasi untuk ';
                $contextInfo .= implode(' | ', array_filter([
                    $namaProdi,
                    $namaUniv,
                    'Invoice: ' . $pembayaran->nomor_invoice,
                    $pembayaran->tanggal_jatuh_tempo
                        ? 'Jatuh Tempo: ' . Carbon::parse($pembayaran->tanggal_jatuh_tempo)->translatedFormat('d M Y')
                        : null,
                ]));
                $isUploadUlang = $pembayaran->status_pembayaran === 'upload_ulang';

                $prodUsers = $pengajuan->studyProgram?->users;
                $emails    = $prodUsers && $prodUsers->isNotEmpty()
                    ? $resolver->emailsForUsers($prodUsers)
                    : $resolver->emailsForUser($pengajuan->pengaju);

                if (empty($emails)) continue;

                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to'   => $pengajuan->status,
                    'changed_by'  => auth()->id(),
                    'changed_at'  => now(),
                    'keterangan'  => "Pengingat pembayaran {$jenisPembayaran} dikirim ke UPPS {$namaProdi}",
                ]);

                // Route show UPPS sesuai jenis
                $actionUrl = $isBanding
                    ? route('upps.pelaksanaan-banding.show', $pengajuan->id)
                    : route('upps.validasi-pembayaran.show', $pembayaran->id);

                $delivery->sendToEmails(
                    $emails,
                    new ReminderContextMail(
                        recipientName: 'Tim Akreditasi Program Studi <strong>' . $namaProdi . '</strong>',
                        pesanReminder: $validated['pesan_reminder'],
                        subject: $isUploadUlang
                            ? "Pengingat Upload Ulang Bukti Pembayaran {$labelJenis}"
                            : "Pengingat Pembayaran {$labelJenis}",
                        actionUrl: $actionUrl,
                        actionLabel: $isUploadUlang ? 'Upload Ulang Bukti' : 'Buka Halaman Pembayaran',
                        contextInfo: $contextInfo,
                        headerTitle: $isUploadUlang
                            ? "Pengingat Upload Ulang Bukti Pembayaran {$labelJenis}"
                            : "Pengingat Pembayaran {$labelJenis}",
                        preheader: $isUploadUlang
                            ? "Segera upload ulang bukti pembayaran {$jenisPembayaran} melalui sistem DAISY."
                            : "Segera selesaikan pembayaran {$jenisPembayaran} sebelum jatuh tempo.",
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
            Log::error("Kirim reminder UPPS {$jenisPembayaran} gagal", ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }

    /**
     * Helper: data pending validasi untuk modal reminder keuangan.
     */
    protected function getPendingValidasi(string $jenisPembayaran = 'akreditasi')
    {
        return PengajuanPembayaran::with(['pengajuan.studyProgram.university'])
            ->where('jenis_pembayaran', $jenisPembayaran)
            ->where('status_pembayaran', 'menunggu_verifikasi')
            ->get();
    }

    /**
     * Helper: data pending pembayaran untuk modal reminder UPPS.
     */
    protected function getPendingPembayaran(string $jenisPembayaran = 'akreditasi')
    {
        return PengajuanPembayaran::with(['pengajuan.studyProgram.university'])
            ->where('jenis_pembayaran', $jenisPembayaran)
            ->whereIn('status_pembayaran', ['menunggu_pembayaran', 'upload_ulang'])
            ->get();
    }
}
