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
        $limit = $request->input('limit', 4);

        $results = SearchSource::search($query, $source, $limit);

        if ($format == 'rss') {
            return response()->make($results->serializeRss(), 200, [
                'Content-Type' => 'application/xml',
            ]);
        }

        return response()->json($results->serializeJson());
    }
}
