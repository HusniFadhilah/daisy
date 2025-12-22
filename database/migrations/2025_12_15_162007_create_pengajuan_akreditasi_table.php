<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique(); // Auto-generated: AK/2024/001

            // Relasi
            $table->foreignId('id_program_studi')->constrained('study_programs')->onDelete('cascade');
            $table->foreignId('id_user_pengaju')->default(null)->nullable()->constrained('users')->comment('User dari prodi yang mengajukan');
            $table->foreignId('id_de_assigned')->default(null)->nullable()->constrained('users')->comment('Desk Evaluator yang ditugaskan');

            // Data Pengajuan
            $table->year('tahun_akreditasi');
            $table->enum('jenis_akreditasi', ['baru', 'perpanjangan', 're-akreditasi']);
            $table->date('tanggal_pengajuan')->nullable();
            $table->text('catatan_pengaju')->nullable();

            // Status
            $table->enum('status', [
                'pengingat_dikirim',
                'surat_permohonan_diterima',
                'borang_dikirim',
                'draft_borang_diterima',
                'review_kesiapan_siap',
                'review_kesiapan_belum_siap',
                'menunggu_pembayaran',
                'pembayaran_diterima',
                'borang_final_diterima',
                'lanjut_ke_ak',
                'ditolak'
            ])->default('pengingat_dikirim');

            // Tracking
            $table->timestamp('tanggal_pengingat')->nullable();
            $table->timestamp('tanggal_surat_permohonan')->nullable();
            $table->timestamp('tanggal_borang_dikirim')->nullable();
            $table->timestamp('tanggal_draft_borang')->nullable();
            $table->timestamp('tanggal_review_kesiapan')->nullable();
            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->timestamp('tanggal_borang_final')->nullable();
            $table->timestamp('tanggal_lanjut_ak')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pengajuan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')->constrained('pengajuan_akreditasi')->onDelete('cascade');

            $table->enum('jenis_dokumen', [
                'surat_permohonan',
                'borang_template',
                'draft_borang',
                'borang_final',
                'bukti_pembayaran',
                'lainnya'
            ]);

            $table->string('nama_file');
            $table->string('path_file');
            $table->string('original_filename');
            $table->integer('file_size')->comment('in bytes');
            $table->string('mime_type');

            $table->foreignId('uploaded_by')->constrained('users');
            $table->text('keterangan')->nullable();
            $table->integer('versi')->default(1);
            $table->boolean('is_latest')->default(true);

            $table->timestamps();
        });

        Schema::create('review_kesiapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')->constrained('pengajuan_akreditasi')->onDelete('cascade');
            $table->foreignId('id_reviewer')->constrained('users')->comment('DE yang review');

            $table->enum('hasil_review', ['siap', 'belum_siap']);
            $table->text('catatan_review');
            $table->json('checklist_kesiapan')->nullable()->comment('JSON array checklist items');

            $table->integer('versi_review')->default(1);
            $table->timestamp('tanggal_review');

            $table->timestamps();
        });

        Schema::create('pembayaran_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')->constrained('pengajuan_akreditasi')->onDelete('cascade');

            $table->string('nomor_invoice')->unique();
            $table->decimal('jumlah_pembayaran', 15, 2);
            $table->enum('status_pembayaran', ['pending', 'dibayar', 'verified', 'ditolak'])->default('pending');

            $table->timestamp('tanggal_jatuh_tempo')->nullable();
            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->text('catatan_verifikasi')->nullable();
            $table->text('alasan_penolakan')->nullable();

            $table->timestamps();
        });

        Schema::create('pengajuan_status_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')->constrained('pengajuan_akreditasi')->onDelete('cascade');
            $table->string('status_from');
            $table->string('status_to');
            $table->foreignId('changed_by')->constrained('users');
            $table->text('keterangan')->nullable();
            $table->timestamp('changed_at');

            $table->timestamps();
        });

        Schema::create('study_program_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_study_program')->constrained('study_programs')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');

            // Role user di prodi ini (admin_prodi, koordinator, dll)
            $table->string('role_in_prodi')->default('admin_prodi')->comment('admin_prodi, koordinator, staff');

            // Status aktif/tidak
            $table->boolean('is_active')->default(true);

            // Tanggal mulai dan berakhir (opsional)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();

            // Unique constraint: 1 user tidak bisa punya role yang sama di prodi yang sama
            $table->unique(
                ['id_study_program', 'id_user', 'role_in_prodi'],
                'spu_program_user_role_unique'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('study_program_user');
        Schema::dropIfExists('pengajuan_status_log');
        Schema::dropIfExists('pembayaran_akreditasi');
        Schema::dropIfExists('review_kesiapan');
        Schema::dropIfExists('pengajuan_dokumen');
        Schema::dropIfExists('pengajuan_akreditasi');
    }
};
