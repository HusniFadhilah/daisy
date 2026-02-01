<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmail extends Model
{
    protected $table = 'user_emails';

    protected $fillable = [
        'user_id',
        'email',
        'is_primary',
        'is_active',
        'verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active'  => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: email yang aktif (dan opsional sudah verified)
     */
    public function scopeActive($query, bool $mustVerified = false)
    {
        $query->where('is_active', true);

        if ($mustVerified) {
            $query->whereNotNull('verified_at');
        }

        return $query;
    }
}
