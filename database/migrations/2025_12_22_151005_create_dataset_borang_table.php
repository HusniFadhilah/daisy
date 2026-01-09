<?php
// database/migrations/xxxx_create_dataset_borang_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Dataset/Tabel Borang (E.1.1, E.1.2, E.2.1, etc)
        Schema::create('dataset_borang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_elemen')->constrained('elemen_standar')->onDelete('cascade');
            $table->string('kode', 30)->unique(); // E.1.1, E.1.2, D.1.1
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['tabel', 'narasi', 'data'])->default('tabel');
            $table->json('expected_columns')->nullable(); // Expected table structure
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Main borang import record
        Schema::create('borang_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')->constrained('pengajuan_akreditasi')->onDelete('cascade');
            $table->foreignId('id_dokumen')->constrained('pengajuan_dokumen')->onDelete('cascade');
            $table->string('original_filename');
            $table->enum('status', ['pending', 'parsing', 'success', 'failed'])->default('pending');
            $table->integer('total_sections')->default(0);
            $table->integer('total_tables')->default(0);
            $table->integer('parsed_sections')->default(0);
            $table->integer('parsed_tables')->default(0);
            $table->text('parsing_notes')->nullable();
            $table->json('parsing_errors')->nullable();
            $table->foreignId('imported_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        // Parsed sections (D.1, E.1, etc)
        Schema::create('borang_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_import')->constrained('borang_imports')->onDelete('cascade');
            $table->foreignId('id_elemen')->nullable()->constrained('elemen_standar')->onDelete('set null');
            $table->string('kode_section', 20); // D.1, E.1
            $table->string('judul_section');
            $table->text('konten_narasi')->nullable(); // Text before "Mohon isi di sini"
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['id_import', 'kode_section']);
        });

        // Parsed tables (E.1.1, E.1.2, etc)
        Schema::create('borang_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_section')->constrained('borang_sections')->onDelete('cascade');
            $table->foreignId('id_dataset')->nullable()->constrained('dataset_borang')->onDelete('set null');
            $table->string('kode_tabel', 30); // E.1.1, E.1.2
            $table->string('judul_tabel');
            $table->integer('row_count')->default(0);
            $table->integer('col_count')->default(0);
            $table->json('headers')->nullable(); // Table headers
            $table->json('data')->nullable(); // Full table data as JSON
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['id_section', 'kode_tabel']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('borang_tables');
        Schema::dropIfExists('borang_sections');
        Schema::dropIfExists('borang_imports');
        Schema::dropIfExists('dataset_borang');
    }
};
