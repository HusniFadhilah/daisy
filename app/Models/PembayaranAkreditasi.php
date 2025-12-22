<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranAkreditasi extends Model
{
    protected $table = 'pembayaran_akreditasi';

    protected $fillable = [
        'id_pengajuan',
        'nomor_invoice',
        'jumlah_pembayaran',
        'status_pembayaran',
        'tanggal_jatuh_tempo',
        'tanggal_pembayaran',
        'tanggal_verifikasi',
        'verified_by',
        'catatan_verifikasi',
        'alasan_penolakan',
    ];

    protected $casts = [
        'jumlah_pembayaran' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function generateNomorInvoice()
    {
        $year = date('Y');
        $month = date('m');
        $lastInvoice = self::where('nomor_invoice', 'like', "INV/$year/$month/%")
            ->orderBy('nomor_invoice', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNum = (int) substr($lastInvoice->nomor_invoice, -4);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return sprintf('INV/%s/%s/%04d', $year, $month, $newNum);
    }
}
