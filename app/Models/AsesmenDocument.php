<?php
// app/Models/AsesmenDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenDocument extends Model
{
    protected $fillable = [
        'id_asesmen',
        'type',
        'title',
        'sort_order',
        'path',
        'original_name',
        'size',
        'mime',
        'is_active',
        'version',
        'uploaded_by',
        'uploaded_at',
        'status_persetujuan_prodi',
        'approved_by_prodi',
        'approved_at_prodi',
        'catatan_prodi',
        'status_persetujuan_de',
        'approved_by_de',
        'approved_at_de',
        'catatan_de',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'approved_at_prodi' => 'datetime',
        'approved_at_de' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the uploader
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the prodi approver
     */
    public function prodiApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_prodi');
    }

    public function deApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_de');
    }

    /**
     * Get the asesmen
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    /**
     * Get status badge class for prodi approval
     */
    public function getStatusProdiBadgeClassAttribute()
    {
        return match ($this->status_persetujuan_prodi) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'revision_required' => 'bg-info',
            'rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label for prodi approval
     */
    public function getStatusProdiLabelAttribute()
    {
        return match ($this->status_persetujuan_prodi) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'revision_required' => 'Perlu Revisi',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }

    /**
     * Check if can be revised (pending or revision_required)
     */
    public function canBeRevised(): bool
    {
        return in_array($this->status_persetujuan_prodi, ['pending', 'revision_required']);
    }

    /**
     * Check if is final status (approved or rejected)
     */
    public function isFinalStatus(): bool
    {
        return in_array($this->status_persetujuan_prodi, ['approved', 'rejected']);
    }
}
