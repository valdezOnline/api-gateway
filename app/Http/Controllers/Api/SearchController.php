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
        $format = $request->input('format');

        $results = SearchSource::search($query, $source);

        if ($format == 'rss') {
            return response()->json($results->serializeRss());
        }

        return response()->json($results->serializeJson());
    }
}
