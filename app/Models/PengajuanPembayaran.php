<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PengajuanPembayaran extends Model
{
    protected $table = 'pengajuan_pembayaran';

    public const BIAYA_AKREDITASI = 59500000;
    public const BIAYA_BANDING    = 30000000; // 30 juta

    protected $fillable = [
        'id_pengajuan',
        'nomor_invoice',
        'jenis_pembayaran',
        'jumlah_pembayaran',
        'tanggal_jatuh_tempo',
        'tanggal_pembayaran',
        'tanggal_verifikasi',
        'tanggal_upload_ulang',
        'tanggal_ditolak',
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
        'tanggal_upload_ulang' => 'datetime',
        'tanggal_ditolak' => 'datetime',
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

        $label = str_replace('_', ' ', $this->status_pembayaran);
        $label = str_replace(
            ['verifikasi', 'terverifikasi'],
            ['validasi', 'tervalidasi'],
            $label
        );

        return ucwords($label);
    }

    public function getStatusPembayaranBadgeAttribute()
    {
        return match ($this->status_pembayaran) {
            'menunggu_pembayaran' => 'secondary',
            'menunggu_verifikasi' => 'warning',
            'upload_ulang'        => 'info',
            'ditolak'             => 'danger',
            'terverifikasi'       => 'success',
            default               => 'secondary',
        };
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
