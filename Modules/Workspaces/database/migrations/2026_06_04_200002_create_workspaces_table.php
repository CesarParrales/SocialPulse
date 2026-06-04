<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('industry_category')->nullable();
            $table->string('timezone')->default('UTC');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['agency_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
