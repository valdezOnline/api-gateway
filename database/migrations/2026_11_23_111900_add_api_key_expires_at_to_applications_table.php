<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('api_key_expires_at')->nullable()->after('api_key_revoked_at');
        });

        DB::table('applications')
            ->whereNull('api_key_expires_at')
            ->update([
                'api_key_expires_at' => now()->addYear(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('api_key_expires_at');
        });
    }
};
