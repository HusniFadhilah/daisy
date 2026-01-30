<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasUuids;

    /**
     * Table name
     */
    protected $table = 'notifications';

    /**
     * Primary key type
     */
    protected $keyType = 'string';

    /**
     * Disable auto increment
     */
    public $incrementing = false;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'id',
        'type',
        'notifiable_id',
        'notifiable_type',
        'data',
        'read_at',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Morph relation (User, Admin, dll)
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Helper: mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'read_at' => now(),
        ]);
    }

    /**
     * Helper: check unread status
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }
}
