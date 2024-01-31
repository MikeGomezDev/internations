<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
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


Route::post('/authenticate', [AuthController::class, 'authenticate']);

Route::get('/users', [UserController::class, 'index']);
Route::get('/groups', [GroupController::class, 'index']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);

    Route::post('/group', [GroupController::class, 'store']);
    Route::post('/group/{group_id}/addUser/{user_id}', [GroupController::class, 'addUser']);
    Route::post('/group/{group_id}/removeUser/{user_id}', [GroupController::class, 'removeUser']);
    Route::delete('/group/{id}', [GroupController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
