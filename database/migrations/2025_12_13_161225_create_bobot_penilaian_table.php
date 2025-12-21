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
        Schema::create('bobot_penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_elemen');
            $table->foreignId('id_level')->constrained('degree_levels')->onDelete('cascade');
            $table->integer('bobot');
            $table->timestamps();
            
            // Foreign key for elemen_standar with custom reference
            $table->foreign('id_elemen')->references('id_elemen')->on('elemen_standar')->onDelete('cascade');
            
            // Indexes
            $table->index('id_elemen');
            $table->index('id_level');
            
            // Unique constraint
            $table->unique(['id_elemen', 'id_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bobot_penilaian');
    }
};
