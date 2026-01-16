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
            $table->foreignId('id_elemen')->constrained('elemen_standar')->onDelete('cascade');
            $table->foreignId('id_category')->constrained('study_program_categories')->onDelete('cascade');
            $table->integer('bobot');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('id_elemen');
            $table->index('id_category');

            // Unique constraint
            $table->unique(['id_elemen', 'id_category']);
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
