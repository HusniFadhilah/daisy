<?php
// app/Models/BorangSection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangSection extends Model
{
    protected $fillable = [
        'id_import',
        'id_elemen',
        'kode_section',
        'judul_section',
        'konten_narasi',
        'position',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function import()
    {
        return $this->belongsTo(BorangImport::class, 'id_import');
    }

    public function elemen()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }

    public function tables()
    {
        return $this->hasMany(BorangTable::class, 'id_section')->orderBy('position');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function scopeForElemen($query, $elemenId)
    {
        return $query->where('id_elemen', $elemenId);
    }

    public function scopeWithTables($query)
    {
        return $query->with('tables');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getHasNarasiAttribute()
    {
        return !empty($this->konten_narasi);
    }

    public function getTableCountAttribute()
    {
        return $this->tables()->count();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Add table to this section
     */
    public function addTable($tableData)
    {
        return $this->tables()->create($tableData);
    }

    /**
     * Get next position for table
     */
    public function getNextTablePosition()
    {
        return $this->tables()->max('position') + 1;
    }
}
