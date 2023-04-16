<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;

class Calisphere implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $results = ['query' => $this->query, 'source' => 'Calisphere', 'results' => [], 'total' => 0];
    return $results;
  }
}
