<?php

namespace App\Repositories;

use App\Models\DegreeLevel;
use App\Models\SyaratAkreditasi;
use App\Models\SyaratAkreditasiLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyaratAkreditasiRepository
{
    private const CACHE_PREFIX = 'syarat_akreditasi';
    private const CACHE_TTL    = 86400;

    // ── Fallback — dipakai jika data belum ada di DB ──
    private const DEFAULT_KRITERIA_REQUIRED = ['D', 'E', 'P', 'I', 'L', 'A', 'R'];
    private const DEFAULT_RASIO_LINGKUNGAN  = 40;
    private const DEFAULT_RASIO_DEFAULT     = 30;
    private const DEFAULT_RUMPUN_KHUSUS     = ['lingkungan'];

    private const DEFAULT_JABATAN_BY_GROUP = [
        'diploma'  => ['lektor', 'lektor kepala', 'guru besar', 'professor'],
        'sarjana'  => ['lektor', 'lektor kepala', 'guru besar', 'professor'],
        'magister' => ['lektor kepala', 'guru besar', 'professor'],
        'doktor'   => ['lektor kepala', 'guru besar', 'professor'],
        'profesi'  => [],
    ];
    private const DEFAULT_PERSEN_JABATAN    = 50.0;
    private const DEFAULT_PERSEN_SERTIFIKAT = 50.0;
    private const DEFAULT_PERSEN_LULUSAN    = 10.0;
    private const DEFAULT_TIPE_LULUSAN      = 'inovasi_industri';

    // =========================================================
    // READ — RENTANG SKOR (global)
    // =========================================================

    public function getRentangSkor(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . ':rentang_skor',
            self::CACHE_TTL,
            fn() => SyaratAkreditasi::aktif()
                ->whereNull('id_degree_level')
                ->where('kelompok', 'rentang_skor')
                ->get()
                ->map(fn($item) => $item->nilai_cast)
                ->filter()
                ->sortBy('skor_min')
                ->values()
                ->toArray()
        );
    }

    // =========================================================
    // READ — SKOR (global)
    // =========================================================

    public function getSkorMinimumUnggul(): int
    {
        return $this->getAllConfig()['skor_minimum'];
    }

    // =========================================================
    // READ — PELAMPAUAN (global)
    // =========================================================

    public function getKriteriaRequired(): array
    {
        return $this->getGlobalNilai('pelampauan', 'kriteria_required')
            ?? self::DEFAULT_KRITERIA_REQUIRED;
    }

    // =========================================================
    // READ — RASIO DTPS (global)
    // =========================================================

    public function getRasioMaksLingkungan(): int
    {
        return (int)($this->getGlobalNilai('rasio_dtps', 'max_rasio_lingkungan')
            ?? self::DEFAULT_RASIO_LINGKUNGAN);
    }

    public function getRasioMaksDefault(): int
    {
        return (int)($this->getGlobalNilai('rasio_dtps', 'max_rasio_default')
            ?? self::DEFAULT_RASIO_DEFAULT);
    }

    public function getRumpunRasioKhusus(): array
    {
        return $this->getGlobalNilai('rasio_dtps', 'rumpun_rasio_khusus')
            ?? self::DEFAULT_RUMPUN_KHUSUS;
    }

    public function getRasioMaksForRumpun(string $rumpun): int
    {
        $khusus = array_map('strtolower', $this->getRumpunRasioKhusus());

        return in_array(strtolower($rumpun), $khusus)
            ? $this->getRasioMaksLingkungan()
            : $this->getRasioMaksDefault();
    }

    // =========================================================
    // READ — JABATAN (per degree level)
    // =========================================================

    public function getJabatanValid(int $degreeLevelId): array
    {
        // Profesi tidak punya syarat jabatan — return empty agar tidak salah hitung
        if ($this->isDegreeLevelProfesi($degreeLevelId)) {
            return [];
        }

        return $this->getDegreeLevelNilai('jabatan', 'jabatan_valid', $degreeLevelId)
            ?? self::DEFAULT_JABATAN_BY_GROUP['sarjana'];
    }

    private function isDegreeLevelProfesi(int $degreeLevelId): bool
    {
        static $cache = [];
        if (!isset($cache[$degreeLevelId])) {
            $code = DegreeLevel::find($degreeLevelId)?->code ?? '';
            $cache[$degreeLevelId] = in_array($code, ['profesi', 'spesialis-1', 'spesialis-2'], true);
        }
        return $cache[$degreeLevelId];
    }

    public function getPersenMinimumJabatan(int $degreeLevelId): float
    {
        return (float)($this->getDegreeLevelNilai('jabatan', 'persen_minimum', $degreeLevelId)
            ?? self::DEFAULT_PERSEN_JABATAN);
    }

    // =========================================================
    // READ — SERTIFIKAT PROFESI (per degree level)
    // =========================================================

    public function getPersenMinimumSertifikatProfesi(int $degreeLevelId): float
    {
        return (float)($this->getDegreeLevelNilai('sertifikat', 'persen_minimum_sertifikat_profesi', $degreeLevelId)
            ?? self::DEFAULT_PERSEN_SERTIFIKAT);
    }

    // =========================================================
    // READ — CAPAIAN LULUSAN (per degree level)
    // =========================================================

    /**
     * Persentase minimum lulusan yang harus memenuhi capaian.
     * Doktor = 5%, lainnya = 10%.
     */
    public function getPersenMinimumLulusan(int $degreeLevelId): float
    {
        return (float)($this->getDegreeLevelNilai('lulusan', 'persen_minimum', $degreeLevelId)
            ?? self::DEFAULT_PERSEN_LULUSAN);
    }

    /**
     * Tipe capaian lulusan — menentukan sheet, sub-tabel, dan kolom di R.3.
     * Nilai: 'inovasi_industri' | 'publikasi_sinta' | 'publikasi_internasional' | 'inovasi_profesi'
     */
    public function getTipeCapaianLulusan(int $degreeLevelId): string
    {
        return (string)($this->getDegreeLevelNilai('lulusan', 'tipe_capaian', $degreeLevelId)
            ?? self::DEFAULT_TIPE_LULUSAN);
    }

    // =========================================================
    // READ — SYARAT KUALITATIF (global)
    // =========================================================

    public function getSyaratKualitatif(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . ':syarat_kualitatif',
            self::CACHE_TTL,
            fn() => SyaratAkreditasi::aktif()
                ->whereNull('id_degree_level')
                ->where('kelompok', 'syarat_kualitatif')
                ->get()
                ->map(fn($item) => $item->nilai_cast)
                ->filter()
                ->values()
                ->toArray()
        );
    }

    // =========================================================
    // READ — CONFIG GABUNGAN
    // =========================================================

    /**
     * Config lengkap untuk satu degree level.
     * Dipanggil dari HasilAkreditasiService::cekSyaratUnggul().
     *
     * @return array{
     *   skor_minimum: int,
     *   rentang_skor: array,
     *   kriteria_required: string[],
     *   rasio_maks_lingkungan: int,
     *   rasio_maks_default: int,
     *   rumpun_rasio_khusus: string[],
     *   jabatan_valid: string[]|null,
     *   persen_jabatan: float,
     *   persen_sertifikat: float,
     *   persen_lulusan: float,
     *   tipe_lulusan: string,
     * }
     */
    public function getAllConfig(?int $degreeLevelId = null): array
    {
        $cacheKey = self::CACHE_PREFIX . ':all:'
            . ($degreeLevelId ? "dl_{$degreeLevelId}" : 'global');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($degreeLevelId) {
            $rentang = $this->getRentangSkor();

            $skorMinimumUnggul = collect($rentang)
                ->filter(fn($r) => str_contains(strtolower($r['status'] ?? ''), 'unggul'))
                ->min('skor_min');

            $config = [
                // Global
                'skor_minimum'          => (int)($skorMinimumUnggul ?? 281),
                'rentang_skor'          => $rentang,
                'kriteria_required'     => $this->getKriteriaRequired(),
                'rasio_maks_lingkungan' => $this->getRasioMaksLingkungan(),
                'rasio_maks_default'    => $this->getRasioMaksDefault(),
                'rumpun_rasio_khusus'   => $this->getRumpunRasioKhusus(),

                // Per degree level — null jika tidak dioper
                'jabatan_valid'    => null,
                'persen_jabatan'   => self::DEFAULT_PERSEN_JABATAN,
                'persen_sertifikat' => self::DEFAULT_PERSEN_SERTIFIKAT,
                'persen_lulusan'   => self::DEFAULT_PERSEN_LULUSAN,
                'tipe_lulusan'     => self::DEFAULT_TIPE_LULUSAN,
            ];

            if ($degreeLevelId) {
                $config['jabatan_valid']    = $this->getJabatanValid($degreeLevelId);
                $config['persen_jabatan']   = $this->getPersenMinimumJabatan($degreeLevelId);
                $config['persen_sertifikat'] = $this->getPersenMinimumSertifikatProfesi($degreeLevelId);
                $config['persen_lulusan']   = $this->getPersenMinimumLulusan($degreeLevelId);
                $config['tipe_lulusan']     = $this->getTipeCapaianLulusan($degreeLevelId);
            }

            return $config;
        });
    }

    // =========================================================
    // WRITE — UPDATE SYARAT
    // =========================================================

    public function updateSyarat(
        string  $kelompok,
        string  $kunci,
        mixed   $nilaiBaru,
        ?int    $degreeLevelId = null,
        ?string $alasan = null,
        ?int    $userId = null
    ): SyaratAkreditasi {
        DB::beginTransaction();
        try {
            $query = SyaratAkreditasi::aktif()
                ->where('kelompok', $kelompok)
                ->where('kunci', $kunci);

            $degreeLevelId
                ? $query->where('id_degree_level', $degreeLevelId)
                : $query->whereNull('id_degree_level');

            $syarat = $query->firstOrFail();

            $nilaiLama    = $syarat->nilai;
            $nilaiBaruStr = is_array($nilaiBaru)
                ? json_encode($nilaiBaru, JSON_UNESCAPED_UNICODE)
                : (string)$nilaiBaru;

            SyaratAkreditasiLog::create([
                'id_syarat'  => $syarat->id,
                'nilai_lama' => $nilaiLama,
                'nilai_baru' => $nilaiBaruStr,
                'alasan'     => $alasan,
                'changed_by' => $userId ?? auth()->id(),
                'changed_at' => now(),
            ]);

            $syarat->update([
                'nilai'      => $nilaiBaruStr,
                'updated_by' => $userId ?? auth()->id(),
            ]);

            DB::commit();
            $this->invalidateCache($degreeLevelId);

            return $syarat->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================
    // CACHE
    // =========================================================

    public function invalidateCache(?int $degreeLevelId = null): void
    {
        Cache::forget(self::CACHE_PREFIX . ':rentang_skor');
        Cache::forget(self::CACHE_PREFIX . ':all:global');
        Cache::forget(self::CACHE_PREFIX . ':syarat_kualitatif');

        if ($degreeLevelId) {
            Cache::forget(self::CACHE_PREFIX . ":all:dl_{$degreeLevelId}");
            $this->forgetDegreeLevelEntryCache($degreeLevelId);
        } else {
            DegreeLevel::pluck('id')->each(function ($id) {
                Cache::forget(self::CACHE_PREFIX . ":all:dl_{$id}");
                $this->forgetDegreeLevelEntryCache($id);
            });
        }
    }

    // =========================================================
    // PRIVATE
    // =========================================================

    private function getGlobalNilai(string $kelompok, string $kunci): mixed
    {
        $cacheKey = self::CACHE_PREFIX . ":{$kelompok}:{$kunci}:global";

        $cached = Cache::get($cacheKey, '__MISS__');

        if ($cached !== '__MISS__' && $cached !== null) {
            return $cached;
        }

        $syarat = SyaratAkreditasi::aktif()
            ->whereNull('id_degree_level')
            ->where('kelompok', $kelompok)
            ->where('kunci', $kunci)
            ->first();

        $nilai = $syarat?->nilai_cast;

        if ($nilai !== null) {
            Cache::put($cacheKey, $nilai, self::CACHE_TTL);
        }

        return $nilai;
    }

    private function getDegreeLevelNilai(string $kelompok, string $kunci, int $degreeLevelId): mixed
    {
        $cacheKey = self::CACHE_PREFIX . ":{$kelompok}:{$kunci}:dl_{$degreeLevelId}";

        // Jika ada di cache, cek dulu nilainya
        // Jangan percaya cache yang menyimpan null — bisa jadi stale dari sebelum seeder
        $cached = Cache::get($cacheKey, '__MISS__');

        if ($cached !== '__MISS__' && $cached !== null) {
            return $cached;
        }

        // Query DB
        $syarat = SyaratAkreditasi::aktif()
            ->where('id_degree_level', $degreeLevelId)
            ->where('kelompok', $kelompok)
            ->where('kunci', $kunci)
            ->first();

        $syarat ??= SyaratAkreditasi::aktif()
            ->whereNull('id_degree_level')
            ->where('kelompok', $kelompok)
            ->where('kunci', $kunci)
            ->first();

        $nilai = $syarat?->nilai_cast;

        // Hanya cache jika nilai tidak null — hindari menyimpan null stale
        if ($nilai !== null) {
            Cache::put($cacheKey, $nilai, self::CACHE_TTL);
        }

        return $nilai;
    }

    private function forgetDegreeLevelEntryCache(int $degreeLevelId): void
    {
        $entries = [
            ['jabatan',    'jabatan_valid'],
            ['jabatan',    'persen_minimum'],
            ['sertifikat', 'persen_minimum_sertifikat_profesi'],
            ['lulusan',    'persen_minimum'],   // ← tambah
            ['lulusan',    'tipe_capaian'],     // ← tambah
        ];

        foreach ($entries as [$kelompok, $kunci]) {
            Cache::forget(self::CACHE_PREFIX . ":{$kelompok}:{$kunci}:dl_{$degreeLevelId}");
        }
    }
}
