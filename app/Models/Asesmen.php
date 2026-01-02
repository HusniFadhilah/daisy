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
    ];

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

    public function penilaianElemenAK()
    {
        return $this->hasMany(new PenilaianElemenAK, 'id_asesmen');
    }

    public function penilaianElemenAL()
    {
        return $this->hasMany(PenilaianElemenAL::class, 'id_asesmen');
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
}
