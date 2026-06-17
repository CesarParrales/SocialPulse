<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('connected_assets')->cascadeOnDelete();
            $table->string('story_id');
            $table->timestamp('captured_at');
            $table->unsignedInteger('reach')->nullable();
            $table->unsignedInteger('impressions')->nullable();
            $table->unsignedInteger('taps_forward')->nullable();
            $table->unsignedInteger('taps_back')->nullable();
            $table->unsignedInteger('exits')->nullable();
            $table->unsignedInteger('replies')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamps();

            $table->index(['asset_id', 'story_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories_snapshots');
    }
};
