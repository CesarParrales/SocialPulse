<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_drafts', function (Blueprint $table) {
            $table->string('media_url', 2048)->nullable()->after('caption');
            $table->string('platform_post_id')->nullable()->after('scheduled_at');
            $table->timestamp('published_to_platform_at')->nullable()->after('platform_post_id');
            $table->text('publish_error')->nullable()->after('published_to_platform_at');
        });
    }

    public function down(): void
    {
        Schema::table('content_drafts', function (Blueprint $table) {
            $table->dropColumn([
                'media_url',
                'platform_post_id',
                'published_to_platform_at',
                'publish_error',
            ]);
        });
    }
};
