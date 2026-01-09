<?php
// app/Models/BorangTable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangTable extends Model
{
    protected $fillable = [
        'id_section',
        'id_dataset',
        'kode_tabel',
        'judul_tabel',
        'row_count',
        'col_count',
        'headers',
        'data',
        'html_content',
        'position',
    ];

    protected $casts = [
        'headers' => 'array',
        'data' => 'array',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function section()
    {
        return $this->belongsTo(BorangSection::class, 'id_section');
    }

    public function dataset()
    {
        return $this->belongsTo(DatasetBorang::class, 'id_dataset');
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    /**
     * Get data as array (handle both JSON and HTML storage)
     */
    public function getDataArrayAttribute()
    {
        if (is_array($this->data)) {
            return $this->data;
        }

        // Try to decode JSON
        if ($this->isJson($this->data)) {
            return json_decode($this->data, true);
        }

        return [];
    }

    /**
     * Get HTML representation
     */
    public function getHtmlAttribute()
    {
        // Return stored HTML or generate from data
        return $this->html_content ?? $this->generateHtml();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Generate HTML table from data
     */
    private function generateHtml()
    {
        if (empty($this->headers) && empty($this->data)) {
            return '';
        }

        $html = '<table style="width:100%;border-collapse:collapse;">';

        // Headers
        if (!empty($this->headers)) {
            $html .= '<thead><tr>';
            foreach ($this->headers as $header) {
                $html .= '<th style="border:1px solid #ddd;padding:8px;background-color:#f2f2f2;">' .
                    htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }

        // Data rows
        if (!empty($this->data_array)) {
            $html .= '<tbody>';
            foreach ($this->data_array as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td style="border:1px solid #ddd;padding:8px;">' .
                        htmlspecialchars($cell ?: '&nbsp;') . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }

        $html .= '</table>';

        return $html;
    }

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
     * Set data from array
     */
    public function setDataFromArray($arrayData)
    {
        $this->row_count = count($arrayData);
        $this->col_count = !empty($arrayData) ? count($arrayData[0]) : 0;
        $this->data = $arrayData;
        $this->save();
    }

    /**
     * Set data from HTML
     */
    public function setDataFromHtml($html)
    {
        $this->html_content = $html;

        // Parse HTML to extract headers and data
        $parsed = $this->parseHtmlTable($html);

        if ($parsed) {
            $this->headers = $parsed['headers'];
            $this->data = $parsed['data'];
            $this->row_count = count($parsed['data']);
            $this->col_count = !empty($parsed['headers']) ? count($parsed['headers']) : 0;
        }

        $this->save();
    }

    /**
     * Parse HTML table to extract structure
     */
    private function parseHtmlTable($html)
    {
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);

        if (empty($rows[1])) {
            return null;
        }

        $headers = [];
        $data = [];

        foreach ($rows[1] as $rowIndex => $rowHtml) {
            preg_match_all('/<t[hd][^>]*>(.*?)<\/t[hd]>/is', $rowHtml, $cells);

            if (empty($cells[1])) continue;

            $rowData = [];
            foreach ($cells[1] as $cellHtml) {
                $cellText = strip_tags($cellHtml);
                $cellText = html_entity_decode($cellText);
                $cellText = trim($cellText);
                $rowData[] = $cellText;
            }

            if ($rowIndex === 0 && strpos($rowHtml, '<th') !== false) {
                $headers = $rowData;
            } else {
                $data[] = $rowData;
            }
        }

        return [
            'headers' => $headers,
            'data' => $data
        ];
    }
}
