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

class SearchResult
{
  public $results = [];

  public $total;

  public $source;

  public $allResultsLink;

  public $error;

  public function __construct($options = [])
  {
    $this->results = $options['results'] ?? [];
    $this->total = $options['total'] ?? 0;
    $this->source = $options['source'] ?? '';
    $this->allResultsLink = $options['allResultsLink'] ?? '';
    $this->error = $options['error'] ?? '';
  }

  public function serializeJson()
  {
    return [
      'results' => $this->results,
      'total' => $this->total,
      'source' => $this->source,
      'allResultsLink' => $this->allResultsLink,
      'error' => $this->error,
    ];
  }

  public function serializeRss() {
      $output = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><rss version=\"2.0\"><channel><title></title><link/><description></description>";

      foreach ($this->results as $result) {
        $output .= "
<item>
<title>{$result['title']}</title>
<link>{$result['url']}</link>
<description>{$result['contents']}</description>
</item>";
      }

      $output .= "</channel></rss>";

      return $output;
  }
}
