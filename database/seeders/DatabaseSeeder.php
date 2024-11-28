<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Application::factory(4)->create();
        \App\Models\Application::factory()->create([
            'name' => 'ul-apps',
            'user_id' => 1,
            'apikey' => Crypt::encrypt('my-super-secret-key'),
            'status' => fake()->randomElement([0, 1]),
            'remember_token' => Str::random(10),
        ]);

        \App\Models\User::factory()->create([
            'netid' => 'joelval',
            'first_name' => 'Joel',
            'last_name' => 'Valdez',
            'email' => 'joelval@ucr.edu',
            'email_verified_at' => now(),
            'password' => Hash::make('P@55w0rd'),
            'hasApiAccess' => 1,
            'remember_token' => Str::random(64),
        ]);
    }
}