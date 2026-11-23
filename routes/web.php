<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function (Request $request) {
    return view('test');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/applications-access', 'auth.applications')->name('applications.access');
Route::view('/applications-access/create', 'auth.application-create')->name('applications.create');
Route::view('/applications-access/{application}/edit', 'auth.application-edit')->name('applications.edit');

Route::get('/welcome', function (Request $request) {
    return view('welcome');
});

Route::get('/search-test', function () {
    return view('search-result-box');
});