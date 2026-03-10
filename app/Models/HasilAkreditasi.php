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
        'id_status_ak_banding',
        'id_status_al_banding',
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
        'skor_ak_banding',
        'skor_ak_banding_tertimbang',
        'total_bobot_ak_banding',
        'detail_skor_ak_banding',
        'pelampauan_standar_ak_banding',
        'tanggal_finalisasi_ak_banding',
        'finalized_ak_banding_by',
        'skor_al_banding',
        'skor_al_banding_tertimbang',
        'total_bobot_al_banding',
        'detail_skor_al_banding',
        'pelampauan_standar_al_banding',
        'tanggal_finalisasi_al_banding',
        'finalized_al_banding_by',
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
        'catatan_penetapan',         // ← tambah
        'tanggal_finalisasi_penetapan', // ← tambah
        'finalized_penetapan_by',    // ← tambah
        'status',
        'catatan_perhitungan',
        'metadata',
    ];

    protected $casts = [
        'detail_skor_ak'             => 'array',
        'detail_skor_al'             => 'array',
        'detail_skor_ak_banding'             => 'array',
        'detail_skor_al_banding'             => 'array',
        'detail_skor_hasil'          => 'array',
        'detail_skor_final'          => 'array',
        'pelampauan_standar_ak'      => 'array',
        'pelampauan_standar_al'      => 'array',
        'pelampauan_standar_ak_banding'      => 'array',
        'pelampauan_standar_al_banding'      => 'array',
        'pelampauan_standar_hasil'   => 'array',
        'pelampauan_standar_final'   => 'array',
        'metadata'                   => 'array',
        'tanggal_finalisasi_ak'      => 'datetime',
        'tanggal_finalisasi_al'      => 'datetime',
        'tanggal_finalisasi_ak_banding'      => 'datetime',
        'tanggal_finalisasi_al_banding'      => 'datetime',
        'tanggal_finalisasi_hasil'   => 'datetime',
        'tanggal_finalisasi_penetapan' => 'datetime', // ← tambah
        'skor_ak'                    => 'decimal:2',
        'skor_ak_tertimbang'         => 'decimal:2',
        'skor_al'                    => 'decimal:2',
        'skor_al_tertimbang'         => 'decimal:2',
        'skor_ak_banding'                    => 'decimal:2',
        'skor_ak_banding_tertimbang'         => 'decimal:2',
        'skor_al_banding'                    => 'decimal:2',
        'skor_al_banding_tertimbang'         => 'decimal:2',
        'skor_hasil'                 => 'decimal:2',
        'skor_hasil_tertimbang'      => 'decimal:2',
        'skor_final'                 => 'decimal:2',
        'skor_final_tertimbang'      => 'decimal:2',
        'memenuhi_syarat_unggul'     => 'boolean',
    ];

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

    public function statusAkBanding()
    {
        return $this->belongsTo(StatusAkreditasi::class, 'id_status_ak_banding');
    }

    public function statusAlBanding()
    {
        return $this->belongsTo(StatusAkreditasi::class, 'id_status_al_banding');
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

    public function finalizedAkBandingBy()
    {
        return $this->belongsTo(User::class, 'finalized_ak_banding_by');
    }

    public function finalizedAlBandingBy()
    {
        return $this->belongsTo(User::class, 'finalized_al_banding_by');
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
            'final_hasil',
            'draft_ak_banding',
            'final_ak_banding',
            'draft_al_banding',
            'final_al_banding',
            'draft_penetapan',
            'final_penetapan',
            'published',
        ]);
    }

    public function isAlFinalized(): bool
    {
        return in_array($this->status, [
            'final_al',
            'final_hasil',
            'draft_ak_banding',
            'final_ak_banding',
            'draft_al_banding',
            'final_al_banding',
            'draft_penetapan',
            'final_penetapan',
            'published',
        ]);
    }

    public function isHasilFinalized(): bool
    {
        return in_array($this->status, [
            'final_hasil',
            'draft_ak_banding',
            'final_ak_banding',
            'draft_al_banding',
            'final_al_banding',
            'draft_penetapan',
            'final_penetapan',
            'published',
        ]);
    }

    public function isAkBandingFinalized(): bool
    {
        return in_array($this->status, [
            'final_ak_banding',
            'draft_al_banding',
            'final_al_banding',
            'draft_penetapan',
            'final_penetapan',
            'published',
        ]);
    }

    public function isAlBandingFinalized(): bool
    {
        return in_array($this->status, [
            'final_al',
            'final_hasil',
            'draft_ak_banding',
            'final_ak_banding',
            'draft_al_banding',
            'final_al_banding',
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

    public function isPenetapanDraft(): bool
    {
        return $this->status === 'draft_penetapan';
    }

    public function isPenetapanFinalized(): bool
    {
        return in_array($this->status, ['final_penetapan', 'published']);
    }

    public static function getStatusHasilAkreditasi($asesmen): string
    {
        $ak = $asesmen->asesmenKecukupan->status ?? null;
        $al = $asesmen->asesmenLapangan->status  ?? null;
        $akBanding = $asesmen->asesmenKecukupanBanding->status ?? null;
        $alBanding = $asesmen->asesmenLapanganBanding->status  ?? null;

        return match (true) {
            $al === 'completed'                        => 'final_al',
            $ak === 'completed'                        => 'final_ak',
            $al === 'active'                           => 'draft_al',
            $ak === 'active'                           => 'draft_ak',
            $alBanding === 'completed'                 => 'final_al_banding',
            $akBanding === 'completed'                 => 'final_ak_banding',
            $alBanding === 'active'                    => 'draft_al_banding',
            $akBanding === 'active'                    => 'draft_ak_banding',
            default                                    => 'draft',
        };
    }

    // =========================================================
    // SYARAT PELAMPAUAN STANDAR
    // =========================================================

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

    /** @deprecated Gunakan memenuhi_syarat_pelampauan() */
    public function memenuhi_syarat_unggul_check(): bool
    {
        return $this->memenuhi_syarat_pelampauan();
    }

    // =========================================================
    // PERINGKAT
    // =========================================================

    /**
     * Satu-satunya method untuk menentukan peringkat dari skor.
     *
     * Syarat Kunci  : skor masuk range status di DB
     * Syarat Perlu  : hanya berlaku jika status yang match adalah Unggul
     *                 → $memenuhiPelampauan && $memenuhiP1 harus true
     *
     * Default kedua syarat perlu = false → aman, tidak akan pernah
     * mengembalikan Unggul kecuali eksplisit dipenuhi.
     */
    public function getPeringkatFromSkor(
        float $skor,
        bool  $memenuhiPelampauan = false,
        bool  $memenuhiP1 = false
    ): string {
        $allStatus = $this->getAllStatusAkreditasi();

        // Cari status yang range-nya mencakup skor
        $statusMatch = $allStatus
            ->filter(fn($s) => (float)$s->skor_min <= $skor && (float)$s->skor_max >= $skor)
            ->sortByDesc('skor_min')
            ->first();

        if (!$statusMatch) {
            return 'Tidak Terakreditasi';
        }

        // Bukan Unggul → langsung return, syarat perlu tidak relevan
        if (!$this->isStatusUnggul($statusMatch)) {
            return $statusMatch->status;
        }

        // ── Status match = Unggul: cek syarat perlu ──
        if ($memenuhiPelampauan && $memenuhiP1) {
            return $statusMatch->status;
        }

        // ── Downgrade: syarat perlu tidak terpenuhi ──
        $batasUnggul = $allStatus
            ->filter(fn($s) => $this->isStatusUnggul($s))
            ->min('skor_min');

        $downgrade = $allStatus
            ->filter(fn($s) => !$this->isStatusUnggul($s) && (float)$s->skor_max < (float)$batasUnggul)
            ->sortByDesc('skor_max')
            ->first();

        return $downgrade?->status ?? 'Terakreditasi';
    }

    /**
     * Alias semantik — untuk keterbacaan di service layer.
     * Sepenuhnya delegate ke getPeringkatFromSkor.
     */
    public function getPeringkatFromSkorAL(
        float $skor,
        bool  $memenuhiPelampauan = false,
        bool  $memenuhiP1 = false
    ): string {
        return $this->getPeringkatFromSkor($skor, $memenuhiPelampauan, $memenuhiP1);
    }

    private function isStatusUnggul(StatusAkreditasi $status): bool
    {
        return str_contains(strtolower($status->status), 'unggul');
    }

    private function getAllStatusAkreditasi(): \Illuminate\Support\Collection
    {
        static $cache = null;
        return $cache ??= StatusAkreditasi::all();
    }

    // =========================================================
    // TAMPILAN
    // =========================================================

    public function getPeringkatColor(): string
    {
        return $this->statusFinal?->warna
            ?? $this->statusBanding?->warna
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

        $pengajuan->asesmen->asesmenLapangan->update([
            'status'       => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => $authId,
        ]);

        if ($hasil->status === 'draft_al') {
            try {
                DB::beginTransaction();

                if (!$hasil->skor_ak) {
                    $hasilService->saveHasilAK($asesmen, $authId);
                    $hasil->refresh();
                }

                $hasilService->saveHasilAL($asesmen, $authId);
                $hasil->refresh();

                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('al', 'status_hasil_akreditasi_dihitung', [
                    'peringkat_hasil' => $hasil->peringkat_akreditasi_hasil,
                    'skor_hasil'      => $hasil->skor_al,
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

    public static function initializeBanding($hasilService, $pengajuan, $authId): self
    {
        $asesmen = $pengajuan->asesmen;
        $hasil = $asesmen->hasil;
        $pengajuan->asesmen->asesmenLapanganBanding->update([
            'status'       => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => $authId,
        ]);

        if ($hasil->status === 'draft_al_banding') {
            try {
                DB::beginTransaction();

                $hasilService->saveHasilAlBanding($asesmen, $authId);
                $hasil->refresh();

                $statusFrom = $pengajuan->status;
                $pengajuan->update([
                    'peringkat_banding' => $hasil->peringkat_akreditasi_banding,
                    'skor_banding'      => $hasil->skor_banding,
                ]);

                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_HASIL_BANDING_DIHITUNG,
                    ],
                    [
                        'changed_by' => $authId,
                        'keterangan' => 'Hasil banding telah dihitung secara otomatis.',
                        'changed_at' => now(),
                    ]
                );

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auto-calculate HasilBanding failed', [
                    'pengajuan_id' => $pengajuan->id,
                    'error'        => $e->getMessage(),
                    'trace'        => $e->getTraceAsString(),
                ]);
            }
        }

        return $hasil;
    }

    public function getKriteriaOrderedList()
    {
        $detailSkorAL = $this->detail_skor_al ?? [];
        $kriteriaList = $detailSkorAL['kriteria'] ?? [];
        $elemenList   = $detailSkorAL['elemen']   ?? [];
        $kriteriaOrdered = [];
        foreach ($elemenList as $elemen) {
            $kode = $elemen['kode_kriteria'];
            if (!isset($kriteriaOrdered[$kode]) && isset($kriteriaList[$kode])) {
                $kriteriaOrdered[$kode] = $kriteriaList[$kode];
            }
        }
        return $kriteriaOrdered;
    }
}
