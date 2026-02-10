<?php

namespace App\Domain\Akreditasi;

use App\Models\PengajuanAkreditasi as P;

final class AllowedStatus
{
    /**
     * Transisi status yang diperbolehkan (single source of truth).
     * Dipakai oleh PengajuanAkreditasi::allowedStatusTransitions()
     */
    public static function transitions(): array
    {
        return [
            // =========================
            // Awal pengajuan
            // =========================
            P::STATUS_DRAFT => [
                P::STATUS_PENGINGAT_DIKIRIM,
            ],

            P::STATUS_PENGINGAT_DIKIRIM => [
                P::STATUS_SURAT_PERMOHONAN_DIKIRIM,
            ],

            // Surat permohonan
            P::STATUS_SURAT_PERMOHONAN_DIKIRIM => [
                P::STATUS_SURAT_PERMOHONAN_DITERIMA,
                P::STATUS_SURAT_PERMOHONAN_DITOLAK,
                P::STATUS_SURAT_PERMOHONAN_UPLOAD_ULANG,
            ],

            P::STATUS_SURAT_PERMOHONAN_UPLOAD_ULANG => [
                P::STATUS_SURAT_PERMOHONAN_DIKIRIM,
            ],

            P::STATUS_SURAT_PERMOHONAN_DITERIMA => [
                P::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            ],

            P::STATUS_SURAT_PENERIMAAN_DIKIRIM => [
                P::STATUS_TEMPLATE_LED_DIKIRIM,
                P::STATUS_MENUNGGU_PEMBAYARAN,
            ],

            P::STATUS_TEMPLATE_LED_DIKIRIM => [
                P::STATUS_MENUNGGU_PEMBAYARAN,
            ],

            // =========================
            // Pembayaran
            // =========================
            P::STATUS_MENUNGGU_PEMBAYARAN => [
                P::STATUS_PEMBAYARAN_DITERIMA,
                P::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN,
            ],

            P::STATUS_PEMBAYARAN_DITERIMA => [
                P::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN,
            ],

            P::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN => [
                P::STATUS_PEMBAYARAN_DIVERIFIKASI,
                P::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN,
                P::STATUS_MENUNGGU_PEMBAYARAN,
            ],

            P::STATUS_PEMBAYARAN_DIVERIFIKASI => [
                P::STATUS_DRAFT_BORANG_DIKIRIM,
                P::STATUS_DRAFT_BORANG_DITERIMA,
            ],

            // =========================
            // Borang / Dokumen
            // =========================
            P::STATUS_DRAFT_BORANG_DIKIRIM => [
                P::STATUS_DRAFT_BORANG_DITERIMA,
            ],

            P::STATUS_DRAFT_BORANG_DITERIMA => [
                P::STATUS_BORANG_ONLINE_SELESAI,
            ],

            P::STATUS_BORANG_ONLINE_SELESAI => [
                P::STATUS_BORANG_VALIDATION_PENDING,
            ],

            P::STATUS_BORANG_VALIDATION_PENDING => [
                P::STATUS_BORANG_IN_VALIDATION,
            ],

            P::STATUS_BORANG_IN_VALIDATION => [
                P::STATUS_BORANG_REVISION_REQUIRED,
                P::STATUS_BORANG_VALIDATED,
            ],

            P::STATUS_BORANG_REVISION_REQUIRED => [
                P::STATUS_DRAFT_BORANG_DIKIRIM,
                P::STATUS_DRAFT_BORANG_DITERIMA,
                P::STATUS_BORANG_ONLINE_SELESAI,
            ],

            P::STATUS_BORANG_VALIDATED => [
                P::STATUS_BORANG_FINAL_DITERIMA,
                P::STATUS_VALIDASI_BORANG_DILAPORKAN,
            ],

            P::STATUS_BORANG_FINAL_DITERIMA => [
                P::STATUS_VALIDASI_BORANG_DILAPORKAN,
            ],

            P::STATUS_VALIDASI_BORANG_DILAPORKAN => [
                P::STATUS_PENGAJUAN_COMPLETED,
            ],

            // =========================
            // AK -> AL
            // =========================
            P::STATUS_PENGAJUAN_COMPLETED => [
                P::STATUS_ASESOR_AK_ASSIGNED,
            ],

            P::STATUS_ASESOR_AK_ASSIGNED => [
                P::STATUS_AK_IN_PROGRESS,
            ],

            P::STATUS_AK_IN_PROGRESS => [
                P::STATUS_AK_ON_VALIDATION,
            ],

            P::STATUS_AK_ON_VALIDATION => [
                P::STATUS_AK_SELESAI,
            ],

            P::STATUS_AK_SELESAI => [
                P::STATUS_AK_DILAPORKAN,
            ],

            P::STATUS_AK_DILAPORKAN => [
                P::STATUS_ASESOR_AL_ASSIGNED,
            ],

            P::STATUS_ASESOR_AL_ASSIGNED => [
                P::STATUS_AL_IN_PROGRESS,
            ],

            P::STATUS_AL_IN_PROGRESS => [
                P::STATUS_AL_SELESAI,
            ],

            P::STATUS_AL_SELESAI => [
                P::STATUS_AL_DILAPORKAN,
            ],

            // =========================
            // Hasil, Sanggah, Banding
            // =========================
            P::STATUS_AL_DILAPORKAN => [
                P::STATUS_HASIL_AKREDITASI_DIHITUNG,
            ],

            P::STATUS_HASIL_AKREDITASI_DIHITUNG => [
                P::STATUS_HASIL_AKREDITASI_DIKIRIM,
            ],

            P::STATUS_HASIL_AKREDITASI_DIKIRIM => [
                P::STATUS_MASA_SANGGAH_DIMULAI,
            ],

            P::STATUS_MASA_SANGGAH_DIMULAI => [
                P::STATUS_MASA_SANGGAH_SELESAI,
                P::STATUS_BANDING_DIAJUKAN,
            ],

            P::STATUS_MASA_SANGGAH_SELESAI => [
                P::STATUS_HASIL_DITETAPKAN,
                P::STATUS_BANDING_DIAJUKAN,
            ],

            // Banding lengkap
            P::STATUS_BANDING_DIAJUKAN => [
                P::STATUS_BANDING_DITERIMA,
            ],
            P::STATUS_BANDING_DITERIMA => [
                P::STATUS_BANDING_DITUGASKAN,
            ],
            P::STATUS_BANDING_DITUGASKAN => [
                P::STATUS_BANDING_DILAKSANAKAN,
            ],
            P::STATUS_BANDING_DILAKSANAKAN => [
                P::STATUS_BANDING_DILAPORKAN,
            ],
            P::STATUS_BANDING_DILAPORKAN => [
                P::STATUS_HASIL_DITETAPKAN,
            ],

            // =========================
            // Finalisasi
            // =========================
            P::STATUS_HASIL_DITETAPKAN => [
                P::STATUS_HASIL_DIUMUMKAN,
            ],

            P::STATUS_HASIL_DIUMUMKAN => [
                P::STATUS_HASIL_DILAPORKAN,
            ],

            P::STATUS_HASIL_DILAPORKAN => [
                P::STATUS_ARSIP_DISIMPAN,
            ],

            P::STATUS_ARSIP_DISIMPAN => [
                P::STATUS_SELESAI,
            ],

            // Terminal
            P::STATUS_SELESAI => [],
            P::STATUS_DITOLAK => [],
            P::STATUS_SURAT_PERMOHONAN_DITOLAK => [],
        ];
    }

