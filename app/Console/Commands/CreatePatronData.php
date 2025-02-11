<?php

namespace App\Console\Commands;

use Date;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class CreatePatronData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-patron-data {source=ucr_card_data} {parameters?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create patron data based on Source and Search parameters (key-pair).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }

    private function CreateFromCardData(Date $dateStart, Date $dateEnd)
    {

        // Get the data
        $cardData = DB::table('UCR_CARD_DATA')
            ->whereBetween('imported', [$dateStart, $dateEnd])
            ->select('net_id', 'ssn', 'student_id', 'iso', 'lib_num', 'issued', 'edit_date', 'photo_date', 'imported')
            ->get();

        // Loop through the data
        foreach ($cardData as $card) {
            # Begin building the patron data...
            # recordType = PUBLIC, accountType = EXTERNAL
            if ($card['lib_num'] != '') {
                # Check if starting number is student
                if (Str::startsWith($card['student_id'], '86')) {
                    # Get the student_id and get student info from SIS API
                    # (net_id, name, address, phone, email, grp=ucru or ucrg)
                    $stringId = $card['student_id'];

                    // URI = /sis/active-students/{stringId}
                    $sisData = self::invokeApi("/sis/active-students/$stringId");
                    // Check status code
                    switch ($sisData->status()) {
                        case 404:
                            // continue;
                            break;
                        default:
                            self::MakePatronRecord($sisData, $card);
                            break;
                    }
                }
                # Check if starting number is employee
                if (Str::startsWith($card['student_id'], '10')) {
                    # Get the employee_id and get employee info from HR API
                    # (net_id, name, address, phone, email, grp=ucrs, ucra or ucrs)
                    $stringId = $card['net_id'];
                    // URI = /hr/employee/{stringId}
                    $hrData = self::invokeApi("/hr/employee/$stringId");
                    switch ($hrData->status()) {
                        case 404:
                            // continue;
                            break;
                        default:
                            self::MakePatronRecord($hrData, $card);
                            break;
                    }
                }
            }
        }
    }

    private function invokeApi(string $uri)
    {
        $requestInvoker = Request::create($uri, 'GET');
        $requestInvoker->headers->set('Accept', 'appliction/json');
        $response = app()->handle($requestInvoker);
        $data = json_decode($response->getContent());
        return $data;
    }

    private function MakePatronRecord($apiData, $cardData)
    {
        //TODO: Create/Make Patron Data
        // 1 Check the type of data
        // 2 Check the data - if no data Create the Patron Data
        if (Str::startsWith($cardData['student_id'], '86')) {
            // Student
            $patronData = [
                'identifier_netid' => $apiData->netId,
                'identifier_barcode' => $apiData->lib_num,
                'identifier_netid_email' => "{$apiData->netId}@ucr.edu",
                'primary_id' => $apiData->studentId,
                'first_name' => $apiData->firstName,
                'middle_name' => $apiData->middleName,
                'last_name' => $apiData->lastName,
                'address_line1' => $apiData->address,
                'address_line2' => $apiData->address,
                'address_state_province' => $apiData->address,
                'address_postal_code' => $apiData->address,
                'address_country' => $apiData->address,
                'phone' => $apiData->phoneNumber,
                'email' => $apiData->email,
                'grp' => Str::contains($cardData['status1'], 'Graduate') ? 'ucrg' : 'ucru',
            ];
        }
        if (Str::startsWith($cardData['student_id'], '10')) {
            // Employee
            $patronData = [
                'identifier_netid' => $apiData->netId,
                'identifier_barcode' => $apiData->lib_num,
                'identifier_netid_email' => "{$apiData->netId}@ucr.edu",
            ];
        }
        // switch ($cardData->) {
        //     case 'student':
        //         # code...
        //         $patronData = [
        //             'identifier_netid' => $apiData->netId,
        //             'identifier_barcode' => $apiData->lib_num,
        //             'identifier_netid_email' => "{$apiData->netId}@ucr.edu",
        //             'primary_id' => $apiData->studentId,
        //             'first_name' => $apiData->firstName,
        //             'middle_name' => $apiData->middleName,
        //             'last_name' => $apiData->lastName,
        //             'address_line1' => $apiData->address,
        //             'address_line2' => $apiData->address,
        //             'address_state_province' => $apiData->address,
        //             'address_postal_code' => $apiData->address,
        //             'address_country' => $apiData->address,
        //             'phone' => $apiData->phoneNumber,
        //             'email' => $apiData->email,
        //             'grp' => $data->affiliationType ?? 'ucru',
        //         ];
        //         break;
        //     case 'employee':
        //         # code...
        //         break;
        //     default:
        //         # code...
        //         break;
        // }

    }
}
