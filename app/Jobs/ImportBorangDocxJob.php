<?php
// app/Jobs/ImportBorangDocxJob.php

namespace App\Jobs;

use App\Models\BorangData;
use App\Models\BorangImport;
use App\Models\ElemenStandar;
use App\Models\PengajuanAkreditasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Image;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class ImportBorangDocxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pengajuanId;
    protected $filePath;
    protected $importId;

    public function __construct($importId, $pengajuanId, $filePath)
    {
        $this->importId = $importId;
        $this->pengajuanId = $pengajuanId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $borangImport = null;

        try {
            if (!file_exists($this->filePath)) {
                throw new \Exception('File not found');
            }

            $pengajuan = PengajuanAkreditasi::findOrFail($this->pengajuanId);

            $borangImport = BorangImport::where('id', $this->importId)
                ->where('id_pengajuan', $this->pengajuanId)
                ->firstOrFail();

            if ($borangImport) {
                $borangImport->markAsProcessing();
            }

            $tempDir = storage_path('app/tmp/phpword_' . uniqid());
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0775, true);
            }
            Settings::setTempDir($tempDir);
            Settings::setOutputEscapingEnabled(true);

            // ✅ EXTRACT IMAGES FROM ZIP FIRST (backup method)
            $extractedImages = $this->extractImagesFromDocxZip();
            $extractedImageUrls = $this->saveExtractedImages($extractedImages);

            Log::error("Pre-extracted images: " . count($extractedImageUrls));

            $phpWord = IOFactory::load($this->filePath);

            $frontMatter = $this->extractFrontMatter($phpWord);
            $this->saveFrontMatterToBorangData($pengajuan, $frontMatter, $borangImport);

            $totalParsed = 0;
            $tableIndex = 0;

            foreach ($phpWord->getSections() as $sectionIdx => $section) {
                Log::error("Processing section #{$sectionIdx}");

                foreach ($section->getElements() as $elementIdx => $element) {
                    $elementClass = get_class($element);
                    Log::error("Section element #{$elementIdx}: {$elementClass}");

                    if ($element instanceof Table) {
                        $tableText = $this->extractTableText($element);

                        // Check if this is elemen box
                        if (preg_match('/\b([DEPILLAR])\.(\d+)\.\s+/i', $tableText, $matches)) {
                            $elemenCode = $matches[1] . '.' . $matches[2];

                            Log::error("Found elemen box: {$elemenCode}");

                            $deskripsi = '';
                            $tablesBuffer = [];

                            $rows = $element->getRows();

                            Log::error("Table has " . count($rows) . " rows");

                            if (count($rows) >= 2) {
                                $descriptionRow = $rows[1];

                                Log::error("Processing description row with " . count($descriptionRow->getCells()) . " cells");

                                foreach ($descriptionRow->getCells() as $cellIdx => $cell) {
                                    Log::error("Processing cell #{$cellIdx}");
                                    $this->parseCell($cell, $deskripsi, $tablesBuffer);
                                }
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
                            }
                        }

                        $tableIndex++;
                    }
                }
            }

            if ($borangImport) {
                $borangImport->update([
                    'status' => 'completed',
                    'parsed_sections' => $totalParsed,
                    'completed_at' => now(),
                ]);
            }

            if (file_exists($this->filePath)) {
                @unlink($this->filePath);
            }

            // if (is_dir($tempDir)) {
            //     $this->deleteTempDir($tempDir);
            // }
        } catch (\Exception $e) {
            Log::error("Job failed: " . $e->getMessage());
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
        Log::error("parseCell called, elements count: " . count($cell->getElements()));

        foreach ($cell->getElements() as $index => $element) {
            $elementClass = get_class($element);
            Log::error("Element #{$index}: {$elementClass}");

            // Extract text
            if ($element instanceof Text || $element instanceof TextRun) {
                $text = $this->extractTextContent($element);

                if (!$this->isPlaceholderText($text)) {
                    $cleaned = trim($text);
                    if (!empty($cleaned)) {
                        $deskripsi .= $cleaned . "\n";
                    }
                }
            }

            // ✅ Extract images - dengan logging detail
            if ($element instanceof Image) {
                Log::error("🖼️ Image element detected!");

                $imgUrl = $this->storeImageElement($element);

                if ($imgUrl) {
                    $alt = "Gambar untuk elemen borang";
                    $deskripsi .= "<img src=\"{$imgUrl}\" alt=\"{$alt}\" style=\"max-width:100%;height:auto;display:block;margin:10px 0;\" />\n";

                    Log::error("✅ Image embedded in description: {$imgUrl}");
                } else {
                    $deskripsi .= "<p><em>[Gambar tidak dapat dimuat]</em></p>\n";
                    Log::warning("❌ Failed to embed image in description");
                }
            }

            // ✅ Cek untuk Drawing/Shape elements (gambar mungkin dalam bentuk ini)
            if ($element instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {
                Log::error("Container element found, checking children...");
                $this->extractImagesFromContainer($element, $deskripsi);
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
     * ✅ Extract images dari container elements (Drawing, Shape, etc)
     */
    private function extractImagesFromContainer($container, &$deskripsi)
    {
        if (!method_exists($container, 'getElements')) {
            return;
        }

        foreach ($container->getElements() as $child) {
            $childClass = get_class($child);
            Log::error("Container child: {$childClass}");

            if ($child instanceof Image) {
                Log::error("🖼️ Image found in container!");

                $imgUrl = $this->storeImageElement($child);

                if ($imgUrl) {
                    $alt = "Gambar untuk elemen borang";
                    $deskripsi .= "<img src=\"{$imgUrl}\" alt=\"{$alt}\" style=\"max-width:100%;height:auto;display:block;margin:10px 0;\" />\n";
                    Log::error("✅ Container image embedded: {$imgUrl}");
                }
            }

            // Recursive untuk nested containers
            if ($child instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {
                $this->extractImagesFromContainer($child, $deskripsi);
            }
        }
    }

    /**
     * ✅ NEW: Extract all images directly from DOCX file (ZIP extraction)
     */
    private function extractImagesFromDocxZip(): array
    {
        $images = [];

        try {
            $zip = new \ZipArchive();

            if ($zip->open($this->filePath) === true) {
                Log::error("DOCX opened as ZIP, checking for media...");

                // List all files in word/media/
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);

                    // Check if it's a media file
                    if (preg_match('#^word/media/image\d+\.(png|jpg|jpeg|gif|bmp|webp)$#i', $filename, $matches)) {
                        Log::error("Found media file: {$filename}");

                        $ext = strtolower($matches[1]);
                        $content = $zip->getFromIndex($i);

                        if ($content) {
                            $images[] = [
                                'filename' => basename($filename),
                                'ext' => $ext,
                                'content' => $content
                            ];
                        }
                    }
                }

                $zip->close();

                Log::error("Extracted " . count($images) . " images from DOCX");
            } else {
                Log::warning("Failed to open DOCX as ZIP");
            }
        } catch (\Exception $e) {
            Log::error("Error extracting images from ZIP: " . $e->getMessage());
        }

        return $images;
    }

    /**
     * ✅ Save extracted images from ZIP
     */
    private function saveExtractedImages(array $extractedImages): array
    {
        $savedUrls = [];

        foreach ($extractedImages as $index => $imageData) {
            try {
                $dir = "permohonan-akreditasi/{$this->pengajuanId}/kualitatif/images";
                $timestamp = now()->format('YmdHis');
                $random = substr(md5(uniqid()), 0, 8);
                $name = "img_{$this->importId}_{$timestamp}_{$random}_{$index}.{$imageData['ext']}";
                $storedPath = "{$dir}/{$name}";

                Storage::disk('public')->put($storedPath, $imageData['content']);

                if (Storage::disk('public')->exists($storedPath)) {
                    $url = Storage::disk('public')->url($storedPath);
                    $savedUrls[] = $url;
                    Log::error("Saved image from ZIP: {$url}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to save extracted image: " . $e->getMessage());
            }
        }

        return $savedUrls;
    }

    private function storeImageElement(\PhpOffice\PhpWord\Element\Image $image): ?string
    {
        try {
            Log::error("Attempting to extract image", [
                'has_getImageStringData' => method_exists($image, 'getImageStringData'),
                'has_getSource' => method_exists($image, 'getSource'),
                'has_getMediaIndex' => method_exists($image, 'getMediaIndex'),
            ]);

            $binary = null;
            $ext = 'png';
            $mimeType = null;

            // METHOD 1: getImageStringData
            if (method_exists($image, 'getImageStringData')) {
                Log::error("Trying getImageStringData...");
                try {
                    $binary = $image->getImageStringData(true);
                    Log::error("getImageStringData result: " . (is_string($binary) ? strlen($binary) . " bytes" : "not string"));
                } catch (\Exception $e) {
                    Log::warning("getImageStringData failed: " . $e->getMessage());
                }

                if ($binary) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($binary);
                    $ext = $this->getExtensionFromMime($mimeType);
                    Log::error("Image from getImageStringData: {$mimeType}, {$ext}");
                }
            }

            // METHOD 2: getSource
            if (!$binary && method_exists($image, 'getSource')) {
                Log::error("Trying getSource...");
                $src = $image->getSource();
                Log::error("getSource result: {$src}");

                if ($src && file_exists($src)) {
                    $binary = file_get_contents($src);
                    $pathInfo = pathinfo($src);

                    if (!empty($pathInfo['extension'])) {
                        $ext = strtolower($pathInfo['extension']);
                    }

                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($src);
                    Log::error("Image from getSource: {$src}, {$mimeType}, " . strlen($binary) . " bytes");
                } else {
                    Log::warning("Source file not found or not accessible: {$src}");
                }
            }

            // METHOD 3: Try reflection to get private properties
            if (!$binary) {
                Log::error("Trying reflection to access image data...");
                try {
                    $reflection = new \ReflectionClass($image);

                    // Try to get source property
                    if ($reflection->hasProperty('source')) {
                        $sourceProp = $reflection->getProperty('source');
                        $sourceProp->setAccessible(true);
                        $source = $sourceProp->getValue($image);
                        Log::error("Reflection source: {$source}");

                        if ($source && file_exists($source)) {
                            $binary = file_get_contents($source);
                            Log::error("Got binary from reflection: " . strlen($binary) . " bytes");
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Reflection failed: " . $e->getMessage());
                }
            }

            if (!$binary) {
                Log::error("Could not extract image binary from element - all methods failed");
                return null;
            }

            if (!$this->isValidImage($binary)) {
                Log::warning("Invalid image data");
                return null;
            }

            // Generate filename
            $dir = "permohonan-akreditasi/{$this->pengajuanId}/kualitatif/images";
            $timestamp = now()->format('YmdHis');
            $random = substr(md5(uniqid()), 0, 8);
            $name = "img_{$this->importId}_{$timestamp}_{$random}.{$ext}";
            $storedPath = "{$dir}/{$name}";

            Storage::disk('public')->put($storedPath, $binary);

            if (!Storage::disk('public')->exists($storedPath)) {
                Log::error("Failed to save image to storage: {$storedPath}");
                return null;
            }

            $url = Storage::disk('public')->url($storedPath);
            Log::error("✅ Image saved successfully: {$url}");

            return $url;
        } catch (\Throwable $e) {
            Log::error("Failed storing image: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return null;
        }
    }

    /**
     * ✅ FIXED: Precise placeholder detection
     */
    private function isPlaceholderText($text)
    {
        $text = trim($text);
        if (empty($text)) return true;

        // ✅ skip template instruction persis ini
        if (stripos($text, 'mohon jangan dihapus') !== false) {
            return true;
        }

        $textLower = strtolower($text);

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

        // 4. Single word labels
        $singleWords = ['deskripsi', 'tabel', 'keterangan', 'catatan', 'no', 'nama'];
        if (in_array($textLower, $singleWords)) {
            return true;
        }

        // 5. Too short
        if (strlen($text) < 10) {
            return true;
        }

        return false;
    }

    private function stripTemplateInstructions(string $text): string
    {
        // hapus kalimat instruksi umum
        $text = preg_replace('/\(?\s*mohon\s+jangan\s+dihapus\s*\)?/i', '', $text);

        // hapus placeholder [....] pendek
        $text = preg_replace('/\[[^\]]{1,200}\]/', '', $text);

        // rapikan spasi dan baris
        return $this->cleanText($text);
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
        $cleanedDeskripsi = $this->stripTemplateInstructions($deskripsi);
        $cleanedDeskripsi = trim($cleanedDeskripsi);

        BorangData::updateOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'dataset_id' => $descKey
            ],
            [
                'nilai' => $cleanedDeskripsi !== '' ? $cleanedDeskripsi : null,
                'id_borang_import' => $borangImport ? $borangImport->id : null
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
                if ($isFirstRow) $style .= 'background-color:#f2f2f2;font-weight:bold;text-align:center;';

                $html .= '<' . $cellTag . ' style="' . $style . '">';

                $cellParts = [];

                foreach ($cell->getElements() as $element) {
                    // Text content
                    if ($element instanceof Text || $element instanceof TextRun) {
                        $t = trim($this->extractTextContent($element));
                        if ($t !== '') $cellParts[] = htmlspecialchars($t);
                    }

                    // ✅ Images in table cells
                    if ($element instanceof Image) {
                        $imgUrl = $this->storeImageElement($element);

                        if ($imgUrl) {
                            $cellParts[] = '<img src="' . htmlspecialchars($imgUrl) . '" alt="Gambar dalam tabel" style="max-width:100%;height:auto;max-height:200px;" />';
                        } else {
                            $cellParts[] = '<em>[Gambar]</em>';
                        }
                    }

                    // Nested tables
                    if ($element instanceof Table) {
                        $cellParts[] = $this->convertTableToHtml($element);
                    }
                }

                $html .= !empty($cellParts) ? implode('<br>', $cellParts) : '&nbsp;';
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

    /**
     * Extract Kata Pengantar, Ringkasan, Suplemen (simple mode-based)
     * NOTE: diadaptasi dari BorangParserService
     */
    private function extractFrontMatter($phpWord): array
    {
        $kataPengantar = '';
        $ringkasan = '';
        $suplemen = [];

        $mode = null; // kata_pengantar | ringkasan | suplemen | stop

        foreach ($phpWord->getSections() as $wordSection) {
            foreach ($wordSection->getElements() as $element) {

                // Ambil text dari element (table/paragraph)
                $text = trim($this->flattenElementText($element));

                if ($text === '') continue;

                // ===== STOP FRONT MATTER =====
                // Begitu masuk daftar isi atau heading bab utama, stop mengumpulkan ringkasan/kata pengantar
                if ($this->isTocBlock($text)) {
                    $mode = 'stop';
                    continue;
                }

                // Judul bab utama seperti: "D. Diferensiasi Misi", "E. Edukasi ..."
                if (preg_match('/^([DEPILAR])\.\s+.+$/i', $text)) {
                    $mode = 'stop';
                    continue;
                }

                // Sub-bab utama seperti: "D.1. ..." atau "1. D.1. ..."
                if (preg_match('/^(\d+\.\s*)?([DEPILAR])\.(\d+)\.?\s+.+$/i', $text)) {
                    $mode = 'stop';
                    continue;
                }

                if ($mode === 'stop') {
                    continue;
                }

                // ===== HEADER DETECTORS =====
                if (preg_match('/^KATA\s+PENGANTAR$/i', $text)) {
                    $mode = 'kata_pengantar';
                    continue;
                }
                if (preg_match('/\bringkasan\b/i', $text) && strlen($text) <= 80) {
                    $mode = 'ringkasan';
                    continue;
                }
                if (preg_match('/^Suplemen\s+Program\s+Studi/i', $text)) {
                    $mode = 'suplemen';
                    continue;
                }
                // ===== SKIP INSTRUCTION / PLACEHOLDER =====
                // ===== FRONT MATTER CLEANUP (jangan skip total) =====
                if ($mode === 'kata_pengantar' || $mode === 'ringkasan' || $mode === 'suplemen') {
                    $text = $this->cleanFrontMatterText($text);
                    if ($text === '') continue;
                } else {
                    if ($this->isInstructionText($text)) continue;
                }

                // ===== SKIP JUDUL ULANG =====
                // Setelah heading "KATA PENGANTAR" biasanya ada baris "Kata Pengantar"
                if ($mode === 'kata_pengantar' && preg_match('/^Kata\s+Pengantar$/i', $text)) {
                    continue;
                }
                // Setelah heading "RINGKASAN" biasanya ada baris "Ringkasan (Mohon jangan dihapus)"
                if ($mode === 'ringkasan' && preg_match('/^Ringkasan\b/i', $text)) {
                    continue;
                }
                // ===== APPEND =====
                if ($mode === 'kata_pengantar') {
                    $kataPengantar .= $text . "\n";
                } elseif ($mode === 'ringkasan') {
                    $ringkasan .= $text . "\n";
                } elseif ($mode === 'suplemen') {
                    $suplemen[] = $text;
                }
            }
        }

        return [
            'kata_pengantar' => $this->cleanText($kataPengantar),
            'ringkasan'      => $this->cleanText($ringkasan),
            'suplemen'       => array_values(array_filter(array_map('trim', $suplemen))),
        ];
    }

    /**
     * Instruction text detection (diadaptasi dari BorangParserService)
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

    private function isTocBlock(string $text): bool
    {
        $t = strtolower(trim(preg_replace('/\s+/', ' ', $text)));

        // 1) kata kunci utama
        if (preg_match('/\bdaftar isi\b/i', $t)) return true;

        // 2) pola baris TOC yang sering muncul di template (Halaman, Cover, Pengesahan, dst)
        if (preg_match('/\bhalaman\b/i', $t) && preg_match('/\bcover|pengesahan|kata|ringkasan|daftar\b/i', $t)) {
            return true;
        }

        // 3) titik-titik TOC ".... 12"
        if (preg_match('/\.{3,}\s*\d+$/', $t)) return true;

        return false;
    }

    private function saveFrontMatterToBorangData($pengajuan, array $frontMatter, $borangImport = null): void
    {
        // 1) Kata Pengantar
        BorangData::updateOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'dataset_id'   => 'kata_pengantar',
            ],
            [
                'nilai'            => $frontMatter['kata_pengantar'] !== '' ? $frontMatter['kata_pengantar'] : null,
                'id_borang_import' => $borangImport?->id,
                'is_template'      => false,
                // 'id_dataset_borang' => null, // kalau kolom ini ada & nullable biarkan
            ]
        );

        // 2) Ringkasan
        BorangData::updateOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'dataset_id'   => 'ringkasan',
            ],
            [
                'nilai'            => $frontMatter['ringkasan'] !== '' ? $frontMatter['ringkasan'] : null,
                'id_borang_import' => $borangImport?->id,
                'is_template'      => false,
            ]
        );

        // 3) Suplemen (disimpan JSON biar rapi)
        $suplemenArr = $frontMatter['suplemen'] ?? [];
        $suplemenArr = is_array($suplemenArr) ? array_values(array_filter(array_map('trim', $suplemenArr))) : [];

        BorangData::updateOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'dataset_id'   => 'suplemen',
            ],
            [
                'nilai'            => !empty($suplemenArr) ? json_encode($suplemenArr, JSON_UNESCAPED_UNICODE) : null,
                'id_borang_import' => $borangImport?->id,
                'is_template'      => false,
            ]
        );
    }

    private function flattenElementText($element): string
    {
        if ($element === null) return '';

        // Text
        if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
            return (string) $element->getText();
        }

        // PreserveText (kadang dipakai untuk field/spacing)
        if ($element instanceof \PhpOffice\PhpWord\Element\PreserveText) {
            $text = $element->getText();

            // getText() bisa array atau string
            if (is_array($text)) {
                return implode('', array_map('strval', $text));
            }

            return (string) $text;
        }

        // TextRun: gabungkan semua anaknya
        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            $out = '';
            foreach ($element->getElements() as $child) {
                $out .= $this->flattenElementText($child);
                // kasih spasi pemisah biar gak nempel
                $out .= ' ';
            }
            return trim($out);
        }

        // ListItem mirip TextRun
        if ($element instanceof \PhpOffice\PhpWord\Element\ListItem) {
            $out = '';
            foreach ($element->getElements() as $child) {
                $out .= $this->flattenElementText($child) . ' ';
            }
            return trim($out);
        }

        // TextBreak: jadikan newline
        if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
            return "\n";
        }

        // Table: gabungkan semua cell
        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            $out = '';
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $child) {
                        $out .= $this->flattenElementText($child) . ' ';
                    }
                    $out .= "\n";
                }
            }
            return trim($out);
        }

        // Generic fallback: kalau punya getElements(), flatten juga
        if (method_exists($element, 'getElements')) {
            $out = '';
            foreach ($element->getElements() as $child) {
                $out .= $this->flattenElementText($child) . ' ';
            }
            return trim($out);
        }

        return '';
    }

    private function cleanFrontMatterText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text)); // normalize spasi dulu

        // buang frasa instruksi meski spasi acak/pecah
        $noSpace = preg_replace('/\s+/', '', strtolower($text));
        if (strpos($noSpace, 'mohonjangan') !== false) {
            $text = preg_replace('/\(\s*mohon\s*jangan\s*d[ií]h?a?p?u?\s*s[^)]*\)/i', '', $text);
            $text = preg_replace('/mohon\s*jangan\s*d[ií]h?a?p?u?\s*s/i', '', $text);
        }

        // buang "(Maksimal 500 kata)"
        $text = preg_replace('/\(\s*maksimal\s+\d+\s+kata\s*\)/i', '', $text);

        // buang label "Kata Pengantar" / "Ringkasan" kalau menempel di awal kalimat
        $text = preg_replace('/^\s*kata\s+pengantar\s*[:\-]?\s*/i', '', $text);
        $text = preg_replace('/^\s*ringkasan\s*[:\-]?\s*/i', '', $text);

        // rapikan spasi sebelum tanda baca
        $text = preg_replace('/\s+([,.;:!?])/', '$1', $text);
        // rapikan spasi ganda
        $text = preg_replace('/\s{2,}/', ' ', $text);

        return trim($text);
    }

    /**
     * ✅ Helper: Get extension from MIME type
     */
    private function getExtensionFromMime(?string $mimeType): string
    {
        if (!$mimeType) return 'png';

        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/svg+xml' => 'svg',
        ];

        return $mimeMap[$mimeType] ?? 'png';
    }

    /**
     * ✅ Helper: Validate image binary
     */
    private function isValidImage(string $binary): bool
    {
        if (empty($binary) || strlen($binary) < 100) {
            return false;
        }

        // Check magic bytes
        $magicBytes = [
            'png' => "\x89PNG",
            'jpg' => "\xFF\xD8\xFF",
            'gif' => "GIF",
            'bmp' => "BM",
            'webp' => "RIFF",
        ];

        foreach ($magicBytes as $type => $magic) {
            if (strpos($binary, $magic) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ Cleanup temporary directory
     */
    private function deleteTempDir(string $dir): void
    {
        try {
            if (!is_dir($dir)) return;

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $method = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                @$method($fileinfo->getRealPath());
            }

            @rmdir($dir);
        } catch (\Throwable $e) {
            Log::warning("Failed to cleanup temp dir: " . $e->getMessage());
        }
    }
}