    /**
     * Daftar status yang dianggap bagian dari sebuah "fase/attribute"
     * (menggantikan hardcode di getCustomLastStatus()).
     */
    public static function phase(string $key): array
    {
        return match ($key) {
            'surat_permohonan_ps' => [
                P::STATUS_DRAFT,
                P::STATUS_PENGINGAT_DIKIRIM,
                P::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                P::STATUS_SURAT_PERMOHONAN_UPLOAD_ULANG,
                P::STATUS_SURAT_PERMOHONAN_DITERIMA,
                P::STATUS_SURAT_PERMOHONAN_DITOLAK,
            ],

            'surat_penerimaan_de' => [
                P::STATUS_SURAT_PERMOHONAN_DITERIMA,
                P::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            ],

            'borang_template' => [
                P::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                P::STATUS_TEMPLATE_LED_DIKIRIM,
            ],

            'draft_borang' => [
                P::STATUS_DRAFT_BORANG_DIKIRIM,
                P::STATUS_DRAFT_BORANG_DITERIMA,
                P::STATUS_BORANG_ONLINE_SELESAI,
            ],

            'borang_final' => [
                P::STATUS_DRAFT_BORANG_DIKIRIM,
                P::STATUS_DRAFT_BORANG_DITERIMA,
                P::STATUS_BORANG_ONLINE_SELESAI,
                P::STATUS_BORANG_VALIDATION_PENDING,
                P::STATUS_BORANG_IN_VALIDATION,
                P::STATUS_BORANG_REVISION_REQUIRED,
                P::STATUS_BORANG_VALIDATED,
                P::STATUS_BORANG_FINAL_DITERIMA,
                P::STATUS_VALIDASI_BORANG_DILAPORKAN,
            ],

            'validasi_dokumen' => [
                P::STATUS_DRAFT_BORANG_DIKIRIM,
                P::STATUS_DRAFT_BORANG_DITERIMA,
                P::STATUS_BORANG_ONLINE_SELESAI,
                P::STATUS_BORANG_VALIDATION_PENDING,
                P::STATUS_BORANG_IN_VALIDATION,
                P::STATUS_BORANG_REVISION_REQUIRED,
                P::STATUS_BORANG_VALIDATED,
            ],

            'pelaporan_dokumen' => [
                P::STATUS_VALIDASI_BORANG_DILAPORKAN,
            ],

            'penugasan_asesor_ak' => [
                P::STATUS_PENGAJUAN_COMPLETED,
                P::STATUS_ASESOR_AK_ASSIGNED,
            ],

            'validasi_ak' => [
                P::STATUS_AK_IN_PROGRESS,
                P::STATUS_AK_ON_VALIDATION,
                P::STATUS_AK_SELESAI,
            ],

            'pelaporan_ak' => [
                P::STATUS_AK_SELESAI,
                P::STATUS_AK_DILAPORKAN,
            ],

            'penugasan_asesor_al' => [
                P::STATUS_AK_DILAPORKAN,
                P::STATUS_ASESOR_AL_ASSIGNED,
            ],

            'pelaksanaan_al' => [
                P::STATUS_AL_IN_PROGRESS,
                P::STATUS_AL_SELESAI,
            ],

            'pelaporan_al' => [
                P::STATUS_AL_SELESAI,
                P::STATUS_AL_DILAPORKAN,
            ],

            // STEP 15: Penyampaian hasil akreditasi
            // (kalau kamu pakai "dihitung", masukkan juga di sini)
            'penyampaian_hasil' => [
                P::STATUS_HASIL_AKREDITASI_DIHITUNG,
                P::STATUS_HASIL_AKREDITASI_DIKIRIM,
            ],

            // STEP 16: Masa sanggah (mulai & selesai)
            'masa_sanggah' => [
                P::STATUS_MASA_SANGGAH_DIMULAI,
                P::STATUS_MASA_SANGGAH_SELESAI,
            ],

            // STEP 17: Permohonan banding (dari diajukan sampai ditugaskan)
            'permohonan_banding' => [
                P::STATUS_BANDING_DIAJUKAN,
                P::STATUS_BANDING_DITERIMA,
                P::STATUS_BANDING_DITUGASKAN,
            ],

            // STEP 17: Pelaksanaan banding (aksi lapangan)
            'pelaksanaan_banding' => [
                P::STATUS_BANDING_DILAKSANAKAN,
            ],

            // STEP 18: Pelaporan banding
            'pelaporan_banding' => [
                P::STATUS_BANDING_DILAPORKAN,
            ],

            // STEP 19: Penetapan hasil
            // (kalau di UI kamu Step 19 itu penetapan, Step 20 pelaporan/umumkan)
            'penetapan_hasil' => [
                P::STATUS_HASIL_DITETAPKAN,
            ],

            // STEP 20: Pelaporan hasil (dan/atau pengumuman)
            // kamu sudah punya HASIL_DIUMUMKAN dan HASIL_DILAPORKAN
            'pelaporan_hasil' => [
                P::STATUS_HASIL_DIUMUMKAN,
                P::STATUS_HASIL_DILAPORKAN,
            ],

            // STEP 21: Penyimpanan arsip (dan selesai)
            'penyimpanan_arsip' => [
                P::STATUS_ARSIP_DISIMPAN,
                P::STATUS_SELESAI,
            ],

            default => [],
        };
    }

