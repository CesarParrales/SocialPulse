<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organic_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('connected_assets')->cascadeOnDelete();
            $table->string('platform_post_id');
            $table->string('post_type')->default('feed');
            $table->timestamp('published_at')->nullable();
            $table->text('content_preview')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->json('raw_metrics')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->unique(['asset_id', 'platform_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organic_posts');
    }
};
