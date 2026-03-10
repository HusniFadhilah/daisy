<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\Kriteria;
use Illuminate\Support\Str;
use App\Models\ElemenStandar;
use App\Models\JenjangPenilaian;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as SharedDrawing;

class PenilaianExcelService
{
    protected $modelPenilaianElemen, $penilaianName, $penilaianFullName, $asesorName, $mode, $useColorFormatting;

    public function __construct($modelPenilaianElemen, $mode = 'full', $useColorFormatting = true)
    {
        $this->modelPenilaianElemen = $modelPenilaianElemen;
        $this->penilaianName = $modelPenilaianElemen == \App\Models\PenilaianElemenAl::class ? 'AL' : 'AK';
        $this->penilaianFullName = $this->penilaianName == 'AL' ? 'Asesmen Lapangan' : 'Asesmen Kecukupan';
        $this->asesorName = Auth::user()->name ?? 'Asesor LAMDEPILAR';
        $this->mode = $mode; // 'full', 'template', 'personal', 'personal_al', 'split'
        $this->useColorFormatting = $useColorFormatting;
    }
    /**
     * Generate template Excel file (format kosong)
     */
    public function generateTemplate(Asesmen $asesmen): string
    {
        [$spreadsheet, $sheet] = $this->createSheetBase($asesmen, true);

        $spreadsheet->setActiveSheetIndex(1);

        return $this->saveSpreadsheet($spreadsheet, 'Templat_Penilaian_' . $this->penilaianName);
    }

    /**
     * Generate Excel file dengan hasil penilaian
     */
    public function generateWithData(Asesmen $asesmen, $userId): string
    {
        if ($this->mode === 'personal' || $this->mode === 'personal_al') {
            return $this->generatePersonalAssessment($asesmen, $userId);
        }

        // ✅ NEW: mode split => hanya sheet Penilaian AK, data dari DB semua asesor
        if ($this->mode === 'split') {
            return $this->generateSplitPenilaianAkOnly($asesmen, $userId);
        }

        // Mode full (default)
        [$spreadsheet, $sheet] = $this->createSheetBase($asesmen, false, $userId);

        $spreadsheet->setActiveSheetIndex(1);

        return $this->saveSpreadsheet(
            $spreadsheet,
            'Penilaian_' . $this->penilaianName . '_Lengkap_',
            Str::slug($asesmen->code) . '_' . Str::slug($this->asesorName)
        );
    }

    /**
     * Generate Excel file HANYA sheet Penilaian (personal assessment only)
     * Hanya 1 kolom penilaian (asesor sendiri), data langsung dari DB
     */
    /**
     * Generate Excel file HANYA sheet Penilaian (personal assessment only)
     * Hanya 1 kolom penilaian (asesor sendiri), data langsung dari DB
     */
    public function generatePersonalAssessment(Asesmen $asesmen, $userId): string
    {
        $spreadsheet = new Spreadsheet();
        $penilaianName = strtoupper($this->penilaianName);

        // Ambil asesor login untuk keperluan tampilan / validasi personal
        $jenisAsesmen = strtolower($this->penilaianName);
        $asesor = \App\Models\AsesmenUserRole::where('id_asesmen', $asesmen->id)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->where('id_user', $userId)
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->with('user')
            ->first();

        if (!$asesor) {
            throw new \Exception('Asesor tidak ditemukan untuk asesmen ini');
        }

        // default = personal biasa pakai user login
        $sourceUserId = $userId;

        // special mode: personal_al => data diambil dari first penilai
        if ($this->mode === 'personal_al') {
            $firstAsesorUserId = $this->getFirstAsesorUserId($asesmen);

            if (!$firstAsesorUserId) {
                throw new \Exception('Asesor pertama tidak ditemukan untuk asesmen ini');
            }

            $sourceUserId = $firstAsesorUserId;
        }

        // Buat sheet Penilaian Personal
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penilaian ' . ucfirst($this->penilaianFullName));
        self::addLogoAndZoom($sheet, 70, 'B2', null, 35);

        // Set column widths - hanya sampai kolom G
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(80);

        // Build headers personal
        $this->buildPersonalHeaders($sheet, $asesmen, $asesor);

        // Build data rows personal
        $lastRow = $this->renderPersonalRows($sheet, $asesmen, $sourceUserId, $asesor);

        // ✅ Signature section
        $signatureStartRow = null;
        if (strtolower($this->penilaianName) == 'al') {
            $asesors = $this->getAsesors($asesmen, false);
            [$lastRow, $signatureStartRow] = $this->buildSignatureSection($sheet, $asesmen, $asesors, $lastRow);
        }

        $printAreaLastCol = 'H';
        $printAreaLastRow = $lastRow + 1;
        $sheet->getPageSetup()->setPrintArea("A1:{$printAreaLastCol}{$printAreaLastRow}");
        $sheet->getColumnDimension($printAreaLastCol)->setWidth(5);

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        if ($sheet->getTitle() === 'Penilaian ' . ucfirst($this->penilaianFullName)) {
            $sheet->freezePane('A7');
        }

        // // 🔒 LOCK semua cells dulu
        // $sheet->getStyle($sheet->calculateWorksheetDimension())
        //     ->getProtection()
        //     ->setLocked(Protection::PROTECTION_PROTECTED);

        // // ✅ UNLOCK signature section (jika ada) - SELURUH BAGIAN
        // if ($signatureStartRow !== null) {
        //     $sheet->getStyle("B{$signatureStartRow}:G{$lastRow}")
        //         ->getProtection()
        //         ->setLocked(Protection::PROTECTION_UNPROTECTED);
        // }

        // // 🔐 AKTIFKAN SHEET PROTECTION
        // $sheet->getProtection()->setSheet(true);
        // $sheet->getProtection()->setPassword('lamdepilar');

        // // ✅ Permissions
        // $sheet->getProtection()->setFormatColumns(true);
        // $sheet->getProtection()->setFormatRows(true);
        // $sheet->getProtection()->setInsertColumns(false);
        // $sheet->getProtection()->setDeleteColumns(false);
        // $sheet->getProtection()->setInsertRows(false);
        // $sheet->getProtection()->setDeleteRows(false);
        // $sheet->getProtection()->setSort(false);
        // $sheet->getProtection()->setAutoFilter(false);
        // $sheet->getProtection()->setFormatCells(true);

        // $sheet->getProtection()->setSelectLockedCells(false);
        // $sheet->getProtection()->setSelectUnlockedCells(false);

        return $this->saveSpreadsheet(
            $spreadsheet,
            'Penilaian_' . $this->penilaianName . '_',
            Str::slug($asesmen->code) . '_' . Str::slug($this->asesorName)
        );
    }

    public function generateSplitPenilaianAkOnly(Asesmen $asesmen, $userId): string
    {
        // ⚠️ sesuai request: hanya untuk AK
        if (strtoupper($this->penilaianName) !== 'AK') {
            throw new \Exception("Mode split hanya untuk Asesmen Kecukupan (AK).");
        }

        $spreadsheet = new Spreadsheet();

        // sheet aktif = satu-satunya sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penilaian ' . ucfirst($this->penilaianFullName));
        self::addLogoAndZoom($sheet, 60);

        // Build sheet Penilaian AK seperti full-withData, tapi DB untuk semua asesor
        $this->buildPenilaianJenisSheetOnly(
            $sheet,
            $asesmen,
            strtoupper($this->penilaianName),
            $userId,
            true // ✅ forceDbForAllAsesors
        );

        return $this->saveSpreadsheet(
            $spreadsheet,
            'Penilaian_' . $this->penilaianName . '_Split_',
            Str::slug($asesmen->code) . '_' . Str::slug($this->asesorName)
        );
    }

    private function buildPenilaianJenisSheetOnly($sheet, Asesmen $asesmen, $penilaianName, $userId, bool $forceDbForAllAsesors): void
    {
        // ambil semua asesor AK (templateOnly = false)
        $asesors = $this->getAsesors($asesmen, false);

        // reorder: asesor login di depan (opsional, sesuai pola kamu)
        if ($userId && $asesors->count() > 1) {
            $currentAsesor = $asesors->where('id_user', $userId)->first();
            $otherAsesors  = $asesors->where('id_user', '!=', $userId)->values();
            if ($currentAsesor) $asesors = collect([$currentAsesor])->merge($otherAsesors);
        }

        // column widths fixed
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);

        // withData => 1 kolom per asesor
        $startCol = 'G';
        foreach ($asesors as $index => $asesor) {
            $col = chr(ord($startCol) + $index);
            $sheet->getColumnDimension($col)->setWidth(80);
        }

        $splitCol = chr(ord('G') + $asesors->count());
        $ketCol   = chr(ord($splitCol) + 1);

        $sheet->getColumnDimension($splitCol)->setWidth(12);
        $sheet->getColumnDimension($ketCol)->setWidth(80);

        // headers withData
        $this->buildPenilaianJenisHeaders($sheet, $asesmen, $asesors, false, true);

        // render rows withData, tapi DB untuk semua asesor
        $lastRow = $this->renderPenilaianJenisRowsDbAll(
            $sheet,
            $asesmen,
            $penilaianName,
            $asesors,
            $forceDbForAllAsesors
        );

