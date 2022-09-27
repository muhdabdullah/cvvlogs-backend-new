<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => '/api.v.2/recruiter', 'middleware' => ['checkAuth']], function () {
    Route::get('recruiters', 'App\Http\Controllers\Api\Recruiter\Sample@index')->name('recruiters');
});

Route::group(['prefix' => '/api.v.1/user', 'middleware' => ['checkAuth']], function () {
    Route::get('users', function (){
        dd(\App\Models\User::all());
    })->name('users');
});
