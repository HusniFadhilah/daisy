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

            // Konten LHA
            $table->text('pendahuluan')->nullable();
            $table->text('proses_al')->nullable();
            $table->text('hasil_al')->nullable();
            $table->text('rekomendasi_ps')->nullable();
            $table->text('rekomendasi_lamdepilar')->nullable();

            // Status
            $table->enum('status', ['draft', 'submitted', 'finalized'])->default('draft');

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            // Index
            $table->index(['id_asesmen', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lha_asesors');
    }
};
