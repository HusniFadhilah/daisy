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
        Schema::create('penilaian_elemen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('id_elemen')->constrained('elemen_standar', 'id_elemen')->onDelete('cascade');
            $table->integer('skor')->nullable()->comment('0=Not Met, 1=Not Met, 2=Weakness, 3=Met');
            $table->text('komentar')->nullable()->comment('Deskripsi/justifikasi penilaian asesor');
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();

            // Unique constraint: satu user hanya bisa nilai 1 elemen 1x per assessment
            $table->unique(['id_asesmen', 'id_user', 'id_elemen'], 'unique_penilaian');

            // Index untuk query cepat
            $table->index(['id_asesmen', 'id_user']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_elemen');
    }
};
