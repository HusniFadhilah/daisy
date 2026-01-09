<?php
// app/Models/DatasetBorang.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatasetBorang extends Model
{
    protected $table = 'dataset_borang';

    protected $fillable = [
        'id_elemen',
        'kode',
        'nama',
        'deskripsi',
        'tipe',
        'expected_columns',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'expected_columns' => 'array',
        'is_active' => 'boolean',
    ];

    public function elemen()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }

    public function borangTables()
    {
        return $this->hasMany(BorangTable::class, 'id_dataset');
    }
}
