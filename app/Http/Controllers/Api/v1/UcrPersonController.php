<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use app\Services\UcrPerson\DataTransferObjects;
use App\Services\UcrPerson\UcrPersonService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class UcrPersonController extends Controller
{
    use ApiResponses;
    public function index(UcrPersonService $ucrPersonService, Request $request)
    {
        return $ucrPersonService->ucrPerson($request->stringId);
    }

    public function personSearch(UcrPersonService $ucrPersonService, Request $request)
    {
        return $ucrPersonService->ucrPersonSearch($request->searchField, $request->searchTerm);
    }
}