<?php

namespace App\Http\Controllers\Asesmen;

use App\Http\Controllers\Controller;
use App\Mail\Reminder\ReminderPenawaranAsesmenMail;
use App\Mail\Reminder\ReminderProgressAsesmenMail;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use App\Models\AsesmenUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReminderController extends Controller
{
    private RecipientResolverService $recipientResolver;
    private MailDeliveryService $mailDelivery;

    public function __construct(
        RecipientResolverService $recipientResolver,
        MailDeliveryService $mailDelivery
    ) {
        $this->recipientResolver = $recipientResolver;
        $this->mailDelivery = $mailDelivery;
    }

    public function kirimReminderAssignment(Request $request, $assignmentId)
    {
        $request->validate([
            'pesan_reminder' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $assignment = AsesmenUserRole::with([
                'user.activeEmails',
                'role_selected',
                'asesmen.pengajuan.studyProgram.university',
                'asesmen.pengajuan.studyProgram.degreeLevel',
            ])->findOrFail($assignmentId);

            if ($assignment->jenis_asesmen !== 'ak') {
                return back()->with('error', 'Pengingat hanya berlaku untuk penugasan AK.');
            }

            $meta = $assignment->resolveReminderMeta();

            if (!$meta) {
                return back()->with('error', 'Penugasan ini tidak memenuhi syarat untuk dikirim pengingat.');
            }

            $pesanReminder = $request->filled('pesan_reminder')
                ? $request->pesan_reminder
                : $meta['message'];

            $recipientEmails = $this->recipientResolver->emailsForUsers(
                collect([$assignment->user])
            );

            if ($meta['type'] === 'penawaran') {
                $result = $this->mailDelivery->sendToEmails(
                    $recipientEmails,
                    new ReminderPenawaranAsesmenMail($assignment, $pesanReminder),
                    [],
                    [],
                    false
                );
            } else {
                $result = $this->mailDelivery->sendToEmails(
                    $recipientEmails,
                    new ReminderProgressAsesmenMail($assignment, $pesanReminder),
                    [],
                    [],
                    false
                );
            }

            $pengajuan = $assignment->asesmen->pengajuan;

            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Pengingat ' . $meta['label'] . ' dikirim ke ' .
                    ($assignment->role_selected->alias ?? $assignment->role_selected->name) .
                    ' ' . $assignment->user->name,
            ]);

            DB::commit();

            $totalEmail = count($result['sent_to'] ?? []);

            return back()->with('success', "Pengingat berhasil dikirim ke {$assignment->user->name}. Total {$totalEmail} email terkirim.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('kirimReminderAssignment failed', [
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }
}
