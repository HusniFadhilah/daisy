<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Asesmen;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenKecukupan;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanPembayaran;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role_selected;

        // Tentukan data berdasarkan role
        switch ($role) {
            case 'asesi':
            case 'super_admin':
                return $this->dashboardAsesi();
            case 'asesor':
                return $this->dashboardAsesor();
            case 'validator':
                return $this->dashboardValidator();
            case 'verifikator':
                return $this->dashboardVerifikator();
            case 'admin_univ':
                return $this->dashboardAdminUniv();
            case 'admin_prodi':
                return $this->dashboardAdminProdi();
            case 'keuangan_lamdepilar':
                return $this->dashboardKeuangan();
            default:
                return $this->dashboardDefault();
        }
    }

    /**
     * ✅ Dashboard DE (Asesi) dan Super Admin
     */
    private function dashboardAsesi()
    {
        $now = Carbon::now();
        $sevenMonthsFromNow = $now->copy()->addMonths(7);
        $startOfYear = $now->copy()->startOfYear();

        // 1. Pengingat Masa Akreditasi (masa berakhir dalam 7 bulan)
        $pengingatMasaAkreditasi = StudyProgram::where('is_active', true)
            ->whereNotNull('tanggal_kedaluwarsa')
            ->where('tanggal_kedaluwarsa', '<=', $sevenMonthsFromNow)
            ->where('tanggal_kedaluwarsa', '>', $now)
            ->count();

        // 2. ✅ Penerimaan Dokumen Akreditasi (dari status log - pernah di status ini)
        $penerimaanDokumen = PengajuanStatusLog::whereIn('status_to', [
            PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
        ])
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        // Alternative: Jika ingin lebih detail, ambil unique pengajuan
        // $penerimaanDokumen = PengajuanStatusLog::whereIn('status_to', [
        //     PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
        //     PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
        // ])->select('id_pengajuan')->distinct()->get()->count();

        // 3. Penawaran Menunggu (current status = pending)
        $penawaranMenunggu = AsesmenUserRole::where('status_penawaran', 'pending')
            ->count();

        // 4. Penugasan Aktif (current status = accepted & in progress)
        $penugasanAktif = AsesmenUserRole::where('status_penawaran', 'accepted')
            ->whereIn('status_pekerjaan', ['not_started', 'in_progress', 'submitted'])
            ->count();

        // 5. ✅ Proses AK Berlangsung (dari status log tahun ini - pernah/sedang di status AK)
        $prosesAK = PengajuanStatusLog::whereIn('status_to', [
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
        ])
            ->whereYear('changed_at', $now->year)
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        // 6. ✅ Proses AL Berlangsung (dari status log tahun ini - pernah/sedang di status AL)
        $prosesAL = PengajuanStatusLog::whereIn('status_to', [
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_SELESAI,
        ])
            ->whereYear('changed_at', $now->year)
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        // 7. ✅ Total Selesai Tahun Ini (dari status log - pernah mencapai status selesai tahun ini)
        $totalSelesai = PengajuanStatusLog::whereIn('status_to', [
            PengajuanAkreditasi::STATUS_SELESAI,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
        ])
            ->whereYear('changed_at', $now->year)
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        // 8. ✅ Validasi Borang (pernah di status validasi borang)
        $validasiBorang = PengajuanStatusLog::whereIn('status_to', [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
        ])
            ->whereYear('changed_at', $now->year)
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        $stats = [
            'penawaran' => $pengingatMasaAkreditasi,
            'penerimaan_dokumen' => $penerimaanDokumen,
            'penawaran_menunggu' => $penawaranMenunggu,
            'penugasan_aktif' => $penugasanAktif,
            'proses_ak' => $prosesAK,
            'proses_al' => $prosesAL,
            'total_selesai' => $totalSelesai,
            'validasi_borang' => $validasiBorang,
        ];

        $additionalStats = [
            'total_prodi' => StudyProgram::where('is_active', true)->count(),
            'total_pengajuan' => PengajuanAkreditasi::count(),
            'pengajuan_aktif' => PengajuanAkreditasi::whereNotIn('status', [
                PengajuanAkreditasi::STATUS_SELESAI,
                PengajuanAkreditasi::STATUS_DITOLAK
            ])->count(),
        ];

        // Recent Activities (ambil dari status log)
        $recentActivities = $this->getRecentActivitiesDE();

        // Upcoming Tasks
        $upcomingTasks = $this->getUpcomingTasksDE();

        $penawaranBaru = $penawaranMenunggu;
        $notificationCount = $penawaranMenunggu + $penugasanAktif;

        return view('admin.dashboard', compact(
            'stats',
            'additionalStats',
            'recentActivities',
            'upcomingTasks',
            'penawaranBaru',
            'penugasanAktif',
            'notificationCount'
        ));
    }

    /**
     * ✅ Dashboard Asesor
     */
    private function dashboardAsesor()
    {
        $user = Auth::user();

        // 1. Penawaran Menunggu untuk user ini
        $penawaranMenunggu = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_penawaran', 'pending')
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->count();

        // 2. Penugasan Aktif (accepted dan in progress)
        $penugasanAktif = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereIn('status_pekerjaan', ['not_started', 'in_progress', 'submitted'])
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->count();

        // 3. Penugasan Selesai (approved)
        $penugasanSelesai = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_pekerjaan', 'approved')
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->count();

        $stats = [
            'penawaran' => $penawaranMenunggu,
            'penugasan_aktif' => $penugasanAktif,
            'penugasan_selesai' => $penugasanSelesai,
        ];

        $additionalStats = [
            'total_assignment' => AsesmenUserRole::where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                ->count(),
        ];

        $recentActivities = $this->getRecentActivitiesAsesor($user->id);
        $upcomingTasks = $this->getUpcomingTasksAsesor($user->id);

        $penawaranBaru = $penawaranMenunggu;
        $prosesAK = $penugasanAktif;
        $notificationCount = $penawaranMenunggu;

        return view('admin.dashboard', compact(
            'stats',
            'additionalStats',
            'recentActivities',
            'upcomingTasks',
            'penawaranBaru',
            'penugasanAktif',
            'prosesAK',
            'notificationCount'
        ));
    }

    /**
     * ✅ Dashboard Validator
     */
    private function dashboardValidator()
    {
        $user = Auth::user();

        // 1. Penawaran Menunggu
        $penawaranMenunggu = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_penawaran', 'pending')
            ->whereHas('role', function ($q) {
                $q->where('name', 'validator');
            })
            ->count();

        // 2. Penugasan Aktif
        $penugasanAktif = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereIn('status_pekerjaan', ['not_started', 'in_progress', 'submitted'])
            ->whereHas('role', function ($q) {
                $q->where('name', 'validator');
            })
            ->count();

        // 3. Penugasan Selesai
        $penugasanSelesai = AsesmenUserRole::where('id_user', $user->id)
            ->where('status_pekerjaan', 'approved')
            ->whereHas('role', function ($q) {
                $q->where('name', 'validator');
            })
            ->count();

        $stats = [
            'penawaran' => $penawaranMenunggu,
            'penugasan_aktif' => $penugasanAktif,
            'penugasan_selesai' => $penugasanSelesai,
        ];

        $additionalStats = [
            'total_assignment' => AsesmenUserRole::where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->count(),
        ];

        $recentActivities = $this->getRecentActivitiesValidator($user->id);
        $upcomingTasks = $this->getUpcomingTasksValidator($user->id);

        $penawaranBaru = $penawaranMenunggu;
        $prosesAK = $penugasanAktif;
        $notificationCount = $penawaranMenunggu;

        return view('admin.dashboard', compact(
            'stats',
            'additionalStats',
            'recentActivities',
            'upcomingTasks',
            'penawaranBaru',
            'penugasanAktif',
            'prosesAK',
            'notificationCount'
        ));
    }

    /**
     * ✅ Dashboard Verifikator
     */
    private function dashboardVerifikator()
    {
        return $this->dashboardValidator(); // Same logic as validator
    }

    /**
     * ✅ Dashboard Admin Universitas
     */
    private function dashboardAdminUniv()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Get study programs under this university
        $studyProgramIds = StudyProgram::where('id_university', $user->id_university)
            ->pluck('id')
            ->toArray();

        // 1. ✅ Permohonan Berjalan (pernah diajukan tahun ini, belum selesai)
        $permohonanBerjalan = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotIn('status', [
                PengajuanAkreditasi::STATUS_SELESAI,
                PengajuanAkreditasi::STATUS_DITOLAK
            ])
            ->whereYear('created_at', $now->year)
            ->count();

        // 2. ✅ Permohonan Selesai (dari status log - pernah mencapai status selesai)
        $permohonanSelesai = PengajuanStatusLog::where('status_to', PengajuanAkreditasi::STATUS_SELESAI)
            ->whereIn('id_pengajuan', function ($query) use ($studyProgramIds) {
                $query->select('id')
                    ->from('pengajuan_akreditasi')
                    ->whereIn('id_program_studi', $studyProgramIds);
            })
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        $stats = [
            'permohonan_berjalan' => $permohonanBerjalan,
            'permohonan_selesai' => $permohonanSelesai,
        ];

        $additionalStats = [
            'total_prodi' => count($studyProgramIds),
            'total_pengajuan' => PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)->count(),
        ];

        $recentActivities = $this->getRecentActivitiesAdminUniv($studyProgramIds);
        $upcomingTasks = $this->getUpcomingTasksAdminUniv($studyProgramIds);

        $penawaranBaru = 0;
        $penugasanAktif = $permohonanBerjalan;
        $prosesAK = $permohonanBerjalan;
        $notificationCount = 0;

        return view('admin.dashboard', compact(
            'stats',
            'additionalStats',
            'recentActivities',
            'upcomingTasks',
            'penawaranBaru',
            'penugasanAktif',
            'prosesAK',
            'notificationCount'
        ));
    }

    /**
     * ✅ Dashboard Admin Prodi
     */
    private function dashboardAdminProdi()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Get study programs for this admin
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();

        if (empty($studyProgramIds)) {
            // Fallback jika belum ada study program
            $stats = [
                'permohonan_berjalan' => 0,
                'permohonan_selesai' => 0,
            ];

            $additionalStats = [
                'total_prodi' => 0,
                'total_pengajuan' => 0,
            ];

            $recentActivities = [];
            $upcomingTasks = [];

            return view('admin.dashboard', compact(
                'stats',
                'additionalStats',
                'recentActivities',
                'upcomingTasks'
            ));
        }

        // 1. Permohonan Berjalan
        $permohonanBerjalan = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotIn('status', [
                PengajuanAkreditasi::STATUS_SELESAI,
                PengajuanAkreditasi::STATUS_DITOLAK
            ])
            ->count();

        // 2. ✅ Permohonan Selesai (dari status log)
        $permohonanSelesai = PengajuanStatusLog::where('status_to', PengajuanAkreditasi::STATUS_SELESAI)
            ->whereIn('id_pengajuan', function ($query) use ($studyProgramIds) {
                $query->select('id')
                    ->from('pengajuan_akreditasi')
                    ->whereIn('id_program_studi', $studyProgramIds);
            })
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        $stats = [
            'permohonan_berjalan' => $permohonanBerjalan,
            'permohonan_selesai' => $permohonanSelesai,
        ];

        $additionalStats = [
            'total_prodi' => count($studyProgramIds),
            'total_pengajuan' => PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)->count(),
        ];

        $recentActivities = $this->getRecentActivitiesAdminProdi($studyProgramIds);
        $upcomingTasks = $this->getUpcomingTasksAdminProdi($studyProgramIds);

        $penawaranBaru = 0;
        $penugasanAktif = $permohonanBerjalan;
        $prosesAK = $permohonanBerjalan;
        $notificationCount = 0;

        return view('admin.dashboard', compact(
            'stats',
            'additionalStats',
            'recentActivities',
            'upcomingTasks',
            'penawaranBaru',
            'penugasanAktif',
            'prosesAK',
            'notificationCount'
        ));
    }

    /**
     * ✅ Dashboard Keuangan
     */
    private function dashboardKeuangan()
    {
        // 1. Pembayaran Perlu Diverifikasi (current status)
        $perluDiverifikasi = PengajuanPembayaran::where('status_pembayaran', 'menunggu_verifikasi')
            ->count();

        // 2. ✅ Total Selesai Diverifikasi (dari history - pernah terverifikasi)
        $selesaiDiverifikasi = PengajuanPembayaran::where('status_pembayaran', 'terverifikasi')
            ->count();

        $stats = [
            'perlu_diverifikasi' => $perluDiverifikasi,
            'selesai_diverifikasi' => $selesaiDiverifikasi,
        ];

        $additionalStats = [
            'total_pembayaran' => PengajuanPembayaran::count(),
            'pending_upload' => PengajuanPembayaran::where('status_pembayaran', 'menunggu_pembayaran')->count(),
            'ditolak' => PengajuanPembayaran::where('status_pembayaran', 'ditolak')->count(),
        ];

        $recentActivities = $this->getRecentActivitiesKeuangan();
        $upcomingTasks = $this->getUpcomingTasksKeuangan();

        $penawaranBaru = 0;
        $penugasanAktif = $perluDiverifikasi;
        $prosesAK = 0;
        $notificationCount = $perluDiverifikasi;

        return view('admin.dashboard', compact(
            'stats',
            'additionalStats',
            'recentActivities',
            'upcomingTasks',
            'penawaranBaru',
            'penugasanAktif',
            'prosesAK',
            'notificationCount'
        ));
    }

    /**
     * ✅ Dashboard Default
     */
    private function dashboardDefault()
    {
        $stats = [
            'penawaran' => 0,
            'penugasan_aktif' => 0,
            'penugasan_selesai' => 0,
        ];

        $additionalStats = [
            'total_prodi' => StudyProgram::count(),
        ];

        $recentActivities = [];
        $upcomingTasks = [];

        $penawaranBaru = 0;
        $penugasanAktif = 0;
        $prosesAK = 0;
        $notificationCount = 0;

        return view('admin.dashboard', compact(
            'stats',
            'additionalStats',
            'recentActivities',
            'upcomingTasks',
            'penawaranBaru',
            'penugasanAktif',
            'prosesAK',
            'notificationCount'
        ));
    }

    // ========================================
    // Helper Methods - Recent Activities
    // ========================================

    private function getRecentActivitiesDE()
    {
        $activities = [];

        // ✅ Get from status log (latest changes)
        $recentLogs = PengajuanStatusLog::with(['pengajuan.studyProgram', 'user'])
            ->latest('changed_at')
            ->take(10)
            ->get();

        foreach ($recentLogs as $log) {
            if (!$log->pengajuan) continue;

            $statusMap = PengajuanAkreditasi::statusMap();
            $statusInfo = $statusMap[$log->status_to] ?? ['label' => $log->status_to, 'icon' => 'bi-info-circle'];

            $activities[] = (object)[
                'type' => 'primary',
                'icon' => $statusInfo['icon'] ?? '📝',
                'title' => $statusInfo['label'],
                'description' => ($log->pengajuan->studyProgram->name ?? '-') .
                    ($log->user ? ' oleh ' . $log->user->name : ''),
                'time' => Carbon::parse($log->changed_at)->diffForHumans(),
            ];
        }

        return $activities;
    }

    private function getRecentActivitiesAsesor($userId)
    {
        $activities = [];

        $assignments = AsesmenUserRole::where('id_user', $userId)
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->with(['asesmen.studyProgram'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($assignments as $assignment) {
            $activities[] = (object)[
                'type' => 'primary',
                'icon' => '📝',
                'title' => $assignment->status_label,
                'description' => ($assignment->asesmen->studyProgram->name ?? '-') .
                    ' - ' . strtoupper($assignment->jenis_asesmen),
                'time' => $assignment->updated_at->diffForHumans(),
            ];
        }

        return $activities;
    }

    private function getRecentActivitiesValidator($userId)
    {
        return $this->getRecentActivitiesAsesor($userId);
    }

    private function getRecentActivitiesAdminUniv($studyProgramIds)
    {
        $activities = [];

        // ✅ Get from status log for university programs
        $recentLogs = PengajuanStatusLog::whereIn('id_pengajuan', function ($query) use ($studyProgramIds) {
                $query->select('id')
                    ->from('pengajuan_akreditasi')
                    ->whereIn('id_program_studi', $studyProgramIds);
            })
            ->with(['pengajuan.studyProgram'])
            ->latest('changed_at')
            ->take(5)
            ->get();

        foreach ($recentLogs as $log) {
            if (!$log->pengajuan) continue;

            $statusMap = PengajuanAkreditasi::statusMap();
            $statusInfo = $statusMap[$log->status_to] ?? ['label' => $log->status_to];

            $activities[] = (object)[
                'type' => 'info',
                'icon' => '📊',
                'title' => $statusInfo['label'],
                'description' => $log->pengajuan->studyProgram->name ?? '-',
                'time' => Carbon::parse($log->changed_at)->diffForHumans(),
            ];
        }

        return $activities;
    }

    private function getRecentActivitiesAdminProdi($studyProgramIds)
    {
        return $this->getRecentActivitiesAdminUniv($studyProgramIds);
    }

    private function getRecentActivitiesKeuangan()
    {
        $activities = [];

        $recentPayments = PengajuanPembayaran::with(['pengajuan.studyProgram'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($recentPayments as $payment) {
            $statusLabel = match($payment->status_pembayaran) {
                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                'terverifikasi' => 'Terverifikasi',
                'ditolak' => 'Ditolak',
                default => ucwords(str_replace('_', ' ', $payment->status_pembayaran)),
            };

            $activities[] = (object)[
                'type' => $payment->status_pembayaran === 'terverifikasi' ? 'success' : 'info',
                'icon' => '💰',
                'title' => $statusLabel,
                'description' => $payment->pengajuan->studyProgram->name ?? '-',
                'time' => $payment->updated_at->diffForHumans(),
            ];
        }

        return $activities;
    }

    // ========================================
    // Helper Methods - Upcoming Tasks
    // ========================================

    private function getUpcomingTasksDE()
    {
        $tasks = [];

        // Pending validations
        $pendingValidations = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING)
            ->with('studyProgram')
            ->take(3)
            ->get();

        foreach ($pendingValidations as $pengajuan) {
            $tasks[] = (object)[
                'title' => 'Validasi Dokumen',
                'priority' => 'high',
                'description' => $pengajuan->studyProgram->name ?? '-',
                'deadline' => $pengajuan->updated_at->addDays(7)->format('d M Y'),
                'days_left' => max(0, $pengajuan->updated_at->addDays(7)->diffInDays(now(), false)),
            ];
        }

        return $tasks;
    }

    private function getUpcomingTasksAsesor($userId)
    {
        $tasks = [];

        $assignments = AsesmenUserRole::where('id_user', $userId)
            ->where('status_penawaran', 'accepted')
            ->whereIn('status_pekerjaan', ['not_started', 'in_progress'])
            ->with(['asesmen.studyProgram'])
            ->take(3)
            ->get();

        foreach ($assignments as $assignment) {
            $tasks[] = (object)[
                'title' => 'Penilaian ' . strtoupper($assignment->jenis_asesmen),
                'priority' => 'high',
                'description' => $assignment->asesmen->studyProgram->name ?? '-',
                'deadline' => $assignment->updated_at->addDays(14)->format('d M Y'),
                'days_left' => max(0, $assignment->updated_at->addDays(14)->diffInDays(now(), false)),
            ];
        }

        return $tasks;
    }

    private function getUpcomingTasksValidator($userId)
    {
        return $this->getUpcomingTasksAsesor($userId);
    }

    private function getUpcomingTasksAdminUniv($studyProgramIds)
    {
        $tasks = [];

        $pengajuans = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotIn('status', [PengajuanAkreditasi::STATUS_SELESAI, PengajuanAkreditasi::STATUS_DITOLAK])
            ->with('studyProgram')
            ->take(3)
            ->get();

        foreach ($pengajuans as $pengajuan) {
            $tasks[] = (object)[
                'title' => 'Monitor Proses Akreditasi',
                'priority' => 'medium',
                'description' => $pengajuan->studyProgram->name ?? '-',
                'deadline' => $pengajuan->updated_at->addMonths(2)->format('d M Y'),
                'days_left' => max(0, $pengajuan->updated_at->addMonths(2)->diffInDays(now(), false)),
            ];
        }

        return $tasks;
    }

    private function getUpcomingTasksAdminProdi($studyProgramIds)
    {
        return $this->getUpcomingTasksAdminUniv($studyProgramIds);
    }

    private function getUpcomingTasksKeuangan()
    {
        $tasks = [];

        $pendingPayments = PengajuanPembayaran::where('status_pembayaran', 'menunggu_verifikasi')
            ->with(['pengajuan.studyProgram'])
            ->take(3)
            ->get();

        foreach ($pendingPayments as $payment) {
            $deadline = $payment->tanggal_pembayaran ?
                Carbon::parse($payment->tanggal_pembayaran)->addDays(3) :
                now()->addDays(3);

            $tasks[] = (object)[
                'title' => 'Verifikasi Pembayaran',
                'priority' => 'high',
                'description' => $payment->pengajuan->studyProgram->name ?? '-',
                'deadline' => $deadline->format('d M Y'),
                'days_left' => max(0, $deadline->diffInDays(now(), false)),
            ];
        }

        return $tasks;
    }
}
