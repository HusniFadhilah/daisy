<?php
// app/Models/BeritaAcaraAl.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcaraAl extends Model
{
    use HasFactory;

    protected $table = 'berita_acara_al';

    protected $fillable = [
        'id_pengajuan',
        'id_asesor',
        'file_path',
        'original_filename',
        'file_size',
        'tanggal_pelaksanaan',
        'tempat_pelaksanaan',
        'catatan_asesor',
        'status_persetujuan_prodi',
        'tanggal_disetujui_prodi',
        'catatan_prodi',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'datetime',
        'tanggal_disetujui_prodi' => 'datetime',
    ];

    /**
     * Get the pengajuan
     */
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    /**
     * Get the asesor
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'id_asesor');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status_persetujuan_prodi) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status_persetujuan_prodi) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }
}
