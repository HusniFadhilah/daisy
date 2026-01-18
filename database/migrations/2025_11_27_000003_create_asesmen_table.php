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
        Schema::create('asesmens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')
                ->nullable()
                ->constrained('pengajuan_akreditasi')
                ->onDelete('set null')
                ->comment('Relasi ke pengajuan akreditasi (jika ada)');

            // Add fields from StudyProgram
            $table->foreignId('id_study_program')
                ->nullable()
                ->constrained('study_programs')
                ->onDelete('set null')
                ->comment('Program studi yang diases');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');

            // Informasi Perguruan Tinggi
            $table->string('kode_panel', 50)->nullable();

            // Periode
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            // Status
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])
                ->default('draft');

            // Index
            $table->index('status');
            $table->index('kode_panel');

            $table->timestamps();
        });

        Schema::create('asesmen_kecukupan', function (Blueprint $table) {
            $table->id();

            // Foreign key to main asesmen
            $table->foreignId('id_asesmen')
                ->constrained('asesmens')
                ->onDelete('cascade')
                ->comment('Link to main asesmen');

            // Basic info
            $table->string('code', 50)->unique()->comment('Unique code for AK, e.g., AK-12345');

            // Schedule
            $table->date('tanggal_mulai')->nullable()->comment('Start date of AK');
            $table->date('tanggal_selesai')->nullable()->comment('End date of AK');

            // Status
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])
                ->default('draft')
                ->comment('Status of Asesmen Kecukupan');

            // Additional info
            $table->text('catatan')->nullable()->comment('Notes or remarks');
            $table->text('hasil_asesmen')->nullable()->comment('Assessment results/summary');

            // Completion tracking
            $table->timestamp('completed_at')->nullable()->comment('When AK was completed');
            $table->foreignId('completed_by')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Who completed this AK');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('id_asesmen');
            $table->index('status');
            $table->index(['id_asesmen', 'status']);
        });

        Schema::create('asesmen_lapangan', function (Blueprint $table) {
            $table->id();

            // Foreign key to main asesmen
            $table->foreignId('id_asesmen')
                ->constrained('asesmens')
                ->onDelete('cascade')
                ->comment('Link to main asesmen');

            // Link to AK (optional - AL usually follows AK)
            $table->foreignId('id_asesmen_kecukupan')
                ->nullable()
                ->constrained('asesmen_kecukupan')
                ->onDelete('set null')
                ->comment('Link to related Asesmen Kecukupan');

            // Basic info
            $table->string('code', 50)->unique()->comment('Unique code for AL, e.g., AL-12345');

            // Schedule
            $table->date('tanggal_mulai')->nullable()->comment('Start date of AL');
            $table->date('tanggal_selesai')->nullable()->comment('End date of AL');

            // Location info for field assessment
            $table->string('lokasi')->nullable()->comment('Location of field assessment');
            $table->text('alamat')->nullable()->comment('Detailed address');
            $table->string('koordinat')->nullable()->comment('GPS coordinates if applicable');

            // Status
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])
                ->default('draft')
                ->comment('Status of Asesmen Lapangan');

            // Additional info
            $table->text('agenda')->nullable()->comment('Assessment agenda/schedule');
            $table->text('catatan')->nullable()->comment('Notes or remarks');
            $table->text('hasil_asesmen')->nullable()->comment('Assessment results/summary');

            // Documents
            $table->text('link_laporan')->nullable()->comment('Link to final report');
            $table->text('link_dokumentasi')->nullable()->comment('Link to documentation/photos');

            // Completion tracking
            $table->timestamp('completed_at')->nullable()->comment('When AL was completed');
            $table->foreignId('completed_by')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Who completed this AL');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('id_asesmen');
            $table->index('id_asesmen_kecukupan');
            $table->index('status');
            $table->index(['id_asesmen', 'status']);
            $table->index(['id_asesmen_kecukupan', 'status']);
        });

        Schema::create('asesmen_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_role')->constrained('roles', 'id')->onDelete('cascade');
            $table->enum('jenis_asesmen', ['ak', 'al', 'dokumen'])->default('ak')->comment('Type of asesmen: ak (Asesmen Kecukupan) or al (Asesmen Lapangan)');
            $table->foreignId('id_asesmen_kecukupan')
                ->nullable()
                ->constrained('asesmen_kecukupan')
                ->onDelete('cascade');
            $table->foreignId('id_asesmen_lapangan')
                ->nullable()
                ->constrained('asesmen_lapangan')
                ->onDelete('cascade');
            $table->integer('urutan_asesor')->nullable();

            // Status penawaran dan assignment
            $table->enum('status_penawaran', [
                'pending',      // Menunggu respon asesor/validator
                'accepted',     // Diterima oleh asesor/validator
                'rejected',     // Ditolak oleh asesor/validator
            ])->default('pending');

            // Tanggal respon
            $table->timestamp('responded_at')->nullable();

            // Catatan dari asesor/validator saat menerima/menolak
            $table->text('response_note')->nullable();

            // Status pekerjaan asesor
            $table->enum('status_pekerjaan', [
                'not_started',  // Belum mulai
                'in_progress',  // Sedang dikerjakan
                'submitted',    // Sudah submit
                'validated',    // Sudah divalidasi (untuk asesor)
                'revision_required',     // Perlu revisi
                'approved',     // Disetujui final
            ])->default('not_started');

            // Tanggal submit penilaian
            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users', 'id')
                ->onDelete('set null');
            // Index untuk query cepat
            $table->index('status_penawaran');
            $table->index('status_pekerjaan');
            $table->index(['id_asesmen', 'jenis_asesmen']);
            $table->index(['id_asesmen_kecukupan', 'id_role']);
            $table->index(['id_asesmen_lapangan', 'id_role']);
            $table->index('urutan_asesor');
            $table->unique(
                ['id_asesmen', 'id_user', 'jenis_asesmen'],
                'unique_asesmen_user_borang'
            );
            $table->timestamps();
        });

        Schema::create('asesmen_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_asesmen')
                ->constrained('asesmens')
                ->cascadeOnDelete();

            // contoh type: berita_acara, lampiran, foto, dll
            $table->string('type', 50)->index();

            // judul/label file (mis: "Berita Acara Hari 1", "Lampiran A")
            $table->string('title')->nullable();

            // urutan saat digabung ke laporan (semakin kecil semakin dulu)
            $table->unsignedInteger('sort_order')->default(1)->index();

            // path file di storage/public
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime', 100)->nullable();

            // status aktif (bisa nonaktifkan kalau file lama)
            $table->boolean('is_active')->default(true)->index();

            // optional: versi per dokumen (kalau kamu mau revisi file yang sama)
            $table->unsignedInteger('version')->default(1);

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();

            $table->timestamps();

            // Boleh banyak file aktif -> JANGAN unique (id_asesmen,type)
            $table->index(['id_asesmen', 'type', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen_documents');
        Schema::dropIfExists('asesmen_user_roles');
        Schema::dropIfExists('asesmen_lapangan');
        Schema::dropIfExists('asesmen_kecukupan');
        Schema::dropIfExists('asesmens');
    }
};
