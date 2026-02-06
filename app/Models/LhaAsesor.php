<?php
// app/Models/LhaAsesor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LhaAsesor extends Model
{
    protected $fillable = [
        'id_asesmen',
        'pendahuluan',
        'proses_al',
        'hasil_al',
        'rekomendasi_ps',
        'rekomendasi_lamdepilar',
        'status',
        'created_by',
        'updated_by',
        'submitted_at',
        'finalized_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    /**
     * Get asesmen
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get updater
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if can be edited
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'submitted']);
    }

    /**
     * Check if is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if is finalized
     */
    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage(): int
    {
        $fields = [
            'pendahuluan',
            'proses_al',
            'hasil_al',
            'rekomendasi_ps',
            'rekomendasi_lamdepilar',
        ];

        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $filled++;
            }
        }

        return round(($filled / count($fields)) * 100);
    }
}
