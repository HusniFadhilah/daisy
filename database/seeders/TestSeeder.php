<?php

namespace Database\Seeders;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a new PHPWord object and section
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add a table
        $table = $section->addTable();

        // Add header row
        $table->addRow();
        $table->addCell(1500)->addText('Item');
        $table->addCell(1500)->addText('Price');

        // Add data rows
        $table->addRow();
        $table->addCell(1500)->addText('Product A');
        $table->addCell(1500)->addText('10'); // Numeric value

        $table->addRow();
        $table->addCell(1500)->addText('Product B');
        $table->addCell(1500)->addText('20'); // Numeric value

        // Add the total row
        $table->addRow();
        $table->addCell(1500)->addText('Total:');
        $totalCell = $table->addCell(1500);

        // Add the formula using a field code
        // The field code for a sum above the current cell is {=SUM(ABOVE)}
        // The *MERGEFORMAT switch ensures proper formatting
        $totalCell->addText('{=SUM(ABOVE)*MERGEFORMAT}');

        // Save the document
        $filename = 'table_with_sum_formula.docx';
        $filePath = storage_path("app/public/templates/{$filename}.docx");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filePath);

        echo "Document '{$filename}' created successfully. Open it in Microsoft Word and press F9 to update the field.";
    }
}
