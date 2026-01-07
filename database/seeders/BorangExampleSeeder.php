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

class BorangExampleSeeder extends Seeder
{
    protected PhpWord $phpWord;

    protected array $numberingRegistered = [];
    protected ?DegreeLevel $activeDegreeLevel = null;
    protected int $currentTableWidthTwips = 8900;
    protected bool $isLandscape = false;
    protected bool $hasRestartedContentNumbering = false;

    public function run()
    {
        $degreeLevel = DegreeLevel::whereIn('code', ['S1'])->firstOrFail();
        $this->activeDegreeLevel = $degreeLevel;
        $this->phpWord = new PhpWord();
        $this->phpWord->getSettings()->setUpdateFields(true);

        $this->phpWord->addTitleStyle(1, ['bold' => true, 'size' => 14], ['spaceAfter' => 240]);
        $this->phpWord->addTitleStyle(2, ['bold' => true, 'size' => 12], ['spaceAfter' => 180]);
        $this->phpWord->addTitleStyle(3, ['bold' => false, 'size' => 11], ['spaceAfter' => 120]);

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

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $safeCode = Str::slug($degreeLevel->code, '_'); // contoh: "s2-terapan" jadi "s2_terapan"
        $filePath = storage_path("app/public/templates/TEMPLATE_BORANG_EVALUASI_DIRI_{$safeCode}.docx");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $objWriter->save($filePath);
        $this->injectSumFields($filePath);

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

        // Paragraf 1
        $section->addText(
            'Puji syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa atas tersusunnya laporan ini sebagai bagian dari dokumentasi dan evaluasi kinerja Program Studi. Laporan ini disusun untuk memberikan gambaran menyeluruh mengenai capaian akademik, penelitian, pengabdian kepada masyarakat, serta kinerja mahasiswa dan dosen selama beberapa tahun terakhir.',
            ['size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
        );

        // Paragraf 2
        $section->addText(
            'Laporan ini memuat berbagai data terkait penerimaan mahasiswa, capaian pembelajaran, prestasi akademik, kegiatan penelitian dan pengabdian kepada masyarakat, serta kerja sama dengan pihak eksternal. Seluruh data disajikan secara sistematis dan berdasarkan catatan administrasi Program Studi, dengan harapan dapat menjadi bahan evaluasi dan perbaikan dalam rangka peningkatan mutu pendidikan.',
            ['size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
        );

        // Paragraf 3
        $section->addText(
            'Kami menyadari bahwa penyusunan laporan ini tidak lepas dari dukungan berbagai pihak. Oleh karena itu, kami menyampaikan apresiasi dan terima kasih kepada seluruh dosen, tenaga kependidikan, mahasiswa, serta mitra kerja yang telah berkontribusi dalam pengumpulan data dan penyusunan laporan ini.',
            ['size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
        );

        // Paragraf 4
        $section->addText(
            'Akhir kata, semoga laporan ini dapat memberikan manfaat sebagai sarana transparansi, evaluasi, dan peningkatan mutu Program Studi di masa yang akan datang.',
            ['size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 600]
        );

        // Penutup (kanan bawah)
        $section->addText(
            '[Nama Kota], [Tanggal Penyusunan]',
            ['size' => 11],
            ['alignment' => Jc::END, 'spaceAfter' => 100]
        );

        $section->addText(
            'Ketua Program Studi',
            ['size' => 11],
            ['alignment' => Jc::END, 'spaceAfter' => 600]
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

        // Judul
        $section->addText(
            'RINGKASAN',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
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
                ['size' => 11, 'italic' => true],
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
                        ['size' => 11, 'bold' => true],
                        ['spaceAfter' => 100]
                    );
                    $section->addText(
                        '(' . $this->getKriteriaSubtitle($kriteria->kode_kriteria) . ')',
                        ['size' => 11, 'italic' => true],
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
     * ✅ Kotak besar untuk deskripsi + TABEL
     * - Portrait tables: masuk ke dalam kotak (table border)
     * - Landscape tables: dibuat di section landscape khusus
     * - Landscape berurutan: tetap dalam 1 section landscape (tanpa dobel section break)
     * - Setelah landscape selesai: balik ke portrait section baru + buat ulang kotak supaya lanjut rapi
     *
     * PERBAIKAN: header elemen/kriteria tidak dicetak lagi di landscape (agar tidak double),
     * karena sudah dicetak lewat addElemenBox() sebelum fungsi ini dipanggil.
     */
    private function addDeskripsiBox($section, $kriteria, $elemen)
    {
        // Helper untuk membuat (ulang) kotak deskripsi dan mengembalikan cell kontennya
        $makeDeskripsiBox = function ($section) use ($elemen) {
            $table = $section->addTable([
                'borderSize' => 6,
                'borderColor' => '000000',
                'cellMargin' => 100,
                'width' => 100 * 50,
                'unit' => 'pct'
            ]);

            $table->addRow();
            $cell = $table->addCell(9500);

            $cell->addText(
                'Deskripsi ' . strtolower($elemen->pernyataan_elemen),
                ['size' => 11, 'italic' => true],
                ['spaceAfter' => 200]
            );

            $cell->addText(
                '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
                ['size' => 11, 'color' => 'FF0000', 'italic' => true],
                ['spaceAfter' => 300, 'alignment' => Jc::BOTH]
            );

            return $cell;
        };

        // Awal: berada di portrait section
        $currentOrientation = 'portrait';
        $cell = $makeDeskripsiBox($section);

        if (!$elemen->datasetBorang || $elemen->datasetBorang->count() === 0) {
            return $section;
        }

        $datasets = $elemen->datasetBorang->values(); // rapikan index 0..n-1

        for ($i = 0; $i < $datasets->count(); $i++) {
            $dataset = $datasets[$i];
            if ($dataset->tipe_field !== 'table') continue;

            $isLandscape = $this->shouldLandscape($dataset);

            // =========================
            // LANDSCAPE TABLES
            // =========================
            if ($isLandscape) {

                // Jika belum landscape, pindah ke landscape section sekali saja
                if ($currentOrientation !== 'landscape') {
                    $section = $this->createLandscapeSection();
                    $currentOrientation = 'landscape';

                    // ❌ PERBAIKAN: jangan cetak ulang header kriteria/elemen di sini
                    // karena sudah ada addElemenBox() di portrait sebelumnya.
                }

                // Judul per tabel (tetap ditampilkan)
                $section->addText(
                    $dataset->nama,
                    ['size' => 11, 'bold' => true],
                    ['spaceAfter' => 120]
                );

                // Render tabel di landscape section
                $this->addDatasetTable($section, $dataset);

                $section->addTextBreak(1);

                // Peek next: kalau berikutnya bukan landscape, baru balik portrait
                $next = $datasets->get($i + 1);
                $nextIsLandscape = $next && $next->tipe_field === 'table' && $this->shouldLandscape($next);

                if (!$nextIsLandscape) {
                    // Balik ke portrait section baru (sekali)
                    $section = $this->createPortraitSection();
                    $currentOrientation = 'portrait';

                    // Karena section baru, untuk menjaga format, buat ulang elemen box + kotak deskripsi
                    $this->addElemenBox($section, $kriteria, $elemen);
                    $section->addTextBreak(0.5);
                    $cell = $makeDeskripsiBox($section);
                }

                continue;
            }

            // =========================
            // PORTRAIT TABLES (di kotak)
            // =========================
            if ($currentOrientation !== 'portrait') {
                // Safety: kalau entah bagaimana masih landscape, balik dulu
                $section = $this->createPortraitSection();
                $currentOrientation = 'portrait';

                $this->addElemenBox($section, $kriteria, $elemen);
                $section->addTextBreak(0.5);
                $cell = $makeDeskripsiBox($section);
            }

            // Render tabel portrait di dalam kotak deskripsi
            $cell->addText(
                $dataset->nama,
                ['size' => 11, 'bold' => true],
                ['spaceAfter' => 100]
            );

            $this->addDatasetTable($cell, $dataset);
            $cell->addTextBreak(1);
        }

        return $section;
    }

    /**
     * Deskripsi box + tabel, semuanya dirender di SECTION YANG SAMA.
     * Tidak ada switching portrait/landscape di sini.
     */
    private function addDeskripsiBoxUnified($section, $kriteria, $elemen)
    {
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

        $cell->addText(
            'Deskripsi ' . strtolower($elemen->pernyataan_elemen),
            ['size' => 11, 'italic' => true],
            ['spaceAfter' => 200]
        );

        $cell->addText(
            '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            ['size' => 11, 'color' => 'FF0000', 'italic' => true],
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
            ['size' => 11, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE],
            ['spaceAfter' => 200]
        );

        if ($elemen->pernyataan && $elemen->pernyataan->count() > 0) {
            foreach ($elemen->pernyataan as $pernyataan) {
                // ✅ JUSTIFY TEXT
                $section->addText(
                    $pernyataan->pernyataan,
                    ['size' => 11],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 200] // BOTH = Justify
                );
            }
        } else {
            $section->addText(
                '[Tidak ada pernyataan standar]',
                ['size' => 11, 'italic' => true, 'color' => 'FF0000'],
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
            ['size' => 11, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE],
            ['spaceAfter' => 200]
        );

        if (!$elemen->indikator || $elemen->indikator->count() === 0) {
            $section->addText('[Tidak ada indikator]', ['size' => 11, 'italic' => true, 'color' => 'FF0000']);
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
                $section->addText(trim($m[1]), ['size' => 11], ['alignment' => Jc::BOTH, 'spaceAfter' => 150]);
            }

            preg_match_all('/\(\d+\)\.\s*(.*?)(?=\(\d+\)\.|$)/s', $text, $matches);

            foreach ($matches[1] as $item) {
                $section->addListItem(
                    $item,
                    0,
                    ['size' => 11],
                    $numberingName,
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 120]
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

        // Pilih konten suplemen sesuai degree
        switch ($code) {
            case 'D1':
            case 'D2':
            case 'D3':
                $this->renderSuplemenDiploma123($section, $numberingName);
                break;

            case 'D4': // Sarjana Terapan
            case 'S1T': // kalau Anda pakai kode lain utk sarjana terapan, map di normalizeDegreeCode()
                $this->renderSuplemenSarjanaTerapan($section, $numberingName);
                break;

            case 'S1':
                $this->renderSuplemenSarjana($section, $numberingName);
                break;

            case 'PROFESI':
                $this->renderSuplemenProfesi($section, $numberingName);
                break;

            case 'S2':
                $this->renderSuplemenMagister($section, $numberingName);
                break;

            case 'S2T': // Magister Terapan
                $this->renderSuplemenMagisterTerapan($section, $numberingName);
                break;

            case 'S3':
                $this->renderSuplemenDoktor($section, $numberingName);
                break;

            default:
                // fallback: kalau kode tidak dikenali, tampilkan yang umum (mis. sarjana)
                $this->renderSuplemenSarjana($section, $numberingName);
                break;
        }
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

    private function createPortraitSection(
        string $numberType = 'arabic',
        bool $restartNumbering = false
    ): \PhpOffice\PhpWord\Element\Section {
        $this->isLandscape = false;
        $this->currentTableWidthTwips = 8906;

        $section = $this->phpWord->addSection([
            'orientation' => 'portrait',
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);

        $this->applyFooterPageNumber(
            $section,
            $numberType,
            $restartNumbering ? 1 : null
        );

        return $section;
    }

    /**
     * ✅ PERBAIKAN: Create Landscape Section dengan Page Number Control
     */
    private function createLandscapeSection(
        string $numberType = 'arabic',
        bool $restartNumbering = false
    ): \PhpOffice\PhpWord\Element\Section {
        $this->isLandscape = true;
        $this->currentTableWidthTwips = 14838;

        $section = $this->phpWord->addSection([
            'orientation' => 'landscape',
            'marginTop' => 500,
            'marginBottom' => 500,
            'marginLeft' => 500,
            'marginRight' => 500,
        ]);

        $this->applyFooterPageNumber(
            $section,
            $numberType,
            $restartNumbering ? 1 : null
        );

        return $section;
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
                'color' => 'FF0000'
            ]);
        } else {
            $cell->addText($labelNormal, ['size' => 11]);
        }

        // Kolom ":"
        $table->addCell(300)->addText(':', ['size' => 11]);

        // Kolom Value
        $table->addCell(3200)->addText($value ?: ' ', ['size' => 11]);
    }

    private function switchToSection(string $orientation): \PhpOffice\PhpWord\Element\Section
    {
        if ($orientation === 'landscape') {
            return $this->createLandscapeSection();
        }
        return $this->createPortraitSection();
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
            'D1' => ['TS-3', 'TS-2', 'Jumlah'],
            'D2' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            'D3' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            'D4' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            'S1' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            default => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
        };
    }

    private function getRowsE22ByDegree(string $code): array
    {
        // Ini hanya placeholder rows; struktur kolomnya yang utama berbeda per instrumen.
        // Kalau Anda butuh rows persis seperti format instrumen (TS-6, TS-5, dst),
        // Anda bisa definisikan sesuai kebutuhan.
        return match ($code) {
            'D1' => ['TS-1', 'TS'],
            'D2' => ['TS-2', 'TS-1', 'TS'],
            'D3' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            'D4', 'S1' => ['TS-6', 'TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            'S2', 'S2 Terapan' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            'S3', 'S3 Terapan' => ['TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            default => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
        };
    }


    private function normalizeDegreeCode(?string $code): string
    {
        $c = strtoupper(trim((string) $code));

        // key = kode output, value = daftar sinonim input
        $map = [
            'D1'  => ['D1', 'DIPLOMA 1'],
            'D2'  => ['D2', 'DIPLOMA 2'],
            'D3'  => ['D3', 'DIPLOMA 3'],
            'D4'  => ['D4', 'DIPLOMA 4', 'SARJANA TERAPAN', 'S1 TERAPAN'],
            'S1'  => ['S1', 'SARJANA'],
            'S2'  => ['S2', 'MAGISTER'],
            'S2T' => ['S2 TERAPAN', 'MAGISTER TERAPAN'],
            'S3'  => ['S3', 'DOKTOR', 'S3 TERAPAN', 'DOKTOR TERAPAN'],
            'PROFESI' => ['PROFESI'],
        ];

        foreach ($map as $out => $aliases) {
            if (in_array($c, $aliases, true)) return $out;
        }

        return $c;
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

    /**
     * ✅ Post-process: Replace placeholder dengan Word field code untuk SUM(ABOVE)
     */
    private function injectSumFields($filePath)
    {
        $zip = new \ZipArchive();

        if ($zip->open($filePath) === true) {
            // Read document.xml
            $documentXml = $zip->getFromName('word/document.xml');

            // ✅ Find placeholder dan replace dengan field code XML yang proper
            $documentXml = preg_replace(
                '/<w:t>SUMFIELD_PLACEHOLDER<\/w:t>/',
                '<w:fldChar w:fldCharType="begin"/>' .
                    '<w:instrText xml:space="preserve"> =SUM(ABOVE) \# "0" </w:instrText>' .
                    '<w:fldChar w:fldCharType="separate"/>' .
                    '<w:t>0</w:t>' .
                    '<w:fldChar w:fldCharType="end"/>',
                $documentXml
            );

            // Write back
            $zip->deleteName('word/document.xml');
            $zip->addFromString('word/document.xml', $documentXml);
            $zip->close();
        }
    }

    // Ubah addSumAboveField() jadi:
    private function addSumAboveField($cell, array $fontStyle = [], array $paragraphStyle = []): void
    {
        // ✅ Tambahkan PLACEHOLDER yang akan diganti dengan field code di post-processing
        $cell->addText('SUMFIELD_PLACEHOLDER', $fontStyle, $paragraphStyle);
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
        $this->addTOCLine($section, 'Daftar Gambar', 'v', $tocStyle);
        $this->addTOCLine($section, 'Daftar Tabel', 'vi', $tocStyle);

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
}
