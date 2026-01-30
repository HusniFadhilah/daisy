<?php
// app/Http/Requests/DE/KirimSuratPenerimaanRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KirimSuratPenerimaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['asesi', 'super_admin']);
    }

    public function rules(): array
    {
        return [
            'file_surat_penerimaan' => [
                'required',
                'file',
                'mimes:pdf',
                'max:5120', // 5MB
            ],
            'keterangan' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'file_surat_penerimaan.required' => 'File surat penerimaan wajib diupload.',
            'file_surat_penerimaan.mimes' => 'File harus berformat PDF.',
            'file_surat_penerimaan.max' => 'Ukuran file maksimal 5MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'file_surat_penerimaan' => 'file penerimaan permohonan akreditasi',
        ];
    }
}
