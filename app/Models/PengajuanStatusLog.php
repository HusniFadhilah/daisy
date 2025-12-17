<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanStatusLog extends Model
{
    protected $table = 'pengajuan_status_log';

    protected $fillable = [
        'id_pengajuan',
        'status_from',
        'status_to',
        'changed_by',
        'keterangan',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function pengajuanAkreditasi()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }
}
