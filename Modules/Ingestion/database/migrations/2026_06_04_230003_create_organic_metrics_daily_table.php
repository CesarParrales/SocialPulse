<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organic_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('connected_assets')->cascadeOnDelete();
            $table->date('date');
            $table->string('metric_type');
            $table->decimal('value', 16, 4);
            $table->string('platform')->default('meta');
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['asset_id', 'date', 'metric_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organic_metrics_daily');
    }
};
