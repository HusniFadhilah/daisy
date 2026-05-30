<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->string('akreditasi_source')->nullable()->after('status_kedaluwarsa')
                ->comment('banpt | lamdepilar | manual');
            $table->timestamp('akreditasi_locked_at')->nullable()->after('akreditasi_source');
            $table->text('akreditasi_locked_reason')->nullable()->after('akreditasi_locked_at');
            $table->timestamp('last_banpt_checked_at')->nullable()->after('akreditasi_locked_reason');
            $table->json('last_banpt_payload')->nullable()->after('last_banpt_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('study_programs', function (Blueprint $table) {
            $table->dropColumn([
                'akreditasi_source',
                'akreditasi_locked_at',
                'akreditasi_locked_reason',
                'last_banpt_checked_at',
                'last_banpt_payload',
            ]);
        });
    }
};
