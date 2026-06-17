<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_credential_sets', function (Blueprint $table) {
            $table->string('youtube_client_id')->nullable()->after('linkedin_client_secret');
            $table->text('youtube_client_secret')->nullable()->after('youtube_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('integration_credential_sets', function (Blueprint $table) {
            $table->dropColumn(['youtube_client_id', 'youtube_client_secret']);
        });
    }
};
