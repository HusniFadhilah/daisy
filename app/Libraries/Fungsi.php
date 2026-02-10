<?php

namespace App\Libraries;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Format response.
 */
class Fungsi
{
    public static function compressImage($image, $path, $intensity = 70, $maxWidth = null, $maxHeight = null, $customName = null)
    {
        $fileExtension   = strtolower($image->getClientOriginalExtension());
        $fileName       = $customName ?? (sha1(uniqid() . $image . uniqid()) . '.' . $fileExtension);
        $destinationPath = 'assets/img/' . $path;
        $img = Image::make($image->getRealPath());

        // Cek ukuran gambar
        $width = $img->width();
        $height = $img->height();
        $maxWidth = $maxWidth ?? $width;
        $maxHeight = $maxHeight ?? $height;
        // Periksa jika ukuran gambar melebihi batas maksimal
        if ($width && $height && ($width > $maxWidth || $height > $maxHeight)) {
            // Tentukan skala penyesuaian berdasarkan sisi terpanjang
            $scale = $width > $height ? $maxWidth / $width : $maxHeight / $height;
            // Resize gambar
            $img->resize($width * $scale, $height * $scale);
        }

        $img->save(storage_path() . '/app/public/' . $destinationPath . $fileName, $intensity);
        return [$image->getClientOriginalName(), $destinationPath . $fileName];
    }

    public static function sweetalert($text, $icon, $title, $href = null)
    {
        if ($href != null) {
            session()->setFlashdata('href', $href);
        }
        session()->flash('text', $text);
        session()->flash('icon', $icon);
        session()->flash('title', $title);
    }

    public static function convertToAlphabet($number)
    {
        $alphabet = range('A', 'Z'); // Array abjad dari A hingga Z

        $result = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26; // Menghitung sisa pembagian dengan 26
            $result = $alphabet[$remainder] . $result; // Menambahkan abjad ke hasil
            $number = floor(($number - 1) / 26); // Membagi angka dengan 26
        }

        return $result;
    }

    public static function getRoleSession()
    {
        return Str::slug(Auth::user()->role ?? '');
    }

    public static function uniqueCode($limit)
    {
        return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
    }

    public static function randomColor()
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    public static function sliceStringByWord($string)
    {
        $url = env('APP_URL', 'http://localhost');
        $path = substr($url, -1) == "/" ? 'storage/' : '/storage/';
        return Str::after($string,  $url . $path);
    }

    public static function sliceStringByParams($string, $url)
    {
        return Str::after($string, $url);
    }

    public static function currency($value, $lang = 'id', $isSpace = true)
    {
        if ($isSpace)
            return ($lang == 'id' ? "Rp. " : 'IDR ') . number_format($value, 0, ',', '.');
        else
            return ($lang == 'id' ? "Rp." : 'IDR') . number_format($value, 0, ',', '.');
    }

    // public static function floorDecimal($number, $digits = 2)
    // {
    //     $formatter = new \NumberFormatter('id_ID', \NumberFormatter::DECIMAL);
    //     // $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $digits);
    //     return $number !== null ? $formatter->format(number_format((float)$number, $digits, '.', '')) : '-';
    //     // return $number !== null ? number_format((float)$number, $digits, ',', '') : '-';
    // }

    public static function floorDecimal($number, $decimalDigits = 4)
    {
        // Cek apakah ada koma dalam angka
        if (strpos($number, ',') !== false) {
            // Ganti koma dengan titik, karena kita ingin memisahkan bagian bulat dan desimal menggunakan titik
            $number = str_replace(',', '.', $number);
        }

        // Pisahkan bagian desimal dan bulat
        $parts = explode('.', $number);

        if (count($parts) > 1) {
            // Konversi bagian bulat dan desimal ke tipe data numerik
            $integerPart = (int) $parts[0];
            $decimalPart = (float) ('0.' . $parts[1]);

            // Format bagian bulat dengan number_format
            $formatted_integer = number_format($integerPart, 0, ',', '.');

            // Ambil $decimalDigits digit dari bagian desimal
            $formatted_decimal = sprintf('%.' . $decimalDigits . 'f', $decimalPart);
            $formatted_decimal = substr($formatted_decimal, 2); // hilangkan '0.'

            return $formatted_integer . ',' . $formatted_decimal;
        } elseif (count($parts) == 1) {
            // Jika hanya bagian bulat tanpa desimal
            $formatted_integer = number_format((int) $parts[0], 0, ',', '.');
            return $formatted_integer;
        } else {
            // Input tidak valid, kembalikan pesan error atau lakukan penanganan yang sesuai
            return '-';
        }
    }
    public static function fetchUrlWithCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Untuk mengikuti redirect jika ada
        $data = curl_exec($ch);
        curl_close($ch);

        if ($data === false) {
            throw new \Exception('Error fetching URL');
        }

        return $data;
    }

    public static function getBase64ImageSrc($url)
    {
        $imageData = self::fetchUrlWithCurl($url);
        $base64Image = base64_encode($imageData);
        return 'data:image/png;base64,' . $base64Image;
    }

    public static function terbilang($angka)
    {
        $angka = abs($angka);
        $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return self::terbilang($angka - 10) . ' belas';
        } elseif ($angka < 100) {
            return self::terbilang($angka / 10) . ' puluh ' . self::terbilang($angka % 10);
        } elseif ($angka < 200) {
            return 'seratus ' . self::terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return self::terbilang($angka / 100) . ' ratus ' . self::terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return 'seribu ' . self::terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return self::terbilang($angka / 1000) . ' ribu ' . self::terbilang($angka % 1000);
        }

        return $angka;
    }
}
