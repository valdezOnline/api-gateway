<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;
use Exception;

class Calisphere implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_URL, "https://solr.calisphere.org/solr/query/?q=" . urlencode($this->query) . "&rows=5&wt=json&indent=true&mm=100%25&pf3=title&pf=text,title&qs=12&ps=12");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Authentication-Token: 82dbd622-32c4-4169-b25f-5435ef337a93'));
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);

    $results = [];

    if ($res === false) {
      $res = curl_error($curl);
      curl_close($curl);
      throw new Exeption("Calisphere API error: " . $res);
    } else {
      curl_close($curl);
      $json = json_decode($res, true);
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
