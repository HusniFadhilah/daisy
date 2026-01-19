<?php

namespace App\Services;

use PhpOffice\PhpWord\SimpleType\Jc;
use Illuminate\Support\Facades\Log;

class HtmlToPhpWordParser
{
    protected array $tmpImages = [];
    protected array $numberingRegistered = [];
    protected $phpWord;
    protected int $pengajuanId;

    public function __construct($phpWord = null, int $pengajuanId)
    {
        $this->phpWord = $phpWord;
        $this->pengajuanId = $pengajuanId;
    }

    /**
     * ✅ Main method: Add HTML content ke container
     */
    public function addHtmlContent($container, string $html): void
    {
        if (trim($html) === '') return;

        // Clean HTML
        $html = $this->sanitizeHtml($html);

        // ✅ PRE-EXTRACT images with regex (fallback)
        $images = $this->extractImagesWithRegex($html);

        // Remove images from HTML temporarily
        $htmlWithoutImages = preg_replace('/<img[^>]+>/i', '<!-- IMAGE_PLACEHOLDER -->', $html);

        // Parse HTML without images
        if (strlen($htmlWithoutImages) > 10000) {
            $chunks = $this->chunkHtml($htmlWithoutImages, 5000);
            foreach ($chunks as $chunk) {
                $this->parseAndAddHtml($container, $chunk);
            }
        } else {
            $this->parseAndAddHtml($container, $htmlWithoutImages);
        }

        // ✅ Add images at the end (or wherever placeholder is)
        foreach ($images as $imgData) {
            try {
                $this->addImageFromSrc($container, $imgData['src'], $imgData['width'], $imgData['height']);
            } catch (\Exception $e) {
                Log::error("Failed to add extracted image: " . $e->getMessage());
                $container->addText(
                    '[Gambar tidak dapat dimuat]',
                    ['size' => 10, 'italic' => true, 'color' => 'FF0000'],
                    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
                );
            }
        }
    }

    /**
     * ✅ Parse HTML dan add ke container
     */
    private function parseAndAddHtml($container, string $html): void
    {
        // ✅ PRE-PROCESS: Extract images dari conditional comments
        $html = $this->extractImagesFromConditionalComments($html);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        $wrappedHtml = '<?xml encoding="UTF-8"><div>' . $html . '</div>';
        $dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        $body = $dom->getElementsByTagName('div')->item(0);
        if ($body) {
            $this->processHtmlNode($container, $body);
        }
    }

    /**
     * ✅ NEW: Extract images dari conditional comments (Word export format)
     */
    private function extractImagesFromConditionalComments(string $html): string
    {
        // Pattern untuk detect conditional comments dengan image
        // Format: <!--[if !vml]--><img ...><!--[endif]-->
        $pattern = '/<!--\[if[^\]]*\]-->(.*?)<img([^>]+)>(.*?)<!--\[endif\]-->/is';

        $count = 0;
        $html = preg_replace_callback($pattern, function ($matches) use (&$count) {
            $imgTag = '<img' . $matches[2] . '>';
            $count++;

            return $imgTag; // Replace dengan img tag saja
        }, $html);

        return $html;
    }

    /**
     * ✅ Process HTML node recursively - Support table & image
     */
    private function processHtmlNode($container, \DOMNode $node, int $depth = 0): void
    {
        $indent = str_repeat('  ', $depth);

        foreach ($node->childNodes as $child) {
            $nodeName = $child->nodeName;

            // ✅ Log semua nodes yang di-process
            if ($depth < 3) { // Limit logging untuk avoid spam
                // Log::debug("{$indent}Processing node: {$nodeName}");
            }

            switch ($nodeName) {
                case 'p':
                    $this->addHtmlParagraph($container, $child);
                    break;

                case 'table':
                    $this->addHtmlTableFromNode($container, $child);
                    break;

                case 'br':
                    $container->addTextBreak(1);
                    break;

                case 'ul':
                case 'ol':
                    $this->addHtmlList($container, $child);
                    break;

                case 'img':
                    $this->handleHtmlImage($container, $child);
                    break;

                case '#text':
                    $text = trim($child->textContent);
                    if ($text !== '') {
                        $container->addText($text, ['size' => 11], ['alignment' => Jc::BOTH, 'spaceAfter' => 200]);
                    }
                    break;

                case 'span':
                    // ✅ Span often wraps images - recurse into it
                    if ($child->hasChildNodes()) {
                        $this->processHtmlNode($container, $child, $depth + 1);
                    }
                    break;

                default:
                    if ($child->hasChildNodes()) {
                        $this->processHtmlNode($container, $child, $depth + 1);
                    }
                    break;
            }
        }
    }

