<?php

namespace App\Search;

use Exception;
use Illuminate\Support\Facades\Cache;

class SearchSource
{
    public function search($query, $source, $limit = 4) : SearchResult
    {
        try {
            $class = 'App\\Search\\Sources\\' . $source;

            if (!class_exists($class)) {
                throw new Exception('Search source does not exist');
            }

            if (empty($query)) {
                throw new Exception('Search query is empty');
            }

            $instance = new $class($query, $limit);

            $results = Cache::remember('search_' . $source . '_' . $query, 60, function () use ($instance) {
                return $instance->results();
            });

            return $results;

        } catch (Exception $e) {
            return new SearchResult([
                'error' => $e->getMessage(),
                'source' => $source,
            ]);
        }
    }
}
