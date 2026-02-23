<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HasilAkreditasi extends Model
{
    protected $table = 'hasil_akreditasi';

    protected $fillable = [
        'id_pengajuan',
        'id_asesmen',
        'id_study_program',
        'id_category',
        'id_status_ak',
        'id_status_al',
        'id_status_hasil',
        'id_status_banding',
        'id_status_final',
        'skor_ak',
        'skor_ak_tertimbang',
        'total_bobot_ak',
        'detail_skor_ak',
        'pelampauan_standar_ak',
        'tanggal_finalisasi_ak',
        'finalized_ak_by',
        'skor_al',
        'skor_al_tertimbang',
        'total_bobot_al',
        'detail_skor_al',
        'pelampauan_standar_al',
        'tanggal_finalisasi_al',
        'finalized_al_by',
        'skor_hasil',
        'skor_hasil_tertimbang',
        'total_bobot_hasil',
        'detail_skor_hasil',
        'pelampauan_standar_hasil',
        'tanggal_finalisasi_hasil',
        'finalized_hasil_by',
        'skor_banding',
        'skor_banding_tertimbang',
        'total_bobot_banding',
        'detail_skor_banding',
        'pelampauan_standar_banding',
        'tanggal_finalisasi_banding',
        'finalized_banding_by',
        'skor_final',
        'skor_final_tertimbang',
        'total_bobot_final',
        'detail_skor_final',
        'pelampauan_standar_final',
        'peringkat_akreditasi_hasil',
        'peringkat_akreditasi_banding',
        'peringkat_akreditasi_final',
        'memenuhi_syarat_unggul',
        'catatan_validasi',
        'status',
        'catatan_perhitungan',
        'metadata',
    ];

    protected $casts = [
        'detail_skor_ak'           => 'array',
        'detail_skor_al'           => 'array',
        'detail_skor_hasil'        => 'array',
        'detail_skor_banding'      => 'array',
        'detail_skor_final'        => 'array',
        'pelampauan_standar_ak'    => 'array',
        'pelampauan_standar_al'    => 'array',
        'pelampauan_standar_hasil' => 'array',
        'pelampauan_standar_banding' => 'array',
        'pelampauan_standar_final' => 'array',
        'metadata'                 => 'array',
        'tanggal_finalisasi_ak'    => 'datetime',
        'tanggal_finalisasi_al'    => 'datetime',
        'tanggal_finalisasi_hasil' => 'datetime',
        'tanggal_finalisasi_banding' => 'datetime',
        'skor_ak'                  => 'decimal:2',
        'skor_ak_tertimbang'       => 'decimal:2',
        'skor_al'                  => 'decimal:2',
        'skor_al_tertimbang'       => 'decimal:2',
        'skor_hasil'               => 'decimal:2',
        'skor_hasil_tertimbang'    => 'decimal:2',
        'skor_banding'             => 'decimal:2',
        'skor_banding_tertimbang'  => 'decimal:2',
        'skor_final'               => 'decimal:2',
        'skor_final_tertimbang'    => 'decimal:2',
        'memenuhi_syarat_unggul'   => 'boolean',
    ];

    // =========================================================
    // KONSTANTA FALLBACK
    // Digunakan hanya jika SyaratAkreditasiRepository tidak tersedia.
    // Nilai sesungguhnya selalu diambil dari DB via service layer.
    // =========================================================

    /** @var string[] Fallback statis — jangan gunakan langsung di logika bisnis */
    public const KRITERIA_REQUIRED_FALLBACK = ['D', 'E', 'P', 'I', 'L', 'A', 'R'];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_study_program');
    }

    public function category()
    {
        return $this->belongsTo(StudyProgramCategory::class, 'id_category');
    }

    public function statusAk()
    {
        return $this->belongsTo(StatusAkreditasi::class, 'id_status_ak');
    }

    public function statusAl()
    {
        return $this->belongsTo(StatusAkreditasi::class, 'id_status_al');
    }

    public function statusHasil()
    {
        return $this->belongsTo(StatusAkreditasi::class, 'id_status_hasil');
    }

    public function statusBanding()
    {
        return $this->belongsTo(StatusAkreditasi::class, 'id_status_banding');
    }

    public function statusFinal()
    {
        return $this->belongsTo(StatusAkreditasi::class, 'id_status_final');
    }

    public function finalizedAkBy()
    {
        return $this->belongsTo(User::class, 'finalized_ak_by');
    }

    public function finalizedAlBy()
    {
        return $this->belongsTo(User::class, 'finalized_al_by');
    }

    public function finalizedHasilBy()
    {
        return $this->belongsTo(User::class, 'finalized_hasil_by');
    }

    public function finalizedBandingBy()
    {
        return $this->belongsTo(User::class, 'finalized_banding_by');
    }

    public function finalizedPenetapanBy()
    {
        return $this->belongsTo(User::class, 'finalized_penetapan_by');
    }
    // =========================================================
    // STATUS HELPERS
    // =========================================================

    public function isAkFinalized(): bool
    {
        return in_array($this->status, [
            'final_ak',
            'draft_al',
            'final_al',
            'final_combined',
            'published',
        ]);
    }

    public function isPenetapanFinalized(): bool
    {
        return in_array($this->status, ['final_penetapan', 'published']);
    }

    public function isPenetapanDraft(): bool
    {
        return $this->status === 'draft_penetapan';
    }

    public function isAlFinalized(): bool
    {
        return in_array($this->status, [
            'final_al',
            'final_hasil',
            'draft_banding',
            'final_banding',
            'draft_penetapan',
            'final_penetapan',
            'published',
        ]);
    }

    public function isHasilFinalized(): bool
    {
        return in_array($this->status, [
            'final_hasil',
            'draft_banding',
            'final_banding',
            'draft_penetapan',
            'final_penetapan',
            'published',
        ]);
    }

    public function isBandingFinalized(): bool
    {
        return in_array($this->status, [
            'final_banding',
            'draft_penetapan',
            'final_penetapan',
            'published',
        ]);
    }

    public function isFinalCombined(): bool
    {
        return in_array($this->status, ['final_combined', 'published']);
    }

    public static function getStatusHasilAkreditasi($asesmen): string
    {
        $ak = $asesmen->asesmenKecukupan->status ?? null;
        $al = $asesmen->asesmenLapangan->status  ?? null;

        return match (true) {
            $ak === 'completed' && $al === 'completed' => 'final_combined',
            $al === 'completed'                        => 'final_al',
            $ak === 'completed'                        => 'final_ak',
            $al === 'active'                           => 'draft_al',
            $ak === 'active'                           => 'draft_ak',
            default                                    => 'draft',
        };
    }

    // =========================================================
    // SYARAT PELAMPAUAN STANDAR
    //
    // Model TIDAK tahu soal SyaratAkreditasiRepository.
    // Service layer yang bertanggung jawab mengoper array
    // $kriteriaRequired dinamis dari DB ke method ini.
    //
    // Jika $kriteriaRequired tidak dioper (null), fallback ke
    // konstanta statis KRITERIA_REQUIRED_FALLBACK.
    // =========================================================

    /**
     * Cek apakah semua kriteria memiliki minimal 1 elemen Melampaui Standar.
     *
     * @param  string[]|null  $kriteriaRequired  Dari SyaratAkreditasiRepository::getKriteriaRequired()
     */
    public function memenuhi_syarat_pelampauan(?array $kriteriaRequired = null): bool
    {
        $kriteria = $kriteriaRequired ?? self::KRITERIA_REQUIRED_FALLBACK;

        if (empty($this->pelampauan_standar_al)) {
            return false;
        }

        foreach ($kriteria as $kode) {
            if (empty($this->pelampauan_standar_al[$kode])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Daftar kode kriteria yang belum memiliki elemen Melampaui Standar.
     *
     * @param  string[]|null  $kriteriaRequired  Dari SyaratAkreditasiRepository::getKriteriaRequired()
     * @return string[]
     */
    public function getMissingKriteriaForUnggul(?array $kriteriaRequired = null): array
    {
        $kriteria = $kriteriaRequired ?? self::KRITERIA_REQUIRED_FALLBACK;

        if (empty($this->pelampauan_standar_al)) {
            return $kriteria;
        }

        return array_values(array_filter(
            $kriteria,
            fn($kode) => empty($this->pelampauan_standar_al[$kode])
        ));
    }

    /**
     * @deprecated Gunakan memenuhi_syarat_pelampauan() — dipertahankan untuk backward compatibility.
     */
    public function memenuhi_syarat_unggul_check(): bool
    {
        return $this->memenuhi_syarat_pelampauan();
    }

    // =========================================================
    // PERINGKAT
    // =========================================================

    /**
     * Tentukan peringkat dari skor AL dengan validasi syarat Unggul.
     *
     * Parameter $memenuhiPelampauan dan $memenuhiP1 dioper dari service
     * setelah menjalankan semua pengecekan (termasuk LKPS dan DB config).
     *
     * Alur:
     *   1. Lookup StatusAkreditasi dari tabel berdasarkan skor
     *   2. Jika status == "Terakreditasi Unggul" → validasi syarat tambahan
     *   3. Jika syarat tidak terpenuhi → downgrade ke status tertinggi non-Unggul
     *
     * @param  float  $skor
     * @param  bool   $memenuhiPelampauan  Dari memenuhi_syarat_pelampauan()
     * @param  bool   $memenuhiP1          Dari LkpsDataReaderService::cekSemuaSyaratP1()
     */
    public function getPeringkatFromSkorAL(
        float $skor,
        bool  $memenuhiPelampauan = false,
        bool  $memenuhiP1 = false
    ): string {
        $status = StatusAkreditasi::where('skor_min', '<=', $skor)
            ->where('skor_max', '>=', $skor)
            ->first();

        if (!$status) {
            return 'Tidak Terakreditasi';
        }

        // Bukan Unggul → langsung kembalikan, tidak perlu cek syarat tambahan
        if ($status->status !== 'Terakreditasi Unggul') {
            return $status->status;
        }

        // Status Unggul — semua syarat harus terpenuhi
        if ($memenuhiPelampauan && $memenuhiP1) {
            return $status->status; // "Terakreditasi Unggul"
        }

        // Salah satu syarat tidak terpenuhi → cari status tertinggi non-Unggul
        $downgrade = StatusAkreditasi::where('status', '!=', 'Terakreditasi Unggul')
            ->where('skor_min', '<=', $skor)
            ->where('skor_max', '>=', $skor)
            ->orderByDesc('skor_max')
            ->first();

        return $downgrade?->status ?? 'Terakreditasi';
    }

    /**
     * Peringkat dari skor mentah tanpa syarat Unggul — untuk tampilan informatif di UI.
     */
    public function getPeringkatFromSkor(float $skor): string
    {
        $status = StatusAkreditasi::where('skor_min', '<=', $skor)
            ->where('skor_max', '>=', $skor)
            ->first();

        return $status?->status ?? 'Tidak Terakreditasi';
    }

    // =========================================================
    // TAMPILAN
    // =========================================================
    public function getPeringkatColor(): string
    {
        return $this->statusFinal?->warna
            ?? $this->statusAl?->warna
            ?? $this->statusAk?->warna
            ?? '#e2e3e5';
    }

    public function getLabelPeringkatAttribute(): string
    {
        $status = $this->statusFinal;
        if (!$status) return '';

        $tahun = (int)$status->siklus_tahun;
        return "{$status->status} ({$tahun} Tahun)";
    }

    // =========================================================
    // STATIC INITIALIZER
    // =========================================================

    /**
     * Buat atau ambil HasilAkreditasi, lalu auto-hitung AK & AL
     * jika status masih draft_al.
     *
     * Dipanggil dari controller setelah AL selesai, sebelum menampilkan
     * halaman show. Tidak perlu dipanggil lagi bila hasil sudah ada.
     */
    public static function initializeHasil($hasilService, $pengajuan, $authId): self
    {
        $asesmen = $pengajuan->asesmen;

        $hasil = self::firstOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'id_asesmen'   => $asesmen->id,
            ],
            [
                'id_study_program' => $asesmen->id_study_program,
                'id_category'      => $pengajuan->studyProgram->id_category,
                'status'           => 'draft_al',
            ]
        );

        // Pastikan AL sudah ditandai finalized sebelum hitung skor
        $pengajuan->asesmen->asesmenLapangan->update([
            'status'       => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => $authId,
        ]);

        if ($hasil->status === 'draft_al') {
            try {
                DB::beginTransaction();

                // Hitung AK jika belum ada
                if (!$hasil->skor_ak) {
                    $hasilService->saveHasilAK($asesmen, $authId);
                    $hasil->refresh();
                }

                // Hitung AL
                $hasilService->saveHasilAL($asesmen, $authId);
                $hasil->refresh();

                // Update status pengajuan
                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('al', 'status_hasil_akreditasi_dihitung', [
                    'peringkat_hasil' => $hasil->peringkat_akreditasi_hasil,
                    'skor_hasil' => $hasil->skor_al,
                    // 'catatan_hasil' => 'Memenuhi seluruh indikator'
                ]);

                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIHITUNG,
                    ],
                    [
                        'changed_by' => $authId,
                        'keterangan' => 'Hasil akreditasi telah dihitung secara otomatis.',
                        'changed_at' => now(),
                    ]
                );

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auto-calculate HasilAkreditasi failed', [
                    'pengajuan_id' => $pengajuan->id,
                    'error'        => $e->getMessage(),
                    'trace'        => $e->getTraceAsString(),
                ]);
            }
        }

        return $hasil;
    }
}