    /**
     * Opsional: mapping attribute -> nomor step timeline (biar gampang dipakai UI)
     */
    public static function phaseStep(string $key): ?int
    {
        return match ($key) {
            'penyampaian_hasil'   => 15,
            'masa_sanggah'        => 16,
            'permohonan_banding'  => 17,
            'pelaksanaan_banding' => 17,
            'pelaporan_banding'   => 18,
            'penetapan_hasil'     => 19,
            'pelaporan_hasil'     => 20,
            'penyimpanan_arsip'   => 21,
            default => null,
        };
    }

    /**
     * Helper: semua key fase yang tersedia (buat validasi input).
     */
    public static function phaseKeys(): array
    {
        return [
            'surat_permohonan_ps',
            'surat_penerimaan_de',
            'borang_template',
            'draft_borang',
            'borang_final',
            'validasi_dokumen',
            'pelaporan_dokumen',
            'penugasan_asesor_ak',
            'validasi_ak',
            'pelaporan_ak',
            'penugasan_asesor_al',
            'pelaksanaan_al',
            'pelaporan_al',
            'penyampaian_hasil',
            'masa_sanggah',
            'permohonan_banding',
            'pelaksanaan_banding',
            'pelaporan_banding',
            'penetapan_hasil',
            'pelaporan_hasil',
            'penyimpanan_arsip',
        ];
    }

