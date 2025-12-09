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
        Schema::create('validasi_penilaian', function (Blueprint $table) {
            $table->id('id_validasi');

            // Foreign Keys
            $table->unsignedBigInteger('id_penilaian');
            $table->unsignedBigInteger('id_validator');

            // Validasi Data
            $table->enum('status_validasi', ['not_validated', 'validated', 'revision_needed'])
                ->default('not_validated');
            $table->integer('skor_final')->nullable()->comment('Skor final yang disetujui validator');
            $table->text('catatan_validator')->nullable();
            $table->timestamp('tanggal_validasi')->nullable();

            // Metadata
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('id_penilaian')
                ->references('id_penilaian')
                ->on('penilaian_elemen')
                ->onDelete('cascade');

            $table->foreign('id_validator')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('id_penilaian');
            $table->index('id_validator');
            $table->index('status_validasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validasi_penilaian');
    }
};
