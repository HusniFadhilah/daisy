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
            $table->foreignId('id_degree_level')->nullable()->constrained('degree_levels')->onDelete('cascade');

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
            $table->json('expected_rows')->nullable();
            $table->longText('template_html')->nullable();
            $table->json('validation_rules')->nullable();

            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_degree_variants')->default(false);
            $table->timestamps();

            $table->index(['id_elemen', 'kode']);
            $table->index(['id_elemen', 'id_degree_level']);
            $table->index(['tipe_field']);
        });

        Schema::create('dataset_borang_degree_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dataset_borang')->constrained('dataset_borang')->onDelete('cascade');
            $table->foreignId('id_degree_level')->constrained('degree_levels')->onDelete('cascade');

            // ✅ Template khusus untuk degree level ini
            $table->json('expected_columns')->nullable(); // Override columns
            $table->longText('template_html')->nullable(); // Override template
            $table->json('validation_rules')->nullable(); // Override validation
            $table->text('keterangan')->nullable(); // Catatan khusus

            $table->timestamps();

            // Unique: satu dataset hanya punya 1 variasi per degree level
            $table->unique(['id_dataset_borang', 'id_degree_level'], 'dataset_degree_unique');
        });

        // ========================================
        // 2. BORANG IMPORTS (Import Records)
        // ========================================
        Schema::create('borang_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_degree_level')->nullable()->constrained('degree_levels')->onDelete('cascade');
            $table->foreignId('id_dokumen')
                ->nullable()
                ->constrained('pengajuan_dokumen')
                ->onDelete('set null');

            $table->string('original_filename')->nullable();
            $table->string('stored_path')->nullable();

            $table->enum('status', [
                'pending',
                'parsing',
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
            $table->text('kata_pengantar')->nullable();
            $table->text('ringkasan')->nullable();
            $table->json('suplemen')->nullable();

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
        // 5. BORANG DATA (Key-Value Storage)
        // ========================================
        Schema::create('borang_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_degree_level')->nullable()->constrained('degree_levels')->onDelete('cascade');
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
            $table->index(['id_pengajuan', 'id_degree_level']);
            $table->index(['id_pengajuan']);
            $table->index(['id_borang_import']);
            $table->index(['dataset_id']);
        });

        Schema::create('borang_data_excel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_degree_level')
                ->nullable()
                ->constrained('degree_levels')
                ->onDelete('set null');
            $table->foreignId('id_borang_import')
                ->constrained('borang_imports')
                ->onDelete('cascade');

            $table->enum('source', ['excel_import', 'online_input'])
                ->default('excel_import');

            // Identifikasi tabel
            $table->string('sheet_name', 50);       // "E.2", "R.3.1.a"
            $table->string('elemen_kode', 20);      // "E.2", "R.3" (normalized)
            $table->tinyInteger('table_index');     // 1, 2, 3 (urutan tabel dalam sheet)
            $table->string('table_title', 255)->nullable(); // "Tabel E.2.1 Mahasiswa..."

            // Optional link ke dataset_borang (untuk mapping ke template)
            $table->foreignId('id_dataset_borang')
                ->nullable()
                ->constrained('dataset_borang')
                ->onDelete('set null');

            // Data
            $table->json('headers')->nullable();    // [[col1, col2, ...], [...]]
            $table->json('rows');                   // [[val1, val2, ...], [...]]

            // Status review
            $table->enum('status_review', [
                'raw',       // Baru diimport, belum direview
                'reviewed',  // Sudah direview admin/prodi
                'approved',  // Disetujui
                'rejected',  // Ditolak, perlu upload ulang
            ])->default('raw');

            $table->text('catatan_review')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Satu sheet+tabel per import (idempotent re-import)
            $table->unique(
                ['id_borang_import', 'sheet_name', 'table_index'],
                'excel_import_table_unique'
            );

            $table->index(['id_pengajuan', 'sheet_name']);
            $table->index(['id_pengajuan', 'elemen_kode']);
            $table->index(['id_borang_import']);
            $table->index('status_review');
        });

        Schema::create('borang_validations', function (Blueprint $table) {
            $table->id();

            // ✅ Link to assignment (reuse existing table)
            $table->foreignId('id_assignment')
                ->constrained('asesmen_user_roles')
                ->onDelete('cascade');

            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');

            // Validation checklist (flexible JSON)
            $table->json('review_led')->nullable()
                ->comment('Review per elemen: {elemen_id: {grade: A/B/C, catatan: string}}');

            $table->json('review_suplemen')->nullable()
                ->comment('Review suplemen per elemen: {elemen_id: {grade: A/B/C, catatan: string}}');

            $table->json('review_lkps')->nullable()
                ->comment('Review LKPS per indikator kuantitatif: {indikator_id: {grade: A/B/C, catatan: string}}');
            $table->json('revision_points')->nullable(); // Per-section revisions
            // Catatan umum per kategori
            $table->text('catatan_led')->nullable();
            $table->text('catatan_suplemen')->nullable();
            $table->text('catatan_lkps')->nullable();
            $table->text('catatan_validator')->nullable();

            // Statistik otomatis
            $table->integer('total_sections')->default(0);
            $table->integer('validated_sections')->default(0);
            $table->integer('total_elemen_led')->default(0);
            $table->integer('total_elemen_suplemen')->default(0);
            $table->integer('total_indikator_lkps')->default(0);

            $table->integer('reviewed_led')->default(0);
            $table->integer('reviewed_suplemen')->default(0);
            $table->integer('reviewed_lkps')->default(0);
            $table->foreignId('id_validator_assigned')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->enum('final_action', [
                'approve',
                'revision',
            ])->default('revision');
            $table->timestamps();

            $table->index(['id_assignment', 'id_pengajuan']);
        });

        Schema::create('dataset_suplemen', function (Blueprint $table) {
            $table->id();
            $table->string('degree_level_code', 50); // d1, d2, d3, d4, s1, s1-terapan, s2, s2-terapan, s3, s3-terapan, profesi
            $table->string('section_key', 100); // bagian_a_common, pemastian_cpl, dll
            $table->string('content_type', 50); // list_item, paragraph, bullet
            $table->integer('numbering_level')->nullable(); // 0, 1, 2, 3
            $table->text('text_content');
            $table->json('formatting')->nullable(); // {size: 11, bold: true, spaceAfter: 120}
            $table->integer('urutan')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->index(['degree_level_code', 'urutan']);
            $table->foreign('parent_id')->references('id')->on('dataset_suplemen')->onDelete('cascade');
        });

        // ========================================
        // 3. NEW TABLE: borang_revision_history
        // ========================================
        Schema::create('borang_revision_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_validation')
                ->constrained('borang_validations')
                ->onDelete('cascade');

            $table->integer('revision_number')->default(1);
            $table->json('revised_sections')->nullable();
            $table->text('revision_notes')->nullable();

            $table->foreignId('revised_by')->constrained('users');
            $table->timestamp('revised_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('borang_revision_history');
        Schema::dropIfExists('dataset_suplemen');
        Schema::dropIfExists('borang_validations');
        Schema::dropIfExists('borang_data');
        Schema::dropIfExists('borang_imports');
        Schema::dropIfExists('dataset_borang_degree_level');
        Schema::dropIfExists('dataset_borang');
    }
};
