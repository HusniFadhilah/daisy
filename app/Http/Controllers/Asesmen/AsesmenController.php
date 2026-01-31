<?php

namespace App\Http\Controllers\Asesmen;

use App\Models\Role;
use App\Models\User;
use App\Models\Asesmen;
use App\Libraries\Fungsi;
use Illuminate\Http\Request;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use App\Models\AsesmenKecukupan;
use App\Models\BorangValidation;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendPenawaranAsesmenEmail;
use App\Notifications\ValidatorBorangAssignedNotification;

class AsesmenController extends Controller
{
    /**
     * Display a listing of asesmen (Admin view)
     */
    public function index()
    {
        $asesmens = Asesmen::with(['userRoles.user', 'userRoles.role'])
            ->withCount('userRoles')
            ->latest()
            ->paginate(15);

        return view('asesmen.index', compact('asesmens'));
    }

    /**
     * Show the form for creating a new asesmen
     */
    public function create(Request $request)
    {
        // ✅ CHECK: Apakah ada pengajuan_id dari URL?
        $pengajuanId = $request->get('pengajuan_id');
        $pengajuan = null;
        $studyProgram = null;

        if ($pengajuanId) {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.degreeLevel',
                'studyProgram.university'
            ])->findOrFail($pengajuanId);

            // Check if Permohonan akreditasi telah punya asesmen
            if ($pengajuan->asesmen) {
                return redirect()
                    ->route('asesmen.show', $pengajuan->asesmen->id)
                    ->with('info', 'Asesmen untuk permohonan akreditasi ini telah dibuat.');
            }

