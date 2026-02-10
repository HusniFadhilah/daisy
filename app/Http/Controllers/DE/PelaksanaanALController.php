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

class PelaksanaanALController extends Controller
{
    /**
     * Dashboard pelaksanaan & monitoring AL
     */
    public function index(Request $request)
    {
        $alStatuses = [
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_SELESAI,
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
        ];

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenLapangan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->with(['user', 'role_selected']);
            },
            // opsional untuk tampilan status/tanggal berbasis log
            'statusLog' => function ($q) use ($alStatuses) {
                $q->whereIn('status_to', $alStatuses)->orderBy('changed_at', 'desc');
            },
        ])
            ->whereExists(function ($q) use ($alStatuses) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $alStatuses);
            });

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status
        if ($request->filled('status_al')) {
            switch ($request->status_al) {
                case 'sedang_visitasi':
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->whereIn('l.status_to', [
                                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                            ]);
                    });
                    break;

                case 'perlu_validator':
                    // pernah AL_SELESAI, belum AL_DILAPORKAN + belum ada validator assignment
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_SELESAI);
                    })->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN);
                    })->whereDoesntHave('asesmen.asesmenUserRoles', function ($q) {
                        $q->where('jenis_asesmen', 'al')
                            ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'));
                    });
                    break;

                case 'sedang_pelaporan':
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_SELESAI);
                    })->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN);
                    })->whereHas('asesmen.asesmenUserRoles', function ($q) {
                        $q->where('jenis_asesmen', 'al')
                            ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'));
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

        // Get universities & users
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();
        $availableValidators = User::notAdmin()->orderBy('name')->get();

        // Check if AJAX
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.pelaksanaan-al.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.pelaksanaan-al.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'availableValidators'
        ));
    }

    /**
     * Detail pelaksanaan AL untuk satu pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenLapangan',
            'asesmen.beritaAcaraAL' => function ($q) {
                $q->where('type', 'berita_acara_al')
                    ->where('is_active', true)
                    ->with('uploader')
                    ->latest('uploaded_at');
            },
            'asesmen.documents' => function ($q) {
                $q->where('type', 'lha_asesor')
                    ->where('is_active', true)
                    ->with('uploadedBy')
                    ->latest('uploaded_at');
            },
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->with(['user', 'role_selected']);
            },
            'asesmen.asesmenKecukupan.validators' => function ($q) {
                $q->whereIn('status_penawaran', ['accepted', 'pending'])
                    ->with('user');
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get progress penilaian AL dari asesor
        $totalElemens = DB::table('elemen_standar')->count();

        // Progress per asesor
        $asesorProgress = DB::table('penilaian_elemen_al')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('skor')
            ->groupBy('id_asesor')
            ->select('id_asesor', DB::raw('COUNT(*) as completed'))
            ->pluck('completed', 'id_asesor');

        // Berita Acara progress (check from asesmen_documents)
        $beritaAcaraProgress = null;
        if ($pengajuan->asesmen) {
            $beritaAcaraProgress = $pengajuan->asesmen->documents()
                ->where('type', 'berita_acara_al')
                ->where('is_active', true)
                ->first();
        }

        // Map progress
        $userProgress = [];
        if ($pengajuan->asesmen) {
            foreach ($pengajuan->asesmen->asesmenUserRoles as $assignment) {
                if ($assignment->role_selected->name === 'asesor') {
                    $completed = $asesorProgress[$assignment->id_user] ?? 0;
                    $userProgress[$assignment->id_user] = [
                        'total' => $totalElemens,
                        'completed' => $completed,
                        'percentage' => $totalElemens ? round(($completed / $totalElemens) * 100, 1) : 0,
                    ];
                } elseif ($assignment->role_selected->name === 'validator') {
                    // Validator progress based on berita acara completion
                    $userProgress[$assignment->id_user] = [
                        'berita_acara_completed' => $beritaAcaraProgress ? true : false,
                        'percentage' => $beritaAcaraProgress ? 100 : 0,
                    ];
                }
            }
        }

        // Validator assignment status
        $hasValidator = $pengajuan->asesmen?->asesmenUserRoles()
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->exists();

        // ✅ Get Validator AK (jika ada)
        $validatorAK = $pengajuan->asesmen?->asesmenKecukupan?->validators()
            ->whereIn('status_penawaran', ['accepted', 'pending'])
            ->with('user')
            ->first();

        // Available validators
        $availableValidators = User::notAdmin()->orderBy('name')->get();

        return view('de.pelaksanaan-al.show', compact(
            'pengajuan',
            'userProgress',
            'hasValidator',
            'beritaAcaraProgress',
            'availableValidators',
            'validatorAK', // ✅ NEW
            'totalElemens'
        ));
    }

    /**
     * ✅ UPDATE: Assign validator untuk rekap berita acara & pelaporan AL
     */
    public function assignValidator(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'use_validator_ak' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'asesmen.asesmenLapangan',
                'asesmen.asesmenKecukupan.validators'
            ])->findOrFail($id);

            if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenLapangan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesmen Lapangan tidak ditemukan'
                ], 404);
            }

            $asesmen = $pengajuan->asesmen;
            $asesmenLapangan = $asesmen->asesmenLapangan;

            // Get validator role
            $validatorRole = Role::where('name', 'validator')->firstOrFail();

            // ✅ Handle Validator AK Logic (mirip pattern di Penugasan AK)
            $useValidatorAK = $request->boolean('use_validator_ak', false);
            $validatorAK = null;
            $user = null;

            if ($useValidatorAK) {
                // Get validator AK
                $validatorAK = $asesmen->asesmenKecukupan?->validators()
                    ->whereIn('status_penawaran', ['accepted', 'pending'])
                    ->first();

                if (!$validatorAK) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validator AK tidak ditemukan. Silakan pilih validator lain.'
                    ], 422);
                }

                // Override user dengan validator AK
                $user = $validatorAK->user;
            } else {
                // Pilih validator baru
                if (!$request->id_user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Silakan pilih validator atau gunakan validator AK yang ada'
                    ], 422);
                }
                $user = User::findOrFail($request->id_user);
            }

            // Check if validator already assigned
            $exists = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                ->where('id_user', $user->id)
                ->where('id_role', $validatorRole->id)
                ->where('jenis_asesmen', 'al')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validator telah ditugaskan untuk pelaporan AL ini'
                ], 422);
            }

            // ✅ Status langsung accepted jika menggunakan validator AK
            $statusPenawaran = ($useValidatorAK && $validatorAK) ? 'accepted' : 'pending';

            // Create assignment
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $asesmen->id,
                'id_user' => $user->id,
                'id_role' => $validatorRole->id,
                'jenis_asesmen' => 'al',
                'id_asesmen_lapangan' => $asesmenLapangan->id,
                'urutan_asesor' => null, // NULL for validator
                'status_penawaran' => $statusPenawaran, // ✅
                'status_pekerjaan' => 'not_started',
                'tanggal_penugasan' => now(),
            ]);

            // ✅ Kirim email HANYA jika bukan validator AK
            if (!($useValidatorAK && $validatorAK)) {
                try {
                    SendPenawaranAsesmenEmail::dispatch($assignment);
                } catch (\Exception $e) {
                    Log::error("Gagal dispatch email job penawaran validator AL", [
                        'assignment_id' => $assignment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $message = "Validator {$user->name} berhasil ditugaskan untuk rekap berita acara & pelaporan AL.";

            if ($useValidatorAK && $validatorAK) {
                $message .= " Status langsung diterima (accepted).";
            } else {
                $message .= " Email penawaran telah dikirim.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'assignment' => $assignment,
                    'user' => $user,
                    'used_validator_ak' => $useValidatorAK && $validatorAK !== null,
                    'status_penawaran' => $statusPenawaran,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('assignValidator failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menugaskan validator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove validator assignment
     */
    public function removeValidator(Request $request, $id, $userId)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

            $assignment = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('id_user', $userId)
                ->where('jenis_asesmen', 'al')
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan validator tidak ditemukan'
                ], 404);
            }

            // Check if validator has started work (berita acara created in documents)
            $hasBeritaAcara = $pengajuan->asesmen->documents()
                ->where('type', 'berita_acara_al')
                ->where('is_active', true)
                ->where('uploaded_by', $userId)
                ->exists();

            if ($hasBeritaAcara) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validator tidak bisa dihapus karena telah mulai membuat berita acara.'
                ], 422);
            }

            $userName = $assignment->user->name;
            $assignment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$userName} berhasil dihapus dari penugasan validator AL."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('removeValidator failed', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus validator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics()
    {
        $alStatuses = [
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_SELESAI,
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
        ];

        $base = PengajuanAkreditasi::query()
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $alStatuses));

        // ✅ historical (status log)
        $total = (clone $base)->count();

        $selesai = (clone $base)
            ->whereHas('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN))
            ->count();

        // ✅ snapshot (pengajuan_akreditasi.status)
        $sedangVisitasi = (clone $base)
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            ])
            ->count();

        // Perlu validator: status saat ini AL_SELESAI dan belum ada validator
        $perluValidator = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AL_SELESAI)
            ->whereDoesntHave('asesmen.asesmenUserRoles', function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'));
            })
            ->count();

        // Sedang pelaporan: status saat ini AL_SELESAI dan telah ada validator
        $sedangPelaporan = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AL_SELESAI)
            ->whereHas('asesmen.asesmenUserRoles', function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'));
            })
            ->count();

        return [
            'total' => $total,
            'sedang_visitasi' => $sedangVisitasi,
            'perlu_validator' => $perluValidator,
            'sedang_pelaporan' => $sedangPelaporan,
            'selesai' => $selesai,
        ];
    }
}
