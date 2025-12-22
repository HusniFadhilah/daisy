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
            $table->enum('bentuk_pt', ['Universitas', 'Institut', 'Sekolah Tinggi', 'Politeknik', 'Akademi'])
                ->nullable()
                ->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->dropColumn('bentuk_pt');
        });
    }
};
