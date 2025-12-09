<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AsesmenUserRole;
use App\Models\PenilaianElemen;
use App\Models\Kriteria;
use App\Models\ElemenStandar;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ValidasiController extends Controller
{
    /**
     * Dashboard validasi untuk validator
     */
    public function dashboard()
    {
        $validator = Auth::user();

        // Get all asesmen yang perlu divalidasi oleh validator ini
        $assignments = AsesmenUserRole::where('id_user', $validator->id)
            ->where('role', 'validator')
            ->with(['asesmen', 'asesmen.program'])
            ->get();

        return view('asesmen.ak.validasi.dashboard', compact('assignments'));
    }

    /**
     * Halaman validasi untuk specific asesmen dan asesor
     */
    public function asesor($asesmenId, $asesor1Id, $asesor2Id = null)
    {
        $validator = Auth::user();

        // Verify validator has access to this asesmen
        $validatorAssignment = AsesmenUserRole::where('id_asesmen', $asesmenId)
            ->where('id_user', $validator->id)
            ->where('role', 'validator')
            ->firstOrFail();

        $asesmen = $validatorAssignment->asesmen;

        // Get asesor assignments
        $asesor1 = User::findOrFail($asesor1Id);

        // If asesor2 not specified, auto-detect the other asesor
        if (!$asesor2Id) {
            $asesor2 = AsesmenUserRole::where('id_asesmen', $asesmenId)
                ->where('role', 'asesor')
                ->where('id_user', '!=', $asesor1Id)
                ->firstOrFail()
                ->user;
        } else {
            $asesor2 = User::findOrFail($asesor2Id);
        }

        // Load kriteria with elemen, indikator, and penilaian from both asesor
        $kriterias = Kriteria::with([
            'elemenStandar' => function ($query) {
                $query->orderBy('urutan');
            },
            'elemenStandar.indikator' => function ($query) {
                $query->orderBy('urutan');
            },
            'elemenStandar.penilaian' => function ($query) use ($asesor1Id, $asesor2Id, $asesmenId) {
                $query->whereIn('id_user', [$asesor1Id, $asesor2Id])
                    ->where('id_asesmen', $asesmenId);
            }
        ])->orderBy('urutan')->get();

        // Calculate progress for each asesor
        $totalElemen = ElemenStandar::count();

        $progress1 = [
            'total' => $totalElemen,
            'completed' => PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_user', $asesor1Id)
                ->whereNotNull('skor')
                ->distinct('id_elemen')
                ->count('id_elemen'),
            'percentage' => 0
        ];
        $progress1['percentage'] = $totalElemen ? round(($progress1['completed'] / $totalElemen) * 100) : 0;

        $progress2 = [
            'total' => $totalElemen,
            'completed' => PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_user', $asesor2Id)
                ->whereNotNull('skor')
                ->distinct('id_elemen')
                ->count('id_elemen'),
            'percentage' => 0
        ];
        $progress2['percentage'] = $totalElemen ? round(($progress2['completed'] / $totalElemen) * 100) : 0;

        // Calculate validation progress
        $validatedCount = PenilaianElemen::where('id_asesmen', $asesmenId)
            ->whereIn('status_validasi', ['validated', 'revision_needed'])
            ->distinct('id_elemen')
            ->count('id_elemen');

        $validationPercentage = $totalElemen ? round(($validatedCount / $totalElemen) * 100) : 0;

        $allValidated = $validatedCount == $totalElemen;

        return view('asesmen.ak.validasi.asesor', compact(
            'asesmen',
            'asesor1',
            'asesor2',
            'kriterias',
            'progress1',
            'progress2',
            'totalElemen',
            'validatedCount',
            'validationPercentage',
            'allValidated'
        ));
    }

    /**
     * Get detail penilaian untuk modal validasi
     */
    public function getElemenDetail($asesmenId, $elemenId)
    {
        $elemen = ElemenStandar::with([
            'kriteria',
            'indikator' => function ($query) {
                $query->orderBy('urutan');
            },
            'penilaian' => function ($query) use ($asesmenId) {
                $query->where('id_asesmen', $asesmenId)
                    ->with('user');
            }
        ])->findOrFail($elemenId);

        return response()->json([
            'success' => true,
            'data' => [
                'elemen' => $elemen,
                'penilaian' => $elemen->penilaian,
            ]
        ]);
    }

    /**
     * Validasi satu elemen
     */
    public function validateElemen(Request $request, $asesmenId, $elemenId)
    {
        $request->validate([
            'status' => 'required|in:validated,revision_needed',
            'catatan_validator' => 'nullable|string',
            'skor_final' => 'required_if:status,validated|integer|min:0|max:4',
        ]);

        DB::beginTransaction();
        try {
            // Update semua penilaian untuk elemen ini
            PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_elemen', $elemenId)
                ->update([
                    'status_validasi' => $request->status,
                    'catatan_validator' => $request->catatan_validator,
                    'validated_at' => now(),
                    'validated_by' => Auth::user()->id,
                ]);

            // Jika validated, set skor final
            if ($request->status == 'validated') {
                PenilaianElemen::where('id_asesmen', $asesmenId)
                    ->where('id_elemen', $elemenId)
                    ->update([
                        'skor_final' => $request->skor_final,
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Validasi berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validasi otomatis semua elemen yang nilainya sama
     */
    public function validateAgreed($asesmenId)
    {
        DB::beginTransaction();
        try {
            // Get all elemen in this asesmen
            $elemenIds = ElemenStandar::pluck('id_elemen');

            $validatedCount = 0;

            foreach ($elemenIds as $elemenId) {
                // Get penilaian from both asesor
                $penilaianList = PenilaianElemen::where('id_asesmen', $asesmenId)
                    ->where('id_elemen', $elemenId)
                    ->whereNotNull('skor')
                    ->get();

                // Must have exactly 2 penilaian
                if ($penilaianList->count() != 2) {
                    continue;
                }

                // Check if both scores are the same
                $skor1 = $penilaianList[0]->skor;
                $skor2 = $penilaianList[1]->skor;

                if ($skor1 == $skor2) {
                    // Auto-validate with the agreed score
                    PenilaianElemen::where('id_asesmen', $asesmenId)
                        ->where('id_elemen', $elemenId)
                        ->update([
                            'status_validasi' => 'validated',
                            'skor_final' => $skor1,
                            'catatan_validator' => 'Auto-validated: Kedua asesor memberikan nilai yang sama',
                            'validated_at' => now(),
                            'validated_by' => Auth::user()->id,
                        ]);

                    $validatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memvalidasi {$validatedCount} elemen yang nilainya sama",
                'validated_count' => $validatedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan validasi otomatis: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve semua penilaian (final step)
     */
    public function approveAll($asesmenId, $asesorId)
    {
        DB::beginTransaction();
        try {
            // Check if all elemen are validated
            $totalElemen = ElemenStandar::count();
            $validatedCount = PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('status_validasi', 'validated')
                ->distinct('id_elemen')
                ->count('id_elemen');

            if ($validatedCount < $totalElemen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak semua elemen telah divalidasi. Harap lengkapi validasi terlebih dahulu.',
                ], 422);
            }

            // Update asesor assignment status
            AsesmenUserRole::where('id_asesmen', $asesmenId)
                ->where('id_user', $asesorId)
                ->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::user()->id,
                ]);

            // Lock all penilaian
            PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_user', $asesorId)
                ->update([
                    'is_locked' => true,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua penilaian telah disetujui dan dikunci',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export perbandingan penilaian ke Excel
     */
    public function exportComparison($asesmenId, $asesor1Id, $asesor2Id)
    {
        $asesmen = AsesmenUserRole::where('id_asesmen', $asesmenId)
            ->firstOrFail()
            ->asesmen;

        $asesor1 = User::findOrFail($asesor1Id);
        $asesor2 = User::findOrFail($asesor2Id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Perbandingan Penilaian');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);

        // Header
        $sheet->setCellValue('A1', 'KERTAS KERJA VALIDATOR');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Asesmen: ' . $asesmen->name);
        $sheet->mergeCells('A2:I2');

        $sheet->setCellValue('A3', 'Asesor 1: ' . $asesor1->name);
        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('E3', 'Asesor 2: ' . $asesor2->name);
        $sheet->mergeCells('E3:I3');

        // Table header
        $row = 5;
        $headers = [
            'Kriteria',
            'Kode Elemen',
            'Elemen Standar',
            'Indikator',
            'Asesor 1 Pemenuhan',
            'Asesor 1 Pelampauan',
            'Asesor 2 Pemenuhan',
            'Asesor 2 Pelampauan',
            'Status Validasi'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF932136');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($col . $row)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $col++;
        }

        // Data
        $row = 6;
        $kriterias = Kriteria::with([
            'elemenStandar.indikator',
            'elemenStandar.penilaian' => function ($query) use ($asesmenId, $asesor1Id, $asesor2Id) {
                $query->where('id_asesmen', $asesmenId)
                    ->whereIn('id_user', [$asesor1Id, $asesor2Id]);
            }
        ])->orderBy('urutan')->get();

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemen) {
                // Get penilaian
                $penilaian1 = $elemen->penilaian->where('id_user', $asesor1Id)->first();
                $penilaian2 = $elemen->penilaian->where('id_user', $asesor2Id)->first();

                // Indikator
                $indikatorText = $elemen->indikator->map(function ($ind) {
                    return $ind->kode_indikator . ': ' . $ind->deskripsi_indikator;
                })->implode("\n");

                $sheet->setCellValue('A' . $row, $kriteria->kode_kriteria);
                $sheet->setCellValue('B' . $row, $elemen->kode_elemen);
                $sheet->setCellValue('C' . $row, $elemen->pernyataan_elemen);
                $sheet->setCellValue('D' . $row, $indikatorText);

                // Asesor 1
                if ($penilaian1) {
                    if ($penilaian1->skor != 4) {
                        $sheet->setCellValue('E' . $row, $penilaian1->skor);
                        $this->applySkorColor($sheet, 'E' . $row, $penilaian1->skor);
                    }
                    if ($penilaian1->skor == 4) {
                        $sheet->setCellValue('F' . $row, 4);
                        $this->applySkorColor($sheet, 'F' . $row, 4);
                    }
                }

                // Asesor 2
                if ($penilaian2) {
                    if ($penilaian2->skor != 4) {
                        $sheet->setCellValue('G' . $row, $penilaian2->skor);
                        $this->applySkorColor($sheet, 'G' . $row, $penilaian2->skor);
                    }
                    if ($penilaian2->skor == 4) {
                        $sheet->setCellValue('H' . $row, 4);
                        $this->applySkorColor($sheet, 'H' . $row, 4);
                    }
                }

                // Status validasi
                $status = '-';
                if ($penilaian1 || $penilaian2) {
                    $validasi = $penilaian1 ?? $penilaian2;
                    if ($validasi->status_validasi == 'validated') {
                        $status = 'Disetujui';
                        $sheet->getStyle('I' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFE8F5E9');
                    } elseif ($validasi->status_validasi == 'revision_needed') {
                        $status = 'Perlu Revisi';
                        $sheet->getStyle('I' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFF3E0');
                    }
                }
                $sheet->setCellValue('I' . $row, $status);

                // Text wrapping
                $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
                $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);

                $row++;
            }
        }

        // Borders
        $sheet->getStyle('A5:I' . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        $filename = 'Perbandingan_Penilaian_' . date('YmdHis') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);

        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Helper: Apply color based on skor
     */
    private function applySkorColor($sheet, $cell, $skor)
    {
        $colors = [
            0 => 'FFF44336', // Red
            1 => 'FFFF9800', // Orange
            2 => 'FFFFEB3B', // Yellow
            3 => 'FF8BC34A', // Light Green
            4 => 'FF4CAF50', // Dark Green
        ];

        $color = $colors[$skor] ?? 'FFE0E0E0';

        $sheet->getStyle($cell)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($color);

        $sheet->getStyle($cell)->getFont()->setBold(true);
        $sheet->getStyle($cell)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }
}
