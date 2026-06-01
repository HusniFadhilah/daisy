<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_akreditasi', function (Blueprint $table) {
            if (!Schema::hasColumn('hasil_akreditasi', 'catatan_validasi_banding')) {
                $table->text('catatan_validasi_banding')
                    ->nullable()
                    ->after('catatan_validasi')
                    ->comment('Keterangan lengkap hasil cek syarat Unggul saat banding');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hasil_akreditasi', function (Blueprint $table) {
            if (Schema::hasColumn('hasil_akreditasi', 'catatan_validasi_banding')) {
                $table->dropColumn('catatan_validasi_banding');
            }
        });
    }
};
