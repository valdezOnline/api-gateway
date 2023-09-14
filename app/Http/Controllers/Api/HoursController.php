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

        // For now, mock the response to the library website
        $output = ["exceptions" => []];

        $output["exceptions"]["rivera"] = [];

        $output["exceptions"]["orbach"] = [];

        $output["exceptions"]["scua"] = [];

        $output["exceptions"]["bearhelp"] = [];

        return response()->json($output);
    }
}
