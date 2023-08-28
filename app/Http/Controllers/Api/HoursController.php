<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Facades\App\Search\SearchSource;

class HoursController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start');

        // For now, mock the results to the library website done
        $output = [
            "location" => []
          ];

        $output['location'][] = [
            'name' => 'rivera',

        ];

        $output['locations'][] = [
            'name' => 'orbach',
        ];

        $output['locations'][] = [
            'name' => 'scua',
        ];

        return response()->json($output);
    }
}