    /**
     * Phase khusus setelah pelaporan AL (fase hasil akhir)
     */
    public static function phaseAfterPelaporanAl(string $key): array
    {
        return match ($key) {
            // Step 15
            'penyampaian_hasil' => [
                P::STATUS_HASIL_AKREDITASI_DIHITUNG,
                P::STATUS_HASIL_AKREDITASI_DIKIRIM,
            ],

            // Step 16
            'masa_sanggah' => [
                P::STATUS_MASA_SANGGAH_DIMULAI,
                P::STATUS_MASA_SANGGAH_SELESAI,
            ],

            // Step 17 (permohonan/admin banding)
            'permohonan_banding' => [
                P::STATUS_MASA_SANGGAH_DIMULAI,
                P::STATUS_BANDING_DIAJUKAN,
                P::STATUS_BANDING_DITERIMA,
                P::STATUS_BANDING_DITUGASKAN,
            ],

            // Step 17 (pelaksanaan)
            'pelaksanaan_banding' => [
                P::STATUS_BANDING_DILAKSANAKAN,
            ],

            // Step 18
            'pelaporan_banding' => [
                P::STATUS_BANDING_DILAPORKAN,
            ],

            // Step 19
            'penetapan_hasil' => [
                P::STATUS_HASIL_DITETAPKAN,
            ],

            // Step 20
            'pelaporan_hasil' => [
                P::STATUS_HASIL_DIUMUMKAN,
                P::STATUS_HASIL_DILAPORKAN,
            ],

            // Step 21
            'penyimpanan_arsip' => [
                P::STATUS_ARSIP_DISIMPAN,
                P::STATUS_SELESAI,
            ],

            default => [],
        };
    }
}
