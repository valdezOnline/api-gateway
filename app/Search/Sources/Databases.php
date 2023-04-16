<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;

class Databases implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $results = ['query' => $this->query, 'source' => 'Databases', 'results' => [], 'total' => 0];
    return $results;
  }
}
