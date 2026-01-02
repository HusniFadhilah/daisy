<?php
// app/Services/BorangParserService.php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use App\Models\BorangImport;
use App\Models\BorangSection;
use App\Models\BorangTable;
use App\Models\ElemenStandar;
use App\Models\DatasetBorang;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BorangParserService
{
    private $debugMode = true;

    /**
     * Parse DOCX borang dengan rules spesifik
     */
    public function parseBorangDOCX(PengajuanDokumen $dokumen): BorangImport
    {
        DB::beginTransaction();

        try {
            $this->log("=== START PARSING ===");
            $this->log("Dokumen: " . $dokumen->original_filename);

            // Create import record
            $import = BorangImport::create([
                'id_pengajuan' => $dokumen->id_pengajuan,
                'id_dokumen' => $dokumen->id,
                'original_filename' => $dokumen->original_filename,
                'status' => 'parsing',
                'imported_by' => auth()->id(),
                'imported_at' => now(),
            ]);

            // Get file path
            $filePath = Storage::disk('public')->path($dokumen->path_file);

            if (!file_exists($filePath)) {
                throw new \Exception('File tidak ditemukan: ' . $filePath);
            }

            // Load DOCX
            $this->log("Loading DOCX file...");
            $phpWord = IOFactory::load($filePath);

            // Parse document structure
            $this->log("Extracting structured data...");
            $parsedData = $this->extractStructuredData($phpWord);

            // Save parsed data
            $this->log("Saving to database...");
            $this->saveParsedData($import, $parsedData);

            // Update import status
            $import->update([
                'status' => 'completed',
                'total_sections' => count($parsedData['sections']),
                'total_tables' => collect($parsedData['sections'])->sum(fn($s) => count($s['tables'])),
                'parsed_sections' => count($parsedData['sections']),
                'parsed_tables' => collect($parsedData['sections'])->sum(fn($s) => count($s['tables'])),
            ]);

            DB::commit();

            $this->log("=== PARSING SUCCESS ===");

            return $import->fresh(['sections.tables']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Borang parsing failed: ' . $e->getMessage(), [
                'dokumen_id' => $dokumen->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update status to failed
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
     * Extract structured data from DOCX
     */
    private function extractStructuredData($phpWord): array
    {
        $sections = [];
        $currentSection = null;
        $sectionPosition = 0;
        $tablePosition = 0;

        $mohonIsiMarkerFound = false;
        $pendingTable = null;
        $lastParagraphText = '';

        foreach ($phpWord->getSections() as $wordSection) {
            $elements = $wordSection->getElements();

            foreach ($elements as $element) {
                // Extract text from paragraphs
                $text = $this->extractTextFromElement($element);

                if (empty($text)) continue;

                $this->log("Para: " . substr($text, 0, 100));

                // Check for "Mohon isi di sini" marker
                if ($this->isMohonIsiMarker($text)) {
                    $mohonIsiMarkerFound = true;
                    $this->log("✓ Found 'Mohon isi di sini' marker");
                    continue;
                }

                // Detect section header (D.1, E.1, E.2, etc)
                if ($sectionMatch = $this->detectSectionHeader($text)) {
                    $this->log("✓ Section: {$sectionMatch['kode']} - {$sectionMatch['judul']}");

                    // Save previous section
                    if ($currentSection) {
                        $sections[] = $currentSection;
                    }

                    // Start new section
                    $currentSection = [
                        'kode_section' => $sectionMatch['kode'],
                        'judul_section' => $sectionMatch['judul'],
                        'konten_narasi' => '',
                        'position' => $sectionPosition++,
                        'tables' => [],
                    ];

                    $tablePosition = 0;
                    $mohonIsiMarkerFound = false;
                }
                // Detect table header (Tabel E.1.1, Tabel E.1.2, etc)
                elseif ($tableMatch = $this->detectTableHeader($text)) {
                    $this->log("✓ Table: {$tableMatch['kode']} - {$tableMatch['judul']}");

                    $pendingTable = [
                        'kode_tabel' => $tableMatch['kode'],
                        'judul_tabel' => $tableMatch['judul'],
                        'position' => $tablePosition++,
                    ];
                }
                // Store narasi content before "Mohon isi di sini"
                elseif ($currentSection && !$mohonIsiMarkerFound) {
                    $currentSection['konten_narasi'] .= $text . "\n";
                }

                // Process tables (only after "Mohon isi di sini")
                if ($element instanceof Table && $mohonIsiMarkerFound) {
                    $this->log("✓ Processing table after marker");

                    $tableData = $this->extractTableData($element);

                    if ($pendingTable) {
                        $tableRecord = array_merge($pendingTable, $tableData);
                    } else {
                        // Auto-generate table info if no header found
                        $autoKode = $currentSection ? $currentSection['kode_section'] . '.' . ($tablePosition + 1) : 'AUTO.' . ($tablePosition + 1);
                        $tableRecord = array_merge([
                            'kode_tabel' => $autoKode,
                            'judul_tabel' => 'Tabel tanpa judul',
                            'position' => $tablePosition++,
                        ], $tableData);
                    }

                    if ($currentSection) {
                        $currentSection['tables'][] = $tableRecord;
                        $this->log("  → Added to section: " . $currentSection['kode_section']);
                    }

                    $pendingTable = null;
                    $mohonIsiMarkerFound = false; // Reset marker
                }
            }
        }

        // Save last section
        if ($currentSection) {
            $sections[] = $currentSection;
        }

        $this->log("\n=== SUMMARY ===");
        $this->log("Total Sections: " . count($sections));
        $totalTables = collect($sections)->sum(fn($s) => count($s['tables']));
        $this->log("Total Tables: " . $totalTables);

        return [
            'sections' => $sections,
        ];
    }

    /**
     * Extract text from element
     */
    private function extractTextFromElement($element): string
    {
        $text = '';

        if (method_exists($element, 'getText')) {
            $text = $element->getText();
        } elseif ($element instanceof TextRun) {
            foreach ($element->getElements() as $textElement) {
                if ($textElement instanceof Text) {
                    $text .= $textElement->getText() . ' ';
                }
            }
        }

        return trim($text);
    }

    /**
     * Check if text is "Mohon isi di sini" marker
     */
    private function isMohonIsiMarker(string $text): bool
    {
        $patterns = [
            '/Mohon\s+isi\s+di\s+sini/i',
            '/Mohon\s+diisi\s+disini/i',
            '/Mohon\s+isi\s+disini/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect section header (D.1, E.1, etc)
     */
    private function detectSectionHeader(string $text): ?array
    {
        // Pattern: D.1, E.1, E.2, P.1, L.1, I.1, A.1, R.1
        if (preg_match('/^([DEPLIARK])\.(\d+)\s+(.+)$/i', $text, $matches)) {
            return [
                'kode' => strtoupper($matches[1]) . '.' . $matches[2],
                'judul' => trim($matches[3]),
            ];
        }

        return null;
    }

    /**
     * Detect table header (Tabel E.1.1, etc)
     */
    private function detectTableHeader(string $text): ?array
    {
        // Pattern: Tabel E.1.1, Tabel D.1.1, etc
        if (preg_match('/Tabel\s+([DEPLIARK]\.\d+\.\d+)\s*[-:]?\s*(.+)/i', $text, $matches)) {
            return [
                'kode' => strtoupper($matches[1]),
                'judul' => trim($matches[2]),
            ];
        }

        return null;
    }

    /**
     * Extract table data
     */
    private function extractTableData(Table $table): array
    {
        $rows = $table->getRows();
        $data = [];
        $headers = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = $row->getCells();
            $rowData = [];

            foreach ($cells as $cell) {
                $cellText = '';

                foreach ($cell->getElements() as $cellElement) {
                    $cellText .= $this->extractTextFromElement($cellElement) . ' ';
                }

                $rowData[] = trim($cellText);
            }

            // First row as headers
            if ($rowIndex === 0) {
                $headers = $rowData;
            }

            $data[] = $rowData;
        }

        return [
            'row_count' => count($data),
            'col_count' => count($headers),
            'headers' => $headers,
            'data' => $data,
        ];
    }

    /**
     * Save parsed data to database
     */
    private function saveParsedData(BorangImport $import, array $parsedData): void
    {
        foreach ($parsedData['sections'] as $sectionData) {
            // Find matching elemen standar
            $elemen = ElemenStandar::where('kode_elemen', $sectionData['kode_section'])->first();

            // Create section
            $section = BorangSection::create([
                'id_import' => $import->id,
                'id_elemen' => $elemen?->id,
                'kode_section' => $sectionData['kode_section'],
                'judul_section' => $sectionData['judul_section'],
                'konten_narasi' => trim($sectionData['konten_narasi']),
                'position' => $sectionData['position'],
            ]);

            $this->log("Saved section: {$section->kode_section}");

            // Create tables
            foreach ($sectionData['tables'] as $tableData) {
                // Find matching dataset
                $dataset = DatasetBorang::where('kode', $tableData['kode_tabel'])->first();

                BorangTable::create([
                    'id_section' => $section->id,
                    'id_dataset' => $dataset?->id,
                    'kode_tabel' => $tableData['kode_tabel'],
                    'judul_tabel' => $tableData['judul_tabel'],
                    'row_count' => $tableData['row_count'],
                    'col_count' => $tableData['col_count'],
                    'headers' => $tableData['headers'],
                    'data' => $tableData['data'],
                    'position' => $tableData['position'],
                ]);

                $this->log("  Saved table: {$tableData['kode_tabel']}");
            }
        }
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
