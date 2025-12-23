<?php
// app/Models/BorangTable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangTable extends Model
{
    protected $fillable = [
        'id_section',
        'id_dataset',
        'kode_tabel',
        'judul_tabel',
        'row_count',
        'col_count',
        'headers',
        'data',
        'position',
    ];

    protected $casts = [
        'headers' => 'array',
        'data' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(BorangSection::class, 'id_section');
    }

    public function dataset()
    {
        return $this->belongsTo(DatasetBorang::class, 'id_dataset');
    }
}
