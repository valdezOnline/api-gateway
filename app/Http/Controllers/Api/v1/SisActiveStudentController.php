<?php

namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use App\Services\SisActiveStudent\SisActiveStudentService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class SisActiveStudentController extends Controller
{
    use ApiResponses;
    public function index(SisActiveStudentService $sisActiveStudentService, Request $request)
    {
        return $sisActiveStudentService->sisActiveStudent($request->stringId);
    }

    public function activeStudentCount(SisActiveStudentService $sisActiveStudentService)
    {
        return $sisActiveStudentService->activeStudentCount();
    }

    public function activeStudents(SisActiveStudentService $sisActiveStudentService, Request $request)
    {
        return $sisActiveStudentService->sisActiveStudents($request);
    }
}