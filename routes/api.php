<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\StaffController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Client API
Route::get('/clients',          [ClientController::class, 'index']);
Route::get('/client/{id}',      [ClientController::class, 'show']);
Route::post('/add-client',      [ClientController::class, 'create']);
Route::patch('/edit-client/{id}', [ClientController::class, 'update']);
Route::delete('/client/{id}',   [ClientController::class, 'destroy']);

// Staff API
Route::get('/staff',            [StaffController::class, 'index']);
Route::post('/staff',           [StaffController::class, 'store']);
Route::delete('/staff/{id}',    [StaffController::class, 'destroy']);
