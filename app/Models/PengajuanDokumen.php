<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PengajuanDokumen extends Model
{
    protected $table = 'pengajuan_dokumen';

    protected $fillable = [
        'id_pengajuan',
        'jenis_dokumen',
        'nama_file',
        'path_file',
        'original_filename',
        'file_size',
        'mime_type',
        'uploaded_by',
        'keterangan',
        'versi',
        'is_latest',
    ];

    protected $casts = [
        'is_latest' => 'boolean',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDownloadUrlAttribute()
    {
        return route('pengajuan.dokumen.download', $this->id);
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
