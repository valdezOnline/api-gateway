<?php

namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use App\Services\SisData\ActiveStudentService;
// use App\Services\SisActiveStudent\SisActiveStudentService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ActiveStudentController extends Controller
{
    use ApiResponses;
    public function activeStudent(ActiveStudentService $activeStudentService, Request $request)
    {
        return $activeStudentService->activeStudent($request->stringId);
    }

    public function activeStudentCount(ActiveStudentService $activeStudentService)
    {
        return $activeStudentService->activeStudentCount();
    }

    public function activeStudents(ActiveStudentService $activeStudentService, Request $request)
    {
        return $activeStudentService->activeStudents($request);
    }
}