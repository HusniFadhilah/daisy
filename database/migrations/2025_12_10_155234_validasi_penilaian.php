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
            $table->id();

            // Foreign Keys
            $table->foreignId('id_penilaian')->nullable()->constrained('penilaian_elemen', 'id')->onDelete('cascade');
            $table->foreignId('id_validator')->nullable()->constrained('users', 'id')->onDelete('cascade');

            // Validasi Data
            $table->enum('status_validasi', ['not_validated', 'validated', 'revision_required', 'approved'])
                ->default('not_validated');
            $table->integer('skor_final')->nullable()->comment('Skor final yang disetujui validator');
            $table->text('catatan_validator')->nullable();
            $table->timestamp('validated_at')->nullable();

            // Metadata
            $table->timestamps();

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
