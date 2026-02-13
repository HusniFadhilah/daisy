<?php
// app/Models/DatasetBorang.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatasetBorang extends Model
{
    protected $table = 'dataset_borang';

    protected $fillable = [
        'id_elemen',
        'id_degree_level',
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
        'expected_rows',
        'template_html',
        'validation_rules',
        'urutan',
        'is_active',
        'has_degree_variants',
    ];

    protected $casts = [
        'expected_columns' => 'array',
        'expected_rows' => 'array',
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'has_degree_variants' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function elemen()
    {
        return $this->belongsTo(ElemenStandar::class, 'id_elemen');
    }

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLevel::class, 'id_degree_level');
    }

    public function degreeLevelVariants()
    {
        return $this->belongsToMany(
            DegreeLevel::class,
            'dataset_borang_degree_level',
            'id_dataset_borang',
            'id_degree_level'
        )->withPivot([
            'expected_columns',
            'template_html',
            'validation_rules',
            'keterangan'
        ])->withTimestamps();
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
     * Generate default HTML templat for table fields
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
     * ✅ Get template untuk degree level tertentu
     */
    public function getTemplateForDegree($degreeLevelId)
    {
        // Cek apakah ada variant untuk degree level ini
        $variant = $this->degreeLevelVariants()
            ->where('id_degree_level', $degreeLevelId)
            ->first();

        if ($variant && $variant->pivot->template_html) {
            return [
                'template_html' => $variant->pivot->template_html,
                'expected_columns' => $variant->pivot->expected_columns,
                'validation_rules' => $variant->pivot->validation_rules,
                'keterangan' => $variant->pivot->keterangan,
                'source' => 'variant',
            ];
        }

        // Fallback ke templat default
        return [
            'template_html' => $this->template_html,
            'expected_columns' => $this->expected_columns,
            'validation_rules' => $this->validation_rules,
            'keterangan' => $this->keterangan,
            'source' => 'default',
        ];
    }

    /**
     * ✅ Check if dataset is applicable for degree level
     */
    public function isApplicableFor($degreeLevelId)
    {
        // Jika id_degree_level NULL = berlaku untuk semua
        if ($this->id_degree_level === null) {
            return true;
        }

        // Jika spesifik, harus match
        if ($this->id_degree_level == $degreeLevelId) {
            return true;
        }

        // Cek di variants
        return $this->degreeLevelVariants()
            ->where('id_degree_level', $degreeLevelId)
            ->exists();
    }

    /**
     * ✅ Scope: Filter by degree level
     */
    public function scopeForDegreeLevel($query, $degreeLevelId)
    {
        return $query->where(function ($q) use ($degreeLevelId) {
            $q->whereNull('id_degree_level')
                ->orWhere('id_degree_level', $degreeLevelId)
                ->orWhereHas('degreeLevelVariants', function ($q2) use ($degreeLevelId) {
                    $q2->where('id_degree_level', $degreeLevelId);
                });
        });
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
