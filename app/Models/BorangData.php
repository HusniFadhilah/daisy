<?php
// app/Models/BorangData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangData extends Model
{
    protected $table = 'borang_data';

    protected $fillable = [
        'id_pengajuan',
        'id_borang_import',
        'id_elemen',
        'id_dataset_borang',
        'dataset_id',
        'nilai',
        'is_template'
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanAkreditasi::class, 'id_pengajuan');
    }

    public function borangImport()
    {
        return $this->belongsTo(BorangImport::class, 'id_borang_import');
    }

    public function datasetBorang()
    {
        return $this->belongsTo(DatasetBorang::class, 'id_dataset_borang');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeForPengajuan($query, $pengajuanId)
    {
        return $query->where('id_pengajuan', $pengajuanId);
    }

    public function scopeForDataset($query, $datasetId)
    {
        return $query->where('dataset_id', $datasetId);
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    /**
     * Get value (auto-decode JSON if applicable)
     */
    public function getValueAttribute()
    {
        if ($this->isJson($this->nilai)) {
            return json_decode($this->nilai, true);
        }
        return $this->nilai;
    }

    /**
     * Set value (auto-encode arrays to JSON)
     */
    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['nilai'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['nilai'] = $value;
        }
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if string is JSON
     */
    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Check if value is empty
     */
    public function isEmpty()
    {
        if (empty($this->nilai)) return true;

        $value = $this->value;

        if (is_array($value)) {
            return empty($value);
        }

        return trim($value) === '';
    }

    /**
     * Get value as HTML (for table fields)
     */
    public function getAsHtml()
    {
        $value = $this->nilai;

        // If already HTML table
        if (strpos($value, '<table') !== false) {
            return $value;
        }

        // If JSON array, convert to HTML table
        if ($this->isJson($value)) {
            $data = json_decode($value, true);
            return $this->arrayToHtmlTable($data);
        }

        return htmlspecialchars($value);
    }

    /**
     * Convert array data to HTML table
     */
    private function arrayToHtmlTable($data)
    {
        if (empty($data)) return '';

        $html = '<table style="width:100%;border-collapse:collapse;">';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td style="border:1px solid #ddd;padding:8px;">' .
                    htmlspecialchars($cell ?: '&nbsp;') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Static helper: Get or create borang data
     */
    public static function getOrCreateForPengajuan($pengajuanId, $datasetId, $defaultValue = null)
    {
        return static::firstOrCreate(
            [
                'id_pengajuan' => $pengajuanId,
                'dataset_id' => $datasetId
            ],
            [
                'nilai' => $defaultValue
            ]
        );
    }

    /**
     * Static helper: Bulk update data
     */
    public static function bulkUpdateForPengajuan($pengajuanId, array $data)
    {
        foreach ($data as $datasetId => $value) {
            static::updateOrCreate(
                [
                    'id_pengajuan' => $pengajuanId,
                    'dataset_id' => $datasetId
                ],
                [
                    'nilai' => $value
                ]
            );
        }
    }
}
