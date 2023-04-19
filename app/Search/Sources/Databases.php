<?php

namespace App\Search\Sources;

use App\Search\SearchResult;
use App\Search\SearchSourceInterface;
use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use Illuminate\Support\Facades\Http;

class Databases implements SearchSourceInterface
{
  protected SearchResult $searchResults;

  protected $query;

  public function __construct($query)
  {
    $this->query = $query;

    $this->searchResults = new SearchResult([
      'source' => 'Databases',
    ]);
  }

  public function results(): SearchResult
  {
    $url = "https://guides.lib.ucr.edu/process/az/dbsearch?action=520&first=&subject_id=&type_id=&vendor_id=&content_id=0&search=" . urlencode($this->query) . "&site_id=534&is_widget=0";
    $json = Http::acceptJson()
      ->get($url)
      ->throw()
      ->json();

    $html = HtmlDomParser::str_get_html($json['data']['html']);
    $results = [];
    $index = 0;

    foreach ($html->find('div.s-lg-az-result') as $result) {
      $link =  $result->find('a');

      $results[] = [
        'title' => $link[0]->innertext,
        'url' => $link[0]->href,
      ];

      if (++$index > 4) {
        break;
      }
    }

    $this->searchResults->allResultsLink = "https://guides.lib.ucr.edu/az.php?q=" . urlencode($this->query);
    $this->searchResults->total =  $json['data']['count'];
    $this->searchResults->results = $results;

    return $this->searchResults;
  }
}
