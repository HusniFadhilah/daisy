<?php

namespace App\Http\Controllers\Prodi;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Asesmen;
use App\Libraries\Fungsi;
use App\Models\BorangImport;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Mail\PembayaranVerifiedMail;
use App\Models\BorangValidation;
use App\Models\PengajuanDokumen;
use App\Models\PengingatAkreditasi;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanPembayaran;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\BorangTemplateSentMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DeskEvaluatorController extends Controller
{
    /**
     * CONFIG: Set validation mode
     * 'strict' = Only 1 validator allowed
     * 'flexible' = Multiple validators allowed (like AK/AL)
     */
    private const BORANG_VALIDATOR_MODE = 'strict'; // or 'flexible'
    /**
     * Dashboard DE
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Permohonan akreditasiyang di-assign ke DE ini
        $query = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel', // ✅ FIX
            'studyProgram.university',
            'pengaju',
            'asesmen.userRoles' => function ($q) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->with(['user', 'role']);
            },
        ]);

        // Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ✅ ADD: Filter by validator status
        if ($request->filled('validator_status')) {
            $query->whereHas('asesmen.userRoles', function ($q) use ($request) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->where('status_penawaran', $request->validator_status);
            });
        }

        $pengajuans = $query->latest()->paginate(10);

        // Statistics
        $stats = [
            'total' => PengajuanAkreditasi::count(),
            'menunggu_review' => PengajuanAkreditasi::where('status', 'draft_borang_diterima')->count(),
            'menunggu_pembayaran' => PengajuanAkreditasi::where('status', 'menunggu_pembayaran')->count(),
            'siap_lanjut' => PengajuanAkreditasi::where('status', 'pengajuan_completed')->count(),
            'menunggu_validator' => PengajuanAkreditasi::where('status', 'borang_validation_pending')->count(),
            'in_validation' => PengajuanAkreditasi::where('status', 'borang_in_validation')->count(),
            'perlu_revisi' => PengajuanAkreditasi::where('status', 'borang_revision_required')->count(),
            'borang_validated' => PengajuanAkreditasi::where('status', 'borang_validated')->count(),
        ];

        return view('asesmen.de.index', compact('pengajuans', 'stats'));
    }

    /**
     * Show Permohonan akreditasidetail
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'pengaju' => function ($query) {
                $query->select('id', 'name', 'email', 'role');
            },
            'deskEvaluator' => function ($query) {
                $query->select('id', 'name', 'email', 'role');
            },
            'dokumen' => function ($query) {
                $query->with(['uploader' => function ($q) {
                    $q->select('id', 'name', 'email');
                }]);
            },
            'pembayaran' => function ($query) {
                $query->with(['verifier' => function ($q) {
                    $q->select('id', 'name', 'email');
                }]);
            },
            'statusLog' => function ($query) {
                $query->with(['changedBy' => function ($q) {
                    $q->select('id', 'name', 'email');
                }])->latest();
            }
        ])->findOrFail($id);

        $currentValidator = $pengajuan->getCurrentBorangValidator();
        $canAssignValidator = $pengajuan->canAssignValidator();
        $currentStep = $pengajuan->getCurrentStepNumber();
        $nextAllowedStatuses = $pengajuan->getNextAllowedStatuses();
        $canProceedToHasilAkreditasi = $pengajuan->canStartHasilAkreditasi();
        $canProceedToMasaSanggah = $pengajuan->canStartMasaSanggah();

        return view('asesmen.de.show', compact(
            'pengajuan',
            'currentValidator',
            'canAssignValidator',
            'currentStep',
            'nextAllowedStatuses',
            'canProceedToHasilAkreditasi',
            'canProceedToMasaSanggah'
        ));
    }

    public function destroy(Request $request, $id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // optional: authorization tambahan
        // $this->authorize('delete', $pengajuan);

        $pengajuan->delete();

        return redirect()
            ->back()
            ->with('success', 'Data Permohonan akreditasi berhasil dihapus.');
    }

    /**
     * Kirim form borang (Langkah 3)
     */
    public function kirimFormBorang(Request $request, $id)
    {
        $request->validate([
            'metode_kirim'    => 'required|in:link,upload',
            'template_link'   => 'required_if:metode_kirim,link|nullable|url|max:500',
            'borang_template' => 'required_if:metode_kirim,upload|nullable|file|mimes:docx,doc,rar,zip,pdf,xlsx,xls|max:10240',
            'keterangan'      => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::query()
                ->with(['studyProgram.degreeLevel', 'pengaju'])
                ->lockForUpdate()
                ->findOrFail($id);

            if (!in_array($pengajuan->status, [PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM])) {
                DB::rollBack();
                return back()->with('error', 'Status Permohonan akreditasi tidak sesuai untuk kirim templat.');
            }

            // hindari ?. : ambil degree level dengan aman
            $degreeLevel = '-';
            if ($pengajuan->studyProgram && $pengajuan->studyProgram->degreeLevel) {
                $degreeLevel = $pengajuan->studyProgram->degreeLevel->code;
            }

            $metode = $request->input('metode_kirim');

            $pathFile = null;
            $filename = null;
            $originalFilename = null;
            $fileSize = null;
            $mimeType = null;
            $templateLink = null;

            if ($metode === 'upload') {
                $file = $request->file('borang_template');
                if (!$file) {
                    DB::rollBack();
                    return back()->with('error', 'File templat tidak ditemukan.');
                }

                $filename = 'template_dokumen_akreditasi_' . time() . '.' . $file->getClientOriginalExtension();
                $pathFile = $file->storeAs('permohonan-akreditasi/' . $pengajuan->id . '/template', $filename, 'public');

                $originalFilename = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
            } else {
                $templateLink = $request->input('template_link');
                if (!$templateLink) {
                    DB::rollBack();
                    return back()->with('error', 'URL templat wajib diisi.');
                }

                $filename = 'led_template_link_' . time() . '.url';
                $pathFile = 'links/' . $filename;

                $originalFilename = 'TEMPLATE_LEMBAR_EVALUASI_DIRI_' . $degreeLevel;
                $mimeType = 'text/uri-list';
                $fileSize = strlen($templateLink);
            }

            // set dokumen lama is_latest = false
            \App\Models\PengajuanDokumen::query()
                ->where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'borang_template')
                ->update(['is_latest' => false]);

            $dokumen = \App\Models\PengajuanDokumen::create([
                'id_pengajuan'       => $pengajuan->id,
                'jenis_dokumen'      => 'borang_template',
                'nama_file'          => $filename,
                'path_file'          => $pathFile,
                'original_filename'  => $originalFilename,
                'file_size'          => $fileSize,
                'mime_type'          => $mimeType,
                'uploaded_by'        => Auth::id(),
                'keterangan'         => $request->input('keterangan'),
                'is_latest'          => true,
                'template_link'      => $templateLink,
            ]);

            $keteranganLog = ($metode === 'link')
                ? "Templat LED/LKPS dikirim via link: {$templateLink}"
                : "Templat LED/LKPS diupload: {$originalFilename}";

            $this->logStatus($pengajuan, $pengajuan->status, PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM, $keteranganLog);

            // Gate final: kalau kedua dokumen sudah ada, status jadi MENUNGGU_PEMBAYARAN
            $this->tryUpdateStatusMenungguPembayaran($pengajuan);

            // kirim email (tidak menggagalkan transaksi kalau error)
            try {
                if ($pengajuan->pengaju) {
                    Mail::to($pengajuan->pengaju->email)->send(
                        new \App\Mail\BorangTemplateSentMail($pengajuan, $dokumen, $metode)
                    );
                }
            } catch (\Exception $e) {
                Log::error('Failed to send templat email', [
                    'pengajuan_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();
            return back()->with('success', 'Template LED/LKPS berhasil dikirim. Status akan menjadi Menunggu Pembayaran setelah formulir pembayaran juga dikirim.');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function kirimFormulirPembayaran(Request $request, $id)
    {
        $request->validate([
            'metode_kirim_pembayaran'     => 'required|in:link,upload',
            'pembayaran_link'             => 'required_if:metode_kirim_pembayaran,link|nullable|url|max:500',
            'formulir_pembayaran_file'    => 'required_if:metode_kirim_pembayaran,upload|nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,zip,rar',
            'nomor_invoice' => 'required|string|max:20|unique:pengajuan_pembayaran,nomor_invoice',
            'jatuh_tempo_hari' => 'required|integer|min:1|max:30',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'keterangan_pembayaran' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::query()
                ->with(['studyProgram', 'pengaju'])
                ->lockForUpdate()
                ->findOrFail($id);

            if (!in_array($pengajuan->status, [PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM])) {
                DB::rollBack();
                return back()->with('error', 'Status Permohonan akreditasi tidak sesuai untuk kirim formulir pembayaran.');
            }

            $metode = $request->input('metode_kirim_pembayaran');

            $pathFile = null;
            $filename = null;
            $originalFilename = null;
            $fileSize = null;
            $mimeType = null;
            $externalUrl = null;

            if ($metode === 'link') {
                $externalUrl = $request->input('pembayaran_link');
                if (!$externalUrl) {
                    DB::rollBack();
                    return back()->with('error', 'URL formulir pembayaran wajib diisi.');
                }

                $filename = 'template_formulir_pembayaran_link_' . time() . '.url';
                $pathFile = 'links/' . $filename;

                $originalFilename = 'TEMPLATE_FORMULIR_PEMBAYARAN (LINK)';
                $mimeType = 'text/uri-list';
                $fileSize = strlen($externalUrl);
            } else {
                $file = $request->file('formulir_pembayaran_file');
                if (!$file) {
                    DB::rollBack();
                    return back()->with('error', 'File formulir pembayaran tidak ditemukan.');
                }

                $filename = 'template_formulir_pembayaran_' . time() . '.' . $file->getClientOriginalExtension();
                $pathFile = $file->storeAs('permohonan-akreditasi/' . $pengajuan->id . '/formulir-pembayaran', $filename, 'public');

                $originalFilename = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
            }

            \App\Models\PengajuanDokumen::query()
                ->where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'template_formulir_pembayaran')
                ->update(['is_latest' => false]);

            $dokumen = \App\Models\PengajuanDokumen::create([
                'id_pengajuan'       => $pengajuan->id,
                'jenis_dokumen'      => 'template_formulir_pembayaran',
                'nama_file'          => $filename,
                'path_file'          => $pathFile,
                'original_filename'  => $originalFilename,
                'file_size'          => $fileSize,
                'mime_type'          => $mimeType,
                'uploaded_by'        => Auth::id(),
                'keterangan'         => $request->input('keterangan_pembayaran'),
                'is_latest'          => true,
                'template_link'      => $externalUrl, // reuse kolom untuk simpan link jika metode=link
            ]);

            $keteranganLog = ($metode === 'link')
                ? "Formulir pembayaran dikirim via link: {$externalUrl}"
                : "Formulir pembayaran diupload: {$originalFilename}";
            $pembayaran = PengajuanPembayaran::updateOrCreate(
                ['id_pengajuan' => $pengajuan->id],
                [
                    // Isi default minimal (sesuaikan kebutuhan Anda)
                    'status_pembayaran' => 'menunggu_pembayaran',   // atau 'menunggu_pembayaran'
                    'nomor_invoice' => $request->nomor_invoice, // kalau ada
                    'tanggal_jatuh_tempo' => Carbon::now()->addDays((int) $request->jatuh_tempo_hari), // kalau ada
                    'jumlah_pembayaran' => $request->jumlah_pembayaran,
                ]
            );

            // Gate final: kalau kedua dokumen sudah ada, status jadi MENUNGGU_PEMBAYARAN
            $this->tryUpdateStatusMenungguPembayaran($pengajuan);

            DB::commit();
            return back()->with('success', 'Formulir pembayaran berhasil dikirim. Status akan menjadi Menunggu Pembayaran setelah templat LED/LKPS juga dikirim.');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Status akhir harus MENUNGGU_PEMBAYARAN jika:
     * - borang_template ada
     * - formulir_pembayaran ada
     */
    private function tryUpdateStatusMenungguPembayaran(\App\Models\PengajuanAkreditasi $pengajuan): void
    {
        $hasTemplate = \App\Models\PengajuanDokumen::query()
            ->where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'borang_template')
            ->exists();

        $hasFormPembayaran = \App\Models\PengajuanDokumen::query()
            ->where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'template_formulir_pembayaran')
            ->exists();

        if (!$hasTemplate || !$hasFormPembayaran) {
            return;
        }

        // hanya naikkan jika masih di tahap surat permohonan diterima
        if (!in_array($pengajuan->status, [PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM])) {
            return;
        }

        $oldStatus = $pengajuan->status;

        $pengajuan->update([
            'status' => PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN,
            'tanggal_template_led_dikirim' => now(), // atau buat field baru jika perlu
        ]);

        $this->logStatus(
            $pengajuan,
            $oldStatus,
            PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN,
            'Formulir pembayaran dan templat dokumen sudah lengkap. Menunggu pembayaran dari PS.'
        );
    }

    /**
     * View parsed borang HTML (Read-only for DE)
     */
    public function viewBorangHTML($pengajuanId, $importId)
    {
        $pengajuan = PengajuanAkreditasi::with('studyProgram.university')->findOrFail($pengajuanId);

        $import = BorangImport::with([
            'sections' => function ($q) {
                $q->with(['elemen', 'tables.dataset'])->orderBy('position');
            }
        ])->findOrFail($importId);

        // Use same preview view
        return view('asesmen.pengajuan.borang-preview', compact('pengajuan', 'import'));
    }

    /**
     * Show form untuk assign validator borang
     */
    public function showAssignValidatorForm($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'latestBorangImport',
            'borangValidators.user',
            'borangValidators.role'
        ])->findOrFail($id);

        // Validation checks
        if (!$pengajuan->canAssignValidator()) {
            return back()->with('error', 'Permohonan akreditasi ini belum siap untuk assign validator.');
        }

        // Get current assignment
        $currentAssignment = $pengajuan->borangValidators()
            ->whereIn('status_penawaran', ['pending', 'accepted'])
            ->with(['user', 'role'])
            ->first();

        // Get available validators (exclude already assigned)
        $excludeUserIds = $pengajuan->borangValidators()
            ->pluck('id_user')
            ->toArray();

        $validators = User::whereJsonContains('roles', 'validator')
            ->when(count($excludeUserIds) > 0, function ($q) use ($excludeUserIds) {
                $q->whereNotIn('id', $excludeUserIds);
            })
            ->orderBy('name')
            ->get();

        return view('asesmen.de.assign-validator', compact(
            'pengajuan',
            'currentAssignment',
            'validators'
        ));
    }

    /**
     * Assign validator untuk borang (STRICT MODE)
     */
    public function assignValidatorBorang(Request $request, $id)
    {
        $request->validate([
            'id_validator' => 'required|exists:users,id',
            'catatan_de' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        $pengajuan = PengajuanAkreditasi::with('latestBorangImport')->lockForUpdate()->findOrFail($id);

        // ============================================
        // VALIDATION CHECKS
        // ============================================
        if (!$pengajuan->canAssignValidator()) {
            return ResponseFormatter::error(null, 'Permohonan akreditasitidak dapat ditugaskan oleh validator.', 422);
        }

        // Check if validator user exists and has validator role
        $validator = User::findOrFail($request->id_validator);
        if (!$validator->hasRole('validator')) {
            return ResponseFormatter::error(null, 'User yang dipilih bukan validator.', 422);
        }

        // ============================================
        // PREVENT DUPLICATE ASSIGNMENT
        // ============================================
        if ($pengajuan->isUserAssignedAsValidator($validator->id)) {
            return ResponseFormatter::error(null, 'Validator ini sudah pernah ditugaskan untuk Permohonan akreditasiini.', 422);
        }

        try {
            // ============================================
            // AUTO-CREATE ASESMEN IF NOT EXISTS
            // ============================================
            if (!$pengajuan->asesmen) {
                $asesmen = Asesmen::create([
                    'id_pengajuan' => $pengajuan->id,
                    'id_study_program' => $pengajuan->id_program_studi,
                    'code' => $pengajuan->nomor_pengajuan,
                    'name' => $pengajuan->judul,
                    // 'description' => 'Asesmen untuk Permohonan akreditasi' . $pengajuan->nomor_pengajuan,
                    'description' => $pengajuan->judul,
                    'status' => 'active',
                ]);
            } else {
                $asesmen = $pengajuan->asesmen;
            }

            // ============================================
            // MODE: STRICT - Delete pending assignments
            // ============================================
            if (self::BORANG_VALIDATOR_MODE === 'strict') {
                // Delete ONLY pending assignments (not accepted/rejected)
                $deletedCount = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                    ->where('jenis_asesmen', 'dokumen')
                    ->where('status_penawaran', 'pending')
                    ->delete();

                if ($deletedCount > 0) {
                    Log::info('Deleted pending dokumen validator assignments', [
                        'pengajuan_id' => $id,
                        'count' => $deletedCount
                    ]);
                }
            }

            // ============================================
            // GET VALIDATOR ROLE
            // ============================================
            $validatorRole = Role::where('name', 'validator')->firstOrFail();

            // ============================================
            // CREATE NEW ASSIGNMENT
            // ============================================
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $asesmen->id,
                'id_user' => $validator->id,
                'id_role' => $validatorRole->id,
                'jenis_asesmen' => 'dokumen',
                'status_penawaran' => 'pending',
                'status_pekerjaan' => 'not_started',
            ]);

            // ============================================
            // CREATE BORANG VALIDATION RECORD
            // ============================================
            BorangValidation::create([
                'id_assignment' => $assignment->id,
                'id_pengajuan' => $pengajuan->id,
                'total_sections' => $pengajuan->latestBorangImport->total_sections ?? 0,
                'validated_sections' => 0,
            ]);

            // ============================================
            // UPDATE Permohonan akreditasi STATUS
            // ============================================
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                'tanggal_validasi_borang_assigned' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                "Validator {$validator->name} ditugaskan untuk review LED. " . ($request->catatan_de ?? '')
            );

            // ============================================
            // SEND EMAIL NOTIFICATION (QUEUED)
            // ============================================
            try {
                Mail::to($validator->email)
                    ->queue(new \App\Mail\ValidatorBorangAssignedMail(
                        $pengajuan,
                        $assignment,
                        $request->catatan_de
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to queue validator assignment email', [
                    'pengajuan_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return ResponseFormatter::success(['assignment_id' => $assignment->id, 'validator' => $validator->name], "Validator {$validator->name} berhasil ditugaskan. Email penawaran sedang diproses.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign borang validator', [
                'pengajuan_id' => $id,
                'validator_id' => $request->id_validator,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ResponseFormatter::error(null, 'Terjadi kesalahan server.', 500);
        }
    }

    /**
     * Reassign validator (jika rejected atau pending)
     */
    public function reassignValidator(Request $request, $validationId)
    {
        $request->validate([
            'id_validator' => 'required|exists:users,id',
            'catatan_de' => 'nullable|string|max:1000',
        ]);

        $oldAssignment = AsesmenUserRole::with(['asesmen.pengajuan'])->findOrFail($validationId);
        $pengajuan = $oldAssignment->asesmen->pengajuan;

        // ============================================
        // VALIDATION: Can only reassign if NOT accepted
        // ============================================
        if ($oldAssignment->status_penawaran === 'accepted') {
            return ResponseFormatter::error(null, 'Tidak dapat menugaskan ulang validator yang sudah diterima.', 422);
        }

        // Check validator role
        $newValidator = User::findOrFail($request->id_validator);
        if (!$newValidator->hasRole('validator')) {
            return ResponseFormatter::error(null, 'User yang dipilih bukan validator.', 422);
        }

        // ============================================
        // PREVENT DUPLICATE
        // ============================================
        if ($pengajuan->isUserAssignedAsValidator($newValidator->id)) {
            return ResponseFormatter::error(null, 'Validator ini sudah pernah ditugaskan.', 422);
        }

        DB::beginTransaction();
        try {
            // Delete old assignment and validation
            BorangValidation::where('id_assignment', $oldAssignment->id)->delete();
            $oldAssignment->delete();

            // Get validator role
            $validatorRole = Role::where('name', 'validator')->firstOrFail();

            // Create new assignment
            $newAssignment = AsesmenUserRole::create([
                'id_asesmen' => $oldAssignment->id_asesmen,
                'id_user' => $newValidator->id,
                'id_role' => $validatorRole->id,
                'jenis_asesmen' => 'dokumen',
                'status_penawaran' => 'pending',
                'status_pekerjaan' => 'not_started',
            ]);

            // Create new validation record
            BorangValidation::create([
                'id_assignment' => $newAssignment->id,
                'id_pengajuan' => $pengajuan->id,
                'total_sections' => $pengajuan->latestBorangImport->total_sections ?? 0,
                'validated_sections' => 0,
            ]);

            // Update status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                'tanggal_validasi_borang_assigned' => now(),
            ]);

            // Log
            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                "Validator ditugaskan ulang ke {$newValidator->name}. " . ($request->catatan_de ?? '')
            );

            // Send email
            try {
                Mail::to($newValidator->email)
                    ->queue(new \App\Mail\ValidatorBorangAssignedMail(
                        $pengajuan,
                        $newAssignment,
                        $request->catatan_de
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to queue validator reassignment email', [
                    'pengajuan_id' => $pengajuan->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();
            return ResponseFormatter::success(null, "Validator berhasil ditugaskan ulang ke {$newValidator->name}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reassign validator', [
                'old_assignment_id' => $validationId,
                'new_validator_id' => $request->id_validator,
                'error' => $e->getMessage(),
            ]);

            return ResponseFormatter::error(null, 'Gagal menugaskan ulang validator: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel validator assignment (if pending)
     */
    public function cancelValidatorAssignment($validationId)
    {
        $assignment = AsesmenUserRole::with(['asesmen.pengajuan'])->findOrFail($validationId);
        $pengajuan = $assignment->asesmen->pengajuan;

        if ($assignment->status_penawaran !== 'pending') {
            return ResponseFormatter::error(null, 'Hanya penugasan yang berstatus "menunggu" yang dapat dibatalkan.', 422);
        }

        DB::beginTransaction();
        try {
            // Delete validation record
            BorangValidation::where('id_assignment', $assignment->id)->delete();

            // Delete assignment
            $assignment->delete();

            // Revert status if no other pending/accepted validators
            $hasOtherValidators = $pengajuan->borangValidators()
                ->whereIn('status_penawaran', ['pending', 'accepted'])
                ->exists();

            if (!$hasOtherValidators) {
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                    'id_validator_assigned' => null,
                ]);
            }

            DB::commit();

            return ResponseFormatter::success(null, 'Penugasan validator berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel validator assignment', [
                'assignment_id' => $validationId,
                'error' => $e->getMessage(),
            ]);

            return ResponseFormatter::error(null, 'Gagal membatalkan penugasan.', 500);
        }
    }

    /**
     * Approve untuk lanjut ke AK (Step 8)
     * Syarat:
     * - LED sudah divalidasi (borang_validated atau validasi_borang_dilaporkan)
     * - Pembayaran sudah verified
     */
    public function approveLanjutAK($id)
    {
        $pengajuan = PengajuanAkreditasi::with(['pembayaran'])->findOrFail($id);

        // ============================================
        // VALIDATION CHECKS
        // ============================================

        // 1. Check status LED (harus validated)
        if (!in_array($pengajuan->status, [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN
        ])) {
            return back()->with('error', 'LED harus divalidasi terlebih dahulu.');
        }

        // 2. Check pembayaran (harus verified)
        if (!$pengajuan->pembayaran || $pengajuan->pembayaran->status_pembayaran !== 'terverifikasi') {
            return back()->with('error', 'Pembayaran belum diverifikasi.');
        }

        // 3. Optional: Check if validator approved (not just validated)
        $validatorApproved = $pengajuan->borangValidators()
            ->where('status_pekerjaan', 'approved')
            ->exists();

        if (!$validatorApproved) {
            return back()->with('error', 'Validator belum menyetujui LED.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $pengajuan->status;

            // Update to Step 8: Penugasan Asesor AK
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                'tanggal_lanjut_ak' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                'LED divalidasi dan pembayaran terverifikasi. Disetujui lanjut ke tahap AK - Menunggu penugasan asesor'
            );

            DB::commit();

            return back()->with('success', 'Permohonan akreditasi disetujui untuk lanjut ke tahap AK. Silakan tugaskan asesor untuk Asesmen Kecukupan.');
        } catch (\Exception $e) {
            Log::error('Failed to approve lanjut AK', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    /**
     * Lapor hasil validasi Dokumen (Step 7)
     * Setelah validator approve, DE melaporkan hasil validasi
     */
    public function laporHasilValidasi($id)
    {
        $pengajuan = PengajuanAkreditasi::with(['borangValidators.borangValidation'])
            ->findOrFail($id);

        // Check status harus borang_validated
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BORANG_VALIDATED) {
            return back()->with('error', 'LED belum divalidasi.');
        }

        // Check if validator approved
        $validatorApproved = $pengajuan->borangValidators()
            ->where('status_pekerjaan', 'approved')
            ->exists();

        if (!$validatorApproved) {
            return back()->with('error', 'Validator belum menyetujui LED.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $pengajuan->status;

            // Update to Step 7: Pelaporan Validasi Dokumen
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                'tanggal_pelaporan_validasi_borang' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                'Laporan Kesiapan LED Program Studi (LKLED) selesai - siap untuk lanjut ke tahap berikutnya'
            );

            DB::commit();

            return back()->with('success', 'Laporan Kesiapan LED Program Studi (LKLED) berhasil diproses. Silakan approve untuk lanjut ke tahap AK.');
        } catch (\Exception $e) {
            Log::error('Failed to lapor validasi', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            DB::rollBack();
            return back()->with('error', 'Gagal melapor validasi: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Handle borang revision notification to prodi
     */
    public function handleBorangRevision($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'borangValidators.borangValidation',
            'borangValidators.user'
        ])->findOrFail($id);

        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED) {
            return back()->with('error', 'Status tidak sesuai.');
        }

        DB::beginTransaction();
        try {
            // Get validator assignment with revision
            $validatorAssignment = $pengajuan->borangValidators()
                ->where('status_pekerjaan', 'revision_required')
                ->with(['user', 'borangValidation'])
                ->first();

            if (!$validatorAssignment || !$validatorAssignment->borangValidation) {
                return back()->with('error', 'Data validasi tidak ditemukan.');
            }

            // Send email to prodi
            Mail::to($pengajuan->pengaju->email)
                ->queue(new \App\Mail\BorangRevisionNotification(
                    $pengajuan,
                    $validatorAssignment->borangValidation
                ));

            // Log the action
            $this->logStatus(
                $pengajuan,
                $pengajuan->status,
                $pengajuan->status,
                'Notifikasi revisi dikirim ke prodi - ' . count($validatorAssignment->borangValidation->revision_points ?? []) . ' poin revisi'
            );

            DB::commit();

            return back()->with('success', 'Notifikasi revisi berhasil dikirim ke prodi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle borang revision', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal mengirim notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Reassign to validator after prodi revision
     */
    public function reassignAfterRevision($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram',
            'borangValidators.user'
        ])->findOrFail($id);

        // Must be borang_online_selesai (prodi sudah revisi)
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI) {
            return back()->with('error', 'Status tidak sesuai untuk reassign.');
        }

        // Must have previous validator with revision_required
        $previousValidator = $pengajuan->borangValidators()
            ->where('status_pekerjaan', 'revision_required')
            ->with('user')
            ->first();

        if (!$previousValidator) {
            return back()->with('error', 'Tidak ada validator sebelumnya yang meminta revisi.');
        }

        DB::beginTransaction();
        try {
            // Reset validator status to pending for re-review
            $previousValidator->update([
                'status_penawaran' => 'pending',
                'status_pekerjaan' => 'not_started',
                'responded_at' => null,
                'approved_at' => null,
            ]);

            // Update Permohonan akreditasi status
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            ]);

            $this->logStatus(
                $pengajuan,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                'LED dikembalikan ke validator ' . $previousValidator->user->name . ' untuk review ulang setelah revisi'
            );

            DB::commit();

            return back()->with('success', 'LED berhasil dikembalikan ke validator untuk review ulang.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reassign after revision', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal reassign: ' . $e->getMessage());
        }
    }

    /**
     * Log status
     */
    private function logStatus($pengajuan, $oldStatus, $newStatus, $keterangan = null)
    {
        $pengajuan->statusLog()->create([
            'status_from' => $oldStatus ?? 'new',
            'status_to' => $newStatus,
            'changed_by' => Auth::id(),
            'keterangan' => $keterangan,
            'changed_at' => now(),
        ]);
    }
}
