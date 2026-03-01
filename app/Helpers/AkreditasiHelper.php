<?php

namespace App\Helpers;

use App\Models\JenjangPenilaian;
use App\Models\StatusAkreditasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AkreditasiHelper
{
    private const CACHE_TTL = 86400; // 1 hari

    // =========================================================
    // READ — StatusAkreditasi dari DB (cached)
    // =========================================================

    /**
     * Semua StatusAkreditasi diurutkan skor_min ASC, di-cache 1 hari.
     *
     * @return Collection<int, StatusAkreditasi>
     */
    private static function getAllStatus(): Collection
    {
        return Cache::remember('status_akreditasi:all', self::CACHE_TTL, function () {
            return StatusAkreditasi::orderBy('skor_min')->get();
        });
    }

    /**
     * Cari StatusAkreditasi yang range-nya mencakup $skor.
     */
    public static function getStatusBySkor(float $skor): ?StatusAkreditasi
    {
        return static::getAllStatus()
            ->first(fn($s) => $skor >= $s->skor_min && $skor <= $s->skor_max);
    }

    // =========================================================
    // SYARAT / MAKNA PER STATUS
    // =========================================================

    /**
     * Teks syarat berdasarkan nama status (dari DB).
     * Mengembalikan array baris teks untuk ditampilkan di UI.
     */
    public static function getPeringkatRequirements(string $statusNama): array
    {
        $status = static::getAllStatus()->firstWhere('status', $statusNama);

        if (!$status) {
            return [];
        }

        $syarat = [];

        $syarat[] = "Skor AL {$status->skor_min}–{$status->skor_max}";

        if (!empty($status->persen_min) && !empty($status->persen_max)) {
            $syarat[] = "Persentase {$status->persen_min}–{$status->persen_max}%";
        }

        if ($status->siklus_tahun) {
            $syarat[] = "Siklus reakreditasi: {$status->siklus_tahun} tahun";
        }

        if ($status->keterangan) {
            $syarat[] = $status->keterangan;
        }

        return $syarat;
    }

    /**
     * Teks syarat berdasarkan skor aktual.
     * Berguna saat satu nama status (misal "Terakreditasi Unggul")
     * muncul di beberapa rentang dengan siklus berbeda.
     */
    public static function getPeringkatRequirementsBySkor(float $skor): array
    {
        $status = static::getStatusBySkor($skor);

        if (!$status) {
            return [];
        }

        $syarat = [];

        $syarat[] = "Rentang: {$status->skor_min}–{$status->skor_max}";

        if (!empty($status->persen_min) && !empty($status->persen_max)) {
            $syarat[] = "Persentase: {$status->persen_min}–{$status->persen_max}%";
        }

        if ($status->siklus_tahun) {
            $syarat[] = "Siklus reakreditasi: {$status->siklus_tahun} tahun";
        }

        if ($status->keterangan) {
            $syarat[] = $status->keterangan;
        }

        return $syarat;
    }

    // =========================================================
    // WARNA
    // =========================================================

    /**
     * Warna hex berdasarkan nama status.
     */
    public static function getWarnaByStatus(string $statusNama): string
    {
        return static::getAllStatus()->firstWhere('status', $statusNama)?->warna ?? '#e2e3e5';
    }

    /**
     * Warna hex berdasarkan skor aktual.
     */
    public static function getWarnaBySkor(float $skor): string
    {
        return static::getStatusBySkor($skor)?->warna ?? '#e2e3e5';
    }

    // =========================================================
    // PESAN VALIDASI
    // =========================================================

    /**
     * Format pesan validasi untuk ditampilkan di UI.
     *
     * $validationSummary berasal dari HasilAkreditasiService::getValidationSummary().
     * Mengembalikan string kosong jika semua syarat terpenuhi.
     */
    public static function formatValidationMessage(array $validationSummary): string
    {
        if ($validationSummary['dapat_unggul']) {
            return '';
        }

        $messages = [];
        $skorMin  = $validationSummary['skor_minimum']; // dari DB via SyaratAkreditasiRepository
        $skor     = $validationSummary['skor'];
        $sp       = $validationSummary['syarat_p1'];

        if (!$validationSummary['skor_memenuhi']) {
            $messages[] = "📊 Skor {$skor} belum mencapai minimum Unggul ({$skorMin}).";
        }

        if ($validationSummary['skor_memenuhi'] && !$validationSummary['pelampauan_memenuhi']) {
            $messages[] = "⚠️ Skor {$skor} mencapai syarat Unggul (≥ {$skorMin}), "
                . "namun belum memenuhi syarat " . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ".";
            $messages[] = "📋 Kriteria yang belum terpenuhi: "
                . implode(', ', $validationSummary['missing_kriteria']);
        }

        if (!$sp['rasio']['memenuhi']) {
            $messages[] = "⚠️ " . $sp['rasio']['keterangan'];
        }

        if (!$sp['jabatan']['memenuhi']) {
            $messages[] = "⚠️ " . $sp['jabatan']['keterangan'];
        }

        return implode("\n", $messages);
    }

    // =========================================================
    // CACHE MANAGEMENT
    // =========================================================

    /**
     * Hapus cache StatusAkreditasi.
     * Panggil setelah data StatusAkreditasi diupdate via admin.
     */
    public static function invalidateCache(): void
    {
        Cache::forget('status_akreditasi:all');
    }
}
