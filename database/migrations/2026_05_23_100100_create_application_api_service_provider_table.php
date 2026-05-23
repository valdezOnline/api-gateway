<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_api_service_provider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_service_provider_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique([
                'application_id',
                'api_service_provider_id',
            ], 'application_service_provider_unique');

            $table->index([
                'application_id',
                'enabled',
            ], 'application_service_provider_enabled_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_api_service_provider');
    }
};
