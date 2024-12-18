<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->insert(
            [
                'user_name' => 'svc-papi-ul',
                'password' => Hash::make('MU5T-b3-C0MPL1C@T3D'),
                'first_name' => 'Service',
                'last_name' => 'Account',
                'email' => 'joelval@ucr.edu',
                'email_verified_at' => now(),
                'hasApiAccess' => 1,
                'created_by' => 'joelval',
                'created_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $id = User::select('id')->where('user_name', '=', 'svc-papi-ul')->first();
        DB::table('users')->delete(
            [$id]
        );
    }
};
