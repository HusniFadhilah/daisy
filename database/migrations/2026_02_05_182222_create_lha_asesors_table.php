<?php
// database/migrations/xxxx_create_lha_asesors_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lha_asesors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens')->cascadeOnDelete();
            // $table->foreignId('id_user')->constrained('users')->nullOnDelete();

            // Konten LHA
            $table->text('pendahuluan')->nullable();
            $table->text('proses_al')->nullable();
            $table->text('hasil_al')->nullable();
            $table->text('rekomendasi_ps')->nullable();
            $table->text('rekomendasi_lamdepilar')->nullable();

            // Status
            $table->enum('status', ['draft', 'submitted', 'revision_required', 'finalized'])->default('draft');

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->unsignedBigInteger('pendahuluan_updated_by')->nullable();
            $table->timestamp('pendahuluan_updated_at')->nullable();

            $table->unsignedBigInteger('proses_al_updated_by')->nullable();
            $table->timestamp('proses_al_updated_at')->nullable();

            $table->unsignedBigInteger('hasil_al_updated_by')->nullable();
            $table->timestamp('hasil_al_updated_at')->nullable();

            $table->unsignedBigInteger('rekomendasi_ps_updated_by')->nullable();
            $table->timestamp('rekomendasi_ps_updated_at')->nullable();

            $table->unsignedBigInteger('rekomendasi_lamdepilar_updated_by')->nullable();
            $table->timestamp('rekomendasi_lamdepilar_updated_at')->nullable();

            // Foreign keys
            $table->foreign('pendahuluan_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('proses_al_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hasil_al_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rekomendasi_ps_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rekomendasi_lamdepilar_updated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Index
            $table->index(['id_asesmen', 'status']);
        });

        Schema::create('lha_asesor_banding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens')->cascadeOnDelete();
            // $table->foreignId('id_user')->constrained('users')->nullOnDelete();

            // Konten LHA
            $table->text('pendahuluan')->nullable();
            $table->text('proses_al')->nullable();
            $table->text('hasil_al')->nullable();
            $table->text('rekomendasi_ps')->nullable();
            $table->text('rekomendasi_lamdepilar')->nullable();

            // Status
            $table->enum('status', ['draft', 'submitted', 'revision_required', 'finalized'])->default('draft');

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->unsignedBigInteger('pendahuluan_updated_by')->nullable();
            $table->timestamp('pendahuluan_updated_at')->nullable();

            $table->unsignedBigInteger('proses_al_updated_by')->nullable();
            $table->timestamp('proses_al_updated_at')->nullable();

            $table->unsignedBigInteger('hasil_al_updated_by')->nullable();
            $table->timestamp('hasil_al_updated_at')->nullable();

            $table->unsignedBigInteger('rekomendasi_ps_updated_by')->nullable();
            $table->timestamp('rekomendasi_ps_updated_at')->nullable();

            $table->unsignedBigInteger('rekomendasi_lamdepilar_updated_by')->nullable();
            $table->timestamp('rekomendasi_lamdepilar_updated_at')->nullable();

            // Foreign keys
            $table->foreign('pendahuluan_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('proses_al_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hasil_al_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rekomendasi_ps_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rekomendasi_lamdepilar_updated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Index
            $table->index(['id_asesmen', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lha_asesor_banding');
        Schema::dropIfExists('lha_asesor');
    }
};
