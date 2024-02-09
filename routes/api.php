<?php

use App\Http\Controllers\ControllerRedirect;
use App\Http\Controllers\ControllerRedirectLog;
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



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('redirects', [ControllerRedirect::class, 'createRedirect'])->name('createRedirect');
Route::put('redirects', [ControllerRedirect::class, 'updateRedirect'])->name('updateRedirect');
Route::delete('redirects', [ControllerRedirect::class, 'deleteRedirect'])->name('deleteRedirect');
Route::get('redirects', [ControllerRedirect::class, 'getListAllRedirect'])->name('getListAllRedirect');
Route::get('/redirects/{redirect}/stats', [ControllerRedirectLog::class, 'statisticsLog'])->name('statisticsLog');
Route::get('/redirects/{redirect}/logs', [ControllerRedirectLog::class, 'logsAcess'])->name('logsAcess');
