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

        $results = [];

        return response()->json($results);
    }
}
