<?php

use App\Http\Controllers\Api\v1\SisStudentPersonController;
use App\Http\Controllers\Api\v1\TermStudentController;
use App\Http\Controllers\Api\v1\UcrCardDataController;
use App\Http\Controllers\Api\v1\UserAuthController;
use App\Http\Controllers\Api\v1\ApplicationsController;
use App\Http\Controllers\Api\v1\ExLibrisAlmaController;
use App\Http\Controllers\Api\v1\HrEmployeeDetailController;
use App\Http\Controllers\Api\v1\SisActiveStudentController;
use App\Http\Controllers\Api\v1\ActiveStudentController;
use App\Http\Controllers\Api\v1\ApiAuthController;
use App\Http\Controllers\Api\v1\ApiServiceProvidersController;
use App\Http\Controllers\Api\v1\ApplicationServiceProviderAccessController;
use App\Http\Controllers\Api\v1\UcrPersonController;
use App\Http\Controllers\Api\v1\FileLoadController;
use App\Http\Controllers\Api\v1\UserServiceProviderAccessController;
use App\Http\Controllers\Api\v1\UsersController;
use App\Http\Middleware\EnsureApiKeyIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//API Entry Point
Route::middleware(EnsureApiKeyIsValid::class)->group(function () {
    Route::post('/entry', [ApiAuthController::class, 'entry']);
});

// API Exit Point
Route::middleware('auth:sanctum')->post('/exit', [ApiAuthController::class, 'exit']);

// API User Login
Route::post('/login', [UserAuthController::class, 'login']);

// Applications Request
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('users', UsersController::class)->except(['store', 'update', 'delete']);
    Route::apiResource('applications', ApplicationsController::class)->except(['store', 'update', 'delete']);
    Route::post('applications', [ApplicationsController::class, 'store']);
    Route::patch('applications/{application}', [ApplicationsController::class, 'update']);
    Route::get('/service-providers', [ApiServiceProvidersController::class, 'index']);

    Route::get('/applications/{application}/service-providers', [ApplicationServiceProviderAccessController::class, 'index']);
    Route::put('/applications/{application}/service-providers/{apiServiceProvider}', [ApplicationServiceProviderAccessController::class, 'upsert']);
    Route::delete('/applications/{application}/service-providers/{apiServiceProvider}', [ApplicationServiceProviderAccessController::class, 'destroy']);

    Route::get('/users/{user}/service-providers', [UserServiceProviderAccessController::class, 'index']);
    Route::put('/users/{user}/service-providers/{apiServiceProvider}', [UserServiceProviderAccessController::class, 'upsert']);
    Route::delete('/users/{user}/service-providers/{apiServiceProvider}', [UserServiceProviderAccessController::class, 'destroy']);

    Route::post('/exit', [ApiAuthController::class, 'exit']);
});

// UCRGW API Requests
Route::middleware(['auth:sanctum', 'service.access:ucr_person'])->group(function () {
    Route::get('/ucr-person/{stringId}', [UcrPersonController::class, 'person']);
    Route::get('/ucr-person/{searchField}/{searchTerm}', [UcrPersonController::class, 'personSearch']);
});

Route::middleware(['auth:sanctum', 'service.access:sis_active_student'])->group(function () {
    Route::get('/sis/active-students/{stringId}', [SisActiveStudentController::class, 'activeStudent']);
    Route::get('/sis/active-student/{stringId}', [ActiveStudentController::class, 'activeStudent']);
    Route::get('/sis/active-students', [ActiveStudentController::class, 'activeStudents']);
    Route::get('/sis/active-students-count', [ActiveStudentController::class, 'activeStudentCount']);
    Route::get('/sis/term-student', [TermStudentController::class, 'searchCriteria']);
    Route::get('/sis/student-person/{stringId}', [SisStudentPersonController::class, 'searchStudentPerson']);
});

Route::middleware(['auth:sanctum', 'service.access:hr_employee_detail'])->group(function () {
    Route::get('/hr/employee/{netId}', [HrEmployeeDetailController::class, 'hrEmployee']);
    Route::get('/hr/employee-details/{netIds}', [HrEmployeeDetailController::class, 'hrEmployeeDetails']);
    Route::get('/hr/employee-job/{netId}', [HrEmployeeDetailController::class, 'hrEmployeeJob']);
});

Route::middleware(['auth:sanctum', 'service.access:file_load'])->group(function () {
    Route::post('/file-upload', [FileLoadController::class, 'upload']);
    Route::get('/file-download/{fileName}', [FileLoadController::class, 'download']);
});

// ExLibris Alma API Requests
Route::middleware(['auth:sanctum', 'service.access:exlibris_alma'])->group(function () {
    Route::post('/alma-user/patron/guestLogin/{stringCreds}', [ExLibrisAlmaController::class, 'guestLogin']);
    Route::post('/alma-user/patron/guest-login/{stringCreds}', [ExLibrisAlmaController::class, 'guestLogin']);
    Route::get('/alma-user/patron/{stringId}', [ExLibrisAlmaController::class, 'patron']);
    Route::get('/alma-user/patron-search', [ExLibrisAlmaController::class, 'search']);
    Route::get('/alma-user/fees/{stringId}', [ExLibrisAlmaController::class, 'fees']);
    Route::get('/alma-user/fees/{stringId}/{feeId}');
    Route::get('/alma-user/fees/{stringId}/{status}');
    Route::get('/alma-user/loans/{stringId}', [ExLibrisAlmaController::class, 'loans']);
    Route::get('/alma-user/loans/{stringId}/{loanId}');
    Route::post('/alma-user/loans/renew/{stringId}/{loanId}');
});

// UCR Card Data API Requests
Route::middleware(['auth:sanctum', 'service.access:ucr_card_data'])->group(function () {
    // Main list endpoint with query parameters
    Route::get('/ucr-card-data/list', [UcrCardDataController::class, 'list']);

    // Multiple parameter search endpoint
    Route::post('/ucr-card-data/search-multiple', [UcrCardDataController::class, 'searchMultiple']);

    // Specific search endpoints (alternative approach)
    Route::get('/ucr-card-data/net-id/{netId}', [UcrCardDataController::class, 'searchByNetId']);
    Route::get('/ucr-card-data/ssn/{ssn}', [UcrCardDataController::class, 'searchBySsn']);
    Route::get('/ucr-card-data/student-id/{studentId}', [UcrCardDataController::class, 'searchByStudentId']);
    Route::get('/ucr-card-data/iso/{iso}', [UcrCardDataController::class, 'searchByIso']);
    Route::get('/ucr-card-data/library-number/{libraryNumber}', [UcrCardDataController::class, 'searchByLibraryNumber']);
    Route::get('/ucr-card-data/barcode/{barcode}', [UcrCardDataController::class, 'searchByLibraryNumber']);
    Route::post('/ucr-card-data/date-range', [UcrCardDataController::class, 'searchByDateRange']);
});
