<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\SimpleType\Jc;

class HtmlToPhpWordParser
{
    protected array $tmpImages = [];
    protected array $pendingImages = [];
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
        $this->pendingImages = $images;

        // Remove images from HTML temporarily
        $htmlWithoutImages = preg_replace('/<img\b[^>]*\/?>/i', '<phpword-image-placeholder></phpword-image-placeholder>', $html);

        // Parse HTML without images
        if (str_contains($htmlWithoutImages, 'phpword-image-placeholder')) {
            $this->parseAndAddHtml($container, $htmlWithoutImages);
        } else {
            if (strlen($htmlWithoutImages) > 10000) {
                $chunks = $this->chunkHtml($htmlWithoutImages, 5000);
                foreach ($chunks as $chunk) {
                    $this->parseAndAddHtml($container, $chunk);
                }
            } else {
                $this->parseAndAddHtml($container, $htmlWithoutImages);
            }
        }

        // ✅ Add images at the end (or wherever placeholder is)
        // foreach ($images as $imgData) {
        //     try {
        //         $this->addImageFromSrc($container, $imgData['src'], $imgData['width'], $imgData['height']);
        //     } catch (\Exception $e) {
        //         Log::error("Failed to add extracted image: " . $e->getMessage());
        //         $container->addText(
        //             '[Gambar tidak dapat dimuat]',
        //             ['size' => 10, 'italic' => true, 'color' => 'FF0000'],
        //             ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        //         );
        //     }
        // }
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
                    // kalau p mengandung image/placeholder, jangan pakai addHtmlParagraph biasa
                    if ($this->nodeHasImageOrPlaceholder($child)) {
                        $this->addParagraphWithImages($container, $child);
                    } else {
                        $this->addHtmlParagraph($container, $child);
                    }
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
                    // $this->handleHtmlImage($container, $child);
                    break;
                case 'phpword-image-placeholder':
                    $imgData = array_shift($this->pendingImages);
                    if ($imgData) {
                        $this->addImageFromSrc($container, $imgData['src'], $imgData['width'], $imgData['height'], $child->parentNode);
                    }
                    break;
                // case '#comment':
                //     $comment = trim($child->textContent);
                //     if ($comment === 'IMAGE_PLACEHOLDER') {
                //         $imgData = array_shift($this->pendingImages); // ambil gambar berikutnya
                //         if ($imgData) {
                //             $this->addImageFromSrc(
                //                 $container,
                //                 $imgData['src'],
                //                 $imgData['width'],
                //                 $imgData['height'],
                //                 $child->parentNode // supaya bisa baca align dari <p> parent
                //             );
                //         }
                //     }
                //     break;

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

    private function nodeHasImageOrPlaceholder(\DOMNode $node): bool
    {
        foreach ($node->childNodes as $ch) {
            $name = strtolower($ch->nodeName);
            if ($name === 'img' || $name === 'phpword-image-placeholder') return true;
            if ($ch->hasChildNodes() && $this->nodeHasImageOrPlaceholder($ch)) return true;
        }
        return false;
    }

    /**
     * ✅ Add HTML paragraph dengan inline formatting
     */
    private function addHtmlParagraph($container, \DOMNode $pNode): void
    {
        $css = [];
        if ($pNode instanceof \DOMElement && $pNode->hasAttribute('style')) {
            $css = $this->parseCss($pNode->getAttribute('style'));
        }

        $pStyle = $this->cssToParagraphStyle($css);

        $textRun = $container->addTextRun($pStyle + ['spaceAfter' => ($pStyle['spaceAfter'] ?? 200)]);
        $this->addInlineHtmlContent($textRun, $pNode, ['size' => 11], $css);
    }

