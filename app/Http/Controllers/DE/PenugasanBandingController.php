<?php

namespace App\Http\Controllers\DE;

use App\Models\Role;
use App\Models\User;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendPenawaranAsesmenEmail;
use Illuminate\Support\Facades\Storage;

class PenugasanBandingController extends Controller
{
    private const JENIS_ASESMEN      = 'banding';
    private const ROLE_ASESOR_BANDING = 'asesor_banding';

    // Banding hanya menggunakan asesor_banding — tidak ada role validator
    private function phaseStatuses(): array
    {
        return [
            PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
        ];
    }

    // ============================================================
    // INDEX
    // ============================================================

    public function index(Request $request)
    {
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenBanding',
            'asesmen.asesmenUserRoles' => fn($q) =>
            $q->where('jenis_asesmen', self::JENIS_ASESMEN)
                ->with(['user', 'role_selected']),
            'statusLog',
        ])
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $this->phaseStatuses()));

        if ($request->filled('university_id')) {
            $query->whereHas(
                'studyProgram',
                fn($q) =>
                $q->where('id_university', $request->university_id)
            );
        }

        if ($request->filled('status_banding')) {
            switch ($request->status_banding) {
                case 'belum_ditugaskan':
                    $query->whereHas(
                        'statusLog',
                        fn($q) =>
                        $q->where('status_to', PengajuanAkreditasi::STATUS_BANDING_DITERIMA)
                    )->whereDoesntHave('asesmen.asesmenBanding');
                    break;
                case 'sudah_ditugaskan':
                    $query->whereHas(
                        'statusLog',
                        fn($q) =>
                        $q->whereIn('status_to', [
                            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                        ])
                    )->whereHas('asesmen.asesmenBanding');
                    break;
                case 'selesai':
                    $query->whereHas(
                        'statusLog',
                        fn($q) =>
                        $q->where('status_to', PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN)
                    );
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas(
                        'studyProgram',
                        fn($sq) =>
                        $sq->where('name', 'like', "%{$search}%")
                    );
            });
        }

        $pengajuans   = $query->orderByDesc('updated_at')->paginate(20);
        $stats        = $this->calculateStatistics();
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.penugasan-banding.components.table-content', compact('pengajuans'))->render();
            return response()->json(['success' => true, 'html' => $html, 'total' => $pengajuans->total()]);
        }

        return view('de.penugasan-banding.index', compact('pengajuans', 'stats', 'universities'));
    }

    // ============================================================
    // SHOW
    // ============================================================

    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenBanding',
            'asesmen.asesmenUserRoles' => fn($q) =>
            $q->where('jenis_asesmen', self::JENIS_ASESMEN)
                ->with(['user', 'role_selected']),
            'dokumen',
            'statusLog' => fn($q) => $q->orderByDesc('changed_at')->with('changedBy'),
        ])->findOrFail($id);

        $requirementsStatus = $this->getRequirementsStatus($pengajuan);
        $availableUsers     = User::notAdmin()->orderBy('name')->get();

        // Hanya role asesor_banding — tidak ada validator untuk banding
        $roles = Role::where('name', self::ROLE_ASESOR_BANDING)->get();

        return view('de.penugasan-banding.show', compact(
            'pengajuan',
            'requirementsStatus',
            'availableUsers',
            'roles',
        ));
    }

    // ============================================================
    // MARK READY
    // ============================================================

    public function markReady(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'catatan'         => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DITERIMA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan harus berstatus banding_diterima.',
                ], 422);
            }

            if (!$pengajuan->pembayaranBanding || $pengajuan->pembayaranBanding->status_pembayaran !== 'terverifikasi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran banding belum terverifikasi.',
                ], 422);
            }

            // Ambil atau buat Asesmen
            if (!$pengajuan->asesmen) {
                $asesmen = Asesmen::create([
                    'name'             => 'Asesmen Banding — ' . $pengajuan->studyProgram->name,
                    'code'             => $pengajuan->nomor_pengajuan,
                    'id_program_studi' => $pengajuan->id_program_studi,
                    'id_pengajuan'     => $pengajuan->id,
                    'tanggal_mulai'    => $request->tanggal_mulai,
                    'tanggal_selesai'  => $request->tanggal_selesai,
                    'status'           => 'active',
                ]);
                $pengajuan->update(['id_asesmen' => $asesmen->id]);
            } else {
                $asesmen = $pengajuan->asesmen;
                $asesmen->update([
                    'tanggal_mulai'   => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                ]);
            }

            \App\Models\AsesmenBanding::firstOrCreate(
                ['id_asesmen' => $asesmen->id],
                [
                    'code'            => 'BANDING-' . $asesmen->code,
                    'tanggal_mulai'   => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'status'          => 'active',
                ]
            );

            $statusFrom = $pengajuan->status;
            $pengajuan->update([
                'status'                    => PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                'tanggal_penugasan_banding' => now(),
            ]);

            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
                'changed_by'  => Auth::id(),
                'keterangan'  => 'Ditetapkan siap untuk penugasan asesor banding. Periode: '
                    . $request->tanggal_mulai . ' s/d ' . $request->tanggal_selesai
                    . ($request->catatan ? '. Catatan: ' . $request->catatan : ''),
                'changed_at'  => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan banding berhasil ditetapkan. Silakan tugaskan asesor banding.',
                'data'    => ['tanggal_mulai' => $request->tanggal_mulai, 'tanggal_selesai' => $request->tanggal_selesai],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('markReady banding failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // ASSIGN USER (hanya asesor_banding, tidak ada validator)
    // ============================================================

    public function assignUser(Request $request, $id)
    {
        $request->validate([
            'id_user'          => 'required|exists:users,id',
            'id_role'          => 'required|exists:roles,id',
            'file_surat_tugas' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'asesmen.asesmenBanding',
                'asesmen.asesmenUserRoles',
                'dokumen',
            ])->findOrFail($id);

            if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenBanding) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ditetapkan. Klik "Tetapkan Siap Banding" terlebih dahulu.',
                ], 422);
            }

            $asesmen = $pengajuan->asesmen;
            $role    = Role::findOrFail($request->id_role);
            $user    = User::findOrFail($request->id_user);

            // Pastikan role yang ditugaskan adalah asesor_banding
            if ($role->name !== self::ROLE_ASESOR_BANDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak valid. Hanya asesor_banding yang dapat ditugaskan.',
                ], 422);
            }

            // Cek duplikat
            $exists = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                ->where('id_user', $user->id)
                ->where('id_role', $request->id_role)
                ->where('jenis_asesmen', self::JENIS_ASESMEN)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User sudah ditugaskan sebagai asesor banding.',
                ], 422);
            }

            // Urutan asesor banding
            $existingUrutans = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                ->where('jenis_asesmen', self::JENIS_ASESMEN)
                ->orderBy('urutan_asesor')
                ->pluck('urutan_asesor')
                ->toArray();

            $urutanAsesor = 1;
            foreach ($existingUrutans as $u) {
                if ($u !== $urutanAsesor) break;
                $urutanAsesor++;
            }

            $assignment = AsesmenUserRole::create([
                'id_asesmen'      => $asesmen->id,
                'id_user'         => $user->id,
                'id_role'         => $request->id_role,
                'jenis_asesmen'   => self::JENIS_ASESMEN,
                'urutan_asesor'   => $urutanAsesor,
                'status_penawaran' => 'pending',
            ]);

            // Satu surat tugas bersama untuk semua asesor banding (dibuat sekali)
            $suratTugasAda = $pengajuan->dokumen()
                ->where('jenis_dokumen', 'surat_tugas_asesor_banding')
                ->where('is_latest', true)
                ->exists();

            if (!$suratTugasAda) {
                if ($request->hasFile('file_surat_tugas')) {
                    $this->uploadSuratTugasBanding($pengajuan, $request->file('file_surat_tugas'));
                } else {
                    $this->generateSuratTugasBanding($pengajuan);
                }
            }

            // Kirim email penawaran
            try {
                SendPenawaranAsesmenEmail::dispatch($assignment);
            } catch (\Throwable $e) {
                Log::error('Gagal dispatch email penawaran banding', [
                    'assignment_id' => $assignment->id,
                    'error'         => $e->getMessage(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$user->name} berhasil ditugaskan sebagai Asesor Banding #{$urutanAsesor}. Email penawaran telah dikirim.",
                'data'    => [
                    'assignment'   => $assignment,
                    'urutan_asesor' => $urutanAsesor,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('assignUser banding failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal menugaskan: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // REMOVE USER
    // ============================================================

    public function removeUser(Request $request, $id, $userId)
    {
        DB::beginTransaction();
        try {
            $pengajuan  = PengajuanAkreditasi::with('asesmen')->findOrFail($id);
            $assignment = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('id_user', $userId)
                ->where('jenis_asesmen', self::JENIS_ASESMEN)
                ->firstOrFail();

            $userName = $assignment->user->name;
            $assignment->delete();
            $this->reorganizeAsesorOrder($pengajuan->asesmen->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$userName} berhasil dihapus. Urutan asesor diatur ulang.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // DOWNLOAD SURAT TUGAS
    // ============================================================

    public function downloadSuratTugas($pengajuanId, $jenisDokumen)
    {
        abort_unless($jenisDokumen === 'surat_tugas_asesor_banding', 400, 'Jenis dokumen tidak valid.');

        $pengajuan = PengajuanAkreditasi::findOrFail($pengajuanId);
        $dokumen   = $pengajuan->dokumen()
            ->where('jenis_dokumen', $jenisDokumen)
            ->where('is_latest', true)
            ->firstOrFail();

        if ($dokumen->template_link) return redirect($dokumen->template_link);

        if ($dokumen->path_file && Storage::disk('public')->exists($dokumen->path_file)) {
            return Storage::disk('public')->download($dokumen->path_file, $dokumen->original_filename);
        }

        abort(404, 'File tidak ditemukan.');
    }

    // ============================================================
    // UPLOAD SURAT TUGAS (manual / upload ulang)
    // ============================================================

    public function uploadSuratTugas(Request $request, $pengajuanId, $jenisDokumen)
    {
        abort_unless($jenisDokumen === 'surat_tugas_asesor_banding', 400, 'Jenis dokumen tidak valid.');
        $request->validate(['file_surat_tugas' => 'required|file|mimes:pdf|max:5120']);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($pengajuanId);
            $this->uploadSuratTugasBanding($pengajuan, $request->file('file_surat_tugas'));
            DB::commit();
            return back()->with('success', 'Surat tugas berhasil diupload.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    /**
     * Persyaratan banding: minimal 1 asesor_banding. Tidak ada validator.
     */
    private function getRequirementsStatus(PengajuanAkreditasi $pengajuan): array
    {
        if (!$pengajuan->asesmen || !$pengajuan->asesmen->asesmenBanding) {
            return ['met' => false, 'asesor_count' => 0, 'missing' => ['asesor banding']];
        }

        $assignments  = $pengajuan->asesmen->asesmenUserRoles->where('jenis_asesmen', self::JENIS_ASESMEN);
        $asesorCount  = $assignments->filter(
            fn($a) =>
            $a->role_selected && $a->role_selected->name === self::ROLE_ASESOR_BANDING
        )->count();

        $missing = [];
        if ($asesorCount < 1) $missing[] = 'asesor banding';

        return ['met' => empty($missing), 'asesor_count' => $asesorCount, 'missing' => $missing];
    }

    private function uploadSuratTugasBanding(PengajuanAkreditasi $pengajuan, $file): PengajuanDokumen
    {
        $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_banding')
            ->update(['is_latest' => false]);

        $versi        = ($pengajuan->dokumen()->where('jenis_dokumen', 'surat_tugas_asesor_banding')->max('versi') ?? 0) + 1;
        $originalName = $file->getClientOriginalName();
        $sanitized    = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename     = time() . '_surat_tugas_asesor_banding_' . $sanitized;
        $path         = $file->storeAs('dokumen/surat-tugas-banding', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen'     => 'surat_tugas_asesor_banding',
            'nama_file'         => $filename,
            'path_file'         => $path,
            'original_filename' => $originalName,
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
            'uploaded_by'       => Auth::id(),
            'keterangan'        => "Surat Tugas Asesor Banding — {$pengajuan->nomor_pengajuan}",
            'is_latest'         => true,
            'versi'             => $versi,
        ]);
    }

    private function generateSuratTugasBanding(PengajuanAkreditasi $pengajuan): PengajuanDokumen
    {
        $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_tugas_asesor_banding')
            ->update(['is_latest' => false]);

        $versi = ($pengajuan->dokumen()->where('jenis_dokumen', 'surat_tugas_asesor_banding')->max('versi') ?? 0) + 1;
        $nomor = 'ST-BANDING/' . date('Y') . '/' . str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT);

        return $pengajuan->dokumen()->create([
            'jenis_dokumen'     => 'surat_tugas_asesor_banding',
            'nama_file'         => "surat_tugas_asesor_banding_{$pengajuan->nomor_pengajuan}.pdf",
            'path_file'         => null,
            'original_filename' => "Surat Tugas Banding — {$pengajuan->nomor_pengajuan}.pdf",
            'file_size'         => null,
            'mime_type'         => 'application/pdf',
            'uploaded_by'       => Auth::id(),
            'keterangan'        => "Nomor: {$nomor}",
            'is_latest'         => true,
            'versi'             => $versi,
        ]);
    }

    private function reorganizeAsesorOrder(int $idAsesmen): void
    {
        $asesors   = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', self::JENIS_ASESMEN)
            ->orderBy('urutan_asesor')
            ->get();

        $newUrutan = 1;
        foreach ($asesors as $a) {
            if ($a->urutan_asesor !== $newUrutan) $a->update(['urutan_asesor' => $newUrutan]);
            $newUrutan++;
        }
    }

    private function calculateStatistics(): array
    {
        $total = PengajuanAkreditasi::whereHas(
            'statusLog',
            fn($q) =>
            $q->whereIn('status_to', $this->phaseStatuses())
        )->count();

        $belumDitugaskan = PengajuanAkreditasi::whereHas(
            'statusLog',
            fn($q) =>
            $q->where('status_to', PengajuanAkreditasi::STATUS_BANDING_DITERIMA)
        )->whereDoesntHave('asesmen.asesmenBanding')->count();

        $sudahDitugaskan = PengajuanAkreditasi::whereHas(
            'statusLog',
            fn($q) =>
            $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            ])
        )->whereHas('asesmen.asesmenBanding')->count();

        $selesai = PengajuanAkreditasi::whereHas(
            'statusLog',
            fn($q) =>
            $q->where('status_to', PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN)
        )->count();

        return [
            'total'            => $total,
            'belum_ditugaskan' => $belumDitugaskan,
            'sudah_ditugaskan' => $sudahDitugaskan,
            'selesai'          => $selesai,
        ];
    }
}
