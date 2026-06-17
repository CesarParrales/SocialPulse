<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('connected_assets')->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('engagement_rate_avg', 8, 4)->nullable();
            $table->decimal('reach_avg', 16, 4)->nullable();
            $table->decimal('cpm_avg', 16, 4)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['workspace_id', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_snapshots');
    }
};
