<?php
// app/Services/BorangExportService.php

namespace App\Services;

use App\Models\Kriteria;
use App\Models\BorangData;
use App\Models\DegreeLevel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use App\Models\PengajuanAkreditasi;
use PhpOffice\PhpWord\SimpleType\Jc;

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

    public function __construct($pengajuan)
    {
        $this->pengajuan = $pengajuan;
        $this->phpWord = new PhpWord();

        // Load existing borang data
        $borangData = BorangData::where('id_pengajuan', $pengajuan->id)->get();
        foreach ($borangData as $data) {
            $this->borangDataMap[$data->dataset_id] = $data->value;
        }

        $this->phpWord->setDefaultFontName('Montserrat');
        $this->phpWord->setDefaultFontSize(11);
    }

    public function generate()
    {
        $this->addCoverPage();
        $this->addLembarPengesahan();
        $this->addKataPengantarSection();
        $this->addRingkasanSection();
        $this->addContentPages();
        $this->addSuplemenSection();

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
        if ($this->pengajuan->studyProgram->university->logo) {
            $section->addImage(
                storage_path('app/public/' . $this->pengajuan->studyProgram->university->logo),
                ['width' => 100, 'height' => 100, 'alignment' => Jc::CENTER]
            );
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
        $textRun->addText(date('F, Y', strtotime($this->pengajuan->created_at)), ['size' => 12]);
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
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.datasetBorang'
        ])->get();

        foreach ($kriterias as $indexKriteria => $kriteria) {

            // Section utama (portrait) untuk teks kriteria + pernyataan + indikator
            $section = $this->createPortraitSection();

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

                // ✅ Detail section: portrait/landscape sesuai kebutuhan elemen (seperti Seeder)
                $detailSection = $this->elemenNeedsLandscape($elemen)
                    ? $this->createLandscapeSection()
                    : $this->createPortraitSection();

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

        $cell->addText(
            'Deskripsi ' . strtolower($elemen->pernyataan_elemen),
            ['size' => 11, 'italic' => true],
            ['spaceAfter' => 200]
        );

        // ambil deskripsi dari map Anda
        $descKey   = 'desc_' . $elemen->id;
        $deskripsi = $this->borangDataMap[$descKey] ?? '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]';

        $cell->addText(
            $deskripsi,
            ['size' => 11, 'color' => empty($this->borangDataMap[$descKey]) ? 'FF0000' : '000000', 'italic' => empty($this->borangDataMap[$descKey])],
            ['spaceAfter' => 300, 'alignment' => Jc::BOTH]
        );

        // render semua tabel di dalam kotak yang sama (seperti seeder addDeskripsiBoxUnified)
        if ($elemen->datasetBorang && $elemen->datasetBorang->count() > 0) {
            foreach ($elemen->datasetBorang as $dataset) {
                if ($dataset->tipe_field !== 'table') continue;

                $cell->addText($dataset->nama, ['size' => 11, 'bold' => true], ['spaceAfter' => 100]);

                $tableData = $this->borangDataMap[$dataset->kode] ?? null;

                if ($tableData) {
                    $this->addHtmlTableToCell($cell, $tableData);
                } else {
                    $this->addDatasetTable($cell, $dataset);
                }

                $cell->addTextBreak(1);
            }
        }
    }


    /**
     * ✅ Convert HTML table string to PhpWord table
     */
    private function addHtmlTableToCell($cell, $htmlTable)
    {
        // Simple HTML parser untuk extract table data
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
            // Extract cells
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
            'width' => 100,
            'unit' => 'pct',
        ];

        $dataTable = $cell->addTable($tableStyle);

        // Header
        $dataTable->addRow(400);
        foreach ($columns as $col) {
            $headerCell = $dataTable->addCell(
                intval(100 / $columnCount),
                ['bgColor' => 'D3D3D3', 'valign' => 'center']
            );
            $headerCell->addText($col, ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
        }

        // Empty rows
        for ($i = 1; $i <= 3; $i++) {
            $dataTable->addRow(350);
            foreach ($columns as $index => $col) {
                $rowCell = $dataTable->addCell(intval(100 / $columnCount));
                $rowCell->addText(
                    $index === 0 ? (string)$i : '',
                    ['size' => 10],
                    ['alignment' => $index === 0 ? Jc::CENTER : Jc::START]
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
            case 'd1':
            case 'd2':
            case 'd3':
                $this->renderSuplemenDiploma123($section, $numberingName);
                break;

            case 'd4':
            case 's1-terapan':
                $this->renderSuplemenSarjanaTerapan($section, $numberingName);
                break;

            case 's1':
                $this->renderSuplemenSarjana($section, $numberingName);
                break;

            case 'profesi':
                $this->renderSuplemenProfesi($section, $numberingName);
                break;

            case 's2':
                $this->renderSuplemenMagister($section, $numberingName);
                break;

            case 's2-terapan':
                $this->renderSuplemenMagisterTerapan($section, $numberingName);
                break;

            case 's3':
                $this->renderSuplemenDoktor($section, $numberingName);
                break;

            case 's3-terapan':
                $this->renderSuplemenDoktorTerapan($section, $numberingName);
                break;

            default:
                // fallback: kalau kode tidak dikenali, tampilkan yang umum (mis. sarjana)
                $this->renderSuplemenSarjana($section, $numberingName);
                break;
        }
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
        $table->addRow($boxHeight, ['exactHeight' => true]);
        $cell = $table->addCell(9500, ['valign' => 'top']);

        // Header kecil di dalam kotak
        $cell->addText(
            'Deskripsi ' . strtolower($elemen->pernyataan_elemen),
            ['size' => 11, 'italic' => true],
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
                'color'  => $filled ? '000000' : 'FF0000',
                'italic' => !$filled,
            ],
            ['alignment' => Jc::BOTH]
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
}
