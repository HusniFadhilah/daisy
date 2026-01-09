<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use App\Models\Kriteria;
use App\Models\ElemenStandar;
use App\Models\Pernyataan;
use App\Models\Indikator;

class BorangExampleSeeder extends Seeder
{
    private $phpWord;

    public function run()
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->setDefaultFontName('Arial');
        $this->phpWord->setDefaultFontSize(11);

        $this->addCoverPage();
        $this->addLembarPengesahan();
        $this->addContentPages();

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

        $section->addTextBreak(3);
        $section->addText('Logo', ['size' => 14, 'bold' => true], ['alignment' => Jc::CENTER]);
        $section->addText('Universitas', ['size' => 12], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(8);
        $section->addText('Evaluasi Diri', ['size' => 16, 'bold' => true], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);
        $section->addText('Nama Program Studi', ['size' => 14], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(10);
        $section->addText('Universitas ..........................................', ['size' => 12], ['alignment' => Jc::CENTER]);
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

        $section->addText(
            'Lembar Pengesahan Evaluasi Diri Program Studi',
            ['size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(2);

        // Form fields
        $this->addFormLine($section, 'Nama Institusi Penjaminan Mutu Universitas');
        $this->addFormLine($section, 'Telp');

        $textRun = $section->addTextRun(['spaceAfter' => 200]);
        $textRun->addText('Mobile ', ['size' => 11]);
        $textRun->addText('(hp dan atau WA)', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);
        $textRun->addText(str_repeat(' ', 10) . ':' . str_repeat(' ', 10));

        $this->addFormLine($section, 'Alamat Email');
        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['spaceAfter' => 200]);
        $textRun->addText('Nama PIC ', ['size' => 11]);
        $textRun->addText('(Akreditasi)', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);
        $textRun->addText(str_repeat(' ', 10) . ':' . str_repeat(' ', 10));

        $this->addFormLine($section, 'Telp');

        $textRun = $section->addTextRun(['spaceAfter' => 200]);
        $textRun->addText('Mobile ', ['size' => 11]);
        $textRun->addText('(hp dan atau WA)', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);
        $textRun->addText(str_repeat(' ', 10) . ':' . str_repeat(' ', 10));

        $this->addFormLine($section, 'Alamat Email');
        $section->addTextBreak(2);

        $textRun = $section->addTextRun(['alignment' => Jc::CENTER, 'spaceAfter' => 200]);
        $textRun->addText('Kota Kedudukan Universitas, ', ['size' => 11]);
        $textRun->addText('tanggal', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);
        $textRun->addText(', Bulan, ', ['size' => 11]);
        $textRun->addText('Tahun', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);

        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['alignment' => Jc::CENTER, 'spaceAfter' => 200]);
        $textRun->addText('Ttd dan ', ['size' => 11]);
        $textRun->addText('stamp', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);

        $section->addText(
            'Pejabat Institusi Penjaminan Mutu Universitas',
            ['size' => 11, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
        );

        $this->addFormLine($section, 'Nama', false, Jc::CENTER);
        $this->addFormLine($section, 'NIP', false, Jc::CENTER);
    }

    private function addContentPages()
    {
        $kriteria = Kriteria::with(['elemenStandar.pernyataan', 'elemenStandar.indikator'])->orderBy('kode_kriteria')->get();

        foreach ($kriteria as $krit) {
            $section = $this->phpWord->addSection([
                'marginTop' => 1000,
                'marginBottom' => 1000,
                'marginLeft' => 1500,
                'marginRight' => 1500,
            ]);

            // Header
            $section->addText('Deskripsi Evaluasi Diri', ['size' => 13, 'bold' => true], ['spaceAfter' => 200]);

            // Kriteria
            $section->addText(
                $krit->kode_kriteria . '. ' . $krit->nama_kriteria,
                ['size' => 13, 'bold' => true],
                ['spaceAfter' => 300]
            );

            foreach ($krit->elemenStandar as $elemen) {
                // Elemen in box
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
                $table->addRow();
                $cell = $table->addCell(9000);
                $cell->addText(
                    $elemen->kode_elemen . ' ' . $elemen->pernyataan_elemen,
                    ['size' => 11, 'bold' => true]
                );

                $section->addTextBreak(1);

                // Pernyataan Standar
                if ($elemen->pernyataans && $elemen->pernyataans->count() > 0) {
                    $section->addText('Pernyataan Standar:', ['size' => 11, 'bold' => true, 'italic' => true]);
                    foreach ($elemen->pernyataans as $pernyataan) {
                        $section->addText(
                            '• ' . $pernyataan->pernyataan,
                            ['size' => 10],
                            ['spaceAfter' => 100]
                        );
                    }
                    $section->addTextBreak(1);
                }

                // Indikator
                if ($elemen->indikator && $elemen->indikator->count() > 0) {
                    $section->addText('Indikator:', ['size' => 11, 'bold' => true, 'italic' => true]);
                    foreach ($elemen->indikator as $indikator) {
                        $section->addText(
                            '• ' . $indikator->kode_indikator . ': ' . $indikator->deskripsi_indikator,
                            ['size' => 10],
                            ['spaceAfter' => 100]
                        );
                    }
                    $section->addTextBreak(1);
                }

                // Mohon isi di sini
                $textRun = $section->addTextRun(['spaceAfter' => 200]);
                $textRun->addText('Mohon ', ['size' => 11]);
                $textRun->addText('isi', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);
                $textRun->addText(' di ', ['size' => 11]);
                $textRun->addText('sini', ['size' => 11, 'underline' => Font::UNDERLINE_SINGLE, 'color' => 'FF0000']);

                $section->addTextBreak(1);

                // Example table
                $section->addText(
                    $elemen->kode_elemen . ' Tabel Data',
                    ['size' => 11, 'bold' => true],
                    ['spaceAfter' => 200]
                );

                $this->addExampleTable($section);

                $section->addTextBreak(2);
            }
        }
    }

    private function addExampleTable($section)
    {
        $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $headerStyle = ['bgColor' => 'FFA500'];

        $table = $section->addTable($tableStyle);
        $table->addRow(400);
        $table->addCell(2000, $headerStyle)->addText('No', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
        $table->addCell(4000, $headerStyle)->addText('Keterangan', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
        $table->addCell(4000, $headerStyle)->addText('Data', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);

        for ($i = 1; $i <= 3; $i++) {
            $table->addRow();
            $table->addCell(2000)->addText($i, ['size' => 10], ['alignment' => Jc::CENTER]);
            $table->addCell(4000)->addText('Isi sesuai kebutuhan', ['size' => 10]);
            $table->addCell(4000)->addText('', ['size' => 10]);
        }
    }

    private function addFormLine($section, $label, $hasRedText = false, $alignment = Jc::LEFT)
    {
        $textRun = $section->addTextRun(['alignment' => $alignment, 'spaceAfter' => 200]);
        $textRun->addText($label . str_repeat(' ', 15) . ':' . str_repeat(' ', 15), ['size' => 11]);
    }
}
