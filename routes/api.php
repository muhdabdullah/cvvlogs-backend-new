<?php

use App\Http\Controllers\Api\Admin\AuthApiController;
use App\Http\Controllers\Api\Admin\DashboardApiController;
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
Route::middleware(['cors'])->group(function () {
    Route::group(['prefix' => '/api.v.1/admin'], function () {
        Route::post('login', [AuthApiController::class, 'login'])->name('admin.login');
    });

    Route::group(['prefix' => '/api.v.1/admin', 'middleware' => ['permission']], function () {
        Route::get('get-job-list', [DashboardApiController::class, 'getAllJobs'])->name('admin.getAllJobs');
        Route::get('get-stats', [DashboardApiController::class, 'getStats'])->name('admin.getStats');
        Route::get('get-monthly-stats', [DashboardApiController::class, 'getMonthlyStats'])->name('admin.getStats');
        Route::get('get-user-videos', [DashboardApiController::class, 'getUserVideos'])->name('admin.getUserVideos');
        Route::put('update-video-status', [DashboardApiController::class, 'updateVideoStatus'])->name('admin.updateVideoStatus');
    });

    Route::group(['prefix' => '/api.v.2/recruiter', 'middleware' => ['checkAuth']], function () {
        Route::get('recruiters', [Sample::class, 'index'])->name('recruiters');
    });

    Route::group(['prefix' => '/api.v.1/user', 'middleware' => ['checkAuth']], function () {
        Route::get('users', function (){
            dd(\App\Models\User::all());
        })->name('users');
    });
});