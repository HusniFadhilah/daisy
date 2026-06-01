<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\PengajuanPembayaran;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            PengajuanPembayaran::SETTING_BIAYA_AKREDITASI => PengajuanPembayaran::BIAYA_AKREDITASI,
            PengajuanPembayaran::SETTING_BIAYA_BANDING => PengajuanPembayaran::BIAYA_BANDING,
        ];

        foreach ($settings as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }
    }
}
