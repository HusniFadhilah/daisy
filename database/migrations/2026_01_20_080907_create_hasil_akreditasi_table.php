<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_akreditasi', function (Blueprint $table) {
            $table->id();

            // Range skor (contoh: 0-200, 201-280, dst)
            $table->unsignedSmallInteger('skor_min');
            $table->unsignedSmallInteger('skor_max');

            // Range persentase (contoh: 0-50, 51-70, dst)
            $table->unsignedTinyInteger('persen_min');
            $table->unsignedTinyInteger('persen_max');

            // Makna pemenuhan syarat
            $table->text('makna');

            // Status akreditasi (Tidak Terakreditasi / Terakreditasi / Terakreditasi Unggul)
            $table->string('status', 50);
            $table->string('warna', 20)->nullable();

            // Siklus pembinaan/reakreditasi (tahun)
            $table->unsignedTinyInteger('siklus_tahun');

            // opsional: urutan tampilan
            $table->unsignedTinyInteger('urutan')->default(1);

            $table->timestamps();

            // Index biar pencarian by range cepat
            $table->index(['skor_min', 'skor_max']);
            $table->index(['persen_min', 'persen_max']);
        });

        Schema::create('hasil_akreditasi', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('id_pengajuan')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_asesmen')
                ->constrained('asesmens')
                ->onDelete('cascade');
            $table->foreignId('id_study_program')
                ->constrained('study_programs')
                ->onDelete('cascade');
            $table->foreignId('id_category')
                ->constrained('study_program_categories')
                ->onDelete('cascade');
            $table->foreignId('id_status_ak')->nullable()->constrained('status_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_status_al')->nullable()->constrained('status_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_status_hasil')->nullable()->constrained('status_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_status_banding')->nullable()->constrained('status_akreditasi')
                ->onDelete('cascade');
            $table->foreignId('id_status_final')->nullable()->constrained('status_akreditasi')
                ->onDelete('cascade');

            // Hasil AK (Asesmen Kecukupan)
            $table->decimal('skor_ak', 8, 2)->nullable()->comment('Total skor AK');
            $table->decimal('skor_ak_tertimbang', 8, 2)->nullable()->comment('Skor AK setelah bobot');
            $table->integer('total_bobot_ak')->nullable();
            $table->json('detail_skor_ak')->nullable()->comment('Detail per kriteria');
            $table->json('pelampauan_standar_ak')->nullable()->comment('Track elemen dengan skor 4 per kriteria - AK');
            $table->timestamp('tanggal_finalisasi_ak')->nullable();
            $table->foreignId('finalized_ak_by')->nullable()->constrained('users');

            // Hasil AL (Asesmen Lapangan)
            $table->decimal('skor_al', 8, 2)->nullable()->comment('Total skor AL');
            $table->decimal('skor_al_tertimbang', 8, 2)->nullable()->comment('Skor AL setelah bobot');
            $table->integer('total_bobot_al')->nullable();
            $table->json('detail_skor_al')->nullable()->comment('Detail per kriteria');
            $table->json('pelampauan_standar_al')->nullable()->comment('Track elemen dengan skor 4 per kriteria - AL');
            $table->timestamp('tanggal_finalisasi_al')->nullable();
            $table->foreignId('finalized_al_by')->nullable()->constrained('users');

            $table->decimal('skor_hasil', 8, 2)->nullable()->comment('Total skor Hasil');
            $table->decimal('skor_hasil_tertimbang', 8, 2)->nullable()->comment('Skor Hasil setelah bobot');
            $table->integer('total_bobot_hasil')->nullable();
            $table->json('detail_skor_hasil')->nullable()->comment('Detail per kriteria');
            $table->json('pelampauan_standar_hasil')->nullable()->comment('Track elemen dengan skor 4 per kriteria - Hasil');
            $table->timestamp('tanggal_finalisasi_hasil')->nullable();
            $table->foreignId('finalized_hasil_by')->nullable()->constrained('users');

            $table->decimal('skor_banding', 8, 2)->nullable()->comment('Total skor Banding');
            $table->decimal('skor_banding_tertimbang', 8, 2)->nullable()->comment('Skor Banding setelah bobot');
            $table->integer('total_bobot_banding')->nullable();
            $table->json('detail_skor_banding')->nullable()->comment('Detail per kriteria');
            $table->json('pelampauan_standar_banding')->nullable()->comment('Track elemen dengan skor 4 per kriteria - Banding');
            $table->timestamp('tanggal_finalisasi_banding')->nullable();
            $table->foreignId('finalized_banding_by')->nullable()->constrained('users');

            // Hasil Final (Setelah penetapan)
            $table->decimal('skor_final', 8, 2)->nullable()->comment('Skor akhir (0-400)');
            $table->decimal('skor_final_tertimbang', 8, 2)->nullable()->comment('Skor Final setelah bobot');
            $table->integer('total_bobot_final')->nullable();
            $table->json('detail_skor_final')->nullable()->comment('Detail per kriteria');
            $table->json('pelampauan_standar_final')->nullable()->comment('Track elemen dengan skor 4 per kriteria - Final');
            $table->timestamp('tanggal_finalisasi_penetapan')->nullable();
            $table->foreignId('finalized_penetapan_by')->nullable()->constrained('users');
            $table->text('catatan_penetapan')->nullable();
            $table->string('peringkat_akreditasi_hasil')->nullable()->comment('Diambil dari status akreditasi hasil');
            $table->string('peringkat_akreditasi_banding')->nullable()->comment('Diambil dari status akreditasi banding');
            $table->string('peringkat_akreditasi_final')->nullable()->comment('Diambil dari status akreditasi final');

            $table->boolean('memenuhi_syarat_unggul')->default(false)->comment('Apakah memenuhi syarat melampaui standar untuk Unggul');
            $table->text('catatan_validasi')->nullable()->comment('Catatan hasil validasi syarat Unggul');

            // Metadata
            $table->enum('status', [
                'draft',       // AK & AL in progress
                'draft_ak',       // AK calculation in progress
                'final_ak',       // AK finalized, waiting AL
                'draft_al',       // AL calculation in progress
                'final_al',       // AL finalized
                'draft_hasil',       // Hasil calculation in progress
                'final_hasil',       // Hasil finalized
                'draft_banding',       // Banding calculation in progress
                'final_banding',       // Banding finalized
                'draft_penetapan',
                'final_penetapan', // Combined score calculated
                'published'       // Result published to prodi
            ])->default('draft');

            $table->text('catatan_perhitungan')->nullable();
            $table->json('metadata')->nullable()->comment('Additional calculation metadata');

            $table->timestamps();

            // Indexes
            $table->index(['id_pengajuan', 'status']);
            $table->index('id_asesmen');
            $table->index('peringkat_akreditasi_hasil');
            $table->index('peringkat_akreditasi_banding');
            $table->index('peringkat_akreditasi_final');
        });

        Schema::create('syarat_akreditasi', function (Blueprint $table) {
            $table->id();

            // Pengelompokan syarat agar mudah di-query sekaligus
            $table->enum('kelompok', [
                'skor',         // threshold skor minimum
                'pelampauan',   // kriteria yang wajib ada elemen melampaui standar
                'rasio_dtps',   // rasio DTPS:Mahasiswa per rumpun
                'jabatan',      // syarat jabatan fungsional dosen
                'rentang_skor',
                'syarat_kualitatif',
            ])->index();

            // Kunci unik dalam kelompok, contoh:
            //   skor       → 'skor_minimum_unggul'
            //   pelampauan → 'kriteria_required'
            //   rasio_dtps → 'max_rasio_lingkungan', 'max_rasio_default'
            //   jabatan    → 'jabatan_valid_lektor', 'persen_minimum_lektor'
            $table->string('kunci', 100);

            // Nilai disimpan sebagai string; casting sesuai tipe
            $table->text('nilai');

            // Tipe data untuk casting saat dibaca
            $table->enum('tipe', ['integer', 'float', 'string', 'array', 'boolean', 'json'])
                ->default('string');

            $table->string('label', 200)
                ->comment('Label deskriptif untuk UI admin');

            $table->text('keterangan')->nullable()
                ->comment('Penjelasan lengkap syarat ini');

            // Versi / periode berlaku
            $table->string('versi', 20)->default('1.0')
                ->comment('Versi regulasi, mis. 2024-v1');

            $table->date('berlaku_mulai')->nullable()
                ->comment('Tanggal syarat ini mulai berlaku');

            $table->date('berlaku_sampai')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Satu kunci per kelompok per versi hanya boleh ada satu yang aktif
            $table->unique(['kelompok', 'kunci', 'versi'], 'unique_syarat_kelompok_kunci_versi');
            $table->index(['kelompok', 'is_active']);
        });

        // Riwayat perubahan syarat untuk audit
        Schema::create('syarat_akreditasi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_syarat')
                ->constrained('syarat_akreditasi')
                ->cascadeOnDelete();
            $table->text('nilai_lama');
            $table->text('nilai_baru');
            $table->string('alasan')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index('id_syarat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_akreditasi');
    }
};
