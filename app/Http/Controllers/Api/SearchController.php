<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Facades\App\Search\SearchSource;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $source = $request->input('s');

        $results = SearchSource::search($query, $source);

        return response()->json($results);
    }
}
