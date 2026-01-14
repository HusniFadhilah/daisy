<?php
// app/Models/BorangValidation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangValidation extends Model
{
    protected $table = 'borang_validations';

    protected $fillable = [
        'id_assignment',
        'id_pengajuan',
        'review_led',
        'review_suplemen',
        'review_lkps',
        'catatan_led',
        'catatan_suplemen',
        'catatan_lkps',
        'revision_points',
        'catatan_validator',
        'total_sections',
        'validated_sections',
        'total_elemen_led',
        'total_elemen_suplemen',
        'total_indikator_lkps',
        'reviewed_led',
        'reviewed_suplemen',
        'reviewed_lkps',
    ];

    protected $casts = [
        'review_led' => 'array',
        'review_suplemen' => 'array',
        'review_lkps' => 'array',
        'revision_points' => 'array',
    ];

    public function assignment()
    {
        return $this->belongsTo(AsesmenUserRole::class, 'id_assignment');
    }

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    /**
     * Get review progress percentage
     */
    public function getProgressPercentage(): array
    {
        $ledTotal = (int) $this->total_elemen_led;
        $supTotal = (int) $this->total_elemen_suplemen;
        $lkpsTotal = (int) $this->total_indikator_lkps;

        $ledReviewed = (int) $this->reviewed_led;
        $supReviewed = (int) $this->reviewed_suplemen;
        $lkpsReviewed = (int) $this->reviewed_lkps;

        $total = $ledTotal + $supTotal + $lkpsTotal;
        $reviewed = $ledReviewed + $supReviewed + $lkpsReviewed;

        $pct = fn(int $rev, int $tot) => $tot > 0 ? (int) round(($rev / $tot) * 100) : 100;

        return [
            'total' => $total,
            'reviewed' => $reviewed,
            'percentage' => $total > 0 ? (int) round(($reviewed / $total) * 100) : 100,

            'led_percentage' => $pct($ledReviewed, $ledTotal),
            'suplemen_percentage' => $pct($supReviewed, $supTotal),
            'lkps_percentage' => $pct($lkpsReviewed, $lkpsTotal),
        ];
    }

    /**
     * Check if all items reviewed
     */
    public function isCompletelyReviewed(): bool
    {
        return
            (int)$this->reviewed_led >= (int)$this->total_elemen_led &&
            (int)$this->reviewed_suplemen >= (int)$this->total_elemen_suplemen &&
            (int)$this->reviewed_lkps >= (int)$this->total_indikator_lkps;
    }

    /**
     * Get items that need revision (grade A or B)
     */
    public function getNeedsRevisionItems(): array
    {
        $needsRevision = [
            'led' => [],
            'suplemen' => [],
            'lkps' => [],
        ];

        // LED
        if ($this->review_led) {
            foreach ($this->review_led as $elemenId => $review) {
                if (in_array($review['grade'] ?? null, ['A', 'B'])) {
                    $needsRevision['led'][] = [
                        'elemen_id' => $elemenId,
                        'grade' => $review['grade'],
                        'catatan' => $review['catatan'] ?? null,
                    ];
                }
            }
        }

        // Suplemen
        if ($this->review_suplemen) {
            foreach ($this->review_suplemen as $elemenId => $review) {
                if (in_array($review['grade'] ?? null, ['A', 'B'])) {
                    $needsRevision['suplemen'][] = [
                        'elemen_id' => $elemenId,
                        'grade' => $review['grade'],
                        'catatan' => $review['catatan'] ?? null,
                    ];
                }
            }
        }

        // LKPS
        if ($this->review_lkps) {
            foreach ($this->review_lkps as $indikatorId => $review) {
                if (in_array($review['grade'] ?? null, ['A', 'B'])) {
                    $needsRevision['lkps'][] = [
                        'indikator_id' => $indikatorId,
                        'grade' => $review['grade'],
                        'catatan' => $review['catatan'] ?? null,
                    ];
                }
            }
        }

        return $needsRevision;
    }

    /**
     * Check if validation passed (all items grade C)
     */
    public function isValidationPassed(): bool
    {
        $needsRevision = $this->getNeedsRevisionItems();

        return empty($needsRevision['led']) &&
            empty($needsRevision['suplemen']) &&
            empty($needsRevision['lkps']);
    }

    /**
     * Get grade label
     */
    public static function getGradeLabel(string $grade): string
    {
        return match ($grade) {
            'A' => 'Perlu diperbaiki',
            'B' => 'Kurang lengkap, perlu dilengkapi',
            'C' => 'Sudah tepat',
            default => 'Belum direview',
        };
    }

    /**
     * Get grade badge class
     */
    public static function getGradeBadgeClass(string $grade): string
    {
        return match ($grade) {
            'A' => 'bg-danger',
            'B' => 'bg-warning',
            'C' => 'bg-success',
            default => 'bg-secondary',
        };
    }
}
