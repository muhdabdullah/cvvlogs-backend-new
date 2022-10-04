<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Recruiter\Sample;
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
Route::group(['prefix' => '/api.v.1/admin'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('admin.login');
});

Route::group(['prefix' => '/api.v.1/admin', 'middleware' => ['permission']], function () {
    Route::post('get-job-list', [DashboardController::class, 'getAllJobs'])->name('admin.getAllJobs');
    Route::get('get-stats', [DashboardController::class, 'getStats'])->name('admin.getStats');
});

Route::group(['prefix' => '/api.v.2/recruiter', 'middleware' => ['checkAuth']], function () {
    Route::get('recruiters', [Sample::class, 'index'])->name('recruiters');
});

Route::group(['prefix' => '/api.v.1/user', 'middleware' => ['checkAuth']], function () {
    Route::get('users', function (){
        dd(\App\Models\User::all());
    })->name('users');
});
