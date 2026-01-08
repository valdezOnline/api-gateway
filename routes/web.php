<?php

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

Route::get('/welcome', function (Request $request) {
    return view('welcome');
});

Route::get('/search-test', function () {
    return view('search-result-box');
});