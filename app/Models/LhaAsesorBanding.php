<?php
// app/Models/LhaAsesor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LhaAsesorBanding extends Model
{
    protected $table = 'lha_asesor_banding';

    protected $fillable = [
        'id_asesmen',
        'pendahuluan',
        'pendahuluan_updated_by',
        'pendahuluan_updated_at',
        'proses_al',
        'proses_al_updated_by',
        'proses_al_updated_at',
        'hasil_al',
        'hasil_al_updated_by',
        'hasil_al_updated_at',
        'rekomendasi_ps',
        'rekomendasi_ps_updated_by',
        'rekomendasi_ps_updated_at',
        'rekomendasi_lamdepilar',
        'rekomendasi_lamdepilar_updated_by',
        'rekomendasi_lamdepilar_updated_at',
        'status',
        'finalized_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
        'pendahuluan_updated_at' => 'datetime',
        'proses_al_updated_at' => 'datetime',
        'hasil_al_updated_at' => 'datetime',
        'rekomendasi_ps_updated_at' => 'datetime',
        'rekomendasi_lamdepilar_updated_at' => 'datetime',
    ];

    // Relasi
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relasi untuk tracking field-specific editors
    public function pendahuluanEditor()
    {
        return $this->belongsTo(User::class, 'pendahuluan_updated_by');
    }

    public function prosesAlEditor()
    {
        return $this->belongsTo(User::class, 'proses_al_updated_by');
    }

    public function hasilAlEditor()
    {
        return $this->belongsTo(User::class, 'hasil_al_updated_by');
    }

    public function rekomendasiPsEditor()
    {
        return $this->belongsTo(User::class, 'rekomendasi_ps_updated_by');
    }

    public function rekomendasiLamdepilarEditor()
    {
        return $this->belongsTo(User::class, 'rekomendasi_lamdepilar_updated_by');
    }

    // Status methods
    public function isFinalized()
    {
        return $this->status === 'finalized';
    }

    public function isFinalizedApproved()
    {
        return in_array($this->status, ['finalized', 'approved']);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    // Calculate completion
    public function getCompletionPercentage()
    {
        $fields = [
            'pendahuluan',
            'proses_al',
            'hasil_al',
            'rekomendasi_ps',
            'rekomendasi_lamdepilar',
        ];

        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $filled++;
            }
        }

        return round(($filled / count($fields)) * 100);
    }

    // Get editor info for a field
    public function getFieldEditorInfo($field)
    {
        $editorField = $field . '_updated_by';
        $timeField = $field . '_updated_at';

        return [
            'editor' => $this->$editorField ? $this->{$field . 'Editor'} : null,
            'time' => $this->$timeField,
        ];
    }
}
