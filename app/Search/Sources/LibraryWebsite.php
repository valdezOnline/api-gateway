<?php

namespace App\Search\Sources;

use App\Search\SearchResult;
use App\Search\SearchSourceInterface;
use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use Illuminate\Support\Facades\Http;

class LibraryWebsite implements SearchSourceInterface
{
  protected SearchResult $searchResults;

  protected $query;

  public function __construct($query, $limit = 5)
  {
    $this->query = $query;

    $this->searchResults = new SearchResult([
      'source' => 'Library Website',
    ]);
  }

  public function results(): SearchResult
  {
    $url = "https://library.ucr.edu/search?keywords=" . urlencode($this->query);
    $html = Http::get($url)->throw();
    $html = HtmlDomParser::str_get_html($html);

    $results = [];
    $index = 0;

    foreach ($html->find('a[rel="bookmark"]') as $link) {
      $results[] = [
        'title' => $link->find('span')[0]->innertext ?? '',
        'url' => 'https://library.ucr.edu' . $link->href ?? '',
      ];

      if (++$index > 2) {
        break;
      }
    }

    $this->searchResults->allResultsLink = "https://library.ucr.edu/search?keywords=" . urlencode($this->query);
    $this->searchResults->total =  count($results);
    $this->searchResults->results = $results;

    return $this->searchResults;
  }
}
