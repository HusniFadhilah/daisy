<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorangDataExcel extends Model
{
    protected $table = 'borang_data_excel';

    protected $fillable = [
        'id_pengajuan',
        'id_degree_level',
        'id_elemen',
        'id_borang_import',
        'sheet_name',
        'elemen_kode',
        'table_index',
        'table_title',
        'id_dataset_borang',
        'headers',
        'rows',
        'status_review',
        'catatan_review',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'rows' => 'array',
        'reviewed_at' => 'datetime',
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeForPengajuan($query, $pengajuanId)
    {
        return $query->where('id_pengajuan', $pengajuanId);
    }

    public function scopeForSheet($query, $sheetName)
    {
        return $query->where('sheet_name', $sheetName);
    }

    public function scopeForElemen($query, $elemenKode)
    {
        return $query->where('elemen_kode', $elemenKode);
    }

    public function scopeRaw($query)
    {
        return $query->where('status_review', 'raw');
    }

// ==========================================
// HELPERS
// ==========================================

    /**
     * Render sebagai HTML table untuk preview UI
     */
    public function toHtml(): string
    {
        $html = '<table class="table table-bordered table-sm">';

        // Headers
        if (!empty($this->headers)) {
            $html .= '<thead class="table-light">';
            foreach ($this->headers as $headerRow) {
                $html .= '<tr>';
                foreach ($headerRow as $cell) {
                    if ($cell !== null && $cell !== '') {
                        $html .= '<th>' . htmlspecialchars((string)$cell) . '</th>';
                    }
                }
                $html .= '</tr>';
            }
            $html .= '</thead>';
        }

        // Rows
        $html .= '<tbody>';
        foreach ($this->rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)($cell ?? '')) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>
</table>';

        return $html;
    }

    /**
     * Copy ke borang_data setelah di-approve
     */
    public function syncToBorangData(): BorangData
    {
        $datasetId = "LKPS_{$this->sheet_name}_T{$this->table_index}";

        return BorangData::updateOrCreate(
            [
                'id_pengajuan' => $this->id_pengajuan,
                'dataset_id' => $datasetId,
            ],
            [
                'id_degree_level' => $this->id_degree_level,
                'id_borang_import' => $this->id_borang_import,
                'id_elemen' => $this->id_elemen,
                'id_dataset_borang' => $this->id_dataset_borang,
                'nilai' => json_encode([
                    'sheet' => $this->sheet_name,
                    'table_index' => $this->table_index,
                    'table_title' => $this->table_title,
                    'headers' => $this->headers,
                    'rows' => $this->rows,
                ], JSON_UNESCAPED_UNICODE),
                'is_template' => false,
            ]
        );
    }
}
