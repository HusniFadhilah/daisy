<?php

namespace App\Http\Controllers\DE;

use App\Models\Role;
use App\Models\User;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Models\AsesmenKecukupan;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendPenawaranAsesmenEmail;

class PenugasanAKController extends Controller
{
    /**
     * Dashboard penugasan AK
     */
    public function index(Request $request)
    {
        $statusList = [
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
        ];

        // Build query - pengajuan yang sudah bisa lanjut ke AK
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupan.asesors',
            'asesmen.asesmenKecukupan.validators',

            // opsional: biar status terakhir bisa ditampilkan di tabel
            'statusLog',
        ])
            ->whereHas('statusLog', function ($q) use ($statusList) {
                $q->whereIn('status_to', $statusList);
            });

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status
        if ($request->filled('status_ak')) {
            switch ($request->status_ak) {
                case 'belum_ditugaskan':
                    $query->whereHas('statusLog', function ($q) {
                        $q->whereIn('status_to', [
                            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                            PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                        ]);
                    })
                        ->whereDoesntHave('asesmen.asesmenKecukupan.asesors');
                    break;

                case 'sudah_ditugaskan':
                    $query->whereHas('statusLog', function ($q) {
                        $q->whereIn('status_to', [
                            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                            PengajuanAkreditasi::STATUS_AK_SELESAI,
                        ]);
                    })
                        ->whereHas('asesmen.asesmenKecukupan');
                    break;

                case 'selesai':
                    $query->whereHas('statusLog', function ($q) {
                        $q->where('status_to', PengajuanAkreditasi::STATUS_AK_DILAPORKAN);
                    });
                    break;
            }
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

        $stats = $this->calculateStatistics();

        $universities = \App\Models\University::nonExample()->orderBy('name')->get();
        $availableUsers = User::notAdmin()->orderBy('name')->get();
        $roles = Role::whereIn('name', ['asesor', 'validator'])->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.penugasan-ak.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.penugasan-ak.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'availableUsers',
            'roles'
        ));
    }

    /**
     * Detail penugasan AK untuk satu pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupan.asesors.user',
            'asesmen.asesmenKecukupan.validators.user',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'ak')
                    ->with(['user', 'role_selected']);
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get progress penilaian
        $totalElemens = DB::table('elemen_standar')->count();

        // Progress asesor AK
        $asesorProgress = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('skor')
            ->groupBy('id_asesor')
            ->select('id_asesor', DB::raw('COUNT(*) as completed'))
            ->pluck('completed', 'id_asesor');

        // Progress validator AK
        $validatorProgress = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('validated_at')
            ->groupBy('validated_by')
            ->select('validated_by', DB::raw('COUNT(DISTINCT id_elemen) as completed'))
            ->pluck('completed', 'validated_by');

        // Map progress
        $userProgress = [];
        if ($pengajuan->asesmen) {
            foreach ($pengajuan->asesmen->asesmenUserRoles as $assignment) {
                if ($assignment->role_selected->name === 'asesor') {
                    $completed = $asesorProgress[$assignment->id_user] ?? 0;
                } elseif ($assignment->role_selected->name === 'validator') {
                    $completed = $validatorProgress[$assignment->id_user] ?? 0;
                } else {
                    $completed = 0;
                }

                $userProgress[$assignment->id_user] = [
                    'total' => $totalElemens,
                    'completed' => $completed,
                    'percentage' => $totalElemens ? round(($completed / $totalElemens) * 100, 1) : 0,
                ];
            }
        }

        // Requirements status
        $requirementsStatus = $this->getRequirementsStatus($pengajuan);

        // Available users for assignment
        $availableUsers = User::notAdmin()->orderBy('name')->get();
        $roles = Role::whereIn('name', ['asesor', 'validator'])->get();

        return view('de.penugasan-ak.show', compact(
            'pengajuan',
            'userProgress',
            'requirementsStatus',
            'availableUsers',
            'roles',
            'totalElemens'
        ));
    }

    /**
     * Tetapkan status "Siap Lanjut ke AK"
     * (Optional - karena bisa langsung assign dari STATUS_VALIDASI_BORANG_DILAPORKAN)
     */
    public function markReadyForAK(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validasi: harus sudah status VALIDASI_BORANG_DILAPORKAN
            if (!in_array($pengajuan->status, [
                PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan harus dalam status VALIDASI BORANG DILAPORKAN untuk bisa lanjut ke AK.'
                ], 422);
            }

            $tanggalMulai = $request->tanggal_mulai;
            $tanggalSelesai = $request->tanggal_selesai;

            // Create/Get Asesmen
            if (!$pengajuan->asesmen) {
                $asesmen = Asesmen::create([
                    'name' => 'Asesmen - ' . $pengajuan->studyProgram->name,
                    'code' => $pengajuan->code,
                    'id_program_studi' => $pengajuan->id_program_studi,
                    'id_pengajuan' => $pengajuan->id,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'status' => 'active',
                ]);

                $pengajuan->update(['id_asesmen' => $asesmen->id]);
            } else {
                $asesmen = $pengajuan->asesmen;
                $asesmen->update([
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                ]);
            }

            // Create AsesmenKecukupan if not exists
            $asesmenKecukupan = AsesmenKecukupan::firstOrCreate(
                ['id_asesmen' => $asesmen->id],
                [
                    'code' => 'AK-' . $asesmen->code,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'status' => 'active',
                ]
            );

            // Update tanggal jika sudah ada
            if (!$asesmenKecukupan->wasRecentlyCreated) {
                $asesmenKecukupan->update([
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                ]);
            }

            // Update status to PENGAJUAN_COMPLETED (optional)
            if ($pengajuan->status === PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN) {
                $statusFrom = $pengajuan->status;
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                    'tanggal_lanjut_ak' => now(),
                ]);

                $pengajuan->statusLog()->create([
                    'status_from' => $statusFrom,
                    'status_to' => PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
                    'changed_by' => Auth::id(),
                    'keterangan' => "Ditetapkan siap untuk Asesmen Kecukupan (AK) - Periode: {$tanggalMulai} s/d {$tanggalSelesai}",
                    'changed_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil ditetapkan siap untuk Asesmen Kecukupan (AK). Silakan tugaskan asesor & validator.',
                'data' => [
                    'asesmen_id' => $asesmen->id,
                    'ak_id' => $asesmenKecukupan->id,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('markReadyForAK failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menetapkan status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tugaskan asesor/validator untuk AK
     */
    public function assignUser(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_role' => 'required|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('asesmen.asesmenKecukupan')->findOrFail($id);

            if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenKecukupan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesmen Kecukupan belum dibuat. Tetapkan status "Siap AK" terlebih dahulu.'
                ], 422);
            }

            $asesmen = $pengajuan->asesmen;
            $asesmenKecukupan = $asesmen->asesmenKecukupan;
            $role = Role::findOrFail($request->id_role);

            // Check if user already assigned
            $exists = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                ->where('id_user', $request->id_user)
                ->where('id_role', $request->id_role)
                ->where('jenis_asesmen', 'ak')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User sudah ditugaskan dengan role ini untuk AK'
                ], 422);
            }

            // Determine urutan_asesor if asesor
            $urutanAsesor = null;
            if ($role->name === 'asesor') {
                $existingUrutans = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                    ->where('jenis_asesmen', 'ak')
                    ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                    ->pluck('urutan_asesor')
                    ->toArray();

                sort($existingUrutans);

                $urutanAsesor = 1;
                foreach ($existingUrutans as $urutan) {
                    if ($urutan !== $urutanAsesor) {
                        break;
                    }
                    $urutanAsesor++;
                }
            }

            // Create assignment
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $asesmen->id,
                'id_user' => $request->id_user,
                'id_role' => $request->id_role,
                'jenis_asesmen' => 'ak',
                'id_asesmen_kecukupan' => $asesmenKecukupan->id,
                'urutan_asesor' => $urutanAsesor,
                'status_penawaran' => 'pending',
            ]);

            $user = User::find($request->id_user);

            // Check requirements
            $requirementsMet = $asesmenKecukupan->hasMinimumRequirements();
            $missingRequirements = $asesmenKecukupan->getMissingRequirements();

            // ✅ Update status pengajuan (gunakan checkUpdateStatusAKAL)
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

            // Send email
            try {
                SendPenawaranAsesmenEmail::dispatch($assignment);
            } catch (\Exception $e) {
                Log::error("Gagal dispatch email job penawaran", [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil ditugaskan sebagai {$role->alias} untuk AK" .
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
            DB::rollBack();
            Log::error('assignUser failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menugaskan user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus assignment user
     */
    public function removeUser(Request $request, $id, $userId)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

            $assignment = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('id_user', $userId)
                ->where('jenis_asesmen', 'ak')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan tidak ditemukan'
                ], 404);
            }

            // Check if user has penilaian
            $hasPenilaian = DB::table('penilaian_elemen_ak')
                ->where('id_asesmen', $pengajuan->asesmen->id)
                ->where(function ($query) use ($userId) {
                    $query->where('id_asesor', $userId)
                        ->orWhere('validated_by', $userId);
                })->exists();

            if ($hasPenilaian) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak bisa dihapus karena sudah melakukan penilaian.'
                ], 422);
            }

            // Check minimum requirements
            $role = Role::find($assignment->id_role);
            if ($assignment->status_penawaran === 'accepted') {
                $currentCount = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                    ->where('jenis_asesmen', 'ak')
                    ->where('id_role', $assignment->id_role)
                    ->whereIn('status_penawaran', ['accepted', 'pending'])
                    ->count();

                $minRequired = $role->name === 'asesor' ? 2 : 1;

                if ($currentCount <= $minRequired) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tidak bisa menghapus {$role->alias} karena akan melanggar persyaratan minimum ({$minRequired} {$role->alias} untuk AK)."
                    ], 422);
                }
            }

            $userName = $assignment->user->name;
            $isAsesor = $role->name === 'asesor';
            $assignment->delete();

            if ($isAsesor) {
                $this->reorganizeAsesorOrder($pengajuan->asesmen->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$userName} berhasil dihapus dari AK." .
                    ($isAsesor ? " Urutan asesor telah diatur ulang." : "")
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('removeUser failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignments AJAX
     */
    public function getAssignments($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'asesmen.asesmenUserRoles' => function ($q) {
                    $q->where('jenis_asesmen', 'ak')
                        ->whereIn('status_penawaran', ['pending', 'accepted'])
                        ->with(['user', 'role_selected']);
                }
            ])->findOrFail($id);

            if (!$pengajuan->asesmen) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $assignments = $pengajuan->asesmen->asesmenUserRoles->groupBy('role_selected.name')->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'user' => [
                            'id' => $item->user->id,
                            'name' => $item->user->name,
                            'email' => $item->user->email,
                        ],
                        'role' => [
                            'id' => $item->role_selected->id,
                            'name' => $item->role_selected->name,
                            'alias' => $item->role_selected->alias,
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
                'data' => $assignments
            ]);
        } catch (\Exception $e) {
            Log::error('getAssignments failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get requirements status
     */
    public function getRequirementsStatusAjax($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with('asesmen.asesmenKecukupan')->findOrFail($id);

            if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenKecukupan) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'requirements_met' => false,
                        'missing' => ['2 asesor', '1 validator'],
                        'current' => ['asesor' => 0, 'validator' => 0],
                    ]
                ]);
            }

            $asesmenKecukupan = $pengajuan->asesmen->asesmenKecukupan;
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
        } catch (\Exception $e) {
            Log::error('getRequirementsStatusAjax failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics()
    {
        $allStatuses = [
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
        ];

        $total = PengajuanAkreditasi::whereHas('statusLog', function ($q) use ($allStatuses) {
            $q->whereIn('status_to', $allStatuses);
        })->count();

        $belumDitugaskan = PengajuanAkreditasi::whereHas('statusLog', function ($q) {
            $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
            ]);
        })
            ->whereDoesntHave('asesmen.asesmenKecukupan.asesors')
            ->count();

        $sudahDitugaskan = PengajuanAkreditasi::whereHas('statusLog', function ($q) {
            $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                PengajuanAkreditasi::STATUS_AK_SELESAI,
            ]);
        })
            ->whereHas('asesmen.asesmenKecukupan')
            ->count();

        $selesai = PengajuanAkreditasi::whereHas('statusLog', function ($q) {
            $q->where('status_to', PengajuanAkreditasi::STATUS_AK_DILAPORKAN);
        })->count();

        return [
            'total' => $total,
            'belum_ditugaskan' => $belumDitugaskan,
            'sudah_ditugaskan' => $sudahDitugaskan,
            'selesai' => $selesai,
        ];
    }

    /**
     * Get requirements status helper
     */
    private function getRequirementsStatus($pengajuan)
    {
        if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenKecukupan) {
            return [
                'met' => false,
                'asesor_count' => 0,
                'validator_count' => 0,
                'missing' => ['2 asesor', '1 validator'],
            ];
        }

        $asesmenKecukupan = $pengajuan->asesmen->asesmenKecukupan;
        $asesorCount = $asesmenKecukupan->asesors->count();
        $validatorCount = $asesmenKecukupan->validators->count();

        return [
            'met' => $asesmenKecukupan->hasMinimumRequirementsWithCounts($asesorCount, $validatorCount),
            'asesor_count' => $asesorCount,
            'validator_count' => $validatorCount,
            'missing' => $asesmenKecukupan->getMissingRequirementsWithCounts($asesorCount, $validatorCount),
        ];
    }

    /**
     * Reorganize asesor order
     */
    private function reorganizeAsesorOrder($idAsesmen)
    {
        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'ak')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->orderBy('urutan_asesor')
            ->get();

        $newUrutan = 1;
        foreach ($asesors as $asesor) {
            if ($asesor->urutan_asesor !== $newUrutan) {
                $asesor->update(['urutan_asesor' => $newUrutan]);
            }
            $newUrutan++;
        }

        return $asesors->count();
    }
}
