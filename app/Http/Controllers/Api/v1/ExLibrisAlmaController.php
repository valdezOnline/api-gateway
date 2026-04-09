<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ExLibrisAlma\ExLibrisAlmaFeeDataService;
use App\Services\ExLibrisAlma\ExLibrisAlmaLoanDataService;
use App\Services\ExLibrisAlma\ExLibrisAlmaPatronDataService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ExLibrisAlmaController extends Controller
{
    use ApiResponses;

    public function fees(ExLibrisAlmaFeeDataService $exLibrisAlmaFeeDataService, Request $request)
    {
        return $exLibrisAlmaFeeDataService->Fees($request->stringId);
    }

    public function patron(ExLibrisAlmaPatronDataService $exLibrisAlmaPatronDataService, Request $request)
    {
        return $exLibrisAlmaPatronDataService->Patron($request->stringId);
    }

    public function guestLogin(ExLibrisAlmaPatronDataService $exLibrisAlmaPatronDataService, Request $request)
    {
        return $exLibrisAlmaPatronDataService->GuestLogin($request->stringCreds);
    }

    public function search(ExLibrisAlmaPatronDataService $exLibrisAlmaPatronDataService, Request $request)
    {
        return $exLibrisAlmaPatronDataService->Search();
    }

    // public function renewLoan(ExLibrisAlmaLoanDataService $exLibrisAlmaLoanDataService, Request $request)
    // {
    //     return $exLibrisAlmaPatronDataService->RenewLoan($request->stringId, $request->loanId);
    // }

    // public function renewAllLoans(ExLibrisAlmaLoanDataService $exLibrisAlmaPatronDataService, Request $request)
    // {
    //     return $exLibrisAlmaPatronDataService->RenewAllLoans($request->stringId);
    // }

    public function loans(ExLibrisAlmaLoanDataService $exLibrisAlmaLoanDataService, Request $request)
    {
        return $exLibrisAlmaLoanDataService->Loans($request->stringId, $request->limit ?? '25', $request->offset ?? '0');
    }
}
