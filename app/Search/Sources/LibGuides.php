<?php

namespace App\Search\Sources;

use App\Search\SearchResult;
use App\Search\SearchSourceInterface;
use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use Illuminate\Support\Facades\Http;

class LibGuides implements SearchSourceInterface
{
  protected SearchResult $searchResults;

  protected $query;

  public function __construct($query, $limit = 5)
  {
    $this->query = $query;

    $this->searchResults = new SearchResult([
      'source' => 'LibGuides',
    ]);
  }

  public function results(): SearchResult
  {
    $url = "http://lgapi.libapps.com/1.1/guides?site_id=534&key=97115ae9b0880f4833252d4049715874&sort_by=relevance&search_terms=". urlencode($this->query);
    $json = Http::acceptJson()
      ->get($url)
      ->throw()
      ->json();

    $results = [];
    $index = 0;

    foreach ($json as $element) {
      $results[] = [
        'title' => $element['name'] ?? '',
        'url' => $element['url'] ?? '',
        'description' => $element['description'] ?? '',
        'type' => $element['type_label'] ?? '',
      ];

      if ($index++ > 2) {
        break;
      }
    }

    $this->searchResults->allResultsLink = "https://guides.lib.ucr.edu/srch.php?q=" . urlencode($this->query);
    $this->searchResults->total =  count($json);
    $this->searchResults->results = $results;

    return $this->searchResults;
  }
}
