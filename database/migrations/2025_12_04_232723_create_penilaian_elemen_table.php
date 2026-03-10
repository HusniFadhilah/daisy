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
        Schema::create('penilaian_elemen_ak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_asesor')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_elemen')->constrained('elemen_standar', 'id')->onDelete('cascade');
            $table->integer('skor')->nullable()->comment('0=Not Met, 1=Not Met, 2=Weakness, 3=Met');
            $table->text('komentar')->nullable()->comment('Deskripsi/justifikasi penilaian asesor');
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            // Status validasi oleh validator
            $table->enum('status_validasi', ['pending', 'not_validated', 'validated', 'validated_diff', 'revision_required', 'approved'])
                ->default('not_validated');
            $table->integer('preferensi_skor')->nullable()->comment('Skor yang disarankan validator');
            $table->integer('skor_final')->nullable()->comment('Skor final yang disetujui validator');
            $table->text('catatan_validator')->nullable();

            // ID validator yang memvalidasi
            $table->foreignId('validated_by')->nullable()
                ->constrained('users', 'id')
                ->onDelete('set null');

            // Tanggal validasi
            $table->timestamp('validated_at')->nullable();

            // Catatan validasi dari validator
            $table->text('validation_note')->nullable();

            // Versi penilaian (untuk tracking revisi)
            $table->integer('revision_count')->default(0);

            $table->boolean('is_locked')->comment('0-false,1-true')->default(0);
            $table->timestamps();

            // Unique constraint: satu user hanya bisa nilai 1 elemen 1x per asesmen
            $table->unique(['id_asesmen', 'id_asesor', 'id_elemen'], 'unique_penilaian');

            // Index untuk query cepat
            $table->index(['id_asesmen', 'id_asesor']);
            $table->index('status_validasi');
            $table->index('status');
        });

        Schema::create('penilaian_elemen_al', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_asesor')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_elemen')->constrained('elemen_standar', 'id')->onDelete('cascade');
            $table->integer('skor')->nullable()->comment('0=Not Met, 1=Not Met, 2=Weakness, 3=Met');
            $table->text('komentar')->nullable()->comment('Deskripsi/justifikasi penilaian asesor');
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');

            $table->boolean('is_locked')->comment('0-false,1-true')->default(0);
            $table->timestamps();

            // Unique constraint: satu user hanya bisa nilai 1 elemen 1x per asesmen
            $table->unique(['id_asesmen', 'id_asesor', 'id_elemen'], 'unique_penilaian');

            // Index untuk query cepat
            $table->index(['id_asesmen', 'id_asesor']);
            $table->index('status');
        });

        Schema::create('penilaian_elemen_ak_banding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_asesor')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_elemen')->constrained('elemen_standar', 'id')->onDelete('cascade');
            $table->integer('skor')->nullable()->comment('0=Not Met, 1=Not Met, 2=Weakness, 3=Met');
            $table->text('komentar')->nullable()->comment('Deskripsi/justifikasi penilaian asesor banding');
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            // Status validasi oleh validator
            $table->enum('status_validasi', ['pending', 'not_validated', 'validated', 'validated_diff', 'revision_required', 'approved'])
                ->default('not_validated');
            $table->integer('preferensi_skor')->nullable()->comment('Skor yang disarankan validator');
            $table->integer('skor_final')->nullable()->comment('Skor final yang disetujui validator');
            $table->text('catatan_validator')->nullable();

            // ID validator yang memvalidasi
            $table->foreignId('validated_by')->nullable()
                ->constrained('users', 'id')
                ->onDelete('set null');

            // Tanggal validasi
            $table->timestamp('validated_at')->nullable();

            // Catatan validasi dari validator
            $table->text('validation_note')->nullable();

            // Versi penilaian (untuk tracking revisi)
            $table->integer('revision_count')->default(0);

            $table->boolean('is_locked')->comment('0-false,1-true')->default(0);
            $table->timestamps();

            // Unique constraint: satu user hanya bisa nilai 1 elemen 1x per asesmen
            $table->unique(['id_asesmen', 'id_asesor', 'id_elemen'], 'unique_penilaian');

            // Index untuk query cepat
            $table->index(['id_asesmen', 'id_asesor']);
            $table->index('status_validasi');
            $table->index('status');
        });

        Schema::create('penilaian_elemen_al_banding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_asesor')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_elemen')->constrained('elemen_standar', 'id')->onDelete('cascade');
            $table->integer('skor')->nullable()->comment('0=Not Met, 1=Not Met, 2=Weakness, 3=Met');
            $table->text('komentar')->nullable()->comment('Deskripsi/justifikasi penilaian asesor  banding');
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');

            $table->boolean('is_locked')->comment('0-false,1-true')->default(0);
            $table->timestamps();

            // Unique constraint: satu user hanya bisa nilai 1 elemen 1x per asesmen
            $table->unique(['id_asesmen', 'id_asesor', 'id_elemen'], 'unique_penilaian');

            // Index untuk query cepat
            $table->index(['id_asesmen', 'id_asesor']);
            $table->index('status');
        });

        Schema::create('penilaian_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_asesor')->constrained('users', 'id')->onDelete('cascade');
            $table->string('filename');
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->integer('total_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->json('errors_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['id_asesmen', 'id_asesor', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_import_logs');
        Schema::dropIfExists('penilaian_elemen_al_banding');
        Schema::dropIfExists('penilaian_elemen_ak_banding');
        Schema::dropIfExists('penilaian_elemen_al');
        Schema::dropIfExists('penilaian_elemen_ak');
    }
};
