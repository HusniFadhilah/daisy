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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('institution')->nullable()->after('address');
            $table->foreignId('id_university')->nullable()->after('institution')->constrained('universities')->onDelete('set null');
            $table->foreignId('id_study_program')->nullable()->after('id_university')->constrained('study_programs')->onDelete('set null');
            $table->string('position')->nullable()->after('id_study_program'); // Untuk jabatan seperti Kaprodi
            $table->string('avatar')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_university']);
            $table->dropForeign(['id_study_program']);
            $table->dropColumn([
                'phone',
                'address',
                'institution',
                'id_university',
                'id_study_program',
                'position',
                'avatar',
            ]);
        });
    }
};
