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
        Schema::create('indikator_penilaian_elemen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemen_standar_id')->nullable()->constrained('elemen_standar', 'id_elemen')->onDelete('cascade');
            $table->foreignId('jenjang_penilaian_id')->nullable()->constrained('jenjang_penilaian')->onDelete('cascade');
            $table->text('deskripsi_penilaian')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator_penilaian_elemen');
    }
};
