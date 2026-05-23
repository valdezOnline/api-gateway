<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Application::factory(4)->create();

        $this->call(ApiServiceProviderSeeder::class);

        \App\Models\User::factory()->create([
            'user_name' => 'joelval',
            'first_name' => 'Joel',
            'last_name' => 'Valdez',
            'email' => 'joel.valdez@ucr.edu',
            'email_verified_at' => now(),
            'password' => Hash::make('P@55w0rd'),
            'hasApiAccess' => 1,
            'remember_token' => Str::random(64),
        ]);
    }
}
