<?php
// app/Services/BorangParserService.php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use App\Models\BorangImport;
use App\Models\BorangData;
use App\Models\ElemenStandar;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BorangParserService
{
    private $debugMode = true;

    public function parseBorangDOCX(PengajuanDokumen $dokumen): BorangImport
    {
        DB::beginTransaction();

        try {
            $this->log("=== START PARSING ===");
            $this->log("Dokumen: " . $dokumen->original_filename);

            $import = BorangImport::create([
                'id_pengajuan' => $dokumen->id_pengajuan,
                'id_dokumen' => $dokumen->id,
                'original_filename' => $dokumen->original_filename,
                'status' => 'parsing',
                'imported_by' => auth()->id() ?? $dokumen->uploaded_by,
                'imported_at' => now(),
            ]);

            $filePath = Storage::disk('public')->path($dokumen->path_file);

            if (!file_exists($filePath)) {
                throw new \Exception('File tidak ditemukan: ' . $filePath);
            }

            $this->log("Loading DOCX file...");
            $phpWord = IOFactory::load($filePath);

            $this->log("Extracting structured data...");
            $parsedData = $this->extractStructuredData($phpWord);

            $import->update([
                'kata_pengantar' => $parsedData['kata_pengantar'] ?? null,
                'ringkasan' => $parsedData['ringkasan'] ?? null,
                'suplemen' => !empty($parsedData['suplemen']) ? implode("\n\n", $parsedData['suplemen']) : null,
            ]);

            $this->log("Saving to BorangData...");
            $savedCount = $this->saveToBorangData($dokumen->pengajuan, $parsedData, $import);

            $import->update([
                'status' => 'completed',
                'total_sections' => count($parsedData['sections']),
                'parsed_sections' => $savedCount,
                'total_tables' => collect($parsedData['sections'])->sum(fn($s) => count($s['tables'])),
                'parsed_tables' => collect($parsedData['sections'])->sum(fn($s) => count($s['tables'])),
                'completed_at' => now(),
            ]);

            DB::commit();

            $this->log("=== PARSING SUCCESS ===");
            $this->log("Saved {$savedCount} sections to BorangData");

            return $import->fresh();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Borang parsing failed: ' . $e->getMessage(), [
                'dokumen_id' => $dokumen->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($import)) {
                $import->update([
                    'status' => 'failed',
                    'parsing_notes' => $e->getMessage(),
                    'parsing_errors' => [
                        'message' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                    ]
                ]);
            }

            throw $e;
        }
    }

    /**
     * ✅ FIXED: Extract structured data with LINE-BY-LINE filtering
     */
    private function extractStructuredData($phpWord): array
    {
        $kataPengantar = '';
        $ringkasan = '';
        $suplemenContent = [];
        $sections = [];

        $currentSection = null;
        $currentMode = null;
        $sectionPosition = 0;

        foreach ($phpWord->getSections() as $wordSection) {
            foreach ($wordSection->getElements() as $element) {

                // ✅ Process TABLE content
                if ($element instanceof Table) {
                    $tableText = $this->extractTableText($element);

                    $this->log("📋 Found table with text length: " . strlen($tableText));

                    // ✅ CRITICAL: Clean table text LINE BY LINE
                    if ($currentMode === 'kata_pengantar' && !empty(trim($tableText))) {
                        $cleaned = $this->cleanFrontMatterTableText($tableText);
                        if (!empty($cleaned)) {
                            $kataPengantar .= $cleaned . "\n";
                            $this->log("✅ Added table content to KATA PENGANTAR: " . strlen($cleaned) . " chars");
                        } else {
                            $this->log("⚠️ Table cleaned to empty for KATA PENGANTAR");
                        }
                    } elseif ($currentMode === 'ringkasan' && !empty(trim($tableText))) {
                        $cleaned = $this->cleanFrontMatterTableText($tableText);
                        if (!empty($cleaned)) {
                            $ringkasan .= $cleaned . "\n";
                            $this->log("✅ Added table content to RINGKASAN: " . strlen($cleaned) . " chars");
                        } else {
                            $this->log("⚠️ Table cleaned to empty for RINGKASAN");
                        }
                    } elseif ($currentMode === 'suplemen' && !empty(trim($tableText))) {
                        $cleaned = $this->cleanFrontMatterTableText($tableText);
                        if (!empty($cleaned)) {
                            $suplemenContent[] = $cleaned;
                            $this->log("✅ Added table content to SUPLEMEN");
                        }
                    } elseif ($currentMode === 'content' && $currentSection) {
                        $tableResult = $this->parseTableContent($element);

                        if (!empty($tableResult['description'])) {
                            $currentSection['konten_narasi'] .= $tableResult['description'] . "\n";
                        }

                        if (!empty($tableResult['tables'])) {
                            $currentSection['tables'] = array_merge(
                                $currentSection['tables'],
                                $tableResult['tables']
                            );
                        }
                    }

                    continue;
                }

                // Extract text from non-table elements
                $text = $this->extractTextContent($element);
                if (empty($text)) continue;

                $this->log("📝 Text: " . substr($text, 0, 100));

                // Detect section headers
                if ($this->isKataPengantarHeader($text)) {
                    $currentMode = 'kata_pengantar';
                    $this->log("✓ Entering KATA PENGANTAR section");
                    continue;
                }

                if ($this->isRingkasanHeader($text)) {
                    $currentMode = 'ringkasan';
                    $this->log("✓ Entering RINGKASAN section");
                    continue;
                }

                if ($this->isSuplemenHeader($text)) {
                    $currentMode = 'suplemen';
                    $this->log("✓ Entering SUPLEMEN section");
                    continue;
                }

                if ($this->isDaftarIsiHeader($text)) {
                    $currentMode = 'daftar_isi';
                    $this->log("✓ Entering DAFTAR ISI - stopping front matter");
                    continue;
                }

                // Detect content sections
                if ($sectionMatch = $this->detectSectionHeader($text)) {
                    $currentMode = 'content';
                    $this->log("✓ Section: {$sectionMatch['kode']} - {$sectionMatch['judul']}");

                    if ($currentSection) {
                        $sections[] = $currentSection;
                    }

                    $currentSection = [
                        'kode_section' => $sectionMatch['kode'],
                        'judul_section' => $sectionMatch['judul'],
                        'konten_narasi' => '',
                        'position' => $sectionPosition++,
                        'tables' => [],
                    ];
                    continue;
                }

                // Skip instruction text
                if ($this->isInstructionText($text)) {
                    $this->log("⚠️ Skipped instruction: " . substr($text, 0, 50));
                    continue;
                }

                // Skip placeholder
                if ($this->isPlaceholderText($text)) {
                    $this->log("⚠️ Skipped placeholder: " . substr($text, 0, 50));
                    continue;
                }

                // Append content based on mode
                switch ($currentMode) {
                    case 'kata_pengantar':
                        $kataPengantar .= $text . "\n";
                        $this->log("→ Added to KATA PENGANTAR: " . substr($text, 0, 50));
                        break;

                    case 'ringkasan':
                        $ringkasan .= $text . "\n";
                        $this->log("→ Added to RINGKASAN: " . substr($text, 0, 50));
                        break;

                    case 'suplemen':
                        $suplemenContent[] = $text;
                        $this->log("→ Added to SUPLEMEN: " . substr($text, 0, 50));
                        break;

                    case 'content':
                        if ($currentSection) {
                            $currentSection['konten_narasi'] .= $text . "\n";
                        }
                        break;

                    case 'daftar_isi':
                        break;
                }
            }
        }

        if ($currentSection) {
            $sections[] = $currentSection;
        }

        // Clean all content
        $kataPengantar = $this->cleanText($kataPengantar);
        $ringkasan = $this->cleanText($ringkasan);

        $this->log("\n=== SUMMARY ===");
        $this->log("Kata Pengantar: " . (empty($kataPengantar) ? 'Empty' : strlen($kataPengantar) . ' chars'));
        $this->log("Ringkasan: " . (empty($ringkasan) ? 'Empty' : strlen($ringkasan) . ' chars'));
        $this->log("Suplemen Items: " . count($suplemenContent));
        $this->log("Total Sections: " . count($sections));
        $totalTables = collect($sections)->sum(fn($s) => count($s['tables']));
        $this->log("Total Tables: " . $totalTables);

        return [
            'kata_pengantar' => $kataPengantar,
            'ringkasan' => $ringkasan,
            'suplemen' => array_values(array_filter(array_map('trim', $suplemenContent))),
            'sections' => $sections,
        ];
    }

    /**
     * ✅ NEW: Clean front matter table text LINE BY LINE
     */
    private function cleanFrontMatterTableText(string $tableText): string
    {
        $lines = explode("\n", $tableText);
        $cleanedLines = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) continue;

            // ✅ Skip ONLY instruction/placeholder lines (not the whole block)
            if ($this->isInstructionText($line)) {
                $this->log("  ⊗ Skipped instruction line: " . substr($line, 0, 60));
                continue;
            }

            if ($this->isPlaceholderText($line)) {
                $this->log("  ⊗ Skipped placeholder line: " . substr($line, 0, 60));
                continue;
            }

            // ✅ Keep real content
            $cleanedLines[] = $line;
            $this->log("  ✓ Kept content line: " . substr($line, 0, 60));
        }

        return implode("\n", $cleanedLines);
    }

    /**
     * Extract ALL text from table
     */
    private function extractTableText(Table $table): string
    {
        $text = '';

        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                foreach ($cell->getElements() as $cellElement) {
                    if ($cellElement instanceof Table) {
                        $text .= $this->extractTableText($cellElement) . "\n";
                    } else {
                        $cellText = $this->extractTextContent($cellElement);
                        if (!empty(trim($cellText))) {
                            $text .= $cellText . "\n";
                        }
                    }
                }
            }
        }

        return trim($text);
    }

    /**
     * Extract text from element
     */
    private function extractTextContent($element): string
    {
        if ($element instanceof Text) {
            $text = $element->getText();
            return is_string($text) ? $text : '';
        }

        if ($element instanceof TextRun) {
            $text = '';
            foreach ($element->getElements() as $subElement) {
                if ($subElement instanceof Text) {
                    $subText = $subElement->getText();
                    $text .= is_string($subText) ? $subText : '';
                }
            }
            return $text;
        }

        return '';
    }

    /**
     * Parse table content
     */
    private function parseTableContent(Table $table): array
    {
        $description = '';
        $tables = [];

        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                $this->parseCell($cell, $description, $tables);
            }
        }

        return [
            'description' => $this->cleanText($description),
            'tables' => $tables,
        ];
    }

    /**
     * Parse cell recursively
     */
    private function parseCell($cell, &$description, &$tablesBuffer)
    {
        foreach ($cell->getElements() as $element) {

            if ($element instanceof Text || $element instanceof TextRun) {
                $text = $this->extractTextContent($element);

                if (!$this->isPlaceholderText($text)) {
                    $cleaned = trim($text);
                    if (!empty($cleaned)) {
                        $description .= $cleaned . "\n";
                    }
                }
            }

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
     * Convert table to HTML
     */
    private function convertTableToHtml(Table $table): string
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

    /**
     * Check if table has real data
     */
    private function hasRealTableData($tableHtml): bool
    {
        if (empty($tableHtml) || strlen($tableHtml) < 100) {
            return false;
        }

        if (strpos($tableHtml, '<table') === false) {
            return false;
        }

        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $tableHtml, $cells);

        if (empty($cells[1])) {
            return false;
        }

        $nonEmptyCells = 0;

        foreach ($cells[1] as $cellHtml) {
            $cellText = strip_tags($cellHtml);
            $cellText = html_entity_decode($cellText);
            $cellText = trim($cellText);
            $cellText = str_replace(['&nbsp;', ' '], '', $cellText);

            if (is_numeric($cellText) && (int)$cellText <= 10) {
                continue;
            }

            if (!empty($cellText) && strlen($cellText) > 0) {
                $nonEmptyCells++;
            }
        }

        return $nonEmptyCells >= 3;
    }

    /**
     * Save to BorangData
     */
    private function saveToBorangData($pengajuan, array $parsedData, BorangImport $import): int
    {
        $savedCount = 0;

        if (!empty($parsedData['kata_pengantar'])) {
            BorangData::updateOrCreate(
                [
                    'id_pengajuan' => $pengajuan->id,
                    'dataset_id' => 'kata_pengantar'
                ],
                [
                    'nilai' => $parsedData['kata_pengantar'],
                    'id_borang_import' => $import->id
                ]
            );
            $savedCount++;
        }

        if (!empty($parsedData['ringkasan'])) {
            BorangData::updateOrCreate(
                [
                    'id_pengajuan' => $pengajuan->id,
                    'dataset_id' => 'ringkasan'
                ],
                [
                    'nilai' => $parsedData['ringkasan'],
                    'id_borang_import' => $import->id
                ]
            );
            $savedCount++;
        }

        if (!empty($parsedData['suplemen'])) {
            BorangData::updateOrCreate(
                [
                    'id_pengajuan' => $pengajuan->id,
                    'dataset_id' => 'suplemen'
                ],
                [
                    'nilai' => is_array($parsedData['suplemen'])
                        ? implode("\n\n", $parsedData['suplemen'])
                        : $parsedData['suplemen'],
                    'id_borang_import' => $import->id
                ]
            );
            $savedCount++;
        }

        foreach ($parsedData['sections'] as $sectionData) {
            $elemen = ElemenStandar::where('kode_elemen', $sectionData['kode_section'])->first();

            if (!$elemen) {
                $this->log("⚠️ Elemen {$sectionData['kode_section']} not found in database");
                continue;
            }

            $descKey = 'desc_' . $elemen->id;
            $cleanedDeskripsi = $this->cleanText($sectionData['konten_narasi']);

            if (!empty($cleanedDeskripsi)) {
                BorangData::updateOrCreate(
                    [
                        'id_pengajuan' => $pengajuan->id,
                        'dataset_id' => $descKey
                    ],
                    [
                        'nilai' => $cleanedDeskripsi,
                        'id_borang_import' => $import->id
                    ]
                );
                $savedCount++;
                $this->log("✓ Saved description for {$sectionData['kode_section']}");
            }

            if (!empty($sectionData['tables'])) {
                $datasets = $elemen->datasetBorang()
                    ->where('tipe_field', 'table')
                    ->orderBy('urutan')
                    ->get();

                foreach ($sectionData['tables'] as $index => $tableData) {
                    if (isset($datasets[$index])) {
                        $dataset = $datasets[$index];

                        $html = is_array($tableData) ? $tableData['html'] : $tableData;

                        BorangData::updateOrCreate(
                            [
                                'id_pengajuan' => $pengajuan->id,
                                'dataset_id' => $dataset->kode
                            ],
                            [
                                'nilai' => $html,
                                'id_dataset_borang' => $dataset->id,
                                'id_borang_import' => $import->id
                            ]
                        );
                        $savedCount++;
                        $this->log("  ✓ Saved table {$dataset->kode}");
                    }
                }
            }
        }

        return $savedCount;
    }

    /**
     * Clean text
     */
    private function cleanText($text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);

        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines);

        return trim(implode("\n", $lines));
    }

    // Header detection methods
    private function isKataPengantarHeader(string $text): bool
    {
        return preg_match('/^KATA\s+PENGANTAR$/i', trim($text));
    }

    private function isRingkasanHeader(string $text): bool
    {
        return preg_match('/^RINGKASAN$/i', trim($text));
    }

    private function isSuplemenHeader(string $text): bool
    {
        return preg_match('/^Suplemen\s+Program\s+Studi/i', $text);
    }

    private function isDaftarIsiHeader(string $text): bool
    {
        return preg_match('/^DAFTAR\s+ISI$/i', trim($text));
    }

    private function detectSectionHeader(string $text): ?array
    {
        if (preg_match('/^([DEPLIARK])\.(\d+)\s+(.+)$/i', $text, $matches)) {
            return [
                'kode' => strtoupper($matches[1]) . '.' . $matches[2],
                'judul' => trim($matches[3]),
            ];
        }

        return null;
    }

    /**
     * Check if placeholder text
     */
    private function isPlaceholderText($text): bool
    {
        $text = trim($text);
        if (empty($text)) return true;

        $textLower = strtolower($text);

        if (preg_match('/^deskripsi\s+[a-z\s]+\.$/i', $text)) {
            return true;
        }

        if (preg_match('/^tabel\s+[a-z]\.\d+\.[a-z]/i', $text)) {
            return true;
        }

        $placeholders = [
            '[mohon isi',
            'mohon isi sesuai',
            'mohon jangan dihapus',
        ];

        foreach ($placeholders as $placeholder) {
            if (strpos($textLower, $placeholder) !== false) {
                return true;
            }
        }

        $singleWords = ['deskripsi', 'tabel', 'keterangan', 'catatan', 'no', 'nama'];
        if (in_array($textLower, $singleWords)) {
            return true;
        }

        if (strlen($text) < 10) {
            return true;
        }

        return false;
    }

    /**
     * Check if instruction text
     */
    private function isInstructionText(string $text): bool
    {
        $text = trim($text);

        if (empty($text) || strlen($text) < 5) return true;

        if (preg_match('/^Deskripsi\s+.+?\s*\(Mohon jangan dihapus\)\s*$/i', $text)) {
            return true;
        }

        if (preg_match('/^\[Mohon isi.+?maksimal\s+\d+\s+kata.+?\]$/is', $text)) {
            return true;
        }

        $instructionKeywords = [
            'Mohon jangan dihapus',
            'Mohon isi deskripsi di sini',
            'maksimal 1000 kata',
            'maksimal 500 kata',
            'sesuai dengan kondisi program studi',
        ];

        foreach ($instructionKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return true;
            }
        }

        if (preg_match('/^\[.*?\]$/', $text) && strlen($text) < 200) {
            return true;
        }

        return false;
    }

    /**
     * Debug log
     */
    private function log(string $message): void
    {
        if ($this->debugMode) {
            Log::info('[BorangParser] ' . $message);
        }
    }
}
