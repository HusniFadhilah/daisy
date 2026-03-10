<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class AsesmenBanding extends Model
{
    protected $table = 'asesmen_banding';

    protected $fillable = [
        'id_asesmen',
        'code',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    /**
     * Semua surveyor (role surveillance) yang ditugaskan
     */
    public function asesors()
    {
        return $this->asesmen
            ->asesmenUserRoles()
            ->where('jenis_asesmen', 'banding')
            ->whereHas('role_selected', function ($q) {
                $q->where('name', 'asesor_banding');
            })
            ->whereIn('status_penawaran', ['accepted', 'pending']);
    }

    /**
     * Semua validator yang ditugaskan
     */
    public function validators()
    {
        return $this->asesmen
            ->asesmenUserRoles()
            ->where('jenis_asesmen', 'banding')
            ->whereHas('role_selected', function ($q) {
                $q->where('name', 'validator');
            })
            ->whereIn('status_penawaran', ['accepted', 'pending']);
    }
}
