<?php

use App\Http\Controllers\Api\v1\UcrCardDataController;
use App\Http\Controllers\Api\v1\UserAuthController;
use App\Http\Controllers\Api\v1\ApplicationsController;
use App\Http\Controllers\Api\v1\ExLibrisAlmaController;
use App\Http\Controllers\Api\v1\HrEmployeeDetailController;
use App\Http\Controllers\Api\v1\SisActiveStudentController;
use App\Http\Controllers\Api\v1\ApiAuthController;
use App\Http\Controllers\Api\v1\UcrPersonController;
use App\Http\Controllers\Api\v1\FileLoadController;
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
    Route::post('/exit', [ApiAuthController::class, 'exit']);
});

// UCRGW API Requests
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ucr-person/{stringId}', [UcrPersonController::class, 'person']);
    Route::get('/ucr-person/{searchField}/{searchTerm}', [UcrPersonController::class, 'personSearch']);
    Route::get('/sis/active-students/{stringId}', [SisActiveStudentController::class, 'activeStudent']);
    Route::get('/sis/active-students', [SisActiveStudentController::class, 'activeStudents']);
    Route::get('/sis/active-students-count', [SisActiveStudentController::class, 'activeStudentCount']);
    Route::get('/hr/employee/{netId}', [HrEmployeeDetailController::class, 'hrEmployee']);
    Route::get('/hr/employee-details/{netIds}', [HrEmployeeDetailController::class, 'hrEmployeeDetails']);
    Route::get('/hr/employee-job/{netId}', [HrEmployeeDetailController::class, 'hrEmployeeJob']);
    Route::post('/file-upload', [FileLoadController::class, 'upload']);
    Route::get('/file-download/{fileName}', [FileLoadController::class, 'download']);
});

// ExLibris Alma API Requests
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/alma-user/patron/guestLogin/{stringCreds}', [ExLibrisAlmaController::class, 'guestLogin']);
    Route::get('/alma-user/patron/{stringId}', [ExLibrisAlmaController::class, 'patron']);
    Route::get('/alma-user/patron-search', [ExLibrisAlmaController::class, 'search']);
    Route::get('/alma-user/fees/{stringId}', [ExLibrisAlmaController::class, 'fees']);
    Route::get('/alma-user/fees/{stringId}/{feeId}');
    Route::get('/alma-user/fees/{stringId}/{status}');
});

// UCR Card Data API Requests
Route::middleware('auth:sanctum')->group(function () {
    // Main list endpoint with query parameters
    Route::get('/ucr-card-data/list', [UcrCardDataController::class, 'list']);

    // Specific search endpoints (alternative approach)
    Route::get('/ucr-card-data/net-id/{netId}', [UcrCardDataController::class, 'searchByNetId']);
    Route::get('/ucr-card-data/ssn/{ssn}', [UcrCardDataController::class, 'searchBySsn']);
    Route::get('/ucr-card-data/student-id/{studentId}', [UcrCardDataController::class, 'searchByStudentId']);
    Route::get('/ucr-card-data/iso/{iso}', [UcrCardDataController::class, 'searchByIso']);
    Route::post('/ucr-card-data/date-range', [UcrCardDataController::class, 'searchByDateRange']);
});