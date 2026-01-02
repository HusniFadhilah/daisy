<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenjangPenilaian extends Model
{
    protected $table = 'jenjang_penilaian';

    protected $fillable = [
        'name',
        'color',
        'skor',
    ];

    public function indikatorPenilaianElemen()
    {
        return $this->hasMany(IndikatorPenilaianElemen::class, 'id_jenjang_penilaian');
    }

    public static function getColorBySkor($skor)
    {
        return self::where('skor', $skor)->value('color') ?? 'cccccc';
    }

    /**
     * Get skor label
     */
    public static function getSkorLabelAttribute($skor, $isFull = False)
    {
        $labels = [
            0 => ($isFull ? '0 - ' : '') . 'Tidak Memenuhi (Not Met)',
            1 => ($isFull ? '1 - ' : '') . 'Belum Memenuhi (Not Met)',
            2 => ($isFull ? '2 - ' : '') . 'Lemah (Weakness/Cause of Concern)',
            3 => ($isFull ? '3 - ' : '') . 'Memenuhi (Met)',
            4 => ($isFull ? '4 - ' : '') . 'Pelampauan Standar',
        ];

        return $labels[$skor] ?? 'N/A';
    }

    /**
     * Get skor class for styling
     */
    public static function getSkorClassAttribute($skor)
    {
        $classes = [
            0 => 'danger',
            1 => 'danger',
            2 => 'warning',
            3 => 'success',
            4 => 'success',
        ];

        return $classes[$skor] ?? 'secondary';
    }

    /**
     * Get skor information (label, color, class)
     */
    public static function getSkorInfo($skor, $isFull = False)
    {
        $skorMapping = [
            0 => [
                'label' => ($isFull ? '0 - ' : '') . 'Tidak Memenuhi (Not Met)',
                'color' => '#f44336',
                'class' => 'danger',
            ],
            1 => [
                'label' => ($isFull ? '1 - ' : '') . 'Belum Memenuhi (Not Met)',
                'color' => '#ff9800',
                'class' => 'warning',
            ],
            2 => [
                'label' => ($isFull ? '2 - ' : '') . 'Lemah (Weakness/Cause of Concern)',
                'color' => '#ffeb3b',
                'class' => 'warning',
            ],
            3 => [
                'label' => ($isFull ? '3 - ' : '') . 'Memenuhi (Met)',
                'color' => '#8bc34a',
                'class' => 'success',
            ],
            4 => [
                'label' => ($isFull ? '4 - ' : '') . 'Pelampauan Standar (Exceeding Standard)',
                'color' => '#4caf50',
                'class' => 'success',
            ],
        ];

        return $skorMapping[$skor] ?? [
            'label' => 'Unknown',
            'color' => '#9e9e9e',
            'class' => 'secondary',
        ];
    }

    public static function getSkorColor($skor)
    {
        $colors = [
            0 => '#f44336', // Red - Not Met
            1 => '#ff9800', // Orange - Not Met
            2 => '#ffeb3b', // Yellow - Weakness
            3 => '#8bc34a', // Light Green - Met
            4 => '#4caf50', // Dark Green - Exceeding
        ];

        return $colors[$skor] ?? 'e0e0e0';
    }

    public static function textColorByBg($hex)
    {
        $hex = ltrim($hex, '#');
        return (hexdec(substr($hex, 0, 2)) * 0.299 +
            hexdec(substr($hex, 2, 2)) * 0.587 +
            hexdec(substr($hex, 4, 2)) * 0.114) > 186
            ? '#000'
            : '#fff';
    }
}
