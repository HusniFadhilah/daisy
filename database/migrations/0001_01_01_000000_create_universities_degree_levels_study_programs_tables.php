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
        if (!Schema::hasTable('universities')) {
            Schema::create('universities', function (Blueprint $table) {
                $table->id();
                $table->string('code');
                $table->string('name');
                $table->string('logo_path')->nullable();
                $table->string('email')->nullable();
                $table->string('lpm_name')->nullable()->comment('Lembaga Penjaminan Mutu');
                $table->string('lpm_phone')->nullable()->comment('Telp LPM');
                $table->string('lpm_mobile')->nullable()->comment('Mobile/WA LPM');
                $table->string('lpm_email')->nullable()->comment('Email LPM');
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('is_example')->default(false)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('study_program_categories')) {
            Schema::create('study_program_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Create degree_levels table
        if (!Schema::hasTable('degree_levels')) {
            Schema::create('degree_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_category')->nullable()->constrained('study_program_categories')->onDelete('set null');
                $table->string('code', 15);
                $table->string('alias', 15);
                $table->string('name', 50)->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // Create study_programs table
        if (!Schema::hasTable('study_programs')) {
            Schema::create('study_programs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('full_name');
                $table->string('code');
                $table->foreignId('id_university')->constrained('universities')->onDelete('cascade');
                $table->foreignId('id_degree_level')->constrained('degree_levels')->onDelete('cascade');
                $table->foreignId('id_category')->nullable()->constrained('study_program_categories')->onDelete('set null');
                $table->enum('bentuk_pt', ['Universitas', 'Institut', 'Sekolah Tinggi', 'Politeknik', 'Akademi'])->nullable();
                $table->string('email')->nullable();
                $table->string('peringkat_akreditasi')->nullable();
                $table->date('tanggal_kedaluwarsa')->nullable();
                $table->enum('status_kedaluwarsa', ['Aktif', 'Kedaluwarsa', 'Belum Terakreditasi'])->default(null)->nullable();
                $table->string('ketua_prodi_name')->nullable()->comment('Nama Ketua Program Studi');
                $table->string('ketua_prodi_nip')->nullable()->comment('NIP Ketua Program Studi');
                $table->string('ketua_tim_akreditasi')->nullable()->comment('Ketua Tim Akreditasi');
                $table->string('akreditasi_phone')->nullable()->comment('Telp Tim Akreditasi');
                $table->string('akreditasi_mobile')->nullable()->comment('Mobile/WA Tim Akreditasi');
                $table->string('akreditasi_email')->nullable()->comment('Email Tim Akreditasi');
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('is_example')->default(false)->index();
                $table->timestamps();

                $table->index('id_category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('degree_levels');
        Schema::dropIfExists('study_program_categories');
        Schema::dropIfExists('universities');
    }
};
