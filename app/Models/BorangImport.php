<?php
// app/Models/BorangImport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangImport extends Model
{
    protected $fillable = [
        'id_pengajuan',
        'id_dokumen',
        'original_filename',
        'stored_path',
        'status',
        'total_sections',
        'total_tables',
        'parsed_sections',
        'parsed_tables',
        'parsing_notes',
        'parsing_errors',
        'kata_pengantar',
        'ringkasan',
        'suplemen',
        'imported_by',
        'imported_at',
        'completed_at',
    ];

    protected $casts = [
        'parsing_errors' => 'array',
        'suplemen' => 'array',
        'imported_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function dokumen()
    {
        return $this->belongsTo(PengajuanDokumen::class, 'id_dokumen');
    }

    public function sections()
    {
        return $this->hasMany(BorangSection::class, 'id_import')->orderBy('position');
    }

    public function borangData()
    {
        return $this->hasMany(BorangData::class, 'id_borang_import');
    }

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeForPengajuan($query, $pengajuanId)
    {
        return $query->where('id_pengajuan', $pengajuanId);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getCompletionPercentageAttribute()
    {
        if ($this->total_tables == 0) return 0;
        return round(($this->parsed_tables / $this->total_tables) * 100, 2);
    }

    public function getSectionPercentageAttribute()
    {
        if ($this->total_sections == 0) return 0;
        return round(($this->parsed_sections / $this->total_sections) * 100, 2);
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsFailedAttribute()
    {
        return $this->status === 'failed';
    }

    public function getIsProcessingAttribute()
    {
        return $this->status === 'processing';
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Mark import as processing
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'imported_at' => now()
        ]);
    }

    /**
     * Mark import as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Mark import as failed
     */
    public function markAsFailed($errors = [])
    {
        $this->update([
            'status' => 'failed',
            'parsing_errors' => $errors,
            'completed_at' => now()
        ]);
    }

    /**
     * Add parsing error
     */
    public function addError($error)
    {
        $errors = $this->parsing_errors ?? [];
        $errors[] = [
            'message' => $error,
            'timestamp' => now()->toDateTimeString()
        ];

        $this->update(['parsing_errors' => $errors]);
    }

    /**
     * Increment parsed counters
     */
    public function incrementParsed($sections = 0, $tables = 0)
    {
        $this->increment('parsed_sections', $sections);
        $this->increment('parsed_tables', $tables);
    }

    /**
     * Get import summary
     */
    public function getSummary()
    {
        return [
            'status' => $this->status,
            'filename' => $this->original_filename,
            'sections' => [
                'total' => $this->total_sections,
                'parsed' => $this->parsed_sections,
                'percentage' => $this->section_percentage
            ],
            'tables' => [
                'total' => $this->total_tables,
                'parsed' => $this->parsed_tables,
                'percentage' => $this->completion_percentage
            ],
            'errors' => $this->parsing_errors ?? [],
            'imported_at' => $this->imported_at,
            'completed_at' => $this->completed_at,
        ];
    }
}
