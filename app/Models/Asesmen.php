<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asesmen extends Model
{
    protected $fillable = [
        'id_pengajuan',
        'id_study_program',
        'code',
        'name',
        'description',
        'kode_panel',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'is_active',
        'is_example'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function scopeNonExample($query)
    {
        return $query->where('is_example', false);
    }

    public function scopeWithExample($query)
    {
        return $query->whereIn('is_example', [false, true]);
    }

    public function scopeExample($query)
    {
        return $query->where('is_example', true);
    }

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_study_program');
    }

    public function userRoles()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen');
    }

    public function asesmenUserRoles()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen');
    }

    public function hasil()
    {
        return $this->hasOne(HasilAkreditasi::class, 'id_asesmen');
    }

    /**
     * Relasi ke Asesmen Kecukupan (AK)
     * 1 Asesmen bisa punya 1 AK
     */
    public function asesmenKecukupan()
    {
        return $this->hasOne(AsesmenKecukupan::class, 'id_asesmen');
    }

    /**
     * Relasi ke Asesmen Lapangan (AL)
     * 1 Asesmen bisa punya 1 AL
     */
    public function asesmenLapangan()
    {
        return $this->hasOne(AsesmenLapangan::class, 'id_asesmen');
    }

    public function penilaianElemenAk()
    {
        return $this->hasMany(PenilaianElemenAk::class, 'id_asesmen');
    }

    public function penilaianElemenAl()
    {
        return $this->hasMany(PenilaianElemenAl::class, 'id_asesmen');
    }

    /**
     * Asesor AK saja
     */
    public function asesorAK()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'asesor');
            })->where('jenis_asesmen', 'ak');
    }

    /**
     * Asesor AL saja
     */
    public function asesorAL()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'asesor');
            })->where('jenis_asesmen', 'al');
    }

    /**
     * Validator saja
     */
    public function validators()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'validator');
            });
    }

    public function validatorAK()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'validator');
            })->where('jenis_asesmen', 'ak');
    }

    public function validatorAL()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'validator');
            })->where('jenis_asesmen', 'al');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'asesmen_user_roles',
            'id_asesmen',
            'id_user'
        )
            ->withPivot([
                'id_role',
                'jenis_asesmen',
                'status_penawaran',
                'responded_at',
                'response_note',
                'status_pekerjaan',
                'submitted_at',
                'approved_at',
                'approved_by',
            ])
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(AsesmenDocument::class, 'id_asesmen');
    }

    public function asesmenDocuments()
    {
        return $this->hasMany(AsesmenDocument::class, 'id_asesmen');
    }

    public function beritaAcara()
    {
        return $this->documents()
            ->where('type', 'berita_acara_al')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function beritaAcaraAL()
    {
        return $this->documents()
            ->where('type', 'berita_acara_al')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
