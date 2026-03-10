<?php

namespace App\Domain\Akreditasi;

use App\Models\PengajuanAkreditasi;

final class PengajuanStatus
{
    public static function map(): array
    {
        return [
            PengajuanAkreditasi::STATUS_DRAFT => [
                'label' => 'Draft Permohonan Akreditasi',
                'label_short_for' => [
                    'de'   => 'Draft Belum Terkirim',
                    'upps' => 'Draft Siap Dikirim',
                    'prodi' => 'Draft Siap Dikirim',
                ],
                'label_long_for' => [
                    'de'   => 'Draft Permohonan Akreditasi Belum Terkirim',
                    'upps' => 'Draft Permohonan Akreditasi Dibuat',
                    'prodi' => 'Draft Permohonan Akreditasi Dibuat',
                ],
                'bg' => 'bg-secondary',
                'icon' => 'bi-pencil',
            ],

            PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM => [
                'label' => 'Pengingat Masa Akreditasi',
                'label_short_for' => [
                    'de'   => 'Pengingat Terkirim',
                    'upps' => 'Pengingat Diterima',
                    'prodi' => 'Pengingat Diterima',
                ],
                'label_long_for' => [
                    'de'   => 'Pengingat Masa Akreditasi Telah Dikirim',
                    'upps' => 'Pengingat Masa Akreditasi Telah Diterima dari LAMDEPILAR',
                    'prodi' => 'Pengingat Masa Akreditasi Telah Diterima dari LAMDEPILAR',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-bell',
            ],

            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM => [
                'label' => 'Permohonan Akreditasi',
                'label_short_for' => [
                    'de'   => 'Permohonan Dikirim',
                    'upps' => 'Permohonan Terkirim',
                    'prodi' => 'Permohonan Terkirim',
                ],
                'label_long_for' => [
                    'de'   => 'Permohonan Akreditasi Dikirim oleh PS',
                    'upps' => 'Permohonan Akreditasi Telah Dikirim',
                    'prodi' => 'Permohonan Akreditasi Telah Dikirim',
                ],
                'bg' => 'bg-primary',
                'icon' => 'bi-envelope',
            ],

            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK => [
                'label' => 'Permohonan Akreditasi Ditolak',
                'label_short_for' => [
                    'de'   => 'Permohonan Ditolak',
                    'upps' => 'Permohonan Ditolak',
                    'prodi' => 'Permohonan Ditolak',
                ],
                'label_long_for' => [
                    'de'   => 'Permohonan Akreditasi Ditolak',
                    'upps' => 'Permohonan Akreditasi Ditolak oleh LAMDEPILAR',
                    'prodi' => 'Permohonan Akreditasi Ditolak oleh LAMDEPILAR',
                ],
                'bg' => 'bg-danger',
                'icon' => 'bi-envelope',
            ],

            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_UPLOAD_ULANG => [
                'label' => 'Pengiriman Ulang Permohonan Akreditasi',
                'label_short_for' => [
                    'de'   => 'Proses Pengiriman Ulang',
                    'upps' => 'Proses Pengiriman Ulang',
                    'prodi' => 'Proses Pengiriman Ulang',
                ],
                'label_long_for' => [
                    'de'   => 'Permohonan Akreditasi Dikirim Ulang',
                    'upps' => 'Pengiriman Ulang Permohonan Akreditasi',
                    'prodi' => 'Pengiriman Ulang Permohonan Akreditasi',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-envelope',
            ],

            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA => [
                'label' => 'Permohonan Akreditasi Diterima',
                'label_short_for' => [
                    'de'   => 'Telah Ditanggapi',
                    'upps' => 'Telah Ditanggapi',
                    'prodi' => 'Telah Ditanggapi',
                ],
                'label_long_for' => [
                    'de'   => 'Permohonan Akreditasi Telah Ditanggapi',
                    'upps' => 'Permohonan Akreditasi Telah Ditanggapi',
                    'prodi' => 'Permohonan Akreditasi Telah Ditanggapi',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-envelope',
            ],

            PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM => [
                'label' => 'Penerimaan Permohonan Akreditasi Dikirim',
                'label_short_for' => [
                    'de'   => 'Penerimaan Terkirim',
                    'upps' => 'Permohonan Diterima',
                    'prodi' => 'Permohonan Diterima',
                ],
                'label_long_for' => [
                    'de'   => 'Penerimaan Permohonan Akreditasi Dikirim',
                    'upps' => 'Permohonan Akreditasi Diterima',
                    'prodi' => 'Permohonan Akreditasi Diterima',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-envelope',
            ],

            PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM => [
                'label' => 'Pengiriman Formulir dan Templat Dokumen',
                'label_short_for' => [
                    'de'   => 'Formulir & Templat Terkirim',
                    'upps' => 'Formulir & Templat Diterima',
                    'prodi' => 'Formulir & Templat Diterima',
                ],
                'label_long_for' => [
                    'de'   => 'Formulir & Templat Dokumen Telah Dikirim',
                    'upps' => 'Formulir & Templat Dokumen Diterima',
                    'prodi' => 'Formulir & Templat Dokumen Diterima',
                ],
                'bg' => 'bg-primary',
                'icon' => 'bi-file-earmark-arrow-down',
            ],

            PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN => [
                'label' => 'Menunggu Pembayaran',
                'label_short_for' => [
                    'de'   => 'Menunggu Pembayaran',
                    'upps' => 'Menunggu Pembayaran',
                    'prodi' => 'Menunggu Pembayaran',
                ],
                'label_long_for' => [
                    'de'   => 'Menunggu Pembayaran dari PS',
                    'upps' => 'Menunggu Pembayaran',
                    'prodi' => 'Menunggu Pembayaran',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-hourglass-split',
            ],

            PengajuanAkreditasi::STATUS_PEMBAYARAN_DITERIMA => [
                'label' => 'Pembayaran Diterima',
                'label_short_for' => [
                    'de'   => 'Bukti Pembayaran Masuk',
                    'upps' => 'Bukti Pembayaran Terkirim',
                    'prodi' => 'Bukti Pembayaran Terkirim',
                ],
                'label_long_for' => [
                    'de'   => 'Bukti Pembayaran Diterima',
                    'upps' => 'Bukti Pembayaran Terkirim',
                    'prodi' => 'Bukti Pembayaran Terkirim',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-credit-card',
            ],

            PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN => [
                'label' => 'Menunggu Validasi Pembayaran',
                'label_short_for' => [
                    'de'   => 'Menunggu Validasi',
                    'upps' => 'Menunggu Validasi',
                    'prodi' => 'Menunggu Validasi',
                ],
                'label_long_for' => [
                    'de'   => 'Menunggu Validasi Pembayaran',
                    'upps' => 'Menunggu Validasi Pembayaran',
                    'prodi' => 'Menunggu Validasi Pembayaran',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-shield-exclamation',
            ],

            PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI => [
                'label' => 'Validasi Pembayaran Selesai',
                'label_short_for' => [
                    'de'   => 'Pembayaran Tervalidasi',
                    'upps' => 'Pembayaran Tervalidasi',
                    'prodi' => 'Pembayaran Tervalidasi',
                ],
                'label_long_for' => [
                    'de'   => 'Pembayaran Tervalidasi',
                    'upps' => 'Pembayaran Tervalidasi',
                    'prodi' => 'Pembayaran Tervalidasi',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle',
            ],

            PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM => [
                'label' => 'Dokumen Dikirim',
                'label_short_for' => [
                    'de'   => 'Dokumen Dikirim',
                    'upps' => 'Dokumen Terkirim',
                    'prodi' => 'Dokumen Terkirim',
                ],
                'label_long_for' => [
                    'de'   => 'Dokumen Telah Dikirim',
                    'upps' => 'Dokumen Telah Dikirim',
                    'prodi' => 'Dokumen Telah Dikirim',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-check',
            ],

            PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA => [
                'label' => 'Dokumen Diterima',
                'label_short_for' => [
                    'de'   => 'Dokumen Diterima',
                    'upps' => 'Dokumen Diterima',
                    'prodi' => 'Dokumen Diterima',
                ],
                'label_long_for' => [
                    'de'   => 'Dokumen Telah Diterima',
                    'upps' => 'Dokumen Telah Diterima',
                    'prodi' => 'Dokumen Telah Diterima',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-check',
            ],

            PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI => [
                'label' => 'Dokumen Diterima',
                'label_short_for' => [
                    'de'   => 'Dokumen Masuk',
                    'upps' => 'Dokumen Diterima',
                    'prodi' => 'Dokumen Diterima',
                ],
                'label_long_for' => [
                    'de'   => 'Dokumen Online Diterima',
                    'upps' => 'Dokumen Online Menunggu Validasi',
                    'prodi' => 'Dokumen Online Menunggu Validasi',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-ui-checks',
            ],

            PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING => [
                'label' => 'Menunggu Validasi Dokumen',
                'label_short_for' => [
                    'de'   => 'Menunggu Validasi',
                    'upps' => 'Menunggu Validasi',
                    'prodi' => 'Menunggu Validasi',
                    'validator' => 'Menunggu Validasi',
                ],
                'label_long_for' => [
                    'de'   => 'Menunggu Validasi Dokumen',
                    'upps' => 'Menunggu Validasi Dokumen',
                    'prodi' => 'Menunggu Validasi Dokumen',
                    'validator' => 'Menunggu Validasi Dokumen',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-clock-history',
            ],

            PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION => [
                'label' => 'Validasi Dokumen Berlangsung',
                'label_short_for' => [
                    'de'   => 'Validasi Berlangsung',
                    'upps' => 'Validasi Berlangsung',
                    'prodi' => 'Validasi Berlangsung',
                    'validator' => 'Validasi Berlangsung',
                ],
                'label_long_for' => [
                    'de'   => 'Validasi Dokumen Sedang Berlangsung',
                    'upps' => 'Validasi Dokumen Sedang Berlangsung',
                    'prodi' => 'Validasi Dokumen Sedang Berlangsung',
                    'validator' => 'Validasi Dokumen Sedang Berlangsung',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-clipboard-check',
            ],

            PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED => [
                'label' => 'Dokumen Perlu Revisi',
                'label_short_for' => [
                    'de'   => 'Perlu Revisi',
                    'upps' => 'Perlu Revisi',
                    'prodi' => 'Perlu Revisi',
                    'validator' => 'Permintaan Revisi',
                ],
                'label_long_for' => [
                    'de'   => 'Permintaan Revisi Dokumen',
                    'upps' => 'Dokumen Perlu Revisi',
                    'prodi' => 'Dokumen Perlu Revisi',
                    'validator' => 'Permintaan Revisi Dokumen',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-exclamation-triangle',
            ],

            PengajuanAkreditasi::STATUS_BORANG_VALIDATED => [
                'label' => 'Dokumen Divalidasi',
                'label_short_for' => [
                    'de'   => 'Tervalidasi',
                    'upps' => 'Tervalidasi',
                    'prodi' => 'Tervalidasi',
                    'validator' => 'Tervalidasi',
                ],
                'label_long_for' => [
                    'de'   => 'Dokumen Tervalidasi',
                    'upps' => 'Dokumen Tervalidasi',
                    'prodi' => 'Dokumen Tervalidasi',
                    'validator' => 'Dokumen Tervalidasi',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle-fill',
            ],

            PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA => [
                'label' => 'Draft Final Dokumen Diterima',
                'label_short_for' => [
                    'de'   => 'Final Diterima',
                    'upps' => 'Final Diterima LAMDEPILAR',
                    'prodi' => 'Final Diterima LAMDEPILAR',
                ],
                'label_long_for' => [
                    'de'   => 'Draft Final Dokumen Diterima',
                    'upps' => 'Draft Final Dokumen Diterima oleh LAMDEPILAR',
                    'prodi' => 'Draft Final Dokumen Diterima oleh LAMDEPILAR',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-arrow-up',
            ],

            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN => [
                'label' => 'Pelaporan Validasi Dokumen',
                'label_short_for' => [
                    'de'   => 'Telah Dikirimkan',
                    'upps' => 'Telah Dikirim',
                    'prodi' => 'Telah Dikirim',
                    'validator' => 'Telah Dikirim',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaporan Validasi Dokumen Telah Dikirim',
                    'upps' => 'Pelaporan Validasi Dokumen Telah Dikirim',
                    'prodi' => 'Pelaporan Validasi Dokumen Telah Dikirim',
                    'validator' => 'Pelaporan Validasi Dokumen Telah Dikirim',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-text',
            ],

            PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED => [
                'label' => 'Proses Penugasan Asesor AK',
                'label_short_for' => [
                    'de'   => 'Siap Penugasan AK',
                    'upps' => 'Penugasan AK',
                    'prodi' => 'Penugasan AK',
                ],
                'label_long_for' => [
                    'de'   => 'Siap Masuk Proses Penugasan Asesor AK',
                    'upps' => 'Dalam Proses Penugasan Asesor AK',
                    'prodi' => 'Dalam Proses Penugasan Asesor AK',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-text',
            ],

            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED => [
                'label' => 'Penugasan Asesor AK',
                'label_short_for' => [
                    'de'   => 'Asesor AK Ditugaskan',
                    'upps' => 'Asesor AK Ditugaskan',
                    'prodi' => 'Asesor AK Ditugaskan',
                ],
                'label_long_for' => [
                    'de'   => 'Asesor AK Telah Ditugaskan',
                    'upps' => 'Asesor AK Telah Ditugaskan',
                    'prodi' => 'Asesor AK Telah Ditugaskan',
                ],
                'bg' => 'bg-primary',
                'icon' => 'bi-person-check',
            ],

            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS => [
                'label' => 'Penugasan Asesor AK Berlangsung',
                'label_short_for' => [
                    'de'   => 'Sedang Berlangsung',
                    'upps' => 'Sedang Berlangsung',
                    'prodi' => 'Sedang Berlangsung',
                ],
                'label_long_for' => [
                    'de'   => 'Proses AK Berlangsung',
                    'upps' => 'Proses AK Berlangsung',
                    'prodi' => 'Proses AK Berlangsung',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-clipboard-data',
            ],

            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION => [
                'label' => 'Validasi AK Berlangsung',
                'label_short_for' => [
                    'de'   => 'Validasi AK',
                    'upps' => 'Validasi AK',
                    'prodi' => 'Validasi AK',
                ],
                'label_long_for' => [
                    'de'   => 'Validasi AK Sedang Berlangsung',
                    'upps' => 'Validasi AK Sedang Berlangsung',
                    'prodi' => 'Validasi AK Sedang Berlangsung',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-clipboard2-check',
            ],

            PengajuanAkreditasi::STATUS_AK_SELESAI => [
                'label' => 'Validasi AK Selesai',
                'label_short_for' => [
                    'de'   => 'Validasi AK Selesai',
                    'upps' => 'Validasi AK Selesai',
                    'prodi' => 'Validasi AK Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Validasi AK Selesai',
                    'upps' => 'Validasi AK Selesai',
                    'prodi' => 'Validasi AK Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-check',
            ],

            PengajuanAkreditasi::STATUS_AK_DILAPORKAN => [
                'label' => 'Pelaporan AK Selesai',
                'label_short_for' => [
                    'de'   => 'Pelaporan AK Selesai',
                    'upps' => 'Pelaporan AK Selesai',
                    'prodi' => 'Pelaporan AK Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaporan AK Selesai',
                    'upps' => 'Pelaporan AK Selesai',
                    'prodi' => 'Pelaporan AK Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-medical',
            ],

            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED => [
                'label' => 'Penugasan Asesor AL',
                'label_short_for' => [
                    'de'   => 'Asesor AL Ditugaskan',
                    'upps' => 'Asesor AL Ditugaskan',
                    'prodi' => 'Asesor AL Ditugaskan',
                ],
                'label_long_for' => [
                    'de'   => 'Asesor AL Telah Ditugaskan',
                    'upps' => 'Asesor AL Telah Ditugaskan',
                    'prodi' => 'Asesor AL Telah Ditugaskan',
                ],
                'bg' => 'bg-primary',
                'icon' => 'bi-person-badge',
            ],

            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS => [
                'label' => 'Pelaksanaan AL Berlangsung',
                'label_short_for' => [
                    'de'   => 'AL Berlangsung',
                    'upps' => 'AL Berlangsung',
                    'prodi' => 'AL Berlangsung',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaksanaan AL Sedang Berlangsung',
                    'upps' => 'Pelaksanaan AL Sedang Berlangsung',
                    'prodi' => 'Pelaksanaan AL Sedang Berlangsung',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-building',
            ],

            PengajuanAkreditasi::STATUS_AL_SELESAI => [
                'label' => 'Pelaksanaan AL dan Penyampaian Berita Acara AL Selesai',
                'label_short_for' => [
                    'de'   => 'AL Selesai',
                    'upps' => 'AL Selesai',
                    'prodi' => 'AL Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaksanaan AL & Berita Acara Selesai',
                    'upps' => 'Pelaksanaan AL & Berita Acara Selesai',
                    'prodi' => 'Pelaksanaan AL & Berita Acara Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-building-check',
            ],

            PengajuanAkreditasi::STATUS_AL_DILAPORKAN => [
                'label' => 'Pelaporan AL Selesai',
                'label_short_for' => [
                    'de'   => 'Laporan AL Selesai',
                    'upps' => 'Laporan AL Selesai',
                    'prodi' => 'Laporan AL Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaporan AL Selesai',
                    'upps' => 'Pelaporan AL Selesai',
                    'prodi' => 'Pelaporan AL Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-data',
            ],

            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM => [
                'label' => 'Penyampaian Hasil Akreditasi',
                'label_short_for' => [
                    'de'   => 'Hasil Dikirim',
                    'upps' => 'Hasil Disampaikan',
                    'prodi' => 'Hasil Disampaikan',
                ],
                'label_long_for' => [
                    'de'   => 'Hasil Akreditasi Disampaikan',
                    'upps' => 'Hasil Akreditasi Disampaikan',
                    'prodi' => 'Hasil Akreditasi Disampaikan',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-envelope-paper',
            ],

            PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI => [
                'label' => 'Masa Sanggah Berlangsung',
                'label_short_for' => [
                    'de'   => 'Masa Sanggah Berlangsung',
                    'upps' => 'Masa Sanggah Berlangsung',
                    'prodi' => 'Masa Sanggah Berlangsung',
                ],
                'label_long_for' => [
                    'de'   => 'Masa Sanggah Berlangsung',
                    'upps' => 'Masa Sanggah Berlangsung',
                    'prodi' => 'Masa Sanggah Berlangsung',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-clock-history',
            ],

            PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI => [
                'label' => 'Masa Sanggah Selesai',
                'label_short_for' => [
                    'de'   => 'Masa Sanggah Selesai',
                    'upps' => 'Masa Sanggah Selesai',
                    'prodi' => 'Masa Sanggah Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Masa Sanggah Selesai',
                    'upps' => 'Masa Sanggah Selesai',
                    'prodi' => 'Masa Sanggah Selesai',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-clock-history',
            ],

            PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN => [
                'label' => 'Banding Diajukan',
                'label_short_for' => [
                    'de'   => 'Banding Diajukan',
                    'upps' => 'Banding Diajukan',
                    'prodi' => 'Banding Diajukan',
                ],
                'label_long_for' => [
                    'de'   => 'Permohonan Banding Diajukan',
                    'upps' => 'Permohonan Banding Telah Diajukan',
                    'prodi' => 'Permohonan Banding Telah Diajukan',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-file-earmark-break',
            ],

            PengajuanAkreditasi::STATUS_BANDING_DITERIMA => [
                'label' => 'Banding Diterima',
                'label_short_for' => [
                    'de'   => 'Banding Diterima',
                    'upps' => 'Banding Diterima',
                    'prodi' => 'Banding Diterima',
                ],
                'label_long_for' => [
                    'de'   => 'Permohonan Banding Diterima',
                    'upps' => 'Permohonan Banding Telah Diterima',
                    'prodi' => 'Permohonan Banding Telah Diterima',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-file-earmark-break',
            ],

            PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN_BANDING => [
                'label' => 'Menunggu Pembayaran Banding',
                'label_short_for' => [
                    'de'   => 'Menunggu Pembayaran',
                    'upps' => 'Menunggu Pembayaran',
                    'prodi' => 'Menunggu Pembayaran',
                ],
                'label_long_for' => [
                    'de'   => 'Menunggu Pembayaran Banding dari PS',
                    'upps' => 'Menunggu Pembayaran Banding',
                    'prodi' => 'Menunggu Pembayaran Banding',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-hourglass-split',
            ],

            PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DITERIMA => [
                'label' => 'Pembayaran Banding Diterima',
                'label_short_for' => [
                    'de'   => 'Bukti Pembayaran Masuk',
                    'upps' => 'Bukti Pembayaran Terkirim',
                    'prodi' => 'Bukti Pembayaran Terkirim',
                ],
                'label_long_for' => [
                    'de'   => 'Bukti Pembayaran Banding Diterima',
                    'upps' => 'Bukti Pembayaran Banding Terkirim',
                    'prodi' => 'Bukti Pembayaran Banding Terkirim',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-credit-card',
            ],

            PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN_BANDING => [
                'label' => 'Menunggu Validasi Pembayaran Banding',
                'label_short_for' => [
                    'de'   => 'Menunggu Validasi',
                    'upps' => 'Menunggu Validasi',
                    'prodi' => 'Menunggu Validasi',
                ],
                'label_long_for' => [
                    'de'   => 'Menunggu Validasi Pembayaran Banding',
                    'upps' => 'Menunggu Validasi Pembayaran Banding',
                    'prodi' => 'Menunggu Validasi Pembayaran Banding',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-shield-exclamation',
            ],

            PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI => [
                'label' => 'Validasi Pembayaran Banding Selesai',
                'label_short_for' => [
                    'de'   => 'Pembayaran Tervalidasi',
                    'upps' => 'Pembayaran Tervalidasi',
                    'prodi' => 'Pembayaran Tervalidasi',
                ],
                'label_long_for' => [
                    'de'   => 'Pembayaran Banding Tervalidasi',
                    'upps' => 'Pembayaran Banding Tervalidasi',
                    'prodi' => 'Pembayaran Banding Tervalidasi',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-check-circle',
            ],

            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN => [
                'label' => 'Pelaksanaan Banding',
                'label_short_for' => [
                    'de'   => 'Banding Berlangsung',
                    'upps' => 'Banding Berlangsung',
                    'prodi' => 'Banding Berlangsung',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaksanaan Banding Sedang Berlangsung',
                    'upps' => 'Pelaksanaan Banding Sedang Berlangsung',
                    'prodi' => 'Pelaksanaan Banding Sedang Berlangsung',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-arrow-repeat',
            ],

            PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED => [
                'label' => 'Penugasan Asesor AK Banding',
                'label_short_for' => [
                    'de'   => 'Asesor AK Banding Ditugaskan',
                    'upps' => 'Asesor AK Banding Ditugaskan',
                    'prodi' => 'Asesor AK Banding Ditugaskan',
                ],
                'label_long_for' => [
                    'de'   => 'Asesor AK Banding Telah Ditugaskan',
                    'upps' => 'Asesor AK Banding Telah Ditugaskan',
                    'prodi' => 'Asesor AK Banding Telah Ditugaskan',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-person-check',
            ],

            PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS => [
                'label' => 'Penugasan Asesor AK Banding Berlangsung',
                'label_short_for' => [
                    'de'   => 'Sedang Berlangsung',
                    'upps' => 'Sedang Berlangsung',
                    'prodi' => 'Sedang Berlangsung',
                ],
                'label_long_for' => [
                    'de'   => 'Proses AK Banding Berlangsung',
                    'upps' => 'Proses AK Banding Berlangsung',
                    'prodi' => 'Proses AK Banding Berlangsung',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-clipboard-data',
            ],

            PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION => [
                'label' => 'Validasi AK Banding Berlangsung',
                'label_short_for' => [
                    'de'   => 'Validasi AK Banding',
                    'upps' => 'Validasi AK Banding',
                    'prodi' => 'Validasi AK Banding',
                ],
                'label_long_for' => [
                    'de'   => 'Validasi AK Banding Sedang Berlangsung',
                    'upps' => 'Validasi AK Banding Sedang Berlangsung',
                    'prodi' => 'Validasi AK Banding Sedang Berlangsung',
                ],
                'bg' => 'bg-warning',
                'icon' => 'bi-clipboard2-check',
            ],

            PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI => [
                'label' => 'Validasi AK Banding Selesai',
                'label_short_for' => [
                    'de'   => 'Validasi AK Banding Selesai',
                    'upps' => 'Validasi AK Banding Selesai',
                    'prodi' => 'Validasi AK Banding Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Validasi AK Banding Selesai',
                    'upps' => 'Validasi AK Banding Selesai',
                    'prodi' => 'Validasi AK Banding Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-check',
            ],

            PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN => [
                'label' => 'Pelaporan AK Banding Selesai',
                'label_short_for' => [
                    'de'   => 'Pelaporan AK Banding Selesai',
                    'upps' => 'Pelaporan AK Banding Selesai',
                    'prodi' => 'Pelaporan AK Banding Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaporan AK Banding Selesai',
                    'upps' => 'Pelaporan AK Banding Selesai',
                    'prodi' => 'Pelaporan AK Banding Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-file-earmark-medical',
            ],

            PengajuanAkreditasi::STATUS_ASESOR_AL_BANDING_ASSIGNED => [
                'label' => 'Penugasan Asesor AL Banding',
                'label_short_for' => [
                    'de'   => 'Asesor AL Banding Ditugaskan',
                    'upps' => 'Asesor AL Banding Ditugaskan',
                    'prodi' => 'Asesor AL Banding Ditugaskan',
                ],
                'label_long_for' => [
                    'de'   => 'Asesor AL Banding Telah Ditugaskan',
                    'upps' => 'Asesor AL Banding Telah Ditugaskan',
                    'prodi' => 'Asesor AL Banding Telah Ditugaskan',
                ],
                'bg' => 'bg-primary',
                'icon' => 'bi-person-badge',
            ],

            PengajuanAkreditasi::STATUS_AL_BANDING_IN_PROGRESS => [
                'label' => 'Pelaksanaan AL Banding Berlangsung',
                'label_short_for' => [
                    'de'   => 'AL Banding Berlangsung',
                    'upps' => 'AL Banding Berlangsung',
                    'prodi' => 'AL Banding Berlangsung',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaksanaan AL Banding Sedang Berlangsung',
                    'upps' => 'Pelaksanaan AL Banding Sedang Berlangsung',
                    'prodi' => 'Pelaksanaan AL Banding Sedang Berlangsung',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-building',
            ],

            PengajuanAkreditasi::STATUS_AL_BANDING_SELESAI => [
                'label' => 'Pelaksanaan AL Banding dan Penyampaian Berita Acara Selesai',
                'label_short_for' => [
                    'de'   => 'AL Banding Selesai',
                    'upps' => 'AL Banding Selesai',
                    'prodi' => 'AL Banding Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaksanaan AL Banding & Berita Acara Selesai',
                    'upps' => 'Pelaksanaan AL Banding & Berita Acara Selesai',
                    'prodi' => 'Pelaksanaan AL Banding & Berita Acara Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-building-check',
            ],

            PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN => [
                'label' => 'Pelaporan AL Banding Selesai',
                'label_short_for' => [
                    'de'   => 'Laporan AL Banding Selesai',
                    'upps' => 'Laporan AL Banding Selesai',
                    'prodi' => 'Laporan AL Banding Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaporan AL Banding Selesai',
                    'upps' => 'Pelaporan AL Banding Selesai',
                    'prodi' => 'Pelaporan AL Banding Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-clipboard-data',
            ],

            PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN => [
                'label' => 'Pelaporan Banding',
                'label_short_for' => [
                    'de'   => 'Laporan Banding Selesai',
                    'upps' => 'Laporan Banding Selesai',
                    'prodi' => 'Laporan Banding Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaporan Banding Selesai',
                    'upps' => 'Pelaporan Banding Selesai',
                    'prodi' => 'Pelaporan Banding Selesai',
                ],
                'bg' => 'bg-info',
                'icon' => 'bi-file-earmark-ruled',
            ],

            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN => [
                'label' => 'Penetapan Hasil Akreditasi',
                'label_short_for' => [
                    'de'   => 'Hasil Ditetapkan',
                    'upps' => 'Hasil Ditetapkan',
                    'prodi' => 'Hasil Ditetapkan',
                ],
                'label_long_for' => [
                    'de'   => 'Hasil Akreditasi Ditetapkan',
                    'upps' => 'Hasil Akreditasi Ditetapkan',
                    'prodi' => 'Hasil Akreditasi Ditetapkan',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-award',
            ],

            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN => [
                'label' => 'Hasil Akreditasi Diumumkan',
                'label_short_for' => [
                    'de'   => 'Hasil Diumumkan',
                    'upps' => 'Hasil Diumumkan',
                    'prodi' => 'Hasil Diumumkan',
                ],
                'label_long_for' => [
                    'de'   => 'Hasil Akreditasi Diumumkan',
                    'upps' => 'Hasil Akreditasi Diumumkan',
                    'prodi' => 'Hasil Akreditasi Diumumkan',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-megaphone-fill',
            ],

            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN => [
                'label' => 'Pelaporan Hasil Akreditasi',
                'label_short_for' => [
                    'de'   => 'Laporan Hasil Selesai',
                    'upps' => 'Laporan Hasil Selesai',
                    'prodi' => 'Laporan Hasil Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Pelaporan Hasil Akreditasi Selesai',
                    'upps' => 'Pelaporan Hasil Akreditasi Selesai',
                    'prodi' => 'Pelaporan Hasil Akreditasi Selesai',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-megaphone',
            ],

            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN => [
                'label' => 'Penyimpanan Arsip',
                'label_short_for' => [
                    'de'   => 'Arsip Disimpan',
                    'upps' => 'Arsip Tersedia',
                    'prodi' => 'Arsip Tersedia',
                ],
                'label_long_for' => [
                    'de'   => 'Arsip Disimpan',
                    'upps' => 'Arsip Tersedia',
                    'prodi' => 'Arsip Tersedia',
                ],
                'bg' => 'bg-light',
                'icon' => 'bi-archive',
            ],

            PengajuanAkreditasi::STATUS_SELESAI => [
                'label' => 'Proses Akreditasi Selesai',
                'label_short_for' => [
                    'de'   => 'Selesai',
                    'upps' => 'Selesai',
                    'prodi' => 'Selesai',
                ],
                'label_long_for' => [
                    'de'   => 'Proses Akreditasi Selesai',
                    'upps' => 'Proses Akreditasi Selesai',
                    'prodi' => 'Proses Akreditasi Selesai',
                ],
                'bg' => 'bg-success',
                'icon' => 'bi-check-circle-fill',
            ],

            PengajuanAkreditasi::STATUS_DITOLAK => [
                'label' => 'Ditolak',
                'label_short_for' => [
                    'de'   => 'Ditolak',
                    'upps' => 'Ditolak',
                    'prodi' => 'Ditolak',
                ],
                'label_long_for' => [
                    'de'   => 'Telah Ditolak',
                    'upps' => 'Telah Ditolak oleh LAMDEPILAR',
                    'prodi' => 'Telah Ditolak oleh LAMDEPILAR',
                ],
                'bg' => 'bg-danger',
                'icon' => 'bi-x-octagon',
            ],
        ];
    }

    public static function bg(string $status): string
    {
        return PengajuanAkreditasi::map()[$status]['bg'] ?? 'bg-secondary';
    }
}
