<?php

namespace App\Http\Controllers\DE\Concerns;

use App\Mail\Reminder\ReminderPelaporanMail;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait HasReminderPelaporan
{
    /**
     * Ambil assignments yang belum lapor untuk ditampilkan di modal.
     */
    protected function getPendingReminderAssignments(
        string $jenisAsesmen,
        string $documentType,
        array $requiredStatuses = []
    ) {
        $query = AsesmenUserRole::with([
            'user',
            'asesmen.pengajuan.studyProgram',
        ])
            ->where('jenis_asesmen', $jenisAsesmen)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role_selected', fn($q) => $q->where('name', 'validator'))
            ->whereDoesntHave(
                'asesmen.documents',
                fn($q) =>
                $q->where('type', $documentType)->where('is_active', true)
            );

        if (!empty($requiredStatuses)) {
            $query->whereHas(
                'asesmen.pengajuan.statusLog',
                fn($q) =>
                $q->whereIn('status_to', $requiredStatuses)
            );
        }

        return $query->get();
    }

    /**
     * Proses pengiriman reminder pelaporan.
     *
     * @param  string  $mailSubject     Subject email
     * @param  string  $actionRouteName Named route untuk tombol di email
     * @param  string  $keteranganLog   Keterangan yang disimpan di status log
     */
    protected function processKirimReminder(
        Request $request,
        string $jenisAsesmen,
        string $documentType,
        string $mailSubject,
        string $actionRouteName,
        array $requiredStatuses = [],
        string $keteranganLog = 'Pengingat pelaporan dikirim ke validator'
    ) {
        $validated = $request->validate([
            'id_assignment'   => 'required|array',
            'id_assignment.*' => 'exists:asesmen_user_roles,id',
            'pesan_reminder'  => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $query = AsesmenUserRole::with(['user', 'asesmen.pengajuan'])
                ->whereIn('id', $validated['id_assignment'])
                ->where('jenis_asesmen', $jenisAsesmen)
                ->where('status_penawaran', 'accepted')
                ->whereHas('role_selected', fn($q) => $q->where('name', 'validator'))
                ->whereDoesntHave(
                    'asesmen.documents',
                    fn($q) =>
                    $q->where('type', $documentType)->where('is_active', true)
                );

            if (!empty($requiredStatuses)) {
                $query->whereHas(
                    'asesmen.pengajuan.statusLog',
                    fn($q) =>
                    $q->whereIn('status_to', $requiredStatuses)
                );
            }

            $assignments = $query->get();

            /** @var RecipientResolverService $recipientResolver */
            $recipientResolver = app(RecipientResolverService::class);
            /** @var MailDeliveryService $mailDelivery */
            $mailDelivery = app(MailDeliveryService::class);

            $sent = 0;

            foreach ($assignments as $assignment) {
                $pengajuan = $assignment->asesmen->pengajuan;

                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to'   => $pengajuan->status,
                    'changed_by'  => auth()->id(),
                    'changed_at'  => now(),
                    'keterangan'  => $keteranganLog . ' ' . $assignment->user->name,
                ]);

                $mailDelivery->sendToEmails(
                    $recipientResolver->emailsForUsers(collect([$assignment->user])),
                    new ReminderPelaporanMail(
                        assignment: $assignment,
                        pesanReminder: $validated['pesan_reminder'],
                        subject: $mailSubject,
                        actionUrl: route($actionRouteName, $assignment->id),
                        preheader: '',
                        headerTitle: $mailSubject
                    ),
                    [],
                    [],
                    true
                );

                $sent++;
            }

            DB::commit();

            return back()->with('success', "Pengingat berhasil dikirim ke {$sent} validator.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }
}
