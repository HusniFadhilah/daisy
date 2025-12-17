<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asesmens', function (Blueprint $table) {
            // Add foreign key to pengajuan_akreditasi
            $table->foreignId('id_pengajuan')
                ->nullable()
                ->after('id')
                ->constrained('pengajuan_akreditasi')
                ->onDelete('set null')
                ->comment('Relasi ke pengajuan akreditasi (jika ada)');

            // Add fields from StudyProgram
            $table->foreignId('id_study_program')
                ->nullable()
                ->after('id_pengajuan')
                ->constrained('study_programs')
                ->onDelete('set null')
                ->comment('Program studi yang diases');
        });
    }

    public function down()
    {
        Schema::table('asesmens', function (Blueprint $table) {
            $table->dropForeign(['id_pengajuan']);
            $table->dropColumn('id_pengajuan');

            $table->dropForeign(['id_study_program']);
            $table->dropColumn('id_study_program');
        });
    }
};
