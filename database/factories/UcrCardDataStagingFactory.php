<?php

namespace Database\Factories;

use App\Models\UcrCardDataStaging;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UcrCardDataStaging>
 */
class UcrCardDataStagingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UcrCardDataStaging::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $netId = $this->faker->unique()->userName;
        $ssn = $this->faker->unique()->numerify('#########');
        $studentId = $ssn; // Often the same as SSN

        return [
            'net_id' => $netId,
            'ssn' => $ssn,
            'student_id' => $studentId,
            'iso' => $this->faker->numerify('################'),
            'lib_num' => '2' . $this->faker->numerify('################'),
            'status1' => $this->faker->randomElement([
                'Active Student',
                'Active Faculty',
                'Inactive Student',
                'Graduate Student',
                'International Student',
                'Staff Member'
            ]),
            'class' => $this->faker->randomElement(['U', 'G', 'F', 'S']),
            'yr_in_school' => $this->faker->randomElement(['U1', 'U2', 'U3', 'U4', 'G1', 'G2', 'G3', '']),
            'stud_fac' => $this->faker->randomElement(['S', 'F']),
            'prox_int' => $this->faker->numerify('#####'),
            'prox_ext' => $this->faker->numerify('#####'),
            'prox_status' => $this->faker->randomElement(['A', 'I']),
            'issued' => $this->faker->optional()->dateTimeBetween('-2 years', 'now'),
            'edit_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'photo_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now'),
            'imported' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'load_status' => $this->faker->randomElement(['created', 'updated', 'full-load']),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Indicate that the record is newly created.
     */
    public function created(): static
    {
        return $this->state(fn(array $attributes) => [
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Indicate that the record is updated.
     */
    public function updated(): static
    {
        return $this->state(fn(array $attributes) => [
            'load_status' => 'updated',
            'updated_at' => now()
        ]);
    }

    /**
     * Indicate that the record is from a full load.
     */
    public function fullLoad(): static
    {
        return $this->state(fn(array $attributes) => [
            'load_status' => 'full-load'
        ]);
    }

    /**
     * Create a student record.
     */
    public function student(): static
    {
        return $this->state(fn(array $attributes) => [
            'status1' => $this->faker->randomElement(['Active Student', 'Graduate Student', 'International Student']),
            'class' => $this->faker->randomElement(['U', 'G']),
            'yr_in_school' => $this->faker->randomElement(['U1', 'U2', 'U3', 'U4', 'G1', 'G2', 'G3']),
            'stud_fac' => 'S'
        ]);
    }

    /**
     * Create a faculty record.
     */
    public function faculty(): static
    {
        return $this->state(fn(array $attributes) => [
            'status1' => 'Active Faculty',
            'class' => 'F',
            'yr_in_school' => '', // Faculty don't have year in school
            'stud_fac' => 'F'
        ]);
    }

    /**
     * Create a staff record.
     */
    public function staff(): static
    {
        return $this->state(fn(array $attributes) => [
            'status1' => 'Staff Member',
            'class' => 'S',
            'yr_in_school' => '', // Staff don't have year in school
            'stud_fac' => 'F'
        ]);
    }

    /**
     * Create record with older timestamp.
     */
    public function older(): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30)
        ]);
    }

    /**
     * Create record with newer timestamp.
     */
    public function newer(): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => now()->subHour(),
            'updated_at' => now()
        ]);
    }

    /**
     * Create record with specific net_id.
     */
    public function withNetId(string $netId): static
    {
        return $this->state(fn(array $attributes) => [
            'net_id' => $netId
        ]);
    }

    /**
     * Create record with specific SSN and student ID.
     */
    public function withIdentifiers(string $ssn, string $studentId = null): static
    {
        return $this->state(fn(array $attributes) => [
            'ssn' => $ssn,
            'student_id' => $studentId ?? $ssn
        ]);
    }
}
