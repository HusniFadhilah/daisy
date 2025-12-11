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
        Schema::create('penilaian_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asesmen')->constrained('asesmens', 'id')->onDelete('cascade');
            $table->foreignId('id_asesor')->constrained('users', 'id')->onDelete('cascade');
            $table->string('filename');
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->integer('total_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['id_asesmen', 'id_asesor', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_import_logs');
    }
};
