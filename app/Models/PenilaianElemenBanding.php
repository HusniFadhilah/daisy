<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianElemenBanding extends Model
{
    use HasFactory;

    protected $table = 'penilaian_elemen_banding';

    protected $fillable = [
        'id_asesmen',
        'id_asesor',
        'id_elemen',
        'skor',
        'komentar',
        'status',
        'is_locked'
    ];

    protected $casts = [
        'skor' => 'integer',
    ];

    /**
     * Get the asesmen
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    /**
     * Get the user (asesor)
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'id_asesor');
    }

    /**
     * Get the elemen standar (alias untuk backward compatibility)
     */
    public function elemenStandar()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen', 'id');
    }

    /**
     * Get the indikator
     */
    public function elemen()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }
}
