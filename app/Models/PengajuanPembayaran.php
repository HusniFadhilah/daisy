<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PengajuanPembayaran extends Model
{
    protected $table = 'pengajuan_pembayaran';

    public const BIAYA_AKREDITASI = 59500000;

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

    public function getStatusPembayaranLabelAttribute()
    {
        if (!$this->status_pembayaran) {
            return null;
        }

        return str_replace('verifikasi', 'validasi', $this->status_pembayaran);
    }

    public static function generateNomorInvoice()
    {
        $year = date('Y');
        $month = date('m');

        $last = PengajuanPembayaran::where('nomor_invoice', 'like', "INV/{$year}/{$month}/%")
            ->orderBy('nomor_invoice', 'desc')
            ->first();

        $newNum = 1;
        if ($last) {
            $lastNum = (int) substr($last->nomor_invoice, -4);
            $newNum = $lastNum + 1;
        }

        return sprintf('INV/%s/%s/%04d', $year, $month, $newNum);
    }
}
