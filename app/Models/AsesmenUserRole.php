<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenUserRole extends Model
{
    protected $fillable = [
        'id_asesmen',
        'id_user',
        'id_role',
        'status_penawaran',
        'responded_at',
        'response_note',
        'status_pekerjaan',
        'submitted_at',
        'approved_at',
        'approved_by'
    ];

    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id');
    }

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_study_program');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