    /**
     * ✅ Add HTML paragraph dengan inline formatting
     */
    private function addHtmlParagraph($container, \DOMNode $pNode): void
    {
        $textRun = $container->addTextRun(['alignment' => Jc::BOTH, 'spaceAfter' => 200]);
        $this->addInlineHtmlContent($textRun, $pNode);
    }

    /**
     * ✅ Add inline HTML content (bold, italic, etc)
     */
    private function addInlineHtmlContent($textRun, \DOMNode $node): void
    {
        foreach ($node->childNodes as $child) {
            $fontStyle = ['size' => 11];

            switch ($child->nodeName) {
                case 'strong':
                case 'b':
                    $fontStyle['bold'] = true;
                    $text = $this->getNodeText($child);
                    if (trim($text) !== '') {
                        $textRun->addText($text, $fontStyle);
                    }
                    break;

                case 'em':
                case 'i':
                    $fontStyle['italic'] = true;
                    $text = $this->getNodeText($child);
                    if (trim($text) !== '') {
                        $textRun->addText($text, $fontStyle);
                    }
                    break;

                case 'u':
                    $fontStyle['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
                    $text = $this->getNodeText($child);
                    if (trim($text) !== '') {
                        $textRun->addText($text, $fontStyle);
                    }
                    break;

                case 'br':
                    $textRun->addTextBreak(1);
                    break;

                case '#text':
                    $text = $child->textContent;
                    if (trim($text) !== '') {
                        $textRun->addText($text, $fontStyle);
                    }
                    break;

                default:
                    if ($child->hasChildNodes()) {
                        $this->addInlineHtmlContent($textRun, $child);
                    }
                    break;
            }
        }
    }

    /**
     * ✅ Add HTML list (ul/ol)
     */
    private function addHtmlList($container, \DOMNode $listNode): void
    {
        $isOrdered = $listNode->nodeName === 'ol';

        $numberingName = 'htmlList_' . uniqid();
        $this->ensureNumberingStyle($numberingName, [
            'type' => 'multilevel',
            'levels' => [[
                'level'   => 0,
                'format'  => $isOrdered ? 'decimal' : 'bullet',
                'text'    => $isOrdered ? '%1.' : '•',
                'left'    => 360,
                'hanging' => 360,
                'tabPos'  => 720,
            ]]
        ]);

        foreach ($listNode->childNodes as $li) {
            if ($li->nodeName === 'li') {
                $text = $this->getNodeText($li);
                if (trim($text) !== '') {
                    $container->addListItem(
                        $text,
                        0,
                        ['size' => 11],
                        $numberingName,
                        ['alignment' => Jc::BOTH, 'spaceAfter' => 120]
                    );
                }
            }
        }
    }

    /**
     * ✅ SIMPLEST: Table dengan fixed twips width
     */
    private function addHtmlTableFromNode($container, \DOMNode $tableNode): void
    {
        $allRows = $this->extractTableRows($tableNode);
        $columnCount = $this->getMaxColumnCount($allRows);

        // ✅ FIXED: Enable autofit
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'width' => 100 * 50, // 100% width
            'unit' => 'pct',
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_AUTO, // ✅ KEY FIX!
        ];

        $phpWordTable = $container->addTable($tableStyle);

        $mergedCells = [];

        foreach ($allRows as $rowIndex => $row) {
            if ($row->nodeName !== 'tr') continue;

            $isHeaderRow = $this->isHeaderRow($row);
            $cells = $this->getRowCells($row);
            $isTitleRow = (count($cells) === 1 && $this->getCellColspan($cells[0]) > 1);

            $phpWordTable->addRow($isHeaderRow || $isTitleRow ? 400 : 350);

            $colIndex = 0;
            foreach ($cells as $cell) {
                while (isset($mergedCells[$rowIndex][$colIndex])) {
                    $colIndex++;
                }

                // ✅ Gunakan autofit method
                $this->addHtmlTableCellAutofit(
                    $phpWordTable,
                    $cell,
                    $isHeaderRow || $isTitleRow,
                    $rowIndex,
                    $colIndex,
                    $mergedCells,
                    $columnCount
                );

                $colIndex++;
            }
        }
    }

