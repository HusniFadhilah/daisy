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
        'id_asesor',
        'id_elemen',
        'skor',
        'komentar',
        'status',
        'status_validasi',
        'preferensi_skor',
        'skor_final',
        'catatan_validator',
        'validated_by',
        'validated_at',
        'validation_note',
        'revision_count',
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

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
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

    /**
     * PenilaianElemen has one Validasi
     */
    public function validasi()
    {
        return $this->hasOne(ValidasiPenilaian::class, 'id_penilaian');
    }
}
