<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;
use Illuminate\Support\Facades\Http;

class Calisphere implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
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
        $link = $doc["url_item"];
        $title = $doc["title_ss"][0];
        $results[] = [
          'title' => $title,
          'url' => $link,
        ];

        if ($index++ > 4) {
          break;
        }
      }
    } else {
      throw new Exeption("Calisphere API error: " . $res);
    }

    $response = [
      'query' => $this->query,
      'source' => 'Calisphere',
      'results' => $results,
      'total' => $json['response']['numFound'],
      'all_results_url' => "https://calisphere.org/search/?q=" . urlencode($this->query)
    ];

    return $response;
  }
}
