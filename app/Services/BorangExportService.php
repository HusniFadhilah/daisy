<?php
// app/Services/BorangExportService.php

namespace App\Services;

use Imagick;
use ImagickException;
use App\Libraries\Date;
use App\Models\Kriteria;
use App\Models\BorangData;
use App\Models\DegreeLevel;
use PhpOffice\PhpWord\PhpWord;
use App\Models\DatasetSuplemen;
use App\Models\PengajuanDokumen;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\SimpleType\Jc;
use App\Services\HtmlToPhpWordParser;
use Symfony\Component\Process\Process;

class BorangExportService
{
    protected PhpWord $phpWord;

    protected array $numberingRegistered = [];
    protected ?DegreeLevel $activeDegreeLevel = null;
    protected int $currentTableWidthTwips = 8900;
    protected bool $isLandscape = false;
    protected bool $hasRestartedContentNumbering = false;
    protected $pengajuan;
    protected $borangDataMap = [];

    protected int $pageHeightTwips = 16838;        // A4 portrait height
    protected int $contentHeightTwips = 14838;     // height - (marginTop+marginBottom)
    protected int $marginTopTwips = 1000;
    protected int $marginBottomTwips = 1000;
    protected string $templateBlue = '1F4E79';
    protected array $tmpImages = [];
    protected HtmlToPhpWordParser $htmlParser;

    public function __construct($pengajuan)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        $this->pengajuan = $pengajuan;

        // Jenjang prodi diakses melalui relasi studyProgram.
        $this->activeDegreeLevel = $pengajuan->studyProgram?->degreeLevel;

        $this->phpWord = new PhpWord();
        $this->phpWord->getSettings()->setUpdateFields(true);

        $this->phpWord->addTitleStyle(1, ['bold' => true, 'size' => 14, 'color' => $this->templateBlue], ['spaceAfter' => 240]);
        $this->phpWord->addTitleStyle(2, ['bold' => true, 'size' => 11, 'color' => $this->templateBlue], ['spaceAfter' => 180]);
        $this->phpWord->addTitleStyle(3, ['bold' => false, 'size' => 11, 'color' => $this->templateBlue], ['spaceAfter' => 120]);

        $this->phpWord->setDefaultFontName('Montserrat');
        $this->phpWord->setDefaultFontSize(11);
        $this->htmlParser = new HtmlToPhpWordParser($this->phpWord, $pengajuan->id);

