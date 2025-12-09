<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidasiPenilaian extends Model
{
    use HasFactory;

    protected $table = 'validasi_penilaian';
    protected $primaryKey = 'id_validasi';

    protected $fillable = [
        'id_penilaian',
        'id_validator',
        'status_validasi',
        'skor_final',
        'catatan_validator',
        'tanggal_validasi',
    ];

    protected $casts = [
        'tanggal_validasi' => 'datetime',
    ];

    /**
     * ============================================
     * RELATIONSHIPS
     * ============================================
     */

    /**
     * Validasi belongs to Penilaian
     */
    public function penilaian()
    {
        return $this->belongsTo(PenilaianElemen::class, 'id_penilaian', 'id_penilaian');
    }

    /**
     * Validasi belongs to Validator (User)
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'id_validator', 'id');
    }

    /**
     * ============================================
     * SCOPES
     * ============================================
     */

    /**
     * Scope untuk status validasi
     */
    public function scopeValidated($query)
    {
        return $query->where('status_validasi', 'validated');
    }

    /**
     * Scope untuk yang perlu revisi
     */
    public function scopeNeedsRevision($query)
    {
        return $query->where('status_validasi', 'revision_needed');
    }

    /**
     * Scope untuk belum divalidasi
     */
    public function scopeNotValidated($query)
    {
        return $query->where('status_validasi', 'not_validated');
    }

    /**
     * ============================================
     * ACCESSORS
     * ============================================
     */

    /**
     * Get badge color based on status
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'not_validated' => ['class' => 'bg-secondary', 'text' => 'Belum Validasi'],
            'validated' => ['class' => 'bg-success', 'text' => 'Disetujui'],
            'revision_needed' => ['class' => 'bg-warning', 'text' => 'Perlu Revisi'],
        ];

        return $badges[$this->status_validasi] ?? $badges['not_validated'];
    }

    /**
     * ============================================
     * METHODS
     * ============================================
     */

    /**
     * Check if validated
     */
    public function isValidated()
    {
        return $this->status_validasi === 'validated';
    }

    /**
     * Check if needs revision
     */
    public function needsRevision()
    {
        return $this->status_validasi === 'revision_needed';
    }
}
