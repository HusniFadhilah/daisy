<?php

namespace App\Exports;

use App\Models\StudyProgram;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReminderStudyProgramsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $programs) {}

    public function collection(): Collection
    {
        return $this->programs;
    }

    public function headings(): array
    {
        return [
            'Universitas',
            'Program Studi',
            'Jenjang',
            'Status Akreditasi',
            'Nomor SK',
            'Tanggal Kedaluwarsa',
        ];
    }

    public function map($program): array
    {
        /** @var StudyProgram $program */
        return [
            $program->university->name ?? '-',
            $program->name ?? '-',
            $program->degreeLevel?->alias ?? $program->degreeLevel?->name ?? '-',
            $program->peringkat_akreditasi ?? '-',
            $program->no_sk ?: '-',
            $program->tanggal_kedaluwarsa ? $program->tanggal_kedaluwarsa->format('Y-m-d') : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
