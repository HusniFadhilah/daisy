<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BanptSyncRun extends Model
{
    protected $table = 'banpt_sync_runs';

    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'started_at',
        'finished_at',
        'status',
        'checked_count',
        'changed_count',
        'error_count',
        'message',
        'metadata',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
        'checked_count' => 'integer',
        'changed_count' => 'integer',
        'error_count'   => 'integer',
        'metadata'      => 'array',
    ];

    public function changes(): HasMany
    {
        return $this->hasMany(BanptAccreditationChange::class, 'id_sync_run');
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }
        $seconds = $this->started_at->diffInSeconds($this->finished_at);
        return "{$seconds}s";
    }
}
