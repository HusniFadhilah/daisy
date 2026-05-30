<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BanptAccreditationChange extends Model
{
    protected $table = 'banpt_accreditation_changes';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPLIED  = 'applied';
    public const STATUS_IGNORED  = 'ignored';
    public const STATUS_CONFLICT = 'conflict';

    protected $fillable = [
        'id_sync_run',
        'id_study_program',
        'old_university_name',
        'old_program_studi',
        'old_jenjang',
        'old_peringkat_akreditasi',
        'old_tanggal_kedaluwarsa',
        'old_status_kedaluwarsa',
        'new_university_name',
        'new_program_studi',
        'new_jenjang',
        'new_peringkat_akreditasi',
        'new_tanggal_kedaluwarsa',
        'new_status_kedaluwarsa',
        'banpt_pt_label',
        'banpt_ps_label',
        'banpt_payload',
        'change_hash',
        'status',
        'conflict_reason',
        'detected_at',
        'applied_at',
        'applied_by',
        'ignored_at',
        'ignored_by',
    ];

    protected $casts = [
        'old_tanggal_kedaluwarsa' => 'date',
        'new_tanggal_kedaluwarsa' => 'date',
        'banpt_payload'           => 'array',
        'detected_at'             => 'datetime',
        'applied_at'              => 'datetime',
        'ignored_at'              => 'datetime',
    ];

    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(BanptSyncRun::class, 'id_sync_run');
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'id_study_program');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function ignoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ignored_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isIgnored(): bool
    {
        return $this->status === self::STATUS_IGNORED;
    }

    public function isConflict(): bool
    {
        return $this->status === self::STATUS_CONFLICT;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'Menunggu',
            self::STATUS_APPLIED  => 'Diterapkan',
            self::STATUS_IGNORED  => 'Diabaikan',
            self::STATUS_CONFLICT => 'Konflik',
            default               => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'warning',
            self::STATUS_APPLIED  => 'success',
            self::STATUS_IGNORED  => 'secondary',
            self::STATUS_CONFLICT => 'danger',
            default               => 'light',
        };
    }
}
