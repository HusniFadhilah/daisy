<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BobotPenilaianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'id_elemen' => 'required|exists:elemen_standar,id_elemen',
            'id_category' => 'required|exists:study_program_categories,id',
            'bobot' => 'required|integer|min:0|max:100',
        ];

        // Untuk update, tambahkan exception untuk unique constraint
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['id_elemen'] = [
                'required',
                'exists:elemen_standar,id_elemen',
                'unique:bobot_penilaian,id_elemen,' . $this->route('bobot_penilaian') . ',id,id_category,' . $this->input('id_category')
            ];
        } else {
            $rules['id_elemen'] = [
                'required',
                'exists:elemen_standar,id_elemen',
                'unique:bobot_penilaian,id_elemen,NULL,id,id_category,' . $this->input('id_category')
            ];
        }

        return $rules;
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'id_elemen.required' => 'Elemen standar harus dipilih',
            'id_elemen.exists' => 'Elemen standar tidak valid',
            'id_elemen.unique' => 'Bobot untuk kombinasi elemen dan kategori ini sudah ada',
            'id_category.required' => 'Kategori program studi harus dipilih',
            'id_category.exists' => 'Kategori program studi tidak valid',
            'bobot.required' => 'Bobot harus diisi',
            'bobot.integer' => 'Bobot harus berupa angka',
            'bobot.min' => 'Bobot minimal 0',
            'bobot.max' => 'Bobot maksimal 100',
        ];
    }
}

