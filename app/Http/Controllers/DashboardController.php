<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Data untuk statistik utama
        $stats = [
            'penawaran' => 0,
            'penugasan_aktif' => 0,
            'progress' => 0,
            'proses_ak' => 0,
            'deadline_days' => 0,
            'total_selesai' => 0,
            'persentase_kenaikan' => 0,
        ];

        // Data untuk statistik tambahan
        $additionalStats = [
            'total_prodi' => 801,
            'akurasi' => '100',
            'waktu_rata' => '5.2',
            'peningkatan' => 15,
            'unread_messages' => 3,
        ];

        // Data untuk aktivitas terkini
        $recentActivities = [
            (object)[
                'type' => 'primary',
                'icon' => '📝',
                'title' => 'Hasil AK Berhasil Diunggah',
                'description' => 'Anda telah mengunggah hasil penilaian AK untuk Program Studi S2 Ilmu Lingkungan - Universitas Diponegoro',
                'time' => '2 jam yang lalu',
            ],
            (object)[
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => 'Terdeteksi Split Nilai',
                'description' => 'Terdapat 3 deskriptor dengan perbedaan penilaian. Silakan lakukan rekonsiliasi dengan partner asesor.',
                'time' => '5 jam yang lalu',
            ],
            (object)[
                'type' => 'success',
                'icon' => '✉️',
                'title' => 'Penawaran Asesmen Diterima',
                'description' => 'Anda telah menerima penawaran asesmen untuk S1 Teknik Informatika - Universitas Bina Nusantara',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'primary',
                'icon' => '💬',
                'title' => 'Pesan Baru dari Partner',
                'description' => 'Dr. Paulus mengirim pesan terkait rekonsiliasi nilai pada butir F1|81|8.3.1',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'success',
                'icon' => '📊',
                'title' => 'Validasi AK Selesai',
                'description' => 'Hasil AK untuk S1 Manajemen - Universitas Pelita Harapan telah divalidasi oleh Dewan Eksekutif',
                'time' => '2 hari yang lalu',
            ],
        ];

        // Data untuk tugas mendatang
        $upcomingTasks = [
            (object)[
                'title' => 'Upload Hasil Penilaian AK',
                'priority' => 'high',
                'description' => 'S2 Ilmu Lingkungan - Universitas Diponegoro',
                'deadline' => '10 November 2025',
                'days_left' => 12,
            ],
            (object)[
                'title' => 'Rekonsiliasi Split Nilai',
                'priority' => 'high',
                'description' => '3 deskriptor perlu didiskusikan dengan Dr. Paulus',
                'deadline' => '11 Jun 2025',
                'days_left' => 4,
            ],
            (object)[
                'title' => 'Visitasi AL',
                'priority' => 'medium',
                'description' => 'Asesmen Lapangan - S2 Ilmu Lingkungan Universitas Diponegoro',
                'deadline' => '14-20 Jun 2025',
                'days_left' => 12,
            ],
            (object)[
                'title' => 'Tanggapi Penawaran Baru',
                'priority' => 'low',
                'description' => '2 penawaran asesmen menunggu respon',
                'deadline' => '20 Jun 2025',
                'days_left' => 18,
            ],
        ];

        // Data untuk badge di sidebar
        $penawaranBaru = 2;
        $penugasanAktif = 1;
        $prosesAK = 1;
        $notificationCount = 5;

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
     * Get notification count
     *
     * @return int
     */
    private function getNotificationCount()
    {
        // Implementasi untuk mengambil jumlah notifikasi dari database
        // return Notification::where('user_id', Auth::id())->unread()->count();
        return 5;
    }

    /**
     * Get recent activities from database
     *
     * @return \Illuminate\Support\Collection
     */
    private function getRecentActivities()
    {
        // Implementasi untuk mengambil aktivitas terkini dari database
        // return Activity::where('user_id', Auth::id())
        //     ->orderBy('created_at', 'desc')
        //     ->limit(5)
        //     ->get();

        return collect([]);
    }

    /**
     * Get upcoming tasks from database
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUpcomingTasks()
    {
        // Implementasi untuk mengambil tugas mendatang dari database
        // return Task::where('user_id', Auth::id())
        //     ->where('status', 'pending')
        //     ->orderBy('deadline', 'asc')
        //     ->limit(4)
        //     ->get();

        return collect([]);
    }
}
