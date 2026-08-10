<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\SisData\EducationalInstitutionUnitsService;

class EducationalInstitutionUnitsController extends Controller
{
    public function searchByDepartment(EducationalInstitutionUnitsService $educationalInstitutionUnitsService)
    {
        return $educationalInstitutionUnitsService->searchByType('department');
    }

    public function searchByCollege(EducationalInstitutionUnitsService $educationalInstitutionUnitsService)
    {
        return $educationalInstitutionUnitsService->searchByType('college');
    }
}
