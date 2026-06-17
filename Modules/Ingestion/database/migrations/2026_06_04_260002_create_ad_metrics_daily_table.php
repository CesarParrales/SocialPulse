<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->string('ad_set_id')->nullable();
            $table->string('ad_id')->nullable();
            $table->date('date');
            $table->string('placement')->default('unknown');
            $table->decimal('spend', 16, 4)->default(0);
            $table->unsignedBigInteger('reach')->nullable();
            $table->unsignedBigInteger('impressions')->nullable();
            $table->unsignedBigInteger('clicks')->nullable();
            $table->decimal('ctr', 10, 6)->nullable();
            $table->decimal('cpm', 16, 4)->nullable();
            $table->decimal('cpc', 16, 4)->nullable();
            $table->decimal('conversions', 16, 4)->nullable();
            $table->decimal('conversion_value', 16, 4)->nullable();
            $table->decimal('roas', 16, 4)->nullable();
            $table->boolean('is_preliminary')->default(false);
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['campaign_id', 'date', 'placement', 'is_preliminary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_metrics_daily');
    }
};
