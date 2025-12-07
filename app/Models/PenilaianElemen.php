<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianElemen extends Model
{
    use HasFactory;

    protected $table = 'penilaian_elemen';

    protected $fillable = [
        'id_asesmen',
        'id_user',
        'id_elemen',
        'skor',
        'komentar',
        'status',
    ];

    protected $casts = [
        'skor' => 'integer',
    ];

    /**
     * Get the asesmen
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class);
    }

    /**
     * Get the user (asesor)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the indikator
     */
    public function elemen()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen', 'id_elemen');
    }

    /**
     * Get skor label
     */
    public function getSkorLabelAttribute()
    {
        $labels = [
            0 => 'Tidak Memenuhi (Not Met)',
            1 => 'Tidak Memenuhi (Not Met)',
            2 => 'Lemah (Weakness/Cause of Concern)',
            3 => 'Memenuhi (Met)',
            4 => 'Pelampauan Standar',
        ];

        return $labels[$this->skor] ?? 'N/A';
    }

    /**
     * Get skor class for styling
     */
    public function getSkorClassAttribute()
    {
        $classes = [
            0 => 'danger',
            1 => 'danger',
            2 => 'warning',
            3 => 'success',
        ];

        return $classes[$this->skor] ?? 'secondary';
    }
}
