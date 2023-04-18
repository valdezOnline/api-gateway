<?php

namespace App\Search\Sources;

use App\Search\SearchSourceInterface;

class Calisphere implements SearchSourceInterface
{
  protected $query;

  public function __construct($query)
  {
    $this->query = $query;
  }

  public function results()
  {
    $results = [];
    $results[] = [
      'title' => 'Calisphere',
      'url' => 'https://calisphere.org/search/?q=' . urlencode($this->query),
      'description' => 'Calisphere is a gateway to digital collections from California\'s great libraries, archives, and museums. Discover over 1,000,000 images, texts, and recordings—and counting.',
      'image' => 'https://calisphere.org/images/logo.png',
      'all_results_url' => 'https://calisphere.org/search/?q=' . urlencode($this->query)
    ];
    $results[] = [
      'title' => 'Calisphere 2',
      'url' => 'https://calisphere.org/search/?q=' . urlencode($this->query),
      'description' => 'Calisphere is a gateway to digital collections from California\'s great libraries, archives, and museums. Discover over 1,000,000 images, texts, and recordings—and counting.',
      'image' => 'https://calisphere.org/images/logo.png',
      'all_results_url' => 'https://calisphere.org/search/?q=' . urlencode($this->query)
    ];
    $results[] = [
      'title' => 'Calisphere 34',
      'url' => 'https://calisphere.org/search/?q=' . urlencode($this->query),
      'description' => 'Calisphere is a gateway to digital collections from California\'s great libraries, archives, and museums. Discover over 1,000,000 images, texts, and recordings—and counting.',
      'image' => 'https://calisphere.org/images/logo.png',
      'all_results_url' => 'https://calisphere.org/search/?q=' . urlencode($this->query)
    ];
    $results[] = [
      'title' => 'Calisphere 55',
      'url' => 'https://calisphere.org/search/?q=' . urlencode($this->query),
      'description' => 'Calisphere is a gateway to digital collections from California\'s great libraries, archives, and museums. Discover over 1,000,000 images, texts, and recordings—and counting.',
      'image' => 'https://calisphere.org/images/logo.png',
      'all_results_url' => 'https://calisphere.org/search/?q=' . urlencode($this->query)
    ];

    $response = ['query' => $this->query, 'source' => 'Calisphere', 'results' => $results, 'total' => 4];
    return $response;
  }
}
