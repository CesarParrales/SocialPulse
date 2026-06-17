<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('public_dashboard_token', 64)->nullable()->unique()->after('settings');
            $table->timestamp('public_dashboard_enabled_at')->nullable()->after('public_dashboard_token');
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn(['public_dashboard_token', 'public_dashboard_enabled_at']);
        });
    }
};
