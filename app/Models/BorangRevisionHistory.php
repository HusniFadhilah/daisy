<?php
// app/Models/BorangRevisionHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BorangRevisionHistory extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'borang_revision_history';

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'id_pengajuan',
        'id_validation',
        'revision_number',
        'revised_sections',
        'revision_notes',
        'revised_by',
        'revised_at',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'revised_sections' => 'array',
        'revised_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    /**
     * Belongs to Pengajuan
     */
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    /**
     * Belongs to Validation
     */
    public function validation()
    {
        return $this->belongsTo(BorangValidation::class, 'id_validation');
    }

    /**
     * Revised by User (Prodi)
     */
    public function revisedBy()
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    /**
     * Scope: By Pengajuan
     */
    public function scopeByPengajuan($query, $pengajuanId)
    {
        return $query->where('id_pengajuan', $pengajuanId);
    }

    /**
     * Scope: By Validation
     */
    public function scopeByValidation($query, $validationId)
    {
        return $query->where('id_validation', $validationId);
    }

    /**
     * Scope: Latest First
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('revision_number', 'desc');
    }

    /**
     * Scope: Oldest First
     */
    public function scopeOldestFirst($query)
    {
        return $query->orderBy('revision_number', 'asc');
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    /**
     * Get formatted revision number
     * e.g., "Revisi #1", "Revisi #2"
     */
    public function getRevisionLabelAttribute()
    {
        return "Revisi #{$this->revision_number}";
    }

    /**
     * Get human readable time
     * e.g., "2 hari yang lalu"
     */
    public function getRevisedTimeAttribute()
    {
        return $this->revised_at ? $this->revised_at->diffForHumans() : null;
    }

    /**
     * Get formatted date
     * e.g., "15 Des 2024 14:30"
     */
    public function getRevisedDateFormattedAttribute()
    {
        return $this->revised_at ? $this->revised_at->format('d M Y H:i') : null;
    }

    /**
     * Get count of revised sections
     */
    public function getRevisedSectionsCountAttribute()
    {
        return is_array($this->revised_sections) ? count($this->revised_sections) : 0;
    }

    /**
     * ========================================
     * STATIC METHODS
     * ========================================
     */

    /**
     * Get next revision number for pengajuan
     */
    public static function getNextRevisionNumber($pengajuanId, $validationId)
    {
        $lastRevision = self::where('id_pengajuan', $pengajuanId)
            ->where('id_validation', $validationId)
            ->max('revision_number');

        return ($lastRevision ?? 0) + 1;
    }

    /**
     * Get total revisions for pengajuan
     */
    public static function getTotalRevisions($pengajuanId, $validationId = null)
    {
        $query = self::where('id_pengajuan', $pengajuanId);

        if ($validationId) {
            $query->where('id_validation', $validationId);
        }

        return $query->count();
    }

    /**
     * Get latest revision
     */
    public static function getLatestRevision($pengajuanId, $validationId)
    {
        return self::where('id_pengajuan', $pengajuanId)
            ->where('id_validation', $validationId)
            ->orderBy('revision_number', 'desc')
            ->first();
    }

    /**
     * ========================================
     * INSTANCE METHODS
     * ========================================
     */

    /**
     * Check if this is the first revision
     */
    public function isFirstRevision()
    {
        return $this->revision_number === 1;
    }

    /**
     * Check if this is the latest revision
     */
    public function isLatestRevision()
    {
        $latestRevision = self::where('id_pengajuan', $this->id_pengajuan)
            ->where('id_validation', $this->id_validation)
            ->max('revision_number');

        return $this->revision_number === $latestRevision;
    }

    /**
     * Get previous revision
     */
    public function getPreviousRevision()
    {
        return self::where('id_pengajuan', $this->id_pengajuan)
            ->where('id_validation', $this->id_validation)
            ->where('revision_number', '<', $this->revision_number)
            ->orderBy('revision_number', 'desc')
            ->first();
    }

    /**
     * Get next revision
     */
    public function getNextRevision()
    {
        return self::where('id_pengajuan', $this->id_pengajuan)
            ->where('id_validation', $this->id_validation)
            ->where('revision_number', '>', $this->revision_number)
            ->orderBy('revision_number', 'asc')
            ->first();
    }

    /**
     * Check if section was revised
     */
    public function isSectionRevised($sectionCode)
    {
        if (!is_array($this->revised_sections)) {
            return false;
        }

        // Check if section code exists in array (either as value or as key)
        return in_array($sectionCode, $this->revised_sections) ||
            array_key_exists($sectionCode, $this->revised_sections);
    }

    /**
     * Get section-specific revision notes
     */
    public function getSectionNotes($sectionCode)
    {
        if (!is_array($this->revised_sections)) {
            return null;
        }

        // If revised_sections is associative array: ['E.1' => 'notes', 'E.2' => 'notes']
        if (isset($this->revised_sections[$sectionCode])) {
            return $this->revised_sections[$sectionCode];
        }

        return null;
    }

    /**
     * ========================================
     * BOOT METHOD
     * ========================================
     */

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set revision_number on creating
        static::creating(function ($revision) {
            if (!$revision->revision_number) {
                $revision->revision_number = self::getNextRevisionNumber(
                    $revision->id_pengajuan,
                    $revision->id_validation
                );
            }

            // Auto-set revised_at if not set
            if (!$revision->revised_at) {
                $revision->revised_at = now();
            }
        });
    }
}
