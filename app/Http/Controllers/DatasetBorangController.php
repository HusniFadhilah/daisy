<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatasetBorang;

class DatasetBorangController extends Controller
{
    public function index(Request $request)
    {
        // optional filter/search
        $q = trim((string) $request->get('q', ''));

        $datasets = DatasetBorang::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%");
            })
            ->get();

        $items = [];

        foreach ($datasets as $dataset) {
            $expected = $dataset->expected_columns ?? [];
            if (!is_array($expected)) {
                $decoded = json_decode($expected, true);
                $expected = is_array($decoded) ? $decoded : [];
            }
            $expectedCount = count($expected);

            $html = $this->extractHtmlFromDataset($dataset);

            $hasColspan = $html ? (bool) preg_match('/colspan\s*=\s*["\']?\d+/i', $html) : false;
            $hasRowspan = $html ? (bool) preg_match('/rowspan\s*=\s*["\']?\d+/i', $html) : false;

            $analysis = [
                'expected_columns' => $expected,
                'expected_count'   => $expectedCount,
                'has_colspan'      => $hasColspan,
                'has_rowspan'      => $hasRowspan,
                'rows'             => [],
                'mismatch_rows'    => 0,
            ];

            $normalizedTable = null;

            if ($html && stripos($html, '<tr') !== false) {
                [$rows, $normalizedTable, $mismatchCount] = $this->analyzeHtmlTable($html, $expectedCount);
                $analysis['rows'] = $rows;
                $analysis['mismatch_rows'] = $mismatchCount;
            }

            $items[] = [
                'dataset'         => $dataset,
                'html'            => $html,
                'analysis'        => $analysis,
                'normalizedTable' => $normalizedTable,
            ];
        }

        return view('debug.dataset-borang', [
            'items' => $items,
            'q' => $q,
            'total' => $datasets->count(),
        ]);
    }

    /**
     * ✅ Sesuaikan di sini: field HTML tabel yang ada di DatasetBorang
     */
    private function extractHtmlFromDataset(DatasetBorang $dataset): ?string
    {
        foreach (['html_table', 'table_html', 'template_html', 'default_html', 'template', 'value'] as $field) {
            if (isset($dataset->{$field}) && is_string($dataset->{$field}) && trim($dataset->{$field}) !== '') {
                return $dataset->{$field};
            }
        }

        return null;
    }

    /**
     * Return:
     * - rowsInfo
     * - normalizedTableHtml
     * - mismatchCount
     */
    private function analyzeHtmlTable(string $html, int $expectedCount): array
    {
        $rowsInfo = [];
        $normalizedRows = [];
        $mismatchCount = 0;

        preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $html, $rowMatches);
        $trs = $rowMatches[1] ?? [];

        foreach ($trs as $rIndex => $rowHtml) {
            preg_match_all('/<t[hd]\b([^>]*)>(.*?)<\/t[hd]>/is', $rowHtml, $cellMatches);

            $attrsList = $cellMatches[1] ?? [];
            $innerList = $cellMatches[2] ?? [];

            $logicalCount = 0;
            $cells = [];

            foreach ($innerList as $cIndex => $inner) {
                $attrs = $attrsList[$cIndex] ?? '';

                $colspan = 1;
                $rowspan = 1;

                if (preg_match('/colspan\s*=\s*["\']?(\d+)/i', $attrs, $m)) {
                    $colspan = max(1, (int) $m[1]);
                }
                if (preg_match('/rowspan\s*=\s*["\']?(\d+)/i', $attrs, $m)) {
                    $rowspan = max(1, (int) $m[1]);
                }

                $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5));

                $cells[] = [
                    'text' => $text,
                    'colspan' => $colspan,
                    'rowspan' => $rowspan,
                ];

                $logicalCount += $colspan;

                // normalized: expand colspan
                for ($k = 0; $k < $colspan; $k++) {
                    $normalizedRows[$rIndex][] = [
                        'text' => $text,
                        'from_colspan' => $colspan,
                        'rowspan' => $rowspan,
                    ];
                }
            }

            $isMismatch = ($expectedCount > 0 && $logicalCount !== $expectedCount);
            if ($isMismatch) $mismatchCount++;

            $rowsInfo[] = [
                'row_index' => $rIndex,
                'logical_count' => $logicalCount,
                'expected_count' => $expectedCount,
                'mismatch' => $isMismatch,
                'cells' => $cells,
            ];
        }

        $normalizedTableHtml = $this->buildNormalizedHtmlTable($normalizedRows, $expectedCount);

        return [$rowsInfo, $normalizedTableHtml, $mismatchCount];
    }

    private function buildNormalizedHtmlTable(array $normalizedRows, int $expectedCount): string
    {
        $html = '<table class="table table-bordered table-sm" style="width:100%; table-layout: fixed; word-break: break-word;">';

        foreach ($normalizedRows as $rIndex => $cells) {
            $count = count($cells);
            $isMismatch = ($expectedCount > 0 && $count !== $expectedCount);

            $html .= '<tr style="' . ($isMismatch ? 'outline:2px solid #dc3545;' : '') . '">';

            foreach ($cells as $cell) {
                $badge = '';
                if (($cell['from_colspan'] ?? 1) > 1) {
                    $badge .= ' <span class="badge bg-warning text-dark">colspan ' . (int)$cell['from_colspan'] . '</span>';
                }
                if (($cell['rowspan'] ?? 1) > 1) {
                    $badge .= ' <span class="badge bg-info text-dark">rowspan ' . (int)$cell['rowspan'] . '</span>';
                }

                $text = e($cell['text'] ?? '');
                $html .= '<td style="overflow:hidden; white-space:normal;">' . $text . $badge . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }
}
