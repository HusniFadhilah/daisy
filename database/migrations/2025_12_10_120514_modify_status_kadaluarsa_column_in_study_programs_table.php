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
            // Change status_kadaluarsa from enum to string to accommodate various status texts
            $table->string('status_kadaluarsa')->default(null)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            // Revert back to enum
            $table->enum('status_kadaluarsa', ['Aktif', 'Kadaluarsa', 'Belum Terakreditasi'])
                ->default(null)->nullable()->change();
        });
    }
};
