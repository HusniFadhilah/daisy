<?php

namespace App\Repositories;

use App\Models\SyaratAkreditasi;
use App\Models\SyaratAkreditasiLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Repository satu-satunya pintu untuk membaca konfigurasi syarat akreditasi.
 *
 * Seeder aktif: SyaratAkreditasiSeeder v2 — hanya mengisi kelompok 'rentang_skor'.
 * Kelompok lain (skor, pelampauan, rasio_dtps, jabatan) tidak ada di DB;
 * method terkait mengembalikan nilai default hardcode.
 *
 * Strategi cache:
 *   - Key utama  : 'syarat_akreditasi:rentang_skor'
 *   - Key derived: 'syarat_akreditasi:all' (config gabungan untuk cekSyaratUnggul)
 *   - TTL 1 hari — invalidate otomatis via updateSyarat()
 */
class SyaratAkreditasiRepository
{
    private const CACHE_PREFIX = 'syarat_akreditasi';
    private const CACHE_TTL    = 86400; // 1 hari

    // Default hardcode — dipakai selama kelompok terkait belum ada di DB
    private const DEFAULT_KRITERIA_REQUIRED     = ['D', 'E', 'P', 'I', 'L', 'A', 'R'];
    private const DEFAULT_RASIO_LINGKUNGAN      = 40;
    private const DEFAULT_RASIO_DEFAULT         = 30;
    private const DEFAULT_RUMPUN_KHUSUS         = ['lingkungan'];
    private const DEFAULT_JABATAN_LEKTOR        = ['lektor', 'lektor kepala', 'guru besar', 'professor'];
    private const DEFAULT_PERSEN_LEKTOR         = 50.0;

    // =========================================================
    // READ — RENTANG SKOR  (sumber data utama seeder v2)
    // =========================================================

    /**
     * Ambil semua rentang skor, diurutkan skor_min ASC.
     *
     * Struktur tiap item (dari kolom `nilai` tipe JSON):
     *   skor_min, skor_max, persen_min, persen_max,
     *   status, makna (string|array), siklus_tahun
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRentangSkor(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . ':rentang_skor',
            self::CACHE_TTL,
            function () {
                return SyaratAkreditasi::aktif()
                    ->where('kelompok', 'rentang_skor')
                    ->get()
                    ->map(fn($item) => $item->nilai_cast) // handles tipe='json'
                    ->filter()                            // buang null jika decode gagal
                    ->sortBy('skor_min')
                    ->values()
                    ->toArray();
            }
        );
    }

    // =========================================================
    // READ — SKOR
    // Diturunkan dari rentang_skor: skor_min terkecil dengan status Unggul
    // =========================================================

    /**
     * Skor minimum untuk mendapatkan status Unggul.
     * Diambil dari rentang_skor — cari skor_min terkecil yang statusnya mengandung 'Unggul'.
     *
     * Contoh dari seeder v2: rentang 281–320 → skor_minimum = 281
     */
    public function getSkorMinimumUnggul(): int
    {
        return $this->getAllConfig()['skor_minimum'];
    }

    // =========================================================
    // READ — PELAMPAUAN STANDAR
    // Tidak ada di seeder v2 → selalu kembalikan default
    // =========================================================

    /**
     * Kode kriteria yang wajib ada elemen Melampaui Standar.
     *
     * @return string[]
     */
    public function getKriteriaRequired(): array
    {
        return self::DEFAULT_KRITERIA_REQUIRED;
    }

    // =========================================================
    // READ — RASIO DTPS
    // Tidak ada di seeder v2 → selalu kembalikan default
    // =========================================================

    public function getRasioMaksLingkungan(): int
    {
        return self::DEFAULT_RASIO_LINGKUNGAN;
    }

    public function getRasioMaksDefault(): int
    {
        return self::DEFAULT_RASIO_DEFAULT;
    }

    /**
     * @return string[]
     */
    public function getRumpunRasioKhusus(): array
    {
        return self::DEFAULT_RUMPUN_KHUSUS;
    }

