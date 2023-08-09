<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Facades\App\Search\SearchSource;

class HoursController extends Controller
{
    public function index(Request $request)
    {
        $location = $request->input('location');
        $from = $request->input('from');
        $to = $request->input('from');

        // $results = call libapps api with $location, $from, $to

        // For now, mock the results to the library website done
        $output = [
            "locations" => []
          ];

        $output['locations'][] = [
            'name' => 'rivera',
            'open' => '2019-09-07T-15:50+00',
            'close' => '2019-09-07T-15:50+00'
        ];

        $output['locations'][] = [
            'name' => 'orbach',
            'open' => '2019-09-07T-15:50+00',
            'close' => '2019-09-07T-15:50+00 '
        ];

        $output['locations'][] = [
            'name' => 'scua',
            'open' => '2019-09-07T-15:50+00',
            'close' => '2019-09-07T-15:50+00 '
        ];

        return response()->json($output);
    }
}
