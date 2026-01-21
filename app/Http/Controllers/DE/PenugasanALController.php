<?php

namespace App\Http\Controllers\DE;

use Illuminate\Http\Request;
use App\Models\PengajuanAkreditasi;
use App\Models\Asesmen;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PenugasanALController extends Controller
{
    /**
     * Dashboard penugasan AL
     */
    public function index(Request $request)
    {
        // Build query - pengajuan yang siap untuk AL atau sudah dalam proses AL
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenLapangan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'asesor'))
                    ->with(['user', 'role_selected']);
            }
        ])
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_AK_DILAPORKAN,           // AK selesai, siap AL
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,   // Asesor AL sudah ditugaskan
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,       // AL sedang berlangsung
                PengajuanAkreditasi::STATUS_AL_SELESAI,           // AL selesai
                PengajuanAkreditasi::STATUS_AL_DILAPORKAN,        // AL dilaporkan
            ]);

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get universities for filter
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();

        // Check if AJAX
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.penugasan-al.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.penugasan-al.index', compact(
            'pengajuans',
            'stats',
            'universities'
        ));
    }

    /**
     * Detail penugasan AL
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenLapangan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->with(['user', 'role_selected']);
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get available asesor (yang belum assigned)
        $assignedAsesorIds = $pengajuan->asesmen?->asesmenUserRoles
            ->where('jenis_asesmen', 'al')
            ->pluck('id_user')
            ->toArray() ?? [];

        $availableAsesors = \App\Models\User::notAdmin()
            ->whereNotIn('id', $assignedAsesorIds)
            ->orderBy('name')
            ->get();

        return view('de.penugasan-al.show', compact(
            'pengajuan',
            'availableAsesors'
        ));
    }

    /**
     * Mark pengajuan as ready for AL and set schedule
     */
    public function markReadyForAL(Request $request, $id)
    {
        try {
            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'lokasi_visitasi' => 'required|string|max:500',
                'catatan' => 'nullable|string|max:500',
            ], [
                'tanggal_mulai.required' => 'Tanggal mulai harus diisi',
                'tanggal_selesai.required' => 'Tanggal selesai harus diisi',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai',
                'lokasi_visitasi.required' => 'Lokasi visitasi harus diisi',
            ]);

            DB::beginTransaction();

            $pengajuan = PengajuanAkreditasi::with('studyProgram')->findOrFail($id);

            // Validate status
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_AK_DILAPORKAN) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan harus dalam status AK Selesai untuk dapat ditetapkan siap AL'
                ], 400);
            }

            // Create or get Asesmen
            $asesmen = $pengajuan->asesmen;
            if (!$asesmen) {
                $asesmen = Asesmen::create([
                    'id_study_program' => $pengajuan->id_program_studi,
                    'id_pengajuan' => $pengajuan->id,
                    'description' => $request->catatan ?? 'Asesmen Lapangan untuk ' . $pengajuan->studyProgram->name,
                    'status' => 'active',
                ]);
            }

            // Create or update AsesmenLapangan
            $asesmenLapangan = AsesmenLapangan::updateOrCreate(
                ['id_asesmen' => $asesmen->id],
                [
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'lokasi_visitasi' => $request->lokasi_visitasi,
                    'catatan' => $request->catatan,
                    'status' => 'scheduled',
                ]
            );

            // Update pengajuan status to PENGAJUAN_COMPLETED (ready for AL assignment)
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
            ]);

            // Add status log
            $tanggalMulai = \Carbon\Carbon::parse($request->tanggal_mulai)->format('d M Y');
            $tanggalSelesai = \Carbon\Carbon::parse($request->tanggal_selesai)->format('d M Y');

            $pengajuan->statusLog()->create([
                'previous_status' => PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                'new_status' => PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => "Ditetapkan siap untuk AL. Periode visitasi: {$tanggalMulai} s/d {$tanggalSelesai}. Lokasi: {$request->lokasi_visitasi}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil ditetapkan siap untuk AL'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign asesor to AL
     */
    public function assignAsesor(Request $request, $id)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'urutan_asesor' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

            // Validate pengajuan has asesmen
            if (!$pengajuan->asesmen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan belum memiliki asesmen. Tetapkan siap untuk AL terlebih dahulu.'
                ], 400);
            }

            // Check if user already assigned
            $exists = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('id_user', $request->user_id)
                ->where('jenis_asesmen', 'al')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesor sudah ditugaskan untuk AL ini'
                ], 400);
            }

            // Get asesor role
            $asesorRole = \Spatie\Permission\Models\Role::where('name', 'asesor')->first();

            // Create assignment
            AsesmenUserRole::create([
                'id_asesmen' => $pengajuan->asesmen->id,
                'id_user' => $request->user_id,
                'id_role' => $asesorRole->id,
                'jenis_asesmen' => 'al',
                'urutan_asesor' => $request->urutan_asesor,
                'status_penawaran' => 'pending',
                'status_pekerjaan' => 'not_started',
                'tanggal_penugasan' => now(),
            ]);

            // Update pengajuan status to ASESOR_AL_ASSIGNED if first asesor
            $totalAsesor = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('jenis_asesmen', 'al')
                ->count();

            if ($totalAsesor == 1) {
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                ]);

                $pengajuan->statusLog()->create([
                    'previous_status' => $pengajuan->status,
                    'new_status' => PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => 'Asesor AL pertama telah ditugaskan',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asesor berhasil ditugaskan untuk AL'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove asesor from AL
     */
    public function removeAsesor(Request $request, $id)
    {
        try {
            $request->validate([
                'assignment_id' => 'required|exists:asesmen_user_roles,id',
            ]);

            DB::beginTransaction();

            $assignment = AsesmenUserRole::findOrFail($request->assignment_id);
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validate assignment belongs to this pengajuan's asesmen
            if ($assignment->id_asesmen !== $pengajuan->asesmen->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment tidak valid'
                ], 400);
            }

            // Delete assignment
            $userName = $assignment->user->name;
            $assignment->delete();

            // Check if any asesor left
            $remainingAsesor = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('jenis_asesmen', 'al')
                ->count();

            // If no asesor left, revert status
            if ($remainingAsesor == 0) {
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                ]);

                $pengajuan->statusLog()->create([
                    'previous_status' => PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                    'new_status' => PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => "Asesor {$userName} dibatalkan. Tidak ada asesor AL tersisa.",
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asesor berhasil dihapus dari penugasan AL'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update AL schedule
     */
    public function updateSchedule(Request $request, $id)
    {
        try {
            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'lokasi_visitasi' => 'required|string|max:500',
                'catatan' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $pengajuan = PengajuanAkreditasi::with('asesmen.asesmenLapangan')->findOrFail($id);

            if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenLapangan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesmen Lapangan tidak ditemukan'
                ], 404);
            }

            // Update schedule
            $pengajuan->asesmen->asesmenLapangan->update([
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'lokasi_visitasi' => $request->lokasi_visitasi,
                'catatan' => $request->catatan,
            ]);

            // Add status log
            $tanggalMulai = \Carbon\Carbon::parse($request->tanggal_mulai)->format('d M Y');
            $tanggalSelesai = \Carbon\Carbon::parse($request->tanggal_selesai)->format('d M Y');

            $pengajuan->statusLog()->create([
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => "Jadwal AL diperbarui. Periode: {$tanggalMulai} s/d {$tanggalSelesai}. Lokasi: {$request->lokasi_visitasi}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal AL berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate statistics for dashboard
     */
    private function calculateStatistics()
    {
        $base = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_SELESAI,
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
        ]);

        $total = (clone $base)->count();

        $siapAL = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            ->count();

        $sudahDitugaskan = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED)
            ->count();

        $sedangBerlangsung = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)
            ->count();

        $selesai = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AL_SELESAI)
            ->count();

        $dilaporkan = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
            ->count();

        return [
            'total' => $total,
            'siap_al' => $siapAL,
            'sudah_ditugaskan' => $sudahDitugaskan,
            'sedang_berlangsung' => $sedangBerlangsung,
            'selesai' => $selesai,
            'dilaporkan' => $dilaporkan,
        ];
    }
}
