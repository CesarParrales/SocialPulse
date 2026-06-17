<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agency_invitations', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                ->nullable()
                ->after('agency_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agency_invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workspace_id');
        });
    }
};
