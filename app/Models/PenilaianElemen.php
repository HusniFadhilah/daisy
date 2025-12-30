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

    /**
     * Get skor label
     */
    public function getSkorLabelAttribute()
    {
        $labels = [
            0 => 'Tidak Memenuhi (Not Met)',
            1 => 'Belum Memenuhi (Not Met)',
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
