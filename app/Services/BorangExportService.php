<?php
// app/Services/BorangExportService.php

namespace App\Services;

use App\Models\PengajuanAkreditasi;
use App\Models\Kriteria;
use App\Models\BorangData;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;

class BorangExportService
{
    protected PhpWord $phpWord;
    protected array $numberingRegistered = [];
    protected $pengajuan;
    protected $borangDataMap = [];

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

        $section->addText(
            'LEMBAR PENGESAHAN',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );

        $this->phpWord->addTableStyle('FormTable', [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
        ]);

        // Lembaga Penjaminan Mutu
        $section->addText('Lembaga Penjaminan Mutu', ['size' => 11, 'bold' => true], ['spaceAfter' => 120]);

        $table1 = $section->addTable('FormTable');
        $this->addRow($table1, 'Universitas/Institut', $this->pengajuan->studyProgram->university->name ?? '');
        $this->addRow($table1, 'Lembaga Penjaminan Mutu', '');
        $this->addRow($table1, 'Telp', '');
        $this->addRow($table1, 'Mobile telp dan/atau WA', '');
        $this->addRow($table1, 'Alamat Email', '');

        $section->addTextBreak(1);

        // Program Studi
        $section->addText('Program Studi Akreditasi', ['size' => 11, 'bold' => true], ['spaceAfter' => 120]);

        $table2 = $section->addTable('FormTable');
        $this->addRow($table2, 'Program Studi', $this->pengajuan->studyProgram->name);
        $this->addRow($table2, 'Akreditasi', $this->pengajuan->studyProgram->accreditation_status ?? '');
        $this->addRow($table2, 'Ketua Tim Akreditasi', $this->pengajuan->user->name ?? '');
        $this->addRow($table2, 'Telp', $this->pengajuan->user->phone ?? '');
        $this->addRow($table2, 'Mobile telp dan/atau WA', $this->pengajuan->user->phone ?? '');
        $this->addRow($table2, 'Alamat Email', $this->pengajuan->user->email ?? '');

        $section->addTextBreak(6);

        // Tanda tangan
        $this->phpWord->addTableStyle('SignTable', [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
        ]);

        $signTable = $section->addTable('SignTable');
        $signTable->addRow();
        $signTable->addCell(5200)->addText(' ');

        $cell = $signTable->addCell(3500);
        $cell->addText(
            date('d F Y', strtotime($this->pengajuan->created_at)),
            ['size' => 11],
            ['alignment' => Jc::START, 'spaceAfter' => 120]
        );

