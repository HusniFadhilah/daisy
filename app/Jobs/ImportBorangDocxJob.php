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
    protected array $elemenCacheByKode = [];
    protected array $datasetTableCacheByElemenId = [];

    /**
     * Map: mediaIndex (int) => public URL
     * Diisi dari ZIP extraction, dipakai saat Image element ditemukan.
     */
    protected array $mediaIndexToUrl = [];

    /**
     * Fallback: urutan gambar yang tersisa (belum dipetakan via mediaIndex)
     */
    protected array $extractedImageUrlsQueue = [];

    /**
     * Numbering format map dari numbering.xml:
     * numId (string) => [ ilvl (string) => ['fmt' => string, 'left' => int] ]
     * fmt: 'decimal', 'lowerLetter', 'upperLetter', 'lowerRoman', 'upperRoman', 'bullet', ...
     * left: indentasi dalam twips
     */
    protected array $numFormatMap = [];

    public function __construct($importId, $pengajuanId, $filePath)
    {
        $this->importId     = $importId;
        $this->pengajuanId  = $pengajuanId;
        $this->filePath     = $filePath;
    }

    // =========================================================================
    // HANDLE
    // =========================================================================

    public function handle()
    {
        $borangImport = null;
        $sanitizedDocxPath = null;
        $tempDir = null;

        try {
            if (!file_exists($this->filePath)) {
                throw new \Exception('File not found: ' . $this->filePath);
            }

            $pengajuan    = PengajuanAkreditasi::findOrFail($this->pengajuanId);
            $borangImport = BorangImport::where('id', $this->importId)
                ->where('id_pengajuan', $this->pengajuanId)
                ->firstOrFail();

            $borangImport->markAsProcessing();

            // --- Temp dir for PhpWord ---
            $tempDir = storage_path('app/tmp/phpword_' . uniqid());
            @mkdir($tempDir, 0775, true);
            Settings::setTempDir($tempDir);
            // ✅ false = PhpWord kembalikan teks mentah, kita tangani escaping sendiri
            // (true menyebabkan double-encode: &quot; → &amp;quot;)
            Settings::setOutputEscapingEnabled(false);

            $this->purgeOldImagesForPengajuan();
            // ✅ 1. Extract semua gambar dari ZIP dan simpan ke storage
            $this->extractAndMapImagesFromZip();

            // ✅ 1b. Parse numbering.xml untuk mendapatkan format & indentasi per numId/ilvl
            $this->parseNumberingXml();


            $sanitizedDocxPath = $this->prepareDocxForPhpWord($this->filePath, $tempDir);

            // --- Load DOCX ---
            $phpWord = IOFactory::load($sanitizedDocxPath);

            // ✅ 2. Build map mediaIndex => URL (setelah PhpWord load, sehingga bisa pakai getMediaIndex)
            $this->buildMediaIndexMap($phpWord);

            // --- Front matter ---
            $frontMatter = $this->extractFrontMatter($phpWord);
            $this->saveFrontMatterToBorangData($pengajuan, $frontMatter, $borangImport);

            // --- Elemen loop ---
            $totalParsed = 0;

            foreach ($phpWord->getSections() as $sectionIdx => $section) {
                foreach ($section->getElements() as $element) {
                    if (!($element instanceof Table)) continue;

                    $tableText = $this->extractTableText($element);

                    if (!preg_match('/\b([DEPILLAR])\.(\d+)\.\s+/i', $tableText, $matches)) {
                        continue;
                    }

                    $elemenCode    = $matches[1] . '.' . $matches[2];
                    $deskripsi     = '';
                    $tablesBuffer  = [];

                    $rows = $element->getRows();
                    if (count($rows) >= 2) {
                        $descriptionRow = $rows[1];
                        foreach ($descriptionRow->getCells() as $cell) {
                            $this->parseCell($cell, $deskripsi, $tablesBuffer);
                        }
                    }

                    $saved = $this->saveBorangData($pengajuan, $elemenCode, $deskripsi, $tablesBuffer, $borangImport);
                    if ($saved) $totalParsed++;
                }
            }

            $borangImport->update([
                'status'          => 'completed',
                'parsed_sections' => $totalParsed,
                'completed_at'    => now(),
            ]);

            @unlink($this->filePath);
        } catch (\Throwable $e) {
            Log::error("[Import] Job failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            if ($borangImport) {
                $borangImport->markAsFailed(['error' => $e->getMessage()]);
            }
            throw $e;
        } finally {
            if ($sanitizedDocxPath && $sanitizedDocxPath !== $this->filePath && file_exists($sanitizedDocxPath)) {
                @unlink($sanitizedDocxPath);
            }
            if ($tempDir) {
                $this->deleteTempDir($tempDir);
            }
        }
    }

    // =========================================================================
    // IMAGE EXTRACTION — ZIP
    // =========================================================================

    /**
     * Create a temporary DOCX copy that PhpWord can load safely.
     */
    private function prepareDocxForPhpWord(string $sourcePath, string $tempDir): string
    {
        $safePath = rtrim($tempDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'phpword_safe_' . uniqid() . '.docx';

        if (!@copy($sourcePath, $safePath)) {
            Log::warning('[Import] Unable to create sanitized DOCX copy, loading original file', [
                'source' => $sourcePath,
                'target' => $safePath,
                'last_error' => error_get_last()['message'] ?? null,
            ]);

            return $sourcePath;
        }

        try {
            $zip = new \ZipArchive();
            if ($zip->open($safePath) !== true) {
                return $sourcePath;
            }

            $unsupportedTargetsByOwner = $this->removeUnsupportedDocxMedia($zip);
            $removedRelIdsByOwner = $this->removeUnsupportedDocxRelationships($zip, $unsupportedTargetsByOwner);
            $this->sanitizeDocxXmlParts($zip, $removedRelIdsByOwner);

            $zip->close();
        } catch (\Throwable $e) {
            Log::warning('[Import] DOCX sanitizing failed, loading original file: ' . $e->getMessage());

            return $sourcePath;
        }

        return $safePath;
    }

    private function removeUnsupportedDocxMedia(\ZipArchive $zip): array
    {
        $unsupportedTargetsByOwner = [];

        for ($i = $zip->numFiles - 1; $i >= 0; $i--) {
            $filename = $zip->getNameIndex($i);
            if (!preg_match('#^word/media/(image\d+)\.(emf|wmf|tif|tiff)$#i', $filename)) {
                continue;
            }

            $target = substr($filename, strlen('word/'));
            $unsupportedTargetsByOwner['word/document.xml'][] = $target;
            $zip->deleteName($filename);
        }

        return $unsupportedTargetsByOwner;
    }

    private function removeUnsupportedDocxRelationships(\ZipArchive $zip, array $unsupportedTargetsByOwner): array
    {
        $removedRelIdsByOwner = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $relsPath = $zip->getNameIndex($i);
            if (!preg_match('#^word/_rels/(.+\.xml)\.rels$#', $relsPath, $m)) {
                continue;
            }

            $ownerPath = 'word/' . $m[1];
            $relsXml = $zip->getFromName($relsPath);
            if (!$relsXml) {
                continue;
            }

            $doc = new \DOMDocument();
            $doc->preserveWhiteSpace = false;
            if (!@$doc->loadXML($relsXml)) {
                continue;
            }

            $changed = false;
            $rels = iterator_to_array($doc->getElementsByTagName('Relationship'));
            foreach ($rels as $rel) {
                $target = preg_replace('#^(\.\./)+#', '', ltrim(str_replace('\\', '/', $rel->getAttribute('Target')), '/'));
                $isUnsupported = preg_match('#^media/.+\.(emf|wmf|tif|tiff)$#i', $target)
                    || in_array($target, $unsupportedTargetsByOwner[$ownerPath] ?? [], true);

                if (!$isUnsupported) {
                    continue;
                }

                $removedRelIdsByOwner[$ownerPath][] = $rel->getAttribute('Id');
                $rel->parentNode?->removeChild($rel);
                $changed = true;
            }

            if ($changed) {
                $zip->addFromString($relsPath, $doc->saveXML());
            }
        }

        return $removedRelIdsByOwner;
    }

    private function sanitizeDocxXmlParts(\ZipArchive $zip, array $removedRelIdsByOwner): void
    {
        $parts = [
            'word/document.xml',
            'word/footnotes.xml',
            'word/endnotes.xml',
        ];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (preg_match('#^word/(header|footer)\d+\.xml$#', $filename)) {
                $parts[] = $filename;
            }
        }

        foreach (array_unique($parts) as $part) {
            $xml = $zip->getFromName($part);
            if (!$xml) {
                continue;
            }

            $doc = new \DOMDocument();
            $doc->preserveWhiteSpace = false;
            if (!@$doc->loadXML($xml)) {
                continue;
            }

            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $xpath->registerNamespace('mc', 'http://schemas.openxmlformats.org/markup-compatibility/2006');

            $changed = $this->downgradeCellPageBreaks($xpath);
            $changed = $this->removeNodesForRelationships($xpath, $removedRelIdsByOwner[$part] ?? []) || $changed;

            if ($changed) {
                $zip->addFromString($part, $doc->saveXML());
            }
        }
    }

    private function downgradeCellPageBreaks(\DOMXPath $xpath): bool
    {
        $changed = false;

        foreach ($xpath->query('//w:tc//w:br[@w:type="page"]') ?: [] as $break) {
            $break->removeAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'type');
            $changed = true;
        }

        return $changed;
    }

    private function removeNodesForRelationships(\DOMXPath $xpath, array $relationshipIds): bool
    {
        if (empty($relationshipIds)) {
            return false;
        }

        $changed = false;
        $relationshipIds = array_values(array_unique(array_filter($relationshipIds)));

        foreach ($relationshipIds as $relId) {
            $literal = $this->xpathLiteral($relId);
            $nodes = $xpath->query("//*[@r:embed={$literal} or @r:id={$literal}]") ?: [];

            foreach (iterator_to_array($nodes) as $node) {
                $remove = $this->closestDocxMediaContainer($node);
                if ($remove && $remove->parentNode) {
                    $remove->parentNode->removeChild($remove);
                    $changed = true;
                }
            }
        }

        return $changed;
    }

    private function closestDocxMediaContainer(\DOMNode $node): ?\DOMNode
    {
        for ($current = $node; $current !== null; $current = $current->parentNode) {
            if (!$current instanceof \DOMElement) {
                continue;
            }

            $name = $current->localName;
            $namespace = $current->namespaceURI;

            if (
                ($namespace === 'http://schemas.openxmlformats.org/wordprocessingml/2006/main' && in_array($name, ['drawing', 'pict', 'object'], true))
                || ($namespace === 'http://schemas.openxmlformats.org/markup-compatibility/2006' && $name === 'AlternateContent')
            ) {
                return $current;
            }
        }

        return $node;
    }

    private function xpathLiteral(string $value): string
    {
        if (!str_contains($value, "'")) {
            return "'{$value}'";
        }

        if (!str_contains($value, '"')) {
            return "\"{$value}\"";
        }

        $parts = array_map(fn($part) => "'{$part}'", explode("'", $value));

        return 'concat(' . implode(', "\'", ', $parts) . ')';
    }

    private function extractAndMapImagesFromZip(): void
    {
        $this->extractedImageUrlsQueue = [];
        $raw = [];

        try {
            $zip = new \ZipArchive();
            if ($zip->open($this->filePath) !== true) {
                Log::warning("[Import] Cannot open DOCX as ZIP");
                return;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (!preg_match('#^word/media/(image(\d+))\.(png|jpg|jpeg|gif|bmp|webp)$#i', $filename, $m)) {
                    continue;
                }

                $content = $zip->getFromIndex($i);
                if (!$content || !$this->isValidImage($content)) continue;

                $ext       = strtolower($m[3]);
                $seqNumber = (int) $m[2]; // angka di image2.png => 2

                $dir       = "permohonan-akreditasi/{$this->pengajuanId}/kualitatif/images";
                $ts        = now()->format('YmdHis');
                $rand      = substr(md5(uniqid()), 0, 8);
                $name      = "img_{$this->importId}_{$ts}_{$rand}_{$seqNumber}.{$ext}";
                $url       = $this->storePublicImage($dir, $name, $content);

                if ($url) {
                    $raw[] = ['seq' => $seqNumber, 'url' => $url];
                }
            }

            $zip->close();

            // Urutkan berdasarkan seq number agar mapping konsisten
            usort($raw, fn($a, $b) => $a['seq'] <=> $b['seq']);

            $this->extractedImageUrlsQueue = array_column($raw, 'url', 'seq');
        } catch (\Exception $e) {
            Log::error("[Import] extractAndMapImagesFromZip: " . $e->getMessage());
        }
    }

    /**
     * Setelah PhpWord load, build map: mediaIndex => URL
     * PhpWord meng-assign mediaIndex mulai dari 1, sesuai image1.png, image2.png, dst.
     */
    /**
     * Parse numbering.xml dari ZIP untuk mendapatkan format & indentasi per numId/ilvl.
     * Hasilnya disimpan di $this->numFormatMap[numId][ilvl] = ['fmt'=>..., 'left'=>...]
     *
     * 'fmt' values: decimal, lowerLetter, upperLetter, lowerRoman, upperRoman, bullet, none
     * 'left' values: indentasi dalam twips (1 inch = 1440 twips, 1 level Word ≈ 360 twips)
     */
    private function parseNumberingXml(): void
    {
        try {
            $localPath = Storage::disk('public')->exists(ltrim($this->filePath, '/'))
                ? Storage::disk('public')->path(ltrim($this->filePath, '/'))
                : $this->filePath;

            $zip = new \ZipArchive();
            if ($zip->open($localPath) !== true) {
                // Fallback: coba path langsung
                if ($zip->open($this->filePath) !== true) return;
            }

            $xmlContent = $zip->getFromName('word/numbering.xml');
            $zip->close();

            if (!$xmlContent) return;

            $xml = new \SimpleXMLElement($xmlContent);
            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

            // --- Parse abstractNum ---
            $abstractNums = [];
            foreach ($xml->children($W)->abstractNum ?? [] as $an) {
                $anId  = (string) $an->attributes($W)->abstractNumId;
                $lvls  = [];
                foreach ($an->children($W)->lvl ?? [] as $lvl) {
                    $ilvl  = (string) $lvl->attributes($W)->ilvl;
                    $fmt   = (string) ($lvl->children($W)->numFmt->attributes($W)->val ?? 'bullet');
                    $left  = 0;
                    $pPr   = $lvl->children($W)->pPr;
                    if ($pPr) {
                        $ind = $pPr->children($W)->ind;
                        if ($ind) {
                            $left = (int) ($ind->attributes($W)->left ?? 0);
                        }
                    }
                    $lvls[$ilvl] = ['fmt' => $fmt, 'left' => $left];
                }
                $abstractNums[$anId] = $lvls;
            }

            // --- Parse num → abstractNum mapping ---
            foreach ($xml->children($W)->num ?? [] as $num) {
                $numId  = (string) $num->attributes($W)->numId;
                $absRef = $num->children($W)->abstractNumId;
                if (!$absRef) continue;
                $absId  = (string) $absRef->attributes($W)->val;
                $this->numFormatMap[$numId] = $abstractNums[$absId] ?? [];
            }
        } catch (\Throwable $e) {
            // numbering.xml parse gagal — tidak fatal, renderListBlock akan gunakan fallback
        }
    }

    private function buildMediaIndexMap($phpWord): void
    {
        $this->mediaIndexToUrl = [];

        foreach ($phpWord->getSections() as $section) {
            $this->collectImagesFromElements($section->getElements());
        }
    }

    private function collectImagesFromElements(array $elements): void
    {
        foreach ($elements as $el) {
            if ($el instanceof Image) {
                $this->mapImageElement($el);
            }
            if (method_exists($el, 'getElements')) {
                $this->collectImagesFromElements($el->getElements());
            }
            if ($el instanceof Table) {
                foreach ($el->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        $this->collectImagesFromElements($cell->getElements());
                    }
                }
            }
        }
    }

    private function mapImageElement(Image $image): void
    {
        if (!method_exists($image, 'getMediaIndex')) return;

        $mediaIndex = $image->getMediaIndex(); // 1-based
        if ($mediaIndex === null) return;

        // Cari URL dari ZIP extraction berdasarkan seq number
        if (isset($this->extractedImageUrlsQueue[$mediaIndex])) {
            $this->mediaIndexToUrl[$mediaIndex] = $this->extractedImageUrlsQueue[$mediaIndex];
        }
    }

    // =========================================================================
    // IMAGE ELEMENT — STORE
    // =========================================================================

    /**
     * ✅ FIXED: Ambil URL gambar dari Image element.
     * Priority:
     *   1. mediaIndex => URL (dari ZIP, paling akurat)
     *   2. getImageStringData(false) => raw binary
     *   3. getSource() => path file
     *   4. Reflection => source property
     */
    private function storeImageElement(Image $image): ?string
    {
        // --- Priority 1: mediaIndex map ---
        if (method_exists($image, 'getMediaIndex')) {
            $mediaIndex = $image->getMediaIndex();
            if ($mediaIndex !== null && isset($this->mediaIndexToUrl[$mediaIndex])) {
                return $this->mediaIndexToUrl[$mediaIndex];
            }
        }

        // --- Priority 2: getImageStringData(false) = raw binary ---
        $binary = null;
        $ext    = 'png';

        if (method_exists($image, 'getImageStringData')) {
            try {
                // false = return raw binary (bukan base64!)
                $data = $image->getImageStringData(false);

                if (is_string($data) && strlen($data) > 100) {
                    // Validasi apakah ini benar binary
                    if ($this->isValidImage($data)) {
                        $binary = $data;
                    } else {
                        // Mungkin masih base64 (versi PhpWord lama), coba decode
                        $decoded = base64_decode(preg_replace('/\s+/', '', $data), false);
                        if ($decoded && $this->isValidImage($decoded)) {
                            $binary = $decoded;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("[Import] getImageStringData error: " . $e->getMessage());
            }
        }

        // --- Priority 3: getSource() ---
        if (!$binary && method_exists($image, 'getSource')) {
            $src = $image->getSource();
            if ($src && file_exists($src)) {
                $raw = file_get_contents($src);
                if ($raw && $this->isValidImage($raw)) {
                    $binary = $raw;
                    $ext    = strtolower(pathinfo($src, PATHINFO_EXTENSION)) ?: 'png';
                }
            }
        }

        // --- Priority 4: Reflection ---
        if (!$binary) {
            try {
                $ref = new \ReflectionClass($image);
                foreach (['source', 'imageSource', 'path'] as $propName) {
                    if ($ref->hasProperty($propName)) {
                        $prop = $ref->getProperty($propName);
                        $prop->setAccessible(true);
                        $val = $prop->getValue($image);
                        if ($val && file_exists($val)) {
                            $raw = file_get_contents($val);
                            if ($raw && $this->isValidImage($raw)) {
                                $binary = $raw;
                                $ext    = strtolower(pathinfo($val, PATHINFO_EXTENSION)) ?: 'png';
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("[Import] Reflection failed: " . $e->getMessage());
            }
        }

        if (!$binary) {
            Log::warning("[Import] All image extraction methods failed");
            return null;
        }

        // Detect MIME & ext dari binary
        try {
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($binary);
            $ext      = $this->getExtensionFromMime($mimeType) ?: $ext;
        } catch (\Exception $e) {
            // keep current $ext
        }

        // Simpan ke storage
        $dir  = "permohonan-akreditasi/{$this->pengajuanId}/kualitatif/images";
        $ts   = now()->format('YmdHis');
        $rand = substr(md5(uniqid()), 0, 8);
        $name = "img_{$this->importId}_{$ts}_{$rand}.{$ext}";
        return $this->storePublicImage($dir, $name, $binary);
    }

    // =========================================================================
    // LIST ITEM → HTML
    // =========================================================================

    /**
     * ✅ Konversi ListItem / ListItemRun ke HTML dengan indentasi level.
     *
     * Word menyimpan list dengan level (0-based):
     *   Level 0 → <ol style="list-style-type:decimal">  atau <ul style="list-style-type:disc">
     *   Level 1 → nested ol/ul dengan indent lebih dalam
     *
     * Kita render sebagai <p> dengan margin-left agar tidak perlu nested open/close tag
     * (karena list items masuk satu-persatu tanpa konteks sebelumnya).
     */
    private function listItemToHtml($listItem): string
    {
        // --- Depth / level (0-based) ---
        $depth = 0;
        if (method_exists($listItem, 'getDepth')) {
            $depth = (int) $listItem->getDepth();
        }

        // --- List type → CSS list-style-type ---
        // PhpWord\Style\ListItem types:
        //   TYPE_NUMBER=1, TYPE_NUMBER_NESTED=2 → decimal
        //   TYPE_ALPHANUM=3, TYPE_ALPHANUM_NESTED=4 → lower-alpha
        //   TYPE_ROMAN_LOWER=5, TYPE_ROMAN_UPPER=6 → lower/upper-roman
        //   TYPE_BULLET=7 → disc
        $listStyle     = null;
        $listType      = null;
        $markerTypeCss = 'disc';

        if (method_exists($listItem, 'getStyle')) {
            $listStyle = $listItem->getStyle();
        }
        if ($listStyle && method_exists($listStyle, 'getListType')) {
            $listType = $listStyle->getListType();
        }

        $listTypeMap = [
            1 => 'decimal',
            2 => 'decimal',
            3 => 'lower-alpha',
            4 => 'lower-alpha',
            5 => 'lower-roman',
            6 => 'upper-roman',
            7 => 'disc',
        ];
        if ($listType !== null && isset($listTypeMap[$listType])) {
            $markerTypeCss = $listTypeMap[$listType];
        }

        // Bullet per level: disc → circle → square (cycling)
        if ($markerTypeCss === 'disc') {
            $bulletByLevel = ['disc', 'circle', 'square', 'disc', 'circle', 'square'];
            $markerTypeCss = $bulletByLevel[$depth % 6] ?? 'disc';
        }

        // --- Inner HTML (teks + inline style) ---
        $inner     = '';
        $paraStyle = null;

        if ($listItem instanceof \PhpOffice\PhpWord\Element\ListItem) {
            $textRun = method_exists($listItem, 'getTextObject') ? $listItem->getTextObject() : null;
            if ($textRun) {
                $inner     = $this->textToInlineHtml($textRun->getText(), $textRun->getFontStyle());
                $paraStyle = method_exists($textRun, 'getParagraphStyle') ? $textRun->getParagraphStyle() : null;
            } else {
                $raw   = $this->flattenElementText($listItem);
                $inner = htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            if (!$paraStyle && method_exists($listItem, 'getParagraphStyle')) {
                $paraStyle = $listItem->getParagraphStyle();
            }
        } elseif ($listItem instanceof \PhpOffice\PhpWord\Element\ListItemRun) {
            $parts = [];
            foreach ($listItem->getElements() as $child) {
                if ($child instanceof Text) {
                    $parts[] = $this->textToInlineHtml($child->getText(), $child->getFontStyle());
                } elseif ($child instanceof Image) {
                    $parts[] = $this->imageElementToHtml($child);
                } elseif ($child instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                    $parts[] = '<br>';
                }
            }
            $inner     = implode('', $parts);
            $paraStyle = method_exists($listItem, 'getParagraphStyle') ? $listItem->getParagraphStyle() : null;
        }

        $inner = trim($inner);
        $innerText = strip_tags($inner);
        if ($innerText === '' || $this->isListItemPlaceholder($innerText)) return '';

        // --- CSS: indentasi + line-spacing + spacing ---
        // Default: 20px per level (Word default ~720 twips/level)
        $indentLeft  = ($depth + 1) * 20;
        $paddingLeft = 20;
        $marginTop   = 1;
        $marginBot   = 1;
        $lineHeight  = '';

        if ($paraStyle instanceof \PhpOffice\PhpWord\Style\Paragraph) {
            // Indentasi eksplisit dari Word (twips → px, 1px ≈ 15 twips)
            if (method_exists($paraStyle, 'getIndentation')) {
                $indObj = $paraStyle->getIndentation();
                if ($indObj instanceof \PhpOffice\PhpWord\Style\Indentation) {
                    $leftTwips = $indObj->getLeft();
                    $hangTwips = $indObj->getHanging();
                    if ($leftTwips > 0) $indentLeft  = round($leftTwips / 15);
                    if ($hangTwips > 0) $paddingLeft = round($hangTwips / 15);
                }
            }

            // Line spacing
            $lineHeight = $this->resolveLineSpacingCss($paraStyle);

            // Space before/after (twips → px: 1px = 20 twips)
            $sb = $paraStyle->getSpaceBefore();
            $sa = $paraStyle->getSpaceAfter();
            if ($sb > 0) $marginTop = round($sb / 20);
            if ($sa > 0) $marginBot = round($sa / 20);
        }

        $cssParts = [
            'display:list-item',
            "list-style-type:{$markerTypeCss}",
            "margin-left:{$indentLeft}px",
            "padding-left:{$paddingLeft}px",
            "margin-top:{$marginTop}px",
            "margin-bottom:{$marginBot}px",
        ];
        if ($lineHeight !== '') $cssParts[] = "line-height:{$lineHeight}";

        $style = implode(';', $cssParts);

        return "<p style='{$style}'>{$inner}</p>\n";
    }

        // =========================================================================
    // LIST BLOCK RENDERER
    // =========================================================================

    /**
     * Ambil numId dari ListItem/ListItemRun via getter atau reflection.
     * numId dibutuhkan untuk lookup numFormatMap (format & indentasi).
     */
    private function getListItemNumId($listItem): ?int
    {
        $ls = method_exists($listItem, 'getStyle') ? $listItem->getStyle() : null;
        if (!$ls) return null;

        // Coba via public getter
        foreach (['getNumId', 'getNumStyle'] as $fn) {
            if (method_exists($ls, $fn)) {
                $v = $ls->$fn();
                if ($v !== null && is_numeric($v)) return (int)$v;
            }
        }

        // Coba via reflection (property mungkin tidak punya public getter)
        try {
            $ref = new \ReflectionClass($ls);
            foreach (['numId', 'numStyle', 'listId'] as $propName) {
                if ($ref->hasProperty($propName)) {
                    $prop = $ref->getProperty($propName);
                    $prop->setAccessible(true);
                    $val = $prop->getValue($ls);
                    if ($val !== null && is_numeric($val)) return (int)$val;
                }
            }
        } catch (\Throwable $e) { /* ignore */
        }

        return null;
    }

    /**
     * ✅ Render array of ListItem/ListItemRun sebagai HTML <ol>/<ul> yang benar-benar nested.
     *
     * Algoritma:
     *   - Iterasi items, tracking depth stack
     *   - Jika depth naik → buka <ol>/<ul> baru
     *   - Jika depth turun → tutup list sampai depth yang sesuai
     *   - Setiap item → <li>...</li>
     */
    private function renderListBlock(array $items): string
    {
        if (empty($items)) return '';

        // =====================================================================
        // PASS 1: Kumpulkan data tiap item (ilvl, numId, leftTwips, fmt, inner)
        // =====================================================================
        $fmtToCss = [
            'decimal'         => ['ol', 'decimal'],
            'lowerLetter'     => ['ol', 'lower-alpha'],
            'upperLetter'     => ['ol', 'upper-alpha'],
            'lowerRoman'      => ['ol', 'lower-roman'],
            'upperRoman'      => ['ol', 'upper-roman'],
            'decimalZero'     => ['ol', 'decimal-leading-zero'],
            'bullet'          => ['ul', 'disc'],
            'none'            => ['ul', 'none'],
        ];

        $rawItems = [];

        foreach ($items as $listItem) {
            // ✅ Handle 'text_node' yang disisipkan di antara list items dari parseCell
            if (is_array($listItem) && ($listItem['type'] ?? '') === 'text') {
                // Simpan sebagai text_node untuk dirender setelah li sebelumnya
                $rawItems[] = [
                    'type'       => 'text_node',
                    'html'       => $listItem['html'],
                    'listItem'   => null,
                    'ilvl'       => 0,
                    'numId'      => null,
                    'leftTwips'  => null,
                    'fmtWord'    => null,
                    'paraStyle'  => null,
                    'inner'      => $listItem['html'],
                ];
                continue;
            }

            // Ambil paragraphStyle
            $paraStyle = null;
            if ($listItem instanceof \PhpOffice\PhpWord\Element\ListItem) {
                $tr = method_exists($listItem, 'getTextObject') ? $listItem->getTextObject() : null;
                if ($tr) {
                    $paraStyle = method_exists($tr, 'getParagraphStyle') ? $tr->getParagraphStyle() : null;
                }
                if (!$paraStyle && method_exists($listItem, 'getParagraphStyle')) {
                    $paraStyle = $listItem->getParagraphStyle();
                }
            } elseif ($listItem instanceof \PhpOffice\PhpWord\Element\ListItemRun) {
                $paraStyle = method_exists($listItem, 'getParagraphStyle') ? $listItem->getParagraphStyle() : null;
            }

            // Ambil ilvl
            $ilvl = 0;
            foreach (['getDepth', 'getLevel', 'getNumLevel', 'getIlvl'] as $fn) {
                if (method_exists($listItem, $fn)) {
                    $v = $listItem->$fn();
                    if ($v !== null) {
                        $ilvl = (int)$v;
                        break;
                    }
                }
            }

            // Ambil numId
            $numId = $this->getListItemNumId($listItem);

            // Lookup leftTwips dan fmt dari numbering.xml map
            $leftTwips = null;
            $fmtWord   = null;
            if ($numId !== null && !empty($this->numFormatMap[(string)$numId])) {
                $lvlData   = $this->numFormatMap[(string)$numId][(string)$ilvl] ?? null;
                $leftTwips = $lvlData ? (int)$lvlData['left'] : null;
                $fmtWord   = $lvlData ? (string)$lvlData['fmt'] : null;
            }

            // Ambil inner HTML
            $inner = '';
            if ($listItem instanceof \PhpOffice\PhpWord\Element\ListItem) {
                $textRun = method_exists($listItem, 'getTextObject') ? $listItem->getTextObject() : null;
                if ($textRun) {
                    $inner = $this->textToInlineHtml($textRun->getText(), $textRun->getFontStyle());
                } else {
                    $raw   = $this->flattenElementText($listItem);
                    $inner = htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            } elseif ($listItem instanceof \PhpOffice\PhpWord\Element\ListItemRun) {
                $parts = [];
                foreach ($listItem->getElements() as $child) {
                    if ($child instanceof Text) {
                        $parts[] = $this->textToInlineHtml($child->getText(), $child->getFontStyle());
                    } elseif ($child instanceof Image) {
                        $parts[] = $this->imageElementToHtml($child);
                    } elseif ($child instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                        $parts[] = '<br>';
                    }
                }
                $inner = implode('', $parts);
            }

            $rawItems[] = [
                'listItem'   => $listItem,
                'ilvl'       => $ilvl,
                'numId'      => $numId,
                'leftTwips'  => $leftTwips, // null jika numFormatMap kosong
                'fmtWord'    => $fmtWord,   // null jika tidak ditemukan
                'paraStyle'  => $paraStyle,
                'inner'      => trim($inner),
            ];
        }

        if (empty($rawItems)) return '';

        // =====================================================================
        // PASS 2: Hitung depth dari leftTwips (relatif terhadap min di blok ini)
        //
        // Word default: setiap level ≈ 360 twips.
        // depth = round((left - min_left) / 360)
        // Jika numFormatMap tidak tersedia, fallback ke ilvl.
        // =====================================================================
        // Hitung minLeft hanya dari list items (bukan text_node)
        $lefts    = array_filter(
            array_column($rawItems, 'leftTwips'),
            fn($v) => $v !== null
        );
        $minLeft  = !empty($lefts) ? min($lefts) : null;
        $hasMap   = $minLeft !== null;
        $step     = 360; // twips per level

        foreach ($rawItems as &$rd) {
            // text_node tidak perlu depth/tag/marker
            if (($rd['type'] ?? '') === 'text_node') continue;

            if ($hasMap && $rd['leftTwips'] !== null) {
                $rd['depth'] = max(0, (int) round(($rd['leftTwips'] - $minLeft) / $step));
            } else {
                $rd['depth'] = $rd['ilvl'];
            }

            // Tentukan tag + markerCss dari fmtWord (prioritas) atau depth fallback
            if ($rd['fmtWord'] && isset($fmtToCss[$rd['fmtWord']])) {
                [$rd['tag'], $rd['markerCss']] = $fmtToCss[$rd['fmtWord']];
            } else {
                $d = $rd['depth'];
                if ($rd['fmtWord'] === 'bullet' || $rd['fmtWord'] === 'none') {
                    $rd['tag']       = 'ul';
                    $bMap            = ['disc', 'circle', 'square', 'disc', 'circle', 'square'];
                    $rd['markerCss'] = $bMap[$d % 6];
                } else {
                    $rd['tag']       = 'ol';
                    $nMap            = [
                        'decimal',
                        'lower-alpha',
                        'lower-roman',
                        'upper-alpha',
                        'upper-roman',
                        'decimal',
                        'lower-alpha',
                        'lower-roman'
                    ];
                    $rd['markerCss'] = $nMap[$d % 8];
                }
            }
        }
        unset($rd);

        // =====================================================================
        // PASS 3: Render HTML dengan depth stack
        // =====================================================================
        $html       = '';
        $depthStack = []; // ['tag', 'depth', 'marker']
        $counters   = []; // "numId:depth" → int

        foreach ($rawItems as $rd) {
            // ✅ text_node: teks di antara list items (body text bawah list item di Word)
            // Render sebagai <p> langsung ke output (di luar <li> tapi dalam konteks list)
            if (($rd['type'] ?? '') === 'text_node') {
                $html .= $rd['html'];
                continue;
            }

            $depth     = $rd['depth'];
            $tag       = $rd['tag'];
            $markerCss = $rd['markerCss'];
            $numId     = $rd['numId'] ?? 'default';
            $paraStyle = $rd['paraStyle'];
            $inner     = $rd['inner'];

            // Skip kosong / placeholder
            $innerText = strip_tags($inner);
            if ($innerText === '' || $this->isListItemPlaceholder($innerText)) {
                continue;
            }

            $counterKey = $numId . ':' . $depth;

            // Sinkronisasi depth stack
            $currentDepth = count($depthStack) - 1;
            $currentTag   = !empty($depthStack) ? end($depthStack)['tag'] : null;

            if ($depth > $currentDepth) {
                for ($i = $currentDepth + 1; $i <= $depth; $i++) {
                    $ck        = $numId . ':' . $i;
                    $startAt   = ($counters[$ck] ?? 0) + 1;
                    $startAttr = ($tag === 'ol' && $startAt > 1) ? " start='{$startAt}'" : '';
                    $lStyle    = "list-style-type:{$markerCss};margin:4px 0;padding-left:24px;";
                    $html     .= "<{$tag}{$startAttr} style='{$lStyle}'>";
                    $depthStack[] = ['tag' => $tag, 'depth' => $i, 'marker' => $markerCss];
                }
            } elseif ($depth < $currentDepth) {
                while (!empty($depthStack) && end($depthStack)['depth'] > $depth) {
                    $top   = array_pop($depthStack);
                    $html .= "</{$top['tag']}>";
                }
                // Jika tag berubah di level ini setelah naik
                if (!empty($depthStack) && end($depthStack)['tag'] !== $tag) {
                    $top   = array_pop($depthStack);
                    $html .= "</{$top['tag']}>";
                    $ck        = $numId . ':' . $depth;
                    $startAt   = ($counters[$ck] ?? 0) + 1;
                    $startAttr = ($tag === 'ol' && $startAt > 1) ? " start='{$startAt}'" : '';
                    $lStyle    = "list-style-type:{$markerCss};margin:4px 0;padding-left:24px;";
                    $html     .= "<{$tag}{$startAttr} style='{$lStyle}'>";
                    $depthStack[] = ['tag' => $tag, 'depth' => $depth, 'marker' => $markerCss];
                }
            } elseif ($currentTag !== null && $currentTag !== $tag) {
                // Tag berubah di depth sama
                array_pop($depthStack);
                $html .= "</{$currentTag}>";
                $ck        = $numId . ':' . $depth;
                $startAt   = ($counters[$ck] ?? 0) + 1;
                $startAttr = ($tag === 'ol' && $startAt > 1) ? " start='{$startAt}'" : '';
                $lStyle    = "list-style-type:{$markerCss};margin:4px 0;padding-left:24px;";
                $html     .= "<{$tag}{$startAttr} style='{$lStyle}'>";
                $depthStack[] = ['tag' => $tag, 'depth' => $depth, 'marker' => $markerCss];
            } elseif (empty($depthStack)) {
                // List pertama
                $lStyle    = "list-style-type:{$markerCss};margin:4px 0;padding-left:24px;";
                $html     .= "<{$tag} style='{$lStyle}'>";
                $depthStack[] = ['tag' => $tag, 'depth' => $depth, 'marker' => $markerCss];
            }

            // Increment counter
            $counters[$counterKey] = ($counters[$counterKey] ?? 0) + 1;

            // Line spacing
            $liStyle = '';
            if ($paraStyle instanceof \PhpOffice\PhpWord\Style\Paragraph) {
                $lhCss = $this->resolveLineSpacingCss($paraStyle);
                $sb    = $paraStyle->getSpaceBefore();
                $sa    = $paraStyle->getSpaceAfter();
                $parts = [];
                if ($lhCss) $parts[] = "line-height:{$lhCss}";
                if ($sb > 0) $parts[] = "margin-top:"    . round($sb / 20) . "px";
                if ($sa > 0) $parts[] = "margin-bottom:" . round($sa / 20) . "px";
                if (!empty($parts)) $liStyle = implode(';', $parts);
            }

            $styleAttr = $liStyle ? " style='{$liStyle}'" : '';
            $html     .= "<li{$styleAttr}>{$inner}</li>\n";
        }

        // Tutup semua list yang masih terbuka
        while (!empty($depthStack)) {
            $top   = array_pop($depthStack);
            $html .= "</{$top['tag']}>";
        }

        return $html . "\n";
    }

    // =========================================================================
    // PARSE CELL (text + style + image + table)
    // =========================================================================

    private function parseCell($cell, string &$deskripsi, array &$tablesBuffer): void
    {
        $listItems = []; // buffer untuk list items berurutan

        // Semua elemen dalam cell diproses dalam dua mode:
        //   - listBuffer: kumpulkan ListItem/TextRun-di-antara-list sebagai satu blok list
        //   - Flush listBuffer hanya saat Table, Image standalone, atau akhir loop
        //
        // Mengapa TextRun di-buffer juga?
        //   Di Word, teks di antara list items (style ListParagraph, tanpa numPr)
        //   adalah body text di bawah list item, bukan paragraf terpisah.
        //   Jika di-flush, setiap blok list punya minLeft sendiri → depth salah.

        $allElements     = $cell->getElements();
        $elementCount    = is_array($allElements) ? count($allElements) : iterator_count($allElements);
        $elementArr      = is_array($allElements) ? $allElements : iterator_to_array($allElements);
        $listBuffer      = []; // mixed: ListItem | ['type'=>'text','html'=>string]
        $listHasItems    = false; // ada minimal 1 ListItem di buffer?

        $flushListBuffer = function () use (&$listBuffer, &$listHasItems, &$deskripsi) {
            if (empty($listBuffer)) return;
            if ($listHasItems) {
                $deskripsi .= $this->renderListBlock($listBuffer);
            } else {
                // Hanya text nodes tanpa list item → render sebagai HTML biasa
                foreach ($listBuffer as $node) {
                    if (is_array($node) && ($node['type'] ?? '') === 'text') {
                        $deskripsi .= $node['html'];
                    }
                }
            }
            $listBuffer   = [];
            $listHasItems = false;
        };

        foreach ($elementArr as $element) {
            // ---- ListItem / ListItemRun (harus SEBELUM cek TextRun!) ----
            if (
                $element instanceof \PhpOffice\PhpWord\Element\ListItem
                || $element instanceof \PhpOffice\PhpWord\Element\ListItemRun
            ) {
                $listBuffer[]  = $element;
                $listHasItems  = true;
                continue;
            }

            // ---- Text / TextRun ----
            if ($element instanceof Text || $element instanceof TextRun) {
                $html     = $this->elementToHtml($element);
                $hasImage = str_contains($html, '<img');
                $textOnly = strip_tags($html);

                if ($hasImage) {
                    // Gambar dalam TextRun → flush list, render gambar
                    $flushListBuffer();
                    $deskripsi .= $html . "\n";
                } elseif (trim($textOnly) === '') {
                    // Empty paragraph
                    if ($listHasItems) {
                        // Di dalam konteks list → buffer sebagai text node (antar-list spacing)
                        $listBuffer[] = ['type' => 'text', 'html' => '<p>&nbsp;</p>' . "\n"];
                    } else {
                        $flushListBuffer();
                        $deskripsi .= '<p>&nbsp;</p>' . "\n";
                    }
                } elseif (!$this->isPlaceholderText($textOnly)) {
                    if ($listHasItems) {
                        // Teks di antara list items (misal body text bawah list item di Word)
                        // Buffer sebagai text node agar minLeft dihitung konsisten
                        $listBuffer[] = ['type' => 'text', 'html' => $html . "\n"];
                    } else {
                        $flushListBuffer();
                        $deskripsi .= $html . "\n";
                    }
                }
                continue;
            }

            // ---- TextBreak (Shift+Enter) ----
            if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                if ($listHasItems) {
                    $listBuffer[] = ['type' => 'text', 'html' => '<br>' . "\n"];
                } else {
                    $flushListBuffer();
                    $deskripsi .= '<br>' . "\n";
                }
                continue;
            }

            // ---- Table, Image, Container → flush list buffer dulu ----
            $flushListBuffer();

            if ($element instanceof Image) {
                $imgParaStyle = method_exists($element, 'getParagraphStyle') ? $element->getParagraphStyle() : null;
                $deskripsi .= $this->imageElementToHtml($element, $imgParaStyle);
                continue;
            }

            if ($element instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {
                $this->extractFromContainer($element, $deskripsi, $tablesBuffer);
                continue;
            }

            if ($element instanceof Table) {
                $tableHtml = $this->convertTableToHtml($element);
                if ($this->hasRealTableData($tableHtml)) {
                    $tablesBuffer[] = ['html' => $tableHtml, 'is_template' => false];
                }
                continue;
            }
        }

        // Flush sisa di akhir
        $flushListBuffer();
    }

    private function extractFromContainer($container, string &$deskripsi, array &$tablesBuffer): void
    {
        if (!method_exists($container, 'getElements')) return;

        $listItems = [];

        foreach ($container->getElements() as $child) {
            // ✅ ListItem/ListItemRun PERTAMA (ListItemRun extends TextRun!)
            if (
                $child instanceof \PhpOffice\PhpWord\Element\ListItem
                || $child instanceof \PhpOffice\PhpWord\Element\ListItemRun
            ) {
                $listItems[] = $child;
                continue;
            }

            // Flush list buffer
            if (!empty($listItems)) {
                $deskripsi .= $this->renderListBlock($listItems);
                $listItems = [];
            }

            if ($child instanceof Image) {
                $imgParaStyle = method_exists($child, 'getParagraphStyle') ? $child->getParagraphStyle() : null;
                $deskripsi .= $this->imageElementToHtml($child, $imgParaStyle);
            } elseif ($child instanceof Text || $child instanceof TextRun) {
                $html = $this->elementToHtml($child);
                $hasImage = str_contains($html, '<img');
                $textOnly = strip_tags($html);
                if ($hasImage) {
                    $deskripsi .= $html . "\n";
                } elseif (trim($textOnly) === '') {
                    $deskripsi .= '<p>&nbsp;</p>' . "\n";
                } elseif (!$this->isPlaceholderText($textOnly)) {
                    $deskripsi .= $html . "\n";
                }
            } elseif ($child instanceof Table) {
                $tableHtml = $this->convertTableToHtml($child);
                if ($this->hasRealTableData($tableHtml)) {
                    $tablesBuffer[] = ['html' => $tableHtml, 'is_template' => false];
                }
            } elseif ($child instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {
                $this->extractFromContainer($child, $deskripsi, $tablesBuffer);
            }
        }

        if (!empty($listItems)) {
            $deskripsi .= $this->renderListBlock($listItems);
        }
    }

    /**
     * ✅ Konversi line spacing PhpWord → CSS line-height value string.
     *
     * PhpWord LineSpacing:
     *   - rule = 'auto'  → spacing = multiplier * 240 (e.g. 240=single, 360=1.5x, 480=double)
     *   - rule = 'exact' → spacing dalam twips (exact px, e.g. 240 twips = 12pt)
     *   - rule = 'atLeast'→ minimum spacing dalam twips
     *
     * Returns CSS string siap pakai, e.g. '1.5' atau '24px', atau '' jika default/unknown.
     */
    private function resolveLineSpacingCss($paragraphStyle): string
    {
        if (!($paragraphStyle instanceof \PhpOffice\PhpWord\Style\Paragraph)) return '';

        $spacing = null;
        $rule    = null;

        // getLineHeight() is direct multiplier in some PhpWord versions
        if (method_exists($paragraphStyle, 'getLineHeight')) {
            $lh = $paragraphStyle->getLineHeight();
            if ($lh && $lh > 0 && $lh != 1.0) {
                return round($lh, 2) . '';
            }
        }

        // getSpacing() returns twips value, getSpacingLineRule() returns the rule
        if (method_exists($paragraphStyle, 'getSpacing')) {
            $spacing = $paragraphStyle->getSpacing();
        }
        if (method_exists($paragraphStyle, 'getSpacingLineRule')) {
            $rule = $paragraphStyle->getSpacingLineRule();
        }
        // Fallback names used in some PhpWord versions
        if ($spacing === null && method_exists($paragraphStyle, 'getLine')) {
            $spacing = $paragraphStyle->getLine();
        }

        if ($spacing === null || $spacing <= 0) return '';

        // 'auto' rule: 240 = single, 360 = 1.5x, 480 = double
        if ($rule === null || $rule === 'auto' || $rule === \PhpOffice\PhpWord\SimpleType\LineSpacingRule::AUTO) {
            $multiplier = round($spacing / 240, 2);
            if ($multiplier === 1.0 || $multiplier === 1.00) return ''; // default, skip
            return (string) $multiplier;
        }

        // 'exact' or 'atLeast': twips → px (1px = 20 twips) or pt (1pt = 20 twips)
        if (
            $rule === 'exact' || $rule === \PhpOffice\PhpWord\SimpleType\LineSpacingRule::EXACT
            || $rule === 'atLeast' || $rule === \PhpOffice\PhpWord\SimpleType\LineSpacingRule::AT_LEAST
        ) {
            $px = round($spacing / 20);
            if ($px > 0 && $px !== 14 && $px !== 15) return $px . 'px'; // skip ~12pt default
        }

        return '';
    }

    /**
     * ✅ Baca alignment dari ParagraphStyle PhpWord → CSS text-align string.
     * Mengembalikan string siap pakai misal 'text-align:justify;' atau '' jika tidak ada.
     */
    private function resolveAlignmentFromParagraphStyle($paragraphStyle): string
    {
        if (!($paragraphStyle instanceof \PhpOffice\PhpWord\Style\Paragraph)) {
            return 'text-align:left;';
        }

        $alignMap = [
            \PhpOffice\PhpWord\SimpleType\Jc::CENTER     => 'center',
            \PhpOffice\PhpWord\SimpleType\Jc::END        => 'right',
            \PhpOffice\PhpWord\SimpleType\Jc::BOTH       => 'justify',
            \PhpOffice\PhpWord\SimpleType\Jc::DISTRIBUTE => 'justify',
            \PhpOffice\PhpWord\SimpleType\Jc::START      => 'left',
        ];

        $align = $paragraphStyle->getAlignment();
        return isset($alignMap[$align])
            ? 'text-align:' . $alignMap[$align] . ';'
            : 'text-align:left;';
    }

    private function imageElementToHtml(Image $image, $paragraphStyle = null): string
    {
        $url = $this->storeImageElement($image);
        if (!$url) {
            return '<p><em>[Gambar tidak dapat dimuat]</em></p>' . "\n";
        }

        // ✅ Baca ukuran gambar dari style PhpWord
        $imgStyle  = 'display:block;';

        // ✅ Default alignment dari paragraphStyle (justify, center, dll)
        // Prioritas: paragraph style > image position style > left
        $wrapAlign = $this->resolveAlignmentFromParagraphStyle($paragraphStyle);

        try {
            $style = $image->getStyle();

            if ($style) {
                // Width & Height (dalam EMU atau pt tergantung versi, biasanya px/pt)
                $w = method_exists($style, 'getWidth')  ? $style->getWidth()  : null;
                $h = method_exists($style, 'getHeight') ? $style->getHeight() : null;

                if ($w && $w > 0) {
                    // PhpWord menyimpan dalam pt/px (bukan EMU)
                    // Konversi ke px jika masuk akal (range 10-2000)
                    $wPx = $w >= 10 && $w <= 2000 ? $w : null;
                    if ($wPx) $imgStyle .= 'width:' . $wPx . 'px;';
                }
                if ($h && $h > 0) {
                    $hPx = $h >= 10 && $h <= 2000 ? $h : null;
                    if ($hPx) $imgStyle .= 'height:' . $hPx . 'px;';
                }

                // Jika tidak ada ukuran eksplisit, pakai max-width
                if (!$w) {
                    $imgStyle .= 'max-width:100%;height:auto;';
                }

                // Alignment / wrapping
                $wrap = null;
                if (method_exists($style, 'getWrap'))          $wrap = $style->getWrap();
                if (method_exists($style, 'getWrappingStyle')) $wrap = $style->getWrappingStyle();

                $pos = null;
                if (method_exists($style, 'getPosH'))      $pos = $style->getPosH();
                if (method_exists($style, 'getAlignment')) $pos = $style->getAlignment();

                // Map PhpWord image position → CSS text-align
                // Hanya override jika gambar punya posisi eksplisit sendiri
                $alignMap = [
                    'center' => 'center',
                    'right' => 'right',
                    'left'   => 'left',
                    'inside' => 'left',
                    'outside' => 'right',
                ];
                if ($pos && isset($alignMap[strtolower((string)$pos)])) {
                    $wrapAlign = 'text-align:' . $alignMap[strtolower((string)$pos)] . ';';
                }
                // Jika tidak ada posisi eksplisit, pertahankan wrapAlign dari paragraphStyle

                // Inline wrapping → float
                if ($wrap) {
                    $wrapLower = strtolower((string)$wrap);
                    if (str_contains($wrapLower, 'left')) {
                        $imgStyle .= 'float:left;margin:0 12px 8px 0;';
                        $wrapAlign = '';
                    }
                    if (str_contains($wrapLower, 'right')) {
                        $imgStyle .= 'float:right;margin:0 0 8px 12px;';
                        $wrapAlign = '';
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("[Import] Image style read failed: " . $e->getMessage());
            $imgStyle .= 'max-width:100%;height:auto;';
        }

        // ✅ FIX: Tentukan CSS alignment untuk img itu sendiri
        // text-align:center pada parent TIDAK bekerja untuk display:block.
        // Solusi: gunakan margin:0 auto untuk center, atau float untuk left/right.
        $imgAlignCss = '';
        $alignValue  = '';
        if ($wrapAlign) {
            // Ekstrak nilai alignment dari string "text-align:center;"
            if (preg_match('/text-align\s*:\s*(\w+)/', $wrapAlign, $m)) {
                $alignValue = $m[1];
            }
        }

        if ($alignValue === 'center') {
            $imgAlignCss = 'margin:10px auto;display:block;';
        } elseif ($alignValue === 'right') {
            $imgAlignCss = 'margin:10px 0 10px auto;display:block;';
        } elseif ($alignValue === 'justify') {
            // justify pada gambar → center (sama seperti Word behavior)
            $imgAlignCss = 'margin:10px auto;display:block;';
        } else {
            // left atau default
            $imgAlignCss = 'margin:10px 0;display:block;';
        }

        // Gabungkan imgAlignCss ke imgStyle (ganti display:block; di depan)
        $imgStyle    = preg_replace('/^display:block;/', '', $imgStyle);
        $finalStyle  = $imgAlignCss . $imgStyle;

        $imgTag = '<img src="' . htmlspecialchars($url) . '" alt="Gambar borang" style="' . $finalStyle . '" />';

        // Jika ada float (wrap left/right) → bungkus div agar clearfix bekerja
        if (str_contains($imgStyle, 'float:')) {
            return '<div style="overflow:hidden;margin:10px 0;">' . $imgTag . '</div>' . "\n";
        }

        return $imgTag . "\n";
    }

    // =========================================================================
    // ELEMENT → HTML (dengan style Word)
    // =========================================================================

    /**
     * ✅ Konversi Text/TextRun ke HTML dengan styling (bold, italic, underline, color, size)
     * Paragraph wrapping HANYA dilakukan di sini (tidak di textToHtml).
     */
    private function elementToHtml($element): string
    {
        if ($element instanceof Text) {
            $inline = $this->textToInlineHtml($element->getText(), $element->getFontStyle());
            return $this->wrapParagraph($inline, $element->getParagraphStyle());
        }

        if ($element instanceof TextRun) {
            $parts     = [];
            $paraStyle = $element->getParagraphStyle();

            foreach ($element->getElements() as $child) {
                if ($child instanceof Text) {
                    $parts[] = $this->textToInlineHtml($child->getText(), $child->getFontStyle());
                } elseif ($child instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                    $parts[] = '<br>';
                } elseif ($child instanceof Image) {
                    // ✅ Pass paragraph style agar alignment (justify/center/dll) ikut terbaca
                    $parts[] = $this->imageElementToHtml($child, $paraStyle);
                }
            }

            $inner = implode('', $parts);
            return $this->wrapParagraph($inner, $paraStyle);
        }

        return '';
    }

    /**
     * ✅ Konversi teks + FontStyle ke INLINE HTML saja (tanpa <p>).
     * Paragraph wrapping ditangani oleh elementToHtml() melalui wrapParagraph().
     */
    private function textToInlineHtml(string $text, $fontStyle): string
    {
        // ✅ FIX 1: Decode entity dulu (PhpWord kadang pre-escape teks → &quot; dll),
        // lalu encode ulang dengan benar supaya tidak double-encode.
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($text === '') return '';

        $isBold        = false;
        $isItalic      = false;
        $isUnderline   = false;
        $isStrike      = false; // ✅ FIX 2: strikethrough
        $css           = [];

        if ($fontStyle instanceof \PhpOffice\PhpWord\Style\Font) {
            $isBold   = (bool) $fontStyle->isBold();
            $isItalic = (bool) $fontStyle->isItalic();

            // getUnderline() → string ('single','double',...) bukan bool
            $ul          = $fontStyle->getUnderline();
            $isUnderline = !empty($ul) && $ul !== \PhpOffice\PhpWord\Style\Font::UNDERLINE_NONE;

            // ✅ FIX 2: Strikethrough (nama method berbeda tiap versi PhpWord)
            if (method_exists($fontStyle, 'isStrikethrough')) {
                $isStrike = (bool) $fontStyle->isStrikethrough();
            } elseif (method_exists($fontStyle, 'getStrikethrough')) {
                $isStrike = (bool) $fontStyle->getStrikethrough();
            }

            $color = $fontStyle->getColor();
            if ($color && $color !== '000000' && strtolower($color) !== 'auto') {
                $css[] = 'color:#' . ltrim($color, '#');
            }

            // ✅ FIX 3: PhpWord getSize() bisa mengembalikan half-points ATAU points,
            // tergantung versi. Deteksi otomatis:
            //   - Nilai > 72  → pasti half-points  (misal 24 = 12pt, 48 = 24pt)
            //   - Nilai <= 72 → sudah dalam points (misal 12 = 12pt, 10 = 10pt)
            // Dengan begitu 12pt di Word selalu menjadi 12pt di HTML.
            $size = $fontStyle->getSize();
            if ($size && $size > 0) {
                $pt = $size > 72 ? round($size / 2, 1) : (float) $size;
                // Emit jika bukan default body (10-12pt) dan dalam range wajar
                if ($pt >= 6 && $pt <= 96 && $pt != 12 && $pt != 11 && $pt != 10) {
                    $css[] = 'font-size:' . $pt . 'pt';
                }
            }

            $name = $fontStyle->getName();
            if ($name && stripos($name, 'times') !== false) {
                $css[] = 'font-family:"Times New Roman",serif';
            }

            // ✅ Highlight (background color)
            $highlight = null;
            if (method_exists($fontStyle, 'getHighlight')) {
                $highlight = $fontStyle->getHighlight(); // e.g. 'yellow', 'cyan', etc.
            } elseif (method_exists($fontStyle, 'getBgColor')) {
                $highlight = $fontStyle->getBgColor(); // hex color
            }
            if ($highlight && $highlight !== 'none' && $highlight !== 'default') {
                // PhpWord bisa return nama warna Word ('yellow') atau hex
                $highlightColorMap = [
                    'yellow'      => '#FFFF00',
                    'green'       => '#00FF00',
                    'cyan'        => '#00FFFF',
                    'magenta'     => '#FF00FF',
                    'blue'        => '#0000FF',
                    'red'         => '#FF0000',
                    'darkBlue'    => '#00008B',
                    'darkCyan'    => '#008B8B',
                    'darkGreen'   => '#006400',
                    'darkMagenta' => '#8B008B',
                    'darkRed'     => '#8B0000',
                    'darkYellow'  => '#808000',
                    'darkGray'    => '#A9A9A9',
                    'lightGray'   => '#D3D3D3',
                    'black'       => '#000000',
                    'white'       => '#FFFFFF',
                    'none'        => '',
                ];
                $bgCss = isset($highlightColorMap[$highlight])
                    ? $highlightColorMap[$highlight]
                    : (preg_match('/^[0-9a-fA-F]{3,6}$/', $highlight) ? '#' . $highlight : '');
                if ($bgCss) {
                    $css[] = 'background-color:' . $bgCss;
                }
            }
        }

        // ✅ Urutan wrap: strike → bold → italic → underline
        if ($isStrike)    $text = "<s>{$text}</s>";
        if ($isBold)      $text = "<strong>{$text}</strong>";
        if ($isItalic)    $text = "<em>{$text}</em>";
        if ($isUnderline) $text = "<u>{$text}</u>";

        if (!empty($css)) {
            $text = '<span style="' . implode(';', $css) . '">' . $text . '</span>';
        }

        return $text;
    }

    /**
     * Backward-compat — dipakai jika ada pemanggil lain yang masih butuh <p> wrapper.
     */
    private function textToHtml(string $text, $fontStyle, $paragraphStyle): string
    {
        return $this->wrapParagraph($this->textToInlineHtml($text, $fontStyle), $paragraphStyle);
    }

    /**
     * Wrap text dalam <p> dengan alignment, spacing, dan style nama paragraf (Caption dll).
     */
    private function wrapParagraph(string $inner, $paragraphStyle): string
    {
        $inner = trim($inner);
        if ($inner === '') return '';

        // ✅ FIX: Jika inner hanya berisi gambar/div (tanpa teks),
        // jangan bungkus dalam <p> — <div> dalam <p> = invalid HTML,
        // dan TinyMCE akan membuang atau merusak struktur tersebut.
        $innerText = trim(strip_tags($inner));
        $hasOnlyMedia = ($innerText === '')
            && (str_contains($inner, '<img') || str_contains($inner, '<div'));
        if ($hasOnlyMedia) return $inner;

        $css       = [];
        $isCaption = false;
        $styleName = '';

        if ($paragraphStyle instanceof \PhpOffice\PhpWord\Style\Paragraph) {
            // --- Alignment ---
            $align = $paragraphStyle->getAlignment();
            $alignMap = [
                \PhpOffice\PhpWord\SimpleType\Jc::CENTER     => 'center',
                \PhpOffice\PhpWord\SimpleType\Jc::END        => 'right',
                \PhpOffice\PhpWord\SimpleType\Jc::RIGHT      => 'right',
                \PhpOffice\PhpWord\SimpleType\Jc::BOTH       => 'justify',
                \PhpOffice\PhpWord\SimpleType\Jc::DISTRIBUTE => 'justify',
            ];
            if ($align && isset($alignMap[$align])) {
                $css[] = 'text-align:' . $alignMap[$align];
            }

            // --- Spacing before/after (twips → px, 1px = 20 twips) ---
            $spaceBefore = $paragraphStyle->getSpaceBefore();
            $spaceAfter  = $paragraphStyle->getSpaceAfter();
            if ($spaceBefore > 0) $css[] = 'margin-top:'    . round($spaceBefore / 20) . 'px';
            if ($spaceAfter  > 0) $css[] = 'margin-bottom:' . round($spaceAfter  / 20) . 'px';

            // --- Line spacing ---
            $lineHeightCss = $this->resolveLineSpacingCss($paragraphStyle);
            if ($lineHeightCss !== '') $css[] = 'line-height:' . $lineHeightCss;

            // --- Indentasi (twips → px, 1px ≈ 15 twips) ---
            if (method_exists($paragraphStyle, 'getIndentation')) {
                $indObj = $paragraphStyle->getIndentation();
                if ($indObj instanceof \PhpOffice\PhpWord\Style\Indentation) {
                    $leftTwips  = $indObj->getLeft();
                    $rightTwips = $indObj->getRight();
                    $firstLine  = $indObj->getFirstLine();
                    $hanging    = $indObj->getHanging();

                    if ($leftTwips  > 0) $css[] = 'margin-left:'   . round($leftTwips  / 15) . 'px';
                    if ($rightTwips > 0) $css[] = 'margin-right:'  . round($rightTwips / 15) . 'px';
                    if ($firstLine  > 0) $css[] = 'text-indent:'   . round($firstLine  / 15) . 'px';
                    if ($hanging    > 0) $css[] = 'text-indent:-'  . round($hanging    / 15) . 'px';
                }
            }
        }

        // ✅ Cek style name untuk Caption, Heading, dll.
        // Style name bisa ada di paragraphStyle (string named style) atau object
        if (is_string($paragraphStyle)) {
            $styleName = strtolower(trim($paragraphStyle));
        } elseif (method_exists($paragraphStyle, 'getStyleName')) {
            $styleName = strtolower(trim((string) $paragraphStyle->getStyleName()));
        }

        // Deteksi Caption (Figure caption, table caption)
        if (str_contains($styleName, 'caption') || str_contains($styleName, 'keterangan gambar')) {
            $isCaption = true;
            $css[] = 'text-align:center';
            $css[] = 'font-style:italic';
            $css[] = 'color:#555';
            $css[] = 'font-size:10pt';
            $css[] = 'margin-top:4px';
            $css[] = 'margin-bottom:10px';
        }

        // Deduplikasi CSS (text-align bisa duplikat jika dari align + caption)
        $css = array_unique($css);

        $styleAttr = !empty($css) ? " style='" . implode(';', $css) . "'" : '';

        if ($isCaption) {
            return "<p class='caption'{$styleAttr}>{$inner}</p>";
        }

        return "<p{$styleAttr}>{$inner}</p>";
    }

    // =========================================================================
    // TABLE → HTML
    // =========================================================================

    private function convertTableToHtml(Table $table): string
    {
        $html       = '<table style="width:100%;border-collapse:collapse;">';
        $isFirstRow = true;

        foreach ($table->getRows() as $row) {
            $html .= '<tr>';

            foreach ($row->getCells() as $cell) {
                $tag   = $isFirstRow ? 'th' : 'td';
                $style = 'border:1px solid #ddd;padding:8px;';
                if ($isFirstRow) $style .= 'background:#f2f2f2;font-weight:bold;text-align:center;';

                // Cell width
                $width = null;
                if (method_exists($cell, 'getWidth')) $width = $cell->getWidth();
                if ($width) $style .= 'width:' . round($width / 100) . '%;';

                // Cell vAlign
                $vAlign = null;
                if (method_exists($cell, 'getStyle')) {
                    $cs = $cell->getStyle();
                    if ($cs instanceof \PhpOffice\PhpWord\Style\Cell) {
                        $vAlign = $cs->getVAlign();
                    }
                }
                if ($vAlign) $style .= 'vertical-align:' . $vAlign . ';';

                $html .= "<{$tag} style='{$style}'>";

                $parts = [];
                foreach ($cell->getElements() as $el) {
                    if ($el instanceof Text || $el instanceof TextRun) {
                        $h = $this->elementToHtml($el);
                        if (strip_tags($h) !== '') $parts[] = $h;
                    } elseif ($el instanceof Image) {
                        $parts[] = $this->imageElementToHtml($el);
                    } elseif ($el instanceof Table) {
                        $parts[] = $this->convertTableToHtml($el);
                    } elseif ($el instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {
                        $inner = '';
                        $dummy = [];
                        $this->extractFromContainer($el, $inner, $dummy);
                        if (trim($inner) !== '') $parts[] = $inner;
                    }
                }

                $html .= !empty($parts) ? implode('', $parts) : '&nbsp;';
                $html .= "</{$tag}>";
            }

            $html       .= '</tr>';
            $isFirstRow  = false;
        }

        $html .= '</table>';
        return $html;
    }

    // =========================================================================
    // SAVE
    // =========================================================================

    private function saveBorangData($pengajuan, $elemenCode, $deskripsi, $tablesData, $borangImport = null)
    {
        $elemen = $this->getElemenByKodeCached($elemenCode);
        if (!$elemen) {
            Log::warning("[Import] Elemen not found in DB: {$elemenCode}");
            return false;
        }

        $savedCount = 0;

        // Description
        $descKey          = 'desc_' . $elemen->id;
        $cleanedDeskripsi = trim($this->stripTemplateInstructions($deskripsi));

        BorangData::updateOrCreate(
            ['id_pengajuan' => $pengajuan->id, 'dataset_id' => $descKey],
            [
                'nilai'            => $cleanedDeskripsi !== '' ? $cleanedDeskripsi : null,
                'id_elemen' => $elemen->id,
                'id_borang_import' => $borangImport?->id,
            ]
        );
        $savedCount++;

        // Tables
        if (!empty($tablesData)) {
            $datasets = $this->getTableDatasetsForElemenCached($elemen->id);

            foreach ($tablesData as $index => $tableData) {
                if (!isset($datasets[$index])) continue;

                $dataset    = $datasets[$index];
                $isTemplate = is_array($tableData) ? ($tableData['is_template'] ?? false) : false;
                $html       = is_array($tableData) ? $tableData['html'] : $tableData;

                BorangData::updateOrCreate(
                    ['id_pengajuan' => $pengajuan->id, 'dataset_id' => $dataset->kode],
                    [
                        'nilai'             => $html,
                        'id_elemen' => $elemen->id,
                        'is_template'       => $isTemplate,
                        'id_dataset_borang' => $dataset->id,
                        'id_borang_import'  => $borangImport?->id,
                    ]
                );
                $savedCount++;
            }
        }

        if ($borangImport) {
            $borangImport->increment('parsed_sections', 1);
            $borangImport->increment('parsed_tables', count($tablesData));
        }

        return $savedCount > 0;
    }

    // =========================================================================
    // FRONT MATTER
    // =========================================================================

    private function extractFrontMatter($phpWord): array
    {
        $kataPengantar = '';
        $ringkasan     = '';
        $suplemen      = [];
        $mode          = null;

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text = trim($this->flattenElementText($element));
                if ($text === '') continue;

                if ($this->isTocBlock($text)) {
                    $mode = 'stop';
                    continue;
                }
                if (preg_match('/^([DEPILAR])\.\s+.+$/i', $text)) {
                    $mode = 'stop';
                    continue;
                }
                if (preg_match('/^(\d+\.\s*)?([DEPILAR])\.(\d+)\.?\s+.+$/i', $text)) {
                    $mode = 'stop';
                    continue;
                }
                if ($mode === 'stop') continue;

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

                if (in_array($mode, ['kata_pengantar', 'ringkasan', 'suplemen'])) {
                    $text = $this->cleanFrontMatterText($text);
                    if ($text === '') continue;
                } else {
                    if ($this->isInstructionText($text)) continue;
                }

                if ($mode === 'kata_pengantar' && preg_match('/^Kata\s+Pengantar$/i', $text)) continue;
                if ($mode === 'ringkasan'      && preg_match('/^Ringkasan\b/i', $text))       continue;

                if ($mode === 'kata_pengantar') $kataPengantar .= $text . "\n";
                elseif ($mode === 'ringkasan')  $ringkasan     .= $text . "\n";
                elseif ($mode === 'suplemen')   $suplemen[]     = $text;
            }
        }

        return [
            'kata_pengantar' => $this->cleanText($kataPengantar),
            'ringkasan'      => $this->cleanText($ringkasan),
            'suplemen'       => array_values(array_filter(array_map('trim', $suplemen))),
        ];
    }

    private function saveFrontMatterToBorangData($pengajuan, array $frontMatter, $borangImport = null): void
    {
        foreach (['kata_pengantar', 'ringkasan'] as $key) {
            BorangData::updateOrCreate(
                ['id_pengajuan' => $pengajuan->id, 'dataset_id' => $key],
                ['nilai' => $frontMatter[$key] !== '' ? $frontMatter[$key] : null, 'id_borang_import' => $borangImport?->id]
            );
        }

        $suplemenArr = array_values(array_filter(array_map('trim', $frontMatter['suplemen'] ?? [])));
        BorangData::updateOrCreate(
            ['id_pengajuan' => $pengajuan->id, 'dataset_id' => 'suplemen'],
            [
                'nilai'            => !empty($suplemenArr) ? json_encode($suplemenArr, JSON_UNESCAPED_UNICODE) : null,
                'id_borang_import' => $borangImport?->id,
            ]
        );
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function hasRealTableData(string $tableHtml): bool
    {
        if (empty($tableHtml) || strlen($tableHtml) < 100) return false;

        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $tableHtml, $cells);
        if (empty($cells[1])) return false;

        $nonEmpty = 0;
        foreach ($cells[1] as $cell) {
            $t = strip_tags(html_entity_decode($cell));
            $t = str_replace([' ', '&nbsp;'], '', trim($t));
            if ($t !== '' && !(is_numeric($t) && (int)$t <= 10)) {
                $nonEmpty++;
            }
        }
        return $nonEmpty >= 3;
    }

    /**
     * Cek apakah teks list item adalah instruksi template (harus dibuang).
     * Berbeda dari isPlaceholderText — TIDAK cek panjang karakter,
     * karena list item sering pendek: "A", "B", "1", dll.
     */
    private function isListItemPlaceholder(string $text): bool
    {
        $text  = trim($text);
        if ($text === '') return true;
        $lower = strtolower($text);
        if (stripos($text, 'mohon jangan dihapus') !== false) return true;
        foreach (['[mohon isi', 'mohon isi sesuai'] as $p) {
            if (strpos($lower, $p) !== false) return true;
        }
        return false;
    }

    private function isPlaceholderText(string $text): bool
    {
        $text = trim($text);
        if (empty($text) || strlen($text) < 10) return true;
        if (stripos($text, 'mohon jangan dihapus') !== false) return true;
        if (preg_match('/^deskripsi\s+[a-z\s]+\.$/i', $text)) return true;
        if (preg_match('/^tabel\s+[a-z]\.\d+\.[a-z]/i', $text)) return true;

        $lower = strtolower($text);
        foreach (['[mohon isi', 'mohon isi sesuai'] as $p) {
            if (strpos($lower, $p) !== false) return true;
        }
        if (in_array($lower, ['deskripsi', 'tabel', 'keterangan', 'catatan', 'no', 'nama'])) return true;

        return false;
    }

    private function stripTemplateInstructions(string $text): string
    {
        $text = preg_replace('/\(?\s*mohon\s+jangan\s+dihapus\s*\)?/i', '', $text);
        $text = preg_replace('/\[[^\]]{1,200}\]/', '', $text);
        return $this->cleanText($text);
    }

    private function cleanText(string $text): string
    {
        $text  = str_replace(["\r\n", "\r"], "\n", $text);
        $text  = preg_replace('/\n{3,}/', "\n\n", $text);
        $text  = preg_replace('/[ \t]+/', ' ', $text);
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        return trim(implode("\n", $lines));
    }

    private function extractTableText(Table $table): string
    {
        $text = '';
        foreach ($table->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                foreach ($cell->getElements() as $el) {
                    if ($el instanceof Text || $el instanceof TextRun) {
                        $text .= $this->extractTextContent($el) . ' ';
                    }
                }
            }
        }
        return trim($text);
    }

    private function extractTextContent($element): string
    {
        if ($element instanceof Text) return (string) $element->getText();
        if ($element instanceof TextRun) {
            $out = '';
            foreach ($element->getElements() as $sub) {
                if ($sub instanceof Text) $out .= $sub->getText();
            }
            return $out;
        }
        return '';
    }

    private function flattenElementText($element): string
    {
        if ($element === null) return '';
        if ($element instanceof \PhpOffice\PhpWord\Element\Text) return (string) $element->getText();
        if ($element instanceof \PhpOffice\PhpWord\Element\PreserveText) {
            $t = $element->getText();
            return is_array($t) ? implode('', array_map('strval', $t)) : (string) $t;
        }
        if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) return "\n";
        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            $out = '';
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $child) $out .= $this->flattenElementText($child) . ' ';
                    $out .= "\n";
                }
            }
            return trim($out);
        }
        if (method_exists($element, 'getElements')) {
            $out = '';
            foreach ($element->getElements() as $child) $out .= $this->flattenElementText($child) . ' ';
            return trim($out);
        }
        return '';
    }

    private function isInstructionText(string $text): bool
    {
        $text = trim($text);
        if (empty($text) || strlen($text) < 5) return true;
        if (preg_match('/^Deskripsi\s+.+?\s*\(Mohon jangan dihapus\)\s*$/i', $text)) return true;
        if (preg_match('/^\[Mohon isi.+?maksimal\s+\d+\s+kata.+?\]$/is', $text))     return true;
        foreach (['Mohon jangan dihapus', 'Mohon isi deskripsi di sini', 'maksimal 1000 kata', 'maksimal 500 kata', 'sesuai dengan kondisi program studi'] as $kw) {
            if (stripos($text, $kw) !== false) return true;
        }
        if (preg_match('/^\[.*?\]$/', $text) && strlen($text) < 200) return true;
        return false;
    }

    private function isTocBlock(string $text): bool
    {
        $t = strtolower(trim(preg_replace('/\s+/', ' ', $text)));
        if (preg_match('/\bdaftar isi\b/i', $t)) return true;
        if (preg_match('/\bhalaman\b/i', $t) && preg_match('/\bcover|pengesahan|kata|ringkasan|daftar\b/i', $t)) return true;
        if (preg_match('/\.{3,}\s*\d+$/', $t)) return true;
        return false;
    }

    private function cleanFrontMatterText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text));
        $text = preg_replace('/\(\s*mohon\s*jangan\s*d[ií]h?a?p?u?\s*s[^)]*\)/i', '', $text);
        $text = preg_replace('/mohon\s*jangan\s*d[ií]h?a?p?u?\s*s/i', '', $text);
        $text = preg_replace('/\(\s*maksimal\s+\d+\s+kata\s*\)/i', '', $text);
        $text = preg_replace('/^\s*kata\s+pengantar\s*[:\-]?\s*/i', '', $text);
        $text = preg_replace('/^\s*ringkasan\s*[:\-]?\s*/i', '', $text);
        $text = preg_replace('/\s+([,.;:!?])/', '$1', $text);
        return trim(preg_replace('/\s{2,}/', ' ', $text));
    }

    private function getExtensionFromMime(?string $mime): string
    {
        return ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'image/bmp' => 'bmp', 'image/svg+xml' => 'svg'][$mime] ?? 'png';
    }

    private function isValidImage(string $binary): bool
    {
        if (strlen($binary) < 100) return false;
        foreach (["\x89PNG", "\xFF\xD8\xFF", "GIF", "BM", "RIFF"] as $magic) {
            if (strncmp($binary, $magic, strlen($magic)) === 0) return true;
        }
        return false;
    }

    private function deleteTempDir(string $dir): void
    {
        try {
            if (!is_dir($dir)) return;
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $f) {
                $f->isDir() ? @rmdir($f->getRealPath()) : @unlink($f->getRealPath());
            }
            @rmdir($dir);
        } catch (\Throwable $e) {
        }
    }

    private function purgeOldImagesForPengajuan(): void
    {
        $dir = "permohonan-akreditasi/{$this->pengajuanId}/kualitatif/images";

        try {
            // hapus semua isi folder (kalau tidak ada, aman)
            Storage::disk('public')->deleteDirectory($dir);
        } catch (\Throwable $e) {
            Log::warning("[Import] purgeOldImagesForPengajuan delete failed: " . $e->getMessage());
        }

        // (opsional) buat ulang folder agar siap dipakai
        $this->ensurePublicDirectory($dir);
    }

    private function storePublicImage(string $dir, string $name, string $binary): ?string
    {
        $path = "{$dir}/{$name}";

        if (!$this->ensurePublicDirectory($dir)) {
            Log::error("[Import] Failed to prepare image directory: {$dir}");
            return null;
        }

        try {
            Storage::disk('public')->put($path, $binary);
        } catch (\Throwable $e) {
            Log::warning("[Import] Storage put image failed, trying direct write: " . $e->getMessage(), [
                'path' => $path,
            ]);

            if (!$this->writePublicFileDirectly($path, $binary)) {
                return null;
            }
        }

        if (!Storage::disk('public')->exists($path)) {
            Log::warning("[Import] Stored image not visible via disk, trying direct write", [
                'path' => $path,
            ]);

            if (!$this->writePublicFileDirectly($path, $binary)) {
                Log::error("[Import] Failed to save image: {$path}");
                return null;
            }
        }

        return Storage::disk('public')->url($path);
    }

    private function ensurePublicDirectory(string $dir): bool
    {
        $disk = Storage::disk('public');

        try {
            $absoluteDir = $disk->path($dir);

            if (is_dir($absoluteDir) || $disk->makeDirectory($dir)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning("[Import] Storage directory creation failed: " . $e->getMessage(), [
                'dir' => $dir,
            ]);
        }

        try {
            $absoluteDir = $disk->path($dir);

            if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
                Log::error("[Import] Native directory creation failed", [
                    'dir' => $dir,
                    'absolute_dir' => $absoluteDir,
                    'last_error' => error_get_last()['message'] ?? null,
                ]);

                return false;
            }

            @chmod($absoluteDir, 0775);

            return is_dir($absoluteDir) && is_writable($absoluteDir);
        } catch (\Throwable $e) {
            Log::error("[Import] Native directory creation exception: " . $e->getMessage(), [
                'dir' => $dir,
            ]);

            return false;
        }
    }

    private function writePublicFileDirectly(string $path, string $binary): bool
    {
        $disk = Storage::disk('public');
        $dir = trim(str_replace('\\', '/', dirname($path)), '.');

        if ($dir !== '' && !$this->ensurePublicDirectory($dir)) {
            return false;
        }

        try {
            $absolutePath = $disk->path($path);
            $bytes = @file_put_contents($absolutePath, $binary, LOCK_EX);

            if ($bytes === false) {
                Log::error("[Import] Native image write failed", [
                    'path' => $path,
                    'absolute_path' => $absolutePath,
                    'last_error' => error_get_last()['message'] ?? null,
                ]);

                return false;
            }

            @chmod($absolutePath, 0664);

            return true;
        } catch (\Throwable $e) {
            Log::error("[Import] Native image write exception: " . $e->getMessage(), [
                'path' => $path,
            ]);

            return false;
        }
    }

    private function getElemenByKodeCached(string $kode): ?ElemenStandar
    {
        if (isset($this->elemenCacheByKode[$kode])) {
            return $this->elemenCacheByKode[$kode];
        }

        $elemen = ElemenStandar::where('kode_elemen', $kode)->first();
        return $this->elemenCacheByKode[$kode] = $elemen;
    }

    private function getTableDatasetsForElemenCached(int $elemenId)
    {
        if (isset($this->datasetTableCacheByElemenId[$elemenId])) {
            return $this->datasetTableCacheByElemenId[$elemenId];
        }

        // 1 query per elemenId (bukan per table row)
        $datasets = \App\Models\DatasetBorang::query()
            ->where('id_elemen', $elemenId)
            ->where('tipe_field', 'table')
            ->orderBy('urutan')
            ->get();

        return $this->datasetTableCacheByElemenId[$elemenId] = $datasets;
    }
}
