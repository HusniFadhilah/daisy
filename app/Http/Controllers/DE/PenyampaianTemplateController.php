<?php
// app/Http/Controllers/DE/PenyampaianTemplateController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Mail\BorangTemplateSentMail;
use App\Models\Notification;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanStatusLog;
use App\Models\University;
use App\Models\User;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PenyampaianTemplateController extends Controller
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

    /**
     * Display list of permohonan yang perlu dikirim templat
     */
    public function index(Request $request)
    {
        // Ambil status terakhir setiap pengajuan
        $latestStatus = PengajuanStatusLog::select('id_pengajuan', 'status_to')
            ->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ])
            ->orderByDesc('changed_at')
            ->get()
            ->unique('id_pengajuan');

        // Query pengajuan dengan status terakhir sesuai filter
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
        ])
            ->whereIn('id', $latestStatus->pluck('id_pengajuan'));

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_university', $request->university_id);
            });
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun_akreditasi', $request->tahun);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort - prioritas ke tanggal_surat_permohonan_diterima
        $sortBy = $request->get('sort_by', 'tanggal_surat_permohonan_diterima');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
        ])
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        // Ambil pengajuan yang sudah template_led_dikirim tapi belum ada invoice
        $pengajuanList = \App\Models\PengajuanAkreditasi::with('studyProgram.degreeLevel', 'studyProgram.university')
            ->where('status', \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM)
            ->whereDoesntHave('pembayaran')
            ->get();
        $countPengajuanList = count($pengajuanList);

        return view('de.penyampaian-template.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList',
            'pengajuanList',
            'countPengajuanList'
        ));
    }

    /**
     * Show detail permohonan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'dokumen' => function ($q) {
                $q->whereIn('jenis_dokumen', ['borang_template', 'template_formulir_pembayaran'])
                    ->where('is_latest', true);
            },
            'statusLog',
        ])->findOrFail($id);

        // ✅ GET: Notifikasi permintaan upload ulang
        $uploadRequests = Notification::where('notifiable_id', Auth::id())->where('notifiable_type', 'App\Models\User')
            ->where('type', 'template_upload_request')
            ->whereNull('read_at')
            ->whereRaw("JSON_EXTRACT(data, '$.id_pengajuan') = ?", [(int)$id])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $templateLed = $pengajuan->dokumen->where('jenis_dokumen', 'borang_template')->where('is_latest', true)->first();
        $formulirPembayaran = $pengajuan->dokumen->where('jenis_dokumen', 'template_formulir_pembayaran')->where('is_latest', true)->first();
        $hasExistingDokumen = $templateLed || $formulirPembayaran;
        return view('de.penyampaian-template.show', compact('pengajuan', 'uploadRequests', 'templateLed', 'formulirPembayaran', 'hasExistingDokumen'));
    }

    /**
     * Kirim templat via link
     */
    public function kirimTemplateLink(Request $request, $id)
    {
        $request->validate([
            'template_led_link' => 'required|url|max:500',
            'template_pembayaran_link' => 'required|url|max:500',
            'keterangan' => 'nullable|string|max:1000',
            'notification_id' => 'nullable|exists:notifications,id',
        ], [
            'template_led_link.required' => 'Link Templat Dokumen wajib diisi',
            'template_led_link.url' => 'Format link Templat Dokumen tidak valid',
            'template_pembayaran_link.required' => 'Link Templat Formulir Pembayaran wajib diisi',
            'template_pembayaran_link.url' => 'Format link Templat Formulir Pembayaran tidak valid',
        ]);

        $pengajuan = PengajuanAkreditasi::with([
            'pengaju.activeEmails',
            'studyProgram.users.activeEmails',
            'studyProgram.degreeLevel',
            'studyProgram.university',
        ])->findOrFail($id);

        $isReupload = $this->hasExistingTemplateDocuments($pengajuan);

        DB::beginTransaction();

        try {
            if ($isReupload) {
                $this->markExistingTemplatesAsNotLatest($pengajuan);
            }

            $templateLed = $pengajuan->dokumen()->create([
                'original_filename' => 'Link Templat Dokumen',
                'nama_file' => 'Link Templat Dokumen',
                'jenis_dokumen' => 'borang_template',
                'template_link' => $request->template_led_link,
                'keterangan' => $request->keterangan ?? 'Templat Dokumen via link',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
                'versi' => $this->nextVersion($pengajuan, 'borang_template'),
            ]);

            $pengajuan->dokumen()->create([
                'original_filename' => 'Link Templat Formulir Pembayaran',
                'nama_file' => 'Link Templat Formulir Pembayaran',
                'jenis_dokumen' => 'template_formulir_pembayaran',
                'template_link' => $request->template_pembayaran_link,
                'keterangan' => $request->keterangan ?? 'Templat Formulir Pembayaran via link',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
                'versi' => $this->nextVersion($pengajuan, 'template_formulir_pembayaran'),
            ]);

            $this->updateStatusIfFirstSend($pengajuan, $isReupload);

            $this->markNotificationAsReadAndReply($request->notification_id, $pengajuan);

            $this->sendTemplateEmail($pengajuan, $templateLed, 'link');

            DB::commit();

            return redirect()
                ->route('de.penyampaian-template')
                ->with(
                    'success',
                    $isReupload
                        ? 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim ulang via link.'
                        : 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim via link.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error kirim templat link', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Gagal mengirim templat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim templat via upload file
     */
    public function kirimTemplateUpload(Request $request, $id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'pengaju.activeEmails',
            'studyProgram.users.activeEmails',
            'studyProgram.degreeLevel',
            'studyProgram.university',
        ])->findOrFail($id);

        $hasExistingTemplate = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'borang_template')
            ->where('is_latest', true)
            ->exists();

        $hasExistingFormulir = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'template_formulir_pembayaran')
            ->where('is_latest', true)
            ->exists();

        $isReupload = $hasExistingTemplate || $hasExistingFormulir;

        $rules = [
            'keterangan' => 'nullable|string|max:1000',
            'notification_id' => 'nullable|exists:notifications,id',
            'file_template_led' => $hasExistingTemplate
                ? 'nullable|file|mimes:pdf,zip,rar,docx|max:51200'
                : 'required|file|mimes:pdf,zip,rar,docx|max:51200',
            'file_template_pembayaran' => $hasExistingFormulir
                ? 'nullable|file|mimes:pdf,docx,xlsx,xls|max:10240'
                : 'required|file|mimes:pdf,docx,xlsx,xls|max:10240',
        ];

        $messages = [
            'file_template_led.required' => 'File Templat Dokumen wajib diupload',
            'file_template_led.mimes' => 'Format file Templat LED harus PDF, ZIP, RAR, atau DOCX',
            'file_template_led.max' => 'Ukuran file Templat LED maksimal 50MB',
            'file_template_pembayaran.required' => 'File Templat Formulir Pembayaran wajib diupload',
            'file_template_pembayaran.mimes' => 'Format file Templat Pembayaran harus PDF, DOCX, XLSX, atau XLS',
            'file_template_pembayaran.max' => 'Ukuran file Templat Pembayaran maksimal 10MB',
        ];

        $request->validate($rules, $messages);

        $storedPaths = [];
        DB::beginTransaction();

        try {
            $templateLed = null;

            if ($request->hasFile('file_template_led')) {
                $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'borang_template')
                    ->where('is_latest', true)
                    ->update(['is_latest' => false]);

                $file = $request->file('file_template_led');
                $filename = time() . '_LED_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('dokumen/template-borang', $filename, 'public');
                $storedPaths[] = $path;

                $templateLed = $pengajuan->dokumen()->create([
                    'jenis_dokumen' => 'borang_template',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'keterangan' => $request->keterangan ?? 'Templat Dokumen',
                    'uploaded_by' => auth()->id(),
                    'is_latest' => true,
                    'versi' => $this->nextVersion($pengajuan, 'borang_template'),
                ]);
            }

            if ($request->hasFile('file_template_pembayaran')) {
                $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'template_formulir_pembayaran')
                    ->where('is_latest', true)
                    ->update(['is_latest' => false]);

                $file = $request->file('file_template_pembayaran');
                $filename = time() . '_PEMBAYARAN_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('dokumen/template-pembayaran', $filename, 'public');
                $storedPaths[] = $path;

                $pengajuan->dokumen()->create([
                    'jenis_dokumen' => 'template_formulir_pembayaran',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'keterangan' => $request->keterangan ?? 'Templat Formulir Pembayaran',
                    'uploaded_by' => auth()->id(),
                    'is_latest' => true,
                    'versi' => $this->nextVersion($pengajuan, 'template_formulir_pembayaran'),
                ]);
            }

            $this->updateStatusIfFirstSend($pengajuan, $isReupload);
            $this->markNotificationAsReadAndReply($request->notification_id, $pengajuan);

            if ($templateLed) {
                $this->sendTemplateEmail($pengajuan, $templateLed, 'upload');
            }

            DB::commit();

            return redirect()
                ->route('de.penyampaian-template')
                ->with(
                    'success',
                    $isReupload
                        ? 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim ulang.'
                        : 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            foreach ($storedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Error kirim templat upload', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Gagal mengirim templat: ' . $e->getMessage());
        }
    }

    /**
     * Download templat yang sudah dikirim
     */
    public function download($id, $jenis = 'borang_template')
    {
        $pengajuanDokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', $jenis)
            ->where('is_latest', true)
            ->firstOrFail();

        // Jika link
        if ($pengajuanDokumen->template_link) {
            return redirect($pengajuanDokumen->template_link);
        }

        return $pengajuanDokumen->downloadDokumen();
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        $base = PengajuanAkreditasi::query();

        // Total: pernah ada permohonan atau template LED terkait
        $total = (clone $base)
            ->whereHas('statusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                    PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                ]);
            })
            ->count();

        // Belum dikirim: status terakhir SURAT_PERMOHONAN_DITERIMA, belum pernah TEMPLATE_LED_DIKIRIM
        $belumDikirim = (clone $base)
            ->whereHas('statusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                    PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                ]);
            })
            ->whereDoesntHave('statusLog', function ($q) {
                $q->where('status_to', PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM);
            })
            ->count();

        // Sudah dikirim: pernah TEMPLATE_LED_DIKIRIM
        $sudahDikirim = (clone $base)
            ->whereHas('statusLog', function ($q) {
                $q->where('status_to', PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM);
            })
            ->count();
        return [
            'total' => $total,
            'belum_dikirim' => $belumDikirim,
            'sudah_dikirim' => $sudahDikirim,
        ];
    }

    private function hasExistingTemplateDocuments(PengajuanAkreditasi $pengajuan): bool
    {
        return $pengajuan->dokumen()
            ->whereIn('jenis_dokumen', ['borang_template', 'template_formulir_pembayaran'])
            ->where('is_latest', true)
            ->exists();
    }

    private function markExistingTemplatesAsNotLatest(PengajuanAkreditasi $pengajuan): void
    {
        $pengajuan->dokumen()
            ->whereIn('jenis_dokumen', ['borang_template', 'template_formulir_pembayaran'])
            ->where('is_latest', true)
            ->update(['is_latest' => false]);
    }

    private function nextVersion(PengajuanAkreditasi $pengajuan, string $jenisDokumen): int
    {
        return ((int) $pengajuan->dokumen()
            ->where('jenis_dokumen', $jenisDokumen)
            ->max('versi')) + 1;
    }

    private function updateStatusIfFirstSend(PengajuanAkreditasi $pengajuan, bool $isReupload): void
    {
        if ($isReupload) {
            return;
        }

        $oldStatus = $pengajuan->status;

        $pengajuan->update([
            'status' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            'tanggal_template_led_dikirim' => now(),
        ]);

        $pengajuan->statusLog()->create([
            'status_from' => $oldStatus,
            'status_to' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
            'keterangan' => 'Formulir Pembayaran dan Templat Dokumen dikirim oleh LAMDEPILAR',
        ]);
    }

    private function markNotificationAsReadAndReply(?string $notificationId, PengajuanAkreditasi $pengajuan): void
    {
        if (empty($notificationId)) {
            return;
        }

        $notification = Notification::find($notificationId);

        if (!$notification || $notification->notifiable_id !== Auth::id()) {
            return;
        }

        $notification->update(['read_at' => now()]);

        $data = is_array($notification->data)
            ? $notification->data
            : json_decode($notification->data, true);

        $requester = null;
        $requesterId = $data['requested_by_id'] ?? null;

        if ($requesterId) {
            $requester = User::find($requesterId);
        }

        if (!$requester && !empty($data['requested_by'])) {
            $requester = User::where('name', $data['requested_by'])->first();
        }

        if (!$requester) {
            $requester = $pengajuan->studyProgram->users()
                ->where('role_selected', 'admin_prodi')
                ->first();
        }

        if (!$requester) {
            return;
        }

        $jenisDokumenLabel = $data['jenis_dokumen_label']
            ?? $data['jenis_dokumen']
            ?? 'Formulir dan Templat';

        Notification::create([
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $requester->id,
            'type' => 'template_upload_request_completed',
            'data' => json_encode([
                'id_pengajuan' => $pengajuan->id,
                'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                'program_studi' => $pengajuan->studyProgram->name,
                'jenis_dokumen_label' => $jenisDokumenLabel,
                'message' => 'Permintaan pengiriman ulang templat telah diproses oleh LAMDEPILAR.',
                'processed_at' => now()->toISOString(),
                'processed_by' => auth()->user()->name,
            ]),
            'read_at' => null,
        ]);
    }

    private function sendTemplateEmail(
        PengajuanAkreditasi $pengajuan,
        PengajuanDokumen $dokumen,
        string $metode
    ): void {
        $emails = [];

        if ($pengajuan->studyProgram && $pengajuan->studyProgram->users) {
            $emails = $this->recipientResolver->emailsForUsers($pengajuan->studyProgram->users);
        }

        if (empty($emails) && $pengajuan->pengaju) {
            $emails = $this->recipientResolver->emailsForUser($pengajuan->pengaju);
        }

        if (empty($emails)) {
            Log::warning('Email template tidak dikirim karena tidak ada email tujuan', [
                'pengajuan_id' => $pengajuan->id,
                'dokumen_id' => $dokumen->id,
                'metode' => $metode,
            ]);
            return;
        }

        $result = $this->mailDelivery->sendToEmails(
            to: $emails,
            mailable: new BorangTemplateSentMail($pengajuan, $dokumen, $metode),
            useQueue: true
        );
    }
}
