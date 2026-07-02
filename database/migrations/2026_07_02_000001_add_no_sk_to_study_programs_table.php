<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('study_programs', 'no_sk')) {
            Schema::table('study_programs', function (Blueprint $table) {
                $table->string('no_sk')->nullable()->after('peringkat_akreditasi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('study_programs', 'no_sk')) {
            Schema::table('study_programs', function (Blueprint $table) {
                $table->dropColumn('no_sk');
            });
        }
    }
};
