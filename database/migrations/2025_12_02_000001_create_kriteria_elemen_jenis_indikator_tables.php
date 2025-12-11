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
        // Create kriteria table
        Schema::create('kriteria', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kriteria', 50);
            $table->string('nama_kriteria', 255);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Create elemen_standar table
        Schema::create('elemen_standar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kriteria')->constrained('kriteria', 'id')->onDelete('cascade');
            $table->string('kode_elemen', 50);
            $table->text('pernyataan_elemen');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Create jenis_indikator table
        Schema::create('jenis_indikator', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenis', 100);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Create indikator table
        Schema::create('indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_elemen')->constrained('elemen_standar', 'id')->onDelete('cascade');
            $table->foreignId('id_jenis')->constrained('jenis_indikator', 'id')->onDelete('cascade');
            $table->string('kode_indikator', 100);
            $table->text('deskripsi_indikator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator');
        Schema::dropIfExists('jenis_indikator');
        Schema::dropIfExists('elemen_standar');
        Schema::dropIfExists('kriteria');
    }
};
