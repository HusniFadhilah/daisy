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
        'keterangan',
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
        return $this->getStatusPembayaranMeta('upps')['class'];
    }

    public static function statusConfig()
    {
        return [
            'menunggu_pembayaran' => [
                'class' => 'warning',
                'icon'  => 'hourglass-split',
                'text'  => [
                    'keuangan' => 'Menunggu Pembayaran',
                    'upps'       => 'Perlu Melakukan Pembayaran',
                    'default'  => 'Menunggu Pembayaran',
                ],
            ],

            'menunggu_verifikasi' => [
                'class' => 'info',
                'icon'  => 'clock-history',
                'text'  => [
                    'keuangan' => 'Perlu Validasi',
                    'upps'       => 'Menunggu Validasi',
                    'default'  => 'Menunggu Validasi',
                ],
            ],

            'terverifikasi' => [
                'class' => 'success',
                'icon'  => 'check-circle',
                'text'  => [
                    'keuangan' => 'Sudah Divalidasi',
                    'upps'       => 'Tervalidasi',
                    'default'  => 'Tervalidasi',
                ],
            ],

            'upload_ulang' => [
                'class' => 'secondary',
                'icon'  => 'arrow-repeat',
                'text'  => [
                    'keuangan' => 'Perlu Upload Ulang',
                    'upps'       => 'Upload Ulang',
                    'default'  => 'Upload Ulang',
                ],
            ],

            'ditolak' => [
                'class' => 'danger',
                'icon'  => 'x-circle',
                'text'  => [
                    'keuangan' => 'Ditolak',
                    'upps'       => 'Ditolak',
                    'default'  => 'Ditolak',
                ],
            ],
        ];
    }

    public function getStatusPembayaranMeta($role = null)
    {
        $config = self::statusConfig();
        $status = $config[$this->status_pembayaran] ?? null;

        if (!$status) {
            return [
                'class' => 'secondary',
                'icon'  => 'question-circle',
                'text'  => 'Unknown'
            ];
        }

        $text = $status['text'];

        // kalau text array (multi-role)
        if (is_array($text)) {
            $text = $text[$role]
                ?? $text['default']
                ?? reset($text);
        }

        return [
            'class' => $status['class'],
            'icon'  => $status['icon'],
            'text'  => $text,
        ];
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
