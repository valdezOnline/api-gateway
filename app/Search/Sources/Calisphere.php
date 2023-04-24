<?php

namespace App\Search\Sources;

use App\Search\SearchResult;
use App\Search\SearchSourceInterface;
use Illuminate\Support\Facades\Http;

class Calisphere implements SearchSourceInterface
{
  protected SearchResult $results;

  protected string $query;

  public function __construct($query)
  {
    $this->query = $query;

    $this->searchResults = new SearchResult([
      'source' => 'Calisphere',
    ]);
  }

  public function results() : SearchResult
  {
    $res = Http::acceptJson()
      ->withHeaders([
        'X-Authentication-Token' => '82dbd622-32c4-4169-b25f-5435ef337a93',
      ])->get("https://solr.calisphere.org/solr/query/?q=" . urlencode($this->query) . "&rows=5&wt=json&indent=true&mm=100%25&pf3=title&pf=text,title&qs=12&ps=12");

    if ($res->successful()) {
      $json = json_decode($res->getBody(), true);

      $results = [];
      $index = 0;

      foreach ($json["response"]["docs"] as $doc) {
        $link = $doc["url_item"] ?? '';
        $title = $doc["title_ss"][0] ?? '';
        $type = $doc["type_ss"][0] ?? '';
        $collection_name = $doc["collection_name"][0] ?? '';
        $collection_url = $doc["collection_url"][0] ?? '';

        $results[] = [
          'title' => $title,
          'url' => $link,
          'type' => $type,
          'collection_name' => $collection_name,
          'collection_url' => $collection_url,
        ];

        if (++$index > 2) {
          break;
        }
      }
    } else {
      throw new Exeption("Calisphere API error: " . $res);
    }

    $this->searchResults->results = $results;
    $this->searchResults->total = $json['response']['numFound'];
    $this->searchResults->allResultsLink = "https://calisphere.org/search/?q=" . urlencode($this->query);

    return $this->searchResults;
  }
}
