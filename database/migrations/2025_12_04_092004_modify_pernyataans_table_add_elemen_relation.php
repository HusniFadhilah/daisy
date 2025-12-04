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
        Schema::table('pernyataans', function (Blueprint $table) {
            $table->foreignId('id_elemen')->after('id')->constrained('elemen_standar', 'id_elemen')->onDelete('cascade');
            $table->text('pernyataan')->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pernyataans', function (Blueprint $table) {
            $table->dropForeign(['id_elemen']);
            $table->dropColumn(['id_elemen', 'pernyataan']);
        });
    }
};