        $cell->addTextBreak(3);
        $cell->addText('Ketua Penjaminan Mutu', ['size' => 11], ['spaceAfter' => 700]);
        $cell->addText('Nama : ___________________', ['size' => 11], ['spaceAfter' => 80]);
        $cell->addText('NIP   : ___________________', ['size' => 11]);
    }

    private function addKataPengantarSection()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);

        $section->addText(
            'KATA PENGANTAR',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );

        // ✅ ISI KATA PENGANTAR DARI DATABASE (jika ada)
        $kataPengantar = $this->borangDataMap['kata_pengantar'] ?? '';
        if ($kataPengantar) {
            $section->addText($kataPengantar, ['size' => 11], ['alignment' => Jc::BOTH]);
        }
    }

    private function addRingkasanSection()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);

        $section->addText(
            'RINGKASAN',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );

        // ✅ ISI RINGKASAN DARI DATABASE (jika ada)
        $ringkasan = $this->borangDataMap['ringkasan'] ?? '';
        if ($ringkasan) {
            $section->addText($ringkasan, ['size' => 11], ['alignment' => Jc::BOTH]);
        }
    }

    private function addContentPages()
    {
        $kriterias = Kriteria::with([
            'elemenStandar.pernyataan',
            'elemenStandar.indikator',
            'elemenStandar.datasetBorang'
        ])->orderBy('kode_kriteria')->get();

        foreach ($kriterias as $kriteria) {
            $section = $this->phpWord->addSection([
                'marginTop' => 1000,
                'marginBottom' => 1000,
                'marginLeft' => 1500,
                'marginRight' => 1500,
            ]);

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

            $countElemen = $kriteria->elemenStandar->count();

            foreach ($kriteria->elemenStandar as $key => $elemen) {

                // Judul elemen
                $section->addText(
                    ($key + 1) . '. ' . $elemen->kode_elemen . '. ' . $elemen->pernyataan_elemen,
                    ['size' => 11, 'bold' => true],
                    ['spaceAfter' => 150]
                );

                // Pernyataan Standar
                $this->addPernyataanStandar($section, $elemen);
                $section->addTextBreak(1);

                // Uraian Indikator
                $this->addUraianIndikator($section, $elemen);
                $section->addPageBreak();

                // Kotak Elemen
                $this->addElemenBox($section, $kriteria, $elemen);
                $section->addTextBreak(0.5);

                // ✅ KOTAK DESKRIPSI DENGAN DATA DARI DATABASE
                $this->addDeskripsiBoxWithData($section, $elemen);

                if ($key + 1 != $countElemen) {
                    $section->addPageBreak();
                }
            }
        }
    }

    /**
     * ✅ Kotak deskripsi dengan data terisi dari database
     */
    private function addDeskripsiBoxWithData($section, $elemen)
    {
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

        // ✅ AMBIL DESKRIPSI DARI DATABASE
        $descKey = 'desc_' . $elemen->id;
        $deskripsi = $this->borangDataMap[$descKey] ?? '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]';

        $cell->addText(
            $deskripsi,
            ['size' => 11, 'color' => empty($this->borangDataMap[$descKey]) ? 'FF0000' : '000000'],
            ['spaceAfter' => 300, 'alignment' => Jc::BOTH]
        );

        // ✅ TABEL DATA DARI DATABASE
        if ($elemen->datasetBorang && $elemen->datasetBorang->count() > 0) {
            foreach ($elemen->datasetBorang as $dataset) {
                if ($dataset->tipe_field === 'table') {

                    $cell->addText(
                        $dataset->nama,
                        ['size' => 11, 'bold' => true],
                        ['spaceAfter' => 100]
                    );

                    // ✅ AMBIL DATA TABEL DARI DATABASE
                    $tableData = $this->borangDataMap[$dataset->kode] ?? null;

                    if ($tableData) {
                        // Insert HTML table (perlu dikonversi ke PhpWord table)
                        $this->addHtmlTableToCell($cell, $tableData);
                    } else {
                        // Template kosong
                        $this->addDatasetTable($cell, $dataset);
                    }

                    $cell->addTextBreak(1);
                }
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
            'width' => 100 * 50,
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

    private function ensureNumberingStyle(string $name, array $definition): void
    {
        if (isset($this->numberingRegistered[$name])) {
            return;
        }

        $this->phpWord->addNumberingStyle($name, $definition);
        $this->numberingRegistered[$name] = true;
    }

    /**
     * ✅ Kotak besar untuk deskripsi + TABEL
     */
    private function addDeskripsiBox($section, $elemen)
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 100,
            'width' => 100 * 50,
            'unit' => 'pct'
        ]);

        $table->addRow();
        $cell = $table->addCell(9500);

        // Header kotak deskripsi
        $cell->addText(
            'Deskripsi ' . strtolower($elemen->pernyataan_elemen),
            ['size' => 11, 'italic' => true],
            ['spaceAfter' => 200]
        );

        // ✅ PLACEHOLDER UNTUK ISI DESKRIPSI
        $cell->addText(
            '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            ['size' => 11, 'color' => 'FF0000', 'italic' => true],
            ['spaceAfter' => 300, 'alignment' => Jc::BOTH] // Justify
        );

        // ✅ TABEL DATA DARI DATASET BORANG (DI DALAM KOTAK)
        if ($elemen->datasetBorang && $elemen->datasetBorang->count() > 0) {
            foreach ($elemen->datasetBorang as $dataset) {
                if ($dataset->tipe_field === 'table') {

                    // Label tabel
                    $cell->addText(
                        $dataset->nama,
                        ['size' => 11, 'bold' => true],
                        ['spaceAfter' => 100]
                    );

                    // ✅ TABEL SESUAI EXPECTED COLUMNS
                    $this->addDatasetTable($cell, $dataset);

                    $cell->addTextBreak(1);
                }
            }
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

    private function addSuplemenSection()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);

        // Multilevel: I. / A. / 1. / a.
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

        // I. (bold)
        $section->addListItem(
            'Suplemen Program Studi Diploma Satu, Diploma Dua, Diploma Tiga',
            0,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 120]
        );

        // A. (bold)  ✅ harus bold
        $section->addListItem(
            'Deskripsi Capaian Pembelajaran Lulusan, Susunan, Materi Pembelajaran, Beban Belajar, Rencana Pembelajaran',
            1,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 80]
        );

        // 1., 2., a., b., dst (contoh ringkas sesuai screenshot kamu)
        $section->addListItem('Capaian Pembelajaran Lulusan (CPL)', 2, ['size' => 11], $numberingName);
        $section->addListItem('Susunan Materi Pembelajaran Untuk Mencapai CPL', 2, ['size' => 11], $numberingName);

        $section->addListItem('Matakuliah', 3, ['size' => 11], $numberingName);
        $section->addListItem('Modul', 3, ['size' => 11], $numberingName);
        $section->addListItem('Blok tematik; dan/atau', 3, ['size' => 11], $numberingName);
        $section->addListItem('Bentuk lain', 3, ['size' => 11], $numberingName);

        $section->addListItem('Beban Belajar dan Masa Tempuh', 2, ['size' => 11], $numberingName);
        $section->addListItem('Rencana Pembelajaran', 2, ['size' => 11], $numberingName);

        $section->addTextBreak(1);

        // B. (bold) ✅ harus bold
        $section->addListItem(
            'Pemastian Capaian Pembelajaran Lulusan',
            1,
            ['size' => 11, 'bold' => true],
            $numberingName,
            ['spaceAfter' => 80]
        );

        // B.1 dst -> di screenshot kamu jadi 1.,2.,3. (level decimal)
        $section->addListItem('Pemenuhan Beban Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);

        // paragraf isi (bukan list) -> indent agar sejajar dengan teks setelah "1."
        $section->addText(
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11],
            ['alignment' => Jc::BOTH, 'indentation' => ['left' => 1080], 'spaceAfter' => 120]
        );

        $section->addListItem('Magang', 2, ['size' => 11], $numberingName, ['spaceAfter' => 40]);
        $section->addText(
            'Kegiatan magang di dunia usaha, dunia industri, dan dunia kerja yang relevan pada program Diploma Satu, Diploma Dua, dan Diploma Tiga (Durasi dan Beban belajar)',
            ['size' => 11],
            ['alignment' => Jc::BOTH, 'indentation' => ['left' => 1080], 'spaceAfter' => 120]
        );

        $section->addListItem('Penilaian Hasil Belajar', 2, ['size' => 11], $numberingName, ['spaceAfter' => 60]);

        // a. b. c. d. (level 3)
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

    private function addFormLine($section, $label, $hasRedText = false, $alignment = Jc::START)
    {
        $textRun = $section->addTextRun(['alignment' => $alignment, 'spaceAfter' => 200]);
        $textRun->addText($label . str_repeat(' ', 5) . ': ................', ['size' => 11]);
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
}