    /**
     * ✅ SIMPLEST: Add cell tanpa explicit width (auto)
     */
    private function addHtmlTableCellSimple(
        $phpWordTable,
        \DOMElement $cellNode,
        bool $isHeader,
        int $rowIndex,
        int $colIndex,
        &$mergedCells
    ): void {
        $colspan = $cellNode->hasAttribute('colspan') ? (int)$cellNode->getAttribute('colspan') : 1;
        $rowspan = $cellNode->hasAttribute('rowspan') ? (int)$cellNode->getAttribute('rowspan') : 1;

        // ✅ Minimal cell style
        $cellStyle = [];

        if ($isHeader) {
            $cellStyle['bgColor'] = 'D3D3D3';
        }

        if ($colspan > 1) {
            $cellStyle['gridSpan'] = $colspan;
        }

        if ($rowspan > 1) {
            $cellStyle['vMerge'] = 'restart';

            for ($r = $rowIndex + 1; $r < $rowIndex + $rowspan; $r++) {
                for ($c = $colIndex; $c < $colIndex + $colspan; $c++) {
                    $mergedCells[$r][$c] = true;
                }
            }
        }

        // ✅ Add cell - no width specified, akan auto-adjust
        $cell = $phpWordTable->addCell(null, $cellStyle);

        $cellText = $this->getCellTextPreserveBreaks($cellNode);

        $fontStyle = ['size' => 10];
        if ($isHeader || $this->hasBoldContent($cellNode)) {
            $fontStyle['bold'] = true;
        }

        $alignment = $this->getAlignmentFromNode($cellNode);

        // Simple text add
        $cell->addText(
            $cellText,
            $fontStyle,
            ['alignment' => $alignment, 'spaceAfter' => 0]
        );
    }

    /**
     * ✅ Get maximum column count dari semua rows
     */
    private function getMaxColumnCount(array $rows): int
    {
        $maxCols = 0;

        foreach ($rows as $row) {
            if ($row->nodeName !== 'tr') continue;

            $colCount = 0;
            foreach ($row->childNodes as $cell) {
                if ($cell->nodeName === 'td' || $cell->nodeName === 'th') {
                    $colspan = $cell->hasAttribute('colspan') ? (int)$cell->getAttribute('colspan') : 1;
                    $colCount += $colspan;
                }
            }

            $maxCols = max($maxCols, $colCount);
        }

        return $maxCols;
    }

