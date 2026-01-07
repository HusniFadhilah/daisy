<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use App\Models\Kriteria;

class BorangExampleSeeder2 extends Seeder
{
    protected PhpWord $phpWord;

    protected array $numberingRegistered = [];

    public function run()
    {
        $this->phpWord = new PhpWord();

        // ✅ FONT Montserrat 11PT (SEPERTI TEMPLATE)
        $this->phpWord->setDefaultFontName('Montserrat');
        $this->phpWord->setDefaultFontSize(11);

        $this->addCoverPage();
        $this->addLembarPengesahan();
        // $this->addKataPengantarSection();
        // $this->addRingkasanSection();
        $this->addContentPages();
        // $this->addSuplemenSection();

        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $filePath = storage_path('app/public/templates/TEMPLATE_BORANG_EVALUASI_DIRI.docx');

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $objWriter->save($filePath);

        $this->command->info('✅ Template DOCX berhasil dibuat: ' . $filePath);
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
        $textRun->addText('Tahun', ['size' => 12, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);
    }

    private function addLembarPengesahan()
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1500,
            'marginRight' => 1500,
        ]);

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

        // Judul
        $section->addText(
            'KATA PENGANTAR',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
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

        // Judul
        $section->addText(
            'RINGKASAN',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 500]
        );
    }

    private function addContentPages()
    {
        // ✅ AMBIL DATA DARI DATABASE
        $kriterias = Kriteria::with([
            'elemenStandar.pernyataan',
            'elemenStandar.indikator',
            'elemenStandar.datasetBorang'
        ])->get();

        foreach ($kriterias as $kriteria) {
            $section = $this->phpWord->addSection([
                'marginTop' => 1000,
                'marginBottom' => 1000,
                'marginLeft' => 1500,
                'marginRight' => 1500,
            ]);

            // ✅ KRITERIA HEADER (D. Diferensiasi Misi)
            $section->addText(
                $kriteria->kode_kriteria . '. ' . $kriteria->nama_kriteria,
                ['size' => 11, 'bold' => true],
                ['spaceAfter' => 100]
            );

            // Italic subtitle
            $section->addText(
                '(' . $this->getKriteriaSubtitle($kriteria->kode_kriteria) . ')',
                ['size' => 11, 'italic' => true],
                ['spaceAfter' => 200]
            );
            $countElemen = $kriteria->elemenStandar->count();
            foreach ($kriteria->elemenStandar as $key => $elemen) {

                // ✅ JUDUL ELEMEN (SESUAI CONTOH WORD)
                $section->addText(
                    $key + 1 . '. ' . $elemen->kode_elemen . '. ' . $elemen->pernyataan_elemen,
                    ['size' => 11, 'bold' => true],
                    ['spaceAfter' => 150]
                );

                // ✅ PERNYATAAN STANDAR (JUSTIFY)
                $this->addPernyataanStandar($section, $elemen);

                $section->addTextBreak(1);

                // ✅ URAIAN INDIKATOR PENILAIAN (NUMBERED LIST)
                $this->addUraianIndikator($section, $elemen);

                $section->addPageBreak();

                // ✅ KOTAK ELEMEN (1. D.1. Legalitas Program dan Tata Pamong)
                $this->addElemenBox($section, $kriteria, $elemen);

                $section->addTextBreak(0.5);

                // ✅ KOTAK DESKRIPSI (BESAR) - DI SINI TABEL AKAN MASUK
                $this->addDeskripsiBox($section, $elemen);

                if ($key + 1 != $countElemen) $section->addPageBreak();
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
     * ✅ Tambahkan tabel dataset di dalam kotak deskripsi
     */
    private function addDatasetTable($cell, $dataset)
    {
        $columns = $dataset->expected_columns ?? ['No', 'Keterangan', 'Data'];
        $columnCount = count($columns);

        // ✅ TABLE STYLE: AUTOFIT KE CONTAINER
        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 80,
            'width'       => 100,
            'unit'        => 'pct',
            'layout'      => \PhpOffice\PhpWord\Style\Table::LAYOUT_AUTO,
        ];

        $dataTable = $cell->addTable($tableStyle);

        // ✅ HEADER
        $dataTable->addRow(400);

        foreach ($columns as $col) {
            $headerCell = $dataTable->addCell(
                intval(100 / $columnCount),
                [
                    'bgColor'   => 'D3D3D3',
                    'valign'    => 'center',
                    'wordWrap'  => true
                ]
            );

            $headerCell->addText(
                $col,
                ['bold' => true, 'size' => 10],
                [
                    'alignment' => Jc::CENTER,
                    'wordWrap'  => true
                ]
            );
        }

        // ✅ ROW DATA (CONTOH 3 BARIS)
        for ($i = 1; $i <= 3; $i++) {
            $dataTable->addRow(350);

            foreach ($columns as $index => $col) {
                $rowCell = $dataTable->addCell(
                    intval(100 / $columnCount),
                    ['wordWrap' => true]
                );

                $rowCell->addText(
                    $index === 0 ? (string)$i : '',
                    ['size' => 10],
                    [
                        'alignment' => $index === 0 ? Jc::CENTER : Jc::START,
                        'wordWrap'  => true
                    ]
                );
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
