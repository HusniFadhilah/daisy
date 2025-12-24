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
        'tipe_field',
        'label_field',
        'placeholder',
        'is_required',
        'options',
        'keterangan',
        'expected_columns',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'expected_columns' => 'array',
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relations
    public function elemen()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }

    public function borangTables()
    {
        return $this->hasMany(BorangTable::class, 'id_dataset');
    }

    public function borangData()
    {
        return $this->hasMany(BorangData::class, 'id_dataset_borang');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForElemen($query, $elemenId)
    {
        return $query->where('id_elemen', $elemenId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
}
