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

    public function penilaianElemen()
    {
        return $this->hasMany(PenilaianElemen::class, 'id_asesmen');
    }
}
