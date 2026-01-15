<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            default:
                return $this->dashboardDefault();
        }
    }

    private function dashboardAsesi()
    {
        // Data untuk DE (Asesi) - fokus pada pengajuan dan status akreditasi
        $stats = [
            'penawaran' => 0,
            'penugasan_aktif' => 0,
            'progress' => 0,
            'proses_ak' => 1,
            'deadline_days' => 45,
            'total_selesai' => 3,
            'persentase_kenaikan' => 50,
        ];

        $additionalStats = [
            'total_prodi' => 5,
            'akurasi' => '100',
            'waktu_rata' => '30',
            'peningkatan' => 25,
            'unread_messages' => 2,
        ];

        $recentActivities = [
            (object)[
                'type' => 'success',
                'icon' => '✅',
                'title' => 'Dokumen Akreditasi Disetujui',
                'description' => 'Berkas akreditasi Program Studi S1 Teknik Informatika telah diverifikasi dan disetujui',
                'time' => '3 jam yang lalu',
            ],
            (object)[
                'type' => 'primary',
                'icon' => '📄',
                'title' => 'Dokumen Diunggah',
                'description' => 'Anda telah mengunggah dokumen pendukung untuk kriteria 5 - Sumber Daya Manusia',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => 'Dokumen Perlu Dilengkapi',
                'description' => 'Masih ada 2 dokumen wajib yang belum diunggah untuk kriteria 3',
                'time' => '2 hari yang lalu',
            ],
            (object)[
                'type' => 'info',
                'icon' => '📊',
                'title' => 'Jadwal Visitasi Ditentukan',
                'description' => 'Asesmen Lapangan dijadwalkan pada tanggal 20-22 Februari 2026',
                'time' => '3 hari yang lalu',
            ],
            (object)[
                'type' => 'success',
                'icon' => '🎯',
                'title' => 'Pengajuan Akreditasi Diterima',
                'description' => 'Pengajuan akreditasi Program Studi S1 Teknik Informatika telah diterima dan diproses',
                'time' => '5 hari yang lalu',
            ],
        ];

        $upcomingTasks = [
            (object)[
                'title' => 'Lengkapi Dokumen Kriteria 3',
                'priority' => 'high',
                'description' => '2 dokumen wajib masih belum diunggah',
                'deadline' => '20 Februari 2026',
                'days_left' => 35,
            ],
            (object)[
                'title' => 'Siapkan Visitasi Lapangan',
                'priority' => 'medium',
                'description' => 'Persiapan ruangan, dokumen fisik, dan jadwal asesmen lapangan',
                'deadline' => '18 Februari 2026',
                'days_left' => 33,
            ],
            (object)[
                'title' => 'Validasi Data PDDIKTI',
                'priority' => 'medium',
                'description' => 'Pastikan data di PDDIKTI sesuai dengan dokumen akreditasi',
                'deadline' => '28 Februari 2026',
                'days_left' => 43,
            ],
        ];

        $penawaranBaru = 0;
        $penugasanAktif = 0;
        $prosesAK = 1;
        $notificationCount = 4;

        return view('user.dashboard', compact(
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

    private function dashboardAsesor()
    {
        // Data untuk Asesor - fokus pada penugasan penilaian
        $stats = [
            'penawaran' => 3,
            'penugasan_aktif' => 2,
            'progress' => 70,
            'proses_ak' => 2,
            'deadline_days' => 8,
            'total_selesai' => 47,
            'persentase_kenaikan' => 18,
        ];

        $additionalStats = [
            'total_prodi' => 156,
            'akurasi' => '98.5',
            'waktu_rata' => '5.2',
            'peningkatan' => 15,
            'unread_messages' => 5,
        ];

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

        $upcomingTasks = [
            (object)[
                'title' => 'Upload Hasil Penilaian AK',
                'priority' => 'high',
                'description' => 'S2 Ilmu Lingkungan - Universitas Diponegoro',
                'deadline' => '25 Januari 2026',
                'days_left' => 10,
            ],
            (object)[
                'title' => 'Rekonsiliasi Split Nilai',
                'priority' => 'high',
                'description' => '3 deskriptor perlu didiskusikan dengan Dr. Paulus',
                'deadline' => '23 Januari 2026',
                'days_left' => 8,
            ],
            (object)[
                'title' => 'Visitasi AL',
                'priority' => 'medium',
                'description' => 'Asesmen Lapangan - S1 Teknik Informatika Universitas Bina Nusantara',
                'deadline' => '28-30 Januari 2026',
                'days_left' => 13,
            ],
            (object)[
                'title' => 'Tanggapi Penawaran Baru',
                'priority' => 'low',
                'description' => '3 penawaran asesmen menunggu respon',
                'deadline' => '5 Februari 2026',
                'days_left' => 21,
            ],
        ];

        $penawaranBaru = 3;
        $penugasanAktif = 2;
        $prosesAK = 2;
        $notificationCount = 8;

        return view('user.dashboard', compact(
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

    private function dashboardValidator()
    {
        // Data untuk Validator - fokus pada validasi hasil asesmen
        $stats = [
            'penawaran' => 0,
            'penugasan_aktif' => 4,
            'progress' => 85,
            'proses_ak' => 4,
            'deadline_days' => 5,
            'total_selesai' => 128,
            'persentase_kenaikan' => 22,
        ];

        $additionalStats = [
            'total_prodi' => 342,
            'akurasi' => '99.2',
            'waktu_rata' => '2.8',
            'peningkatan' => 20,
            'unread_messages' => 7,
        ];

        $recentActivities = [
            (object)[
                'type' => 'success',
                'icon' => '✅',
                'title' => 'Validasi AK Disetujui',
                'description' => 'Hasil validasi AK untuk S1 Manajemen - Universitas Pelita Harapan telah disetujui',
                'time' => '1 jam yang lalu',
            ],
            (object)[
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => 'Hasil AK Perlu Revisi',
                'description' => 'Ditemukan inkonsistensi pada kriteria 4 untuk S2 Ilmu Lingkungan - Universitas Diponegoro',
                'time' => '4 jam yang lalu',
            ],
            (object)[
                'type' => 'primary',
                'icon' => '📋',
                'title' => 'Dokumen Validasi Baru',
                'description' => 'Anda menerima 2 dokumen hasil AK yang perlu divalidasi',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'info',
                'icon' => '💬',
                'title' => 'Diskusi dengan Asesor',
                'description' => 'Permintaan klarifikasi nilai dari asesor untuk butir 5.2.1',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'success',
                'icon' => '📊',
                'title' => 'Laporan Validasi Dikirim',
                'description' => 'Laporan validasi untuk 3 program studi telah dikirim ke verifikator',
                'time' => '2 hari yang lalu',
            ],
        ];

        $upcomingTasks = [
            (object)[
                'title' => 'Validasi Hasil AK',
                'priority' => 'high',
                'description' => 'S2 Ilmu Lingkungan - Universitas Diponegoro (perlu revisi)',
                'deadline' => '20 Januari 2026',
                'days_left' => 5,
            ],
            (object)[
                'title' => 'Review Dokumen Asesmen',
                'priority' => 'high',
                'description' => '2 dokumen AK menunggu validasi pertama',
                'deadline' => '22 Januari 2026',
                'days_left' => 7,
            ],
            (object)[
                'title' => 'Koordinasi dengan Asesor',
                'priority' => 'medium',
                'description' => 'Klarifikasi 5 butir penilaian yang memerlukan penjelasan',
                'deadline' => '25 Januari 2026',
                'days_left' => 10,
            ],
            (object)[
                'title' => 'Finalisasi Laporan Bulanan',
                'priority' => 'medium',
                'description' => 'Laporan validasi bulan Januari 2026',
                'deadline' => '31 Januari 2026',
                'days_left' => 16,
            ],
        ];

        $penawaranBaru = 0;
        $penugasanAktif = 4;
        $prosesAK = 4;
        $notificationCount = 11;

        return view('user.dashboard', compact(
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

    private function dashboardVerifikator()
    {
        // Data untuk Verifikator - fokus pada verifikasi final
        $stats = [
            'penawaran' => 0,
            'penugasan_aktif' => 5,
            'progress' => 90,
            'proses_ak' => 5,
            'deadline_days' => 3,
            'total_selesai' => 215,
            'persentase_kenaikan' => 28,
        ];

        $additionalStats = [
            'total_prodi' => 548,
            'akurasi' => '99.7',
            'waktu_rata' => '1.5',
            'peningkatan' => 25,
            'unread_messages' => 4,
        ];

        $recentActivities = [
            (object)[
                'type' => 'success',
                'icon' => '🎯',
                'title' => 'Verifikasi Final Selesai',
                'description' => 'Keputusan akreditasi S1 Manajemen - Universitas Pelita Harapan telah diverifikasi dan disetujui',
                'time' => '2 jam yang lalu',
            ],
            (object)[
                'type' => 'primary',
                'icon' => '📑',
                'title' => 'Dokumen Validasi Diterima',
                'description' => 'Hasil validasi dari 3 program studi siap untuk verifikasi final',
                'time' => '5 jam yang lalu',
            ],
            (object)[
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => 'Revisi Diperlukan',
                'description' => 'Dokumen S2 Ilmu Lingkungan perlu perbaikan sebelum keputusan final',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'info',
                'icon' => '📊',
                'title' => 'Rapat Pleno Terjadwal',
                'description' => 'Sidang pleno untuk 8 program studi pada 25 Januari 2026',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'success',
                'icon' => '✅',
                'title' => 'Sertifikat Diterbitkan',
                'description' => '5 sertifikat akreditasi telah diterbitkan dan dikirim',
                'time' => '2 hari yang lalu',
            ],
        ];

        $upcomingTasks = [
            (object)[
                'title' => 'Verifikasi Final 3 Prodi',
                'priority' => 'high',
                'description' => 'Review dan approval keputusan akreditasi final',
                'deadline' => '18 Januari 2026',
                'days_left' => 3,
            ],
            (object)[
                'title' => 'Persiapan Sidang Pleno',
                'priority' => 'high',
                'description' => 'Menyiapkan materi untuk 8 program studi',
                'deadline' => '24 Januari 2026',
                'days_left' => 9,
            ],
            (object)[
                'title' => 'Review Dokumen Banding',
                'priority' => 'medium',
                'description' => '1 pengajuan banding perlu ditinjau',
                'deadline' => '28 Januari 2026',
                'days_left' => 13,
            ],
            (object)[
                'title' => 'Koordinasi dengan Dewan',
                'priority' => 'medium',
                'description' => 'Rapat koordinasi hasil verifikasi bulan Januari',
                'deadline' => '30 Januari 2026',
                'days_left' => 15,
            ],
        ];

        $penawaranBaru = 0;
        $penugasanAktif = 5;
        $prosesAK = 5;
        $notificationCount = 9;

        return view('user.dashboard', compact(
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

    private function dashboardAdminUniv()
    {
        // Data untuk Admin Universitas
        $stats = [
            'penawaran' => 0,
            'penugasan_aktif' => 0,
            'progress' => 0,
            'proses_ak' => 3,
            'deadline_days' => 30,
            'total_selesai' => 12,
            'persentase_kenaikan' => 33,
        ];

        $additionalStats = [
            'total_prodi' => 45,
            'akurasi' => '100',
            'waktu_rata' => '25',
            'peningkatan' => 30,
            'unread_messages' => 3,
        ];

        $recentActivities = [
            (object)[
                'type' => 'success',
                'icon' => '✅',
                'title' => 'Dokumen Disetujui',
                'description' => 'Dokumen akreditasi 2 program studi telah disetujui tim verifikasi',
                'time' => '3 jam yang lalu',
            ],
            (object)[
                'type' => 'primary',
                'icon' => '📄',
                'title' => 'Pengajuan Baru',
                'description' => 'Program Studi S1 Sistem Informasi mengajukan akreditasi',
                'time' => '1 hari yang lalu',
            ],
            (object)[
                'type' => 'info',
                'icon' => '📊',
                'title' => 'Jadwal Visitasi',
                'description' => 'Visitasi untuk 3 prodi dijadwalkan bulan Februari 2026',
                'time' => '2 hari yang lalu',
            ],
        ];

        $upcomingTasks = [
            (object)[
                'title' => 'Monitoring Proses Akreditasi',
                'priority' => 'medium',
                'description' => '3 program studi dalam proses asesmen',
                'deadline' => '28 Februari 2026',
                'days_left' => 44,
            ],
            (object)[
                'title' => 'Persiapan Visitasi',
                'priority' => 'high',
                'description' => 'Koordinasi dengan prodi untuk persiapan visitasi',
                'deadline' => '15 Februari 2026',
                'days_left' => 31,
            ],
        ];

        $penawaranBaru = 0;
        $penugasanAktif = 0;
        $prosesAK = 3;
        $notificationCount = 5;

        return view('user.dashboard', compact(
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

    private function dashboardAdminProdi()
    {
        // Data untuk Admin Prodi (sama dengan asesi)
        return $this->dashboardAsesi();
    }

    private function dashboardDefault()
    {
        // Data default untuk role lain
        $stats = [
            'penawaran' => 2,
            'penugasan_aktif' => 1,
            'progress' => 65,
            'proses_ak' => 1,
            'deadline_days' => 12,
            'total_selesai' => 24,
            'persentase_kenaikan' => 12,
        ];

        $additionalStats = [
            'total_prodi' => 156,
            'akurasi' => '98.5',
            'waktu_rata' => '5.2',
            'peningkatan' => 15,
            'unread_messages' => 3,
        ];

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
        ];

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
        ];

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
}
