<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_credential_sets', function (Blueprint $table) {
            $table->string('linkedin_client_id')->nullable()->after('tiktok_client_secret');
            $table->text('linkedin_client_secret')->nullable()->after('linkedin_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('integration_credential_sets', function (Blueprint $table) {
            $table->dropColumn(['linkedin_client_id', 'linkedin_client_secret']);
        });
    }
};
