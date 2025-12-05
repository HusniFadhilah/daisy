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
        Schema::create('asesmens', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');

            // Informasi Perguruan Tinggi
            $table->string('perguruan_tinggi')->nullable();
            $table->string('bentuk_pt', 100)->nullable();
            $table->string('kode_panel', 50)->nullable();

            // Periode Assessment
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            // Status
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])
                ->default('draft');

            // Index
            $table->index('status');
            $table->index('kode_panel');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmens');
    }
};
