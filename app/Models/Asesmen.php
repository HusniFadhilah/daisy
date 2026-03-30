<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function asesmenBanding(): HasOne
    {
        return $this->hasOne(AsesmenBanding::class, 'id_asesmen');
    }

    public function asesmenKecukupanBanding()
    {
        return $this->hasOne(AsesmenKecukupanBanding::class, 'id_asesmen');
    }

    public function asesmenLapanganBanding()
    {
        return $this->hasOne(AsesmenLapanganBanding::class, 'id_asesmen');
    }

    public function penilaianElemenAk()
    {
        return $this->hasMany(PenilaianElemenAk::class, 'id_asesmen');
    }

    public function penilaianElemenAl()
    {
        return $this->hasMany(PenilaianElemenAl::class, 'id_asesmen');
    }

    public function penilaianElemenAkBanding()
    {
        return $this->hasMany(PenilaianElemenAkBanding::class, 'id_asesmen');
    }

    public function penilaianElemenAlBanding()
    {
        return $this->hasMany(PenilaianElemenAlBanding::class, 'id_asesmen');
    }

    public function penilaianElemenBanding()
    {
        return $this->hasMany(PenilaianElemenBanding::class, 'id_asesmen');
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

    public function asesorBanding()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'asesor_banding');
            })->where('jenis_asesmen', 'banding');
    }

    public function asesorAKBanding()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'asesor_banding');
            })->where('jenis_asesmen', 'ak_banding');
    }

    public function asesorALBanding()
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->whereHas('role', function ($query) {
                $query->where('name', 'asesor_banding');
            })->where('jenis_asesmen', 'al_banding');
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

    public function allAsesorRolesAl($isBanding = false)
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->where('jenis_asesmen', $isBanding ? 'al_banding' : 'al')
            ->whereHas('role', fn($q) => $q->where('name', $isBanding ? 'asesor_banding' : 'asesor'));
    }

    public function allAsesorsAk($isBanding = false)
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->where('jenis_asesmen', $isBanding ? 'ak_banding' : 'ak')
            ->whereHas('role', fn($q) => $q->where('name', $isBanding ? 'asesor_banding' : 'asesor'));
    }

    public function allAsesorRolesAlBanding($isBanding = true)
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->where('jenis_asesmen', $isBanding ? 'al_banding' : 'al')
            ->whereHas('role', fn($q) => $q->where('name', $isBanding ? 'asesor_banding' : 'asesor'));
    }

    public function allAsesorsAkBanding($isBanding = true)
    {
        return $this->hasMany(AsesmenUserRole::class, 'id_asesmen')
            ->where('jenis_asesmen', $isBanding ? 'ak_banding' : 'ak')
            ->whereHas('role', fn($q) => $q->where('name', $isBanding ? 'asesor_banding' : 'asesor'));
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

    public function beritaAcaraALBanding()
    {
        return $this->documents()
            ->where('type', 'berita_acara_al_banding')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function beritaAcaraBanding()
    {
        return $this->documents()
            ->where('type', 'berita_acara_banding')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function lhaDocuments()
    {
        return $this->hasMany(AsesmenDocument::class, 'id_asesmen')
            ->where('type', 'lha_asesor')
            ->where('is_active', true);
    }

    public function lhaDocumentsBanding()
    {
        return $this->hasMany(AsesmenDocument::class, 'id_asesmen')
            ->where('type', 'lha_asesor_banding')
            ->where('is_active', true);
    }

    public function lhaAsesor()
    {
        return $this->hasOne(LhaAsesor::class, 'id_asesmen');
    }

    public function lhaAsesorBanding()
    {
        return $this->hasOne(LhaAsesorBanding::class, 'id_asesmen');
    }

    public static function formatJenisAsesmen($text)
    {
        $words = explode('_', $text);
        return collect($words)->map(function ($word, $index) use ($text) {
            return $index === 0 && $text != 'dokumen' ? strtoupper($word) : ucfirst($word);
        })->implode(' ');
    }

    public function getName($isFull = true)
    {
        if ($isFull) {
            return $this->name;
        } else {
            return str_replace('Permohonan ', '', $this->name);
        }
    }

    public function getPermohonanAkreditasiSectionFor($for = 'de')
    {
        $pengajuan = $this->pengajuan;
        if ($pengajuan) {
            $judulPrefix = $pengajuan->judulPrefix('short');
            if ($for == 'upps')
                return <<<HTML
            <p>{$judulPrefix}</p>
            <small class="text-muted">{$pengajuan->studyProgram->name}</small><br>
            <!-- <small class="text-muted">
                Dibuat pada: { \App\Libraries\Date::tglIndo($this->created_at) }
            </small> -->
        HTML;
            else if ($for == 'de')
                return <<<HTML
        <p>{$judulPrefix}</p>
        <small><b>{$pengajuan->studyProgram->name}</b></small><br>
        <small>{$pengajuan->studyProgram->university->name}</small>
        <!-- <br> -->
        <!-- <small class="text-muted">{$this->nomor_pengajuan}</small> -->
        <!-- <br>
        <small class="text-muted">Dibuat pada: { \App\Libraries\Date::tglIndo($this->created_at)}</small> -->
        HTML;
            else if ($for == 'validator')
                return <<<HTML
        <p>{$judulPrefix}</p>
        <small><b>{$pengajuan->studyProgram->name}</b></small><br>
        <small>{$pengajuan->studyProgram->university->name}</small>
        <!-- <br> -->
        <!-- <small class="text-muted">{$this->nomor_pengajuan}</small> -->
        <!-- <br>
        <small class="text-muted">Dibuat pada: { \App\Libraries\Date::tglIndo($this->created_at)}</small> -->
        HTML;
            else if ($for == 'asesor')
                return <<<HTML
        <p>{$judulPrefix}</p>
        <small><b>{$pengajuan->studyProgram->name}</b></small><br>
        <small>{$pengajuan->studyProgram->university->name}</small>
        <!-- <br> -->
        <!-- <small class="text-muted">{$this->nomor_pengajuan}</small> -->
        <!-- <br>
        <small class="text-muted">Dibuat pada: { \App\Libraries\Date::tglIndo($this->created_at)}</small> -->
        HTML;
        } else {
            return $this->getName(false);
        }
    }

    public function getProgramStudiSectionFor($for = 'de')
    {
        $asesmen = $this->asesmen;
        if ($for == 'de')
            return <<<HTML
            <strong>{{ $asesmen->studyProgram->name ?? '-' }}</strong>
            <br>
            <small class="text-muted">
                <i class="bi bi-building"></i>
                {{ $asesmen->studyProgram->university->name ?? '-' }}
            </small>
        HTML;
        else if ($for == 'upps')
            return <<<HTML
            {{ $asesmen->studyProgram->name ?? '-' }}
        HTML;
    }
}
