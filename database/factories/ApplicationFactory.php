<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $apikey;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['alma', 'libcal', 'libguide', 'primo', 'its-idms']),
            'description' => fake()->randomElement(['Application for me', 'Application for them', 'Some Appliction', 'Our Application']),
            'apikey' => Crypt::encrypt('my-0TH3R-super-53CR3T-key'),
            'status' => fake()->randomElement([0, 1]),
            'created_by' => fake()->userName(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}