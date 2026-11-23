<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('api_key_rotated_at')->nullable()->after('apikey');
            $table->timestamp('api_key_revoked_at')->nullable()->after('api_key_rotated_at');
            $table->timestamp('api_key_last_used_at')->nullable()->after('api_key_revoked_at');
            $table->string('api_key_last_used_ip', 45)->nullable()->after('api_key_last_used_at');
        });

        DB::table('applications')
            ->select(['id', 'apikey', 'created_at'])
            ->orderBy('id')
            ->chunkById(100, function ($applications): void {
                foreach ($applications as $application) {
                    $apiKey = (string) $application->apikey;

                    if (Str::startsWith($apiKey, ['$2y$', '$2b$', '$argon2i$', '$argon2id$'])) {
                        continue;
                    }

                    $decryptedApiKey = null;

                    try {
                        $decryptedApiKey = Crypt::decrypt($apiKey);
                    } catch (\Throwable) {
                        continue;
                    }

                    if (!is_string($decryptedApiKey) || $decryptedApiKey === '') {
                        continue;
                    }

                    DB::table('applications')
                        ->where('id', $application->id)
                        ->update([
                            'apikey' => Hash::make($decryptedApiKey),
                            'api_key_rotated_at' => $application->created_at ?? now(),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'api_key_rotated_at',
                'api_key_revoked_at',
                'api_key_last_used_at',
                'api_key_last_used_ip',
            ]);
        });
    }
};
