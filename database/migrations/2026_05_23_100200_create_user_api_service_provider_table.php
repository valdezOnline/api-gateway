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
        Schema::create('user_api_service_provider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_service_provider_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique([
                'user_id',
                'api_service_provider_id',
            ], 'user_service_provider_unique');

            $table->index([
                'user_id',
                'enabled',
            ], 'user_service_provider_enabled_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_api_service_provider');
    }
};
