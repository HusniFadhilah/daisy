<?php

namespace Database\Seeders;

use App\Models\Kriteria;
use App\Models\DegreeLevel;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use Illuminate\Database\Seeder;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\DocProtect;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use App\Models\DatasetSuplemen;

class BorangExampleSeeder extends Seeder
{
    protected PhpWord $phpWord;

    protected array $numberingRegistered = [];
    protected array $borangDataMap = [];
    protected ?DegreeLevel $activeDegreeLevel = null;
    protected int $currentTableWidthTwips = 8900;
    protected bool $isLandscape = false;
    protected bool $hasRestartedContentNumbering = false;

    protected int $pageHeightTwips = 16838;        // A4 portrait height
    protected int $contentHeightTwips = 14838;     // height - (marginTop+marginBottom)
    protected int $marginTopTwips = 1000;
    protected int $marginBottomTwips = 1000;
    protected string $templateBlue = '1F4E79';

    public function run(): void
    {
        // default behavior kalau dipanggil via db:seed
        $degreeLevels = DegreeLevel::all();

        if ($degreeLevels->isEmpty()) {
            $this->command?->warn('Degree level belum ada di database.');
            return;
        }

        foreach ($degreeLevels as $level) {
            $this->runForDegree($level);
        }
    }

    public function runWithCodes(array $codes): void
    {
        $codes = collect($codes)
            ->map(fn($s) => strtolower(trim((string) $s)))
            ->filter()
            ->values()
            ->all();

        $degreeLevels = DegreeLevel::whereIn('code', $codes)->get();

        if ($degreeLevels->isEmpty()) {
            $this->command?->warn('Degree level tidak ditemukan untuk opsi tersebut.');
            return;
        }

        foreach ($degreeLevels as $level) {
            $this->runForDegree($level);
        }
    }

    public function runForDegree(DegreeLevel $degreeLevel): void
    {
        $this->activeDegreeLevel = $degreeLevel;
        $this->phpWord = new PhpWord();
        $this->numberingRegistered = [];
        $this->phpWord->getSettings()->setUpdateFields(true);

        $this->phpWord->addTitleStyle(1, ['bold' => true, 'size' => 14, 'color' => $this->templateBlue], ['spaceAfter' => 240]);
        $this->phpWord->addTitleStyle(2, ['bold' => true, 'size' => 11, 'color' => $this->templateBlue], ['spaceAfter' => 180]);
        $this->phpWord->addTitleStyle(3, ['bold' => false, 'size' => 11, 'color' => $this->templateBlue], ['spaceAfter' => 120]);

        // ✅ FONT Montserrat 11PT (SEPERTI TEMPLATE)
        $this->phpWord->setDefaultFontName('Montserrat');
        $this->phpWord->setDefaultFontSize(11);

        $this->addCoverPage();
        $this->addLembarPengesahan();
        $this->addKataPengantarSection();
        $this->addRingkasanSection();
        $this->addDaftarIsiSection();
        $this->addContentPages();
        $this->addSuplemenSection();
        // ✅ Add basic protection
        // $this->protectDocumentBasic();

        // ✅ Save protection instructions
        $this->saveProtectionInstructions($degreeLevel->code);

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $safeCode = $degreeLevel->code; // contoh: "s2-terapan" jadi "s2_terapan"
        $filePath = storage_path("app/public/templates/TEMPLATE_LAPORAN_EVALUASI_DIRI_{$safeCode}.docx");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $objWriter->save($filePath);

        $this->command->info("✅ Template DOCX dibuat untuk {$safeCode}: " . $filePath);
    }