        $this->loadBorangDataMap(); // pindahkan ke fungsi biar rapi
    }

    public function generate()
    {
        $this->addCoverPage();
        $this->addLembarPengesahan();
        $this->addKataPengantarSection();
        $this->addRingkasanSection();
        $this->addDaftarIsiSection();
        $this->addContentPages();
        $this->addSuplemenSection();
        // optional
        // $this->cleanupTmpPdfImages();

        return $this->phpWord;
    }

    public function save($filePath)
    {
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save($filePath);
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

        // ✅ LOGO (bisa diganti dengan image jika ada)
        if ($this->pengajuan->studyProgram->university->logo_path) {
            $logoPath = storage_path('app/public/' . $this->pengajuan->studyProgram->university->logo_path);
            $this->addLogoFlexible($section, $logoPath, 200, 200);
        } else {
            $section->addText(
                '[LOGO UNIVERSITAS]',
                ['size' => 14, 'bold' => true],
                ['alignment' => Jc::CENTER]
            );
        }

        $section->addTextBreak(5);

        $section->addText(
            'PROGRAM STUDI',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(1);

        // ✅ NAMA PRODI DARI DATABASE
        $section->addText(
            strtoupper($this->pengajuan->studyProgram->name),
            ['size' => 14],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(8);

        // ✅ UNIVERSITAS DARI DATABASE
        $section->addText(
            strtoupper($this->pengajuan->studyProgram->university->name ?? 'UNIVERSITAS'),
            ['size' => 12],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(1);

        // ✅ TAHUN PENGAJUAN
        $textRun = $section->addTextRun(['alignment' => Jc::CENTER]);
        $textRun->addText(Date::bulanTahun($this->pengajuan->created_at), ['size' => 12]);
    }

    private function addLembarPengesahan()
    {
        $pdfPath = $this->getPengajuanDokumenPath('lembar_pengesahan');

        if ($pdfPath) {
            $this->addPdfAsImagesToWord($pdfPath, 'roman');
            return;
        }

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

        // ✅ AMBIL DATA DENGAN PREFILL LOGIC
        $university = $this->pengajuan->studyProgram->university;
        $studyProgram = $this->pengajuan->studyProgram;

        // ===== BLOK 1: Lembaga Penjaminan Mutu =====
        $section->addText('Lembaga Penjaminan Mutu', ['size' => 11, 'bold' => true], ['spaceAfter' => 120]);

        $table1 = $section->addTable('FormTable');

        // ✅ Prefill dengan fallback
        $this->addRow(
            $table1,
            'Universitas/Institut',
            $this->getFieldValue('lpm_university_name', $university->name)
        );
        $this->addRow(
            $table1,
            'Lembaga Penjaminan Mutu',
            $this->getFieldValue('lpm_name', $university->lpm_name)
        );
        $this->addRow(
            $table1,
            'Telp',
            $this->getFieldValue('lpm_phone', $university->lpm_phone)
        );
        $this->addRow(
            $table1,
            'Mobile telp dan/atau WA',
            $this->getFieldValue('lpm_mobile', $university->lpm_mobile)
        );
        $this->addRow(
            $table1,
            'Alamat Email',
            $this->getFieldValue('lpm_email', $university->lpm_email)
        );

        $section->addTextBreak(1);

        // ===== BLOK 2: Program Studi Akreditasi =====
        $section->addText('Program Studi Akreditasi', ['size' => 11, 'bold' => true], ['spaceAfter' => 120]);

        $table2 = $section->addTable('FormTable');

        // ✅ Prefill dengan fallback
        $this->addRow(
            $table2,
            'Program Studi',
            $this->getFieldValue('prodi_name', $studyProgram->name)
        );
        $this->addRow(
            $table2,
            'Akreditasi',
            $this->getFieldValue('prodi_akreditasi', $studyProgram->peringkat_akreditasi)
        );
        $this->addRow(
            $table2,
            'Ketua Tim Akreditasi',
            $this->getFieldValue('ketua_tim_akreditasi', $studyProgram->ketua_tim_akreditasi)
        );
        $this->addRow(
            $table2,
            'Telp',
            $this->getFieldValue('ketua_tim_phone', $studyProgram->akreditasi_phone)
        );
        $this->addRow(
            $table2,
            'Mobile telp dan/atau WA',
            $this->getFieldValue('ketua_tim_mobile', $studyProgram->akreditasi_mobile)
        );
        $this->addRow(
            $table2,
            'Alamat Email',
            $this->getFieldValue('ketua_tim_email', $studyProgram->akreditasi_email)
        );

        // Spacer besar supaya tanda tangan turun ke bawah
        $section->addTextBreak(6);

        // ===== BLOK TANDA TANGAN (KANAN BAWAH) =====
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

        // ✅ Kota dan Tanggal dengan Prefill
        $kota = $this->getFieldValue(
            'pengesahan_kota',
            $studyProgram->city ?? $university->city, // Fallback ke university jika prodi tidak ada city
            '[Kota]'
        );
        $tanggalStr = $this->getFieldValue('tanggal_pengesahan', null);

        if ($tanggalStr) {
            $tanggal = $tanggalStr; // User sudah input manual
        } else {
            $tanggal = Date::tglIndo($this->pengajuan->created_at ?? now());
        }

        $run = $cell->addTextRun(['alignment' => Jc::START, 'spaceAfter' => 120]);
        $run->addText("{$kota}, {$tanggal}");

        $cell->addTextBreak(1);

        $run2 = $cell->addTextRun(['spaceAfter' => 120]);
        $run2->addText('Ttd dan stempel', ['color' => 'bbbbbb', 'italic' => true]);

        $cell->addTextBreak(1);

        $cell->addText(
            'Ketua Penjaminan Mutu Universitas/UPPS',
            ['size' => 11, 'color' => 'bbbbbb', 'italic' => true],
            ['spaceAfter' => 700]
        );

        // ✅ Nama dan NIP dengan Prefill
        $kaprodiName = $this->getFieldValue(
            'ketua_prodi_name',
            $studyProgram->ketua_prodi_name,
            '[Nama Ketua Program Studi]'
        );
        $kaprodiNIP = $this->getFieldValue(
            'ketua_prodi_nip',
            $studyProgram->ketua_prodi_nip,
            '[NIP]'
        );

        $cell->addText('Nama : ' . $kaprodiName, ['size' => 11], ['spaceAfter' => 80]);
        $cell->addText('NIP   : ' . $kaprodiNIP, ['size' => 11]);
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

        [$prefill, $placeholder, $isPlaceholder] = $this->getFieldPrefill(
            $this->borangDataMap,
            'kata_pengantar',
            $defaultKataPengantar,
            500
        );

        // ✅ Kotak + teks bawaan
        $this->addFrontMatterDescBox(
            $section,
            'Kata Pengantar (Mohon jangan dihapus)',
            500,
            $prefill,
            false,
            1800,
            10000,
            $placeholder,
            $isPlaceholder
        );

        // ✅ Penutup (kanan bawah) - PREFILL LOGIC
        $university = $this->pengajuan->studyProgram->university;
        $studyProgram = $this->pengajuan->studyProgram;

        $kota = $this->getFieldValue(
            'kata_pengantar_kota',
            $studyProgram->city,
            '[Nama Kota]'
        );

        // Tanggal
        $tanggalStr = $this->getFieldValue('kata_pengantar_tanggal', null);
        if ($tanggalStr) {
            $tanggal = $tanggalStr;
        } else {
            $tanggal = Date::tglIndo($this->pengajuan->created_at ?? now());
        }

        $kaprodiName = $this->getFieldValue(
            'kata_pengantar_kaprodi_name',
            $studyProgram->ketua_prodi_name,
            '[Nama Ketua Program Studi]'
        );

        $section->addText(
            "{$kota}, {$tanggal}",
            ['size' => 11],
            ['alignment' => Jc::END, 'spaceBefore' => 400, 'spaceAfter' => 100]
        );

        $section->addText(
            'Ketua Program Studi',
            ['size' => 11],
            ['alignment' => Jc::END, 'spaceAfter' => 900]
        );

        $section->addText(
            $kaprodiName,
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

        [$prefill, $placeholder, $isPlaceholder] = $this->getFieldPrefill($this->borangDataMap, 'ringkasan');

        // ✅ Kotak input seperti deskripsi box (max 1000 kata)
        $this->addFrontMatterDescBox(
            $section,
            'Ringkasan (Mohon jangan dihapus)',
            1000,
            $prefill,
            true,
            1800,
            3000,
            $placeholder,
            $isPlaceholder
        );
    }

    private function getFieldPrefill(
        array $borangDataMap,
        string $key,
        string|array|null $default = null,
        int $maxWords = 1000
    ): array {
        $value = trim((string)($borangDataMap[$key] ?? ''));

        if ($value !== '') {
            return [[$value], null, false]; // ✅ false = BUKAN placeholder
        }

        if (is_array($default)) {
            return [$default, null, true]; // ✅ default paragraf = placeholder/template
        }

        $placeholder = is_string($default)
            ? $default
            : "[Mohon isi {$key} di sini sesuai dengan kondisi program studi (maksimal {$maxWords} kata)...]";

        return [[], $placeholder, false]; // ✅ placeholder
    }

    private function addContentPages()
    {
        $kriterias = Kriteria::with([
            'elemenStandar.pernyataan',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.datasetBorang'
        ])->get();

        foreach ($kriterias as $indexKriteria => $kriteria) {

            // Section utama untuk teks kriteria + pernyataan + indikator (portrait)
            $restartNumbering = $indexKriteria == 0 && !$this->hasRestartedContentNumbering;
            $section = $this->createPortraitSection('arabic', $restartNumbering);
            if ($restartNumbering) {
                $this->hasRestartedContentNumbering = true;
            }
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

                // ✅ Detail section: portrait/landscape sesuai kebutuhan elemen (seperti Seeder)
                $detailSection = $this->elemenNeedsLandscape($elemen)
                    ? $this->createLandscapeSection('arabic')
                    : $this->createPortraitSection('arabic');

                // Kotak header elemen
                $this->addElemenBox($detailSection, $kriteria, $elemen);
                $detailSection->addTextBreak(0.5);

                // Kotak deskripsi + tabel menyatu (terisi data bila ada)
                $this->addDeskripsiBoxUnifiedWithData($detailSection, $kriteria, $elemen);

                // ✅ Jangan bikin section baru kalau elemen terakhir (hindari halaman kosong)
                if (!$isLastElemen) {
                    $section = $this->createPortraitSection();

                    // (opsional) cetak ulang header kriteria supaya rapi di halaman berikutnya
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
     * ✅ Kotak deskripsi dengan data terisi dari database
     */
    private function addDeskripsiBoxUnifiedWithData($section, $kriteria, $elemen): void
    {
        $hasTable = $elemen->datasetBorang && $elemen->datasetBorang->contains(fn($d) => $d->tipe_field === 'table');

        // ✅ kalau tidak ada table dataset: bikin 1 halaman penuh
        if (!$hasTable) {
            $this->addDeskripsiBoxFullPage($section, $kriteria, $elemen);
            return;
        }

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 100,
            'width' => 100 * 50,
            'unit' => 'pct'
        ]);

        $table->addRow();
        $cell = $table->addCell(9500);

        // ambil deskripsi dari map
        $descKey   = 'desc_' . $elemen->id;
        $rawText   = trim($this->borangDataMap[$descKey] ?? '');
        $isEmpty   = empty($rawText);

        // ✅ Label "Deskripsi..." hanya muncul jika KOSONG (template)
        if ($isEmpty) {
            $cell->addText(
                'Deskripsi ' . strtolower($elemen->pernyataan_elemen),
                ['size' => 11, 'italic' => true],
                ['spaceAfter' => 200]
            );

            $cell->addText(
                '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
                ['size' => 11, 'color' => '000000', 'italic' => true],
                ['spaceAfter' => 300, 'alignment' => Jc::BOTH]
            );
        } else {
            // ✅ Jika ada data: langsung tampilkan konten tanpa label
            try {
                $this->htmlParser->addHtmlContent($cell, $rawText);
            } catch (\Exception $e) {
                Log::warning("HTML parsing failed: " . $e->getMessage());

                $plainText = strip_tags($rawText);
                $chunks = $this->chunkTextSafe($plainText, 3000);

                foreach ($chunks as $chunk) {
                    if (trim($chunk) === '') continue;
                    $cell->addText($chunk, ['size' => 11], ['alignment' => Jc::BOTH, 'spaceAfter' => 200]);
                }
            }
        }

        // render semua tabel di dalam kotak yang sama
        if ($elemen->datasetBorang && $elemen->datasetBorang->count() > 0) {
            foreach ($elemen->datasetBorang as $dataset) {
                if ($dataset->tipe_field !== 'table') continue;

                $cell->addText($dataset->nama, ['size' => 11, 'bold' => true], ['spaceAfter' => 100]);

                $tableData =
                    $this->borangDataMap['dataset_kode:' . $dataset->kode] ??
                    $this->borangDataMap['dataset_id:' . $dataset->id] ??
                    null;

                if ($tableData) {
                    $this->addHtmlTableToCell($cell, $tableData);
                } else {
                    $this->addDatasetTable($cell, $dataset);
                }

                $cell->addTextBreak(1);
            }
        }
    }

    // ✅ Methods lainnya sama seperti BorangExampleSeeder
    // (addElemenBox, addDatasetTable, addPernyataanStandar, etc)

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

    private function addDatasetTable($cell, $dataset)
    {
        $columns = $dataset->expected_columns ?? ['No', 'Keterangan', 'Data'];
        $columnCount = count($columns);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'width' => 100 * 50,
            'unit' => 'pct',
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_AUTO, // ✅ AutoFit
        ];

        $dataTable = $cell->addTable($tableStyle);

        // Header
        $dataTable->addRow(400);
        foreach ($columns as $col) {
            $headerCell = $dataTable->addCell(
                intval(100 / $columnCount) * 50, // Width in percentage
                [
                    'bgColor' => 'D3D3D3',
                    'valign' => 'center', // ✅ Vertical align
                    'unit' => 'pct'
                ]
            );
            $headerCell->addText(
                $col,
                ['bold' => true, 'size' => 10],
                [
                    'alignment' => Jc::CENTER,
                    'spaceAfter' => 0,
                    'spaceBefore' => 0
                ]
            );
        }

        // Empty rows
        for ($i = 1; $i <= 3; $i++) {
            $dataTable->addRow(350);
            foreach ($columns as $index => $col) {
                $rowCell = $dataTable->addCell(
                    intval(100 / $columnCount) * 50,
                    ['valign' => 'center', 'unit' => 'pct']
                );
                $rowCell->addText(
                    $index === 0 ? (string)$i : '',
                    ['size' => 10],
                    [
                        'alignment' => $index === 0 ? Jc::CENTER : Jc::START,
                        'spaceAfter' => 0
                    ]
                );
            }
        }
    }

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

    private function addSuplemenSection()
    {
        $pdfPath = $this->getPengajuanDokumenPath('data_suplemen');

        if ($pdfPath) {
            // suplemen masuk konten utama? kamu sekarang pakai arabic
            $this->addPdfAsImagesToWord($pdfPath, 'arabic');
            return;
        }

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

    /**
     * ✅ Pernyataan Standar (JUSTIFY)
     */
    private function addPernyataanStandar($section, $elemen)
    {
        $section->addText(
            'Pernyataan Standar',
            ['size' => 11, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE, 'color' => $this->templateBlue],
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
                ['size' => 11, 'italic' => true, 'color' => $this->templateBlue],
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
            ['size' => 11, 'bold' => true, 'color' => $this->templateBlue, 'underline' => Font::UNDERLINE_SINGLE],
            ['spaceAfter' => 200]
        );

        if (!$elemen->indikator || $elemen->indikator->count() === 0) {
            $section->addText('[Tidak ada indikator]', ['size' => 11, 'italic' => true, 'color' => '000000']);
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
                $section->addText(trim($m[1]), ['size' => 11, 'color' => $this->templateBlue], ['alignment' => Jc::BOTH, 'spaceAfter' => 150]);
            }

            preg_match_all('/\(\d+\)\.\s*(.*?)(?=\(\d+\)\.|$)/s', $text, $matches);

            foreach ($matches[1] as $item) {
                $section->addListItem(
                    $item,
                    0,
                    ['size' => 11, 'color' => $this->templateBlue],
                    $numberingName,
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 120]
                );
            }
        }
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

    /**
     * ✅ PERBAIKAN: Create Landscape Section dengan Page Number Control
     */
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
        $reservedTopTwips = $this->isLandscape ? 2200 : 2600;
        $boxHeight = max(2200, $this->contentHeightTwips - $reservedTopTwips);

        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 100,
            'width'       => 100 * 50,
            'unit'        => 'pct',
        ]);

        $table->addRow($boxHeight, ['exactHeight' => false]);
        $cell = $table->addCell(9500, ['valign' => 'top']);

        $descKey   = 'desc_' . $elemen->id;
        $rawText   = trim($this->borangDataMap[$descKey] ?? '');
        $filled    = !empty($rawText);

        // ✅ Label hanya muncul jika KOSONG (template)
        if (!$filled) {
            $cell->addText(
                'Deskripsi ' . strtolower($elemen->pernyataan_elemen),
                ['size' => 11, 'italic' => true],
                ['spaceAfter' => 200]
            );

            $cell->addText(
                '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
                ['size' => 11, 'color' => '000000', 'italic' => true],
                ['alignment' => Jc::BOTH]
            );
            return;
        }

        // ✅ Jika ada data: langsung parse HTML tanpa label
        try {
            $this->htmlParser->addHtmlContent($cell, $rawText);
        } catch (\Exception $e) {
            Log::warning("HTML parsing failed for desc_{$elemen->id}: " . $e->getMessage());

            $plainText = strip_tags($rawText);
            $chunks = $this->chunkTextSafe($plainText, 3000);

            foreach ($chunks as $chunk) {
                if (trim($chunk) === '') continue;
                $cell->addText(
                    $chunk,
                    ['size' => 11, 'color' => '000000'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
                );
            }
        }
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
                        ['size' => 11, 'bold' => $format['bold'] ?? false, 'color' => $this->templateBlue],
                        $numberingName,
                        [
                            'spaceAfter' => $format['spaceAfter'] ?? null,
                        ]
                    );
                    break;

                case 'paragraph':
                    $section->addText(
                        $item->text_content,
                        ['size' => 11, 'color' => $this->templateBlue],
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
                        ['size' => 11, 'color' => $this->templateBlue],
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

    private function addLogoFlexible($section, $logoPath, $maxWidth = 200, $maxHeight = 200)
    {
        if (!file_exists($logoPath)) return;

        [$origWidth, $origHeight] = getimagesize($logoPath);
        $ratio = $origWidth / $origHeight;

        if ($ratio > 1) {          // Landscape
            $width = $maxWidth;
            $height = $maxWidth / $ratio;
        } elseif ($ratio < 1) {    // Portrait
            $height = $maxHeight;
            $width = $maxHeight * $ratio;
        } else {                   // Square
            $width = $maxWidth;
            $height = $maxHeight;
        }

        $section->addImage($logoPath, [
            'width' => $width,
            'height' => $height,
            'alignment' => Jc::CENTER
        ]);
    }

    private function loadBorangDataMap(): void
    {
        // kalau relasi dataset borang namanya "datasetBorang", pakai with
        $borangData = BorangData::with('datasetBorang')
            ->where('id_pengajuan', $this->pengajuan->id)
            ->get();

        foreach ($borangData as $row) {
            // 1) key utama: dataset_id (STRING seperti ringkasan, kata_pengantar, desc_27, suplemen)
            if (!empty($row->dataset_id)) {
                $this->borangDataMap[$row->dataset_id] = $row->nilai;
            }

            // 2) key by FK dataset borang (kalau kamu butuh match via id_dataset_borang)
            if (!empty($row->id_dataset_borang)) {
                $this->borangDataMap['dataset_id:' . $row->id_dataset_borang] = $row->nilai;
            }

            // 3) key by kode dataset (untuk tabel)
            if ($row->datasetBorang?->kode) {
                $this->borangDataMap['dataset_kode:' . $row->datasetBorang->kode] = $row->nilai;
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

    private function addFrontMatterDescBox(
        \PhpOffice\PhpWord\Element\Section $section,
        string $label,
        int $maxWords,
        array $prefillParagraphs = [],
        bool $fullHeight = false,
        int $reservedTopTwips = 1800,
        int $minBoxHeightTwips = 3000,
        ?string $emptyPlaceholder = null,
        bool $isPlaceholder = true
    ): void {

        if ($emptyPlaceholder === null) {
            $emptyPlaceholder = "[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal {$maxWords} kata)...]";
        }

        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 110,
            'width'       => 100 * 50,
            'unit'        => 'pct',
        ];

        $table = $section->addTable($tableStyle);

        if ($fullHeight) {
            $boxHeight = max($minBoxHeightTwips, $this->contentHeightTwips - $reservedTopTwips);
            $table->addRow($boxHeight, ['exactHeight' => false]);
        } else {
            $table->addRow();
        }

        $cell = $table->addCell(9500, ['valign' => 'top']);

        // ✅ Cek apakah ada data user
        $hasPrefill = !empty(array_filter($prefillParagraphs, fn($p) => trim((string)$p) !== ''));

        // ✅ Label hanya muncul jika masih template (belum ada data)
        if (!$hasPrefill) {
            $cell->addText(
                $label,
                ['size' => 11, 'italic' => true, 'color' => $this->templateBlue],
                ['spaceAfter' => 200]
            );
        }

        // kalau kosong -> tampil placeholder
        if (!$hasPrefill) {
            $cell->addText(
                $emptyPlaceholder,
                ['size' => 11, 'color' => '000000'],
                ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
            );
            return;
        }

        // ✅ Kalau ada data user: langsung tampilkan tanpa label
        foreach ($prefillParagraphs as $p) {
            $p = trim((string)$p);
            if ($p === '') continue;

            try {
                if (strip_tags($p) !== $p) {
                    // Contains HTML
                    $this->htmlParser->addHtmlContent($cell, $p);
                } else {
                    // Plain text
                    $chunks = $this->chunkTextSafe($p, 3000);

                    foreach ($chunks as $chunk) {
                        $cell->addText(
                            $chunk,
                            ['size' => 11, 'color' => '000000'],
                            ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::warning("HTML parsing failed: " . $e->getMessage());

                $plainText = strip_tags($p);
                $cell->addText($plainText, ['size' => 11], ['alignment' => Jc::BOTH, 'spaceAfter' => 200]);
            }
        }

        // ✅ Hint max kata hanya untuk placeholder/template
        if ($isPlaceholder && !$hasPrefill) {
            $cell->addText(
                "(Maksimal {$maxWords} kata)",
                ['size' => 10, 'color' => '000000'],
                ['alignment' => Jc::END]
            );
        }
    }

    private function getPengajuanDokumenPath(string $jenis): ?string
    {
        $doc = PengajuanDokumen::query()
            ->where('id_pengajuan', $this->pengajuan->id)
            ->where('jenis_dokumen', $jenis)
            ->latest('id')
            ->first();
        if (!$doc) return null;

        // ✅ sesuaikan kolom path kamu: kadang namanya 'path' / 'file_path' / 'lokasi'
        $relativePath = $doc->path_file ?? null;
        if (!$relativePath) return null;

        // Umumnya file disimpan di storage/app/public/...
        $fullPath = storage_path('app/public/' . ltrim($relativePath, '/'));

        return file_exists($fullPath) ? $fullPath : null;
    }

    /**
     * Convert PDF menjadi image per halaman (PNG).
     * Return array path PNG.
     */
    private function pdfToPngPages(string $pdfPath, int $dpi = 200): array
    {
        if (!extension_loaded('imagick')) {
            $section = $this->phpWord->addSection();
            $section->addText('⚠ Dokumen PDF tidak dapat dimuat karena Imagick belum tersedia.', ['color' => '000000']);
            return [];
        }

        if (!file_exists($pdfPath)) {
            throw new \RuntimeException("File PDF tidak ditemukan: {$pdfPath}");
        }
        $ghostscript = $this->ensureGhostscriptLocal();

        // PDF page images are internal export artifacts; they do not need to be
        // exposed through /storage and should not depend on public-disk ACLs.
        $tmpDir = storage_path("app/tmp_pdf_export/{$this->pengajuan->id}");
        $this->ensureTmpPdfExportDirectory($tmpDir);

        try {
            $probe = new \Imagick();
            $probe->setResolution(72, 72);
            $probe->setOption('pdf:use-cropbox', 'true');
            $probe->pingImage($pdfPath);
            $pageCount = $probe->getNumberImages();
            $probe->clear();
            $probe->destroy();

            $pages = [];
            for ($i = 0; $i < $pageCount; $i++) {
                $out = $tmpDir . '/' . md5($pdfPath) . "_page_" . $i . ".png";
                $lastError = null;
                $renderedPath = $this->renderPdfPageImage($pdfPath, $i, $out, $dpi, 'png', $lastError);

                if ($renderedPath) {
                    $pages[] = $renderedPath;
                    continue;
                }

                $fallbackOut = $tmpDir . '/' . md5($pdfPath) . "_page_" . $i . ".jpg";
                $renderedPath = $this->renderPdfPageImage($pdfPath, $i, $fallbackOut, 150, 'jpeg', $lastError);

                if (!$renderedPath) {
                    $renderedPath = $this->renderPdfPageWithGhostscript(
                        $ghostscript,
                        $pdfPath,
                        $i,
                        $fallbackOut,
                        150,
                        $lastError
                    );
                }

                if ($renderedPath) {
                    $pages[] = $renderedPath;
                    continue;
                }

                Log::warning('Skipping invalid PDF page image during DOCX export', [
                    'pdf' => $pdfPath,
                    'page' => $i,
                    'reason' => $lastError,
                ]);
            }

            return $pages;
        } catch (ImagickException $e) {
            throw new \RuntimeException("Gagal convert PDF ke PNG: " . $e->getMessage(), 0, $e);
        }
    }

    private function renderPdfPageImage(
        string $pdfPath,
        int $pageIndex,
        string $out,
        int $dpi,
        string $format = 'png',
        ?string &$error = null
    ): ?string {
        $error = null;
        $page = null;

        try {
            if (!is_dir(dirname($out)) || !is_writable(dirname($out))) {
                throw new \RuntimeException("Direktori output tidak writable: " . dirname($out));
            }

            @unlink($out);
            $page = new \Imagick();
            $page->setResolution($dpi, $dpi);
            $page->setOption('pdf:use-cropbox', 'true');
            $page->readImage($pdfPath . '[' . $pageIndex . ']');
            $page->setIteratorIndex(0);
            $page->setImageBackgroundColor('white');

            if ($page->getImageAlphaChannel()) {
                $page = $page->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            }

            $page->setImageFormat($format);
            $page->setImageCompressionQuality(90);
            $page->stripImage();
            if (!$page->writeImage($out)) {
                throw new \RuntimeException("ImageMagick gagal menulis {$out}");
            }

            if ($this->isUsableImageForPhpWord($out)) {
                return $out;
            }

            throw new \RuntimeException("ImageMagick menghasilkan image tidak valid: {$out}");
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            @unlink($out);
        } finally {
            if ($page instanceof \Imagick) {
                $page->clear();
                $page->destroy();
            }
        }

        return null;
    }

    private function renderPdfPageWithGhostscript(
        string $ghostscript,
        string $pdfPath,
        int $pageIndex,
        string $out,
        int $dpi,
        ?string &$error = null
    ): ?string {
        try {
            if (!is_dir(dirname($out)) || !is_writable(dirname($out))) {
                throw new \RuntimeException("Direktori output tidak writable: " . dirname($out));
            }

            @unlink($out);
            $process = new Process([
                $ghostscript,
                '-dSAFER',
                '-dBATCH',
                '-dNOPAUSE',
                '-dQUIET',
                '-dUseCropBox',
                '-sDEVICE=jpeg',
                '-dJPEGQ=90',
                "-r{$dpi}",
                '-dFirstPage=' . ($pageIndex + 1),
                '-dLastPage=' . ($pageIndex + 1),
                '-sOutputFile=' . $out,
                $pdfPath,
            ]);
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Ghostscript gagal merender halaman PDF');
            }

            if (!$this->isUsableImageForPhpWord($out)) {
                throw new \RuntimeException("Ghostscript menghasilkan image tidak valid: {$out}");
            }

            $error = null;
            return $out;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            @unlink($out);
        }

        return null;
    }

    /**
     * Sisipkan PDF sebagai halaman-halaman gambar di Word.
     * Cocok untuk "lembar_pengesahan" & "suplemen".
     *
     * - $numberType: 'roman' atau 'arabic'
     * - $restartAt: kalau mau restart numbering dari 1 (opsional)
     */
    private function addPdfAsImagesToWord(
        string $pdfPath,
        string $numberType = 'roman',
        ?int $restartAt = null,
        int $dpi = 200
    ): void {
        $images = $this->pdfToPngPages($pdfPath, $dpi);

        foreach ($images as $idx => $imgPath) {
            $section = $this->phpWord->addSection([
                'marginTop' => 0,
                'marginBottom' => 0,
                'marginLeft' => 0,
                'marginRight' => 0,
            ]);

            // restart hanya di halaman pertama insert pdf (kalau diminta)
            $this->applyFooterPageNumber($section, $numberType, ($idx === 0 ? $restartAt : null));

            // width ini biasanya cukup pas A4 dengan margin 700
            // kalau terlalu besar/kecil tinggal adjust
            $imgPath = $this->persistImage($imgPath);

            $section->addImage($imgPath, [
                'width' => 520,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
        }
    }

    /**
     * Optional: bersihkan file png sementara
     */
    public function cleanupTmpPdfImages(): void
    {
        $tmpDir = storage_path("app/tmp_pdf_export/{$this->pengajuan->id}");
        if (!is_dir($tmpDir)) return;
        foreach (glob($tmpDir . '/*') as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($tmpDir);
        $this->htmlParser->cleanup();
    }


    private function ensureGhostscriptLocal(): string
    {
        $gsBase = public_path('gs');

        // Cari semua kandidat bin, baik "bin" langsung atau versi gsX.Y.Z/bin
        $gsBins = [];

        // 1. Folder stabil: gs/bin
        $stableBin = $gsBase . DIRECTORY_SEPARATOR . 'bin';
        if (is_dir($stableBin)) {
            $gsBins[] = $stableBin;
        }

        // 2. Subfolder versi: gs/gs*/bin
        foreach (glob($gsBase . DIRECTORY_SEPARATOR . 'gs*') as $subfolder) {
            $binPath = $subfolder . DIRECTORY_SEPARATOR . 'bin';
            if (is_dir($binPath)) {
                $gsBins[] = $binPath;
            }
        }

        // Tentukan nama executable sesuai OS
        $gsCandidates = [];
        if (PHP_OS_FAMILY === 'Windows') {
            foreach ($gsBins as $bin) {
                $gsCandidates[] = $bin . DIRECTORY_SEPARATOR . 'gswin64c.exe';
                $gsCandidates[] = $bin . DIRECTORY_SEPARATOR . 'gswin32c.exe';
                $gsCandidates[] = $bin . DIRECTORY_SEPARATOR . 'gs.exe';
            }
            $pathSeparator = ';';
        } else {
            foreach ($gsBins as $bin) {
                $gsCandidates[] = $bin . DIRECTORY_SEPARATOR . 'gs';
            }
            // fallback: yang ada di sistem
            $gsCandidates[] = trim((string) shell_exec('command -v gs'));
            $pathSeparator = ':';
        }

        // Cari gs yang benar-benar ada
        $gsFound = null;
        $gsBinReal = null;
        foreach ($gsCandidates as $cand) {
            if ($cand && file_exists($cand)) {
                $gsFound = $cand;
                $gsBinReal = dirname($cand);
                break;
            }
        }

        if (!$gsFound) {
            throw new \RuntimeException(
                "Ghostscript tidak ditemukan. Pastikan ada folder 'gs/bin' atau 'gs/gsX.Y.Z/bin'"
            );
        }

        // Pastikan executable (Linux/macOS)
        if (PHP_OS_FAMILY !== 'Windows') {
            @chmod($gsFound, 0755);
        }

        // Tambahkan folder gs ke PATH proses PHP
        $path = getenv('PATH') ?: '';
        if (stripos($path, $gsBinReal) === false) {
            $newPath = $gsBinReal . $pathSeparator . $path;
            putenv("PATH={$newPath}");
            $_SERVER['PATH'] = $newPath;
            $_ENV['PATH'] = $newPath;
        }

        // Set env untuk ImageMagick
        putenv("MAGICK_GHOSTSCRIPT_PATH={$gsBinReal}");
        $_SERVER['MAGICK_GHOSTSCRIPT_PATH'] = $gsBinReal;
        $_ENV['MAGICK_GHOSTSCRIPT_PATH'] = $gsBinReal;

        return $gsFound;
    }

    private function persistImage(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Image not found: {$path}");
        }

        if (!$this->isUsableImageForPhpWord($path)) {
            throw new \RuntimeException("Invalid image: {$path}");
        }

        $dir = storage_path("app/tmp_pdf_export/{$this->pengajuan->id}");
        $this->ensureTmpPdfExportDirectory($dir);

        $newPath = $dir . '/' . basename($path);
        if ($path !== $newPath) {
            copy($path, $newPath);
        }

        return $newPath;
    }

    private function ensureTmpPdfExportDirectory(string $dir): void
    {
        if (is_dir($dir) && is_writable($dir)) {
            return;
        }

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Gagal membuat direktori temporary PDF export: {$dir}");
        }

        @chmod($dir, 0775);

        if (!is_writable($dir)) {
            throw new \RuntimeException("Direktori temporary PDF export tidak writable: {$dir}");
        }
    }

    private function isUsableImageForPhpWord(string $path): bool
    {
        if (!is_file($path) || filesize($path) <= 0) {
            return false;
        }

        $info = @getimagesize($path);

        return is_array($info)
            && ($info[0] ?? 0) > 0
            && ($info[1] ?? 0) > 0
            && in_array($info[2] ?? null, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_BMP], true);
    }

    /**
     * ✅ Split text jadi chunks TANPA memotong konten
     * PhpWord has ~32KB limit per addText() call
     */
    private function chunkTextSafe(string $text, int $maxChars = 3000): array
    {
        // Jika text pendek, langsung return
        if (strlen($text) <= $maxChars) {
            return [$text];
        }

        $chunks = [];

        // Split by paragraph (double newline atau single newline)
        $paragraphs = preg_split('/\n+/', $text);

        $currentChunk = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') continue;

            // Jika paragraph sendiri > maxChars, split by sentences
            if (strlen($para) > $maxChars) {
                // Flush current chunk dulu
                if ($currentChunk !== '') {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                }

                // Split paragraph by sentences
                $sentences = $this->splitIntoSentences($para);

                foreach ($sentences as $sentence) {
                    // Jika single sentence > maxChars, force split by characters
                    if (strlen($sentence) > $maxChars) {
                        if ($currentChunk !== '') {
                            $chunks[] = $currentChunk;
                            $currentChunk = '';
                        }

                        // Force split by maxChars
                        $parts = str_split($sentence, $maxChars);
                        foreach ($parts as $part) {
                            $chunks[] = $part;
                        }
                    } else {
                        // Check if adding sentence exceeds limit
                        if (strlen($currentChunk . ' ' . $sentence) > $maxChars) {
                            if ($currentChunk !== '') {
                                $chunks[] = $currentChunk;
                            }
                            $currentChunk = $sentence;
                        } else {
                            $currentChunk .= ($currentChunk ? ' ' : '') . $sentence;
                        }
                    }
                }
            } else {
                // Normal paragraph
                // Check if adding this paragraph exceeds limit
                $separator = $currentChunk ? "\n\n" : '';
                if (strlen($currentChunk . $separator . $para) > $maxChars) {
                    if ($currentChunk !== '') {
                        $chunks[] = $currentChunk;
                    }
                    $currentChunk = $para;
                } else {
                    $currentChunk .= $separator . $para;
                }
            }
        }

        // Add remaining chunk
        if ($currentChunk !== '') {
            $chunks[] = $currentChunk;
        }

        return empty($chunks) ? [$text] : $chunks;
    }

    /**
     * ✅ Split text into sentences
     */
    private function splitIntoSentences(string $text): array
    {
        // Split by period, exclamation, question mark followed by space
        $sentences = preg_split(
            '/(?<=[.!?])\s+(?=[A-Z])/',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        return $sentences ?: [$text];
    }

    /**
     * ✅ FIXED: Convert HTML table string to PhpWord table menggunakan HtmlParser
     */
    private function addHtmlTableToCell($cell, $htmlTable)
    {
        // ✅ Gunakan HTML parser yang sama
        try {
            $this->htmlParser->addHtmlContent($cell, $htmlTable);
        } catch (\Exception $e) {
            Log::warning("Failed to parse HTML table: " . $e->getMessage());

            // Fallback ke simple parser
            $this->addHtmlTableToCell_Fallback($cell, $htmlTable);
        }
    }

    /**
     * ✅ Fallback: Simple regex-based parser (backup)
     */
    private function addHtmlTableToCell_Fallback($cell, $htmlTable)
    {
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $htmlTable, $rows);

        if (empty($rows[1])) {
            return;
        }

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'width' => 100,
            'unit' => 'pct',
        ];

        $dataTable = $cell->addTable($tableStyle);

        foreach ($rows[1] as $rowIndex => $rowHtml) {
            preg_match_all('/<t[hd][^>]*>(.*?)<\/t[hd]>/is', $rowHtml, $cells);

            if (empty($cells[1])) continue;

            $dataTable->addRow($rowIndex === 0 ? 400 : 350);

            foreach ($cells[1] as $cellHtml) {
                $cellText = strip_tags($cellHtml);
                $cellText = html_entity_decode($cellText);
                $cellText = trim($cellText);

                $cellStyle = $rowIndex === 0 ? ['bgColor' => 'D3D3D3'] : [];
                $fontStyle = $rowIndex === 0 ? ['bold' => true, 'size' => 10] : ['size' => 10];

                $tableCell = $dataTable->addCell(null, $cellStyle);
                $tableCell->addText($cellText, $fontStyle, ['alignment' => Jc::CENTER]);
            }
        }
    }

    /**
     * ✅ Get field value dengan fallback ke database
     * Priority: BorangDataMap → Database → Default
     */
    private function getFieldValue(string $key, $dbValue = null, $default = ''): string
    {
        // 1. Cek di borangDataMap dulu (user input)
        $mapValue = trim($this->borangDataMap[$key] ?? '');
        if ($mapValue !== '') {
            return $mapValue;
        }

        // 2. Fallback ke database
        if ($dbValue !== null && trim((string)$dbValue) !== '') {
            return trim((string)$dbValue);
        }

        // 3. Default placeholder
        return $default;
    }
}
