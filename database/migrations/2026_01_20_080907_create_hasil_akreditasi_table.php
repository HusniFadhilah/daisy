<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. status_akreditasi ─────────────────────────────
        Schema::create('status_akreditasi', function (Blueprint $table) {
            $table->id();

            // Range skor (0–400)
            $table->unsignedSmallInteger('skor_min');
            $table->unsignedSmallInteger('skor_max');

            // Range persentase
            $table->unsignedTinyInteger('persen_min');
            $table->unsignedTinyInteger('persen_max');

            // Makna pemenuhan syarat (bisa JSON array atau teks biasa)
            $table->text('makna');

            // Label status (Tidak Terakreditasi / Terakreditasi / Terakreditasi Unggul)
            $table->string('status', 50);
            $table->string('warna', 20)->nullable();

            // Siklus pembinaan / reakreditasi (tahun)
            $table->unsignedTinyInteger('siklus_tahun');

            // Urutan tampilan di UI
            $table->unsignedTinyInteger('urutan')->default(1);

            $table->timestamps();

            $table->index(['skor_min', 'skor_max']);
            $table->index(['persen_min', 'persen_max']);
        });

        // ── 2. syarat_akreditasi ─────────────────────────────
        Schema::create('syarat_akreditasi', function (Blueprint $table) {
            $table->id();

            // NULL = berlaku global (semua jenjang)
            // NOT NULL = berlaku untuk degree level tertentu
            // Foreign key ke degree_levels
            $table->foreignId('id_degree_level')->nullable()->constrained('degree_levels')->onDelete('set null');

            $table->enum('kelompok', [
                'skor',
                'pelampauan',
                'rasio_dtps',
                'jabatan',
                'sertifikat',
                'lulusan',
                'rentang_skor',
                'syarat_kualitatif',
            ])->index();

            $table->string('kunci', 100)
                ->comment('Identifier dalam kelompok, contoh: skor_minimum_unggul, jabatan_valid, persen_minimum');

            $table->text('nilai')
                ->comment('Nilai disimpan sebagai string; casting sesuai kolom tipe');

            $table->enum('tipe', ['integer', 'float', 'string', 'array', 'boolean', 'json'])
                ->default('string')
                ->comment('Tipe untuk auto-casting saat dibaca');

            $table->string('label', 200)
                ->comment('Label deskriptif untuk UI admin');

            $table->text('keterangan')->nullable()
                ->comment('Penjelasan lengkap syarat ini');

            $table->string('versi', 20)->default('1.0')
                ->comment('Versi regulasi, mis. 2026-v1');

            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_sampai')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['kelompok', 'is_active']);
            $table->index(['id_degree_level', 'kelompok', 'kunci']);
        });

        // ── 3. syarat_akreditasi_logs ────────────────────────
        Schema::create('syarat_akreditasi_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_syarat')
                ->constrained('syarat_akreditasi')
                ->cascadeOnDelete();

            $table->text('nilai_lama');
            $table->text('nilai_baru');
            $table->string('alasan')->nullable();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index('id_syarat');
            $table->index('changed_at');
        });

        // ── 4. hasil_akreditasi ──────────────────────────────
        Schema::create('hasil_akreditasi', function (Blueprint $table) {
            $table->id();

            // ── Relations ──
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

            // ── Status FK ──
            $table->foreignId('id_status_ak')
                ->nullable()->constrained('status_akreditasi')->nullOnDelete();
            $table->foreignId('id_status_al')
                ->nullable()->constrained('status_akreditasi')->nullOnDelete();
            $table->foreignId('id_status_hasil')
                ->nullable()->constrained('status_akreditasi')->nullOnDelete();
            $table->foreignId('id_status_ak_banding')
                ->nullable()->constrained('status_akreditasi')->nullOnDelete();
            $table->foreignId('id_status_al_banding')
                ->nullable()->constrained('status_akreditasi')->nullOnDelete();
            $table->foreignId('id_status_final')
                ->nullable()->constrained('status_akreditasi')->nullOnDelete();

            // ── Status workflow ──
            $table->enum('status', [
                'draft',           // awal, belum ada perhitungan
                'draft_ak',        // AK sedang dihitung
                'final_ak',        // AK difinalisasi, menunggu AL
                'draft_al',        // AL sedang dihitung
                'final_al',        // AL difinalisasi (jarang dipakai langsung)
                'final_hasil',     // Hasil difinalisasi & disampaikan ke PS
                'draft_ak_banding', // AK Banding sedang dihitung
                'final_ak_banding', // AK Banding difinalisasi, menunggu AL Banding
                'draft_al_banding', // AL Banding sedang dihitung
                'final_al_banding', // AL Banding difinalisasi (jarang dipakai langsung)
                // 'draft_banding',   // Banding sedang diproses
                // 'final_banding',   // Banding difinalisasi
                'draft_penetapan', // Sedang disiapkan untuk penetapan
                'final_penetapan', // Penetapan dikunci
                'published',       // Dipublikasikan ke PS
            ])->default('draft');

            // ── AK (Asesmen Kecukupan) ──
            $table->decimal('skor_ak', 8, 2)->nullable();
            $table->decimal('skor_ak_tertimbang', 8, 2)->nullable();
            $table->decimal('total_bobot_ak', 8, 2)->nullable();
            $table->json('detail_skor_ak')->nullable()
                ->comment('Array detail skor per kriteria + elemen + metadata');
            $table->json('pelampauan_standar_ak')->nullable()
                ->comment('Elemen skor ≥ 4 per kode kriteria — AK');
            $table->timestamp('tanggal_finalisasi_ak')->nullable();
            $table->foreignId('finalized_ak_by')
                ->nullable()->constrained('users')->nullOnDelete();

            // ── AL (Asesmen Lapangan) ──
            $table->decimal('skor_al', 8, 2)->nullable();
            $table->decimal('skor_al_tertimbang', 8, 2)->nullable();
            $table->decimal('total_bobot_al', 8, 2)->nullable();
            $table->json('detail_skor_al')->nullable()
                ->comment('Array detail skor per kriteria + elemen + metadata');
            $table->json('pelampauan_standar_al')->nullable()
                ->comment('Elemen skor ≥ 4 per kode kriteria — AL');
            $table->timestamp('tanggal_finalisasi_al')->nullable();
            $table->foreignId('finalized_al_by')
                ->nullable()->constrained('users')->nullOnDelete();

            // ── Hasil (setelah penyampaian ke PS) ──
            $table->decimal('skor_hasil', 8, 2)->nullable();
            $table->decimal('skor_hasil_tertimbang', 8, 2)->nullable();
            $table->decimal('total_bobot_hasil', 8, 2)->nullable();
            $table->json('detail_skor_hasil')->nullable();
            $table->json('pelampauan_standar_hasil')->nullable();
            $table->timestamp('tanggal_finalisasi_hasil')->nullable();
            $table->foreignId('finalized_hasil_by')
                ->nullable()->constrained('users')->nullOnDelete();

            // ── Banding ──
            // ── AK (Asesmen Kecukupan) Banding ──
            $table->decimal('skor_ak_banding', 8, 2)->nullable();
            $table->decimal('skor_ak_banding_tertimbang', 8, 2)->nullable();
            $table->decimal('total_bobot_ak_banding', 8, 2)->nullable();
            $table->json('detail_skor_ak_banding')->nullable()
                ->comment('Array detail skor per kriteria + elemen + metadata');
            $table->json('pelampauan_standar_ak_banding')->nullable()
                ->comment('Elemen skor ≥ 4 per kode kriteria — AK Banding');
            $table->timestamp('tanggal_finalisasi_ak_banding')->nullable();
            $table->foreignId('finalized_ak_banding_by')
                ->nullable()->constrained('users')->nullOnDelete();

            // ── AL (Asesmen Lapangan) Banding ──
            $table->decimal('skor_al_banding', 8, 2)->nullable();
            $table->decimal('skor_al_banding_tertimbang', 8, 2)->nullable();
            $table->decimal('total_bobot_al_banding', 8, 2)->nullable();
            $table->json('detail_skor_al_banding')->nullable()
                ->comment('Array detail skor per kriteria + elemen + metadata');
            $table->json('pelampauan_standar_al_banding')->nullable()
                ->comment('Elemen skor ≥ 4 per kode kriteria — AL Banding');
            $table->timestamp('tanggal_finalisasi_al_banding')->nullable();
            $table->foreignId('finalized_al_banding_by')
                ->nullable()->constrained('users')->nullOnDelete();

            // $table->decimal('skor_banding', 8, 2)->nullable();
            // $table->decimal('skor_banding_tertimbang', 8, 2)->nullable();
            // $table->decimal('total_bobot_banding', 8, 2)->nullable();
            // $table->json('detail_skor_banding')->nullable();
            // $table->json('pelampauan_standar_banding')->nullable();
            // $table->timestamp('tanggal_finalisasi_banding')->nullable();
            // $table->foreignId('finalized_banding_by')
            //     ->nullable()->constrained('users')->nullOnDelete();

            // ── Final / Penetapan ──
            $table->decimal('skor_final', 8, 2)->nullable()
                ->comment('Skor akhir setelah penetapan (0–400)');
            $table->decimal('skor_final_tertimbang', 8, 2)->nullable();
            $table->decimal('total_bobot_final', 8, 2)->nullable();
            $table->json('detail_skor_final')->nullable();
            $table->json('pelampauan_standar_final')->nullable();
            $table->timestamp('tanggal_finalisasi_penetapan')->nullable();
            $table->foreignId('finalized_penetapan_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_penetapan')->nullable();
            $table->json('resume_asesmen')->nullable()->comment('Resume asesmen untuk halaman 3 sertifikat: ' . 'kondisi prodi (metrik kuantitatif + narasi), ' . 'temuan keunggulan, dan temuan area perbaikan.');

            // ── Peringkat ──
            $table->string('peringkat_akreditasi_hasil', 60)->nullable();
            $table->string('peringkat_akreditasi_banding', 60)->nullable();
            $table->string('peringkat_akreditasi_final', 60)->nullable();

            // ── Syarat Unggul ──
            $table->boolean('memenuhi_syarat_unggul')->default(false)
                ->comment('True jika semua syarat Unggul terpenuhi saat finalisasi');
            $table->text('catatan_validasi')->nullable()
                ->comment('Keterangan lengkap hasil cek syarat Unggul');

            $table->text('catatan_perhitungan')->nullable();
            $table->json('metadata')->nullable()
                ->comment('Audit trail: syarat_unggul_check, syarat_unggul_check_penetapan, dll.');

            $table->timestamps();

            // ── Indexes ──
            $table->index(['id_pengajuan', 'status']);
            $table->index('id_asesmen');
            $table->index('id_study_program');
            $table->index('peringkat_akreditasi_hasil');
            $table->index('peringkat_akreditasi_banding');
            $table->index('peringkat_akreditasi_final');
            $table->index('memenuhi_syarat_unggul');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_akreditasi');
    }
};
