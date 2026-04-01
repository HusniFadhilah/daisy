<?php

namespace App\Http\Controllers\DE\Banding;

use App\Http\Controllers\Controller;
use App\Jobs\SendPenawaranAsesmenEmail;
use App\Mail\Reminder\ReminderPenawaranAsesmenMail;
use App\Mail\Reminder\ReminderProgressAsesmenMail;
use App\Models\Asesmen;
use App\Models\AsesmenKecukupanBanding;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\Role;
use App\Models\User;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PenugasanAKBandingController extends Controller
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
     * Dashboard penugasan AK
     */
    public function index(Request $request)
    {
        $statusList = [
            PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI,
            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
            PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
        ];

        // Build query - pengajuan yang sudah bisa lanjut ke AK
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupanBanding.asesors',
            'asesmen.asesmenKecukupanBanding.validators',

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
        if ($request->filled('status_ak_banding')) {
            switch ($request->status_ak_banding) {
                case 'belum_ditugaskan':
                    $query->whereHas('statusLog', function ($q) {
                        $q->whereIn('status_to', [
                            PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI,
                            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                        ]);
                    })
                        ->whereDoesntHave('asesmen.asesmenKecukupanBanding.asesors');
                    break;

                case 'sudah_ditugaskan':
                    $query->whereHas('statusLog', function ($q) {
                        $q->whereIn('status_to', [
                            PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                            PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
                            PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
                            PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
                        ]);
                    })
                        ->whereHas('asesmen.asesmenKecukupanBanding');
                    break;

                case 'selesai':
                    $query->whereHas('statusLog', function ($q) {
                        $q->where('status_to', PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN);
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
        $roles = Role::whereIn('name', ['asesor_banding', 'validator'])->orderBy('name', 'desc')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.banding.penugasan-ak-banding.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.banding.penugasan-ak-banding.index', compact(
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
            'asesmen.asesmenKecukupanBanding.asesors.user',
            'asesmen.asesmenKecukupanBanding.validators.user',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'ak_banding')
                    ->with(['user', 'role_selected']);
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get progres penilaian
        $totalElemens = DB::table('elemen_standar')->count();

        // Progress asesor AK
        $asesorProgress = DB::table('penilaian_elemen_ak_banding')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('skor')
            ->groupBy('id_asesor')
            ->select('id_asesor', DB::raw('COUNT(*) as completed'))
            ->pluck('completed', 'id_asesor');

        // Progress validator AK
        $validatorProgress = DB::table('penilaian_elemen_ak_banding')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('validated_at')
            ->groupBy('validated_by')
            ->select('validated_by', DB::raw('COUNT(DISTINCT id_elemen) as completed'))
            ->pluck('completed', 'validated_by');

        // Map progress
        $userProgress = [];
        if ($pengajuan->asesmen) {
            foreach ($pengajuan->asesmen->asesmenUserRoles as $assignment) {
                if ($assignment->role_selected->name === 'asesor_banding') {
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
        $roles = Role::whereIn('name', ['asesor_banding', 'validator'])->orderBy('name', 'desc')->get();

        $validatorDokumen = $pengajuan->borangValidators()
            ->where('jenis_asesmen', 'dokumen')
            ->whereIn('status_penawaran', ['accepted', 'pending'])
            ->with('user')
            ->first();

        $assignmentReminders = [];

        if ($pengajuan->asesmen) {
            foreach ($pengajuan->asesmen->asesmenUserRoles as $assignment) {
                $assignmentReminders[$assignment->id] = $assignment->resolveReminderMeta();
            }
        }

        return view('de.banding.penugasan-ak-banding.show', compact(
            'pengajuan',
            'userProgress',
            'requirementsStatus',
            'availableUsers',
            'roles',
            'totalElemens',
            'validatorDokumen',
            'assignmentReminders'
        ));
    }

    public function kirimReminderPenawaran(Request $request)
    {
        $validated = $request->validate([
            'id_assignment' => 'required|array',
            'id_assignment.*' => 'exists:asesmen_user_roles,id',
            'pesan_reminder' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $assignments = AsesmenUserRole::query()
                ->with([
                    'user.activeEmails',
                    'role_selected',
                    'asesmen.pengajuan.studyProgram.university',
                    'asesmen.pengajuan.studyProgram.degreeLevel',
                ])
                ->whereIn('id', $validated['id_assignment'])
                ->where('jenis_asesmen', 'ak_banding')
                ->where('status_penawaran', 'pending')
                ->whereHas('role_selected', function ($q) {
                    $q->whereIn('name', ['asesor_banding', 'validator']);
                })
                ->get();

            $sent = 0;
            $emailGlobal = [];

            foreach ($assignments as $assignment) {
                $pengajuan = $assignment->asesmen->pengajuan;

                $recipientEmails = $this->recipientResolver->emailsForUsers(
                    collect([$assignment->user])
                );

                $result = $this->mailDelivery->sendToEmails(
                    $recipientEmails,
                    new ReminderPenawaranAsesmenMail($assignment, $validated['pesan_reminder']),
                    [],
                    [],
                    false
                );

                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to' => $pengajuan->status,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => 'Pengingat penawaran AK Banding dikirim ke ' .
                        $assignment->role_selected->alias . ' ' . $assignment->user->name,
                ]);

                if (!empty($result['sent_to'])) {
                    $emailGlobal = array_merge($emailGlobal, $result['sent_to']);
                }

                $sent++;
            }

            DB::commit();

            $emailGlobal = array_values(array_unique(array_filter($emailGlobal)));

            return back()->with(
                'success',
                "Pengingat penawaran berhasil dikirim ke {$sent} penugasan. Total " . count($emailGlobal) . " email terkirim."
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('kirimReminderPenawaran failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal mengirim pengingat penawaran: ' . $e->getMessage());
        }
    }

    public function kirimReminderAssignment(Request $request, $assignmentId)
    {
        $request->validate([
            'pesan_reminder' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $assignment = AsesmenUserRole::with([
                'user.activeEmails',
                'role_selected',
                'asesmen.pengajuan.studyProgram.university',
                'asesmen.pengajuan.studyProgram.degreeLevel',
            ])->findOrFail($assignmentId);

            if ($assignment->jenis_asesmen !== 'ak_banding') {
                return back()->with('error', 'Pengingat hanya berlaku untuk penugasan AK Banding.');
            }

            $meta = $assignment->resolveReminderMeta();

            if (!$meta) {
                return back()->with('error', 'Penugasan ini tidak memenuhi syarat untuk dikirim pengingat.');
            }

            $pesanReminder = $request->filled('pesan_reminder')
                ? $request->pesan_reminder
                : $meta['message'];

            $recipientEmails = $this->recipientResolver->emailsForUsers(
                collect([$assignment->user])
            );

            if ($meta['type'] === 'penawaran') {
                $result = $this->mailDelivery->sendToEmails(
                    $recipientEmails,
                    new ReminderPenawaranAsesmenMail($assignment, $pesanReminder),
                    [],
                    [],
                    false
                );
            } else {
                $result = $this->mailDelivery->sendToEmails(
                    $recipientEmails,
                    new ReminderProgressAsesmenMail($assignment, $pesanReminder),
                    [],
                    [],
                    false
                );
            }

            $pengajuan = $assignment->asesmen->pengajuan;

            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Pengingat ' . $meta['type'] . ' dikirim ke ' .
                    ($assignment->role_selected->alias ?? $assignment->role_selected->name) .
                    ' ' . $assignment->user->name,
            ]);

            DB::commit();

            $totalEmail = count($result['sent_to'] ?? []);

            return back()->with('success', "Pengingat berhasil dikirim ke {$assignment->user->name}. Total {$totalEmail} email terkirim.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('kirimReminderAssignment failed', [
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }

    /**
     * Tetapkan status "Siap Lanjut ke AK"
     * (Optional - karena bisa langsung assign dari STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI)
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
                PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan harus dalam status "Pembayaran Banding Diverifikasi" untuk bisa lanjut ke AK.'
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

            // Create AsesmenKecukupanBanding if not exists
            $asesmenKecukupanBanding = AsesmenKecukupanBanding::firstOrCreate(
                ['id_asesmen' => $asesmen->id],
                [
                    'code' => 'AK-' . $asesmen->code,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'status' => 'active',
                ]
            );

            // Update tanggal jika sudah ada
            if (!$asesmenKecukupanBanding->wasRecentlyCreated) {
                $asesmenKecukupanBanding->update([
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                ]);
            }

            if ($pengajuan->status === PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI) {
                $statusFrom = $pengajuan->status;
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    'tanggal_pelaksanaan_banding' => now(),
                ]);

                $pengajuan->statusLog()->create([
                    'status_from' => $statusFrom,
                    'status_to' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    'changed_by' => Auth::id(),
                    'keterangan' => "Ditetapkan siap untuk Asesmen Kecukupan (AK) - Periode: {$tanggalMulai} s/d {$tanggalSelesai}",
                    'changed_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akreditasi berhasil ditetapkan menjadi "Siap untuk Asesmen Kecukupan (AK) Banding". Silakan tugaskan asesor banding & validator.',
                'data' => [
                    'asesmen_id' => $asesmen->id,
                    'ak_id' => $asesmenKecukupanBanding->id,
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
     * ✅ UPDATE: Tugaskan asesor/validator untuk AK (dengan surat tugas)
     */
    public function assignUser(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_role' => 'required|exists:roles,id',
            'use_validator_dokumen' => 'nullable|boolean',
            'file_surat_tugas' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'asesmen.asesmenKecukupanBanding',
                'dokumen',
                'borangValidators'
            ])->findOrFail($id);

            if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenKecukupanBanding) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesmen Kecukupan Banding belum dibuat. Tetapkan status "Siap AK Banding" terlebih dahulu.'
                ], 422);
            }

            $asesmen = $pengajuan->asesmen;
            $asesmenKecukupanBanding = $asesmen->asesmenKecukupanBanding;
            $role = Role::findOrFail($request->id_role);
            $user = User::findOrFail($request->id_user);

            // Check if user already assigned
            $exists = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                ->where('id_user', $request->id_user)
                ->where('id_role', $request->id_role)
                ->where('jenis_asesmen', 'ak_banding')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User telah ditugaskan dengan role ini untuk AK banding'
                ], 422);
            }

            // ✅ Handle Validator Dokumen Logic
            $useValidatorDokumen = $request->boolean('use_validator_dokumen', false);
            $validatorDokumen = null;

            if ($role->name === 'validator' && $useValidatorDokumen) {
                // Get validator dokumen
                $validatorDokumen = $pengajuan->borangValidators()
                    ->where('jenis_asesmen', 'dokumen')
                    ->whereIn('status_penawaran', ['accepted', 'pending'])
                    ->first();

                if (!$validatorDokumen) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validator sebelumnya tidak ditemukan. Silakan pilih validator lain.'
                    ], 422);
                }

                // Override user dengan validator dokumen
                $user = $validatorDokumen->user;
                $request->merge(['id_user' => $user->id]);
            }

            // Determine urutan_asesor if asesor
            $urutanAsesor = null;
            if ($role->name === 'asesor_banding') {
                $existingUrutans = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                    ->where('jenis_asesmen', 'ak_banding')
                    ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
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

            // ✅ FIX: Jika validator dokumen, langsung accepted
            $statusPenawaran = ($useValidatorDokumen && $validatorDokumen) ? 'accepted' : 'pending';

            // Create assignment
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $asesmen->id,
                'id_user' => $user->id,
                'id_role' => $request->id_role,
                'jenis_asesmen' => 'ak_banding',
                'id_asesmen_kecukupan_banding' => $asesmenKecukupanBanding->id,
                'urutan_asesor' => $urutanAsesor,
                'status_penawaran' => $statusPenawaran, // ✅ accepted jika validator dokumen
            ]);

            // ✅ Handle Surat Tugas
            $suratTugasCreated = false;

            if ($role->name === 'asesor_banding') {
                // ✅ ASESOR: 1 surat tugas untuk SEMUA asesor
                $existingSuratTugas = $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'surat_tugas_asesor_ak_banding')
                    ->where('is_latest', true)
                    ->first();

                if (!$existingSuratTugas) {
                    if ($request->hasFile('file_surat_tugas')) {
                        $this->uploadSuratTugasAsesorAKBanding($pengajuan, $assignment, $request->file('file_surat_tugas'));
                    } else {
                        $this->generateSuratTugasAsesorAKBanding($pengajuan, $assignment);
                    }
                    $suratTugasCreated = true;
                }
            } elseif ($role->name === 'validator') {
                // ✅ VALIDATOR: Copy dari dokumen atau buat baru
                if ($useValidatorDokumen && $validatorDokumen) {
                    // Copy dari validator dokumen
                    $copied = $this->copySuratTugasFromValidatorDokumen($pengajuan, $assignment);
                    $suratTugasCreated = $copied;
                } else {
                    // Generate/upload baru
                    if ($request->hasFile('file_surat_tugas')) {
                        $this->uploadSuratTugasValidatorAK($pengajuan, $assignment, $request->file('file_surat_tugas'));
                        $suratTugasCreated = true;
                    } else {
                        $this->generateSuratTugasValidatorAK($pengajuan, $assignment);
                        $suratTugasCreated = true;
                    }
                }
            }

            // Update status pengajuan
            $statusFrom = $pengajuan->status;
            $pengajuan->checkUpdateStatusAKAL('ak_banding', 'status_asesor_assigned');
            $pengajuan->statusLog()->firstOrCreate(
                [
                    'status_from' => $statusFrom,
                    'status_to'   => PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                ],
                [
                    'changed_by'  => Auth::id(),
                    'keterangan'  => "Penugasan {$role->alias} untuk asesmen kecukupan banding telah dilakukan",
                    'changed_at'  => now(),
                ]
            );

            // ✅ FIX: Kirim email HANYA jika bukan validator dokumen
            if (!($useValidatorDokumen && $validatorDokumen)) {
                try {
                    SendPenawaranAsesmenEmail::dispatch($assignment);
                } catch (\Exception $e) {
                    Log::error("Gagal dispatch email job penawaran", [
                        'assignment_id' => $assignment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            $message = "User {$user->name} berhasil ditugaskan sebagai {$role->alias} untuk AK" .
                ($urutanAsesor ? " (Asesor banding {$urutanAsesor})" : "");

            // ✅ Pesan berbeda jika validator dokumen
            if ($useValidatorDokumen && $validatorDokumen) {
                $message .= ". Status langsung diterima (accepted).";
            } else {
                $message .= ". Email penawaran telah dikirim.";
            }

            if ($suratTugasCreated) {
                $message .= " Surat tugas telah dibuat.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'assignment' => $assignment,
                    'user' => $user,
                    'role' => $role,
                    'urutan_asesor' => $urutanAsesor,
                    'surat_tugas_created' => $suratTugasCreated,
                    'used_validator_dokumen' => $useValidatorDokumen && $validatorDokumen !== null,
                    'status_penawaran' => $statusPenawaran,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('assignUser failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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
                ->where('jenis_asesmen', 'ak_banding')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan tidak ditemukan'
                ], 404);
            }

            // Check if user has penilaian
            $hasPenilaian = DB::table('penilaian_elemen_ak_banding')
                ->where('id_asesmen', $pengajuan->asesmen->id)
                ->where(function ($query) use ($userId) {
                    $query->where('id_asesor', $userId)
                        ->orWhere('validated_by', $userId);
                })->exists();

            if ($hasPenilaian) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak bisa dihapus karena telah melakukan penilaian.'
                ], 422);
            }

            // Check minimum requirements
            $role = Role::find($assignment->id_role);
            if ($assignment->status_penawaran === 'accepted') {
                $currentCount = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                    ->where('jenis_asesmen', 'ak_banding')
                    ->where('id_role', $assignment->id_role)
                    ->whereIn('status_penawaran', ['accepted', 'pending'])
                    ->count();

                $minRequired = $role->name === 'asesor_banding' ? 2 : 1;

                // if ($currentCount <= $minRequired) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => "Tidak bisa menghapus {$role->alias} karena akan melanggar persyaratan minimum ({$minRequired} {$role->alias} untuk AK banding)."
                //     ], 422);
                // }
            }

            $userName = $assignment->user->name;
            $isAsesor = $role->name === 'asesor_banding';
            $assignment->delete();

            if ($isAsesor) {
                $this->reorganizeAsesorOrder($pengajuan->asesmen->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$userName} berhasil dihapus dari AK banding." .
                    ($isAsesor ? " Urutan asesor banding telah diatur ulang." : "")
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
                    $q->where('jenis_asesmen', 'ak_banding')
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
                        'created_at' => $item->created_at->locale('id')->translatedFormat('d M Y H:i'),
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
            $pengajuan = PengajuanAkreditasi::with('asesmen.asesmenKecukupanBanding')->findOrFail($id);

            if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenKecukupanBanding) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'requirements_met' => false,
                        'missing' => ['2 asesor banding', '1 validator'],
                        'current' => ['asesor_banding' => 0, 'validator' => 0],
                    ]
                ]);
            }

            $asesmenKecukupanBanding = $pengajuan->asesmen->asesmenKecukupanBanding;
            $asesorCount = $asesmenKecukupanBanding->asesors->count();
            $validatorCount = $asesmenKecukupanBanding->validators->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'requirements_met' => $asesmenKecukupanBanding->hasMinimumRequirementsWithCounts($asesorCount, $validatorCount),
                    'missing' => $asesmenKecukupanBanding->getMissingRequirementsWithCounts($asesorCount, $validatorCount),
                    'current' => [
                        'asesor_banding' => $asesorCount,
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
            PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI,
            PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
            PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
        ];

        $total = PengajuanAkreditasi::whereHas('statusLog', function ($q) use ($allStatuses) {
            $q->whereIn('status_to', $allStatuses);
        })->count();

        $belumDitugaskan = PengajuanAkreditasi::whereHas('statusLog', function ($q) {
            $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            ]);
        })
            ->whereDoesntHave('asesmen.asesmenKecukupanBanding.asesors')
            ->count();

        $sudahDitugaskan = PengajuanAkreditasi::whereHas('statusLog', function ($q) {
            $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
                PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
            ]);
        })
            ->whereHas('asesmen.asesmenKecukupanBanding')
            ->count();

        $selesai = PengajuanAkreditasi::whereHas('statusLog', function ($q) {
            $q->where('status_to', PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN);
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
        if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenKecukupanBanding) {
            return [
                'met' => false,
                'asesor_count' => 0,
                'validator_count' => 0,
                'missing' => ['2 asesor', '1 validator'],
            ];
        }

        $asesmenKecukupanBanding = $pengajuan->asesmen->asesmenKecukupanBanding;
        $asesorCount = $asesmenKecukupanBanding->asesors->count();
        $validatorCount = $asesmenKecukupanBanding->validators->count();

        return [
            'met' => $asesmenKecukupanBanding->hasMinimumRequirementsWithCounts($asesorCount, $validatorCount),
            'asesor_count' => $asesorCount,
            'validator_count' => $validatorCount,
            'missing' => $asesmenKecukupanBanding->getMissingRequirementsWithCounts($asesorCount, $validatorCount),
        ];
    }

    /**
     * Reorganize asesor order
     */
    private function reorganizeAsesorOrder($idAsesmen)
    {
        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'ak_banding')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
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

    /**
     * ✅ UPDATE: Upload surat tugas asesor AK (1 untuk semua asesor)
     */
    private function uploadSuratTugasAsesorAKBanding(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment, $file)
    {
        // Mark old surat tugas as not latest
        $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_ak_banding')
            ->update(['is_latest' => false]);

        $versi = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_ak_banding')
            ->max('versi') ?? 0;

        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename = time() . '_SURAT_TUGAS_ASESOR_AK_BANDING_' . $sanitizedName;

        $path = $file->storeAs('dokumen/surat-tugas-asesor-ak-banding', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_tugas_asesor_ak_banding',
            'nama_file' => $filename,
            'path_file' => $path,
            'original_filename' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'keterangan' => "Surat Tugas Asesor AK Banding untuk Pengajuan {$pengajuan->nomor_pengajuan}",
            'is_latest' => true,
            'versi' => $versi + 1,
        ]);
    }

    /**
     * ✅ UPDATE: Generate surat tugas asesor AK (1 untuk semua asesor)
     */
    private function generateSuratTugasAsesorAKBanding(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment)
    {
        // Mark old surat tugas as not latest
        $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_ak_banding')
            ->update(['is_latest' => false]);

        $versi = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_ak_banding')
            ->max('versi') ?? 0;

        $nomorSurat = 'ST-ASESOR-AK/' . date('Y') . '/' . str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT);

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_tugas_asesor_ak_banding',
            'nama_file' => "Surat_Tugas_Asesor_AK_BANDING_{$pengajuan->nomor_pengajuan}.pdf",
            'path_file' => null,
            'original_filename' => "Surat Tugas Asesor AK - {$pengajuan->nomor_pengajuan}.pdf",
            'file_size' => null,
            'mime_type' => 'application/pdf',
            'uploaded_by' => auth()->id(),
            'keterangan' => "Surat Tugas Nomor: {$nomorSurat} untuk Asesor AK Pengajuan {$pengajuan->nomor_pengajuan}",
            'template_link' => null,
            'is_latest' => true,
            'versi' => $versi + 1,
        ]);
    }

    /**
     * ✅ NEW: Upload surat tugas validator AK
     */
    private function uploadSuratTugasValidatorAK(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment, $file)
    {
        $versi = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_validator_ak_banding')
            ->max('versi') ?? 0;

        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename = time() . '_surat_tugas_validator_ak_banding_' . $sanitizedName;

        $path = $file->storeAs('dokumen/surat-tugas-validator-ak-banding', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_tugas_validator_ak_banding',
            'nama_file' => $filename,
            'path_file' => $path,
            'original_filename' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'keterangan' => "Surat Tugas Validator AK Banding: {$assignment->user->name}",
            'is_latest' => true,
            'versi' => $versi + 1,
        ]);
    }

    /**
     * ✅ NEW: Generate surat tugas validator AK
     */
    private function generateSuratTugasValidatorAK(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment)
    {
        $versi = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_validator_ak_banding')
            ->max('versi') ?? 0;

        $nomorSurat = 'ST-VALIDATOR-AK/' . date('Y') . '/' . str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT);

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_tugas_validator_ak_banding',
            'nama_file' => "surat_tugas_validator_ak_banding_{$pengajuan->nomor_pengajuan}.pdf",
            'path_file' => null,
            'original_filename' => "Surat Tugas Validator AK Banding - {$assignment->user->name}.pdf",
            'file_size' => null,
            'mime_type' => 'application/pdf',
            'uploaded_by' => auth()->id(),
            'keterangan' => "Surat Tugas Nomor: {$nomorSurat} untuk Validator AK Banding: {$assignment->user->name}",
            'template_link' => null,
            'is_latest' => true,
            'versi' => $versi + 1,
        ]);
    }

    /**
     * ✅ NEW: Copy surat tugas dari validator dokumen
     */
    private function copySuratTugasFromValidatorDokumen(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment)
    {
        // Get surat tugas validator dokumen
        $suratTugasValidatorDokumen = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_validator_dokumen')
            ->where('is_latest', true)
            ->first();

        if (!$suratTugasValidatorDokumen) {
            Log::warning('Surat tugas validator dokumen tidak ditemukan untuk copy', [
                'pengajuan_id' => $pengajuan->id,
                'assignment_id' => $assignment->id,
            ]);
            return false;
        }

        // Create duplicate with new jenis_dokumen
        $newVersi = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_validator_ak_banding')
            ->max('versi') ?? 0;

        $newDokumen = $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_tugas_validator_ak_banding',
            'nama_file' => $suratTugasValidatorDokumen->nama_file,
            'path_file' => $suratTugasValidatorDokumen->path_file, // Same file path
            'original_filename' => str_replace('Validator Dokumen', 'Validator AK Banding', $suratTugasValidatorDokumen->original_filename),
            'file_size' => $suratTugasValidatorDokumen->file_size,
            'mime_type' => $suratTugasValidatorDokumen->mime_type,
            'uploaded_by' => auth()->id(),
            'keterangan' => "Surat Tugas Validator AK Banding (Copy dari Validator Dokumen): {$assignment->user->name}",
            'template_link' => $suratTugasValidatorDokumen->template_link,
            'is_latest' => true,
            'versi' => $newVersi + 1,
        ]);

        return true;
    }

    /**
     * ✅ NEW: Download surat tugas (asesor/validator)
     */
    public function downloadSuratTugas($pengajuanId, $jenisDokumen)
    {
        $validJenis = ['surat_tugas_asesor_ak_banding', 'surat_tugas_validator_ak_banding'];

        if (!in_array($jenisDokumen, $validJenis)) {
            abort(400, 'Jenis dokumen tidak valid');
        }

        $pengajuan = PengajuanAkreditasi::findOrFail($pengajuanId);

        $dokumen = $pengajuan->dokumen()
            ->where('jenis_dokumen', $jenisDokumen)
            ->where('is_latest', true)
            ->firstOrFail();

        // Jika link
        if ($dokumen->template_link) {
            return redirect($dokumen->template_link);
        }

        // Jika file upload
        if ($dokumen->path_file && Storage::disk('public')->exists($dokumen->path_file)) {
            return Storage::disk('public')->download($dokumen->path_file, $dokumen->original_filename);
        }

        // Generate on-the-fly jika belum ada file
        if (!$dokumen->path_file) {
            return $this->generateAndDownloadSuratTugasAK($pengajuan, $dokumen, $jenisDokumen);
        }

        abort(404, 'File tidak ditemukan.');
    }

    /**
     * ✅ NEW: Generate and download surat tugas on-the-fly
     */
    private function generateAndDownloadSuratTugasAK(PengajuanAkreditasi $pengajuan, PengajuanDokumen $dokumen, $jenisDokumen)
    {
        // Get assignment info
        $assignment = null;
        if ($jenisDokumen === 'surat_tugas_asesor_ak_banding') {
            // Extract urutan from keterangan
            preg_match('/#(\d+)/', $dokumen->keterangan, $matches);
            $urutan = $matches[1] ?? 1;

            $assignment = $pengajuan->asesmen->asesmenUserRoles()
                ->where('jenis_asesmen', 'ak_banding')
                ->where('urutan_asesor', $urutan)
                ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
                ->with('user')
                ->first();
        } else {
            $assignment = $pengajuan->asesmen->asesmenUserRoles()
                ->where('jenis_asesmen', 'ak_banding')
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->with('user')
                ->first();
        }

        if (!$assignment) {
            abort(404, 'Data penugasan tidak ditemukan.');
        }

        // TODO: Implement actual PDF generation using DomPDF or TCPDF
        $nomorSurat = $jenisDokumen === 'surat_tugas_asesor_ak_banding'
            ? 'ST-ASESOR-AK/' . date('Y') . '/' . str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT) . '/' . $assignment->urutan_asesor
            : 'ST-VALIDATOR-AK/' . date('Y') . '/' . str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT);

        $tanggal = now()->locale('id')->translatedFormat('d M Y');
        $roleLabel = $jenisDokumen === 'surat_tugas_asesor_ak_banding' ? "Asesor AK #{$assignment->urutan_asesor}" : 'Validator AK Banding';

        $html = view('de.banding.penugasan-ak-banding.templates.surat-tugas', compact(
            'pengajuan',
            'assignment',
            'nomorSurat',
            'tanggal',
            'roleLabel'
        ))->render();

        // Generate PDF
        $pdf = \PDF::loadHTML($html);

        return $pdf->download("{$dokumen->original_filename}");
    }

    /**
     * ✅ NEW: Upload surat tugas (manual upload)
     */
    public function uploadSuratTugas(Request $request, $pengajuanId, $jenisDokumen)
    {
        $validJenis = ['surat_tugas_asesor_ak_banding', 'surat_tugas_validator_ak_banding'];

        if (!in_array($jenisDokumen, $validJenis)) {
            return redirect()->back()->with('error', 'Jenis dokumen tidak valid');
        }

        $request->validate([
            'file_surat_tugas' => 'required|file|mimes:pdf|max:5120',
        ], [
            'file_surat_tugas.required' => 'File surat tugas wajib diupload',
            'file_surat_tugas.mimes' => 'Format file harus PDF',
            'file_surat_tugas.max' => 'Ukuran file maksimal 5MB',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('asesmen.asesmenUserRoles')->findOrFail($pengajuanId);

            // Get first assignment for this role
            $assignment = null;
            if ($jenisDokumen === 'surat_tugas_asesor_ak_banding') {
                $assignment = $pengajuan->asesmen->asesmenUserRoles()
                    ->where('jenis_asesmen', 'ak_banding')
                    ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
                    ->orderBy('urutan_asesor')
                    ->firstOrFail();

                $this->uploadSuratTugasAsesorAKBanding($pengajuan, $assignment, $request->file('file_surat_tugas'));
            } else {
                $assignment = $pengajuan->asesmen->asesmenUserRoles()
                    ->where('jenis_asesmen', 'ak_banding')
                    ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                    ->firstOrFail();

                $this->uploadSuratTugasValidatorAK($pengajuan, $assignment, $request->file('file_surat_tugas'));
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Surat tugas berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload surat tugas AK Banding', [
                'pengajuan_id' => $pengajuanId,
                'jenis' => $jenisDokumen,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal upload surat tugas: ' . $e->getMessage());
        }
    }
}
