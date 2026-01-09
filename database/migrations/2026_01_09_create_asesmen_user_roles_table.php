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
        if (!Schema::hasTable('asesmen_user_roles')) {
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
                    'belum_mulai',
                    'dalam_proses',
                    'selesai',
                ])->default('belum_mulai');

                $table->timestamps();

                // Indexes
                $table->index('id_asesmen');
                $table->index('id_user');
                $table->index('id_role');
                $table->index('status_penawaran');
                $table->index('jenis_asesmen');
                $table->index(['id_asesmen', 'id_user', 'id_role'], 'idx_asesmen_user_role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen_user_roles');
    }
};