    /**
     * ✅ ENHANCED: Add cell dengan autofit dan word wrap
     */
    private function addHtmlTableCellAutofit(
        $phpWordTable,
        \DOMElement $cellNode,
        bool $isHeader,
        int $rowIndex,
        int $colIndex,
        &$mergedCells,
        int $totalColumns
    ): void {
        $colspan = $cellNode->hasAttribute('colspan') ? (int)$cellNode->getAttribute('colspan') : 1;
        $rowspan = $cellNode->hasAttribute('rowspan') ? (int)$cellNode->getAttribute('rowspan') : 1;

        // ✅ FIXED: Cell style dengan proper width
        $cellWidthPercent = ($colspan / $totalColumns) * 100;

        $cellStyle = [
            'width' => $cellWidthPercent * 50,
            'unit' => 'pct',
            'valign' => 'center', // ✅ Vertical align center
        ];

        if ($isHeader) {
            $cellStyle['bgColor'] = 'D3D3D3';
        }

        if ($colspan > 1) {
            $cellStyle['gridSpan'] = $colspan;
        }

        if ($rowspan > 1) {
            $cellStyle['vMerge'] = 'restart';

            for ($r = $rowIndex + 1; $r < $rowIndex + $rowspan; $r++) {
                for ($c = $colIndex; $c < $colIndex + $colspan; $c++) {
                    $mergedCells[$r][$c] = true;
                }
            }
        }

        $cell = $phpWordTable->addCell(null, $cellStyle);

        $cellText = $this->getCellTextPreserveBreaks($cellNode);

        $fontStyle = ['size' => 10];
        if ($isHeader || $this->hasBoldContent($cellNode)) {
            $fontStyle['bold'] = true;
        }

        $alignment = $this->getAlignmentFromNode($cellNode);

        // ✅ FIXED: Enable word wrap dengan proper spacing
        $paragraphStyle = [
            'alignment' => $alignment,
            'spaceAfter' => 0,
            'spaceBefore' => 0,
            'spacing' => 0, // ✅ Compact spacing
            'wordWrap' => true, // ✅ Enable word wrap (though this is default)
        ];

        if (strpos($cellText, "\n") !== false) {
            $lines = explode("\n", $cellText);
            foreach ($lines as $idx => $line) {
                if (trim($line) === '') continue;

                $cell->addText(
                    trim($line),
                    $fontStyle,
                    array_merge($paragraphStyle, [
                        'spaceAfter' => $idx < count($lines) - 1 ? 80 : 0
                    ])
                );
            }
        } else {
            $cell->addText(
                $cellText,
                $fontStyle,
                $paragraphStyle
            );
        }
    }

    /**
     * ✅ Extract all table rows
     */
    private function extractTableRows(\DOMNode $tableNode): array
    {
        $rows = [];

        foreach ($tableNode->childNodes as $child) {
            if ($child->nodeName === 'tbody' || $child->nodeName === 'thead') {
                foreach ($child->childNodes as $row) {
                    if ($row->nodeName === 'tr') {
                        $rows[] = $row;
                    }
                }
            } elseif ($child->nodeName === 'tr') {
                $rows[] = $child;
            }
        }

        return $rows;
    }

    /**
     * ✅ Get cells dari row
     */
    private function getRowCells(\DOMNode $row): array
    {
        $cells = [];
        foreach ($row->childNodes as $cell) {
            if ($cell->nodeName === 'td' || $cell->nodeName === 'th') {
                $cells[] = $cell;
            }
        }
        return $cells;
    }

    /**
     * ✅ Get colspan dari cell
     */
    private function getCellColspan(\DOMElement $cell): int
    {
        return $cell->hasAttribute('colspan') ? (int)$cell->getAttribute('colspan') : 1;
    }

    /**
     * ✅ ENHANCED: Add cell dengan proper colspan/rowspan
     */
    private function addHtmlTableCellEnhanced($phpWordTable, \DOMElement $cellNode, bool $isHeader, int $rowIndex, int $colIndex, &$mergedCells): void
    {
        $colspan = $cellNode->hasAttribute('colspan') ? (int)$cellNode->getAttribute('colspan') : 1;
        $rowspan = $cellNode->hasAttribute('rowspan') ? (int)$cellNode->getAttribute('rowspan') : 1;

        // Cell style
        $cellStyle = [];
        if ($isHeader) {
            $cellStyle['bgColor'] = 'D3D3D3';
        }

        // Handle colspan
        if ($colspan > 1) {
            $cellStyle['gridSpan'] = $colspan;
        }

        // Handle rowspan - mark cells yang akan di-skip di row berikutnya
        if ($rowspan > 1) {
            $cellStyle['vMerge'] = 'restart';

            // Mark cells untuk di-skip
            for ($r = $rowIndex + 1; $r < $rowIndex + $rowspan; $r++) {
                for ($c = $colIndex; $c < $colIndex + $colspan; $c++) {
                    $mergedCells[$r][$c] = true;
                }
            }
        }

        // Add cell
        $cell = $phpWordTable->addCell(null, $cellStyle);

        // Get cell text - preserve line breaks
        $cellText = $this->getCellTextPreserveBreaks($cellNode);

        // Font style
        $fontStyle = ['size' => 10];
        if ($isHeader || $this->hasBoldContent($cellNode)) {
            $fontStyle['bold'] = true;
        }

        // Alignment
        $alignment = $this->getAlignmentFromNode($cellNode);

        // Handle multi-line cell content
        if (strpos($cellText, "\n") !== false) {
            $lines = explode("\n", $cellText);
            foreach ($lines as $idx => $line) {
                if (trim($line) === '') continue;

                $cell->addText(
                    trim($line),
                    $fontStyle,
                    ['alignment' => $alignment, 'spaceAfter' => $idx < count($lines) - 1 ? 80 : 0]
                );
            }
        } else {
            $cell->addText(
                $cellText,
                $fontStyle,
                ['alignment' => $alignment]
            );
        }
    }

