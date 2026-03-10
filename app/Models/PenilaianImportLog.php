<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_asesmen',
        'id_asesor',
        'filename',
        'status',
        'total_rows',
        'imported_rows',
        'failed_rows',
        'errors',
        'errors_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'errors_message' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the asesmen
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    /**
     * Get the user
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'id_asesor');
    }

    /**
     * Check if import is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if import is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if import is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }

        return round(($this->imported_rows / $this->total_rows) * 100, 2);
    }
}
