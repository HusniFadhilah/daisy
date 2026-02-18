<?php

namespace App\Http\Controllers\Test\Asesmen;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestLKPSController extends Controller
{
    public function test()
    {
        return view('tests.asesmen.test-lkps');
    }

    public function test2()
    {
        return view('tests.asesmen.test-lkps2');
    }

    public function model(): JsonResponse
    {
        return response()->json([
            "workbookName" => "LKPS Demo Hardcoded",
            "sheets" => [
                "Input" => [
                    "data" => [
                        ["Nama", "Nilai", "Total (SUM)", "Cari Kategori (VLOOKUP)", "Ambil dari sheet lain"],
                        ["A", 10, "=SUM(B2:B4)", "=VLOOKUP(B2, Data!A2:B6, 2, TRUE)", "=Data!B2"],
                        ["B", 20, null, null, null],
                        ["C", 30, null, null, null],
                        ["", "", "", "", ""],
                    ],
                    "merges" => [
                        ["row" => 4, "col" => 0, "rowspan" => 1, "colspan" => 5],
                    ],
                    "colWidths" => [140, 110, 150, 260, 220],
                    "rowHeights" => [28, 26, 26, 26, 28],
                    "cellMeta" => [
                        "0:0" => ["className" => "xl-bold xl-center xl-wrap", "readOnly" => true, "type" => "text"],
                        "0:1" => ["className" => "xl-bold xl-center xl-wrap", "readOnly" => true, "type" => "text"],
                        "0:2" => ["className" => "xl-bold xl-center xl-wrap", "readOnly" => true, "type" => "text"],
                        "0:3" => ["className" => "xl-bold xl-center xl-wrap", "readOnly" => true, "type" => "text"],
                        "0:4" => ["className" => "xl-bold xl-center xl-wrap", "readOnly" => true, "type" => "text"],

                        "1:0" => ["className" => "xl-fill-FFFF00", "readOnly" => false, "type" => "text"],
                        "1:1" => ["className" => "xl-fill-FFFF00 xl-right", "readOnly" => false, "type" => "numeric"],
                        "2:0" => ["className" => "xl-fill-FFFF00", "readOnly" => false, "type" => "text"],
                        "2:1" => ["className" => "xl-fill-FFFF00 xl-right", "readOnly" => false, "type" => "numeric"],
                        "3:0" => ["className" => "xl-fill-FFFF00", "readOnly" => false, "type" => "text"],
                        "3:1" => ["className" => "xl-fill-FFFF00 xl-right", "readOnly" => false, "type" => "numeric"],

                        "4:0" => ["className" => "xl-wrap", "readOnly" => true, "type" => "text"],
                    ],
                    "editableRanges" => [
                        "A2:B4",
                    ],
                ],

                "Data" => [
                    "data" => [
                        ["Batas Nilai", "Kategori"],
                        [0,  "E"],
                        [10, "D"],
                        [20, "C"],
                        [30, "B"],
                        [40, "A"],
                    ],
                    "merges" => [],
                    "colWidths" => [140, 120],
                    "rowHeights" => [28, 26, 26, 26, 26, 26],
                    "cellMeta" => [
                        "0:0" => ["className" => "xl-bold xl-center", "readOnly" => true, "type" => "text"],
                        "0:1" => ["className" => "xl-bold xl-center", "readOnly" => true, "type" => "text"],
                    ],
                    "editableRanges" => [],
                ],
            ],
        ]);
    }

    public function updateCells(Request $request): JsonResponse
    {
        // patch: [{sheet,row,col,value}, ...]
        $patch = $request->input('patch', []);

        // sementara: return sukses (hardcoded)
        return response()->json([
            "ok" => true,
            "received" => $patch,
        ]);
    }
}
