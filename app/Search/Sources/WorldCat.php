<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;

class WorldCat implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $results = ['query' => $this->query, 'source' => 'WorldCat', 'results' => [], 'total' => 0];
    return $results;
  }
}
