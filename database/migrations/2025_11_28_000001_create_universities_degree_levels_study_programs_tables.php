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
        // Create universities table
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        // Create degree_levels table
        Schema::create('degree_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 50)->nullable();
            $table->timestamps();
        });

        // Create study_programs table
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->foreignId('id_univ')->constrained('universities')->onDelete('cascade');
            $table->foreignId('id_level')->constrained('degree_levels')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('degree_levels');
        Schema::dropIfExists('universities');
    }
};