    /**
     * ✅ Get cell text dengan preserve line breaks
     */
    private function getCellTextPreserveBreaks(\DOMElement $cellNode): string
    {
        $text = '';

        foreach ($cellNode->childNodes as $child) {
            if ($child->nodeName === '#text') {
                $text .= $child->textContent;
            } elseif ($child->nodeName === 'br') {
                $text .= "\n";
            } elseif ($child->nodeName === 'strong' || $child->nodeName === 'b') {
                $text .= $this->getCellTextPreserveBreaks($child);
            } elseif ($child->hasChildNodes()) {
                $text .= $this->getCellTextPreserveBreaks($child);
            }
        }

        return trim($text);
    }

    /**
     * ✅ Check apakah row adalah header
     */
    private function isHeaderRow(\DOMNode $row): bool
    {
        foreach ($row->childNodes as $cell) {
            if ($cell->nodeName === 'th') {
                return true;
            }

            if ($cell->nodeName === 'td') {
                foreach ($cell->childNodes as $child) {
                    if ($child->nodeName === 'strong' || $child->nodeName === 'b') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * ✅ Add cell ke PhpWord table
     */
    private function addHtmlTableCell($phpWordTable, \DOMElement $cellNode, bool $isHeader): void
    {
        $colspan = $cellNode->hasAttribute('colspan') ? (int)$cellNode->getAttribute('colspan') : 1;
        $rowspan = $cellNode->hasAttribute('rowspan') ? (int)$cellNode->getAttribute('rowspan') : 1;

        $cellStyle = [];
        if ($isHeader) {
            $cellStyle['bgColor'] = 'D3D3D3';
        }

        if ($colspan > 1) {
            $cellStyle['gridSpan'] = $colspan;
        }

        if ($rowspan > 1) {
            $cellStyle['vMerge'] = 'restart';
        }

        $cell = $phpWordTable->addCell(null, $cellStyle);

        $cellText = $this->getCellText($cellNode);

        $fontStyle = ['size' => 10];
        if ($isHeader || $this->hasBoldContent($cellNode)) {
            $fontStyle['bold'] = true;
        }

        $alignment = $this->getAlignmentFromNode($cellNode);

        $cell->addText(
            $cellText,
            $fontStyle,
            ['alignment' => $alignment]
        );
    }

    /**
     * ✅ Get text content dari cell
     */
    private function getCellText(\DOMElement $cellNode): string
    {
        $text = '';

        foreach ($cellNode->childNodes as $child) {
            if ($child->nodeName === '#text') {
                $text .= $child->textContent;
            } elseif ($child->nodeName === 'strong' || $child->nodeName === 'b') {
                $text .= $child->textContent;
            } elseif ($child->hasChildNodes()) {
                $text .= $this->getCellText($child);
            }
        }

        return trim($text);
    }

    /**
     * ✅ Check bold content
     */
    private function hasBoldContent(\DOMElement $cellNode): bool
    {
        foreach ($cellNode->childNodes as $child) {
            if ($child->nodeName === 'strong' || $child->nodeName === 'b' || $child->nodeName === 'th') {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ Get alignment dari HTML
     */
    private function getAlignmentFromNode(\DOMElement $node): string
    {
        if ($node->hasAttribute('style')) {
            $style = $node->getAttribute('style');
            if (strpos($style, 'text-align: center') !== false) {
                return Jc::CENTER;
            }
            if (strpos($style, 'text-align: right') !== false) {
                return Jc::END;
            }
            if (strpos($style, 'text-align: left') !== false) {
                return Jc::START;
            }
        }

        return Jc::CENTER;
    }

    /**
     * ✅ ENHANCED: Handle image dengan better error handling
     */
    private function handleHtmlImage($container, \DOMElement $imgNode): void
    {
        if (!$imgNode->hasAttribute('src')) {
            Log::warning("Image tag without src attribute");
            return;
        }

        $src = $imgNode->getAttribute('src');

        // ✅ Log panjang src untuk debug
        $srcLength = strlen($src);
        $srcPreview = substr($src, 0, 100);

        // Skip blob URLs
        if (strpos($src, 'blob:') === 0) {
            Log::warning("Skipping blob image: {$src}");
            $container->addText(
                '[Gambar tidak dapat dimuat - blob URL]',
                ['size' => 10, 'italic' => true, 'color' => 'FF0000'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
            );
            return;
        }

        // ✅ Handle data URI (base64)
        if (strpos($src, 'data:image') === 0) {
            try {
                $this->addDataUriImage($container, $src, $imgNode);
                // ✅ Add confirmation text (temporary for debug)
                $container->addText(
                    '[✓ Gambar berhasil ditambahkan]',
                    ['size' => 9, 'italic' => true, 'color' => '00FF00'],
                    ['alignment' => Jc::CENTER, 'spaceAfter' => 100]
                );
            } catch (\Exception $e) {
                Log::error("❌ Failed to process data URI image: " . $e->getMessage());
                Log::error("Stack trace: " . $e->getTraceAsString());

                $container->addText(
                    '[Gambar gagal dimuat: ' . $e->getMessage() . ']',
                    ['size' => 10, 'italic' => true, 'color' => 'FF0000'],
                    ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
                );
            }
            return;
        }

        // Handle HTTP/HTTPS
        if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0) {

            try {
                $tmpPath = $this->downloadImage($src);
                $container->addImage($tmpPath, [
                    'width' => 300,
                    'alignment' => Jc::CENTER
                ]);
            } catch (\Exception $e) {
                Log::error("❌ Failed to download image: " . $e->getMessage());
                $container->addText(
                    '[Gambar tidak dapat dimuat dari: ' . $src . ']',
                    ['size' => 10, 'italic' => true, 'color' => 'FF0000'],
                    ['alignment' => Jc::CENTER]
                );
            }
            return;
        }

        // Handle relative paths
        $imagePath = storage_path('app/public/' . ltrim($src, '/'));

        if (file_exists($imagePath)) {
            try {
                $container->addImage($imagePath, [
                    'width' => 300,
                    'alignment' => Jc::CENTER
                ]);
            } catch (\Exception $e) {
                Log::error("❌ Failed to add local image: " . $e->getMessage());
            }
        } else {
            Log::warning("❌ Image not found: {$imagePath}");
            $container->addText(
                '[Gambar tidak ditemukan: ' . basename($src) . ']',
                ['size' => 10, 'italic' => true, 'color' => 'FF0000'],
                ['alignment' => Jc::CENTER]
            );
        }
    }

    /**
     * ✅ ENHANCED: Add data URI image dengan better validation
     */
    private function addDataUriImage($container, string $dataUri, \DOMElement $imgNode): void
    {
        // ✅ Parse data URI dengan better regex
        $pattern = '/^data:image\/(\w+);base64,(.+)$/s';

        if (!preg_match($pattern, $dataUri, $matches)) {
            Log::error("Invalid data URI format - Pattern match failed");
            throw new \Exception("Invalid data URI format");
        }

        $extension = strtolower($matches[1]);
        $base64Data = $matches[2];

        // Validate extension
        $allowedExt = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'];
        if (!in_array($extension, $allowedExt)) {
            Log::error("Unsupported format: {$extension}");
            throw new \Exception("Unsupported image format: {$extension}");
        }

        // ✅ Clean base64 data (remove whitespace)
        $base64Data = preg_replace('/\s+/', '', $base64Data);

        // Decode base64
        $imageData = base64_decode($base64Data, true);
        if ($imageData === false) {
            Log::error("Base64 decode failed");
            throw new \Exception("Failed to decode base64 data");
        }

        $decodedSize = strlen($imageData);

        // ✅ Validate minimum size
        if ($decodedSize < 100) {
            Log::error("Image data too small: {$decodedSize} bytes");
            throw new \Exception("Invalid image data (too small)");
        }

        // Create temp directory
        $tmpDir = $this->tmpExportPath();
        if (!is_dir($tmpDir)) {
            if (!mkdir($tmpDir, 0777, true)) {
                Log::error("Failed to create temp directory: {$tmpDir}");
                throw new \Exception("Cannot create temp directory");
            }
        }

        // ✅ Save to temp file
        $tmpFile = $tmpDir . '/' . uniqid('img_', true) . '.' . $extension;

        $written = file_put_contents($tmpFile, $imageData);
        if ($written === false) {
            Log::error("Failed to write file: {$tmpFile}");
            throw new \Exception("Failed to write image file");
        }

        // ✅ Verify file
        if (!file_exists($tmpFile)) {
            Log::error("Temp file not found after creation");
            throw new \Exception("Temp file not found");
        }

        if (!is_readable($tmpFile)) {
            Log::error("Temp file not readable");
            throw new \Exception("Temp file not readable");
        }

        // ✅ Validate image
        $imageInfo = @getimagesize($tmpFile);
        if ($imageInfo === false) {
            Log::warning("getimagesize failed - file might be corrupted");

            // Try to use anyway with default dimensions
            $width = 300;
        } else {
            $origWidth = $imageInfo[0];
            $origHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            // Calculate width
            $htmlWidth = $imgNode->hasAttribute('width') ? (int)$imgNode->getAttribute('width') : null;

            if ($htmlWidth) {
                $width = $htmlWidth;
            } elseif ($origWidth > 500) {
                $width = 500;
            } else {
                $width = $origWidth;
            }
        }

        // ✅ Add to container
        try {
            $container->addImage($tmpFile, [
                'width' => $width,
                'height' => null, // Auto proportion
                'alignment' => Jc::CENTER,
                'wrappingStyle' => 'inline'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to add image to PhpWord: " . $e->getMessage());
            throw new \Exception("Failed to add image to document: " . $e->getMessage());
        }

        // Track for cleanup
        $this->tmpImages[] = $tmpFile;
    }

    /**
     * ✅ Extract all images dari HTML dengan regex
     */
    private function extractImagesWithRegex(string $html): array
    {
        $images = [];

        // Pattern untuk extract img tags (including dalam conditional comments)
        $pattern = '/<img\s+([^>]+)>/is';

        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attributes = $match[1];

            // Extract src
            if (preg_match('/src=["\']([^"\']+)["\']/i', $attributes, $srcMatch)) {
                $src = $srcMatch[1];

                // Extract width (optional)
                $width = null;
                if (preg_match('/width=["\']?(\d+)["\']/i', $attributes, $widthMatch)) {
                    $width = (int)$widthMatch[1];
                }

                // Extract height (optional)
                $height = null;
                if (preg_match('/height=["\']?(\d+)["\']/i', $attributes, $heightMatch)) {
                    $height = (int)$heightMatch[1];
                }

                $images[] = [
                    'src' => $src,
                    'width' => $width,
                    'height' => $height
                ];
            }
        }

        return $images;
    }

    /**
     * ✅ Add image dari src string
     */
    private function addImageFromSrc($container, string $src, ?int $width, ?int $height): void
    {
        // Handle data URI
        if (strpos($src, 'data:image') === 0) {
            // Parse manually
            if (!preg_match('/^data:image\/(\w+);base64,(.+)$/s', $src, $matches)) {
                throw new \Exception("Invalid data URI format");
            }

            $extension = strtolower($matches[1]);
            $base64Data = $matches[2];

            // ✅ Clean whitespace dari base64
            $base64Data = preg_replace('/\s+/', '', $base64Data);

            $imageData = base64_decode($base64Data, true);
            if ($imageData === false) {
                throw new \Exception("Failed to decode base64");
            }

            // Save to temp
            $tmpDir = $this->tmpExportPath();
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }

            $tmpFile = $tmpDir . '/' . uniqid('img_', true) . '.' . $extension;
            file_put_contents($tmpFile, $imageData);

            // Get dimensions
            $imageInfo = @getimagesize($tmpFile);
            if ($imageInfo !== false) {
                $origWidth = $imageInfo[0];
                $origHeight = $imageInfo[1];

                // Calculate display width
                if ($width) {
                    $displayWidth = $width;
                } elseif ($origWidth > 500) {
                    $displayWidth = 500;
                } else {
                    $displayWidth = $origWidth;
                }
            } else {
                $displayWidth = $width ?: 300;
            }

            // Add to container
            $container->addImage($tmpFile, [
                'width' => $displayWidth,
                'alignment' => Jc::CENTER
            ]);

            $this->tmpImages[] = $tmpFile;
        }
        // Handle HTTP/local paths (existing logic)
        else {
            $this->handleImageUrl($container, $src);
        }
    }

    /**
     * ✅ Handle HTTP/local image URLs
     */
    private function handleImageUrl($container, string $url): void
    {
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            $tmpPath = $this->downloadImage($url);
            $container->addImage($tmpPath, [
                'width' => 300,
                'alignment' => Jc::CENTER
            ]);
        } else {
            $imagePath = storage_path('app/public/' . ltrim($url, '/'));
            if (file_exists($imagePath)) {
                $container->addImage($imagePath, [
                    'width' => 300,
                    'alignment' => Jc::CENTER
                ]);
            } else {
                throw new \Exception("Image not found: {$imagePath}");
            }
        }
    }

    /**
     * ✅ Download image dari URL
     */
    private function downloadImage(string $url): string
    {
        $tmpFile = $this->tmpExportPath(uniqid('img_', true) . '.jpg');

        $imageData = @file_get_contents($url);
        if ($imageData === false) {
            throw new \Exception("Failed to download image");
        }

        file_put_contents($tmpFile, $imageData);

        $this->tmpImages[] = $tmpFile;

        return $tmpFile;
    }

    /**
     * ✅ Get text dari node recursively
     */
    private function getNodeText(\DOMNode $node): string
    {
        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeName === '#text') {
                $text .= $child->textContent;
            } elseif ($child->hasChildNodes()) {
                $text .= $this->getNodeText($child);
            }
        }
        return $text;
    }

    /**
     * ✅ Sanitize HTML
     */
    private function sanitizeHtml(string $html): string
    {
        $html = str_replace("\0", '', $html);
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = str_replace('&nbsp;', ' ', $html);
        $html = preg_replace('/\s+/', ' ', $html);

        return trim($html);
    }

    /**
     * ✅ Chunk HTML by tags
     */
    private function chunkHtml(string $html, int $maxChars = 5000): array
    {
        $parts = preg_split('/(<\/?p[^>]*>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        $chunks = [];
        $currentChunk = '';

        foreach ($parts as $part) {
            if (strlen($currentChunk . $part) > $maxChars && !empty($currentChunk)) {
                $chunks[] = $currentChunk;
                $currentChunk = $part;
            } else {
                $currentChunk .= $part;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks ?: [$html];
    }

    /**
     * ✅ Ensure numbering style registered
     */
    private function ensureNumberingStyle(string $name, array $definition): void
    {
        if (isset($this->numberingRegistered[$name])) {
            return;
        }

        if ($this->phpWord) {
            $this->phpWord->addNumberingStyle($name, $definition);
        }

        $this->numberingRegistered[$name] = true;
    }

    /**
     * ✅ Cleanup temporary images
     */
    public function cleanup(): void
    {
        foreach ($this->tmpImages as $tmpImage) {
            if (file_exists($tmpImage)) {
                @unlink($tmpImage);
            }
        }

        $tmpImgDir = storage_path('app/tmp_images');
        if (is_dir($tmpImgDir)) {
            foreach (glob($tmpImgDir . '/*') as $f) {
                @unlink($f);
            }
        }
    }

    /**
     * ✅ Get temporary images list (untuk external cleanup)
     */
    public function getTmpImages(): array
    {
        return $this->tmpImages;
    }

    private function tmpExportPath(string $filename = ''): string
    {
        $basePath = storage_path(
            'app/public/tmp_pdf_export/' . $this->pengajuanId
        );

        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        return $filename
            ? $basePath . DIRECTORY_SEPARATOR . $filename
            : $basePath;
    }
}
