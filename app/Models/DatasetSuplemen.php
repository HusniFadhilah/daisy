<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetSuplemen extends Model
{
    use HasFactory;

    protected $table = 'dataset_suplemen';

    protected $fillable = [
        'degree_level_code',
        'section_key',
        'content_type',
        'numbering_level',
        'text_content',
        'formatting',
        'urutan',
        'parent_id',
    ];

    protected $casts = [
        'formatting' => 'array',
    ];

    /**
     * Get suplemen items by degree level code
     */
    public static function getByDegreeLevel(string $degreeLevelCode): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('degree_level_code', $degreeLevelCode)
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Get suplemen items grouped by section
     */
    public static function getGroupedBySection(string $degreeLevelCode): \Illuminate\Support\Collection
    {
        return self::where('degree_level_code', $degreeLevelCode)
            ->orderBy('urutan')
            ->get()
            ->groupBy('section_key');
    }

    /**
     * Parent relationship
     */
    public function parent()
    {
        return $this->belongsTo(DatasetSuplemen::class, 'parent_id');
    }

    /**
     * Children relationship
     */
    public function children()
    {
        return $this->hasMany(DatasetSuplemen::class, 'parent_id')->orderBy('urutan');
    }
}
