<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_credential_sets', function (Blueprint $table) {
            $table->string('tiktok_client_key')->nullable()->after('google_developer_token');
            $table->text('tiktok_client_secret')->nullable()->after('tiktok_client_key');
        });
    }

    public function down(): void
    {
        Schema::table('integration_credential_sets', function (Blueprint $table) {
            $table->dropColumn(['tiktok_client_key', 'tiktok_client_secret']);
        });
    }
};
