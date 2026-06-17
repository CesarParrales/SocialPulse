<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('name');
            $table->string('platform')->nullable();
            $table->string('handle')->nullable();
            $table->unsignedInteger('followers_count')->nullable();
            $table->decimal('avg_reach', 16, 2)->nullable();
            $table->decimal('avg_engagement_rate', 8, 4)->nullable();
            $table->text('notes')->nullable();
            $table->string('data_source_note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['workspace_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_accounts');
    }
};
