<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;
use Illuminate\Support\Facades\Http;

class CourseReserves implements SearchSourceInterface
{
  private $apiKey = "l7xx51dbfa2e1bca487188b187aae5807b81";

  private $apiUrl = "https://api-na.hosted.exlibrisgroup.com/primo/v1/search";

  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $url = "$this->apiUrl?vid=01CDL_RIV_INST:UCR&scope=CourseReserves&limit=5&q=any,contains," . urlencode($this->query) . "&apikey=$this->apiKey";

    $json = Http::acceptJson()
      ->withHeaders([
        'X-Authentication-Token' => '82dbd622-32c4-4169-b25f-5435ef337a93',
      ])->get($url)
      ->throw()
      ->json();

    $results = [];
    $index = 0;

    foreach ($json['docs'] as $element) {
      $docId = $element['pnx']['control']['recordid'][0];
      $title = $element['pnx']['display']['title'][0];
      $link = 'https://search.library.ucr.edu/discovery/fulldisplay?docid=' . $docId . '&context=PC&vid=01CDL_RIV_INST:UCR&search_scope=CourseReserves&lang=en';

      $results[] = [
        'title' => $title,
        'url' => $link,
      ];

      if ($index++ > 4) {
        break;
      }
    }

    $response = [
      'query' => $this->query,
      'source' => 'Course Reserves',
      'results' => $results,
      'total' => $json['info']['total'],
      'all_results_url' => "https://search.library.ucr.edu/discovery/search?query=any,contains," . urlencode($this->query) . "&tab=CourseReserves&search_scope=CourseReserves&vid=01CDL_RIV_INST:UCR&offset=0",
    ];

    return $response;
  }
}
