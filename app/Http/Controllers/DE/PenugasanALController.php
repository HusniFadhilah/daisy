<?php

namespace App\Http\Controllers\DE;

use App\Models\Role;
use App\Models\User;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Models\AsesmenLapangan;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendPenawaranAsesmenEmail;

class PenugasanALController extends Controller
{
    /**
     * Dashboard penugasan AL
     */
    public function index(Request $request)
    {
        // Build query - pengajuan yang siap untuk AL
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
        if ($request->filled('status_al')) {
            switch ($request->status_al) {
                case 'siap_al':
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AK_DILAPORKAN);
                    })
                        ->whereDoesntHave('asesmen.asesmenUserRoles', function ($q) {
                            $q->where('jenis_asesmen', 'al');
                        });
                    break;
                case 'sudah_ditugaskan':
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->whereIn('l.status_to', [
                                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                                PengajuanAkreditasi::STATUS_AL_SELESAI,
                            ]);
                    });
                    break;
                case 'selesai':
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN);
                    });
                    break;
                case 'selesai':
                    $query->where('status', PengajuanAkreditasi::STATUS_AL_DILAPORKAN);
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

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get universities & users for assignment
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();
        $availableUsers = User::notAdmin()->orderBy('name')->get();

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
            'universities',
            'availableUsers'
        ));
    }

    /**
     * Detail penugasan AL untuk satu pengajuan
     */
    // public function show($id)
    // {
    //     $alScopeStatuses = [
    //         PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
    //         PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
    //         PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
    //         PengajuanAkreditasi::STATUS_AL_SELESAI,
    //         PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
    //     ];

    //     $pengajuan = PengajuanAkreditasi::with([
    //         'studyProgram.university',
    //         'studyProgram.degreeLevel',
    //         'asesmen.asesmenLapangan',
    //         'asesmen.asesmenUserRoles' => function ($q) {
    //             $q->where('jenis_asesmen', 'al')
    //                 ->whereHas('role_selected', fn($r) => $r->where('name', 'asesor'))
    //                 ->with(['user', 'role_selected']);
    //         },
    //         // optional: buat tampilan status log di table
    //         'statusLog' => function ($q) use ($alScopeStatuses) {
    //             $q->whereIn('status_to', $alScopeStatuses)
    //                 ->orderBy('changed_at', 'desc');
    //         },
    //     ])
    //         ->whereExists(function ($q) use ($alScopeStatuses) {
    //             $q->select(DB::raw(1))
    //                 ->from('pengajuan_status_log as l')
    //                 ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
    //                 ->whereIn('l.status_to', $alScopeStatuses);
    //         });

    //     // Get progress penilaian AL
    //     $totalElemens = DB::table('elemen_standar')->count();

    //     // Progress asesor AL
    //     $asesorProgress = DB::table('penilaian_elemen_al')
    //         ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
    //         ->whereNotNull('skor')
    //         ->groupBy('id_asesor')
    //         ->select('id_asesor', DB::raw('COUNT(*) as completed'))
    //         ->pluck('completed', 'id_asesor');

    //     // Map progress
    //     $userProgress = [];
    //     if ($pengajuan->asesmen) {
    //         foreach ($pengajuan->asesmen->asesmenUserRoles as $assignment) {
    //             $completed = $asesorProgress[$assignment->id_user] ?? 0;

    //             $userProgress[$assignment->id_user] = [
    //                 'total' => $totalElemens,
    //                 'completed' => $completed,
    //                 'percentage' => $totalElemens ? round(($completed / $totalElemens) * 100, 1) : 0,
    //             ];
    //         }
    //     }

    //     // Requirements status
    //     $requirementsStatus = $this->getRequirementsStatus($pengajuan);

    //     // Available users for assignment
    //     $availableUsers = User::notAdmin()->orderBy('name')->get();

    //     return view('de.penugasan-al.show', compact(
    //         'pengajuan',
    //         'userProgress',
    //         'requirementsStatus',
    //         'availableUsers',
    //         'totalElemens'
    //     ));
    // }

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

        // Get progress penilaian AL
        $totalElemens = DB::table('elemen_standar')->count();

        // Progress asesor AL
        $asesorProgress = DB::table('penilaian_elemen_al')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('skor')
            ->groupBy('id_asesor')
            ->select('id_asesor', DB::raw('COUNT(*) as completed'))
            ->pluck('completed', 'id_asesor');

        // Map progress
        $userProgress = [];
        if ($pengajuan->asesmen) {
            foreach ($pengajuan->asesmen->asesmenUserRoles as $assignment) {
                $completed = $asesorProgress[$assignment->id_user] ?? 0;

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

        return view('de.penugasan-al.show', compact(
            'pengajuan',
            'userProgress',
            'requirementsStatus',
            'availableUsers',
            'totalElemens'
        ));
    }

    /**
     * Tugaskan asesor untuk AL
     * NOTE: Tidak ada "Mark Ready" - langsung assign asesor dengan schedule + lokasi
     */
    public function assignAsesor(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi_visitasi' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

            // Get or create Asesmen
            if (!$pengajuan->asesmen) {
                $asesmen = Asesmen::create([
                    'name' => 'Asesmen - ' . $pengajuan->studyProgram->name,
                    'code' => 'LAMDEPILAR-' . $pengajuan->id . '-' . now()->format('YmdHis'),
                    'id_program_studi' => $pengajuan->id_program_studi,
                    'id_pengajuan' => $pengajuan->id,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'status' => 'active',
                ]);

                $pengajuan->update(['id_asesmen' => $asesmen->id]);
            } else {
                $asesmen = $pengajuan->asesmen;
            }

            // Get or create AsesmenLapangan
            $asesmenLapangan = AsesmenLapangan::firstOrCreate(
                ['id_asesmen' => $asesmen->id],
                [
                    'code' => 'AL-' . $asesmen->code,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'lokasi_visitasi' => $request->lokasi_visitasi,
                    'status' => 'active',
                ]
            );

            // Update tanggal & lokasi jika sudah ada
            if (!$asesmenLapangan->wasRecentlyCreated) {
                $asesmenLapangan->update([
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'lokasi_visitasi' => $request->lokasi_visitasi,
                ]);
            }

            // Get asesor role
            $asesorRole = Role::where('name', 'asesor')->firstOrFail();

            // Check if user already assigned
            $exists = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                ->where('id_user', $request->id_user)
                ->where('jenis_asesmen', 'al')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesor sudah ditugaskan untuk AL ini'
                ], 422);
            }

            // Determine urutan_asesor
            $existingUrutans = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                ->where('jenis_asesmen', 'al')
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

            // Create assignment
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $asesmen->id,
                'id_user' => $request->id_user,
                'id_role' => $asesorRole->id,
                'jenis_asesmen' => 'al',
                'id_asesmen_lapangan' => $asesmenLapangan->id,
                'urutan_asesor' => $urutanAsesor,
                'status_penawaran' => 'pending',
            ]);

            $user = User::find($request->id_user);

            // Update status pengajuan (gunakan checkUpdateStatusAKAL)
            $statusFrom = $pengajuan->status;
            $pengajuan->checkUpdateStatusAKAL('al', 'status_asesor_assigned');
            $pengajuan->statusLog()->firstOrCreate(
                [
                    'status_from' => $statusFrom,
                    'status_to'   => PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                ],
                [
                    'changed_by'  => Auth::id(),
                    'keterangan'  => 'Penugasan asesor untuk asesmen lapangan telah dilakukan',
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
                'message' => "Asesor {$user->name} berhasil ditugaskan untuk AL (Asesor {$urutanAsesor}). Email penawaran telah dikirim.",
                'data' => [
                    'assignment' => $assignment,
                    'user' => $user,
                    'urutan_asesor' => $urutanAsesor,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('assignAsesor failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menugaskan asesor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus assignment asesor
     */
    public function removeAsesor(Request $request, $id, $userId)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

            $assignment = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('id_user', $userId)
                ->where('jenis_asesmen', 'al')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan tidak ditemukan'
                ], 404);
            }

            // Check if user has penilaian
            $hasPenilaian = DB::table('penilaian_elemen_al')
                ->where('id_asesmen', $pengajuan->asesmen->id)
                ->where('id_asesor', $userId)
                ->exists();

            if ($hasPenilaian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesor tidak bisa dihapus karena sudah melakukan penilaian.'
                ], 422);
            }

            // Check minimum requirements (min 2 asesor)
            if ($assignment->status_penawaran === 'accepted') {
                $currentCount = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                    ->where('jenis_asesmen', 'al')
                    ->whereIn('status_penawaran', ['accepted', 'pending'])
                    ->count();

                if ($currentCount <= 2) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tidak bisa menghapus asesor karena akan melanggar persyaratan minimum (2 asesor untuk AL)."
                    ], 422);
                }
            }

            $userName = $assignment->user->name;
            $assignment->delete();

            // Reorganize urutan
            $this->reorganizeAsesorOrder($pengajuan->asesmen->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$userName} berhasil dihapus dari AL. Urutan asesor telah diatur ulang."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('removeAsesor failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus asesor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update jadwal AL
     */
    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi_visitasi' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
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
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal AL berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateSchedule failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jadwal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics()
    {
        $alScopeStatuses = [
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_SELESAI,
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
        ];

        $base = PengajuanAkreditasi::query()
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $alScopeStatuses));

        // ✅ historical (status log)
        $total = (clone $base)->count();

        $selesai = (clone $base)
            ->whereHas('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN))
            ->count();

        // ✅ snapshot (pengajuan_akreditasi.status)
        // "Siap AL" = status saat ini masih AK_DILAPORKAN (belum masuk fase AL)
        $siapAL = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            ->count();

        // "Sudah Ditugaskan" = status saat ini sudah masuk fase AL (assigned / in progress / selesai),
        // dan punya asesmen lapangan
        $sudahDitugaskan = (clone $base)
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AL_SELESAI,
                // opsional kalau mau dianggap sudah ditugaskan juga:
                // PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ])
            ->whereHas('asesmen.asesmenLapangan')
            ->count();

        return [
            'total' => $total,
            'siap_al' => $siapAL,
            'sudah_ditugaskan' => $sudahDitugaskan,
            'selesai' => $selesai,
        ];
    }

    /**
     * Get requirements status helper
     */
    private function getRequirementsStatus($pengajuan)
    {
        if (!$pengajuan->asesmen) {
            return [
                'met' => false,
                'asesor_count' => 0,
                'missing' => ['2 asesor'],
            ];
        }

        $asesorCount = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
            ->where('jenis_asesmen', 'al')
            ->whereIn('status_penawaran', ['pending', 'accepted'])
            ->count();

        return [
            'met' => $asesorCount >= 2,
            'asesor_count' => $asesorCount,
            'missing' => $asesorCount < 2 ? ['2 asesor'] : [],
        ];
    }

    /**
     * Reorganize asesor order
     */
    private function reorganizeAsesorOrder($idAsesmen)
    {
        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
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
