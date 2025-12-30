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
            $table->foreignId('id_elemen')->constrained('elemen_standar')->onDelete('cascade');

            $table->string('kode', 50)->unique(); // E.1.a, E.1.b, D.1.a
            $table->string('nama', 255);
            $table->text('deskripsi')->nullable();

            // Field types
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

            // Form configuration
            $table->string('label_field')->nullable();
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->text('keterangan')->nullable();

            // Table configuration
            $table->json('expected_columns')->nullable();
            $table->longText('template_html')->nullable();
            $table->json('validation_rules')->nullable();

            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['id_elemen', 'kode']);
            $table->index(['tipe_field']);
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
                ->nullable()
                ->constrained('pengajuan_dokumen')
                ->onDelete('set null');

            $table->string('original_filename')->nullable();
            $table->string('stored_path')->nullable();

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
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['id_pengajuan', 'status']);
            $table->index(['status']);
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
            $table->longText('konten_narasi')->nullable();
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

            $table->string('kode_tabel', 50); // E.1.a, E.1.b, etc.
            $table->string('judul_tabel')->nullable();
            $table->integer('row_count')->default(0);
            $table->integer('col_count')->default(0);
            $table->json('headers')->nullable();
            $table->longText('data')->nullable(); // Store as JSON or HTML
            $table->longText('html_content')->nullable(); // Original HTML
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
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');

            $table->foreignId('id_borang_import')
                ->nullable()
                ->constrained('borang_imports')
                ->onDelete('set null');

            // Flexible key: 'desc_123', 'E.1.a', 'field_name', etc.
            $table->string('dataset_id', 255);

            // Store value
            $table->longText('nilai')->nullable();
            $table->boolean('is_template')->default(true);

            // Optional link to dataset_borang
            $table->foreignId('id_dataset_borang')
                ->nullable()
                ->constrained('dataset_borang')
                ->onDelete('set null');

            $table->timestamps();

            // One value per dataset per pengajuan
            $table->unique(['id_pengajuan', 'dataset_id'], 'borang_data_unique');
            $table->index(['id_pengajuan']);
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
