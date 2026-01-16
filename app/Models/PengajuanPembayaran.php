<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PengajuanPembayaran extends Model
{
    protected $table = 'pengajuan_pembayaran';

    protected $fillable = [
        'id_pengajuan',
        'nomor_invoice',
        'jumlah_pembayaran',
        'tanggal_jatuh_tempo',
        'tanggal_pembayaran',
        'status_pembayaran',
        'bukti_path',
        'catatan_verifikasi',
        'alasan_penolakan',
        'verified_by',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_pembayaran' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(\App\Models\PengajuanAkreditasi::class, 'id_pengajuan', 'id');
    }

    public function verifier()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }
}
