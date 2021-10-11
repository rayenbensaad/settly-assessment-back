<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;

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



Route::post('login', [AuthController::class, 'login']);

Route::post('register', [AuthController::class, 'register']);

Route::get('test', [CustomerController::class, 'test']);

Route::get('verify/{verification_code}', [AuthController::class, 'verifyUser']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('get_user', [AuthController::class, 'get_user']);

    Route::prefix('customer')->group(function () {
        Route::get('list', [CustomerController::class, 'list']);
        Route::post('search', [CustomerController::class, 'search']);
        Route::post('create', [CustomerController::class, 'create']);
        Route::get('show/{id}', [CustomerController::class, 'show']);
        Route::post('update/{id}', [CustomerController::class, 'update']);
        Route::delete('delete/{id}', [CustomerController::class, 'delete']);
        Route::delete('removeAll', [CustomerController::class, 'removeAll']);
    });

    
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