    /**
     * ✅ Add inline HTML content (bold, italic, etc)
     */
    private function addInlineHtmlContent($textRun, \DOMNode $node, array $fontStyle = ['size' => 11], array $inheritedCss = []): void
    {
        foreach ($node->childNodes as $child) {
            $childCss = $inheritedCss;

            if ($child instanceof \DOMElement && $child->hasAttribute('style')) {
                $childCss = array_merge($childCss, $this->parseCss($child->getAttribute('style')));
            }

            $currentFont = $this->cssToFontStyle($childCss, $fontStyle);

            switch (strtolower($child->nodeName)) {
                case 'strong':
                case 'b':
                    $currentFont['bold'] = true;
                    $this->addInlineHtmlContent($textRun, $child, $currentFont, $childCss);
                    break;

                case 'em':
                case 'i':
                    $currentFont['italic'] = true;
                    $this->addInlineHtmlContent($textRun, $child, $currentFont, $childCss);
                    break;

                case 'u':
                    $currentFont['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
                    $this->addInlineHtmlContent($textRun, $child, $currentFont, $childCss);
                    break;

                case 's':
                case 'strike':
                case 'del':
                    $currentFont['strikethrough'] = true;
                    $this->addInlineHtmlContent($textRun, $child, $currentFont, $childCss);
                    break;

                case 'span':
                    $this->addInlineHtmlContent($textRun, $child, $currentFont, $childCss);
                    break;

                case 'br':
                    $textRun->addTextBreak(1);
                    break;

                case '#text':
                    $t = $child->textContent;
                    if (trim($t) !== '') $textRun->addText($t, $currentFont);
                    break;

                case 'img':
                    // kalau masih ada <img> lolos (misal conditional), bisa fallback:
                    // $this->handleHtmlImage($textRun->getParent(), $child);
                    break;

                default:
                    if ($child->hasChildNodes()) {
                        $this->addInlineHtmlContent($textRun, $child, $currentFont, $childCss);
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
        $isOrdered = strtolower($listNode->nodeName) === 'ol';

        // list-style-type dari style HTML (decimal/lower-alpha/disk/circle/square/upper-roman etc)
        $marker = 'decimal';
        if ($listNode instanceof \DOMElement && $listNode->hasAttribute('style')) {
            $css = $this->parseCss($listNode->getAttribute('style'));
            if (!empty($css['list-style-type'])) $marker = strtolower($css['list-style-type']);
        }

        $format = $isOrdered ? 'decimal' : 'bullet';
        if ($isOrdered) {
            $map = [
                'decimal' => 'decimal',
                'lower-alpha' => 'lowerLetter',
                'upper-alpha' => 'upperLetter',
                'lower-roman' => 'lowerRoman',
                'upper-roman' => 'upperRoman',
                'decimal-leading-zero' => 'decimalZero',
            ];
            if (isset($map[$marker])) $format = $map[$marker];
        } else {
            $format = 'bullet';
        }

        $numberingName = 'htmlList_' . uniqid();
        $this->ensureNumberingStyle($numberingName, [
            'type' => 'multilevel',
            'levels' => [[
                'level'   => 0,
                'format'  => $format,
                'text'    => $format === 'bullet' ? '•' : '%1.',
                'left'    => 360,
                'hanging' => 360,
                'tabPos'  => 720,
            ]]
        ]);

        foreach ($listNode->childNodes as $li) {
            if (strtolower($li->nodeName) !== 'li') continue;

            // pakai ListItemRun supaya inline formatting kebawa
            $run = $container->addListItemRun(0, $numberingName, ['spaceAfter' => 120]);

            // render isi li (text + span + strong + u + s + br)
            $this->addInlineHtmlContent($run, $li, ['size' => 11], []);

            // nested list di dalam li
            foreach ($li->childNodes as $maybeNested) {
                $n = strtolower($maybeNested->nodeName);
                if ($n === 'ol' || $n === 'ul') {
                    $this->addHtmlList($container, $maybeNested);
                }
            }
        }
    }

    /**
     * ✅ SIMPLEST: dengan fixed twips width
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

        return Jc::START;
    }

    /**
     * ✅ ENHANCED: Handle image dengan better error handling
     */
    private function handleHtmlImage($container, \DOMElement $imgNode): void
    {
        $src = $imgNode->getAttribute('src');
        $localPath = $this->resolveImageToLocalPath($src);

        if (!$localPath) {
            $container->addText(
                '[Gambar tidak dapat dimuat]',
                ['size' => 10, 'italic' => true, 'color' => 'FF0000'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
            );
            return;
        }

        // width (optional)
        $width = $imgNode->hasAttribute('width') ? (int)$imgNode->getAttribute('width') : 300;

        $container->addImage($localPath, [
            'width' => $width > 0 ? $width : 300,
            'alignment' => Jc::CENTER
        ]);
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

        // lebih robust: dukung <img ...> dan <img .../>
        $pattern = '/<img\b([^>]*?)\/?>/is';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attributes = $match[1] ?? '';

            if (!preg_match('/src=["\']([^"\']+)["\']/i', $attributes, $srcMatch)) {
                continue;
            }

            $src = $srcMatch[1];

            $width = null;
            $height = null;

            if (preg_match('/\bwidth=["\']?(\d+)["\']?/i', $attributes, $m)) {
                $width = (int)$m[1];
            }
            if (preg_match('/\bheight=["\']?(\d+)["\']?/i', $attributes, $m)) {
                $height = (int)$m[1];
            }

            // fallback dari style (px)
            if ($width === null && preg_match('/style=["\'][^"\']*?\bwidth\s*:\s*(\d+)px\s*;?/i', $attributes, $m)) {
                $width = (int)$m[1];
            }
            if ($height === null && preg_match('/style=["\'][^"\']*?\bheight\s*:\s*(\d+)px\s*;?/i', $attributes, $m)) {
                $height = (int)$m[1];
            }

            // simpan style utk alignment/margin auto
            $style = null;
            if (preg_match('/style=["\']([^"\']+)["\']/i', $attributes, $m)) {
                $style = $m[1];
            }

            $images[] = [
                'src' => $src,
                'width' => $width,
                'height' => $height,
                'style' => $style,
            ];
        }

        return $images;
    }

    /**
     * ✅ Add image dari src string
     */
    private function addImageFromSrc($container, string $src, ?int $width, ?int $height, ?\DOMNode $parentNode = null): void
    {
        $localPath = $this->resolveImageToLocalPath($src);
        if (!$localPath) throw new \Exception("Cannot resolve image src: {$src}");

        // Tentukan alignment dari parent <p> (center/left/right)
        $alignment = Jc::CENTER;
        if ($parentNode instanceof \DOMElement && $parentNode->hasAttribute('style')) {
            $style = $parentNode->getAttribute('style');
            if (stripos($style, 'text-align: right') !== false) $alignment = Jc::END;
            elseif (stripos($style, 'text-align: left') !== false) $alignment = Jc::START;
            elseif (stripos($style, 'text-align: center') !== false) $alignment = Jc::CENTER;
            elseif (stripos($style, 'text-align: justify') !== false) $alignment = Jc::CENTER; // gambar biasanya center
        }

        // Kalau width/height tidak ada, ambil dari file asli
        if (!$width || !$height) {
            $info = @getimagesize($localPath);
            if ($info) {
                $width = $width ?: $info[0];
                $height = $height ?: $info[1];
            }
        }

        // Batasi agar tidak lebih lebar dari halaman (sesuaikan angka ini)
        // Biasanya aman: 520-560 px untuk A4 margin normal
        $maxWidth = 520;
        if ($width && $width > $maxWidth) {
            $ratio = $height && $width ? ($height / $width) : null;
            $width = $maxWidth;
            if ($ratio) $height = (int) round($maxWidth * $ratio);
        }

        $opts = [
            'alignment' => $alignment,
            'wrappingStyle' => 'inline',
        ];
        if ($width) $opts['width'] = $width;
        if ($height) $opts['height'] = $height;

        $container->addImage($localPath, $opts);
    }

    private function resolveImageSize(string $localPath, ?int $w, ?int $h, ?int $maxW, bool $heightAuto, int $fallbackMaxW): array
    {
        $info = @getimagesize($localPath);
        $origW = $info ? (int)$info[0] : null;
        $origH = $info ? (int)$info[1] : null;

        // Jika tidak ada info, pakai fallback aman
        if (!$origW || !$origH) {
            $w = $w ?: min($fallbackMaxW, 520);
            return [$w, null];
        }

        // Interpret "max-width:100%" sebagai fit container => pakai fallbackMaxW
        $effectiveMaxW = $fallbackMaxW;
        if ($maxW !== null) {
            $effectiveMaxW = ($maxW >= 99999) ? $fallbackMaxW : min($fallbackMaxW, $maxW);
        }

        // Jika hanya height ada (dan width kosong) -> hitung width dari ratio
        if (!$w && $h) {
            $w = (int) round(($h * $origW) / $origH);
        }

        // Jika width ada, height auto atau kosong -> hitung height dari ratio
        if ($w && (!$h || $heightAuto)) {
            $h = (int) round(($w * $origH) / $origW);
        }

        // Jika keduanya kosong -> pakai original
        if (!$w && !$h) {
            $w = $origW;
            $h = $origH;
        }

        // Fit to max width (scale down, keep ratio)
        if ($w > $effectiveMaxW) {
            $scale = $effectiveMaxW / $w;
            $w = (int) floor($w * $scale);
            $h = $h ? (int) floor($h * $scale) : null;
        }

        // Hard guard biar nggak terlalu kecil / aneh
        if ($w < 50) $w = 50;

        return [$w, $h];
    }

    /**
     * ✅ Handle HTTP/local image URLs
     */
    private function handleImageUrl($container, string $url): void
    {
        // 1) Kalau bentuknya URL http(s), cek dulu apakah itu /storage/...
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {

            $path = parse_url($url, PHP_URL_PATH) ?: '';

            // contoh path: /storage/permohonan-akreditasi/1/xxx.png
            if (strpos($path, '/storage/') === 0) {
                $relative = ltrim(substr($path, strlen('/storage/')), '/');

                $local = storage_path('app/public/' . $relative);
                if (file_exists($local)) {
                    $container->addImage($local, [
                        'width' => 300,
                        'alignment' => Jc::CENTER
                    ]);
                    return;
                }

                // fallback kalau file benerannya ada di public/storage
                $local2 = public_path('storage/' . $relative);
                if (file_exists($local2)) {
                    $container->addImage($local2, [
                        'width' => 300,
                        'alignment' => Jc::CENTER
                    ]);
                    return;
                }

                throw new \Exception("Local storage image not found for URL: {$url}");
            }

            // 2) Kalau bukan /storage/..., baru download
            $tmpPath = $this->downloadImage($url);
            $container->addImage($tmpPath, [
                'width' => 300,
                'alignment' => Jc::CENTER
            ]);
            return;
        }

        // 3) Kalau bukan URL (relatif), existing logic
        $imagePath = storage_path('app/public/' . ltrim($url, '/'));
        if (file_exists($imagePath)) {
            $container->addImage($imagePath, [
                'width' => 300,
                'alignment' => Jc::CENTER
            ]);
            return;
        }

        throw new \Exception("Image not found: {$imagePath}");
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

    private function resolveImageToLocalPath(string $src): ?string
    {
        $src = trim($src);

        // 1) blob: tidak bisa dari backend
        if (Str::startsWith($src, 'blob:')) {
            return null;
        }

        // 2) data URI (base64) -> simpan temp
        if (Str::startsWith($src, 'data:image')) {
            return $this->saveDataUriToTemp($src);
        }

        // 3) URL http(s)
        if (preg_match('#^https?://#i', $src)) {
            $path = parse_url($src, PHP_URL_PATH) ?: '';

            // 3a) Kalau ini URL /storage/... milik aplikasi sendiri -> map ke file lokal
            if (Str::startsWith($path, '/storage/')) {
                $relative = Str::after($path, '/storage/');
                $local = storage_path('app/public/' . $relative);
                if (file_exists($local)) return $local;

                $local2 = public_path('storage/' . $relative);
                if (file_exists($local2)) return $local2;

                return null;
            }

            // 3b) URL remote lain -> download ke temp (lebih aman dari file_get_contents)
            return $this->downloadRemoteImageToTemp($src);
        }

        // 4) src relatif: /storage/.. atau path lokal
        if (Str::startsWith($src, '/storage/')) {
            $relative = Str::after($src, '/storage/');
            $local = storage_path('app/public/' . $relative);
            if (file_exists($local)) return $local;

            $local2 = public_path('storage/' . $relative);
            if (file_exists($local2)) return $local2;

            return null;
        }

        // 5) relative biasa: permohonan-akreditasi/...
        $local = storage_path('app/public/' . ltrim($src, '/'));
        if (file_exists($local)) return $local;

        // 6) kalau ternyata sudah absolute path
        if (file_exists($src)) return $src;

        return null;
    }

    private function saveDataUriToTemp(string $dataUri): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,(.+)$/s', $dataUri, $m)) {
            return null;
        }

        $ext = strtolower($m[1]);
        $base64 = preg_replace('/\s+/', '', $m[2]);
        $bin = base64_decode($base64, true);
        if ($bin === false) return null;

        $tmpDir = $this->tmpExportPath();
        $tmpFile = $tmpDir . '/' . uniqid('img_', true) . '.' . $ext;

        file_put_contents($tmpFile, $bin);
        $this->tmpImages[] = $tmpFile;

        return file_exists($tmpFile) ? $tmpFile : null;
    }

    private function downloadRemoteImageToTemp(string $url): ?string
    {
        try {
            $resp = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            if (!$resp->ok()) return null;

            // Batasi ukuran (misal 8MB)
            $body = $resp->body();
            if (strlen($body) > 8 * 1024 * 1024) return null;

            $ct = strtolower($resp->header('Content-Type', ''));
            $ext = 'img';
            if (str_contains($ct, 'jpeg')) $ext = 'jpg';
            elseif (str_contains($ct, 'png')) $ext = 'png';
            elseif (str_contains($ct, 'webp')) $ext = 'webp';
            elseif (str_contains($ct, 'gif')) $ext = 'gif';

            // fallback: coba dari URL
            if ($ext === 'img') {
                $path = parse_url($url, PHP_URL_PATH) ?: '';
                $guess = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($guess, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    $ext = $guess === 'jpeg' ? 'jpg' : $guess;
                }
            }

            $tmpFile = $this->tmpExportPath(uniqid('img_', true) . '.' . $ext);
            file_put_contents($tmpFile, $body);

            // Validasi beneran gambar
            if (@getimagesize($tmpFile) === false) {
                @unlink($tmpFile);
                return null;
            }

            $this->tmpImages[] = $tmpFile;
            return $tmpFile;
        } catch (\Throwable $e) {
            Log::warning("downloadRemoteImageToTemp failed: {$e->getMessage()}");
            return null;
        }
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
        // $html = preg_replace('/\s+/', ' ', $html);

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
            'app/tmp_pdf_export/' . $this->pengajuanId
        );

        if (!is_dir($basePath)) {
            if (!mkdir($basePath, 0775, true) && !is_dir($basePath)) {
                throw new \RuntimeException("Gagal membuat direktori temporary export: {$basePath}");
            }
        }

        if (!is_writable($basePath)) {
            throw new \RuntimeException("Direktori temporary export tidak writable: {$basePath}");
        }

        return $filename
            ? $basePath . DIRECTORY_SEPARATOR . $filename
            : $basePath;
    }

    private function parseCss(string $style): array
    {
        $out = [];
        foreach (explode(';', $style) as $decl) {
            $decl = trim($decl);
            if ($decl === '' || !str_contains($decl, ':')) continue;
            [$k, $v] = array_map('trim', explode(':', $decl, 2));
            $out[strtolower($k)] = $v;
        }
        return $out;
    }

    private function cssColorToHex(?string $v): ?string
    {
        if (!$v) return null;
        $v = trim($v);
        if ($v === '') return null;

        // #RRGGBB / #RGB
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $v, $m)) {
            $hex = strtoupper($m[1]);
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            return $hex;
        }

        // rgb(r,g,b)
        if (preg_match('/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $v, $m)) {
            $r = max(0, min(255, (int)$m[1]));
            $g = max(0, min(255, (int)$m[2]));
            $b = max(0, min(255, (int)$m[3]));
            return sprintf('%02X%02X%02X', $r, $g, $b);
        }

        return null;
    }

    private function cssToParagraphStyle(array $css): array
    {
        $p = [];

        // alignment
        $ta = strtolower($css['text-align'] ?? '');
        $map = [
            'left' => Jc::START,
            'right' => Jc::END,
            'center' => Jc::CENTER,
            'justify' => Jc::BOTH,
        ];
        if (isset($map[$ta])) $p['alignment'] = $map[$ta];

        // margin-top/bottom px -> twips-ish (PhpWord expects "spaceBefore/After" in twips)
        // 1px ~ 20 twips (kamu pakai mapping ini juga di import)
        foreach (['margin-top' => 'spaceBefore', 'margin-bottom' => 'spaceAfter'] as $k => $dest) {
            if (!empty($css[$k]) && preg_match('/(\d+)\s*px/i', $css[$k], $m)) {
                $p[$dest] = (int)$m[1] * 20;
            }
        }

        // line-height: "1.5" atau "24px"
        if (!empty($css['line-height'])) {
            $lh = trim($css['line-height']);
            if (preg_match('/^\d+(\.\d+)?$/', $lh)) {
                // multiplier (PhpWord supports 'lineHeight' multiplier)
                $p['lineHeight'] = (float)$lh;
            } elseif (preg_match('/(\d+)\s*px/i', $lh, $m)) {
                // px -> twips, pakai spacing + rule "exact"
                $p['spacing'] = (int)$m[1] * 20;
                $p['spacingLineRule'] = \PhpOffice\PhpWord\SimpleType\LineSpacingRule::EXACT;
            }
        }

        // indent (margin-left / text-indent)
        // PhpWord indentation uses twips, 1px ~ 15 twips (mengikuti import kamu)
        $indent = [];
        if (!empty($css['margin-left']) && preg_match('/(\d+)\s*px/i', $css['margin-left'], $m)) {
            $indent['left'] = (int)$m[1] * 15;
        }
        if (!empty($css['margin-right']) && preg_match('/(\d+)\s*px/i', $css['margin-right'], $m)) {
            $indent['right'] = (int)$m[1] * 15;
        }
        if (!empty($css['text-indent'])) {
            if (preg_match('/-?(\d+)\s*px/i', $css['text-indent'], $m)) {
                $px = (int)$m[1];
                // negative indent => hanging, positive => firstLine
                if (str_starts_with(trim($css['text-indent']), '-')) $indent['hanging'] = $px * 15;
                else $indent['firstLine'] = $px * 15;
            }
        }
        if (!empty($indent)) $p['indentation'] = $indent;

        return $p;
    }

    private function cssToFontStyle(array $css, array $base = []): array
    {
        $f = $base;

        // color
        $hex = $this->cssColorToHex($css['color'] ?? null);
        if ($hex) $f['color'] = $hex;

        // background-color => highlight-ish (PhpWord: 'bgColor' on FontStyle works in some writers)
        $bg = $this->cssColorToHex($css['background-color'] ?? null);
        if ($bg) $f['bgColor'] = $bg;

        // font-size: pt atau px
        if (!empty($css['font-size'])) {
            $v = strtolower(trim($css['font-size']));
            if (preg_match('/(\d+(\.\d+)?)\s*pt/', $v, $m)) $f['size'] = (float)$m[1];
            elseif (preg_match('/(\d+)\s*px/', $v, $m)) {
                // px ~ pt*(96/72) => pt = px*0.75
                $f['size'] = round(((int)$m[1]) * 0.75, 1);
            }
        }

        // text-decoration
        if (!empty($css['text-decoration'])) {
            $td = strtolower($css['text-decoration']);
            if (str_contains($td, 'underline')) $f['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
            if (str_contains($td, 'line-through')) $f['strikethrough'] = true;
        }

        // font-weight/font-style
        if (!empty($css['font-weight']) && (str_contains($css['font-weight'], 'bold') || (int)$css['font-weight'] >= 600)) $f['bold'] = true;
        if (!empty($css['font-style']) && str_contains(strtolower($css['font-style']), 'italic')) $f['italic'] = true;

        // font-family (opsional)
        if (!empty($css['font-family'])) {
            // ambil font pertama aja
            $name = trim(explode(',', $css['font-family'])[0], " \t\n\r\0\x0B\"'");
            if ($name !== '') $f['name'] = $name;
        }

        return $f;
    }

    private function addParagraphWithImages($container, \DOMNode $pNode): void
    {
        $css = [];
        if ($pNode instanceof \DOMElement && $pNode->hasAttribute('style')) {
            $css = $this->parseCss($pNode->getAttribute('style'));
        }
        $pStyle = $this->cssToParagraphStyle($css);
        $pStyle = $pStyle + ['spaceAfter' => ($pStyle['spaceAfter'] ?? 200)];

        // kasus paling umum: <p><img ...></p> (atau placeholder)
        // kalau hanya berisi img/placeholder (dan whitespace), langsung addImage saja
        $onlyImage = true;
        foreach ($pNode->childNodes as $ch) {
            $name = strtolower($ch->nodeName);
            if ($name === '#text' && trim($ch->textContent) === '') continue;
            if ($name === 'img' || $name === 'phpword-image-placeholder' || $name === 'span' || $name === 'div') {
                // masih mungkin berisi image di dalam span/div
            } else {
                $onlyImage = false;
            }
        }

        // buat textRun aktif untuk teks (kalau ada)
        $textRun = null;
        $ensureRun = function () use ($container, &$textRun, $pStyle, $css) {
            if (!$textRun) $textRun = $container->addTextRun($pStyle);
            return $textRun;
        };

        // recursive walker untuk isi <p>
        $walk = function (\DOMNode $node) use (&$walk, $container, $pNode, &$textRun, $ensureRun, $css) {
            foreach ($node->childNodes as $ch) {
                $name = strtolower($ch->nodeName);

                if ($name === 'phpword-image-placeholder') {
                    $imgData = array_shift($this->pendingImages);
                    if ($imgData) {
                        // close run (biar gambar tidak “inline” ke textRun)
                        $textRun = null;
                        $this->addImageFromSrc($container, $imgData['src'], $imgData['width'], $imgData['height'], $pNode);
                    }
                    continue;
                }

                if ($name === 'img') {
                    // fallback kalau ada <img> yang lolos tanpa placeholder
                    $src = $ch instanceof \DOMElement ? $ch->getAttribute('src') : null;
                    if ($src) {
                        $w = $ch->hasAttribute('width') ? (int)$ch->getAttribute('width') : null;
                        $h = $ch->hasAttribute('height') ? (int)$ch->getAttribute('height') : null;
                        $textRun = null;
                        $this->addImageFromSrc($container, $src, $w, $h, $pNode);
                    }
                    continue;
                }

                if ($name === '#text') {
                    $t = $ch->textContent;
                    if (trim($t) !== '') {
                        $run = $ensureRun();
                        $run->addText($t, ['size' => 11]); // bisa pakai cssToFontStyle kalau mau konsisten
                    }
                    continue;
                }

                // span/div/strong/em dst: recurse
                if ($ch->hasChildNodes()) {
                    $walk($ch);
                }
            }
        };

        $walk($pNode);

        // kalau p hanya gambar, boleh kasih spaceAfter via paragraph lain / atau biarkan
    }
}