    private function addCoverPage()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);

        $section->addText(
            'LAPORAN EVALUASI DIRI',
            ['size' => 16, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(6);

        // Logo placeholder
        $section->addText(
            '[LOGO UNIVERSITAS]',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(5);

        $section->addText(
            'PROGRAM STUDI',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(1);

        $section->addText(
            '[NAMA PROGRAM STUDI]',
            ['size' => 14],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(8);

        $section->addText(
            'UNIVERSITAS/INSTITUT ..................................',
            ['size' => 12],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['alignment' => Jc::CENTER]);
        $textRun->addText('Bulan, ', ['size' => 12]);
        $textRun->addText('Tahun', ['size' => 12]);
    }

    private function addLembarPengesahan()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);
        $this->applyFooterPageNumber($section, 'roman', 1);

        // Judul
        $section->addText(
            'LEMBAR PENGESAHAN',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );

        // Style table (tanpa border)
        $this->phpWord->addTableStyle('FormTable', [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 0,
            'cellMarginRight' => 0,
        ]);

        // ===== BLOK 1: Lembaga Penjaminan Mutu =====
        $section->addText('Lembaga Penjaminan Mutu', ['size' => 11, 'bold' => true], ['spaceAfter' => 120]);

        $table1 = $section->addTable('FormTable');
        $this->addRow($table1, 'Universitas/Institut');
        $this->addRow($table1, 'Lembaga Penjaminan Mutu');
        $this->addRow($table1, 'Telp');
        $this->addRow($table1, 'Mobile telp dan/atau WA');
        $this->addRow($table1, 'Alamat Email');

        $section->addTextBreak(1);

        // ===== BLOK 2: Program Studi Akreditasi =====
        $section->addText('Program Studi Akreditasi', ['size' => 11, 'bold' => true], ['spaceAfter' => 120]);

        $table2 = $section->addTable('FormTable');
        $this->addRow($table2, 'Program Studi');
        $this->addRow($table2, 'Akreditasi');
        $this->addRow($table2, 'Ketua Tim Akreditasi');
        $this->addRow($table2, 'Telp');
        $this->addRow($table2, 'Mobile telp dan/atau WA');
        $this->addRow($table2, 'Alamat Email');

        // Spacer besar supaya tanda tangan turun ke bawah
        $section->addTextBreak(6);

        // ===== BLOK TANDA TANGAN (KANAN BAWAH) =====
        // Pakai table 1 baris 2 kolom: kiri kosong, kanan isi tanda tangan
        $this->phpWord->addTableStyle('SignTable', [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMarginLeft' => 0,
            'cellMarginRight' => 0,
        ]);

        $signTable = $section->addTable('SignTable');
        $signTable->addRow();

        // Kolom kiri kosong (untuk mendorong konten ke kanan)
        $signTable->addCell(5200)->addText(' ');

        // Kolom kanan (isi tanda tangan)
        $cell = $signTable->addCell(3500);

        $run = $cell->addTextRun(['alignment' => Jc::START, 'spaceAfter' => 120]);
        $run->addText('Kota, tanggal, Bulan, Tahun');

        $cell->addTextBreak(1);

        $run2 = $cell->addTextRun(['spaceAfter' => 120]);
        $run2->addText('Ttd dan stemp', ['color' => 'bbbbbb', 'italic' => true]);

        $cell->addTextBreak(1);

        $cell->addText('Ketua Penjaminan Mutu Universitas/UPPS', ['size' => 11, 'color' => 'bbbbbb', 'italic' => true], ['spaceAfter' => 700]);

        $cell->addText('Nama :', ['size' => 11], ['spaceAfter' => 80]);
        $cell->addText('NIP   :', ['size' => 11]);
    }

    private function addKataPengantarSection()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);
        $this->applyFooterPageNumber($section, 'roman');

        // Judul
        $section->addText(
            'KATA PENGANTAR',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );

        // ✅ Default/placeholder kata pengantar (4 paragraf)
        $defaultKataPengantar = [
            'Puji syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa atas tersusunnya laporan ini sebagai bagian dari dokumentasi dan evaluasi kinerja Program Studi. Laporan ini disusun untuk memberikan gambaran menyeluruh mengenai capaian akademik, penelitian, pengabdian kepada masyarakat, serta kinerja mahasiswa dan dosen selama beberapa tahun terakhir.',
            'Laporan ini memuat berbagai data terkait penerimaan mahasiswa, capaian pembelajaran, prestasi akademik, kegiatan penelitian dan pengabdian kepada masyarakat, serta kerja sama dengan pihak eksternal. Seluruh data disajikan secara sistematis dan berdasarkan catatan administrasi Program Studi, dengan harapan dapat menjadi bahan evaluasi dan perbaikan dalam rangka peningkatan mutu pendidikan.',
            'Kami menyadari bahwa penyusunan laporan ini tidak lepas dari dukungan berbagai pihak. Oleh karena itu, kami menyampaikan apresiasi dan terima kasih kepada seluruh dosen, tenaga kependidikan, mahasiswa, serta mitra kerja yang telah berkontribusi dalam pengumpulan data dan penyusunan laporan ini.',
            'Akhir kata, semoga laporan ini dapat memberikan manfaat sebagai sarana transparansi, evaluasi, dan peningkatan mutu Program Studi di masa yang akan datang.',
        ];

        // ✅ Kotak + teks bawaan
        $this->addFrontMatterDescBox($section, 'Kata Pengantar', 500, $defaultKataPengantar, false);

        // Penutup (kanan bawah)
        $section->addText(
            '[Nama Kota], [Tanggal Penyusunan]',
            ['size' => 11],
            ['alignment' => Jc::END, 'spaceBefore' => 400, 'spaceAfter' => 100]
        );

        $section->addText(
            'Ketua Program Studi',
            ['size' => 11],
            ['alignment' => Jc::END, 'spaceAfter' => 900]
        );

        $section->addText(
            '[Nama Ketua Program Studi]',
            ['size' => 11, 'bold' => true],
            ['alignment' => Jc::END]
        );
    }

    private function addRingkasanSection()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);
        $this->applyFooterPageNumber($section, 'roman');

        $section->addText(
            'RINGKASAN',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );

        // ✅ Kotak input seperti deskripsi box (max 1000 kata)
        $this->addFrontMatterDescBox(
            $section,
            'Ringkasan (Mohon jangan dihapus)',
            1000,
            [],                      // tidak ada prefill
            true,                    // fullHeight = true
            1800,                    // reservedTopTwips (boleh adjust)
            3000,                    // minBoxHeightTwips
            "[Mohon isi ringkasan di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]"
        );
    }

    private function addContentPages()
    {
        $kriterias = Kriteria::with([
            'elemenStandar.pernyataan',
            'elemenStandar.indikator',
            'elemenStandar.datasetBorang'
        ])->get();

        foreach ($kriterias as $indexKriteria => $kriteria) {

            // Section utama untuk teks kriteria + pernyataan + indikator (portrait)
            $restartNumbering = $indexKriteria == 0 && !$this->hasRestartedContentNumbering;
            $section = $this->createPortraitSection('arabic', $restartNumbering);

            // Header kriteria
            $section->addTitle($kriteria->kode_kriteria . '. ' . $kriteria->nama_kriteria, 1);

            $section->addText(
                '(' . $this->getKriteriaSubtitle($kriteria->kode_kriteria) . ')',
                ['size' => 11, 'italic' => true, 'color' => $this->templateBlue],
                ['spaceAfter' => 200]
            );

            $countElemen = $kriteria->elemenStandar->count();

            foreach ($kriteria->elemenStandar as $key => $elemen) {

                $isLastElemen = ($key + 1 === $countElemen);

                // Judul elemen
                $section->addTitle(($key + 1) . '. ' . $elemen->kode_elemen . '. ' . $elemen->pernyataan_elemen, 2);

                // Pernyataan standar + indikator
                $this->addPernyataanStandar($section, $elemen);
                $section->addTextBreak(1);
                $this->addUraianIndikator($section, $elemen);

                // ✅ HAPUS pagebreak manual di sini
                // $section->addPageBreak();

                // ✅ Buat detail section (portrait/landscape) untuk ElemenBox + Deskripsi + tabel menyatu
                $detailSection = $this->elemenNeedsLandscape($elemen)
                    ? $this->createLandscapeSection('arabic')
                    : $this->createPortraitSection('arabic');

                $this->addElemenBox($detailSection, $kriteria, $elemen);
                $detailSection->addTextBreak(0.5);
                $this->addDeskripsiBoxUnified($detailSection, $kriteria, $elemen);

                // ✅ PENTING: Jangan buat section baru kalau ini elemen terakhir,
                // karena akan menghasilkan halaman kosong.
                if (!$isLastElemen) {
                    // Siapkan section portrait baru untuk elemen berikutnya
                    $section = $this->createPortraitSection();

                    // (Opsional) Anda bisa tulis header kriteria lagi, kalau Anda ingin muncul di tiap halaman
                    $section->addText(
                        $kriteria->kode_kriteria . '. ' . $kriteria->nama_kriteria,
                        ['size' => 11, 'bold' => true, 'color' => $this->templateBlue],
                        ['spaceAfter' => 100]
                    );
                    $section->addText(
                        '(' . $this->getKriteriaSubtitle($kriteria->kode_kriteria) . ')',
                        ['size' => 11, 'italic' => true, 'color' => $this->templateBlue],
                        ['spaceAfter' => 200]
                    );
                }
            }
        }
    }

    /**
     * ✅ Kotak kecil untuk elemen
     */
    private function addElemenBox($section, $kriteria, $elemen)
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'width' => 100 * 50, // Full width
            'unit' => 'pct'
        ]);

        $table->addRow();
        $cell = $table->addCell(9500);
        $cell->addText(
            $kriteria->kode_kriteria . '. ' . $kriteria->nama_kriteria,
            ['size' => 11, 'bold' => true],
            ['spaceAfter' => 100]
        );
        $cell->addText(
            $elemen->kode_elemen . '. ' . $elemen->pernyataan_elemen,
            ['size' => 11, 'bold' => true]
        );
    }

    /**
     * ✅ PERBAIKAN: Apply Footer dengan Page Number
     *
     * @param \PhpOffice\PhpWord\Element\Section $section
     * @param string $type 'roman' atau 'arabic' atau 'none'
     * @param int|null $restartAt Restart numbering dari angka ini (opsional)
     */
    private function applyFooterPageNumber(
        \PhpOffice\PhpWord\Element\Section $section,
        string $type = 'arabic',
        ?int $restartAt = null
    ): void {
        if ($type === 'none') {
            return; // tidak ada footer
        }

        // ✅ Restart page numbering jika diminta
        if ($restartAt !== null) {
            $section->getStyle()->setPageNumberingStart($restartAt);
        }

        // ✅ Set format numbering (roman atau arabic)
        if ($type === 'roman') {
            // PhpWord tidak punya direct method untuk roman/arabic via PHP
            // tapi kita bisa pakai field code yang benar
            $footer = $section->addFooter();
            $footer->addPreserveText(
                '{PAGE \* ROMAN}', // ✅ Format Roman
                ['size' => 10],
                ['alignment' => Jc::CENTER]
            );
        } else {
            // Default: Arabic numerals
            $footer = $section->addFooter();
            $footer->addPreserveText(
                '{PAGE}', // ✅ Format Arabic
                ['size' => 10],
                ['alignment' => Jc::CENTER]
            );
        }
    }
    /**
     * Deskripsi box + tabel, semuanya dirender di SECTION YANG SAMA.
     * Tidak ada switching portrait/landscape di sini.
     */
    private function addDeskripsiBoxUnified($section, $kriteria, $elemen)
    {
        $hasTable = $elemen->datasetBorang && $elemen->datasetBorang->contains(fn($d) => $d->tipe_field === 'table');

        // ✅ kalau tidak ada table dataset: bikin 1 halaman penuh
        if (!$hasTable) {
            $this->addDeskripsiBoxFullPage($section, $kriteria, $elemen);
            return;
        }

        // Kotak deskripsi (border)
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 100,
            'width' => 100 * 50,
            'unit' => 'pct'
        ]);

        $table->addRow();
        $cell = $table->addCell(9500);

        $run = $cell->addTextRun(['spaceAfter' => 200]);

        // $cell->addFormField('textinput')
        //     ->setName('desc_' . $elemen->id)
        //     ->setDefaultValue('[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]')
        //     ->setMaxLength(5000);
        $cell->addText(
            '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            ['size' => 11, 'color' => '000000', 'italic' => true],
            ['spaceAfter' => 300, 'alignment' => Jc::BOTH]
        );

        // Render semua tabel di dalam kotak yang sama
        if ($elemen->datasetBorang && $elemen->datasetBorang->count() > 0) {
            foreach ($elemen->datasetBorang as $dataset) {
                if ($dataset->tipe_field !== 'table') continue;

                $cell->addText(
                    $dataset->nama,
                    ['size' => 11, 'bold' => true],
                    ['spaceAfter' => 100]
                );

                // addDatasetTable() Anda sudah mendukung container = Cell
                $this->addDatasetTable($cell, $dataset);

                $cell->addTextBreak(1);
            }
        }
    }

    private function addDatasetTable($container, $dataset)
    {
        $resolved = $this->resolveDatasetForDegree($dataset);

        $columns = $resolved->expected_columns ?? $dataset->expected_columns ?? ['No', 'Keterangan', 'Data'];
        $rows    = $resolved->expected_rows    ?? $dataset->expected_rows    ?? $this->resolveExpectedRows($dataset);

        if (!is_array($columns) || count($columns) === 0) $columns = ['No', 'Keterangan', 'Data'];
        if (!is_array($rows) || count($rows) === 0) $rows = [null, null, null];

        // ====== SMART WIDTH (simulate Word AutoFit) ======
        $tableWidth = $this->currentTableWidthTwips;
        $colWidths  = $this->computeSmartColumnWidthsTwips($columns, $tableWidth);

        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 80,
            'width'       => $tableWidth,
            'unit'        => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            'layout'      => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
        ];

        $table = $container->addTable($tableStyle);

        // ================= HEADER =================
        $headerFontSize = $this->isLandscape ? 9 : 10;

        $table->addRow(400);
        foreach ($columns as $i => $col) {
            $cell = $table->addCell($colWidths[$i], [
                'bgColor'  => 'D3D3D3',
                'valign'   => 'center',
                'wordWrap' => true,
            ]);

            $cell->addText(
                (string) $col,
                ['bold' => true, 'size' => $headerFontSize],
                ['alignment' => Jc::CENTER]
            );
        }

        // ================= ROWS =================
        $firstColName = strtolower(trim((string)($columns[0] ?? '')));
        $isNoColumn   = in_array($firstColName, ['no', 'no.', 'nomor', 'number'], true);

        foreach ($rows as $rowIndex => $rowLabel) {
            $table->addRow(350);

            $label = is_string($rowLabel) ? trim($rowLabel) : $rowLabel;
            $isJumlah    = is_string($label) && preg_match('/^Jumlah$/i', $label);
            $isRekap     = is_string($label) && preg_match('/^Rekapitulasi$/i', $label);
            $isYellowRow = $isJumlah || $isRekap;

            foreach ($columns as $colIndex => $col) {
                $cellStyle = ['wordWrap' => true] + ($isYellowRow ? ['bgColor' => 'FFF2CC'] : []);
                $cell = $table->addCell($colWidths[$colIndex], $cellStyle);

                // Kolom pertama: label TS/ Jumlah / Rekapitulasi atau nomor
                if ($colIndex === 0) {
                    $text = '';

                    if ($rowLabel !== null && $rowLabel !== '') {
                        $text = (string) $rowLabel;
                    } elseif ($isNoColumn) {
                        $text = (string) ($rowIndex + 1);
                    }

                    $isSpecial = is_string($text) && preg_match('/^TS(-\d+)?$|^Jumlah$|^Rekapitulasi$/i', $text);

                    $cell->addText(
                        $text,
                        ['size' => 10] + ($isSpecial ? ['bold' => true] : []),
                        ['alignment' => Jc::CENTER]
                    );

                    continue;
                }

                // ✅ Baris "Jumlah": masukkan field Word SUM(ABOVE)
                if ($isJumlah || $isRekap) {
                    // Cell kosong dengan highlight kuning
                    $cell->addText(
                        '',
                        ['size' => 10, 'bold' => true],
                        ['alignment' => Jc::CENTER]
                    );
                    continue;
                }

                // (Opsional) Rekapitulasi: tetap placeholder Σ
                $cell->addText(
                    '',
                    ['size' => 10],
                    ['alignment' => Jc::START]
                );
            }
        }
    }

    /**
     * Heuristik lebar kolom: makin panjang header → makin lebar.
     * Kolom No dibuat kecil, kolom berisi "Akreditasi"/"Status"/"Peringkat"/"No. dan Tgl." diboost.
     */
    private function computeSmartColumnWidthsTwips(array $columns, int $tableWidthTwips): array
    {
        $min = 700;   // minimum per kolom (twips)
        $max = 4200;  // maksimum per kolom (twips)

        $weights = [];
        foreach ($columns as $col) {
            $h = trim((string)$col);
            $lh = mb_strtolower($h);

            // base weight dari panjang teks
            $w = max(2.0, min(12.0, mb_strlen($h) / 3.0));

            // heuristik khusus
            if (preg_match('/^(no|no\.|nomor|number)$/i', $h)) $w = 1.2;                 // "No" super kecil
            if (str_contains($lh, 'akreditasi')) $w += 3.5;
            if (str_contains($lh, 'status')) $w += 2.0;
            if (str_contains($lh, 'peringkat')) $w += 2.0;
            if (str_contains($lh, 'no.')) $w += 1.5;
            if (str_contains($lh, 'tgl')) $w += 1.5;
            if (str_contains($lh, 'nama')) $w += 1.2;
            if (str_contains($lh, 'program studi')) $w += 1.5;

            $weights[] = $w;
        }

        $sum = array_sum($weights);
        if ($sum <= 0) $sum = 1;

        // alokasi twips berdasarkan bobot
        $widths = [];
        $remaining = $tableWidthTwips;

        foreach ($weights as $i => $w) {
            $alloc = (int) floor(($w / $sum) * $tableWidthTwips);
            $alloc = max($min, min($max, $alloc));
            $widths[$i] = $alloc;
            $remaining -= $alloc;
        }

        // distribusi sisa (agar total pas tableWidthTwips)
        $i = 0;
        while ($remaining !== 0 && count($widths) > 0) {
            $idx = $i % count($widths);

            if ($remaining > 0 && $widths[$idx] < $max) {
                $widths[$idx] += 1;
                $remaining -= 1;
            } elseif ($remaining < 0 && $widths[$idx] > $min) {
                $widths[$idx] -= 1;
                $remaining += 1;
            }

            $i++;
            if ($i > 200000) break; // safety
        }

        return $widths;
    }

    /**
     * Resolve expected rows:
     * - kalau dataset->expected_rows terisi, pakai itu
     * - kalau kosong/null, fallback 3 baris default (1..3)
     */
    private function resolveExpectedRows($dataset): array
    {
        $rows = $dataset->expected_rows ?? null;

        if (is_array($rows) && count($rows) > 0) {
            // Normalisasi: pastikan semuanya string (kecuali null)
            return array_map(function ($r) {
                if ($r === null) return null;
                return is_string($r) ? $r : (string) $r;
            }, $rows);
        }

        // Default lama: 3 baris dummy
        return [null, null, null];
    }

    /**
     * ✅ Pernyataan Standar (JUSTIFY)
     */
    private function addPernyataanStandar($section, $elemen)
    {
        $section->addText(
            'Pernyataan Standar',
            [
                'size' => 11,
                'bold' => true,
                'underline' => Font::UNDERLINE_SINGLE,
                'color' => $this->templateBlue
            ],
            ['spaceAfter' => 200]
        );

        if ($elemen->pernyataan && $elemen->pernyataan->count() > 0) {
            foreach ($elemen->pernyataan as $pernyataan) {
                // ✅ JUSTIFY TEXT
                $section->addText(
                    $pernyataan->pernyataan,
                    ['size' => 11, 'color' => $this->templateBlue],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 200] // BOTH = Justify
                );
            }
        } else {
            $section->addText(
                '[Tidak ada pernyataan standar]',
                [
                    'size' => 11,
                    'italic' => true,
                    'color' => $this->templateBlue
                ],
                ['spaceAfter' => 200]
            );
        }
    }

    /**
     * ✅ Uraian Indikator Penilaian (NUMBERED LIST)
     */
    private function addUraianIndikator($section, $elemen)
    {
        $section->addText(
            'Uraian Indikator Penilaian',
            [
                'size' => 11,
                'bold' => true,
                'underline' => Font::UNDERLINE_SINGLE,
                'color' => $this->templateBlue
            ],
            ['spaceAfter' => 200]
        );

        if (!$elemen->indikator || $elemen->indikator->count() === 0) {
            $section->addText('[Tidak ada indikator]', [
                'size' => 11,
                'italic' => true,
                'color' => $this->templateBlue
            ]);
            return;
        }

        // Register once (idealnya di constructor, tapi ini versi aman)
        $numberingName = 'indikatorNumbering_' . $elemen->id;

        $this->ensureNumberingStyle($numberingName, [
            'type' => 'multilevel',
            'levels' => [[
                'level'   => 0,
                'format'  => 'decimal',
                'text'    => '%1)',
                'left'    => 360,
                'hanging' => 360,
                'tabPos'  => 720,
            ]]
        ]);

        foreach ($elemen->indikator as $indikator) {
            $text = trim($indikator->deskripsi_indikator);

            if (preg_match('/^(.*?Penilaian berfokus pada:)/s', $text, $m)) {
                $section->addText(trim($m[1]), [
                    'size' => 11,
                    'color' => $this->templateBlue
                ], [
                    'alignment' => Jc::BOTH,
                    'spaceAfter' => 150,
                ]);
            }

            preg_match_all('/\(\d+\)\.\s*(.*?)(?=\(\d+\)\.|$)/s', $text, $matches);

            foreach ($matches[1] as $item) {
                $section->addListItem(
                    $item,
                    0,
                    [
                        'size' => 11,
                        'color' => $this->templateBlue
                    ],
                    $numberingName,
                    [
                        'alignment' => Jc::BOTH,
                        'spaceAfter' => 120
                    ]
                );
            }
        }
    }

    private function addSuplemenSection()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);
        $this->applyFooterPageNumber($section, 'arabic');

        $numberingName = 'suplemenNumbering';
        $this->ensureNumberingStyle($numberingName, [
            'type' => 'multilevel',
            'levels' => [
                ['level' => 0, 'format' => 'upperRoman',  'text' => '%1.', 'left' => 360,  'hanging' => 360, 'tabPos' => 720],
                ['level' => 1, 'format' => 'upperLetter', 'text' => '%2.', 'left' => 720,  'hanging' => 360, 'tabPos' => 1080],
                ['level' => 2, 'format' => 'decimal',     'text' => '%3.', 'left' => 1080, 'hanging' => 360, 'tabPos' => 1440],
                ['level' => 3, 'format' => 'lowerLetter', 'text' => '%4.', 'left' => 1440, 'hanging' => 360, 'tabPos' => 1800],
            ],
        ]);

        $code = $this->normalizeDegreeCode($this->activeDegreeLevel?->code);

        $this->renderSuplemenFromDatabase(
            $section,
            $numberingName,
            $code
        );
    }

    // private function addSuplemenSection()
    // {
    //     $section = $this->phpWord->addSection([
    //         'marginTop' => 1000,
    //         'marginBottom' => 1000,
    //         'marginLeft' => 1500,
    //         'marginRight' => 1500,
    //     ]);

    //     // Multilevel: I. / A. / 1. / a.
    //     $numberingName = 'suplemenNumbering';
    //     $this->ensureNumberingStyle($numberingName, [
    //         'type' => 'multilevel',
    //         'levels' => [
    //             ['level' => 0, 'format' => 'upperRoman',  'text' => '%1.', 'left' => 360,  'hanging' => 360, 'tabPos' => 720],
    //             ['level' => 1, 'format' => 'upperLetter', 'text' => '%2.', 'left' => 720,  'hanging' => 360, 'tabPos' => 1080],
    //             ['level' => 2, 'format' => 'decimal',     'text' => '%3.', 'left' => 1080, 'hanging' => 360, 'tabPos' => 1440],
    //             ['level' => 3, 'format' => 'lowerLetter', 'text' => '%4.', 'left' => 1440, 'hanging' => 360, 'tabPos' => 1800],
    //         ],
    //     ]);

    //     // I. (bold)
    //     $section->addListItem(
    //         'Suplemen Program Studi Diploma Satu, Diploma Dua, Diploma Tiga',
    //         0,
    //         ['size' => 11, 'bold' => true],
    //         $numberingName,
    //         ['spaceAfter' => 120]
    //     );

    //     // A. (bold)  ✅ harus bold
    //     $section->addListItem(
    //         'Deskripsi Capaian Pembelajaran Lulusan, Susunan, Materi Pembelajaran, Beban Belajar, Rencana Pembelajaran',
    //         1,
    //         ['size' => 11, 'bold' => true],
    //         $numberingName,
    //         ['spaceAfter' => 80]
    //     );

    //     // 1., 2., a., b., dst (contoh ringkas sesuai screenshot kamu)
    //     $section->addListItem('Capaian Pembelajaran Lulusan (CPL)', 2, ['size' => 11], $numberingName);
    //     $section->addListItem('Susunan Materi Pembelajaran Untuk Mencapai CPL', 2, ['size' => 11], $numberingName);

    //     $section->addListItem('Matakuliah', 3, ['size' => 11], $numberingName);
    //     $section->addListItem('Modul', 3, ['size' => 11], $numberingName);
    //     $section->addListItem('Blok tematik; dan/atau', 3, ['size' => 11], $numberingName);
    //     $section->addListItem('Bentuk lain', 3, ['size' => 11], $numberingName);

    //     $section->addListItem('Beban Belajar dan Masa Tempuh', 2, ['size' => 11], $numberingName);
    //     $section->addListItem('Rencana Pembelajaran', 2, ['size' => 11], $numberingName);

    //     $section->addTextBreak(1);

    //     // B. (bold) ✅ harus bold
    //     $section->addListItem(
    //         'Pemastian Capaian Pembelajaran Lulusan',
    //         1,
    //         ['size' => 11, 'bold' => true],
    //         $numberingName,
    //         ['spaceAfter' => 80]
    //     );

    //     // B.1 dst -> di screenshot kamu jadi 1.,2.,3. (level decimal)
    //     $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);

    //     // paragraf isi (bukan list) -> indent agar sejajar dengan teks setelah "1."
    //     $section->addText(
    //         'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
    //         ['size' => 11],
    //         ['alignment' => Jc::BOTH, 'indentation' => ['left' => 1080], 'spaceAfter' => 120]
    //     );

    //     $section->addListItem('Magang', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
    //     $section->addText(
    //         'Kegiatan magang di dunia usaha, dunia industri, dan dunia kerja yang relevan pada program Diploma Satu, Diploma Dua, dan Diploma Tiga (Durasi dan Beban belajar)',
    //         ['size' => 11],
    //         ['alignment' => Jc::BOTH, 'indentation' => ['left' => 1080], 'spaceAfter' => 120]
    //     );

    //     $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);

    //     // a. b. c. d. (level 3)
    //     $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
    //     $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
    //     $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
    //     $section->addListItem(
    //         'Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, baik secara individu maupun berkelompok, untuk program Diploma Tiga',
    //         3,
    //         ['size' => 11],
    //         $numberingName,
    //         ['spaceAfter' => 40]
    //     );
    // }

    /**
     * Helper untuk subtitle kriteria
     */
    private function getKriteriaSubtitle($kodeKriteria)
    {
        $subtitles = [
            'D' => 'Differentiation of the mission',
            'E' => 'Education, Evaluation System, and Learning Outcomes',
            'P' => 'Human Resource Development',
            'I' => 'Internalization of Quality Assurance',
            'L' => 'Learning Environment and Resources',
            'A' => 'Accountability, Governance, and Cooperation',
            'R' => 'Research, Community Service, and Academic Atmosphere'
        ];

        return $subtitles[$kodeKriteria] ?? '';
    }

    private function addFormLine($section, $label, $hasRedText = false, $alignment = Jc::START)
    {
        $textRun = $section->addTextRun(['alignment' => $alignment, 'spaceAfter' => 200]);
        $textRun->addText($label . str_repeat(' ', 5) . ': ................', ['size' => 11]);
    }

    private function ensureNumberingStyle(string $name, array $definition): void
    {
        if (isset($this->numberingRegistered[$name])) {
            return;
        }

        $this->phpWord->addNumberingStyle($name, $definition);
        $this->numberingRegistered[$name] = true;
    }

    private function shouldLandscape($dataset): bool
    {
        // Hardcode cepat: tinggal tambah kode tabel lain di sini
        $landscapeCodes = [
            'D.1',
            'E.2.a',
            'E.3.b',
            'E.4.a',
            'E.5.c',
            'A.1.a',
            'R.3.1.a',
            'R.3.1.b',
            'R.3.1.c',
            'R.3.1.d',
            'R.3.1.e',
            'R.3.2.a',
            'R.3.2.b',
            'R.3.2.c',
            'R.3.2.d',
            'R.6.1.a',
            'R.6.1.b',
            'R.6.1.c',
            'R.6.1.d',
            'R.6.1.e',
            'R.6.2.a',
            'R.6.2.b',
            'R.6.2.c',
            'R.6.2.d',
            // tambahkan bila perlu:
            // 'E.4.a', 'A.1.a', ...
        ];

        return in_array($dataset->kode, $landscapeCodes, true);
    }

    private function elemenNeedsLandscape($elemen): bool
    {
        if (!$elemen->datasetBorang) return false;

        foreach ($elemen->datasetBorang as $dataset) {
            if ($dataset->tipe_field === 'table' && $this->shouldLandscape($dataset)) {
                return true;
            }
        }
        return false;
    }

    private function createPortraitSection(string $numberType = 'arabic', bool $restartNumbering = false)
    {
        $this->isLandscape = false;

        $this->marginTopTwips = 1000;
        $this->marginBottomTwips = 1000;

        $this->pageHeightTwips = 16838; // A4 portrait ~ 11.69" * 1440
        $this->contentHeightTwips = $this->pageHeightTwips - ($this->marginTopTwips + $this->marginBottomTwips);

        $section = $this->phpWord->addSection([
            'orientation'  => 'portrait',
            'marginTop'    => $this->marginTopTwips,
            'marginBottom' => $this->marginBottomTwips,
            'marginLeft'   => 1500,
            'marginRight'  => 1500,
        ]);

        $this->applyFooterPageNumber($section, $numberType, $restartNumbering ? 1 : null);
        return $section;
    }

    private function createLandscapeSection(string $numberType = 'arabic', bool $restartNumbering = false)
    {
        $this->isLandscape = true;

        $this->marginTopTwips = 500;
        $this->marginBottomTwips = 500;

        $this->pageHeightTwips = 11906; // A4 landscape height ~ 8.27" * 1440
        $this->contentHeightTwips = $this->pageHeightTwips - ($this->marginTopTwips + $this->marginBottomTwips);

        $section = $this->phpWord->addSection([
            'orientation'  => 'landscape',
            'marginTop'    => $this->marginTopTwips,
            'marginBottom' => $this->marginBottomTwips,
            'marginLeft'   => 500,
            'marginRight'  => 500,
        ]);

        $this->applyFooterPageNumber($section, $numberType, $restartNumbering ? 1 : null);
        return $section;
    }

    private function addDeskripsiBoxFullPage($section, $kriteria, $elemen): void
    {
        /**
         * Reservasi tinggi area atas (yang sudah terpakai):
         * - Judul kriteria + subtitle
         * - Judul elemen (di kotak elemen Anda)
         * - spacing/textbreak
         *
         * Silakan adjust angka ini sampai pas dengan template Anda.
         */
        $reservedTopTwips = $this->isLandscape ? 2200 : 2600;

        // Tinggi kotak deskripsi = sisa tinggi halaman konten
        $boxHeight = max(2200, $this->contentHeightTwips - $reservedTopTwips);

        // Outer table = kotak deskripsi
        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 100,
            'width'       => 100 * 50,
            'unit'        => 'pct',
        ]);

        // 1 baris saja, tapi tingginya dibuat "mengisi sisa halaman"
        $table->addRow($boxHeight, ['exactHeight' => false]);
        $cell = $table->addCell(9500, ['valign' => 'top']);

        // Header kecil di dalam kotak
        $cell->addText(
            'Deskripsi ' . strtolower($elemen->pernyataan_elemen) . ' (Mohon jangan dihapus)',
            [
                'size' => 11,
                'italic' => true,
                'color' => '000000'
            ],
            ['spaceAfter' => 200]
        );

        $descKey   = 'desc_' . $elemen->id;
        $filled    = !empty(trim($this->borangDataMap[$descKey] ?? ''));
        $deskripsi = $filled
            ? $this->borangDataMap[$descKey]
            : '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]';

        $cell->addText(
            $deskripsi,
            [
                'size'   => 11,
                'color'  => $filled ? '000000' : '000000',
                'italic' => !$filled,
            ],
            ['alignment' => Jc::BOTH]
        );
    }

    private function addFrontMatterDescBox(
        \PhpOffice\PhpWord\Element\Section $section,
        string $label,
        int $maxWords,
        array $prefillParagraphs = [],
        bool $fullHeight = false,
        int $reservedTopTwips = 1800,      // hanya dipakai kalau fullHeight = true
        int $minBoxHeightTwips = 3000,     // hanya dipakai kalau fullHeight = true
        ?string $emptyPlaceholder = null   // kalau null, auto pakai template umum
    ): void {
        // Placeholder default jika kosong
        if ($emptyPlaceholder === null) {
            $emptyPlaceholder = "[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal {$maxWords} kata)...]";
        }

        // Style table (samakan saja; kalau mau beda margin, bisa tambahkan argumen juga)
        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 110,
            'width'       => 100 * 50,
            'unit'        => 'pct',
        ];

        $table = $section->addTable($tableStyle);

        // Kalau full height: pakai height sisa halaman
        if ($fullHeight) {
            $boxHeight = max($minBoxHeightTwips, $this->contentHeightTwips - $reservedTopTwips);
            $table->addRow($boxHeight, ['exactHeight' => false]);
        } else {
            $table->addRow();
        }

        $cell = $table->addCell(9500, ['valign' => 'top']);

        // Header kecil dalam kotak
        $cell->addText(
            $label,
            ['size' => 11, 'italic' => true],
            ['spaceAfter' => 200]
        );

        $hasPrefill = !empty(array_filter($prefillParagraphs, fn($p) => trim((string)$p) !== ''));

        // Kalau tidak ada prefill: tampilkan placeholder 1 paragraf
        if (!$hasPrefill) {
            $cell->addText(
                $emptyPlaceholder,
                ['size' => 11, 'color' => '000000', 'italic' => true],
                ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
            );
            return;
        }

        // Render paragraf bawaan (placeholder isi)
        foreach ($prefillParagraphs as $p) {
            $p = trim((string)$p);
            if ($p === '') continue;

            $cell->addText(
                $p,
                ['size' => 11, 'color' => '000000', 'italic' => true],
                ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
            );
        }

        // Hint limit kata
        $cell->addText(
            "(Maksimal {$maxWords} kata)",
            ['size' => 10, 'color' => '000000', 'italic' => true],
            ['alignment' => Jc::END]
        );
    }

    private function addRow(
        $table,
        $labelNormal,
        $value = '',
        $labelRed = null
    ) {
        $table->addRow(380);

        // Kolom Label
        $cell = $table->addCell(5200);
        if ($labelRed) {
            $run = $cell->addTextRun();
            $run->addText($labelNormal, ['size' => 11]);
            $run->addText($labelRed, [
                'size' => 11,
                'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE,
                'color' => '000000'
            ]);
        } else {
            $cell->addText($labelNormal, ['size' => 11]);
        }

        // Kolom ":"
        $table->addCell(300)->addText(':', ['size' => 11]);

        // Kolom Value
        $table->addCell(3200)->addText($value ?: ' ', ['size' => 11]);
    }

    private function resolveDatasetForDegree($dataset): object
    {
        // default: pakai dataset as-is
        $resolved = (object)[
            'expected_columns' => $dataset->expected_columns,
            'expected_rows'    => $dataset->expected_rows,
            'template_html'    => $dataset->template_html,
        ];

        // kalau tidak ada degree aktif, atau dataset tidak variant-aware → return default
        if (!$this->activeDegreeLevel || empty($dataset->has_degree_variants)) {
            return $resolved;
        }

        // ambil override dari pivot
        $pivot = DB::table('dataset_borang_degree_level')
            ->where('id_dataset_borang', $dataset->id)
            ->where('id_degree_level', $this->activeDegreeLevel->id)
            ->first();

        // kalau tidak ada override untuk degree tersebut, fallback ke default
        if (!$pivot) {
            return $resolved;
        }

        // decode json kolom
        $resolved->expected_columns = $pivot->expected_columns ? json_decode($pivot->expected_columns, true) : $resolved->expected_columns;

        // IMPORTANT:
        // tabel pivot Anda tidak punya expected_rows.
        // Maka expected_rows bisa tetap ambil dari dataset master,
        // atau Anda bisa derive dari template_html (lebih rumit).
        // Saran: tambahkan kolom expected_rows di pivot jika memang perlu.
        $resolved->template_html = $pivot->template_html ?? $resolved->template_html;

        // Jika Anda ingin per-degree juga beda rows (seperti L.4.a D1 vs D2),
        // yang paling rapi: tambahkan kolom expected_rows di dataset_borang_degree_level.
        // Untuk sementara, kita bisa pakai aturan sederhana berdasarkan kode tabel:
        if ($dataset->kode === 'L.4.a') {
            $resolved->expected_rows = $this->getRowsL4aByDegree($this->activeDegreeLevel->code);
        }

        if ($dataset->kode === 'E.2.2' || $dataset->kode === 'E.3.b') {
            // samakan dengan aturan masa studi (lihat fungsi di bawah)
            $resolved->expected_rows = $this->getRowsE22ByDegree($this->activeDegreeLevel->code);
        }

        return $resolved;
    }

    private function getRowsL4aByDegree(string $code): array
    {
        return match ($code) {
            'd1' => ['TS-3', 'TS-2', 'Jumlah'],
            'd2' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            'd3' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            'd4' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            's1' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            default => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
        };
    }

    private function getRowsE22ByDegree(string $code): array
    {
        // Ini hanya placeholder rows; struktur kolomnya yang utama berbeda per instrumen.
        // Kalau Anda butuh rows persis seperti format instrumen (TS-6, TS-5, dst),
        // Anda bisa definisikan sesuai kebutuhan.
        return match ($code) {
            'd1' => ['TS-1', 'TS'],
            'd2' => ['TS-2', 'TS-1', 'TS'],
            'd3' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            'd4', 's1' => ['TS-6', 'TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            's2', 's2-terapan' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            's3', 's3-terapan' => ['TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            default => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
        };
    }


    private function normalizeDegreeCode(?string $code): string
    {
        $c = strtoupper(trim((string) $code));

        // key = kode output, value = daftar sinonim input
        $map = [
            'd1'  => ['D1', 'DIPLOMA 1', 'DIPLOMA I'],
            'd2'  => ['D2', 'DIPLOMA 2', 'DIPLOMA II'],
            'd3'  => ['D3', 'DIPLOMA 3', 'DIPLOMA III'],
            'd4'  => ['D4', 'DIPLOMA 4', 'DIPLOMA IV', 'SARJANA TERAPAN', 'S1 TERAPAN'],
            's1'  => ['S1', 'SARJANA', 'SARJANA (STRATA 1)'],
            's2'  => ['S2', 'MAGISTER', 'MAGISTER (STRATA 2)'],
            's2-terapan' => ['S2 TERAPAN', 'MAGISTER TERAPAN', 'MAGISTER TERAPAN (STRATA 2)'],
            's3'  => ['S3', 'DOKTOR', 'DOKTOR (STRATA 3)'],
            's3-terapan' => ['S3 TERAPAN', 'DOKTOR TERAPAN', 'DOKTOR TERAPAN (STRATA 3)'],
            'profesi' => ['PROFESI', 'PENDIDIKAN PROFESI'],
            'spesialis' => ['SPESIALIS', 'PENDIDIKAN SPESIALIS'],
        ];

        foreach ($map as $out => $aliases) {
            if (in_array($c, $aliases, true)) return $out;
        }

        return strtolower($c);
    }

    private function renderSuplemenDiploma123($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Diploma Satu, Diploma Dua, Diploma Tiga',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem('Magang', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kegiatan magang di dunia usaha, dunia industri, dan dunia kerja yang relevan pada program Diploma Satu, Diploma Dua, dan Diploma Tiga (Durasi dan Beban belajar).'
        );

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, baik secara individu maupun berkelompok, untuk program Diploma Tiga',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenSarjanaTerapan($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Diploma Empat/Sarjana Terapan',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem(
            'Magang dan/atau Kerja Praktek, Studio dan/atau Praktikum, Kuliah Kerja Nyata/KKN, Kuliah Kerja Lapangan (KKL)',
            2,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );

        // bullet (pakai text biasa agar cepat)
        $this->addSuplemenBulletIndented($section, 'Kegiatan Magang dan/atau Kerja Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Studio dan/atau Praktikum yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Kuliah Kerja Nyata/KKN yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Kuliah Kerja Lapangan/KKL yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).');

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, baik secara individu maupun berkelompok, untuk Diploma Empat/Sarjana Terapan',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenSarjana($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Sarjana',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem('Studio, Praktikum, Magang, Kuliah Kerja Nyata/KKN, Kuliah Kerja Lapangan (KKL)', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Kegiatan Studio dan/atau Praktikum yang relevan pada program Sarjana (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Magang dan/atau Kerja Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program Sarjana (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Kuliah Kerja Nyata/KKN yang relevan pada program Sarjana (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Kuliah Kerja Lapangan/KKL yang relevan pada program Sarjana (Durasi dan Beban belajar).');

        $section->addListItem('Skripsi, prototipe, proyek, tugas akhir, kurikulum berbasis proyek', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Bentuk skripsi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis baik secara individu maupun berkelompok.');
        $this->addSuplemenBulletIndented($section, 'Penerapan kurikulum berbasis proyek atau bentuk pembelajaran lainnya yang sejenis dan asesmen yang dapat menunjukkan kompetensi lulusan.');

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, baik secara individu maupun berkelompok, untuk Sarjana',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenProfesi($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Profesi',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem('Praktek, Studio', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Kegiatan Praktek profesi di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program profesi (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Studio yang mendukung dan/atau relevan dengan kegiatan praktek profesi pada program Profesi (Durasi dan Beban belajar).');

        $section->addListItem('Tugas Akhir', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, yang relevan pada program Profesi.');

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, baik secara individu maupun berkelompok, untuk Profesi',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenMagister($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Magister',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem('Studio dan/atau Praktikum, Penelitian, Perancangan', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Kegiatan Studio dan/atau Praktikum yang relevan pada program Magister (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Penelitian yang relevan pada Program Magister (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Perancangan yang relevan pada program Magister (Durasi dan Beban belajar).');

        $section->addListItem('Tugas Akhir', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Tugas akhir dalam bentuk tesis, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.');

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk tesis, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis untuk program Magister',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenMagisterTerapan($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Magister Terapan',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem('Studio dan/atau Praktikum, Perancangan, Praktek', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Kegiatan Studio dan/atau Praktikum yang relevan pada program Magister Terapan (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Perancangan yang pada program Magister Terapan (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program profesi (Durasi dan Beban belajar).');

        $section->addListItem('Tugas Akhir', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Tugas akhir dalam bentuk tesis, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.');

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk tesis, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis untuk program Magister Terapan',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenDoktor($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Doktor',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem('Penelitian, Perancangan', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Kegiatan Penelitian relevan pada program Doktor (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Perancangan yang relevan pada program Doktor (Durasi dan Beban belajar).');

        $section->addListItem('Tugas Akhir', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Tugas akhir dalam bentuk disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.');

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis untuk program Doktor',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenDoktorTerapan($section, string $numberingName): void
    {
        $section->addListItem(
            'Suplemen Program Studi Doktor Terapan',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        $this->renderSuplemenBagianACommon($section, $numberingName);

        $section->addTextBreak(1);

        $section->addListItem('Pemastian Capaian Pembelajaran Lulusan', 1, ['size' => 11, 'bold' => true], $numberingName, ['spaceAfter' => 80]);

        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenParagraphIndented(
            $section,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.'
        );

        $section->addListItem('Studio dan/atau Praktikum, Perancangan, Praktek', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Kegiatan Studio dan/atau Praktikum yang relevan pada program Doktor Terapan (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Perancangan yang pada program Doktor Terapan (Durasi dan Beban belajar).');
        $this->addSuplemenBulletIndented($section, 'Kegiatan Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program Doktor Terapan (Durasi dan Beban belajar).');

        $section->addListItem('Tugas Akhir', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $this->addSuplemenBulletIndented($section, 'Tugas akhir dalam bentuk Disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.');

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);
        $section->addListItem('Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Non Test', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem('Observasi', 3, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addListItem(
            'Tugas akhir dalam bentuk Disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis untuk program Doktor Terapan',
            3,
            ['size' => 11],
            $numberingName,
            ['spaceAfter' => 40]
        );
    }

    private function renderSuplemenBagianACommon($section, string $numberingName): void
    {
        $section->addListItem(
            'Deskripsi Capaian Pembelajaran Lulusan, Susunan Materi Pembelajaran, Beban Belajar, Rencana Pembelajaran',
            1,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 80]
        );

        $section->addListItem('Capaian Pembelajaran Lulusan (CPL)', 2, ['size' => 11], $numberingName);
        $section->addListItem('Susunan Materi Pembelajaran Untuk Mencapai CPL', 2, ['size' => 11], $numberingName);

        $section->addListItem('Matakuliah', 3, ['size' => 11], $numberingName);
        $section->addListItem('Modul', 3, ['size' => 11], $numberingName);
        $section->addListItem('Blok tematik; dan/atau', 3, ['size' => 11], $numberingName);
        $section->addListItem('Bentuk lain', 3, ['size' => 11], $numberingName);

        $section->addListItem('Beban Belajar dan Masa Tempuh', 2, ['size' => 11], $numberingName);
        $section->addListItem('Rencana Pembelajaran', 2, ['size' => 11], $numberingName);
    }

    private function addSuplemenParagraphIndented($section, string $text): void
    {
        $section->addText(
            $text,
            ['size' => 11],
            [
                'alignment' => Jc::BOTH,
                'indentation' => ['left' => 1080],
                'spaceAfter' => 120
            ]
        );
    }

    private function addSuplemenBulletIndented($section, string $text): void
    {
        // bullet manual "•" seperti contoh Anda
        $section->addText(
            "• " . $text,
            ['size' => 11],
            [
                'alignment' => Jc::BOTH,
                'indentation' => ['left' => 1080],
                'spaceAfter' => 80
            ]
        );
    }

    private function renderSuplemenFromDatabase(
        $section,
        string $numberingName,
        string $degreeCode
    ): void {
        $items = DatasetSuplemen::where('degree_level_code', $degreeCode)
            ->orderBy('urutan')
            ->get();

        foreach ($items as $item) {
            $format = $item->formatting ?? [];

            switch ($item->content_type) {
                case 'list_item':
                    $section->addListItem(
                        $item->text_content,
                        $item->numbering_level,
                        ['size' => 11, 'bold' => $format['bold'] ?? false],
                        $numberingName,
                        [
                            'spaceAfter' => $format['spaceAfter'] ?? null,
                        ]
                    );
                    break;

                case 'paragraph':
                    $section->addText(
                        $item->text_content,
                        ['size' => 11],
                        [
                            'alignment'   => Jc::BOTH,
                            'indentation' => [
                                'left' => $format['indentation'] ?? 0
                            ],
                            'spaceAfter'  => $format['spaceAfter'] ?? 0,
                        ]
                    );
                    break;

                case 'bullet':
                    $section->addText(
                        '• ' . $item->text_content,
                        ['size' => 11],
                        [
                            'alignment'   => Jc::BOTH,
                            'indentation' => [
                                'left' => $format['indentation'] ?? 1080
                            ],
                            'spaceAfter' => $format['spaceAfter'] ?? 80,
                        ]
                    );
                    break;
            }
        }
    }

    private function addDaftarIsiSection(): void
    {
        $section = $this->createPortraitSection('roman');

        // ===== JUDUL DAFTAR ISI =====
        $section->addText(
            'DAFTAR ISI',
            ['bold' => true, 'size' => 14],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );

        // ===== SETUP TAB STYLE (untuk dots leader) =====
        $tocStyle = [
            'spaceAfter' => 100,
            'tabs' => [
                new \PhpOffice\PhpWord\Style\Tab('right', 9000, 'dot')
            ]
        ];

        // ===== FRONT MATTER (Manual Entries dengan Roman numerals) =====
        $this->addTOCLine($section, 'Halaman Cover', '', $tocStyle);
        $this->addTOCLine($section, 'Halaman Pengesahan', 'i', $tocStyle);
        $this->addTOCLine($section, 'Kata Pengantar', 'ii', $tocStyle);
        $this->addTOCLine($section, 'Ringkasan', 'iii', $tocStyle);
        $this->addTOCLine($section, 'Daftar Isi', 'iv', $tocStyle);
        // $this->addTOCLine($section, 'Daftar Gambar', 'v', $tocStyle);
        // $this->addTOCLine($section, 'Daftar Tabel', 'vi', $tocStyle);

        $section->addTextBreak(1);

        // ===== KONTEN UTAMA (TOC Otomatis dari Heading) =====
        // TOC otomatis akan menampilkan Kriteria D, E, P, I, L, A, R dengan halaman Arabic
        $section->addTOC(
            ['size' => 11],
            ['tabLeader' => \PhpOffice\PhpWord\Style\TOC::TAB_LEADER_DOT],
            1,  // Level 1 (Kriteria)
            3   // Sampai Level 3 (sub-elemen)
        );

        $section->addTextBreak(2);

        // ===== INSTRUKSI UPDATE =====
        $section->addText(
            '* Untuk mengupdate nomor halaman konten: klik kanan pada daftar isi > Update Field atau tekan Ctrl+A lalu F9',
            ['size' => 9, 'italic' => true, 'color' => '555555'],
            ['alignment' => Jc::CENTER]
        );
    }

    /**
     * Helper untuk menambahkan satu baris TOC manual
     */
    private function addTOCLine($section, $title, $pageNum, $style, $bold = false)
    {
        $textRun = $section->addTextRun($style);

        // Title
        $textRun->addText(
            $title,
            ['size' => 11, 'bold' => $bold]
        );

        // Tab (dots leader otomatis dari TabStop)
        $textRun->addText("\t");

        // Page number
        $textRun->addText(
            is_numeric($pageNum) ? (string)$pageNum : $pageNum,
            ['size' => 11, 'bold' => $bold]
        );
    }
    /**
     * ✅ Basic document protection
     */
    // private function protectDocumentBasic(): void
    // {
    //     $settings = $this->phpWord->getSettings();
    //     $protection = new Protection();
    //     $protection->setEditing(DocProtect::READ_ONLY);
    //     $protection->setPassword('lamdepilar');
    //     $settings->setDocumentProtection($protection);
    // }

    /**
     * ✅ Save detailed protection instructions
     */
    private function saveProtectionInstructions(string $degreeCode): void
    {
        $guide = <<<'TXT'
# 🔒 PANDUAN PROTECT TEMPLATE LED

## Password Default
**Password:** lamdepilar

## Jenis Protection

### 1. Basic Protection (Already Applied)
- Seluruh dokumen READ-ONLY
- User perlu unprotect dengan password untuk edit

### 2. Advanced Protection (Manual Setup Required)

Untuk allow edit HANYA di area tertentu:

**Area yang BOLEH diedit:**
- Halaman cover (nama prodi, tahun)
- Lembar pengesahan (tanda tangan, nama)
- Kotak deskripsi di setiap elemen (dalam border hitam)
- Tabel data di setiap elemen
- Bagian suplemen

**Area yang DIKUNCI:**
- Header/footer
- Pernyataan standar
- Indikator penilaian
- Label dan instruksi

**Cara Setup (Microsoft Word):**
1. File → Info → Protect Document → Restrict Editing
2. Pilih "Allow only this type of editing": Filling in forms
3. Klik "Select sections..."
4. Centang sections yang boleh diedit:
   - Section 1 (Cover)
   - Section 2 (Pengesahan)
   - Kotak deskripsi (manual select)
   - Suplemen section
5. Klik "Yes, Start Enforcing Protection"
6. Enter password: lamdepilar

**Catatan:**
- Protection berbasis section/region memerlukan manual setup
- Alternatif: Use form fields untuk area editable
- Atau: Provide separated template per section

## Troubleshooting

**Q: Saya lupa password?**
A: Contact admin atau regenerate template

**Q: Kotak deskripsi tidak bisa diedit?**
A: Unprotect → Edit → Re-protect dengan exclude area tersebut

TXT;
        $guidePath = storage_path("app/public/templates/PROTECTION_GUIDE_{$degreeCode}.txt");
        if (!file_exists(dirname($guidePath))) {
            mkdir(dirname($guidePath), 0755, true);
        }
        file_put_contents($guidePath, $guide);
    }
}
