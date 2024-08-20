<?php

use App\Http\Controllers\Api\v1\ApplicationAuthController;
use App\Http\Controllers\Api\v1\ApplicationsController;
use App\Http\Controllers\Api\v1\HrEmployeeDetailController;
use App\Http\Controllers\Api\v1\SisActiveStudentController;
use App\Http\Controllers\Api\v1\ApiAuthController;
use App\Http\Controllers\Api\v1\UcrPersonController;
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
Route::post('/login', [ApplicationAuthController::class, 'login']);

// Applications Request
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApplicationAuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('applications', ApplicationsController::class);
    Route::post('/exit', [ApiAuthController::class, 'exit']);
});

// UCRGW API Requests
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ucr-person', [UcrPersonController::class, 'index']);
    Route::get('/ucr-person/{searchField}/{searchTerm}', [UcrPersonController::class, 'personSearch']);
    Route::get('/sis-active-students/{stringId}', [SisActiveStudentController::class, 'index']);
    Route::get('/sis-active-students', [SisActiveStudentController::class, 'activeStudents']);
    Route::get('/active-student-count', [SisActiveStudentController::class, 'activeStudentCount']);
    Route::get('/employee-detail', [HrEmployeeDetailController::class, 'index']);
    Route::get('/employee-details', [HrEmployeeDetailController::class, 'hrEmployeeDetails']);
    Route::get('/employee-job', [HrEmployeeDetailController::class, 'hrEmployeeJob']);
});