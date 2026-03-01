<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenjangPenilaian extends Model
{
    protected $table = 'jenjang_penilaian';

    public const TIDAK_DAPAT_DINILAI = 'Tidak Dapat Dinilai';
    public const TIDAK_MEMENUHI = 'Tidak Memenuhi';
    public const LEMAH = 'Lemah';
    public const MEMENUHI = 'Memenuhi';
    public const MEMENUHI_STANDAR = 'Memenuhi Standar';
    public const MELAMPAUI_STANDAR = 'Melampaui Standar';
    public const PEMENUHAN_STANDAR = 'Pemenuhan Standar';
    public const PELAMPAUAN_STANDAR = 'Pelampauan Standar';
    public const LABEL_SKOR_0 = self::TIDAK_DAPAT_DINILAI;
    public const LABEL_SKOR_1 = self::TIDAK_MEMENUHI;
    public const LABEL_SKOR_2 = self::LEMAH;
    public const LABEL_SKOR_3 = self::MEMENUHI;
    public const LABEL_SKOR_4 = self::MEMENUHI_STANDAR;
    public const LABEL_SYARAT_UNGGUL_MEMENUHI = self::MEMENUHI_STANDAR;
    public const LABEL_SYARAT_UNGGUL_MELAMPAUI = self::MEMENUHI_STANDAR;
    public const COLOR_SKOR_0 = '#f5c6cb';
    public const COLOR_SKOR_1 = '#ffe0b2';
    public const COLOR_SKOR_2 = '#fff9c4';
    public const COLOR_SKOR_3 = '#dcedc8';
    public const COLOR_SKOR_4 = '#c8e6c9';

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

    public static function exportSkorMap($isFull = false)
    {
        $data = [];

        foreach (range(0, 4) as $skor) {
            $data[$skor] = self::getSkorInfo($skor, $isFull);
        }

        return $data;
    }

    /**
     * Get skor label
     */
    public static function getSkorLabelAttribute($skor, $isFull = False)
    {
        $labels = [
            0 => ($isFull ? '0 - ' : '') . self::LABEL_SKOR_0,
            1 => ($isFull ? '1 - ' : '') . self::LABEL_SKOR_1,
            2 => ($isFull ? '2 - ' : '') . self::LABEL_SKOR_2,
            3 => ($isFull ? '3 - ' : '') . self::LABEL_SKOR_3,
            4 => ($isFull ? '4 - ' : '') . self::LABEL_SKOR_4,
        ];

        return $labels[$skor] ?? 'N/A';
    }

    /**
     * Get skor information (label, color, class)
     */
    public static function getSkorInfo($skor, $isFull = False)
    {
        $skorMapping = [
            0 => [
                'label' => ($isFull ? '0 - ' : '') . self::LABEL_SKOR_0,
                'color' => self::COLOR_SKOR_0,
                'class' => 'danger',
            ],
            1 => [
                'label' => ($isFull ? '1 - ' : '') . self::LABEL_SKOR_1,
                'color' => self::COLOR_SKOR_1,
                'class' => 'warning',
            ],
            2 => [
                'label' => ($isFull ? '2 - ' : '') . self::LABEL_SKOR_2,
                'color' => self::COLOR_SKOR_2,
                'class' => 'warning',
            ],
            3 => [
                'label' => ($isFull ? '3 - ' : '') . self::LABEL_SKOR_3,
                'color' => self::COLOR_SKOR_3,
                'class' => 'success',
            ],
            4 => [
                'label' => ($isFull ? '4 - ' : '') . self::LABEL_SKOR_4,
                'color' => self::COLOR_SKOR_4,
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
            0 => self::COLOR_SKOR_0, // Red - Not Met
            1 => self::COLOR_SKOR_1, // Orange - Not Met
            2 => self::COLOR_SKOR_2, // Yellow - Weakness
            3 => self::COLOR_SKOR_3, // Light Green - Met
            4 => self::COLOR_SKOR_4, // Dark Green - Exceeding
        ];

        return $colors[$skor] ?? 'e0e0e0';
    }

    public static function textColorByBg($hex)
    {
        return '#000';
        // $hex = ltrim($hex, '#');
        // return (hexdec(substr($hex, 0, 2)) * 0.299 +
        //     hexdec(substr($hex, 2, 2)) * 0.587 +
        //     hexdec(substr($hex, 4, 2)) * 0.114) > 186
        //     ? '#000'
        //     : '#fff';
    }
}
