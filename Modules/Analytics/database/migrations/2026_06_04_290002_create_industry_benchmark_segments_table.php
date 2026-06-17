<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industry_benchmark_segments', function (Blueprint $table) {
            $table->id();
            $table->string('industry_category');
            $table->string('community_size_band');
            $table->string('region')->default('global');
            $table->unsignedInteger('sample_size')->default(0);
            $table->decimal('engagement_rate_avg', 10, 4)->nullable();
            $table->decimal('reach_avg', 12, 4)->nullable();
            $table->decimal('cpm_avg', 12, 4)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(
                ['industry_category', 'community_size_band', 'region'],
                'industry_benchmark_segments_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_benchmark_segments');
    }
};
