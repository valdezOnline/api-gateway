<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Support\ApiServiceCatalog;
use App\Traits\ApiResponses;

class ApiServiceCatalogController extends Controller
{
    use ApiResponses;

    public function index(ApiServiceCatalog $catalog)
    {
        return $this->ok('Service catalog loaded.', $catalog->all());
    }
}
