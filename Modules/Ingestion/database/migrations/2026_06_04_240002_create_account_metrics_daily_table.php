<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('connected_assets')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('followers')->nullable();
            $table->unsignedInteger('reach')->nullable();
            $table->unsignedInteger('impressions')->nullable();
            $table->unsignedInteger('profile_views')->nullable();
            $table->unsignedInteger('posts_published')->nullable();
            $table->decimal('engagement_rate', 8, 4)->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['asset_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_metrics_daily');
    }
};
