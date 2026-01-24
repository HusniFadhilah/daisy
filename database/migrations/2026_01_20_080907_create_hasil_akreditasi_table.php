<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_akreditasi', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_asesmen')
                ->constrained('asesmens')
                ->onDelete('cascade');
            $table->foreignId('id_study_program')
                ->constrained('study_programs')
                ->onDelete('cascade');
            $table->foreignId('id_category')
                ->constrained('study_program_categories')
                ->onDelete('cascade');

            // Hasil AK (Asesmen Kecukupan)
            $table->decimal('skor_ak', 8, 2)->nullable()->comment('Total skor AK');
            $table->decimal('skor_ak_tertimbang', 8, 2)->nullable()->comment('Skor AK setelah bobot');
            $table->integer('total_bobot_ak')->nullable();
            $table->json('detail_skor_ak')->nullable()->comment('Detail per kriteria');
            $table->json('pelampauan_standar_ak')->nullable()->comment('Track elemen dengan skor 4 per kriteria - AK');
            $table->timestamp('tanggal_finalisasi_ak')->nullable();
            $table->foreignId('finalized_ak_by')->nullable()->constrained('users');

            // Hasil AL (Asesmen Lapangan)
            $table->decimal('skor_al', 8, 2)->nullable()->comment('Total skor AL');
            $table->decimal('skor_al_tertimbang', 8, 2)->nullable()->comment('Skor AL setelah bobot');
            $table->integer('total_bobot_al')->nullable();
            $table->json('detail_skor_al')->nullable()->comment('Detail per kriteria');
            $table->json('pelampauan_standar_al')->nullable()->comment('Track elemen dengan skor 4 per kriteria - AL');
            $table->timestamp('tanggal_finalisasi_al')->nullable();
            $table->foreignId('finalized_al_by')->nullable()->constrained('users');

            // Hasil Final (kombinasi AK + AL atau AL saja)
            $table->decimal('skor_final', 8, 2)->nullable()->comment('Skor akhir (0-400)');
            $table->enum('peringkat_akreditasi', [
                'Tidak Terakreditasi',
                'Terakreditasi Sementara (2 Tahun)',
                'Terakreditasi (5 Tahun)',
                'Terakreditasi Unggul 2 Tahun (dengan Syarat)',
                'Terakreditasi Unggul (5 Tahun)'
            ])->nullable();

            $table->boolean('memenuhi_syarat_unggul')->default(false)->comment('Apakah memenuhi syarat pelampauan standar untuk Unggul');
            $table->text('catatan_validasi')->nullable()->comment('Catatan hasil validasi syarat Unggul');

            // Metadata
            $table->enum('status', [
                'draft',       // AK & AL in progress
                'draft_ak',       // AK calculation in progress
                'final_ak',       // AK finalized, waiting AL
                'draft_al',       // AL calculation in progress
                'final_al',       // AL finalized
                'final_combined', // Combined score calculated
                'published'       // Result published to prodi
            ])->default('draft');

            $table->text('catatan_perhitungan')->nullable();
            $table->json('metadata')->nullable()->comment('Additional calculation metadata');

            $table->timestamps();

            // Indexes
            $table->index(['id_pengajuan', 'status']);
            $table->index('id_asesmen');
            $table->index('peringkat_akreditasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_akreditasi');
    }
};
