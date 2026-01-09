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
        'template_link',
        'versi',
        'is_latest',
    ];

    protected $casts = [
        'is_latest' => 'boolean',
    ];

    // Relations
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors
    public function getDownloadUrlAttribute()
    {
        // ✅ Handle both file and link
        if ($this->template_link) {
            return $this->template_link;
        }

        if ($this->path_file) {
            return route('pengajuan.dokumen.download', $this->id);
        }

        return null;
    }

    public function getIsLinkBasedAttribute()
    {
        return !empty($this->template_link);
    }

    public function getIsFileBasedAttribute()
    {
        return !empty($this->path_file);
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public function getFileExtensionAttribute()
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    public function getFileIconClassAttribute()
    {
        $ext = $this->file_extension;

        $icons = [
            'pdf' => 'bi-file-pdf text-danger',
            'docx' => 'bi-file-word text-primary',
            'doc' => 'bi-file-word text-primary',
            'xlsx' => 'bi-file-excel text-success',
            'xls' => 'bi-file-excel text-success',
            'jpg' => 'bi-file-image text-info',
            'jpeg' => 'bi-file-image text-info',
            'png' => 'bi-file-image text-info',
        ];

        return $icons[$ext] ?? 'bi-file-earmark text-secondary';
    }

    // Scopes
    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('jenis_dokumen', $type);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('jenis_dokumen', $type);
    }

    public function scopeByPengajuan($query, $pengajuanId)
    {
        return $query->where('id_pengajuan', $pengajuanId);
    }
}
