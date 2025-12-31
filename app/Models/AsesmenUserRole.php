<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenUserRole extends Model
{
    protected $fillable = [
        'id_asesmen',
        'id_user',
        'id_role',
        'jenis_asesmen',
        'id_asesmen_kecukupan',
        'id_asesmen_lapangan',
        'urutan_asesor',
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

    public function asesmenKecukupan()
    {
        return $this->belongsTo(AsesmenKecukupan::class, 'id_asesmen_kecukupan');
    }

    public function asesmenLapangan()
    {
        return $this->belongsTo(AsesmenLapangan::class, 'id_asesmen_lapangan');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeAsesorOnly($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('name', 'asesor');
        });
    }

    public function scopeValidatorOnly($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('name', 'validator');
        });
    }

    public function scopeForAK($query)
    {
        return $query->where('jenis_asesmen', 'ak');
    }

    public function scopeForAL($query)
    {
        return $query->where('jenis_asesmen', 'al');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status_penawaran', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status_penawaran', 'rejected');
    }

    public function scopePending($query)
    {
        return $query->where('status_penawaran', 'pending');
    }
}
