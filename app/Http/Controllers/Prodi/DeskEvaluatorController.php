<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Role;
use App\Models\User;
use App\Models\Asesmen;
use App\Libraries\Fungsi;
use App\Models\BorangImport;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Mail\PembayaranVerified;
use App\Models\BorangValidation;
use App\Models\PengajuanDokumen;
use App\Mail\PengingatAkreditasi;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\BorangTemplateSentMail;
use App\Models\PembayaranAkreditasi;
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

        // Pengajuan yang di-assign ke DE ini
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
     * Show pengajuan detail
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

        return view('asesmen.de.show', compact(
            'pengajuan',
            'currentValidator',
            'canAssignValidator'
        ));
    }

    /**
     * Kirim pengingat akreditasi (Langkah 1)
     */
    public function kirimPengingat(Request $request)
    {
        $request->validate([
            'id_program_studi' => 'required|array',
            'id_program_studi.*' => 'exists:study_programs,id', // ✅ FIX
            'pesan_pengingat' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $prodis = StudyProgram::with(['users', 'degreeLevel'])->whereIn('id', $request->id_program_studi)->get(); // ✅ FIX

            foreach ($prodis as $prodi) {
                // Create pengajuan record
                $pengajuan = PengajuanAkreditasi::create([
                    'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan(),
                    'id_program_studi' => $prodi->id,
                    'id_de_assigned' => Auth::id(),
                    'tahun_akreditasi' => date('Y'),
                    'status' => 'pengingat_dikirim',
                    'tanggal_pengingat' => now(),
                ]);

                // Send email to prodi users
                foreach ($prodi->users as $user) {
                    Mail::to($user->email)->send(new PengingatAkreditasi($pengajuan, $request->pesan_pengingat));
                }

                $this->logStatus($pengajuan, null, 'pengingat_dikirim', 'Pengingat dikirim ke ' . $prodi->name);
            }

            DB::commit();

            return back()->with('success', 'Pengingat berhasil dikirim ke ' . count($prodis) . ' program studi.');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim form borang (Langkah 3)
     */
    public function kirimFormBorang(Request $request, $id)
    {
        $request->validate([
            'metode_kirim' => 'required|in:link,upload',
            'template_link' => 'required_if:metode_kirim,link|nullable|url|max:500',
            'borang_template' => 'required_if:metode_kirim,upload|nullable|file|mimes:docx,doc|max:10240',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'metode_kirim.required' => 'Pilih metode pengiriman template',
            'template_link.required_if' => 'Masukkan URL template LED',
            'template_link.url' => 'Format URL tidak valid',
            'borang_template.required_if' => 'Upload file template LED',
            'borang_template.mimes' => 'File harus berformat DOCX atau DOC',
            'borang_template.max' => 'Ukuran file maksimal 10 MB',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $degreeLevel = $pengajuan->studyProgram->degreeLevel->code;
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA) {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk kirim borang.');
        }

        DB::beginTransaction();
        try {
            $metode = $request->metode_kirim;
            $pathFile = null;
            $filename = null;
            $originalFilename = null;
            $fileSize = null;
            $mimeType = null;
            $templateLink = null;

            // ============================================
            // PROCESS BASED ON METHOD
            // ============================================
            if ($metode === 'upload') {
                // ✅ UPLOAD FILE
                $file = $request->file('borang_template');
                $filename = 'borang_template_' . time() . '.' . $file->getClientOriginalExtension();
                $pathFile = $file->storeAs('pengajuan/' . $pengajuan->id . '/template', $filename, 'public');

                $originalFilename = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
            } else {
                // ✅ LINK
                $templateLink = $request->template_link;

                // Create pseudo filename for record
                $filename = 'led_template_link_' . time() . '.url';
                $pathFile = 'links/' . $filename; // Virtual path
                $originalFilename = 'TEMPLATE_LEMBAR_EVALUASI_DIRI_' . $degreeLevel;
                $mimeType = 'text/uri-list';
                $fileSize = strlen($templateLink);
            }

            // ============================================
            // CREATE DOCUMENT RECORD
            // ============================================
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'borang_template',
                'nama_file' => $filename,
                'path_file' => $pathFile,
                'original_filename' => $originalFilename,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'uploaded_by' => Auth::id(),
                'keterangan' => $request->keterangan,
                'is_latest' => true,
                'template_link' => $templateLink, // ✅ Store link if applicable
            ]);

            // ============================================
            // UPDATE STATUS
            // ============================================
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                'tanggal_template_led_dikirim' => now(),
            ]);

            $keteranganLog = $metode === 'link'
                ? PengajuanAkreditasi::DAFTAR_TEMPLATE . " dikirim via link: {$templateLink}"
                : PengajuanAkreditasi::DAFTAR_TEMPLATE . " diupload: {$originalFilename}";

            $this->logStatus($pengajuan, $oldStatus, 'borang_dikirim', $keteranganLog);

            // ============================================
            // SEND EMAIL NOTIFICATION
            // ============================================
            try {
                Mail::to($pengajuan->pengaju->email)->send(
                    new BorangTemplateSentMail($pengajuan, $dokumen, $metode)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send ' . PengajuanAkreditasi::DAFTAR_TEMPLATE . ' email', [
                    'pengajuan_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the whole process
            }

            DB::commit();

            $successMessage = $metode === 'link'
                ? "Link " . PengajuanAkreditasi::DAFTAR_TEMPLATE . " berhasil dikirim ke prodi."
                : "File " . PengajuanAkreditasi::DAFTAR_TEMPLATE . " berhasil dikirim ke prodi.";

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('Kirim ' . PengajuanAkreditasi::DAFTAR_TEMPLATE . ' failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
            return back()->with('error', 'Pengajuan ini belum siap untuk assign validator.');
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

        $pengajuan = PengajuanAkreditasi::with(['latestBorangImport'])->findOrFail($id);

        // ============================================
        // VALIDATION CHECKS
        // ============================================
        if (!$pengajuan->canAssignValidator()) {
            return ResponseFormatter::error(null, 'Pengajuan tidak dapat di-assign validator.', 422);
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
            return ResponseFormatter::error(null, 'Validator ini sudah pernah di-assign untuk pengajuan ini.', 422);
        }

        DB::beginTransaction();
        try {
            // ============================================
            // AUTO-CREATE ASESMEN IF NOT EXISTS
            // ============================================
            if (!$pengajuan->asesmen) {
                $asesmen = Asesmen::create([
                    'id_pengajuan' => $pengajuan->id,
                    'id_study_program' => $pengajuan->id_program_studi,
                    'code' => 'ASM-' . $pengajuan->nomor_pengajuan,
                    'name' => 'Asesmen ' . $pengajuan->studyProgram->name . ' - ' . $pengajuan->tahun_akreditasi,
                    'description' => 'Asesmen untuk pengajuan ' . $pengajuan->nomor_pengajuan,
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
            // UPDATE PENGAJUAN STATUS
            // ============================================
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'borang_validation_pending',
                'tanggal_validasi_borang_assigned' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                'borang_validation_pending',
                "Validator {$validator->name} di-assign untuk review LED. " . ($request->catatan_de ?? '')
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

            return ResponseFormatter::success(['assignment_id' => $assignment->id, 'validator' => $validator->name], "Validator {$validator->name} berhasil di-assign. Email penawaran sedang diproses.");
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
            return ResponseFormatter::error(null, 'Tidak dapat reassign validator yang sudah diterima.', 422);
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
            return ResponseFormatter::error(null, 'Validator ini sudah pernah di-assign.', 422);
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
            $pengajuan->update([
                'status' => 'borang_validation_pending',
                'tanggal_validasi_borang_assigned' => now(),
            ]);

            // Log
            $this->logStatus(
                $pengajuan,
                $pengajuan->status,
                'borang_validation_pending',
                "Validator di-reassign ke {$newValidator->name}. " . ($request->catatan_de ?? '')
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
            return ResponseFormatter::success(null, "Validator berhasil di-reassign ke {$newValidator->name}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reassign validator', [
                'old_assignment_id' => $validationId,
                'new_validator_id' => $request->id_validator,
                'error' => $e->getMessage(),
            ]);

            return ResponseFormatter::error(null, 'Gagal reassign validator: ' . $e->getMessage(), 500);
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
            return ResponseFormatter::error(null, 'Hanya assignment pending yang dapat dibatalkan.', 422);
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
                    'status' => 'borang_online_selesai',
                ]);
            }

            DB::commit();

            return ResponseFormatter::success(null, 'Assignment validator berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel validator assignment', [
                'assignment_id' => $validationId,
                'error' => $e->getMessage(),
            ]);

            return ResponseFormatter::error(null, 'Gagal membatalkan assignment.', 500);
        }
    }

    /**
     * Verifikasi pembayaran
     */
    public function verifikasiPembayaran(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,ditolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $pembayaran = $pengajuan->pembayaran;

        if (!$pembayaran || $pembayaran->status_pembayaran !== 'dibayar') {
            return back()->with('error', 'Pembayaran belum diupload atau sudah diverifikasi.');
        }

        DB::beginTransaction();
        try {
            if ($request->status === 'verified') {
                $pembayaran->update([
                    'status_pembayaran' => 'verified',
                    'tanggal_verifikasi' => now(),
                    'verified_by' => Auth::id(),
                    'catatan_verifikasi' => $request->catatan_verifikasi,
                ]);

                // ✅ UPDATE: Log status untuk tracking
                $this->logStatus($pengajuan, $pengajuan->status, $pengajuan->status, 'Pembayaran diverifikasi, siap upload borang final');

                // ✅ ADD: Send email notification
                Mail::to($pengajuan->pengaju->email)->send(
                    new PembayaranVerified($pengajuan, true)
                );

                $message = 'Pembayaran berhasil diverifikasi. Prodi dapat upload borang final.';
            } else {
                $pembayaran->update([
                    'status_pembayaran' => 'ditolak',
                    'tanggal_verifikasi' => now(),
                    'verified_by' => Auth::id(),
                    'alasan_penolakan' => $request->catatan_verifikasi,
                ]);

                $pengajuan->update(['status' => 'menunggu_pembayaran']);

                $this->logStatus($pengajuan, 'pembayaran_diterima', 'menunggu_pembayaran', 'Pembayaran ditolak: ' . $request->catatan_verifikasi);

                // ✅ ADD: Send email notification
                Mail::to($pengajuan->pengaju->email)->send(
                    new PembayaranVerified($pengajuan, false)
                );

                $message = 'Pembayaran ditolak. Prodi perlu upload ulang bukti pembayaran.';
            }

            DB::commit();

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
            return back()->with('error', 'LED harus divalidasi oleh validator terlebih dahulu.');
        }

        // 2. Check pembayaran (harus verified)
        if (!$pengajuan->pembayaran || $pengajuan->pembayaran->status_pembayaran !== 'verified') {
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
                'status' => PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                'tanggal_penugasan_asesor_ak' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                'LED divalidasi dan pembayaran verified. Disetujui lanjut ke tahap AK - Menunggu penugasan asesor'
            );

            DB::commit();

            return back()->with('success', 'Pengajuan disetujui untuk lanjut ke tahap AK. Silakan assign asesor untuk Asesmen Kecukupan.');
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
     * Lapor hasil validasi LED (Step 7)
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

            // Update to Step 7: Pelaporan Validasi LED
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                'tanggal_pelaporan_validasi_borang' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                'Pelaporan validasi LED selesai - siap untuk lanjut ke tahap berikutnya'
            );

            DB::commit();

            return back()->with('success', 'Pelaporan hasil validasi LED berhasil. Silakan approve untuk lanjut ke tahap AK.');
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

            // Update pengajuan status
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            ]);

            $this->logStatus(
                $pengajuan,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                'LED dikembalikan ke validator ' . $previousValidator->user->name . ' untuk review ulang setelah revisi'
            );

            // Send email to validator
            Mail::to($previousValidator->user->email)
                ->queue(new \App\Mail\BorangRevalidationRequest($pengajuan));

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
