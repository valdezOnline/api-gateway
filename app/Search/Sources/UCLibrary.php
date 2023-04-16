<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;

class UCLibrary implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $results = ['query' => $this->query, 'source' => 'UC Library Search', 'results' => [], 'total' => 0];
    return $results;
  }
}
