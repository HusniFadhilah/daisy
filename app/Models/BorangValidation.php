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
        $totalItems = $this->total_elemen_led + $this->total_elemen_suplemen + $this->total_indikator_lkps;
        $reviewedItems = $this->reviewed_led + $this->reviewed_suplemen + $this->reviewed_lkps;

        return [
            'total' => $totalItems,
            'reviewed' => $reviewedItems,
            'percentage' => $totalItems > 0 ? round(($reviewedItems / $totalItems) * 100, 2) : 0,
            'led_percentage' => $this->total_elemen_led > 0 ? round(($this->reviewed_led / $this->total_elemen_led) * 100, 2) : 0,
            'suplemen_percentage' => $this->total_elemen_suplemen > 0 ? round(($this->reviewed_suplemen / $this->total_elemen_suplemen) * 100, 2) : 0,
            'lkps_percentage' => $this->total_indikator_lkps > 0 ? round(($this->reviewed_lkps / $this->total_indikator_lkps) * 100, 2) : 0,
        ];
    }

    /**
     * Check if all items reviewed
     */
    public function isCompletelyReviewed(): bool
    {
        return ($this->reviewed_led === $this->total_elemen_led) &&
            ($this->reviewed_suplemen === $this->total_elemen_suplemen) &&
            ($this->reviewed_lkps === $this->total_indikator_lkps);
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
