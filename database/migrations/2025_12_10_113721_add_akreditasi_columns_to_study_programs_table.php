<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->string('peringkat_akreditasi')->nullable()->after('email');
            $table->date('tanggal_kadaluarsa')->nullable()->after('peringkat_akreditasi');
            $table->enum('status_kadaluarsa', ['Aktif', 'Kadaluarsa', 'Belum Terakreditasi'])->default(null)->nullable()->after('tanggal_kadaluarsa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->dropColumn(['peringkat_akreditasi', 'tanggal_kadaluarsa', 'status_kadaluarsa']);
        });
    }
};
