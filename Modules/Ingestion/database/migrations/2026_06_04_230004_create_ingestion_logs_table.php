<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingestion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('connected_assets')->cascadeOnDelete();
            $table->string('job_type');
            $table->string('status');
            $table->unsignedInteger('records_ingested')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at');
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamps();

            $table->index(['asset_id', 'job_type', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingestion_logs');
    }
};
