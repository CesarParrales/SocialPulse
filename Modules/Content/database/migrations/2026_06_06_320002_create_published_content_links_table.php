<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('published_content_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organic_post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_id')->constrained('connected_assets')->cascadeOnDelete();
            $table->string('platform_post_id');
            $table->string('platform_permalink', 2048)->nullable();
            $table->timestamp('published_at');
            $table->timestamps();

            $table->unique(['asset_id', 'platform_post_id']);
            $table->index('organic_post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('published_content_links');
    }
};
