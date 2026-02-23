<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SyaratAkreditasi extends Model
{
    use SoftDeletes;

    protected $table = 'syarat_akreditasi';

    protected $fillable = [
        'kelompok',
        'kunci',
        'nilai',
        'tipe',
        'label',
        'keterangan',
        'versi',
        'berlaku_mulai',
        'berlaku_sampai',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'berlaku_mulai'  => 'date',
        'berlaku_sampai' => 'date',
        'is_active'      => 'boolean',
    ];

    // ── Kelompok ──
    public const KELOMPOK_SKOR            = 'skor';
    public const KELOMPOK_PELAMPAUAN      = 'pelampauan';
    public const KELOMPOK_RASIO_DTPS      = 'rasio_dtps';
    public const KELOMPOK_JABATAN         = 'jabatan';
    public const KELOMPOK_RENTANG_SKOR    = 'rentang_skor';
    public const KELOMPOK_SYARAT_KUALITATIF = 'syarat_kualitatif';

    // ── Kunci ──
    public const KUNCI_SKOR_MIN_UNGGUL       = 'skor_minimum_unggul';
    public const KUNCI_KRITERIA_REQUIRED     = 'kriteria_required';
    public const KUNCI_RASIO_LINGKUNGAN      = 'max_rasio_lingkungan';
    public const KUNCI_RASIO_DEFAULT         = 'max_rasio_default';
    public const KUNCI_RUMPUN_RASIO_KHUSUS   = 'rumpun_rasio_khusus';
    public const KUNCI_JABATAN_LEKTOR        = 'jabatan_lektor_ke_atas';
    public const KUNCI_PERSEN_LEKTOR         = 'persen_minimum_lektor';

    /**
     * Casting nilai ke tipe yang sesuai.
     */
    public function getNilaiCastAttribute(): mixed
    {
        return match ($this->tipe) {
            'integer' => (int)$this->nilai,
            'float'   => (float)$this->nilai,
            'boolean' => filter_var($this->nilai, FILTER_VALIDATE_BOOLEAN),
            'array', 'json' => json_decode($this->nilai, true) ?? [],
            default   => $this->nilai,
        };
    }

    // ── Relasi ──
    public function logs()
    {
        return $this->hasMany(SyaratAkreditasiLog::class, 'id_syarat');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Scope ──
    public function scopeAktif($query)
    {
        return $query->where('is_active', true)
            ->where(
                fn($q) => $q
                    ->whereNull('berlaku_sampai')
                    ->orWhere('berlaku_sampai', '>=', now()->toDateString())
            )
            ->where(
                fn($q) => $q
                    ->whereNull('berlaku_mulai')
                    ->orWhere('berlaku_mulai', '<=', now()->toDateString())
            );
    }
}
