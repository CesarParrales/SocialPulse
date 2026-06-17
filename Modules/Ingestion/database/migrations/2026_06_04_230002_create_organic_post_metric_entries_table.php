<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organic_post_metric_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organic_post_id')->constrained()->cascadeOnDelete();
            $table->timestamp('captured_at');
            $table->json('metrics');
            $table->timestamps();

            $table->index(['organic_post_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organic_post_metric_entries');
    }
};
