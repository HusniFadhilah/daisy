<?php
// app/Http/Controllers/DE/PenyampaianTemplateController.php

namespace App\Http\Controllers\DE;

use App\Models\User;
use App\Models\University;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PenyampaianTemplateController extends Controller
{
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
            'notification_id' => 'nullable|exists:notifications,id', // ✅ ADD: untuk mark notification as read
        ], [
            'template_led_link.required' => 'Link Templat Dokumen wajib diisi',
            'template_led_link.url' => 'Format link Templat Dokumen tidak valid',
            'template_pembayaran_link.required' => 'Link Templat Formulir Pembayaran wajib diisi',
            'template_pembayaran_link.url' => 'Format link Templat Formulir Pembayaran tidak valid',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // ✅ CHECK: Apakah ini upload ulang atau pertama kali
        $isReupload = $pengajuan->dokumen()
            ->whereIn('jenis_dokumen', ['borang_template', 'template_formulir_pembayaran'])
            ->where('is_latest', true)
            ->exists();

        DB::beginTransaction();
        try {
            if ($isReupload) {
                // ✅ Mark dokumen lama sebagai not latest
                $pengajuan->dokumen()
                    ->whereIn('jenis_dokumen', ['borang_template', 'template_formulir_pembayaran'])
                    ->where('is_latest', true)
                    ->update(['is_latest' => false]);
            }

            // ✅ Get versi baru
            $versiLED = $pengajuan->dokumen()
                ->where('jenis_dokumen', 'borang_template')
                ->max('versi') ?? 0;

            $versiPembayaran = $pengajuan->dokumen()
                ->where('jenis_dokumen', 'template_formulir_pembayaran')
                ->max('versi') ?? 0;

            // Simpan Templat Dokumen (versi baru)
            $pengajuan->dokumen()->create([
                'original_filename' => 'Link Templat Dokumen',
                'nama_file' => 'Link Templat Dokumen',
                'jenis_dokumen' => 'borang_template',
                'template_link' => $request->template_led_link,
                'keterangan' => $request->keterangan ?? 'Templat Dokumen via link',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
                'versi' => $versiLED + 1, // ✅ Increment versi
            ]);

            // Simpan Templat Formulir Pembayaran (versi baru)
            $pengajuan->dokumen()->create([
                'original_filename' => 'Link Templat Formulir Pembayaran',
                'nama_file' => 'Link Templat Formulir Pembayaran',
                'jenis_dokumen' => 'template_formulir_pembayaran',
                'template_link' => $request->template_pembayaran_link,
                'keterangan' => $request->keterangan ?? 'Templat Formulir Pembayaran via link',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
                'versi' => $versiPembayaran + 1, // ✅ Increment versi
            ]);

            // ✅ Update status jika pertama kali
            if (!$isReupload) {
                $oldStatus = $pengajuan->status;
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                    'tanggal_template_led_dikirim' => now(),
                ]);

                // Log status change
                $pengajuan->statusLog()->create([
                    'status_from' => $oldStatus,
                    'status_to' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => 'Formulir Pembayaran dan Templat Dokumen telah dikirim oleh LAMDEPILAR',
                ]);
            }

            // ✅ Mark notification as read jika ada
            if ($request->filled('notification_id')) {
                Notification::where('id', $request->notification_id)
                    ->update(['read_at' => now()]);

                // Kirim konfirmasi ke pemohon
                $notification = Notification::find($request->notification_id);
                if ($notification) {
                    $data = json_decode($notification->data, true);

                    // Get user yang request (biasanya admin_prodi)
                    $requester = User::where('name', $data['requested_by'] ?? null)->first();

                    if ($requester) {
                        Notification::create([
                            'notifiable_type' => 'App\Models\User',
                            'notifiable_id' => $requester->id,
                            'type' => 'template_upload_completed',
                            'data' => json_encode([
                                'id_pengajuan' => $pengajuan->id,
                                'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                                'message' => 'Permintaan pengiriman ulang templat telah diproses oleh LAMDEPILAR.',
                                'processed_at' => now()->toISOString(),
                            ]),
                            'read_at' => null,
                        ]);
                    }
                }
            }

            DB::commit();

            $message = $isReupload
                ? 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim ulang via link.'
                : 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim via link.';

            return redirect()
                ->route('de.penyampaian-template')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error kirim templat link: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim templat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim templat via upload file
     */
    public function kirimTemplateUpload(Request $request, $id)
    {
        // ✅ CHECK: Apakah ini upload ulang
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        $hasExistingTemplate = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'borang_template')
            ->where('is_latest', true)
            ->exists();

        $hasExistingFormulir = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'template_formulir_pembayaran')
            ->where('is_latest', true)
            ->exists();

        $isReupload = $hasExistingTemplate || $hasExistingFormulir;

        // ✅ Dynamic validation - file optional jika re-upload
        $rules = [
            'keterangan' => 'nullable|string|max:1000',
            'notification_id' => 'nullable|exists:notifications,id',
        ];

        if (!$hasExistingTemplate) {
            $rules['file_template_led'] = 'required|file|mimes:pdf,zip,rar,docx|max:51200';
        } else {
            $rules['file_template_led'] = 'nullable|file|mimes:pdf,zip,rar,docx|max:51200';
        }

        if (!$hasExistingFormulir) {
            $rules['file_template_pembayaran'] = 'required|file|mimes:pdf,docx,xlsx,xls|max:10240';
        } else {
            $rules['file_template_pembayaran'] = 'nullable|file|mimes:pdf,docx,xlsx,xls|max:10240';
        }

        $messages = [
            'file_template_led.required' => 'File Templat Dokumen wajib diupload',
            'file_template_led.mimes' => 'Format file Templat LED harus PDF, ZIP, RAR, atau DOCX',
            'file_template_led.max' => 'Ukuran file Templat LED maksimal 50MB',
            'file_template_pembayaran.required' => 'File Templat Formulir Pembayaran wajib diupload',
            'file_template_pembayaran.mimes' => 'Format file Templat Pembayaran harus PDF, DOCX, XLSX, atau XLS',
            'file_template_pembayaran.max' => 'Ukuran file Templat Pembayaran maksimal 10MB',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            // ✅ Upload Templat Dokumen (jika ada file baru)
            if ($request->hasFile('file_template_led')) {
                // Mark old as not latest
                $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'borang_template')
                    ->where('is_latest', true)
                    ->update(['is_latest' => false]);

                // Get versi baru
                $versiLED = $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'borang_template')
                    ->max('versi') ?? 0;

                $fileLED = $request->file('file_template_led');
                $filenameLED = time() . '_LED_' . str_replace(' ', '_', $fileLED->getClientOriginalName());
                $pathLED = $fileLED->storeAs('dokumen/template-borang', $filenameLED, 'public');

                $pengajuan->dokumen()->create([
                    'jenis_dokumen' => 'borang_template',
                    'nama_file' => $filenameLED,
                    'path_file' => $pathLED,
                    'original_filename' => $fileLED->getClientOriginalName(),
                    'file_size' => $fileLED->getSize(),
                    'mime_type' => $fileLED->getMimeType(),
                    'keterangan' => $request->keterangan ?? 'Templat Dokumen',
                    'uploaded_by' => auth()->id(),
                    'is_latest' => true,
                    'versi' => $versiLED + 1,
                ]);
            }

            // ✅ Upload Templat Formulir Pembayaran (jika ada file baru)
            if ($request->hasFile('file_template_pembayaran')) {
                // Mark old as not latest
                $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'template_formulir_pembayaran')
                    ->where('is_latest', true)
                    ->update(['is_latest' => false]);

                // Get versi baru
                $versiPembayaran = $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'template_formulir_pembayaran')
                    ->max('versi') ?? 0;

                $filePembayaran = $request->file('file_template_pembayaran');
                $filenamePembayaran = time() . '_PEMBAYARAN_' . str_replace(' ', '_', $filePembayaran->getClientOriginalName());
                $pathPembayaran = $filePembayaran->storeAs('dokumen/template-pembayaran', $filenamePembayaran, 'public');

                $pengajuan->dokumen()->create([
                    'jenis_dokumen' => 'template_formulir_pembayaran',
                    'nama_file' => $filenamePembayaran,
                    'path_file' => $pathPembayaran,
                    'original_filename' => $filePembayaran->getClientOriginalName(),
                    'file_size' => $filePembayaran->getSize(),
                    'mime_type' => $filePembayaran->getMimeType(),
                    'keterangan' => $request->keterangan ?? 'Templat Formulir Pembayaran',
                    'uploaded_by' => auth()->id(),
                    'is_latest' => true,
                    'versi' => $versiPembayaran + 1,
                ]);
            }

            // ✅ Update status jika pertama kali
            if (!$isReupload) {
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

            // ✅ Mark notification as read
            if ($request->filled('notification_id')) {
                $notification = Notification::find($request->notification_id);

                if ($notification && $notification->notifiable_id === Auth::id()) {
                    // Mark as read
                    $notification->update(['read_at' => now()]);

                    // Parse data
                    $data = $notification->data;

                    // ✅ Cari pemohon berdasarkan requested_by_id atau nama
                    $requesterId = $data['requested_by_id'] ?? null;
                    $requester = null;

                    if ($requesterId) {
                        $requester = User::find($requesterId);
                    }

                    // Fallback: cari by name jika ID tidak ada
                    if (!$requester && isset($data['requested_by'])) {
                        $requester = User::where('name', $data['requested_by'])->first();
                    }

                    // Fallback: cari by program studi (admin prodi dari prodi ini)
                    if (!$requester) {
                        $requester = $pengajuan->studyProgram->users()
                            ->where('role_selected', 'admin_prodi')
                            ->first();
                    }

                    // Kirim konfirmasi jika requester ditemukan
                    if ($requester) {
                        $jenisDokumenLabel = $data['jenis_dokumen_label'] ??
                            $data['jenis_dokumen'] ??
                            ['Formulir dan Templat'];

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
                }
            }

            DB::commit();

            $message = $isReupload
                ? 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim ulang.'
                : 'Formulir Pembayaran dan Templat Dokumen berhasil dikirim.';

            return redirect()
                ->route('de.penyampaian-template')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error kirim templat upload: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengirim templat: ' . $e->getMessage());
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
}
