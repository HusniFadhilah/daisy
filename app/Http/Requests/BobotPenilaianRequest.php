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
            'id_elemen' => 'required|exists:elemen_standar,id',
            'id_category' => 'required|exists:study_program_categories,id',
            'id_degree_level' => 'required|exists:degree_levels,id',
            'bobot' => 'required|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ];

        // Untuk update, tambahkan exception untuk unique constraint
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['id_elemen'] = [
                'required',
                'exists:elemen_standar,id',
                'unique:bobot_penilaian,id_elemen,' . $this->route('bobot_penilaian') . ',id,id_category,' . $this->input('id_category') . ',id_degree_level,' . $this->input('id_degree_level')
            ];
        } else {
            $rules['id_elemen'] = [
                'required',
                'exists:elemen_standar,id',
                'unique:bobot_penilaian,id_elemen,NULL,id,id_category,' . $this->input('id_category') . ',id_degree_level,' . $this->input('id_degree_level')
            ];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
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
            'id_degree_level.required' => 'Jenjang program studi harus dipilih',
            'id_degree_level.exists' => 'Jenjang program studi tidak valid',
            'bobot.required' => 'Bobot harus diisi',
            'bobot.integer' => 'Bobot harus berupa angka',
            'bobot.min' => 'Bobot minimal 0',
            'bobot.max' => 'Bobot maksimal 100',
        ];
    }
}
