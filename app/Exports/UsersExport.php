<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with(['university', 'studyProgram'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Email',
            'No. Telepon',
            'Alamat',
            'Institusi',
            'ID Universitas',
            'Nama Universitas',
            'ID Program Studi',
            'Nama Program Studi',
            'Jabatan',
            'Role',
            'Role Aktif',
            'Semua Roles',
            'Multiple Role',
            'Tanggal Dibuat',
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '',
            $user->address ?? '',
            $user->institution ?? '',
            $user->id_university ?? '',
            $user->university ? $user->university->name : '',
            $user->id_study_program ?? '',
            $user->studyProgram ? $user->studyProgram->name : '',
            $user->position ?? '',
            $user->role,
            $user->role_selected,
            implode(', ', $user->roles ?? []),
            $user->is_multiple_role ? 'Ya' : 'Tidak',
            $user->created_at->locale('id')->translatedFormat('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
