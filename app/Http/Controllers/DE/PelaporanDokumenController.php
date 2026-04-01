<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Mail\Reminder\ReminderPelaporanDokumenMail;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use App\Models\University;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelaporanDokumenController extends Controller
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
     * Dashboard monitoring Pelaporan Validasi Dokumen validasi
     */
    public function index(Request $request)
    {
        $statusTarget = [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
        ];

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',

            // kalau mau ditampilkan juga di table
            // kalau mau ambil log terkait pelaporan di table
            'statusLog' => function ($q) use ($statusTarget) {
                $q->whereIn('status_to', $statusTarget)
                    ->orderBy('changed_at', 'desc');
            },

            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->where('status_penawaran', 'accepted')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            },
            'asesmen.documents' => function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true)
                    ->latest();
            }
        ])
            // ✅ filter pakai status TERAKHIR dari PengajuanStatusLog
            ->whereExists(function ($q) use ($statusTarget) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $statusTarget);
            });

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status pelaporan (juga pakai latestStatusLog)
        if ($request->filled('status_pelaporan')) {
            switch ($request->status_pelaporan) {
                case 'belum_upload':
                    $query->whereHas('latestStatusLog', function ($q) {
                        $q->where('status_to', '!=', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN);
                    })
                        ->whereDoesntHave('asesmen.documents', function ($q) {
                            $q->where('type', 'laporan_validasi_borang')
                                ->where('is_active', true);
                        });
                    break;

                case 'sudah_upload':
                    $query->whereHas('latestStatusLog', function ($q) {
                        $q->where('status_to', '!=', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN);
                    })
                        ->whereHas('asesmen.documents', function ($q) {
                            $q->where('type', 'laporan_validasi_borang')
                                ->where('is_active', true);
                        });
                    break;

                case 'selesai':
                    $query->whereHas('latestStatusLog', function ($q) {
                        $q->where('status_to', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN);
                    });
                    break;
            }
        }

        // Search by prodi name or nomor permohonan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort (kalau sort_by bisa 'created_at' dari pengajuan; kalau mau sort by status log terakhir, lihat catatan di bawah)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        $stats = $this->calculateStatistics();
        $universities = University::nonExample()->orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.pelaporan-dokumen.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        $pendingReminderAssignments = AsesmenUserRole::with([
            'user',
            'asesmen.pengajuan.studyProgram',
        ])
            ->where('jenis_asesmen', 'dokumen')
            ->where('status_penawaran', 'accepted')
            ->whereHas('role_selected', fn($q) => $q->where('name', 'validator'))
            ->whereHas('asesmen.pengajuan.latestStatusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                ]);
            })
            ->whereDoesntHave('asesmen.documents', function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $countPendingReminderAssignments = $pendingReminderAssignments->count();

        return view('de.pelaporan-dokumen.index', compact('pengajuans', 'stats', 'universities', 'pendingReminderAssignments', 'countPendingReminderAssignments'));
    }

    /**
     * Detail Pelaporan Validasi Dokumen untuk satu pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->where('status_penawaran', 'accepted')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            },
            'asesmen.documents' => function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->orderBy('version', 'desc')
                    ->orderBy('created_at', 'desc');
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get validator assignment
        $validatorAssignment = $pengajuan->asesmen?->asesmenUserRoles
            ->where('jenis_asesmen', 'dokumen')
            ->where('status_penawaran', 'accepted')
            ->first();

        // Get latest laporan validasi
        $laporanValidasi = $pengajuan->asesmen?->documents
            ->where('type', 'laporan_validasi_borang')
            ->where('is_active', true)
            ->first();

        // Check status pelaporan
        $statusPelaporan = $this->getStatusPelaporan($pengajuan, $laporanValidasi);

        return view('de.pelaporan-dokumen.show', compact(
            'pengajuan',
            'validatorAssignment',
            'laporanValidasi',
            'statusPelaporan'
        ));
    }

    /**
     * Calculate statistics for dashboard
     */
    private function calculateStatistics(): array
    {
        $statusesScope = [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
        ];

        // Base: pernah mencapai salah satu status di atas (via riwayat)
        $baseQuery = PengajuanAkreditasi::query()
            ->whereHas('statusLog', function ($q) use ($statusesScope) {
                $q->whereIn('status_to', $statusesScope);
            });

        $total = (clone $baseQuery)->count();

        // Selesai: pernah mencapai VALIDASI_BORANG_DILAPORKAN (via riwayat)
        $selesai = PengajuanAkreditasi::query()
            ->whereHas('statusLog', function ($q) {
                $q->where('status_to', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN);
            })
            ->count();

        // Belum upload laporan:
        // pernah mencapai VALIDATED atau FINAL_DITERIMA (via riwayat) dan tidak ada dokumen aktif
        $belumUpload = PengajuanAkreditasi::query()
            ->whereHas('statusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                ]);
            })
            ->whereDoesntHave('asesmen.documents', function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true);
            })
            ->count();

        // Menunggu finalisasi:
        // pernah mencapai VALIDATED atau FINAL_DITERIMA (via riwayat) dan sudah ada dokumen aktif
        $menungguFinalisasi = PengajuanAkreditasi::query()
            ->whereHas('latestStatusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                ]);
            })
            ->whereHas('asesmen.documents', function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true);
            })
            ->count();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum_upload' => $belumUpload,
            'menunggu_finalisasi' => $menungguFinalisasi,
        ];
    }

    /**
     * Get status pelaporan untuk satu pengajuan
     */
    private function getStatusPelaporan($pengajuan, $laporanValidasi)
    {
        if ($pengajuan->status === PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN) {
            return [
                'status' => 'selesai',
                'label' => 'Selesai Dilaporkan',
                'class' => 'success',
                'icon' => 'check-circle-fill',
                'description' => 'Pelaporan validasi dokumen telah selesai dan difinalisasi'
            ];
        }

        if ($laporanValidasi) {
            return [
                'status' => 'menunggu_finalisasi',
                'label' => 'Menunggu Finalisasi',
                'class' => 'warning',
                'icon' => 'hourglass-split',
                'description' => 'Laporan validasi telah diupload, menunggu finalisasi dari validator'
            ];
        }

        return [
            'status' => 'belum_upload',
            'label' => 'Belum Upload Laporan',
            'class' => 'danger',
            'icon' => 'x-circle-fill',
            'description' => 'Validator belum mengupload laporan validasi dokumen'
        ];
    }

    /**
     * Get data for ajax table refresh
     */
    public function getTableAjax(Request $request)
    {
        return $this->index($request);
    }

    public function kirimReminder(Request $request)
    {
        $validated = $request->validate([
            'id_assignment' => 'required|array',
            'id_assignment.*' => 'exists:asesmen_user_roles,id',
            'pesan_reminder' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $assignments = AsesmenUserRole::with([
                'user',
                'asesmen.pengajuan',
                'asesmen.pengajuan.latestStatusLog',
            ])
                ->whereIn('id', $validated['id_assignment'])
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_penawaran', 'accepted')
                ->whereHas('role_selected', fn($q) => $q->where('name', 'validator'))
                ->whereHas('asesmen.pengajuan.latestStatusLog', function ($q) {
                    $q->whereIn('status_to', [
                        PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                        PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                    ]);
                })
                ->whereDoesntHave('asesmen.documents', function ($q) {
                    $q->where('type', 'laporan_validasi_borang')
                        ->where('is_active', true);
                })
                ->get();

            $sent = 0;

            foreach ($assignments as $assignment) {
                $pengajuan = $assignment->asesmen->pengajuan;

                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to' => $pengajuan->status,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => 'Pengingat pelaporan dokumen dikirim ke validator ' . $assignment->user->name,
                ]);

                $recipientEmails = $this->recipientResolver->emailsForUsers(
                    collect([$assignment->user])
                );

                $result = $this->mailDelivery->sendToEmails(
                    $recipientEmails,
                    new ReminderPelaporanDokumenMail($assignment, $validated['pesan_reminder']),
                    [],
                    [],
                    true
                );

                $sent++;
            }

            DB::commit();

            return back()->with('success', "Pengingat pelaporan berhasil dikirim ke {$sent} validator.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim pengingat pelaporan: ' . $e->getMessage());
        }
    }
}
