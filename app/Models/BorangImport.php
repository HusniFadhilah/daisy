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
        'status',
        'total_sections',
        'total_tables',
        'parsed_sections',
        'parsed_tables',
        'parsing_notes',
        'parsing_errors',
        'imported_by',
        'imported_at',
    ];

    protected $casts = [
        'parsing_errors' => 'array',
        'imported_at' => 'datetime',
    ];

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

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->total_tables == 0) return 0;
        return round(($this->parsed_tables / $this->total_tables) * 100);
    }
}
