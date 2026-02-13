<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Jobs\SendPenawaranAsesmenEmail;
use App\Models\Asesmen;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PenugasanALController extends Controller
{
    /**
     * Dashboard penugasan AL
     */
    public function index(Request $request)
    {
        $statusList = [
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,        // AK selesai, siap AL
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,   // Asesor AL sudah ditugaskan
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,       // AL sedang berlangsung
            PengajuanAkreditasi::STATUS_AL_SELESAI,           // AL selesai
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,        // AL dilaporkan
        ];

        // Build query - pengajuan yang siap untuk AL
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenLapangan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'asesor'))
                    ->with(['user', 'role_selected']);
            },
            // opsional: untuk tampilkan status terakhir
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
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'surat_tugas_asesor_al' // ✅ Load surat tugas
            ])->orderBy('created_at', 'desc'),
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
    /**
     * ✅ Tugaskan asesor untuk AL dengan surat tugas
     */
    public function assignAsesor(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi_visitasi' => 'required|string|max:500',
            'file_surat_tugas' => 'nullable|file|mimes:pdf|max:5120', // ✅ Optional surat tugas
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
                    'message' => 'Asesor telah ditugaskan untuk AL ini'
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

            // ✅ Handle Surat Tugas AL (1 untuk semua asesor)
            $suratTugasCreated = false;

            // Cek apakah sudah ada surat tugas asesor AL
            $existingSuratTugas = $pengajuan->dokumen()
                ->where('jenis_dokumen', 'surat_tugas_asesor_al')
                ->where('is_latest', true)
                ->first();

            if (!$existingSuratTugas) {
                // Buat surat tugas baru (hanya 1x untuk asesor pertama)
                if ($request->hasFile('file_surat_tugas')) {
                    $this->uploadSuratTugasAsesorAL($pengajuan, $assignment, $request->file('file_surat_tugas'));
                } else {
                    $this->generateSuratTugasAsesorAL($pengajuan, $assignment);
                }
                $suratTugasCreated = true;
            }

            // Update status pengajuan
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
                'message' => "Asesor {$user->name} berhasil ditugaskan untuk AL (Asesor {$urutanAsesor}). Email penawaran telah dikirim." .
                    ($suratTugasCreated ? " Surat tugas telah dibuat." : ""),
                'data' => [
                    'assignment' => $assignment,
                    'user' => $user,
                    'urutan_asesor' => $urutanAsesor,
                    'surat_tugas_created' => $suratTugasCreated,
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
                    'message' => 'Asesor tidak bisa dihapus karena telah melakukan penilaian.'
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

    /**
     * ✅ Upload surat tugas asesor AL (1 untuk semua asesor)
     */
    private function uploadSuratTugasAsesorAL(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment, $file)
    {
        // Mark old surat tugas as not latest
        $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_al')
            ->update(['is_latest' => false]);

        $versi = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_al')
            ->max('versi') ?? 0;

        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename = time() . '_SURAT_TUGAS_ASESOR_AL_' . $sanitizedName;

        $path = $file->storeAs('dokumen/surat-tugas-asesor-al', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_tugas_asesor_al',
            'nama_file' => $filename,
            'path_file' => $path,
            'original_filename' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'keterangan' => "Surat Tugas Asesor AL untuk Pengajuan {$pengajuan->nomor_pengajuan}",
            'is_latest' => true,
            'versi' => $versi + 1,
        ]);
    }

    /**
     * ✅ Generate surat tugas asesor AL (placeholder)
     */
    private function generateSuratTugasAsesorAL(PengajuanAkreditasi $pengajuan, AsesmenUserRole $assignment)
    {
        // Mark old surat tugas as not latest
        $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_al')
            ->update(['is_latest' => false]);

        $versi = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_al')
            ->max('versi') ?? 0;

        $nomorSurat = 'ST-ASESOR-AL/' . date('Y') . '/' . str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT);

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_tugas_asesor_al',
            'nama_file' => "Surat_Tugas_Asesor_AL_{$pengajuan->nomor_pengajuan}.pdf",
            'path_file' => null,
            'original_filename' => "Surat Tugas Asesor AL - {$pengajuan->nomor_pengajuan}.pdf",
            'file_size' => null,
            'mime_type' => 'application/pdf',
            'uploaded_by' => auth()->id(),
            'keterangan' => "Surat Tugas Nomor: {$nomorSurat} untuk Asesor AL Pengajuan {$pengajuan->nomor_pengajuan}",
            'template_link' => null,
            'is_latest' => true,
            'versi' => $versi + 1,
        ]);
    }

    /**
     * ✅ Download surat tugas AL
     */
    public function downloadSuratTugas($pengajuanId, $jenisDokumen)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($pengajuanId);

            $suratTugas = $pengajuan->dokumen()
                ->where('jenis_dokumen', $jenisDokumen)
                ->where('is_latest', true)
                ->firstOrFail();

            // If has path_file, download it
            if ($suratTugas->path_file && Storage::disk('public')->exists($suratTugas->path_file)) {
                return Storage::disk('public')->download(
                    $suratTugas->path_file,
                    $suratTugas->original_filename
                );
            }

            // If has template_link, redirect
            if ($suratTugas->template_link) {
                return redirect($suratTugas->template_link);
            }

            // Otherwise generate on-the-fly (TODO: implement PDF generation)
            return $this->generateAndDownloadSuratTugasAL($pengajuan, $jenisDokumen);
        } catch (\Exception $e) {
            Log::error('Download surat tugas AL failed', [
                'pengajuan_id' => $pengajuanId,
                'jenis' => $jenisDokumen,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal mengunduh surat tugas: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Upload surat tugas manual (via modal)
     */
    public function uploadSuratTugas(Request $request, $pengajuanId, $jenisDokumen)
    {
        $validJenis = ['surat_tugas_asesor_al'];

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

            // Get first assignment for asesor AL
            $assignment = $pengajuan->asesmen->asesmenUserRoles()
                ->where('jenis_asesmen', 'al')
                ->whereHas('role_selected', fn($q) => $q->where('name', 'asesor'))
                ->orderBy('urutan_asesor')
                ->firstOrFail();

            $this->uploadSuratTugasAsesorAL($pengajuan, $assignment, $request->file('file_surat_tugas'));

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Surat tugas berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload surat tugas AL', [
                'pengajuan_id' => $pengajuanId,
                'jenis' => $jenisDokumen,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal upload surat tugas: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Generate and download surat tugas on-the-fly (TODO)
     */
    private function generateAndDownloadSuratTugasAL($pengajuan, $jenisDokumen)
    {
        // TODO: Implement PDF generation using DomPDF/TCPDF
        // For now, return error
        abort(404, 'File surat tugas tidak tersedia. Silakan upload manual.');
    }
}
