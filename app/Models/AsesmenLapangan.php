<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AsesmenLapangan extends Model
{
    use HasFactory;

    protected $table = 'asesmen_lapangan';

    protected $fillable = [
        'id_asesmen',
        'id_asesmen_kecukupan',
        'code',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'alamat',
        'koordinat',
        'status',
        'agenda',
        'catatan',
        'hasil_asesmen',
        'link_laporan',
        'link_dokumentasi',
        'finalized_at',
        'finalized_by',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'finalized_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    public function asesmenKecukupan()
    {
        return $this->belongsTo(AsesmenKecukupan::class, 'id_asesmen_kecukupan');
    }

    public function userRoles()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen_lapangan');
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

        if ($asesorCount < 2) {
            $missing[] = 'Kurang ' . (2 - $asesorCount) . ' asesor';
        }

        return $missing;
    }

    public function hasMinimumRequirementsWithCounts(int $asesorCount): bool
    {
        return $asesorCount >= 2;
    }

    public function getMissingRequirementsWithCounts(int $asesorCount): array
    {
        $missing = [];

        if ($asesorCount < 2) $missing[] = 'Kurang ' . (2 - $asesorCount) . ' asesor';

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

    public function isInProgress()
    {
        return $this->status === 'active';
    }

    public function isFinalized()
    {
        return $this->status === 'finalized';
    }
}
