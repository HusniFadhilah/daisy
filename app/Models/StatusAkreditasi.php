<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusAkreditasi extends Model
{
    protected $table = 'status_akreditasi';

    protected $fillable = [
        'skor_min',
        'skor_max',
        'persen_min',
        'persen_max',
        'makna',
        'status',
        'siklus_tahun',
        'urutan',
    ];

    protected $casts = [
        'skor_min' => 'integer',
        'skor_max' => 'integer',
        'persen_min' => 'integer',
        'persen_max' => 'integer',
        'siklus_tahun' => 'integer',
        'urutan' => 'integer',
    ];

    public function scopeBySkor($query, int $skor)
    {
        return $query->where('skor_min', '<=', $skor)
            ->where('skor_max', '>=', $skor);
    }

    public function scopeByPersen($query, int $persen)
    {
        return $query->where('persen_min', '<=', $persen)
            ->where('persen_max', '>=', $persen);
    }
}
