<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CardTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        \App\Models\UcrCardDataActual::factory()->create(
            [
                'net_id' => 'susieb',
                'ssn' => '865432100',
                'student_id' => '865432100',
                'iso' => '6012730003288326',
                'lib_num' => '21210032883266',
                'status1' => 'Student',
                'class' => 'U',
                'yr_in_school' => 'FR',
                'stud_fac' => 'S',
                'prox_int' => '',
                'prox_ext' => '',
                'prox_status' => '',
                'issued' => '2025-07-01',
                'edit_date' => '2025-07-01',
                'photo_date' => '2025-07-01',
                'imported' => '2025-07-01'
            ]
        );
    }
}