    public function getRasioMaksForRumpun(string $rumpun): int
    {
        $khusus = array_map('strtolower', $this->getRumpunRasioKhusus());

        return in_array(strtolower($rumpun), $khusus)
            ? $this->getRasioMaksLingkungan()
            : $this->getRasioMaksDefault();
    }

    // =========================================================
    // READ — JABATAN
    // Tidak ada di seeder v2 → selalu kembalikan default
    // =========================================================

    /**
     * @return string[]
     */
    public function getJabatanLektorKeAtas(): array
    {
        return self::DEFAULT_JABATAN_LEKTOR;
    }

    public function getPersenMinimumLektor(): float
    {
        return self::DEFAULT_PERSEN_LEKTOR;
    }

    // =========================================================
    // READ — CONFIG GABUNGAN (untuk HasilAkreditasiService)
    // =========================================================

    /**
     * Semua konfigurasi yang dibutuhkan cekSyaratUnggul() dalam satu panggilan.
     *
     * skor_minimum diturunkan dari rentang_skor (skor_min Unggul terkecil).
     * Nilai lain menggunakan konstanta default karena tidak ada di DB.
     *
     * @return array{
     *   skor_minimum: int,
     *   kriteria_required: string[],
     *   rasio_maks_lingkungan: int,
     *   rasio_maks_default: int,
     *   rumpun_rasio_khusus: string[],
     *   jabatan_lektor: string[],
     *   persen_lektor: float,
     *   rentang_skor: array
     * }
     */
    public function getAllConfig(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . ':all',
            self::CACHE_TTL,
            function () {
                $rentang = $this->getRentangSkor();

                // Derive skor_minimum: skor_min terkecil di antara rentang yang statusnya Unggul
                $skorMinimumUnggul = collect($rentang)
                    ->filter(fn($r) => str_contains(strtolower($r['status'] ?? ''), 'unggul'))
                    ->min('skor_min');

                return [
                    // Dari rentang_skor DB
                    'skor_minimum'          => (int)($skorMinimumUnggul ?? 281),
                    'rentang_skor'          => $rentang,

                    // Default hardcode (belum ada di DB seeder v2)
                    'kriteria_required'     => self::DEFAULT_KRITERIA_REQUIRED,
                    'rasio_maks_lingkungan' => self::DEFAULT_RASIO_LINGKUNGAN,
                    'rasio_maks_default'    => self::DEFAULT_RASIO_DEFAULT,
                    'rumpun_rasio_khusus'   => self::DEFAULT_RUMPUN_KHUSUS,
                    'jabatan_lektor'        => self::DEFAULT_JABATAN_LEKTOR,
                    'persen_lektor'         => self::DEFAULT_PERSEN_LEKTOR,
                ];
            }
        );
    }

    // =========================================================
    // WRITE — UPDATE SYARAT (dengan log + invalidate cache)
    // =========================================================

    /**
     * Update nilai satu syarat dan catat ke log audit.
     * Saat ini hanya rentang_skor yang ada di DB.
     */
    public function updateSyarat(
        string  $kelompok,
        string  $kunci,
        mixed   $nilaiBaru,
        ?string $alasan = null,
        ?int    $userId = null
    ): SyaratAkreditasi {
        DB::beginTransaction();

        try {
            $syarat = SyaratAkreditasi::aktif()
                ->where('kelompok', $kelompok)
                ->where('kunci', $kunci)
                ->firstOrFail();

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

            $this->invalidateCache();

            return $syarat->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hapus seluruh cache syarat.
     * Dipanggil otomatis setelah updateSyarat(), atau manual saat deploy.
     */
    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . ':rentang_skor');
        Cache::forget(self::CACHE_PREFIX . ':all');
    }

    public function getSyaratKualitatif(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . ':syarat_kualitatif',
            self::CACHE_TTL,
            fn() => SyaratAkreditasi::aktif()
                ->where('kelompok', 'syarat_kualitatif')
                ->get()
                ->map(fn($item) => $item->nilai_cast)
                ->filter()
                ->values()
                ->toArray()
        );
    }
}
