<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BobotPenilaianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_elemen' => $this->id_elemen,
            'id_category' => $this->id_category,
            'bobot' => $this->bobot,
            'elemen_standar' => [
                'id' => $this->elemenStandar->id_elemen,
                'kode' => $this->elemenStandar->kode_elemen,
                'nama' => $this->elemenStandar->pernyataan_elemen,
                'kriteria' => [
                    'id' => $this->elemenStandar->kriteria->id_kriteria ?? null,
                    'kode' => $this->elemenStandar->kriteria->kode_kriteria ?? null,
                    'nama' => $this->elemenStandar->kriteria->nama_kriteria ?? null,
                ],
            ],
            'category' => [
                'id' => $this->category->id,
                'code' => $this->category->code,
                'name' => $this->category->name,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
