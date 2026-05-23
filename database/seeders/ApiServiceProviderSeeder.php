<?php

namespace Database\Seeders;

use App\Models\ApiServiceProvider;
use Illuminate\Database\Seeder;

class ApiServiceProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'service_key' => 'ucr_person',
                'display_name' => 'UCR Person',
                'description' => 'UCR person identity and search endpoints',
                'enabled' => true,
            ],
            [
                'service_key' => 'sis_active_student',
                'display_name' => 'SIS Active Student',
                'description' => 'SIS active student and student-person endpoints',
                'enabled' => true,
            ],
            [
                'service_key' => 'hr_employee_detail',
                'display_name' => 'HR Employee Detail',
                'description' => 'HR employee lookup and job detail endpoints',
                'enabled' => true,
            ],
            [
                'service_key' => 'file_load',
                'display_name' => 'File Load',
                'description' => 'File upload and download endpoints',
                'enabled' => true,
            ],
            [
                'service_key' => 'exlibris_alma',
                'display_name' => 'ExLibris Alma',
                'description' => 'ExLibris Alma patron, fees, and loans endpoints',
                'enabled' => true,
            ],
            [
                'service_key' => 'ucr_card_data',
                'display_name' => 'UCR Card Data',
                'description' => 'UCR card data list and search endpoints',
                'enabled' => true,
            ],
        ];

        foreach ($providers as $provider) {
            ApiServiceProvider::updateOrCreate(
                ['service_key' => $provider['service_key']],
                $provider
            );
        }
    }
}
