<?php

namespace App\Search;

use Exception;

class SearchSource
{
    public function search($query, $source) : SearchResult
    {
        try {
            $class = 'App\\Search\\Sources\\' . $source;

            if (!class_exists($class)) {
                throw new Exception('Search source does not exist');
            }

            if (empty($query)) {
                throw new Exception('Search query is empty');
            }

            $instance = new $class($query);
            return $instance->results();
        } catch (Exception $e) {
            return new SearchResult([
                'error' => $e->getMessage(),
                'source' => $source,
            ]);
        }
    }
}
