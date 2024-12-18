<?php

use App\Models\Application;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('applications')->insert(
            [
                'name' => 'ul-apps',
                'description' => 'Library Application Portal',
                'apikey' => Crypt::encrypt(Str::uuid()->toString()),
                'status' => true,
                'created_by' => 'joelval',
                'created_at' => now()
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $appId = Application::select('id')->where('name', '=', 'ul-apps')->first();
        DB::table('applications')->delete([$appId]);

    }
};
