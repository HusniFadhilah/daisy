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
        // Create universities table
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        // Create degree_levels table
        Schema::create('degree_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('study_program_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create study_programs table
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('full_name');
            $table->string('code');
            $table->foreignId('id_univ')->constrained('universities')->onDelete('cascade');
            $table->foreignId('id_level')->constrained('degree_levels')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('study_program_categories')->onDelete('set null');
            $table->enum('bentuk_pt', ['Universitas', 'Institut', 'Sekolah Tinggi', 'Politeknik', 'Akademi'])->nullable();
            $table->string('email')->nullable();
            $table->string('peringkat_akreditasi')->nullable();
            $table->date('tanggal_kedaluwarsa')->nullable();
            $table->enum('status_kedaluwarsa', ['Aktif', 'Kedaluwarsa', 'Belum Terakreditasi'])->default(null)->nullable();
            $table->timestamps();

            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('study_program_categories');
        Schema::dropIfExists('degree_levels');
        Schema::dropIfExists('universities');
    }
};
