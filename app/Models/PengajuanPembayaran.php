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
        'tanggal_verifikasi',
        'status_pembayaran',
        'bukti_path',
        'catatan_pembayaran',
        'catatan_verifikasi',
        'alasan_penolakan',
        'verified_by',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_pembayaran' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan', 'id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
