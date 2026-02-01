<?php

namespace App\Domain\Akreditasi;

use App\Models\PengajuanAkreditasi;

final class PengajuanPhase
{
    /**
     * Daftar status yang dianggap bagian dari sebuah "fase/attribute"
     */
    public static function statuses(string $attribute): array
    {
        return match ($attribute) {
            'surat_permohonan_ps' => [
                PengajuanAkreditasi::STATUS_DRAFT,
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
            ],

            'surat_penerimaan_de' => [
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            ],

            'borang_template' => [
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ],

            'draft_borang', 'borang_final' => [
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ],

            default => [],
        };
    }
}
