<?php
// app/Models/DatasetBorang.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatasetBorang extends Model
{
    protected $table = 'dataset_borang';

    protected $fillable = [
        'id_elemen',
        'kode',
        'nama',
        'deskripsi',
        'tipe_field',
        'label_field',
        'placeholder',
        'is_required',
        'options',
        'keterangan',
        'expected_columns',
        'template_html',
        'validation_rules',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'expected_columns' => 'array',
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function elemen()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }

    public function borangTables()
    {
        return $this->hasMany(BorangTable::class, 'id_dataset');
    }

    public function borangData()
    {
        return $this->hasMany(BorangData::class, 'id_dataset_borang');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForElemen($query, $elemenId)
    {
        return $query->where('id_elemen', $elemenId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public function scopeTables($query)
    {
        return $query->where('tipe_field', 'table');
    }

    public function scopeNarasi($query)
    {
        return $query->where('tipe_field', 'narasi');
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    public function getTemplateHtmlAttribute($value)
    {
        // Return stored value or generate default
        return $value ?? $this->generateDefaultTemplate();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Generate default HTML template for table fields
     */
    private function generateDefaultTemplate()
    {
        if ($this->tipe_field !== 'table' || !$this->expected_columns) {
            return null;
        }

        $html = '<table style="width:100%;border-collapse:collapse;">';
        $html .= '<thead><tr>';

        foreach ($this->expected_columns as $column) {
            $html .= '<th style="border:1px solid #ddd;padding:8px;background-color:#f2f2f2;">' . htmlspecialchars($column) . '</th>';
        }

        $html .= '</tr></thead>';
        $html .= '<tbody>';

        // Add 3 empty rows
        for ($i = 0; $i < 3; $i++) {
            $html .= '<tr>';
            foreach ($this->expected_columns as $column) {
                $html .= '<td style="border:1px solid #ddd;padding:8px;">&nbsp;</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Check if field has options (for select type)
     */
    public function hasOptions()
    {
        return $this->tipe_field === 'select' && !empty($this->options);
    }

    /**
     * Get validation rules as array
     */
    public function getValidationRulesArray()
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        }

        if ($this->validation_rules) {
            $rules = array_merge($rules, $this->validation_rules);
        }

        return $rules;
    }

    /**
     * Get column count for table fields
     */
    public function getColumnCount()
    {
        return $this->expected_columns ? count($this->expected_columns) : 0;
    }
}
