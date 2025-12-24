<?php
// app/Models/BorangData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangData extends Model
{
    protected $table = 'borang_data';

    protected $fillable = [
        'id_borang_import',
        'dataset_id',
        'nilai',
        'id_dataset_borang',
    ];

    protected $casts = [
        // If nilai is JSON
        // 'nilai' => 'array',
    ];

    // Relations
    public function borangImport()
    {
        return $this->belongsTo(BorangImport::class, 'id_borang_import');
    }

    public function datasetBorang()
    {
        return $this->belongsTo(DatasetBorang::class, 'id_dataset_borang');
    }

    // Helpers
    public function getValueAttribute()
    {
        // Try to decode JSON, fallback to raw value
        if ($this->isJson($this->nilai)) {
            return json_decode($this->nilai, true);
        }
        return $this->nilai;
    }

    public function setValueAttribute($value)
    {
        // Auto-encode arrays to JSON
        if (is_array($value)) {
            $this->attributes['nilai'] = json_encode($value);
        } else {
            $this->attributes['nilai'] = $value;
        }
    }

    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
