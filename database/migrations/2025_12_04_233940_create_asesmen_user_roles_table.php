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
        Schema::create('asesmen_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_role')->constrained('roles', 'id')->onDelete('cascade');

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

            // Index untuk query cepat
            $table->index('status_penawaran');
            $table->index('status_pekerjaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen_user_roles');
    }
};
