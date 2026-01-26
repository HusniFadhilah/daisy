<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilAkreditasi extends Model
{
    protected $table = 'hasil_akreditasi';

    protected $fillable = [
        'id_pengajuan',
        'id_asesmen',
        'id_study_program',
        'id_category',
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
        'skor_final',
        'peringkat_akreditasi',
        'memenuhi_syarat_unggul',
        'catatan_validasi',
        'status',
        'catatan_perhitungan',
        'metadata',
    ];

    protected $casts = [
        'detail_skor_ak' => 'array',
        'detail_skor_al' => 'array',
        'pelampauan_standar_ak' => 'array',
        'pelampauan_standar_al' => 'array',
        'metadata' => 'array',
        'tanggal_finalisasi_ak' => 'datetime',
        'tanggal_finalisasi_al' => 'datetime',
        'skor_ak' => 'decimal:2',
        'skor_ak_tertimbang' => 'decimal:2',
        'skor_al' => 'decimal:2',
        'skor_al_tertimbang' => 'decimal:2',
        'skor_final' => 'decimal:2',
        'memenuhi_syarat_unggul' => 'boolean',
    ];

    /**
     * ✅ Konstanta kriteria yang harus ada pelampauan
     */
    public const KRITERIA_REQUIRED = ['D', 'E', 'P', 'I', 'L', 'A', 'R'];
    public const TIDAK_TERAKREDITASI = 'Tidak Terakreditasi';
    public const SEMENTARA_2 = 'Terakreditasi Sementara (2 Tahun)';
    public const TERAKREDITASI_5 = 'Terakreditasi (5 Tahun)';
    public const UNGGUL_2_SYARAT = 'Terakreditasi Unggul 2 Tahun (dengan Syarat)';
    public const UNGGUL_5 = 'Terakreditasi Unggul (5 Tahun)';

    // Relations
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

    public function finalizedAkBy()
    {
        return $this->belongsTo(User::class, 'finalized_ak_by');
    }

    public function finalizedAlBy()
    {
        return $this->belongsTo(User::class, 'finalized_al_by');
    }

    // Helpers
    public function getPeringkatFromSkor(float $skor): string
    {
        if ($skor < 200) {
            return 'Tidak Terakreditasi';
        }

        if ($skor <= 300) {
            return 'Terakreditasi Sementara (2 Tahun)';
        }

        if ($skor <= 350) {
            return 'Terakreditasi (5 Tahun)';
        }

        // 351–360
        if ($skor <= 360) {
            return $this->memenuhi_syarat_unggul
                ? 'Terakreditasi Unggul 2 Tahun (dengan Syarat)'
                : 'Terakreditasi (5 Tahun)';
        }

        // 361–400
        return $this->memenuhi_syarat_unggul
            ? 'Terakreditasi Unggul (5 Tahun)'
            : 'Terakreditasi (5 Tahun)';
    }

    public static function getStatusHasilAkreditasi($asesmen): string
    {
        $ak = $asesmen->asesmenKecukupan->status ?? null;
        $al = $asesmen->asesmenLapangan->status ?? null;

        return match (true) {
            $ak === 'completed' && $al === 'completed' => 'final_combined',
            $al === 'completed'                        => 'final_al',
            $ak === 'completed'                        => 'final_ak',
            $al === 'active'                           => 'draft_al',
            $ak === 'active'                           => 'draft_ak',
            default                                    => 'draft',
        };
    }

    public function isAkFinalized(): bool
    {
        return in_array($this->status, ['final_ak', 'draft_al', 'final_al', 'final_combined', 'published']);
    }

    public function isAlFinalized(): bool
    {
        return in_array($this->status, ['final_al', 'final_combined', 'published']);
    }

    public function isFinalCombined(): bool
    {
        return in_array($this->status, ['final_combined', 'published']);
    }

    /**
     * ✅ Check apakah memenuhi syarat Unggul
     */
    public function memenuhi_syarat_unggul_check(): bool
    {
        // Harus ada data pelampauan standar AL
        if (empty($this->pelampauan_standar_al)) {
            return false;
        }

        $pelampauan = $this->pelampauan_standar_al;

        // Check setiap kriteria
        foreach (self::KRITERIA_REQUIRED as $kriteria) {
            // Harus ada minimal 1 elemen dengan skor 4
            if (!isset($pelampauan[$kriteria]) || empty($pelampauan[$kriteria])) {
                return false;
            }
        }

        return true;
    }

    /**
     * ✅ Get missing kriteria untuk Unggul
     */
    public function getMissingKriteriaForUnggul(): array
    {
        if (empty($this->pelampauan_standar_al)) {
            return self::KRITERIA_REQUIRED;
        }

        $missing = [];
        $pelampauan = $this->pelampauan_standar_al;

        foreach (self::KRITERIA_REQUIRED as $kriteria) {
            if (!isset($pelampauan[$kriteria]) || empty($pelampauan[$kriteria])) {
                $missing[] = $kriteria;
            }
        }

        return $missing;
    }

    /**
     * ✅ Get peringkat from skor AL dengan validasi syarat Unggul
     */
    public function getPeringkatFromSkorAL(float $skor): string
    {
        if ($skor <= 250) {
            return 'Tidak Terakreditasi';
        }

        if ($skor <= 300) {
            return 'Terakreditasi Sementara (2 Tahun)';
        }

        if ($skor <= 350) {
            return 'Terakreditasi (5 Tahun)';
        }

        // Skor 351–360
        if ($skor <= 360) {
            return $this->memenuhi_syarat_unggul
                ? 'Terakreditasi Unggul 2 Tahun (dengan Syarat)'
                : 'Terakreditasi (5 Tahun)';
        }

        // Skor 361–400
        return $this->memenuhi_syarat_unggul
            ? 'Terakreditasi Unggul (5 Tahun)'
            : 'Terakreditasi (5 Tahun)';
    }
}
