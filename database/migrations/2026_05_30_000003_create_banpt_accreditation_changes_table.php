<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banpt_accreditation_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sync_run')->nullable()->constrained('banpt_sync_runs')->nullOnDelete();
            $table->foreignId('id_study_program')->constrained('study_programs')->cascadeOnDelete();

            // Snapshot lama
            $table->string('old_university_name')->nullable();
            $table->string('old_program_studi')->nullable();
            $table->string('old_jenjang')->nullable();
            $table->string('old_peringkat_akreditasi')->nullable();
            $table->date('old_tanggal_kedaluwarsa')->nullable();
            $table->string('old_status_kedaluwarsa')->nullable();

            // Data baru dari BAN-PT
            $table->string('new_university_name')->nullable();
            $table->string('new_program_studi')->nullable();
            $table->string('new_jenjang')->nullable();
            $table->string('new_peringkat_akreditasi')->nullable();
            $table->date('new_tanggal_kedaluwarsa')->nullable();
            $table->string('new_status_kedaluwarsa')->nullable();

            // Metadata BAN-PT
            $table->string('banpt_pt_label')->nullable();
            $table->string('banpt_ps_label')->nullable();
            $table->json('banpt_payload')->nullable();

            // Identifikasi & status
            $table->string('change_hash');
            $table->string('status')->default('pending'); // pending, applied, ignored, conflict
            $table->text('conflict_reason')->nullable();

            // Timestamps aksi
            $table->timestamp('detected_at');
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ignored_at')->nullable();
            $table->foreignId('ignored_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('id_study_program');
            $table->index('status');
            $table->index('detected_at');
            $table->index('change_hash');
            // Cegah duplikat pending per prodi+hash via logic di service
            $table->index(['id_study_program', 'change_hash', 'status'], 'banpt_changes_prodi_hash_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banpt_accreditation_changes');
    }
};