            // Check status
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED) {
                return redirect()
                    ->route('de.pengajuan.show', $pengajuan->id)
                    ->with('error', 'Permohonan akreditasi belum disetujui untuk lanjut ke AK.');
            }

            $studyProgram = $pengajuan->studyProgram;
        }

        return view('asesmen.form', compact('pengajuan', 'studyProgram'));
    }

    /**
     * Store a newly created asesmen
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pengajuan' => 'nullable|exists:pengajuan_akreditasi,id',
            'id_study_program' => 'nullable|exists:study_programs,id',
            'name' => 'required|string|max:255',
            'description' => 'string|max:1000',
            'kode_panel' => 'nullable|string|max:50',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();
        try {
            // Create asesmen
            $asesmen = Asesmen::create([
                'id_pengajuan' => $request->id_pengajuan,
                'id_study_program' => $request->id_study_program,
                'code' => 'Asesmen-' . Fungsi::uniqueCode(5),
                'name' => $request->name,
                'description' => $request->description,
                'kode_panel' => $request->kode_panel,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ]);

            // ✅ ADD: Log ke PengajuanStatusLog jika ada pengajuan
            if ($request->id_pengajuan) {
                $pengajuan = PengajuanAkreditasi::find($request->id_pengajuan);
                $pengajuan->statusLog()->create([
                    'status_from' => 'ak_in_progress',
                    'status_to' => 'ak_in_progress',
                    'changed_by' => Auth::id(),
                    'keterangan' => 'Asesmen dibuat: ' . $asesmen->name,
                    'changed_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('asesmen.show', $asesmen->id)
                ->with('success', 'Asesmen berhasil dibuat. Silakan assign asesor dan validator.');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified asesmen with assigned users
     */
    public function show($id)
    {
        $asesmen = Asesmen::with(['userRoles.user', 'userRoles.role'])
            ->findOrFail($id);

        $assignedUserIds = $asesmen->userRoles->pluck('id_user');
        $availableUsers = User::
            // whereNotIn('id', $assignedUserIds)
            where('role', '!=', 'admin')
            ->orderBy('name')
            ->get();

        $roles = Role::whereIn('name', ['asesor', 'validator'])->orderBy('name')->get();
        $totalElemens = DB::table('elemen_standar')->count();

        // Hitung progress bulk
        // === PROGRESS BULK (4 QUERY TOTAL) ===
        $asesorAK = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $asesmen->id)
            ->whereNotNull('skor')
            ->groupBy('id_asesor')
            ->select('id_asesor', DB::raw('COUNT(*) c'))
            ->pluck('c', 'id_asesor');

        $validatorAK = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $asesmen->id)
            ->whereNotNull('validated_at')
            ->groupBy('validated_by')
            ->select('validated_by', DB::raw('COUNT(DISTINCT id_elemen) c'))
            ->pluck('c', 'validated_by');

        $asesorAL = DB::table('penilaian_elemen_al')
            ->where('id_asesmen', $asesmen->id)
            ->whereNotNull('skor')
            ->groupBy('id_asesor')
            ->select('id_asesor', DB::raw('COUNT(*) c'))
            ->pluck('c', 'id_asesor');

        // === MAP USER STATS (NO QUERY) ===
        $userStats = $asesmen->userRoles->mapWithKeys(function ($ur) use (
            $asesorAK,
            $validatorAK,
            $asesorAL,
            $totalElemens
        ) {
            $done = 0;

            if ($ur->id_role == 3) { // asesor
                $done = $ur->jenis_asesmen === 'ak'
                    ? ($asesorAK[$ur->id_user] ?? 0)
                    : ($asesorAL[$ur->id_user] ?? 0);
            }

            if ($ur->id_role == 4 && $ur->jenis_asesmen === 'ak') { // validator
                $done = $validatorAK[$ur->id_user] ?? 0;
            }

            return [$ur->id_user => [
                'total' => $totalElemens,
                'completed' => $done,
                'percentage' => $totalElemens ? round($done / $totalElemens * 100, 1) : 0,
            ]];
        })->toArray();

        // === STATUS STAT ===
        $asesmenStats = $asesmen->userRoles()
            ->selectRaw('jenis_asesmen, status_pekerjaan, COUNT(*) total')
            ->groupBy('jenis_asesmen', 'status_pekerjaan')
            ->get()
            ->groupBy('jenis_asesmen');

        // Map ke userRole
        $userStats = $asesmen->userRoles->mapWithKeys(function ($ur) use (
            $asesorAK,
            $validatorAK,
            $asesorAL,
            $totalElemens
        ) {
            $completed = 0;

            // ===== ASESOR =====
            if ($ur->id_role == 3) {
                if ($ur->jenis_asesmen === 'ak') {
                    $completed = $asesorAK[$ur->id_user] ?? 0;
                }

                if ($ur->jenis_asesmen === 'al') {
                    $completed = $asesorAL[$ur->id_user] ?? 0;
                }
            }

            // ===== VALIDATOR (HANYA AK) =====
            if ($ur->id_role == 4 && $ur->jenis_asesmen === 'ak') {
                $completed = $validatorAK[$ur->id_user] ?? 0;
            }

            $percentage = $totalElemens
                ? round(($completed / $totalElemens) * 100, 1)
                : 0;

            return [
                $ur->id_user => [
                    'total' => $totalElemens,
                    'completed' => $completed,
                    'remaining' => $totalElemens - $completed,
                    'percentage' => $percentage,
                ]
            ];
        })->toArray();

        $statusPekerjaan = AsesmenUserRole::STATUS_PEKERJAAN;

        return view('asesmen.show', compact(
            'asesmen',
            'availableUsers',
            'roles',
            'userStats',
            'asesmenStats',
            'statusPekerjaan'
        ));
    }

    /**
     * Show the form for editing asesmen
     */
    public function edit($id)
    {
        $asesmen = Asesmen::findOrFail($id);
        return view('asesmen.form', compact('asesmen'));
    }

    /**
     * Update the specified asesmen
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'string|max:1000',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $asesmen = Asesmen::findOrFail($id);
            $asesmen->update($request->all());

            return redirect()
                ->route('asesmen.show', $asesmen->id)
                ->with('success', 'Asesmen berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal update asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Assign user (asesor) to asesmen
     */
    public function assignUser(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_role' => 'required|exists:roles,id',
            'jenis_asesmen' => 'required|in:ak,al,dokumen',
        ]);

        try {
            $asesmen = Asesmen::findOrFail($id);
            $role = Role::findOrFail($request->id_role);

            // Check if user already assigned for this jenis_asesmen
            $exists = AsesmenUserRole::where('id_asesmen', $id)
                ->where('id_user', $request->id_user)
                ->where('id_role', $request->id_role)
                ->where('jenis_asesmen', $request->jenis_asesmen)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User telah ditugaskan dengan role ini untuk ' . strtoupper($request->jenis_asesmen)
                ], 422);
            }

            // Get or create AsesmenKecukupan/AsesmenLapangan
            if ($request->jenis_asesmen === 'ak') {
                $asesmenKecukupan = AsesmenKecukupan::firstOrCreate(
                    ['id_asesmen' => $id],
                    [
                        'code' => 'AK-' . $asesmen->code,
                        'status' => 'active',
                    ]
                );
                $linkId = $asesmenKecukupan->id;
                $linkField = 'id_asesmen_kecukupan';
            } else {
                $asesmenLapangan = AsesmenLapangan::firstOrCreate(
                    ['id_asesmen' => $id],
                    [
                        'code' => 'AL-' . $asesmen->code,
                        'status' => 'active',
                    ]
                );
                $linkId = $asesmenLapangan->id;
                $linkField = 'id_asesmen_lapangan';
            }

            // Determine urutan_asesor if role is asesor
            $urutanAsesor = null;
            if ($role->name === 'asesor') {
                $existingUrutans = AsesmenUserRole::where('id_asesmen', $id)
                    ->where('jenis_asesmen', $request->jenis_asesmen)
                    ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                    ->pluck('urutan_asesor')
                    ->toArray();

                sort($existingUrutans);

                // Find first missing number (gap)
                $urutanAsesor = 1;
                foreach ($existingUrutans as $urutan) {
                    if ($urutan !== $urutanAsesor) {
                        break; // Found gap, use this urutan
                    }
                    $urutanAsesor++;
                }
                // $maxUrutan = AsesmenUserRole::where('id_asesmen', $id)
                //     ->where('jenis_asesmen', $request->jenis_asesmen)
                //     ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                //     ->max('urutan_asesor');

                // $urutanAsesor = ($maxUrutan ?? 0) + 1;
            }

            // Create assignment
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $id,
                'id_user' => $request->id_user,
                'id_role' => $request->id_role,
                'jenis_asesmen' => $request->jenis_asesmen,
                $linkField => $linkId,
                'urutan_asesor' => $urutanAsesor,
                'status_penawaran' => 'pending',
            ]);

            $user = User::find($request->id_user);

            // Check requirements after assignment
            $requirementsMet = $request->jenis_asesmen === 'ak'
                ? $asesmenKecukupan->hasMinimumRequirements()
                : $asesmenLapangan->hasMinimumRequirements();

            $missingRequirements = $request->jenis_asesmen === 'ak'
                ? $asesmenKecukupan->getMissingRequirements()
                : $asesmenLapangan->getMissingRequirements();

            $pengajuan = $asesmen->pengajuan;
            if ($pengajuan) {
                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('ak', 'status_asesor_assigned');
                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                    ],
                    [
                        'changed_by'  => Auth::id(),
                        'keterangan'  => 'Penugasan asesor untuk asesmen kecukupan telah dilakukan',
                        'changed_at'  => now(),
                    ]
                );
            }

            try {
                SendPenawaranAsesmenEmail::dispatch($assignment);
            } catch (\Exception $e) {
                // Email gagal di-dispatch, tapi penugasan tetap berhasil
                Log::error("Gagal dispatch email job penawaran", [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil ditugaskan sebagai {$role->alias} untuk " .
                    strtoupper($request->jenis_asesmen) .
                    ($urutanAsesor ? " (Asesor {$urutanAsesor})" : "") .
                    ". Email penawaran telah dikirim.",
                'data' => [
                    'assignment' => $assignment,
                    'user' => $user,
                    'role' => $role,
                    'urutan_asesor' => $urutanAsesor,
                    'requirements_met' => $requirementsMet,
                    'missing_requirements' => $missingRequirements,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually reorder asesor (Admin only)
     */
    public function reorderAsesor(Request $request, $id)
    {
        $request->validate([
            'jenis_asesmen' => 'required|in:ak,al',
            'order_by' => 'in:created_at,name', // Optional: reorder by time or name
        ]);

        try {
            $jenisAsesmen = $request->jenis_asesmen;
            $orderBy = $request->order_by ?? 'created_at';

            // Get all asesor assignments
            $query = AsesmenUserRole::where('id_asesmen', $id)
                ->where('jenis_asesmen', $jenisAsesmen)
                ->whereHas('role', fn($q) => $q->where('name', 'asesor'));

            if ($orderBy === 'name') {
                $query->join('users', 'asesmen_user_roles.id_user', '=', 'users.id')
                    ->orderBy('users.name');
            } else {
                $query->orderBy('created_at');
            }

            $asesors = $query->get();

            // Reorder
            $newUrutan = 1;
            foreach ($asesors as $asesor) {
                $asesor->update(['urutan_asesor' => $newUrutan]);
                $newUrutan++;
            }

            return response()->json([
                'success' => true,
                'message' => "Urutan asesor berhasil diatur ulang ({$asesors->count()} asesor)",
                'data' => [
                    'total_reordered' => $asesors->count(),
                    'order_by' => $orderBy
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal reorder asesor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get penugasan requirements status
     */
    public function getRequirementsStatus($id, $jenisAsesmen)
    {
        try {
            if ($jenisAsesmen === 'ak') {
                $asesmenKecukupan = AsesmenKecukupan::where('id_asesmen', $id)->first();

                if (!$asesmenKecukupan) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'requirements_met' => false,
                            'missing' => ['2 asesor', '1 validator'],
                            'current' => ['asesor' => 0, 'validator' => 0],
                        ]
                    ]);
                }

                $asesorCount = $asesmenKecukupan->asesors->count();
                $validatorCount = $asesmenKecukupan->validators->count();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'requirements_met' => $asesmenKecukupan->hasMinimumRequirementsWithCounts($asesorCount, $validatorCount),
                        'missing' => $asesmenKecukupan->getMissingRequirementsWithCounts($asesorCount, $validatorCount),
                        'current' => [
                            'asesor' => $asesorCount,
                            'validator' => $validatorCount,
                        ],
                    ]
                ]);
            } else {
                // Similar for AL
                $asesmenLapangan = AsesmenLapangan::where('id_asesmen', $id)->first();

                if (!$asesmenLapangan) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'requirements_met' => false,
                            'missing' => ['2 asesor'],
                            'current' => ['asesor' => 0],
                        ]
                    ]);
                }

                $asesorCount = $asesmenLapangan->asesors()->count();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'requirements_met' => $asesmenLapangan->hasMinimumRequirementsWithCounts($asesorCount),
                        'missing' => $asesmenLapangan->getMissingRequirementsWithCounts($asesorCount),
                        'current' => [
                            'asesor' => $asesorCount,
                        ],
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignments for specific jenis asesmen
     */
    public function getAssignments($id, $jenisAsesmen)
    {
        try {
            $asesmen = Asesmen::findOrFail($id);

            // Get assignments based on jenis asesmen
            $assignments = AsesmenUserRole::where('id_asesmen', $id)
                ->where('jenis_asesmen', $jenisAsesmen)
                ->whereIn('status_penawaran', ['pending', 'accepted'])
                ->with(['user', 'role'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by role
            $groupedAssignments = $assignments->groupBy('role.name')->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'user' => [
                            'id' => $item->user->id,
                            'name' => $item->user->name,
                            'email' => $item->user->email,
                        ],
                        'role' => [
                            'id' => $item->role->id,
                            'name' => $item->role->name,
                            'alias' => $item->role->alias,
                        ],
                        'urutan_asesor' => $item->urutan_asesor,
                        'status_penawaran' => $item->status_penawaran,
                        'status_pekerjaan' => $item->status_pekerjaan,
                        'created_at' => $item->created_at->format('d M Y H:i'),
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'data' => $groupedAssignments
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rejected assignments (for replacement guidance)
     */
    public function getRejectedAssignments($id, $jenisAsesmen)
    {
        try {
            $rejected = AsesmenUserRole::where('id_asesmen', $id)
                ->where('jenis_asesmen', $jenisAsesmen)
                ->where('status_penawaran', 'rejected')
                ->with(['user', 'role'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rejected
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user role in asesmen
     */
    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'assignment_id' => 'required|exists:asesmen_user_roles,id',
            'id_role' => 'required|exists:roles,id',
        ]);

        try {
            $assignment = AsesmenUserRole::findOrFail($request->assignment_id);

            // Verify penugasan belongs to this asesmen
            if ($assignment->id_asesmen != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan tidak valid'
                ], 422);
            }

            $assignment->update(['id_role' => $request->id_role]);

            $role = Role::find($request->id_role);

            return response()->json([
                'success' => true,
                'message' => "Role berhasil diupdate menjadi {$role->name}",
                'data' => [
                    'assignment' => $assignment,
                    'role' => $role,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove user from asesmen
     */
    public function removeUser($id, $userId)
    {
        try {
            // Get the penugasan to check jenis_asesmen
            $assignment = AsesmenUserRole::where('id_asesmen', $id)
                ->where('id_user', $userId)
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan tidak ditemukan'
                ], 404);
            }

            // Check if user has any penilaian
            $hasPenilaianElemenAk = DB::table('penilaian_elemen_ak')->where('id_asesmen', $id)
                ->where(function ($query) use ($userId) {
                    $query->where('id_asesor', $userId)->orWhere('validated_by', $userId);
                })->exists();
            $hasPenilaianElemenAl = DB::table('penilaian_elemen_al')->where('id_asesmen', $id)->where('id_asesor', $userId)->exists();

            if ($hasPenilaianElemenAk || $hasPenilaianElemenAl) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak bisa dihapus karena telah melakukan penilaian. Hapus penilaian terlebih dahulu.'
                ], 422);
            }

            // ✅ NEW: Check if removing this user will violate minimum requirements
            $jenisAsesmen = $assignment->jenis_asesmen;
            $roleId = $assignment->id_role;
            $role = Role::find($roleId);

            if ($assignment->status_penawaran === 'accepted') {
                // Count current assignments of this role for this jenis_asesmen
                $currentCount = AsesmenUserRole::where('id_asesmen', $id)
                    ->where('jenis_asesmen', $jenisAsesmen)
                    ->where('id_role', $roleId)
                    ->whereIn('status_penawaran', ['accepted', 'pending'])
                    ->count();

                // Check minimum requirements
                $minRequired = 0;
                if ($role->name === 'asesor') {
                    $minRequired = 2;
                } elseif ($role->name === 'validator') {
                    $minRequired = 1;
                }

                if ($currentCount <= $minRequired) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tidak bisa menghapus {$role->alias} karena akan melanggar persyaratan minimum ({$minRequired} {$role->alias} untuk " . strtoupper($jenisAsesmen) . "). Assign pengganti terlebih dahulu.",
                        'validation_error' => true
                    ], 422);
                }
            }

            // Delete assignment
            $userName = $assignment->user->name;
            $isAsesor = $role->name === 'asesor';
            $assignment->delete();
            if ($isAsesor) {
                $this->reorganizeAsesorOrder($id, $jenisAsesmen);
            }

            return response()->json([
                'success' => true,
                'message' => "{$userName} berhasil dihapus dari asesmen. " .
                    ($isAsesor ? "Urutan asesor telah diatur ulang." : ""),
                'jenis_asesmen' => $jenisAsesmen,
                'reorganized' => $isAsesor
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reassign user (replace rejected assignment)
     */
    public function reassignUser(Request $request, $id)
    {
        $request->validate([
            'assignment_id' => 'required|exists:asesmen_user_roles,id',
            'new_user_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $oldAssignment = AsesmenUserRole::findOrFail($request->assignment_id);

            // Verify old penugasan is rejected
            if ($oldAssignment->status_penawaran !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya penugasan yang ditolak yang bisa ditugaskan ulang'
                ], 422);
            }

            // Check if new user already assigned
            $exists = AsesmenUserRole::where('id_asesmen', $id)
                ->where('id_user', $request->new_user_id)
                ->where('id_role', $oldAssignment->id_role)
                ->where('jenis_asesmen', $oldAssignment->jenis_asesmen)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User telah ditugaskan dengan role ini'
                ], 422);
            }

            // Delete old assignment
            $jenisAsesmen = $oldAssignment->jenis_asesmen;
            $roleId = $oldAssignment->id_role;
            $linkField = $jenisAsesmen === 'ak' ? 'id_asesmen_kecukupan' : 'id_asesmen_lapangan';
            $linkId = $oldAssignment->$linkField;
            $urutanAsesor = $oldAssignment->urutan_asesor;

            $oldAssignment->delete();

            // Create new assignment
            $newAssignment = AsesmenUserRole::create([
                'id_asesmen' => $id,
                'id_user' => $request->new_user_id,
                'id_role' => $roleId,
                'jenis_asesmen' => $jenisAsesmen,
                $linkField => $linkId,
                'urutan_asesor' => $urutanAsesor,
                'status_penawaran' => 'pending',
            ]);

            $pengajuan = $newAssignment->asesmen->pengajuan;
            if ($pengajuan) {
                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('ak', 'status_asesor_assigned');
                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                    ],
                    [
                        'changed_by'  => Auth::id(),
                        'keterangan'  => 'Penugasan asesor untuk asesmen kecukupan telah dilakukan',
                        'changed_at'  => now(),
                    ]
                );
            }

            DB::commit();

            $user = User::find($request->new_user_id);
            $role = Role::find($roleId);

            return response()->json([
                'success' => true,
                'message' => "Berhasil reassign ke {$user->name} sebagai {$role->alias}",
                'data' => [
                    'assignment' => $newAssignment,
                    'user' => $user,
                    'role' => $role,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal reassign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorganize urutan asesor after deletion
     * Menghilangkan gap dan mengurutkan ulang dari 1, 2, 3, ...
     */
    private function reorganizeAsesorOrder($idAsesmen, $jenisAsesmen)
    {
        // Get all asesor assignments for this asesmen & jenis, ordered by urutan_asesor
        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->orderBy('urutan_asesor')
            ->get();

        // Reorganize urutan from 1, 2, 3, ...
        $newUrutan = 1;
        foreach ($asesors as $asesor) {
            if ($asesor->urutan_asesor !== $newUrutan) {
                $asesor->update(['urutan_asesor' => $newUrutan]);

                Log::info("Reorganized asesor urutan", [
                    'id_asesmen' => $idAsesmen,
                    'jenis_asesmen' => $jenisAsesmen,
                    'user_id' => $asesor->id_user,
                    'old_urutan' => $asesor->urutan_asesor,
                    'new_urutan' => $newUrutan
                ]);
            }
            $newUrutan++;
        }

        return $asesors->count();
    }

    /**
     * Reorder asesor based on created_at (oldest = Asesor 1)
     */
    private function reorderAsesorByTime($idAsesmen, $jenisAsesmen)
    {
        // Get all asesor assignments ordered by created_at
        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->orderBy('created_at')
            ->get();

        // Assign new urutan based on order
        $newUrutan = 1;
        foreach ($asesors as $asesor) {
            $asesor->update(['urutan_asesor' => $newUrutan]);
            $newUrutan++;
        }

        return $asesors->count();
    }

    /**
     * Delete asesmen (with confirmation)
     */
    public function destroy($id)
    {
        try {
            $asesmen = Asesmen::findOrFail($id);

            // Check if has penilaian
            $hasPenilaian = $asesmen->penilaianElemenAk()->exists() || $asesmen->penilaianElemenAl()->exists();

            if ($hasPenilaian) {
                return redirect()
                    ->back()
                    ->with('error', 'Asesmen tidak bisa dihapus karena telah ada penilaian.');
            }

            $asesmen->delete();

            return redirect()
                ->route('asesmen.index')
                ->with('success', 'Asesmen berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()
                ->back()
                ->with('error', 'Gagal hapus asesmen: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign users to asesmen
     */
    public function bulkAssign(Request $request, $id)
    {
        $request->validate([
            'id_users' => 'required|array',
            'id_users.*' => 'exists:users,id',
            'id_role' => 'required|exists:roles,id',
        ]);

        try {
            $assignedCount = 0;
            $skippedCount = 0;

            DB::beginTransaction();

            foreach ($request->id_users as $userId) {
                // Check if already assigned
                $exists = AsesmenUserRole::where('id_asesmen', $id)
                    ->where('id_user', $userId)
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                AsesmenUserRole::create([
                    'id_asesmen' => $id,
                    'id_user' => $userId,
                    'id_role' => $request->id_role,
                ]);

                $assignedCount++;
            }

            DB::commit();

            $message = "Berhasil menugaskan {$assignedCount} user.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} user di-skip (telah ditugaskan).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'assigned' => $assignedCount,
                    'skipped' => $skippedCount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menugaskan secara serentak: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign validator for borang (reuse existing assignUser method)
     */
    public function assignValidatorBorang(Request $request, $idPengajuan)
    {
        $request->validate([
            'id_validator' => 'required|exists:users,id',
            'catatan_de' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $pengajuan = PengajuanAkreditasi::findOrFail($idPengajuan);

            // Check if already has validator
            $existingValidator = AsesmenUserRole::where('id_pengajuan', $idPengajuan)
                ->where('jenis_asesmen', 'dokumen')
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->whereIn('status_penawaran', ['pending', 'accepted'])
                ->first();

            if ($existingValidator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permohonan akreditasi telah memiliki validator dokumen.',
                ], 422);
            }

            // Get or create Asesmen for this pengajuan
            $asesmen = $pengajuan->asesmen;
            if (!$asesmen) {
                $asesmen = Asesmen::create([
                    'id_pengajuan' => $idPengajuan,
                    'id_study_program' => $pengajuan->id_program_studi,
                    'code' => $pengajuan->nomor_pengajuan,
                    'name' => $pengajuan->judul,
                    'description' => $pengajuan->judul,
                ]);
            }

            $validatorRole = Role::where('name', 'validator')->firstOrFail();

            // ✅ CREATE ASSIGNMENT (reuse table!)
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $asesmen->id,
                'id_pengajuan' => $idPengajuan, // NEW: direct link
                'id_user' => $request->id_validator,
                'id_role' => $validatorRole->id,
                'jenis_asesmen' => 'borang', // ← NEW enum value
                'status_penawaran' => 'pending',
            ]);

            // Create validation record
            $validation = BorangValidation::create([
                'id_assignment' => $assignment->id,
                'id_pengajuan' => $idPengajuan,
                'catatan_de' => $request->catatan_de,
            ]);

            // Send notification
            $validator = User::find($request->id_validator);
            $validator->notify(new ValidatorBorangAssignedNotification($pengajuan, $assignment));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Validator {$validator->name} berhasil ditugaskan untuk validasi LED.",
                'data' => [
                    'assignment' => $assignment,
                    'validation' => $validation,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assign validator borang failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal assign validator: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dashboard - Overview semua asesmen
     */
    public function dashboard()
    {
        $stats = [
            'total_asesmens' => Asesmen::count(),
            'active_asesmens' => Asesmen::whereHas('userRoles')->count(),
            'total_asesor' => User::where('role', 'asesor')->count(),
            'completed_asesmens' => 0, // Will calculate based on completion
        ];

        // Recent asesmen
        $recentAsesmens = Asesmen::with(['userRoles.user'])
            ->withCount('userRoles')
            ->latest()
            ->take(5)
            ->get();

        // Most active asesor
        $activeAsesor = User::select('users.*', DB::raw('COUNT(asesmen_user_roles.id) as asesmen_count'))
            ->join('asesmen_user_roles', 'users.id', '=', 'asesmen_user_roles.id_user')
            ->groupBy('users.id')
            ->orderByDesc('asesmen_count')
            ->take(5)
            ->get();

        return view('asesmen.dashboard', compact('stats', 'recentAsesmens', 'activeAsesor'));
    }

    /**
     * Search users for penugasan (AJAX)
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $idAsesmen = $request->get('id_asesmen');

        // Get already assigned users
        $assignedUserIds = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->pluck('id_user')
            ->toArray();

        // Search available users
        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->whereNotIn('id', $assignedUserIds)
            ->where('role', '!=', 'admin')
            ->limit(10)
            ->get(['id', 'name', 'email', 'role']);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
