<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ExLibrisAlma\ExLibrisAlmaFeeDataService;
use App\Services\ExLibrisAlma\ExLibrisAlmaPatronDataService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ExLibrisAlmaController extends Controller
{
    use ApiResponses;
    public function fees(ExLibrisAlmaFeeDataService $exLibrisAlmaFeeDataService, Request $request)
    {
        // return dd('inside controller');
        return $exLibrisAlmaFeeDataService->Fees($request->stringId);
    }

    public function patron(ExLibrisAlmaPatronDataService $exLibrisAlmaPatronDataService, Request $request)
    {
        // return dd('inside controller');
        return $exLibrisAlmaPatronDataService->Patron($request->stringId);
    }

    public function search(ExLibrisAlmaPatronDataService $exLibrisAlmaPatronDataService, Request $request)
    {
        // return $this->ok('inside controller - stringSearch value = ' . $request->stringSearch);
        return $exLibrisAlmaPatronDataService->Search($request->stringSearch);
    }

}
