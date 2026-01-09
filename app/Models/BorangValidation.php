<?php
// app/Models/BorangValidation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangValidation extends Model
{
    protected $fillable = [
        'id_assignment',
        'id_pengajuan',
        'checklist_items',
        'revision_points',
        'catatan_validator',
        'total_sections',
        'validated_sections',
    ];

    protected $casts = [
        'checklist_items' => 'array',
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

    public function revisionHistory()
    {
        return $this->hasMany(BorangRevisionHistory::class, 'id_validation')
            ->orderBy('revision_number', 'desc');
    }

    public function getLatestRevision()
    {
        return $this->revisionHistory()->first();
    }

    public function getTotalRevisions()
    {
        return $this->revisionHistory()->count();
    }

    public function hasRevisions()
    {
        return $this->revisionHistory()->exists();
    }
}
