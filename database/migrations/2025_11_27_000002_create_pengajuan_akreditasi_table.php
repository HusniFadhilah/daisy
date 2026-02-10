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
            $table->string('nomor_permohonan')->nullable(); // Auto-generated: AK/2024/001

            // Relasi
            $table->foreignId('id_program_studi')->constrained('study_programs')->onDelete('cascade');
            $table->foreignId('id_user_pengaju')->default(null)->nullable()->constrained('users')->comment('User dari prodi yang mengajukan');
            $table->foreignId('id_de_assigned')->default(null)->nullable()->constrained('users')->comment('DE yang ditugaskan');
            $table->foreignId('id_validator_assigned')->default(null)->nullable()->constrained('users')->comment('Validator yang ditugaskan');

            // Data Pengajuan
            $table->year('tahun_akreditasi');
            $table->string('pemohon_email')->nullable();
            $table->string('pemohon_phone', 20)->nullable();
            $table->enum('jenis_akreditasi', ['baru', 'terakreditasi', 'perpanjangan', 'menuju_unggul']);
            $table->enum('kelompok_akreditasi', ['individual', 'kelompok'])->default('individual')->comment('Kelompok akreditasi: individual (per prodi) atau kelompok (beberapa prodi)');
            $table->date('tanggal_pengajuan')->nullable();
            $table->text('catatan_pengaju')->nullable();

            // Status
            $table->enum('status', [
                'new',
                'draft',
                'pengingat_dikirim',
                'surat_permohonan_dikirim',
                'surat_permohonan_diterima',
                'surat_permohonan_upload_ulang',
                'surat_permohonan_ditolak',
                'surat_penerimaan_dikirim',
                'template_borang_dikirim',
                'menunggu_pembayaran',
                'pembayaran_diterima',
                'menunggu_verifikasi_pembayaran',
                'pembayaran_diverifikasi',
                'draft_borang_dikirim',
                'draft_borang_diterima',
                'borang_online_selesai',
                'borang_validation_pending',
                'borang_in_validation',
                'borang_revision_required',
                'borang_validated',
                'borang_final_diterima',
                'validasi_borang_dilaporkan',
                'pengajuan_completed',
                'asesor_ak_assigned',
                'ak_in_progress',
                'ak_on_validation',
                'ak_selesai',
                'ak_dilaporkan',
                'asesor_al_assigned',
                'al_in_progress',
                'al_selesai',
                'al_dilaporkan',
                'hasil_akreditasi_dihitung',
                'hasil_akreditasi_dikirim',
                'masa_sanggah_dimulai',
                'masa_sanggah_selesai',
                'banding_diajukan',
                'banding_diterima',
                'banding_ditugaskan',
                'banding_dilaksanakan',
                'banding_dilaporkan',
                'hasil_ditetapkan',
                'hasil_dilaporkan',
                'hasil_diumumkan',
                'arsip_disimpan',
                'selesai',
                'ditolak',
            ])->default('pengingat_dikirim');

            // Tracking
            $table->timestamp('tanggal_pengingat')->nullable();
            $table->timestamp('tanggal_surat_permohonan_dikirim')->nullable();
            $table->timestamp('tanggal_surat_permohonan_diterima')->nullable();
            $table->timestamp('tanggal_surat_permohonan_upload_ulang')->nullable();
            $table->timestamp('tanggal_surat_permohonan_ditolak')->nullable();
            $table->timestamp('tanggal_surat_penerimaan_dikirim')->nullable();
            $table->timestamp('tanggal_template_led_dikirim')->nullable();
            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->timestamp('tanggal_draft_borang')->nullable();

            // ===== Timeline baru =====
            // Borang validation
            $table->timestamp('tanggal_validasi_borang_assigned')->nullable()->comment('Tanggal validator di-assign untuk review LED');
            $table->timestamp('tanggal_borang_final')->nullable();
            $table->timestamp('tanggal_validasi_borang_selesai')->nullable()->comment('Tanggal validator approve/request revision LED');
            $table->timestamp('tanggal_pelaporan_validasi_borang')->nullable();
            $table->timestamp('tanggal_lanjut_ak')->nullable();

            // AK Timeline
            $table->timestamp('tanggal_penugasan_asesor_ak')->nullable();
            $table->timestamp('tanggal_ak_mulai')->nullable()->comment('Tanggal mulai proses AK/Penilaian Dokumen');
            $table->timestamp('tanggal_validasi_ak')->nullable()->comment('Tanggal mulai validasi hasil AK');
            $table->timestamp('tanggal_ak_selesai')->nullable()->comment('Tanggal selesai validasi hasil AK');
            $table->timestamp('tanggal_pelaporan_ak')->nullable()->comment('Tanggal selesai pelaporan hasil AK');

            // AL Timeline
            $table->timestamp('tanggal_penugasan_asesor_al')->nullable();
            $table->timestamp('tanggal_pelaksanaan_al')->nullable();
            $table->timestamp('tanggal_al_mulai')->nullable()->comment('Tanggal mulai proses AL/Asesmen Lapangan');
            $table->timestamp('tanggal_al_selesai')->nullable()->comment('Tanggal selesai validasi hasil AL');
            $table->timestamp('tanggal_pelaporan_al')->nullable();

            // Final Timeline
            $table->timestamp('tanggal_hasil_akreditasi_dihitung')->nullable()->comment('Tanggal hasil akreditasi dihitung');
            $table->timestamp('tanggal_hasil_akreditasi_dikirim')->nullable()->comment('Tanggal penyampaian hasil akreditasi');
            $table->timestamp('tanggal_masa_sanggah_mulai')->nullable();
            $table->timestamp('tanggal_masa_sanggah_selesai')->nullable();
            $table->timestamp('tanggal_permohonan_banding')->nullable()->comment('Tanggal pengajuan banding (optional)');
            $table->timestamp('tanggal_penerimaan_banding')->nullable()->comment('Tanggal penerimaan banding (optional)');
            $table->timestamp('tanggal_penugasan_banding')->nullable()->comment('Tanggal penugasan banding (optional)');
            $table->timestamp('tanggal_pelaksanaan_banding')->nullable();
            $table->timestamp('tanggal_pelaporan_banding')->nullable();
            $table->timestamp('tanggal_penetapan')->nullable()->comment('Tanggal penetapan hasil akreditasi');
            $table->timestamp('tanggal_pelaporan_hasil')->nullable();
            $table->timestamp('tanggal_pengumuman')->nullable()->comment('Tanggal pengumuman hasil akreditasi');
            $table->timestamp('tanggal_penyimpanan')->nullable()->comment('Tanggal penyimpanan berkas akreditasi');

            $table->string('peringkat_hasil')->nullable()->comment('Peringkat hasil akreditasi awal yang disampaikan ke prodi');
            $table->decimal('nilai_akhir', 6, 2)->nullable()->comment('Nilai hasil akreditasi awal (0-400)');
            $table->string('peringkat_hasil_banding')->nullable()->comment('Peringkat hasil setelah proses banding (jika ada)');
            $table->decimal('nilai_akhir_banding', 6, 2)->nullable()->comment('Nilai hasil setelah proses banding (jika ada)');
            $table->string('peringkat_final')->nullable();
            $table->decimal('skor_final', 6, 2)->nullable();
            $table->integer('masa_berlaku_tahun')->default(null)->nullable();
            $table->text('catatan_hasil')->nullable();
            $table->text('alasan_banding')->nullable();
            $table->enum('hasil_banding', ['diterima', 'ditolak'])->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_example')->default(false)->index();

            $table->timestamps();
        });

        Schema::create('pengingat_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_program_studi')
                ->constrained('study_programs')
                ->onDelete('cascade');
            $table->foreignId('id_de_pengirim')
                ->constrained('users')
                ->onDelete('cascade');
            $table->year('tahun_akreditasi');
            $table->text('pesan_pengingat');
            $table->datetime('tanggal_dikirim');
            $table->enum('status', ['belum_direspon', 'direspon', 'kedaluwarsa'])
                ->default('belum_direspon');
            $table->datetime('tanggal_direspon')->nullable();
            $table->foreignId('id_pengajuan')
                ->nullable()
                ->constrained('pengajuan_akreditasi')
                ->onDelete('set null');
            $table->string('email_terkirim_ke')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('id_program_studi');
            $table->index('tahun_akreditasi');
            $table->index('status');
            $table->index('tanggal_dikirim');
        });

        Schema::create('pengajuan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan')->constrained('pengajuan_akreditasi')->onDelete('cascade');

            $table->enum('jenis_dokumen', [
                'surat_permohonan',
                'surat_penerimaan_de',
                'surat_tugas',
                'surat_tugas_validator',
                'surat_tugas_validator_dokumen',
                'surat_tugas_asesor_ak',
                'surat_tugas_validator_ak',
                'surat_tugas_asesor_al',
                'surat_tugas_validator_al',
                'surat_tugas_validator_rekap',
                'borang_template',
                'template_formulir_pembayaran',
                'formulir_pembayaran',
                'draft_borang',
                'borang_final',
                'bukti_pembayaran',
                'lembar_pengesahan',
                'dokumen_pendukung',
                'laporan_ak',
                'laporan_al',
                'sertifikat',
                'data_kualitatif',
                'data_kuantitatif',
                'data_suplemen',
                'lainnya'
            ])->index();

            $table->string('nama_file')->nullable(); // ✅ Nullable jika pakai link
            $table->string('path_file')->nullable(); // ✅ Nullable jika pakai link
            $table->string('original_filename')->nullable(); // ✅ Nullable jika pakai link
            $table->unsignedBigInteger('file_size')->nullable()
                ->comment('File size in bytes'); // ✅ Use unsignedBigInteger for large files
            $table->string('mime_type', 100)->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->text('keterangan')->nullable();
            $table->string('template_link', 500)->nullable()->comment('Link to template if sent via link instead of file upload');
            $table->integer('versi')->default(1);
            $table->boolean('is_latest')->default(true)->index();

            $table->timestamps();

            $table->index(['id_pengajuan', 'jenis_dokumen', 'is_latest'], 'idx_pengajuan_jenis_latest');
            $table->index('created_at', 'idx_created_at');
        });

        Schema::create('pengajuan_pembayaran', function (Blueprint $table) {
            $table->id();

            // Relasi ke permohonan akreditasi
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->cascadeOnDelete();

            // Informasi pembayaran
            $table->string('nomor_invoice')->unique();
            $table->decimal('jumlah_pembayaran', 15, 2);

            // Tanggal
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->dateTime('tanggal_upload_ulang')->nullable();
            $table->dateTime('tanggal_ditolak')->nullable();

            // Status pembayaran
            $table->enum('status_pembayaran', [
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'upload_ulang',
                'ditolak',
                'terverifikasi',
            ])->default('menunggu_pembayaran');

            // Bukti & verifikasi
            $table->string('bukti_path')->nullable();
            $table->text('catatan_pembayaran')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->text('alasan_penolakan')->nullable();

            // Verifikator
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Index tambahan (opsional tapi disarankan)
            $table->index('status_pembayaran');
            $table->index('tanggal_pembayaran');
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
        Schema::dropIfExists('study_program_users');
        Schema::dropIfExists('pengajuan_status_log');
        Schema::dropIfExists('pengajuan_pembayaran');
        Schema::dropIfExists('pembayaran_akreditasi');
        Schema::dropIfExists('review_kesiapan');
        Schema::dropIfExists('pengajuan_dokumen');
        Schema::dropIfExists('pengajuan_akreditasi');
    }
};
