<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_credential_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('meta_app_id')->nullable();
            $table->text('meta_app_secret')->nullable();
            $table->string('meta_api_version')->nullable();
            $table->string('meta_system_user_id')->nullable();
            $table->text('meta_system_user_access_token')->nullable();
            $table->string('meta_business_id')->nullable();
            $table->string('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            $table->text('google_developer_token')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_credential_sets');
    }
};
