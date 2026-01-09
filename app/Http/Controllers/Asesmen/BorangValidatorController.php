<?php

namespace App\Http\Controllers\Asesmen;

use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Models\BorangValidation;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BorangValidatorController extends Controller
{
    /**
     * List semua borang yang di-assign (follow ValidasiController pattern)
     */
    public function index()
    {
        $user = Auth::user();

        // ✅ REUSE: Query pattern sama dengan AK validation
        $assignments = AsesmenUserRole::with([
            'pengajuan.studyProgram',
            'pengajuan.latestBorangImport',
            'asesmen',
        ])
            ->where('id_user', $user->id)
            ->whereHas('role_selected', fn($q) => $q->where('name', 'validator'))
            ->where('jenis_asesmen', 'dokumen') // ← Only borang validations
            ->orderByRaw("
            CASE status_pekerjaan
                WHEN 'not_started' THEN 1
                WHEN 'in_progress' THEN 2
                WHEN 'submitted' THEN 3
                WHEN 'revision_required' THEN 4
                WHEN 'approved' THEN 5
            END
        ")
            ->paginate(10);

        // Calculate stats (same pattern)
        $stats = [
            'pending' => AsesmenUserRole::where('id_user', $user->id)
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_pekerjaan', 'not_started')
                ->count(),
            'in_review' => AsesmenUserRole::where('id_user', $user->id)
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_pekerjaan', 'in_progress')
                ->count(),
            'revision' => AsesmenUserRole::where('id_user', $user->id)
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_pekerjaan', 'revision_required')
                ->count(),
            'approved' => AsesmenUserRole::where('id_user', $user->id)
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_pekerjaan', 'approved')
                ->count(),
        ];

        return view('validator.borang.index', compact('assignments', 'stats'));
    }

    /**
     * Show borang for validation (follow asesor() pattern)
     */
    public function show($idAssignment)
    {
        $user = Auth::user();

        $assignment = AsesmenUserRole::with([
            'pengajuan.studyProgram',
            'pengajuan.latestBorangImport',
            'pengajuan.borangData',
            'borangValidation', // NEW relation
        ])
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'dokumen')
            ->findOrFail($idAssignment);

        // ✅ Auto-update status (same pattern as AK)
        if ($assignment->status_pekerjaan === 'not_started') {
            $assignment->update([
                'status_pekerjaan' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        $pengajuan = $assignment->pengajuan;
        $validation = $assignment->borangValidation;

        // Get borang data
        $kriterias = $pengajuan->studyProgram->kriterias()
            ->with([
                'elemenStandar.datasetBorang',
                'elemenStandar.borangData' => function ($query) use ($pengajuan) {
                    $query->where('id_pengajuan', $pengajuan->id);
                }
            ])
            ->get();

        // Checklist template
        $checklistTemplate = [
            'completeness' => 'Kelengkapan data sesuai template',
            'accuracy' => 'Keakuratan data yang diisi',
            'narrative' => 'Kualitas narasi/deskripsi',
            'tables' => 'Kelengkapan tabel data',
            'formatting' => 'Format penulisan sesuai panduan',
        ];

        return view('validator.borang.show', compact(
            'assignment',
            'pengajuan',
            'validation',
            'kriterias',
            'checklistTemplate'
        ));
    }

    /**
     * Submit validation (follow validateElemen pattern)
     */
    public function submit(Request $request, $idAssignment)
    {
        $request->validate([
            'action' => 'required|in:approve,revision',
            'catatan_validator' => 'required|string|min:20|max:5000',
            'checklist' => 'nullable|array',
            'revision_points' => 'required_if:action,revision|array',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $assignment = AsesmenUserRole::where('id_user', $user->id)
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            $validation = $assignment->borangValidation;

            if ($request->action === 'approve') {
                // ✅ APPROVE (same pattern as AK)
                $validation->update([
                    'catatan_validator' => $request->catatan_validator,
                    'checklist_items' => $request->checklist,
                ]);

                $assignment->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $user->id,
                ]);

                $assignment->pengajuan->update([
                    'status' => 'borang_validated',
                    'tanggal_validasi_borang_selesai' => now(),
                ]);

                $message = 'Borang berhasil disetujui!';
            } else {
                // ❌ REQUEST REVISION (same pattern as AK)
                $validation->update([
                    'catatan_validator' => $request->catatan_validator,
                    'checklist_items' => $request->checklist,
                    'revision_points' => $request->revision_points,
                ]);

                $assignment->update([
                    'status_pekerjaan' => 'revision_required',
                ]);

                $assignment->pengajuan->update([
                    'status' => 'borang_revision_required',
                ]);

                $message = 'Permintaan revisi berhasil dikirim!';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit borang validation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal submit validasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
