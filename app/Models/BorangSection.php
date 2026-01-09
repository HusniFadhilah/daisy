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
}