        // print area
        $lastDataCol = chr(ord('F') + $asesors->count() + 2);
        $printAreaLastCol = chr(ord($lastDataCol) + 1);
        $printAreaLastRow = $lastRow + 1;
        $sheet->getPageSetup()->setPrintArea("A1:{$printAreaLastCol}{$printAreaLastRow}");
        $sheet->getColumnDimension($printAreaLastCol)->setWidth(5);

        // page setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Freeze hanya baris 1–7 untuk sheet Penilaian AK
        if ($sheet->getTitle() === 'Penilaian ' . ucfirst($this->penilaianFullName)) {
            $sheet->freezePane('A7'); // freeze baris 1–6
        }
    }

    private function renderPenilaianJenisRowsDbAll($sheet, Asesmen $asesmen, $penilaianName, $asesors, bool $forceDbForAllAsesors): int
    {
        $currentRow = 7;

        $jenisAsesmen  = strtolower($this->penilaianName); // ak
        $relationName  = $jenisAsesmen == 'al' ? 'penilaianElemenAl' : 'penilaianElemenAk';

        $kriterias = Kriteria::with([
            'elemenStandar' => function ($q) {
                $q->orderBy('kode_elemen');
            },
            "elemenStandar.{$relationName}" => function ($q) use ($asesors, $asesmen) {
                $q->whereIn('id_asesor', $asesors->pluck('id_user'))->where('id_asesmen', $asesmen->id);
            },
            "elemenStandar.{$relationName}.asesor"
        ])->get();

        $globalNo = 1;
        foreach ($kriterias as $indexKriteria => $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();

            if ($elemenCount > 1) {
                $blockStartRow = $currentRow;
                $blockEndRow   = $currentRow + $elemenCount - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }

            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $row = $currentRow;

                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$row}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$row}", $kriteria->nama_kriteria);
                }

                $sheet->setCellValue("D{$row}", $globalNo);
                $sheet->setCellValue("E{$row}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$row}", $elemen->pernyataan_elemen);

                $startCol = 'G';
                foreach ($asesors as $asesorIndex => $asesor) {
                    $col = chr(ord($startCol) + $asesorIndex);

                    $penilaian = $elemen->{$relationName}
                        ->where('id_asesor', $asesor->id_user)
                        ->first();

                    if ($penilaian && $penilaian->skor !== null) {
                        $skor = (int) $penilaian->skor;
                        $komentar = $penilaian->komentar ? $penilaian->komentar : '';

                        if ($this->useColorFormatting) {
                            $bgColor = $this->getSkorColor($skor);

                            $sheet->setCellValue("{$col}{$row}", $komentar);
                            $sheet->getStyle("{$col}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB($this->hexToArgb($bgColor));
                        } else {
                            $sheet->setCellValue("{$col}{$row}", $this->formatPenilaianText($skor, $komentar));
                            $sheet->getStyle("{$col}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('FFFFFFFF');
                        }
                    } else {
                        $sheet->setCellValue("{$col}{$row}", '');
                    }
                }

                // ========================
                // ✅ SPLIT DETECTION (hanya mode split ini)
                // compare asesor pertama vs kedua
                // ========================
                $splitCol = chr(ord('G') + $asesors->count());
                $ketCol   = chr(ord($splitCol) + 1);

                $splitValue = 'Tidak Split';
                $keterangan = '';

                if ($asesors->count() >= 2) {
                    $asesor1 = $asesors[0];
                    $asesor2 = $asesors[1];

                    $p1 = $elemen->{$relationName}->where('id_asesor', $asesor1->id_user)->first();
                    $p2 = $elemen->{$relationName}->where('id_asesor', $asesor2->id_user)->first();

                    $skor1 = ($p1 && $p1->skor !== null) ? (int) $p1->skor : null;
                    $skor2 = ($p2 && $p2->skor !== null) ? (int) $p2->skor : null;

                    // Split hanya dihitung kalau dua-duanya ada skor
                    if ($skor1 !== null && $skor2 !== null) {
                        if (abs($skor1 - $skor2) > 1) { // beda 2+ => split
                            $splitValue = 'Ya';

                            $label1 = JenjangPenilaian::getSkorLabelAttribute($skor1) ?? 'Tidak diketahui';
                            $label2 = JenjangPenilaian::getSkorLabelAttribute($skor2) ?? 'Tidak diketahui';

                            $keterangan = "Asesor 1 memberikan penilaian {$label1}. Sementara Asesor 2 memberikan penilaian {$label2}";
                        }
                    }
                }

                $sheet->setCellValue("{$splitCol}{$row}", $splitValue);
                $sheet->setCellValue("{$ketCol}{$row}", $keterangan);

                // style rapih (opsional tapi bagus)
                $sheet->getStyle("{$splitCol}{$row}:{$ketCol}{$row}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);


                // $lastCol = chr(ord($startCol) + $asesors->count() - 1);
                $lastCol = $ketCol;

                $this->applyRowStyling($sheet, $row, null, 'B', $lastCol);

                $sheet->getStyle("B{$row}:{$lastCol}{$row}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);

                $sheet->getStyle("B{$row}:F{$row}")
                    ->getFont()->setSize(14)
                    ->getColor()
                    ->setARGB('FF31869B');

                $currentRow++;
                $globalNo++;
                $isFirstElemen = false;
            }
        }

        // ✅ tidak perlu applyPenilaianJenisConditionalFormatting karena kita sudah warnai langsung per cell dari DB
        return $currentRow - 1;
    }

    /**
     * Buat spreadsheet + sheet, set title, column widths, dan header.
     * Mengembalikan array: [Spreadsheet $spreadsheet, Worksheet $sheet]
     */
    private function createSheetBase(Asesmen $asesmen, $isTemplateOnly = true, $userId = null): array
    {
        $spreadsheet = new Spreadsheet();
        // Sheet 0 → MENU
        $this->buildMenuSheet($spreadsheet, $asesmen);

        // Sheet 1 → Kertas Kerja
        $penilaianName = strtoupper($this->penilaianName);
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Kertas Kerja ' . $penilaianName . ' Asesor');
        $spreadsheet->setActiveSheetIndex(1);
        self::addLogoAndZoom($sheet, 60);

        $this->setColumnWidths($sheet);
        $this->buildHeaders($sheet, $asesmen, $isTemplateOnly);
        // $this->setTanggalCetak($sheet, 'B1:F1');

        // ✅ TAMBAHKAN: Render rows dan set print area
        $lastRow = $this->renderElemenRows($sheet, $asesmen, $userId, $isTemplateOnly);

        // Set print area untuk Sheet Kertas Kerja (A1 sampai N{lastRow+1})
        $printAreaLastCol = 'N'; // M + 1
        $printAreaLastRow = $lastRow + 1;
        $sheet->getPageSetup()->setPrintArea("A1:{$printAreaLastCol}{$printAreaLastRow}");
        $sheet->getColumnDimension($printAreaLastCol)->setWidth(5);

        // ✅ TAMBAHKAN: Apply border ke seluruh print area
        $printRange = "A1:{$printAreaLastCol}{$printAreaLastRow}";
        // $sheet->getStyle($printRange)->getBorders()->getAllBorders()
        //     ->setBorderStyle(Border::BORDER_THIN)
        //     ->getColor()->setARGB('FF1F4E79');

        // Set page setup
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // ✅ TAMBAHKAN: Set margin ke 0
        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        $this->buildPenilaianJenisSheet($spreadsheet, $asesmen, $penilaianName, $isTemplateOnly, $userId); // ✅ Pass userId

        if ($sheet->getTitle() === 'Kertas Kerja ' . $penilaianName . ' Asesor') {
            // Freeze kolom A-H dan baris 1-7
            $sheet->freezePane('I8');
        }

        // // 🔐 AKTIFKAN SHEET PROTECTION untuk Sheet Kertas Kerja
        // $sheet->getProtection()->setSheet(true);
        // $sheet->getProtection()->setPassword('lamdepilar');

        // // ✅ Permissions: Boleh resize, tidak boleh delete
        // $sheet->getProtection()->setFormatColumns(true);  // Boleh resize lebar kolom
        // $sheet->getProtection()->setFormatRows(true);     // Boleh resize tinggi baris
        // $sheet->getProtection()->setInsertColumns(false); // Tidak boleh insert kolom
        // $sheet->getProtection()->setDeleteColumns(false); // Tidak boleh delete kolom
        // $sheet->getProtection()->setInsertRows(false);    // Tidak boleh insert baris
        // $sheet->getProtection()->setDeleteRows(false);    // Tidak boleh delete baris
        // $sheet->getProtection()->setSort(false);          // Tidak boleh sort
        // $sheet->getProtection()->setAutoFilter(false);    // Tidak boleh autofilter
        // $sheet->getProtection()->setFormatCells(true);   // Tidak boleh format cells (butuh password)

        // // ✅ User bisa select locked & unlocked cells
        // $sheet->getProtection()->setSelectLockedCells(false);
        // $sheet->getProtection()->setSelectUnlockedCells(false);

        return [$spreadsheet, $sheet];
    }

    /**
     * Set column widths (dipakai dua mode)
     */
    private function setColumnWidths($sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(70);
        $sheet->getColumnDimension('J')->setWidth(70);
        $sheet->getColumnDimension('K')->setWidth(70);
        $sheet->getColumnDimension('L')->setWidth(70);
        $sheet->getColumnDimension('M')->setWidth(90);
    }

    /**
     * Render baris data (2 baris per elemen).
     * Jika $userId null => template mode
     * Jika $userId ada => withData mode
     */
    private function renderElemenRows($sheet, Asesmen $asesmen, ?int $userId, $isTemplateOnly): int
    {
        $currentRow = 8;
        $sheet->getStyle($sheet->calculateWorksheetDimension())
            ->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED);

        $kriterias = Kriteria::with([
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
        ])->get();

        $globalNo = 1;
        foreach ($kriterias as $indexKriteria => $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();
            if ($elemenCount > 1) {
                $blockStartRow = $currentRow;
                $blockEndRow   = $currentRow + ($elemenCount * 2) - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }
            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $templateRow = $currentRow;
                $asesorRow   = $currentRow + 1;

                // ========== 1) ROW TEMPLATE: I–M berisi instruksi ==========
                $penilaianMap = $this->getPenilaianMapBySkor($elemen);

                // NOTE: file Anda pakai skor 0–4 (I..M). Jika sistem Anda hanya 0–3, silakan sesuaikan.
                $sheet->setCellValue("I{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[0] ?? null, 0));
                $sheet->setCellValue("J{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[1] ?? null, 1));
                $sheet->setCellValue("K{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[2] ?? null, 2));
                $sheet->setCellValue("L{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[3] ?? null, 3));
                $sheet->setCellValue("M{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[4] ?? null, 4));

                $sheet->getStyle("I{$templateRow}:M{$templateRow}")
                    ->getFont()
                    ->setSize(12);

                // ========== 2) Isi identitas kriteria (hanya sekali per grup kriteria) ==========
                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$templateRow}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$templateRow}", $kriteria->nama_kriteria);
                }

                // ========== 3) Isi elemen + indikator ==========
                $indikatorKualitatif = $elemen->indikator
                    ->filter(fn($ind) => $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kualitatif') !== false)
                    ->pluck('deskripsi_indikator')
                    ->implode("\n\n");

                $indikatorKuantitatif = $elemen->indikator
                    ->filter(fn($ind) => $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kuantitatif') !== false)
                    ->pluck('deskripsi_indikator')
                    ->implode("\n\n");

                $sheet->setCellValue("D{$templateRow}", $globalNo);
                $sheet->setCellValue("E{$templateRow}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$templateRow}", $elemen->pernyataan_elemen);
                $sheet->setCellValue("G{$templateRow}", $indikatorKualitatif ?: 'Tidak ada');
                $sheet->setCellValue("H{$templateRow}", $indikatorKuantitatif ?: 'Tidak ada');

                // ========== 4) Baris asesor: kosongkan I–M ==========
                foreach (['I', 'J', 'K', 'L', 'M'] as $col) {
                    $sheet->setCellValue("{$col}{$asesorRow}", '');
                    $sheet->getStyle("{$col}{$asesorRow}")
                        ->getProtection()
                        ->setLocked(Protection::PROTECTION_UNPROTECTED);
                }

                // ===== Data Validation: hanya boleh 1 kolom terisi =====
                foreach (range('I', 'M') as $col) {
                    $cell = $sheet->getCell("{$col}{$asesorRow}");
                    $validation = $cell->getDataValidation();
                    $validation->setType(DataValidation::TYPE_CUSTOM);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Input Salah');
                    $validation->setError('Hanya boleh mengisi satu kolom per baris I-M ini. Silahkan hapus nilai di antara I-M di kolom lain di baris ini yang sudah terisi');
                    $validation->setFormula1("=COUNTA(I{$asesorRow}:M{$asesorRow})<=1");
                }

                // ========== 5) Jika withData mode, isi komentar pada kolom sesuai skor ==========
                if ($userId !== null) {
                    $this->applyPenilaianDataToAsesorRow($sheet, $asesmen, $userId, $elemen, $asesorRow);
                }

                // ========== 6) Merge B–H antar 2 baris (rowspan effect) ==========
                $this->mergeTwoRowBlock($sheet, $templateRow, $asesorRow);

                // ✅ FONT COLOR untuk ISI (bukan header), kolom B–F setelah baris 7
                $sheet->getStyle("B{$templateRow}:F{$asesorRow}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FF31869B');

                $sheet->getStyle("G{$templateRow}:H{$asesorRow}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FF31869B');

                // align B–H gabungan 2 baris
                $sheet->getStyle("B{$templateRow}:H{$asesorRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle("B{$templateRow}:H{$asesorRow}")
                    ->getFont()
                    ->setSize(16);

                // ========== 7) Border & wrap untuk kedua baris ==========
                $this->applyRowStyling($sheet, $templateRow, 200);
                $this->applyRowStyling($sheet, $asesorRow);

                // ========== 8) Style baris asesor (kuning + tinggi) ==========
                $this->applyAsesorRowStyle($sheet, $asesorRow, $isTemplateOnly);

                $currentRow += 2;
                $globalNo++;
                $isFirstElemen = false;
            }
        }
        return $currentRow - 1;
    }

    /**
     * Isi data penilaian (komentar) ke baris asesor sesuai skor (I..M).
     */
    private function applyPenilaianDataToAsesorRow($sheet, Asesmen $asesmen, int $userId, ElemenStandar $elemen, int $asesorRow): void
    {
        $modelPenilaianElemen = $this->modelPenilaianElemen;
        $penilaian = $modelPenilaianElemen::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $userId)
            ->where('id_elemen', $elemen->id)
            ->first();

        if (!$penilaian || $penilaian->skor === null) {
            return;
        }

        // I=0, J=1, K=2, L=3, M=4
        $scoreColumn = chr(73 + (int) $penilaian->skor);
        if (!in_array($scoreColumn, ['I', 'J', 'K', 'L', 'M'], true)) {
            return; // safety
        }

        $sheet->setCellValue("{$scoreColumn}{$asesorRow}", $penilaian->komentar);

        // highlight cell yang terisi
        $sheet->getStyle("{$scoreColumn}{$asesorRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFfff9c4');
    }

    /**
     * Merge cells B–H untuk 2 baris (templateRow + asesorRow)
     */
    private function mergeTwoRowBlock($sheet, int $templateRow, int $asesorRow): void
    {
        $sheet->mergeCells("D{$templateRow}:D{$asesorRow}");
        $sheet->mergeCells("E{$templateRow}:E{$asesorRow}");
        $sheet->mergeCells("F{$templateRow}:F{$asesorRow}");
        $sheet->mergeCells("G{$templateRow}:G{$asesorRow}");
        $sheet->mergeCells("H{$templateRow}:H{$asesorRow}");
    }

    private function mergeBlock($sheet, $blockStart, $blockEnd, int $rowStart, int $rowEnd): void
    {
        if ($rowStart <= $rowEnd) {
            $sheet->mergeCells("$blockStart{$rowStart}:$blockEnd{$rowEnd}");
        }
    }

    /**
     * Style khusus baris asesor (kuning) + tinggi baris
     */
    private function applyAsesorRowStyle($sheet, int $asesorRow, $isTemplateOnly): void
    {
        $sheet->getStyle("B{$asesorRow}:M{$asesorRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff99');

        $sheet->getStyle("B{$asesorRow}:M{$asesorRow}")
            ->getFont()
            ->setSize(16);

        // Hanya set maxHeight jika diberikan
        if ($isTemplateOnly) {
            $sheet->getRowDimension($asesorRow)->setRowHeight(400);
        } else {
            $sheet->getRowDimension($asesorRow)->setRowHeight(-1); // Auto height
            $sheet->getRowDimension($asesorRow)->setRowHeight(500);
        }
    }

    /**
     * Simpan spreadsheet ke storage/app/temp (aman untuk Windows & code yg mengandung slash)
     */
    private function saveSpreadsheet(Spreadsheet $spreadsheet, string $prefix, $code = null): string
    {
        // 1) sanitize code agar tidak jadi folder (LAMDEPILAR/2026/001 -> LAMDEPILAR-2026-001)
        $safeCode = null;
        if (!empty($code)) {
            $safeCode = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', (string) $code);
            $safeCode = preg_replace('/-+/', '-', $safeCode);
            $safeCode = trim($safeCode, '-');
        }

        // 2) pastikan prefix juga aman (jaga-jaga)
        $safePrefix = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', $prefix);

        // 3) bentuk filename
        $date = date('Ymd');
        $filename = $safeCode
            ? "{$safePrefix}{$safeCode}_{$date}.xlsx"
            : "{$safePrefix}.xlsx";

        // 4) base temp dir
        $baseDir = storage_path('app/temp');
        $tempPath = $baseDir . DIRECTORY_SEPARATOR . $filename;

        // 5) pastikan foldernya ada (recursive)
        $dir = dirname($tempPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // 6) save
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Build Excel headers (warna + rowspan sesuai permintaan)
     */
    private function buildHeaders($sheet, $asesmen, $isTemplateOnly = true): void
    {
        // ===== Title =====
        $sheet->mergeCells('B2:M2');
        $sheet->setCellValue('B2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(22);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('B3:M3');
        $sheet->setCellValue('B3', 'Tabel Kertas Kerja Asesor' . ($isTemplateOnly ? '' : ' - ' . $asesmen->name));
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(17);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ===== Header row 5-7 dengan rowspan =====
        // Kriteria (B:C) rowspan sampai row 7
        $sheet->mergeCells('B5:C7');
        $sheet->setCellValue('B5', 'Kriteria');

        // Kode Elemen (D:E) rowspan sampai row 7
        $sheet->mergeCells('D5:E7');
        $sheet->setCellValue('D5', 'Kode Elemen');

        // Elemen Standar (F) rowspan sampai row 7
        $sheet->mergeCells('F5:F7');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // Indikator (G:H) merge row 5, lalu subheader di row 6-7
        $sheet->mergeCells('G5:H5');
        $sheet->setCellValue('G5', 'Indikator');

        // Penilaian (I:M) merge row 5
        $sheet->mergeCells('I5:M5');
        $sheet->setCellValue('I5', 'Penilaian');

        // Subheader indikator (row 6-7 dibuat rowspan per kolom)
        $sheet->setCellValue('G6', 'Kualitatif');
        $sheet->setCellValue('H6', 'Kuantitatif');
        $sheet->mergeCells('G6:G7');
        $sheet->mergeCells('H6:H7');

        // Subheader penilaian row 6
        $jenjangPenilaian = JenjangPenilaian::all(); // ambil semua data

        $startColumn = 'I'; // mulai dari kolom I
        $row = 6;

        foreach ($jenjangPenilaian as $index => $item) {
            $column = Coordinate::stringFromColumnIndex(
                Coordinate::columnIndexFromString($startColumn) + $index
            );

            $sheet->setCellValue($column . $row, $item->name);
        }

        // Skor row 7
        $sheet->setCellValue('I7', '0');
        $sheet->setCellValue('J7', '1');
        $sheet->setCellValue('K7', '2');
        $sheet->setCellValue('L7', '3');
        $sheet->setCellValue('M7', '4');

        // ===== Styling umum header =====
        $headerRange = 'B5:M7';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(15);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Border
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // ===== Warna sesuai permintaan =====
        // B5:F7 putih
        // $sheet->getStyle('B5:F7')->getFill()
        //     ->setFillType(Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FFFFFFFF');

        // // G5:H7 indikator #D0E0E3
        // $sheet->getStyle('G5:H7')->getFill()
        //     ->setFillType(Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FFD0E0E3');

        // // I5:M7 penilaian #D9EAD3
        // $sheet->getStyle('I5:M7')->getFill()
        //     ->setFillType(Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FFD9EAD3');
        $sheet->getStyle('B5:M7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');

        // Tinggi baris header
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(40);
        $sheet->getRowDimension(7)->setRowHeight(20);

        // Petunjuk isi
        $sheet->mergeCells('B4:M4');
        $sheet->setCellValue('B4', 'Silahkan isi di bagian cell berwarna kuning. Masing-masing baris, hanya 1 kolom (di antara I-M) yang dapat diisi');
        $sheet->getStyle('B4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF1F4E79'], // biru
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);
    }

    /**
     * Apply styling to data row
     */
    private function applyRowStyling($sheet, $row, ?int $maxHeight = null, $startColumn = 'B', $endColumn = 'M'): void
    {
        $range = "{$startColumn}{$row}:{$endColumn}{$row}";

        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle($range)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height

        // Hanya set maxHeight jika diberikan
        if ($maxHeight !== null) {
            $sheet->getRowDimension($row)->setRowHeight($maxHeight);
        }
    }

    /**
     * Ambil deskripsi penilaian per skor (0–4) untuk satu elemen.
     */
    private function getPenilaianMapBySkor(ElemenStandar $elemen): array
    {
        $map = [];

        $elemen->loadMissing('indikatorPenilaian.jenjangPenilaian');

        foreach ($elemen->indikatorPenilaian as $indikator) {
            if ($indikator->jenjangPenilaian) {
                $skor = $indikator->jenjangPenilaian->skor;
                $map[$skor] = $indikator->deskripsi_penilaian;
            }
        }

        return $map;
    }

    private function buildMenuSheet(Spreadsheet $spreadsheet, Asesmen $asesmen): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Menu');
        $sheet->mergeCells('A1:A2');
        $sheet->getStyle('A1:D2')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFBD4B4',
                ],
            ],
        ]);

        // Hide gridlines
        $sheet->setShowGridlines(false);

        // ===== COLUMN WIDTH (sesuaikan dengan gambar) =====
        foreach (range('A', 'Y') as $col) {
            $sheet->getColumnDimension($col)->setWidth(8);
        }

        // set row height yang mempengaruhi area logo
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // BARU pasang logo supaya hitung px-nya pakai ukuran final
        self::addLogoAndZoom($sheet, 80, 'B1', 'D2');
        $this->setTanggalCetak($sheet, 'AA1:AE1');
        // ===== ROW 1: AKREDITASI PERGURUAN TINGGI (Orange) =====
        $sheet->mergeCells('E1:Y1');
        $sheet->setCellValue('E1', 'AKREDITASI PERGURUAN TINGGI');
        $sheet->getStyle('E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24,
                'color' => ['argb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFBD4B4']
            ]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ===== ROW 2: BADAN AKREDITASI NASIONAL (Peach) =====
        $sheet->mergeCells('E2:Y2');
        $sheet->setCellValue('E2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('E2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFBD4B4']
            ]
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // ===== ROW 3: Empty =====
        $sheet->getRowDimension(3)->setRowHeight(5);

        // ===== ROW 4: PERGURUAN TINGGI AKADEMIK (Light Green) =====
        $sheet->mergeCells('A4:C4');
        $sheet->getStyle('A4:C4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFEAF1DD',
                ],
            ],
        ]);
        $sheet->mergeCells('D4:Y4');
        $sheet->setCellValue('D4', 'PERGURUAN TINGGI AKADEMIK');
        $sheet->getStyle('D4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEAF1DD']
            ]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // ===== ROW 5: Empty =====
        $sheet->getRowDimension(5)->setRowHeight(5);

        // ===== BACKGROUND TEAL BESAR (Row 6-27) =====
        $fillTeal = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '932136']
            ]
        ];

        foreach (['A3:Y3', 'A5:Y5', 'A6:Y27'] as $range) {
            $sheet->getStyle($range)->applyFromArray($fillTeal);
        }

        // ===== DATA PERGURUAN TINGGI (Kiri - Row 7, 9, 11, 13, 15) =====
        $leftData = [
            7  => ['label' => 'Nama Perguruan Tinggi', 'value' => $asesmen->studyProgram->university->name ?? '-'],
            9  => ['label' => 'Nama Program Studi', 'value' => $asesmen->studyProgram->name ?? '-'],
            11 => ['label' => 'Jenis Permohonan Akreditasi', 'value' => $asesmen->pengajuan->jenis_akreditasi_label ?? '-'],
            13 => ['label' => 'Kode Panel', 'value' => $asesmen->kode_panel],
            15 => ['label' => 'TS *)', 'value' => now()->year],
        ];

        foreach ($leftData as $row => $data) {
            // Label (B:E)
            $sheet->mergeCells("B{$row}:E{$row}");
            $sheet->setCellValue("B{$row}", $data['label']);
            $sheet->getStyle("B{$row}:E{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Colon (F)
            $sheet->setCellValue("F{$row}", ':');
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Value (G:L) - White box
            $sheet->mergeCells("G{$row}:L{$row}");
            $sheet->setCellValue("G{$row}", $data['value']);
            $sheet->getStyle("G{$row}:L{$row}")->applyFromArray([
                'font' => [
                    'size' => 14,
                    'color' => ['argb' => '000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFFFF']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF1F4E79']
                    ]
                ]
            ]);

            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        // ===== VERTICAL YELLOW LINE (Column M) =====
        $sheet->getStyle('M6:M27')->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FFFBFD02']
                ]
            ]
        ]);

        // ===== JENIS ASESMEN HEADER (Row 18) =====
        $sheet->mergeCells('N18:Y18');
        $sheet->setCellValue('N18', strtoupper($this->penilaianFullName));
        $sheet->getStyle('N18')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(18)->setRowHeight(25);

        // ===== (Penilaian Individual) (Row 19) =====
        $sheet->mergeCells('N19:Y19');
        $sheet->setCellValue('N19', '(Penilaian Individual)');
        $sheet->getStyle('N19')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(19)->setRowHeight(20);

        // ===== DATA ASESOR (Kanan - Row 21, 23, 25) =====
        $rightData = [
            21 => ['label' => 'Nama Asesor', 'value' => $this->asesorName],
            23 => ['label' => 'Kota Penilaian', 'value' => 'Semarang'],
            25 => ['label' => 'Tanggal Penilaian', 'value' => date('d-M-Y')],
        ];

        foreach ($rightData as $row => $data) {
            // Label (O:R)
            $sheet->mergeCells("O{$row}:R{$row}");
            $sheet->setCellValue("O{$row}", $data['label']);
            $sheet->getStyle("O{$row}:R{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Colon (S)
            $sheet->setCellValue("S{$row}", ':');
            $sheet->getStyle("S{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Value (T:Y) - White box
            $sheet->mergeCells("T{$row}:X{$row}");
            $sheet->setCellValue("T{$row}", $data['value']);
            $sheet->getStyle("T{$row}:X{$row}")->applyFromArray([
                'font' => [
                    'size' => 14,
                    'color' => ['rgb' => '000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFFFF']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF1F4E79']
                    ]
                ]
            ]);

            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        // ===== FOOTNOTE TS (Row 23) =====
        $sheet->mergeCells('B23:L23');
        $sheet->setCellValue('B23', '*) TS = Tahun akademik penuh terakhir saat permohonan akreditasi');
        $sheet->getStyle('B23')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 9,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(23)->setRowHeight(18);

        // ===== SOURCE (Row 25) =====
        $sheet->mergeCells('B25:L25');
        $sheet->setCellValue('B25', 'LAMDEPILAR      versi 1.0');
        $sheet->getStyle('B25')->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(25)->setRowHeight(16);

        // Perpendek tinggi baris tertentu
        foreach ([8, 10, 12, 14, 22, 24] as $row) {
            $sheet->getRowDimension($row)->setRowHeight(6);
        }

        // =====================
        // PRINT SETUP MENU SHEET
        // =====================
        $sheet->getPageSetup()->setPrintArea('A1:Y27');

        // Landscape + A4 + fit to 1 page width (tinggi biarkan auto)
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Margin 0 semua
        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);
    }

    /**
     * Bangun teks instruksi default untuk cell penilaian.
     */
    private function buildPenilaianInstruction(?string $deskripsi, int $skor): RichText
    {
        $richText = new RichText();

        // Bagian italic
        $italicText = $richText->createTextRun("Tuliskan pernyataan penilaian pada kolom ini apabila:\n\n");
        $italicText->getFont()->setItalic(true)->getColor()->setARGB('FF1F4E79');

        // Bagian deskripsi (normal)
        $desc = $deskripsi ?: "skor {$skor}";
        $descRun = $richText->createTextRun($desc);
        $descRun->getFont()->getColor()->setARGB('FF31869B');
        return $richText;
    }

    /**
     * Build sheet "Penilaian (JENIS)" setelah sheet Kertas Kerja
     */
    private function buildPenilaianJenisSheet(Spreadsheet $spreadsheet, Asesmen $asesmen, $penilaianName, $isTemplateOnly, $userId = null): void
    {
        $asesors = $this->getAsesors($asesmen, $isTemplateOnly);

        // ✅ REORDER: Asesor yang login pindah ke posisi pertama
        if (!$isTemplateOnly && $userId && $asesors->count() > 1) {
            $currentAsesor = $asesors->where('id_user', $userId)->first();
            $otherAsesors = $asesors->where('id_user', '!=', $userId)->values();

            if ($currentAsesor) {
                // Gabungkan: current asesor di depan, sisanya di belakang
                $asesors = collect([$currentAsesor])->merge($otherAsesors);
            }
        }

        // Load data penilaian dari database (untuk asesor lainnya)
        $relationName = $this->penilaianName == 'AL' ? 'penilaianElemenAl' : 'penilaianElemenAk';
        $kriterias = null;

        // Hanya load data jika bukan template (untuk asesor lainnya)
        if (!$isTemplateOnly && $asesors->count() > 1) {
            $kriterias = Kriteria::with([
                'elemenStandar' => function ($q) {
                    $q->orderBy('kode_elemen');
                },
                "elemenStandar.{$relationName}" => function ($q) use ($asesors) {
                    $q->whereIn('id_asesor', $asesors->pluck('id_user'));
                },
                "elemenStandar.{$relationName}.asesor"
            ])->get();
        }

        // Buat sheet baru
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Penilaian ' . ucfirst($this->penilaianFullName));
        self::addLogoAndZoom($sheet, 60);

        // Set column widths - fixed columns dulu
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);

        // Set column widths untuk setiap asesor (2 kolom per asesor: Pemenuhan + Pelampauan)
        // Set column widths untuk setiap asesor
        $startCol = 'G';

        if ($isTemplateOnly) {
            // Template: 2 kolom per asesor (Pemenuhan + Pelampauan)
            foreach ($asesors as $index => $asesor) {
                $col1 = chr(ord($startCol) + ($index * 2));
                $col2 = chr(ord($startCol) + ($index * 2) + 1);
                $sheet->getColumnDimension($col1)->setWidth(80);
                $sheet->getColumnDimension($col2)->setWidth(80);
            }
        } else {
            // WithData: 1 kolom per asesor (gabungan semua skor)
            foreach ($asesors as $index => $asesor) {
                $col = chr(ord($startCol) + $index);
                $sheet->getColumnDimension($col)->setWidth(80);
            }
        }

        // Build headers
        $this->buildPenilaianJenisHeaders($sheet, $asesmen, $asesors, $isTemplateOnly);
        // $this->setTanggalCetak($sheet, 'B1:F1');

        // Build data rows dan dapatkan last row
        $lastRow = $this->renderPenilaianJenisRows($sheet, $asesmen, $penilaianName, $asesors, $isTemplateOnly, $kriterias);

        // Signature section (khusus AL)
        $signatureStartRow = null;
        if (strtolower($this->penilaianName) == 'al' && !$isTemplateOnly) {
            [$lastRow, $signatureStartRow] = $this->buildSignatureSection($sheet, $asesmen, $asesors, $lastRow);
        }

        // Hitung kolom terakhir untuk print area
        if ($isTemplateOnly) {
            $lastDataCol = chr(ord('F') + ($asesors->count() * 2));
        } else {
            $lastDataCol = chr(ord('F') + $asesors->count());
        }

        // Print area: kolom terakhir + 1, baris terakhir + 1
        $printAreaLastCol = chr(ord($lastDataCol) + 1);
        $printAreaLastRow = $lastRow + 1;
        $sheet->getPageSetup()->setPrintArea("A1:{$printAreaLastCol}{$printAreaLastRow}");
        $sheet->getColumnDimension($printAreaLastCol)->setWidth(5);

        // ✅ TAMBAHKAN: Apply border ke seluruh print area
        $printRange = "A1:{$printAreaLastCol}{$printAreaLastRow}";
        // $sheet->getStyle($printRange)->getBorders()->getAllBorders()
        //     ->setBorderStyle(Border::BORDER_THIN)
        //     ->getColor()->setARGB('FF1F4E79');

        // Set page setup
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // ✅ TAMBAHKAN: Set margin ke 0
        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Set margin ke 0
        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Freeze hanya baris 1–7 untuk sheet Penilaian AK
        if ($sheet->getTitle() === 'Penilaian ' . ucfirst($this->penilaianFullName)) {
            $sheet->freezePane('A7'); // freeze baris 1–6
        }

        // // 🔒 LOCK semua cells dulu
        // $sheet->getStyle($sheet->calculateWorksheetDimension())
        //     ->getProtection()
        //     ->setLocked(Protection::PROTECTION_PROTECTED);

        // // ✅ UNLOCK signature section (jika ada) - SELURUH BAGIAN
        // if ($signatureStartRow !== null) {
        //     // Unlock dari signature start sampai last row, kolom B sampai I (lebih lebar untuk cover semua)
        //     $sheet->getStyle("B{$signatureStartRow}:I{$lastRow}")
        //         ->getProtection()
        //         ->setLocked(Protection::PROTECTION_UNPROTECTED);
        // }

        // // 🔐 AKTIFKAN SHEET PROTECTION
        // $sheet->getProtection()->setSheet(true);
        // $sheet->getProtection()->setPassword('lamdepilar');

        // // ✅ Permissions: Boleh resize, tidak boleh delete
        // $sheet->getProtection()->setFormatColumns(true);  // Boleh resize lebar kolom
        // $sheet->getProtection()->setFormatRows(true);     // Boleh resize tinggi baris
        // $sheet->getProtection()->setInsertColumns(false); // Tidak boleh insert kolom
        // $sheet->getProtection()->setDeleteColumns(false); // Tidak boleh delete kolom
        // $sheet->getProtection()->setInsertRows(false);    // Tidak boleh insert baris
        // $sheet->getProtection()->setDeleteRows(false);    // Tidak boleh delete baris
        // $sheet->getProtection()->setSort(false);          // Tidak boleh sort
        // $sheet->getProtection()->setAutoFilter(false);    // Tidak boleh autofilter
        // $sheet->getProtection()->setFormatCells(true);   // Tidak boleh format cells

        // // ✅ User bisa select locked & unlocked cells
        // $sheet->getProtection()->setSelectLockedCells(false);
        // $sheet->getProtection()->setSelectUnlockedCells(false);
    }

    /**
     * Build headers untuk sheet Penilaian (JENIS) - tanpa kolom indikator
     */
    private function buildPenilaianJenisHeaders($sheet, $asesmen, $asesors, $isTemplateOnly, bool $isSplitMode = false): void
    {
        // Hitung kolom terakhir berdasarkan jumlah asesor dan mode
        if ($isTemplateOnly) {
            $lastCol = chr(ord('F') + ($asesors->count() * 2)); // 2 kolom per asesor
        } else {
            $lastCol = chr(ord('F') + $asesors->count()); // 1 kolom per asesor
        }

        if ($isSplitMode && !$isTemplateOnly) {
            $lastCol = chr(ord($lastCol) + 2); // +2 kolom: Split & Keterangan Split
        }

        // ===== Title =====
        $sheet->mergeCells("B2:{$lastCol}2");
        $sheet->setCellValue('B2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("B3:{$lastCol}3");
        $sheet->setCellValue('B3', 'Tabel Penilaian ' . ucfirst($this->penilaianFullName) . ' Prodi ' . ($asesmen->studyProgram->name ?? ''));
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(15);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ===== Header row 5-6 =====
        $sheet->mergeCells('B5:C6');
        $sheet->setCellValue('B5', 'Kriteria');
        $sheet->mergeCells('D5:E6');
        $sheet->setCellValue('D5', 'Kode Elemen');
        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // Penilaian untuk setiap asesor
        $startCol = 'G';

        if ($isTemplateOnly) {
            // ===== TEMPLATE MODE: 2 kolom per asesor =====
            foreach ($asesors as $index => $asesor) {
                $col1 = chr(ord($startCol) + ($index * 2));
                $col2 = chr(ord($startCol) + ($index * 2) + 1);

                $sheet->mergeCells("{$col1}5:{$col2}5");
                $asesorName = $asesor->user->name ?? "Asesor " . ($index + 1);
                $sheet->setCellValue("{$col1}5", "Penilaian Asesor ({$asesorName})");

                $sheet->setCellValue("{$col1}6", JenjangPenilaian::PEMENUHAN_STANDAR);
                $sheet->setCellValue("{$col2}6", JenjangPenilaian::PELAMPAUAN_STANDAR);
            }
        } else {
            // ===== WITHDATA MODE: 1 kolom per asesor =====
            foreach ($asesors as $index => $asesor) {
                $col = chr(ord($startCol) + $index);

                $sheet->mergeCells("{$col}5:{$col}6");
                $asesorName = $asesor->user->name ?? "Asesor " . ($index + 1);
                $sheet->setCellValue("{$col}5", "Penilaian {$this->penilaianFullName}\n({$asesorName})");
            }

            if ($isSplitMode) {
                $splitCol = chr(ord($startCol) + $asesors->count());
                $ketCol   = chr(ord($splitCol) + 1);

                $sheet->mergeCells("{$splitCol}5:{$splitCol}6");
                $sheet->setCellValue("{$splitCol}5", "Split");

                $sheet->mergeCells("{$ketCol}5:{$ketCol}6");
                $sheet->setCellValue("{$ketCol}5", "Keterangan Split");
            }
        }

        // ===== Styling umum header =====
        $headerRange = "B5:{$lastCol}6";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('B5:F6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFFFFF');

        // $sheet->getStyle("G5:{$lastCol}6")->getFill()
        //     ->setFillType(Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FFD9EAD3');
        $sheet->getStyle("B5:{$lastCol}6")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');

        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Petunjuk pengisian
        $sheet->mergeCells("B4:{$lastCol}4");
        if ($isTemplateOnly)
            $sheet->setCellValue('B4', 'Hasil Penilaian (Terisi Otomatis dari Sheet Kertas Kerja Asesor)');
        $sheet->getStyle('B4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1F4E79'], 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);
    }

    /**
     * Render data rows untuk sheet Penilaian (JENIS)
     * - Asesor pertama: Formula + conditional formatting
     * - Asesor lainnya: Data dari database (hanya untuk non-template)
     */
    private function renderPenilaianJenisRows($sheet, Asesmen $asesmen, $penilaianName, $asesors, $isTemplateOnly, $kriterias = null)
    {
        $currentRow = 7;
        $penilaianAsesorRow = $currentRow + 2;

        $jenisAsesmen = strtolower($this->penilaianName);
        $relationName = $jenisAsesmen == 'al' ? 'penilaianElemenAl' : 'penilaianElemenAk';

        // Load kriteria dengan relasi penilaian jika withData mode
        if (!$isTemplateOnly && $kriterias === null) {
            $kriterias = Kriteria::with([
                'elemenStandar' => function ($q) {
                    $q->orderBy('kode_elemen');
                },
                "elemenStandar.{$relationName}" => function ($q) use ($asesors) {
                    $q->whereIn('id_asesor', $asesors->pluck('id_user'));
                },
                "elemenStandar.{$relationName}.asesor"
            ])->get();
        } elseif ($kriterias === null) {
            $kriterias = Kriteria::with([
                'elemenStandar' => function ($q) {
                    $q->orderBy('kode_elemen');
                }
            ])->get();
        }

        $globalNo = 1;
        foreach ($kriterias as $indexKriteria => $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();

            if ($elemenCount > 1) {
                $blockStartRow = $currentRow;
                $blockEndRow   = $currentRow + $elemenCount - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }

            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $templateRow = $currentRow;

                // Isi kriteria (hanya sekali per grup)
                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$templateRow}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$templateRow}", $kriteria->nama_kriteria);
                }

                // Isi elemen
                $sheet->setCellValue("D{$templateRow}", $globalNo);
                $sheet->setCellValue("E{$templateRow}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$templateRow}", $elemen->pernyataan_elemen);

                // Data penilaian untuk setiap asesor
                $startCol = 'G';

                if ($isTemplateOnly) {
                    // ===== TEMPLATE MODE: 2 kolom per asesor =====
                    foreach ($asesors as $asesorIndex => $asesor) {
                        $col1 = chr(ord($startCol) + ($asesorIndex * 2));     // Pemenuhan
                        $col2 = chr(ord($startCol) + ($asesorIndex * 2) + 1); // Pelampauan

                        if ($asesorIndex === 0) {
                            $asesorRowOffset = 1;

                            // Formula Pemenuhan Standar (kolom I-L)
                            $formulaPemenuhan = "=IFERROR(CONCATENATE(" .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$I:\$I,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")," .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$J:\$J,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")," .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$K:\$K,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")," .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$L:\$L,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")" .
                                "),\"\")";
                            $sheet->setCellValue("{$col1}{$templateRow}", $formulaPemenuhan);

                            // Formula Pelampauan Standar (kolom M)
                            $rowAsesor = $penilaianAsesorRow;
                            $formulaPelampauan = "=IF('Kertas Kerja {$penilaianName} Asesor'!M{$rowAsesor}=\"\", \"\", 'Kertas Kerja {$penilaianName} Asesor'!M{$rowAsesor})";
                            $sheet->setCellValue("{$col2}{$templateRow}", $formulaPelampauan);
                        }
                    }

                    $lastCol = chr(ord($startCol) + ($asesors->count() * 2) - 1);
                } else {
                    // ===== WITHDATA MODE: 1 kolom per asesor =====
                    foreach ($asesors as $asesorIndex => $asesor) {
                        $col = chr(ord($startCol) + $asesorIndex);

                        // ASESOR PERTAMA: Formula I-M (semua skor 0-4)
                        if ($asesorIndex === 0) {
                            $asesorRowOffset = 1;

                            $formulaPenilaian = "=IFERROR(CONCATENATE(" .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$I:\$I,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")," .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$J:\$J,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")," .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$K:\$K,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")," .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$L:\$L,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")," .
                                "IFERROR(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$M:\$M,MATCH(E{$templateRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}),\"\")" .
                                "),\"\")";
                            $sheet->setCellValue("{$col}{$templateRow}", $formulaPenilaian);
                        }
                        // ASESOR LAINNYA: Data dari DB
                        elseif ($asesorIndex > 0) {
                            $penilaian = $elemen->{$relationName}
                                ->where('id_asesor', $asesor->id_user)
                                ->first();

                            if ($penilaian && $penilaian->skor !== null) {
                                $skor = (int) $penilaian->skor;
                                $komentar = $penilaian->komentar ?? '';

                                if ($this->useColorFormatting) {

                                    // Get warna berdasarkan skor
                                    $bgColor = $this->getSkorColor($skor);
                                    $textColor = $this->getTextColorByBg($bgColor);

                                    $sheet->setCellValue("{$col}{$templateRow}", $komentar);

                                    // Apply warna
                                    $sheet->getStyle("{$col}{$templateRow}")
                                        ->getFill()
                                        ->setFillType(Fill::FILL_SOLID)
                                        ->getStartColor()
                                        ->setARGB($this->hexToArgb($bgColor));
                                } else {
                                    // ✅ MODE TEXT-ONLY: Background putih, format "Kategori: X - ...\n[komentar]"
                                    $formattedText = $this->formatPenilaianText($skor, $komentar);

                                    $sheet->setCellValue("{$col}{$templateRow}", $formattedText);

                                    // Style: Background putih, text hitam
                                    $sheet->getStyle("{$col}{$templateRow}")
                                        ->getFill()
                                        ->setFillType(Fill::FILL_SOLID)
                                        ->getStartColor()
                                        ->setARGB('FFFFFFFF');
                                }
                            } else {
                                $sheet->setCellValue("{$col}{$templateRow}", '');
                            }
                        }
                    }

                    $lastCol = chr(ord($startCol) + $asesors->count() - 1);
                }

                // Apply styling
                $this->applyRowStyling($sheet, $currentRow, null, 'B', $lastCol);
                $sheet->getStyle("B{$currentRow}:H{$currentRow}")
                    ->getFont()
                    ->setSize(15);

                // Alignment dan wrap text
                $sheet->getStyle("B{$currentRow}:{$lastCol}{$currentRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);
                $sheet->getStyle("B{$currentRow}:F{$currentRow}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FF31869B');

                $currentRow++;
                $globalNo++;
                $penilaianAsesorRow += 2;
                $isFirstElemen = false;
            }
        }

        // Apply conditional formatting HANYA untuk asesor pertama (kolom G-H)
        $this->applyPenilaianJenisConditionalFormatting($sheet, $penilaianName, 7, $currentRow - 1, $isTemplateOnly);
        return $currentRow - 1;
    }

    /**
     * Apply conditional formatting HANYA untuk asesor pertama (kolom G-H)
     */
    private function applyPenilaianJenisConditionalFormatting($sheet, string $penilaianName, int $startRow, int $endRow, $isTemplateOnly): void
    {
        // ✅ Hanya apply conditional formatting jika mode warna aktif
        if (!$this->useColorFormatting && !$isTemplateOnly) {
            return; // Skip conditional formatting untuk mode text-only
        }
        $asesorRowOffset = 1;

        if ($isTemplateOnly) {
            // ===== TEMPLATE MODE: 2 kolom (G=Pemenuhan, H=Pelampauan) =====
            $col1 = 'G';
            $col2 = 'H';

            // Pemenuhan Standar (Kolom G) - Skor 0-3
            $condL = new Conditional();
            $condL->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condL->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$L:\$L,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condL->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFdcedc8');
            // $condL->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF1F4E79');

            $condK = new Conditional();
            $condK->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condK->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$K:\$K,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condK->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFfff9c4');
            // $condK->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF1F4E79');

            $condJ = new Conditional();
            $condJ->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condJ->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$J:\$J,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condJ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFffe0b2');
            // $condJ->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF1F4E79');

            $condI = new Conditional();
            $condI->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condI->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$I:\$I,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condI->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFf5c6cb');
            // $condI->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');

            $sheet->getStyle("{$col1}{$startRow}:{$col1}{$endRow}")
                ->setConditionalStyles([$condL, $condK, $condJ, $condI]);

            // Pelampauan Standar (Kolom H) - Skor 4
            $condM = new Conditional();
            $condM->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condM->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$M:\$M,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condM->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFc8e6c9');
            // $condM->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');

            $sheet->getStyle("{$col2}{$startRow}:{$col2}{$endRow}")
                ->setConditionalStyles([$condM]);
        } else {
            // ===== WITHDATA MODE: 1 kolom (G) mencakup semua skor 0-4 =====
            $col = 'G';

            // Skor 4 - Dark Green (prioritas tertinggi)
            $condM = new Conditional();
            $condM->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condM->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$M:\$M,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condM->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFc8e6c9');

            // Skor 3 - Light Green
            $condL = new Conditional();
            $condL->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condL->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$L:\$L,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condL->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFdcedc8');

            // Skor 2 - Yellow
            $condK = new Conditional();
            $condK->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condK->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$K:\$K,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condK->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFfff9c4');

            // Skor 1 - Orange
            $condJ = new Conditional();
            $condJ->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condJ->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$J:\$J,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condJ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFffe0b2');

            // Skor 0 - Red
            $condI = new Conditional();
            $condI->setConditionType(Conditional::CONDITION_EXPRESSION);
            $condI->addCondition("=LEN(INDEX('Kertas Kerja {$penilaianName} Asesor'!\$I:\$I,MATCH(\$E{$startRow},'Kertas Kerja {$penilaianName} Asesor'!\$E:\$E,0)+{$asesorRowOffset}))>0");
            $condI->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFf5c6cb');

            // Apply semua conditional dengan urutan prioritas M -> L -> K -> J -> I
            $sheet->getStyle("{$col}{$startRow}:{$col}{$endRow}")
                ->setConditionalStyles([$condM, $condL, $condK, $condJ, $condI]);
        }
    }

    /**
     * Build headers untuk personal assessment (1 kolom penilaian)
     */
    private function buildPersonalHeaders($sheet, $asesmen, $asesor): void
    {
        // Title
        $sheet->mergeCells('B2:G2');
        $sheet->setCellValue('B2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('B3:G3');
        $sheet->setCellValue('B3', 'Tabel Penilaian ' . ucfirst($this->penilaianFullName) . ' Prodi ' . ($asesmen->studyProgram->name ?? ''));
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header row 5-6
        $sheet->mergeCells('B5:C6');
        $sheet->setCellValue('B5', 'Kriteria');

        $sheet->mergeCells('D5:E6');
        $sheet->setCellValue('D5', 'Kode Elemen');

        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // Kolom Penilaian (1 kolom saja)
        $sheet->mergeCells('G5:G6');
        // $asesorName = $asesor->user->name ?? 'Asesor';
        $sheet->setCellValue('G5', "Penilaian {$this->penilaianFullName}");

        // Styling
        $sheet->getStyle('B5:G6')->getFont()->setBold(true);
        $sheet->getStyle('B5:G6')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle('B5:G6')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('B5:F6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFFFFF');

        $sheet->getStyle('B5:G6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            // ->getStartColor()->setARGB('FFD9EAD3');
            ->getStartColor()->setARGB('FFD9D9D9');

        $sheet->getStyle('B5:G6')->getFont()->setSize(14);

        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Petunjuk
        $sheet->mergeCells('B4:G4');
        // $sheet->setCellValue('B4', 'Hasil Penilaian Personal');
        $sheet->getStyle('B4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF0000FF'], 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);
    }

    /**
     * Render rows untuk personal assessment (1 kolom, data dari DB)
     */
    private function renderPersonalRows($sheet, Asesmen $asesmen, int $userId, $asesor): int
    {
        $currentRow = 7;
        $jenisAsesmen = strtolower($this->penilaianName);
        $relationName = $jenisAsesmen == 'al' ? 'penilaianElemenAl' : 'penilaianElemenAk';

        // Load kriteria dengan penilaian
        $kriterias = Kriteria::with([
            'elemenStandar' => function ($q) {
                $q->orderBy('kode_elemen');
            },
            "elemenStandar.{$relationName}" => function ($q) use ($userId) {
                $q->where('id_asesor', $userId);
            }
        ])->get();

        $globalNo = 1;
        foreach ($kriterias as $indexKriteria => $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();

            if ($elemenCount > 1) {
                $blockStartRow = $currentRow;
                $blockEndRow = $currentRow + $elemenCount - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }

            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $row = $currentRow;

                // Kriteria (hanya sekali per grup)
                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$row}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$row}", $kriteria->nama_kriteria);
                }

                // Elemen
                $sheet->setCellValue("D{$row}", $globalNo);
                $sheet->setCellValue("E{$row}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$row}", $elemen->pernyataan_elemen);

                // Penilaian dari DB
                $penilaian = $elemen->{$relationName}->first();

                if ($penilaian && $penilaian->skor !== null) {
                    $skor = (int) $penilaian->skor;
                    $komentar = $penilaian->komentar ?? '';

                    if ($this->useColorFormatting) {
                        // ✅ MODE WARNA
                        $bgColor = $this->getSkorColor($skor);
                        // $textColor = $this->getTextColorByBg($bgColor);

                        $sheet->setCellValue("G{$row}", $komentar);

                        $sheet->getStyle("G{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB($this->hexToArgb($bgColor));

                        // $sheet->getStyle("G{$row}")
                        //     ->getFont()
                        //     ->setBold(true)
                        //     ->getColor()
                        //     ->setARGB($this->hexToArgb($textColor));
                    } else {
                        // ✅ MODE TEXT-ONLY
                        $formattedText = $this->formatPenilaianText($skor, $komentar);

                        $sheet->setCellValue("G{$row}", $formattedText);

                        $sheet->getStyle("G{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FFFFFFFF');

                        // $sheet->getStyle("G{$row}")
                        //     ->getFont()
                        //     ->setBold(false)
                        //     ->getColor()
                        //     ->setARGB('FF1F4E79');
                    }
                } else {
                    $sheet->setCellValue("G{$row}", '');
                }

                // Styling
                $this->applyRowStyling($sheet, $row, null, 'B', 'G');
                $sheet->getStyle("B{$row}:G{$row}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);

                // SET FONT ISI
                $sheet->getStyle("B{$row}:F{$row}")
                    ->getFont()->setSize(14)
                    ->getColor()
                    ->setARGB('FF31869B');

                $sheet->getStyle("G{$row}")
                    ->getFont()
                    ->setSize(14);

                $currentRow++;
                $globalNo++;
                $isFirstElemen = false;
            }
        }

        return $currentRow - 1;
    }

    private function setTanggalCetak($sheet, string $range = 'B1:F1'): void
    {
        $sheet->mergeCells($range);

        // pakai timezone app (Laravel) kalau mau konsisten
        $tglCetak = now()->locale('id')->translatedFormat('d-m-Y H:i:s');

        $startCell = explode(':', $range)[0]; // "B1"
        $sheet->setCellValue($startCell, "Tanggal cetak: {$tglCetak}");

        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['argb' => 'FF1F4E79'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(18);
    }

    /**
     * Convert hex color to ARGB format untuk PhpSpreadsheet
     */
    private function hexToArgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        return 'FF' . strtoupper($hex);
    }

    /**
     * Get background color based on skor
     */
    private function getSkorColor(int $skor): string
    {
        return \App\Models\JenjangPenilaian::getSkorColor($skor);
    }

    /**
     * Get text color based on background color
     */
    private function getTextColorByBg(string $hex): string
    {
        return '#000';
        // return \App\Models\JenjangPenilaian::textColorByBg($hex);
    }

    /**
     * Build signature section (khusus untuk Penilaian AL)
     */
    private function buildSignatureSection($sheet, $asesmen, $asesors, $startRow, string $endCol = 'G'): array
    {
        // =========================
        // LAST COLUMN AUTO (BERDASARKAN COUNT ASESOR)
        // 1 -> G, 2 -> H, 3 -> I, dst
        // =========================
        $asesorCount = $asesors->count();
        $autoLastCol = Coordinate::stringFromColumnIndex(7 + max(0, $asesorCount - 1)); // 7 = G
        $lastCol = $endCol ?: $autoLastCol;

        $signatureStartRow = $startRow + 3;
        $currentRow = $signatureStartRow;

        // ===== Header: Mengetahui & menyetujui =====
        $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
        $sheet->setCellValue("B{$currentRow}", "Mengetahui & menyetujui");
        $sheet->getStyle("B{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);
        $currentRow++;

        // ===== Kota dan Tanggal =====
        $kota = 'Kota Penilaian';
        $tanggal = \App\Libraries\Date::tglIndo(now());

        $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
        $sheet->setCellValue("B{$currentRow}", "[{$kota}], {$tanggal}");
        $sheet->getStyle("B{$currentRow}")->applyFromArray([
            'font' => ['size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $currentRow += 2; // jarak

        // ===== Siapkan data asesor =====
        $asesorData = [];
        foreach ($asesors as $index => $asesor) {
            $asesorData[] = [
                'label' => 'Asesor ' . ($index + 1),
                'nama'  => $asesor->user->name ?? 'Asesor ' . ($index + 1),
                'nip'   => $asesor->user->nip ?? '1234'
            ];
        }

        // =========================================================
        // ASESOR VERTIKAL
        // =========================================================
        $ttdHeight = 9;
        $asesorStartRow = $currentRow;
        $asesorCol = 'B';
        $asesorRowCount = count($asesorData);

        // Track NIP row paling bawah asesor (buat dotted stop)
        $lastAsesorNipRow = $asesorStartRow; // fallback

        for ($i = 0; $i < $asesorRowCount; $i++) {
            $data = $asesorData[$i];

            $rowForThisAsesor = $asesorStartRow + ($i * $ttdHeight);

            // Label
            $sheet->setCellValue("{$asesorCol}{$rowForThisAsesor}", $data['label']);
            $sheet->getStyle("{$asesorCol}{$rowForThisAsesor}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 13,
                    'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            // Nama
            $nameRow = $rowForThisAsesor + 5;
            $sheet->setCellValue("{$asesorCol}{$nameRow}", $data['nama']);
            $sheet->getStyle("{$asesorCol}{$nameRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 13],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            // NIP
            $nipRow = $nameRow + 1;
            $sheet->setCellValue("{$asesorCol}{$nipRow}", "NIP. " . $data['nip']);
            $sheet->getStyle("{$asesorCol}{$nipRow}")->applyFromArray([
                'font' => ['size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            $lastAsesorNipRow = $nipRow; // update tiap iterasi, yang terakhir = terbawah
        }

        // =========================================================
        // KAPRODI & DEKAN (kanan)
        // =========================================================
        $kaprodiCol = 'G';
        $kaprodiRow = $asesorStartRow;

        $degreeLevelName = $asesmen->studyProgram->degreeLevel->alias ?? '';
        $kaprodiLabel = "Kaprodi {$degreeLevelName}";

        $sheet->setCellValue("{$kaprodiCol}{$kaprodiRow}", $kaprodiLabel);
        $sheet->getStyle("{$kaprodiCol}{$kaprodiRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $kaprodiNameRow = $kaprodiRow + 5;
        $sheet->setCellValue("{$kaprodiCol}{$kaprodiNameRow}", "Nama Kaprodi {$degreeLevelName}");
        $sheet->getStyle("{$kaprodiCol}{$kaprodiNameRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $kaprodiNipRow = $kaprodiNameRow + 1;
        $sheet->setCellValue("{$kaprodiCol}{$kaprodiNipRow}", "NIP. 1234");
        $sheet->getStyle("{$kaprodiCol}{$kaprodiNipRow}")->applyFromArray([
            'font' => ['size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        // Dekan
        $dekanRow = $kaprodiNipRow + 3;
        $sheet->setCellValue("{$kaprodiCol}{$dekanRow}", "Dekan");
        $sheet->getStyle("{$kaprodiCol}{$dekanRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $dekanNameRow = $dekanRow + 5;
        $sheet->setCellValue("{$kaprodiCol}{$dekanNameRow}", "Nama Dekan");
        $sheet->getStyle("{$kaprodiCol}{$dekanNameRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $dekanNipRow = $dekanNameRow + 1;
        $sheet->setCellValue("{$kaprodiCol}{$dekanNipRow}", "NIP. 1234");
        $sheet->getStyle("{$kaprodiCol}{$dekanNipRow}")->applyFromArray([
            'font' => ['size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        // =========================================================
        // FINAL ROW: maksimal 2 baris di bawah NIP terbawah
        // =========================================================
        $lowestNipRow = max($lastAsesorNipRow, $dekanNipRow);
        $finalRow = $lowestNipRow + 2;

        // =========================================================
        // BORDER DOTTED
        // =========================================================
        $startDottedRow = $startRow + 1;
        $sheet->getStyle("B{$startDottedRow}:{$lastCol}{$finalRow}")->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_DOTTED,
                    'color' => ['argb' => 'FF1F4E79'],
                ],
            ],
        ]);

        return [$finalRow, $signatureStartRow];
    }

    private function getAsesors($asesmen, $isTemplateOnly)
    {
        // Ambil semua asesor untuk asesmen ini
        $jenisAsesmen = strtolower($this->penilaianName);
        $asesorsQuery = \App\Models\AsesmenUserRole::where('id_asesmen', $asesmen->id)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->with('user')
            ->orderBy('urutan_asesor');

        // Jika template only, ambil hanya 1 asesor pertama
        if ($isTemplateOnly) {
            $asesorsQuery->limit(1);
        }

        $asesors = $asesorsQuery->get();
        return $asesors;
    }

    private function getFirstAsesorUserId(Asesmen $asesmen): ?int
    {
        $jenisAsesmen = strtolower($this->penilaianName);

        $firstAsesor = \App\Models\AsesmenUserRole::where('id_asesmen', $asesmen->id)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->with('user')
            ->orderBy('urutan_asesor')
            ->first();

        return $firstAsesor?->id_user;
    }

    /**
     * Format text penilaian tanpa warna: "Kategori: [skor] - [nama]\n[komentar]"
     */
    private function formatPenilaianText(int $skor, string $komentar): string
    {
        // $kategori = \App\Models\JenjangPenilaian::getSkorLabelAttribute($skor);
        return $komentar;
    }

    private function addLogoAndZoom(
        $sheet,
        int $zoomScale = 60,
        string $coordinates = 'B2',
        ?string $endCell = null,
        int $logoHeight = 40
    ): void {
        $sheet->getSheetView()->setZoomScale($zoomScale);

        $path = public_path('assets/images/logo.png');
        if (!file_exists($path)) {
            return;
        }

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath($path);

        $drawing->setResizeProportional(true);
        $drawing->setHeight($logoHeight);

        if ($endCell !== null) {
            [$colStart, $rowStart] = Coordinate::coordinateFromString($coordinates);
            [$colEnd,   $rowEnd]   = Coordinate::coordinateFromString($endCell);

            $startColIdx = Coordinate::columnIndexFromString($colStart);
            $endColIdx   = Coordinate::columnIndexFromString($colEnd);

            // default font (wajib untuk konversi width excel -> px)
            $defaultFont = $sheet->getParent()->getDefaultStyle()->getFont();

            // Total width area (px) - pakai width final kolom (fallback ke default)
            $totalWidthPx = 0;
            for ($col = $startColIdx; $col <= $endColIdx; $col++) {
                $letter = Coordinate::stringFromColumnIndex($col);

                $w = $sheet->getColumnDimension($letter)->getWidth();
                if ($w <= 0) {
                    $w = $sheet->getDefaultColumnDimension()->getWidth();
                }

                $totalWidthPx += SharedDrawing::cellDimensionToPixels($w, $defaultFont);
            }

            // Total height area (px) - pakai height final row (fallback ke default)
            $totalHeightPx = 0;
            for ($row = $rowStart; $row <= $rowEnd; $row++) {
                $h = $sheet->getRowDimension($row)->getRowHeight();
                if ($h <= 0) {
                    $h = $sheet->getDefaultRowDimension()->getRowHeight();
                    if ($h <= 0) $h = 15;
                }

                $totalHeightPx += SharedDrawing::pointsToPixels($h);
            }

            // Ukuran logo (px) dari file asli + target height $logoHeightpx
            $targetHeightPx = $logoHeight;
            $logoWidth = 120;
            $logoHeight = $logoHeight;

            $imgSize = @getimagesize($path);
            if ($imgSize !== false) {
                [$imgW, $imgH] = $imgSize;
                if ($imgH > 0) {
                    $scale = $targetHeightPx / $imgH;
                    $logoWidth  = (int) round($imgW * $scale);
                    $logoHeight = (int) round($imgH * $scale);
                }
            }

            // Anchor di startCell, offset ke tengah area
            $drawing->setCoordinates($coordinates);
            $drawing->setOffsetX((int) round(($totalWidthPx  - $logoWidth)  / 2));
            $drawing->setOffsetY((int) round(($totalHeightPx - $logoHeight) / 2));

            // Biar behave seperti "di-merge area" (bergerak & ikut ukuran cell)
            $drawing->setEditAs(\PhpOffice\PhpSpreadsheet\Worksheet\Drawing::EDIT_AS_ONECELL);
        } else {
            // JANGAN DIUBAH (sesuai request)
            $drawing->setCoordinates($coordinates);
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(2);
        }

        $drawing->setWorksheet($sheet);
    }
}
