<?php

namespace App\Search;

interface SearchSourceInterface {
    public function results() : SearchResult;
}
