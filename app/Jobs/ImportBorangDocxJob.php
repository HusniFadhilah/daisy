<?php
// app/Jobs/ImportBorangDocxJob.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PengajuanAkreditasi;
use App\Models\BorangData;
use App\Models\BorangImport;
use App\Models\ElemenStandar;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use Illuminate\Support\Facades\Log;

class ImportBorangDocxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pengajuanId;
    protected $filePath;

    public function __construct($pengajuanId, $filePath)
    {
        $this->pengajuanId = $pengajuanId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $borangImport = null;

        try {
            Log::info('🚀 IMPORT BORANG - Parse Row-based Structure');
            Log::info('📁 File: ' . $this->filePath);

            if (!file_exists($this->filePath)) {
                throw new \Exception('File not found');
            }

            $pengajuan = PengajuanAkreditasi::findOrFail($this->pengajuanId);

            $borangImport = BorangImport::where('id_pengajuan', $this->pengajuanId)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($borangImport) {
                $borangImport->markAsProcessing();
            }

            $phpWord = IOFactory::load($this->filePath);

            Log::info('');
            Log::info('PROCESSING TABLES');
            Log::info('=====================================');

            $totalParsed = 0;
            $tableIndex = 0;

            foreach ($phpWord->getSections() as $sectionIdx => $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof Table) {
                        $tableText = $this->extractTableText($element);

                        // Check if this is elemen box
                        if (preg_match('/\b([DEPILLAR])\.(\d+)\.\s+/i', $tableText, $matches)) {
                            $elemenCode = $matches[1] . '.' . $matches[2];

                            Log::info('');
                            Log::info("┌─────────────────────────────");
                            Log::info("│ 📍 TABLE #{$tableIndex}: {$elemenCode}");

                            // ✅ PARSE ROW 2 (description + nested tables)
                            $deskripsi = '';
                            $tablesBuffer = [];

                            $rows = $element->getRows();

                            if (count($rows) >= 2) {
                                // Row 2 contains description + nested tables
                                $descriptionRow = $rows[1];

                                foreach ($descriptionRow->getCells() as $cell) {
                                    $this->parseCell($cell, $deskripsi, $tablesBuffer);
                                }

                                Log::info("│ 📝 Desc: " . strlen($deskripsi) . " chars");
                                Log::info("│ 📊 Tables: " . count($tablesBuffer));
                            } else {
                                Log::warning("│ ⚠️  Table has < 2 rows");
                            }

                            // Save
                            $saved = $this->saveBorangData(
                                $pengajuan,
                                $elemenCode,
                                $deskripsi,
                                $tablesBuffer,
                                $borangImport
                            );

                            if ($saved) {
                                $totalParsed++;
                                Log::info("│ 💾 SAVED");
                            }

                            Log::info("└─────────────────────────────");
                        }

                        $tableIndex++;
                    }
                }
            }

            Log::info('');
            Log::info("✅ COMPLETED: Parsed {$totalParsed} elements");

            if ($borangImport) {
                $borangImport->update([
                    'status' => 'completed',
                    'parsed_sections' => $totalParsed,
                    'completed_at' => now(),
                ]);
            }

            $pengajuan->update([
                'borang_status' => 'imported',
                'borang_imported_at' => now()
            ]);

            if (file_exists($this->filePath)) {
                @unlink($this->filePath);
            }
        } catch (\Exception $e) {
            Log::error('❌ ERROR: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($borangImport) {
                $borangImport->markAsFailed(['error' => $e->getMessage()]);
            }

            throw $e;
        }
    }

    /**
     * ✅ Parse cell for text and nested tables
     */
    private function parseCell($cell, &$deskripsi, &$tablesBuffer)
    {
        foreach ($cell->getElements() as $element) {

            // Extract text
            if ($element instanceof Text || $element instanceof TextRun) {
                $text = $this->extractTextContent($element);

                // ✅ FIXED: Better filtering
                if (!$this->isPlaceholderText($text)) {
                    $cleaned = trim($text);
                    if (!empty($cleaned)) {
                        $deskripsi .= $cleaned . "\n";
                    }
                }
            }

            // Extract nested tables
            if ($element instanceof Table) {
                $tableHtml = $this->convertTableToHtml($element);

                if ($this->hasRealTableData($tableHtml)) {
                    $tablesBuffer[] = [
                        'html' => $tableHtml,
                        'is_template' => false
                    ];
                }
            }
        }
    }

    /**
     * ✅ FIXED: Precise placeholder detection
     */
    private function isPlaceholderText($text)
    {
        $text = trim($text);
        if (empty($text)) return true;

        $textLower = strtolower($text);

        // Skip ONLY specific patterns

        // 1. Label italic di template (e.g., "Deskripsi legalitas program dan tata pamong.")
        if (preg_match('/^deskripsi\s+[a-z\s]+\.$/i', $text)) {
            return true;
        }

        // 2. Table labels (e.g., "Tabel D.1.a Dokumen Legalitas")
        if (preg_match('/^tabel\s+[a-z]\.\d+\.[a-z]/i', $text)) {
            return true;
        }

        // 3. Explicit placeholders
        $placeholders = [
            '[mohon isi',
            'mohon isi sesuai',
        ];

        foreach ($placeholders as $placeholder) {
            if (strpos($textLower, $placeholder) !== false) {
                return true;
            }
        }

        // 4. Single word labels (exact match only)
        $singleWords = ['deskripsi', 'tabel', 'keterangan', 'catatan', 'no', 'nama'];
        if (in_array($textLower, $singleWords)) {
            return true;
        }

        // 5. Too short (< 10 chars likely noise)
        if (strlen($text) < 10) {
            return true;
        }

        // ✅ Otherwise, it's real content
        return false;
    }

    private function parseDescriptionBox($table, &$deskripsi, &$tablesBuffer)
    {
        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                foreach ($cell->getElements() as $element) {

                    if ($element instanceof Text || $element instanceof TextRun) {
                        $text = $this->extractTextContent($element);

                        if (!$this->isPlaceholderOrLabel($text)) {
                            $cleaned = trim($text);
                            if (!empty($cleaned)) {
                                $deskripsi .= $cleaned . "\n";
                            }
                        }
                    }

                    if ($element instanceof Table) {
                        $tableHtml = $this->convertTableToHtml($element);

                        // ✅ ONLY save if table has REAL data
                        if ($this->hasRealTableData($tableHtml)) {
                            $tablesBuffer[] = [
                                'html' => $tableHtml,
                                'is_template' => false
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * ✅ NEW: Check if table has real data (not just empty template)
     */
    private function hasRealTableData($tableHtml)
    {
        if (empty($tableHtml) || strlen($tableHtml) < 100) {
            return false;
        }

        // Extract all cell contents (td only, skip th)
        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $tableHtml, $cells);

        if (empty($cells[1])) {
            return false;
        }

        $nonEmptyCells = 0;
        $totalCells = 0;

        foreach ($cells[1] as $cellHtml) {
            $totalCells++;

            // Clean cell content
            $cellText = strip_tags($cellHtml);
            $cellText = html_entity_decode($cellText);
            $cellText = trim($cellText);
            $cellText = str_replace(['&nbsp;', ' '], '', $cellText);

            // Skip if it's just a number (row numbering: 1, 2, 3)
            if (is_numeric($cellText) && (int)$cellText <= 10) {
                continue;
            }

            // Count as filled if has meaningful content
            if (!empty($cellText) && strlen($cellText) > 0) {
                $nonEmptyCells++;
            }
        }

        // ✅ Require at least 3 cells with real data
        // (not counting row numbers)
        return $nonEmptyCells >= 3;
    }

    private function isPlaceholderOrLabel($text)
    {
        $text = strtolower(trim($text));
        if (empty($text)) return true;

        $skipPatterns = [
            'deskripsi',
            '[mohon isi',
            'mohon isi',
            'sesuai dengan kondisi',
            'tabel', // Skip "Tabel D.1.a" labels
        ];

        foreach ($skipPatterns as $pattern) {
            if (strpos($text, $pattern) !== false) {
                return true;
            }
        }

        return strlen($text) < 20;
    }

    private function isValidDataTable($html)
    {
        if (strpos($html, '<table') === false) return false;

        preg_match_all('/<tr[^>]*>/', $html, $matches);
        return !empty($matches[0]) && count($matches[0]) >= 2;
    }

    private function saveBorangData($pengajuan, $elemenCode, $deskripsi, $tablesData, $borangImport = null)
    {
        $elemen = ElemenStandar::where('kode_elemen', $elemenCode)->first();

        if (!$elemen) {
            Log::warning("│   ⚠️ {$elemenCode} not in DB");
            return false;
        }

        $savedCount = 0;

        // ✅ Save description with consistent WHERE clause
        $descKey = 'desc_' . $elemen->id;
        $cleanedDeskripsi = $this->cleanText($deskripsi);

        BorangData::updateOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'dataset_id' => $descKey  // ✅ 2 fields only
            ],
            [
                'nilai' => $cleanedDeskripsi,
                'id_borang_import' => $borangImport ? $borangImport->id : null  // ✅ Saved in data
            ]
        );
        $savedCount++;

        // ✅ Save tables with consistent WHERE clause
        if (!empty($tablesData)) {
            $datasets = $elemen->datasetBorang()
                ->where('tipe_field', 'table')
                ->orderBy('urutan')
                ->get();

            foreach ($tablesData as $index => $tableData) {
                if (isset($datasets[$index])) {
                    $dataset = $datasets[$index];

                    $isTemplate = is_array($tableData) ? ($tableData['is_template'] ?? false) : false;
                    $html = is_array($tableData) ? $tableData['html'] : $tableData;

                    BorangData::updateOrCreate(
                        [
                            'id_pengajuan' => $pengajuan->id,
                            'dataset_id' => $dataset->kode  // ✅ 2 fields only
                        ],
                        [
                            'nilai' => $html,
                            'is_template' => $isTemplate,
                            'id_dataset_borang' => $dataset->id,
                            'id_borang_import' => $borangImport ? $borangImport->id : null  // ✅ Saved in data
                        ]
                    );
                    $savedCount++;
                }
            }
        }

        if ($borangImport) {
            $borangImport->increment('parsed_sections', 1);
            $borangImport->increment('parsed_tables', count($tablesData));
        }

        return $savedCount > 0;
    }

    private function cleanText($text)
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);

        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines);

        return trim(implode("\n", $lines));
    }

    private function extractTableText($table)
    {
        $text = '';
        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                foreach ($cell->getElements() as $element) {
                    if ($element instanceof Text || $element instanceof TextRun) {
                        $text .= $this->extractTextContent($element) . ' ';
                    }
                }
            }
        }
        return trim($text);
    }

    private function convertTableToHtml($table)
    {
        $html = '<table style="width:100%;border-collapse:collapse;">';

        $isFirstRow = true;
        foreach ($table->getRows() as $row) {
            $html .= '<tr>';

            foreach ($row->getCells() as $cell) {
                $cellTag = $isFirstRow ? 'th' : 'td';
                $style = 'border:1px solid #ddd;padding:8px;';

                if ($isFirstRow) {
                    $style .= 'background-color:#f2f2f2;font-weight:bold;text-align:center;';
                }

                $html .= '<' . $cellTag . ' style="' . $style . '">';

                $cellText = '';
                foreach ($cell->getElements() as $element) {
                    if ($element instanceof Text || $element instanceof TextRun) {
                        $cellText .= $this->extractTextContent($element);
                    }
                }

                $html .= !empty(trim($cellText)) ? htmlspecialchars(trim($cellText)) : '&nbsp;';
                $html .= '</' . $cellTag . '>';
            }

            $html .= '</tr>';
            $isFirstRow = false;
        }

        $html .= '</table>';
        return $html;
    }

    private function extractTextContent($element)
    {
        if ($element instanceof Text) {
            return $element->getText();
        }

        if ($element instanceof TextRun) {
            $text = '';
            foreach ($element->getElements() as $subElement) {
                if ($subElement instanceof Text) {
                    $text .= $subElement->getText();
                }
            }
            return $text;
        }

        return '';
    }
}
