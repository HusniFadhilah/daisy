<?php
// database/migrations/xxxx_create_borang_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ========================================
        // 1. DATASET BORANG (Master Data)
        // ========================================
        Schema::create('dataset_borang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_elemen')
                ->constrained('elemen_standar')
                ->onDelete('cascade');

            $table->string('kode', 50)->unique(); // E.1.1, E.1.2, D.1.1
            $table->string('nama', 255);
            $table->text('deskripsi')->nullable();

            // ✅ Extended field types for online form
            $table->enum('tipe_field', [
                'text',
                'textarea',
                'number',
                'date',
                'email',
                'url',
                'select',
                'file',
                'table',
                'narasi'
            ])->default('text');

            // ✅ For form inputs
            $table->string('label_field')->nullable();
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable(); // For select fields
            $table->text('keterangan')->nullable(); // Help text

            // ✅ For table fields
            $table->json('expected_columns')->nullable(); // Expected table structure

            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['id_elemen', 'kode']);
        });

        // ========================================
        // 2. BORANG IMPORTS (Import Records)
        // ========================================
        Schema::create('borang_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_dokumen')
                ->constrained('pengajuan_dokumen')
                ->onDelete('cascade');

            $table->string('original_filename');

            // ✅ FIXED: Complete status enum
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'manual_entry'
            ])->default('pending');

            // Statistics
            $table->integer('total_sections')->default(0);
            $table->integer('total_tables')->default(0);
            $table->integer('parsed_sections')->default(0);
            $table->integer('parsed_tables')->default(0);

            // Notes & Errors
            $table->text('parsing_notes')->nullable();
            $table->json('parsing_errors')->nullable();

            // Metadata
            $table->foreignId('imported_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index(['id_pengajuan', 'status']);
            $table->index(['id_dokumen', 'status']);
        });

        // ========================================
        // 3. BORANG SECTIONS (Parsed Sections)
        // ========================================
        Schema::create('borang_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_import')
                ->constrained('borang_imports')
                ->onDelete('cascade');
            $table->foreignId('id_elemen')
                ->nullable()
                ->constrained('elemen_standar')
                ->onDelete('set null');

            $table->string('kode_section', 50); // D.1, E.1, etc.
            $table->string('judul_section');
            $table->text('konten_narasi')->nullable(); // Text before "Mohon isi di sini"
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['id_import', 'kode_section']);
            $table->index(['id_elemen']);
        });

        // ========================================
        // 4. BORANG TABLES (Parsed Tables)
        // ========================================
        Schema::create('borang_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_section')
                ->constrained('borang_sections')
                ->onDelete('cascade');
            $table->foreignId('id_dataset')
                ->nullable()
                ->constrained('dataset_borang')
                ->onDelete('set null');

            $table->string('kode_tabel', 50); // E.1.1, E.1.2, etc.
            $table->string('judul_tabel');
            $table->integer('row_count')->default(0);
            $table->integer('col_count')->default(0);
            $table->json('headers')->nullable(); // Table headers
            $table->json('data')->nullable(); // Full table data as JSON
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['id_section', 'kode_tabel']);
            $table->index(['id_dataset']);
        });

        // ========================================
        // 5. BORANG DATA (Key-Value Storage)
        // ========================================
        Schema::create('borang_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_borang_import')
                ->constrained('borang_imports')
                ->onDelete('cascade');

            // ✅ Flexible string key (not FK)
            // Supports: 'desc_123', 'table_456', 'field_E.1.1', etc.
            $table->string('dataset_id', 255);

            // Store value as text (can be JSON for complex data)
            $table->text('nilai')->nullable();

            // ✅ Optional: Link to dataset_borang if needed
            $table->foreignId('id_dataset_borang')
                ->nullable()
                ->constrained('dataset_borang')
                ->onDelete('set null');

            $table->timestamps();

            // ✅ Unique: one value per dataset per import
            $table->unique(['id_borang_import', 'dataset_id'], 'borang_data_unique');
            $table->index(['id_borang_import']);
            $table->index(['dataset_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('borang_data');
        Schema::dropIfExists('borang_tables');
        Schema::dropIfExists('borang_sections');
        Schema::dropIfExists('borang_imports');
        Schema::dropIfExists('dataset_borang');
    }
};
