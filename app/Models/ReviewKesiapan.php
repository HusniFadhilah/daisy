<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewKesiapan extends Model
{
    protected $table = 'review_kesiapan';

    protected $fillable = [
        'id_pengajuan',
        'id_reviewer',
        'hasil_review',
        'catatan_review',
        'checklist_kesiapan',
        'versi_review',
        'tanggal_review',
    ];

    protected $casts = [
        'checklist_kesiapan' => 'array',
        'tanggal_review' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'id_reviewer');
    }
}
