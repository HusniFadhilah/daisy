<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AsesmenKecukupan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asesmen_kecukupan';

    protected $fillable = [
        'id_asesmen',
        'code',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
        'hasil_asesmen',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    public function userRoles()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen_kecukupan');
    }

    public function asesors()
    {
        return $this->userRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->where('status_penawaran', 'accepted');
    }

    public function validators()
    {
        return $this->userRoles()
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->where('status_penawaran', 'accepted');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Helpers
    public function hasMinimumRequirements()
    {
        $asesorCount = $this->asesors()->count();
        $validatorCount = $this->validators()->count();

        return $asesorCount >= 2 && $validatorCount >= 1;
    }

    public function getMissingRequirements()
    {
        $missing = [];

        $asesorCount = $this->asesors()->count();
        $validatorCount = $this->validators()->count();

        if ($asesorCount < 2) {
            $missing[] = 'Kurang ' . (2 - $asesorCount) . ' asesor';
        }

        if ($validatorCount < 1) {
            $missing[] = 'Kurang ' . (1 - $validatorCount) . ' validator';
        }

        return $missing;
    }

    public function hasMinimumRequirementsWithCounts(int $asesorCount, int $validatorCount): bool
    {
        return $asesorCount >= 2 && $validatorCount >= 1;
    }

    public function getMissingRequirementsWithCounts(int $asesorCount, int $validatorCount): array
    {
        $missing = [];

        if ($asesorCount < 2) $missing[] = 'Kurang ' . (2 - $asesorCount) . ' asesor';
        if ($validatorCount < 1) $missing[] = 'Kurang ' . (1 - $validatorCount) . ' validator';

        return $missing;
    }

    public function isComplete()
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted($userId = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId ?? auth()->id(),
        ]);
    }
}
