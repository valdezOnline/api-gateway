<?php

namespace Database\Factories;

use App\Models\IdmsCardData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdmsCardData>
 */
class IdmsCardDataFactory extends Factory
{
    protected $model = IdmsCardData::class;

    public function definition(): array
    {
        $netId = $this->faker->unique()->userName;
        $ucrId = $this->faker->unique()->numerify('#########');
        $studentId = $ucrId;

        return [
            'net_id' => $netId,
            'ucr_id' => $ucrId,
            'student_id' => $studentId,
            'iso' => $this->faker->numerify('################'),
            'library_card_number' => '2' . $this->faker->numerify('################'),
            'status1' => $this->faker->randomElement([
                'Active Student',
                'Active Faculty',
                'Inactive Student',
                'Graduate Student',
                'International Student',
                'Staff Member'
            ]),
            'class' => $this->faker->randomElement(['U', 'G', 'F', 'S']),
            'year_in_school' => $this->faker->randomElement(['U1', 'U2', 'U3', 'U4', 'G1', 'G2', 'G3', '']),
            'student_fac' => $this->faker->randomElement(['S', 'F']),
            'prox_int' => $this->faker->numerify('#####'),
            'issue_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now')?->format('Y-m-d'),
            'edit_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now')?->format('Y-m-d'),
            'photo_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now')?->format('Y-m-d'),
            'imported_date' => $this->faker->optional()->dateTimeBetween('-1 month', 'now')?->format('Y-m-d'),
            'load_status' => $this->faker->randomElement(['active', 'inactive', 'current', 'old']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
