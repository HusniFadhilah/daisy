<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banpt_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('status')->default('running'); // running, success, failed
            $table->unsignedInteger('checked_count')->default(0);
            $table->unsignedInteger('changed_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banpt_sync_runs');
    }
};
