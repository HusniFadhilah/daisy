<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratAkreditasiLog extends Model
{
    protected $table = 'syarat_akreditasi_logs';

    protected $fillable = [
        'id_syarat',
        'nilai_lama',
        'nilai_baru',
        'alasan',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function syarat()
    {
        return $this->belongsTo(SyaratAkreditasi::class, 'id_syarat');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
