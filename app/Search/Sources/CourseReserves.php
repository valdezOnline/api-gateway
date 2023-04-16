<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;

class CourseReserves implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $results = ['query' => $this->query, 'source' => 'Course Reserves', 'results' => [], 'total' => 0];
    return $results;
  }
}
