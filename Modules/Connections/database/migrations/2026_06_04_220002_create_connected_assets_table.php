<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connected_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('platform_connections')->cascadeOnDelete();
            $table->string('asset_type');
            $table->string('platform_asset_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['asset_type', 'platform_asset_id']);
            $table->index(['connection_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connected_assets');
    }
};
