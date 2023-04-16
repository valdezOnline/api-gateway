<?php

namespace App\Search;

use App\Search\Sources\Calisphere;
use App\Search\Sources\CourseReserves;
use App\Search\Sources\UCLibrary;
use App\Search\Sources\WorldCat;
use App\Search\Sources\Databases;
use App\Search\Sources\LibraryWebsite;
use App\Search\Sources\LibGuides;
use Exception;

class SearchSource
{
    public function search($query, $source)
    {
        $results = '';

        try {
            $class = 'App\\Search\\Sources\\' . $source;

            if (!class_exists($class)) {
                throw new Exception('Search source does not exist');
            }

            $instance = new $class($query);
            $results = $instance->results();
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'source' => $source];
        }

        return $results;
    }
}
