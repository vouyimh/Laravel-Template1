<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ClientController;


Route::get('clients', [ClientController::class, 'index']);
Route::get('/client/{id}', [ClientController::class, 'show']);
Route::post('add-client', [ClientController::class, 'create']);
Route::delete('/delete-client/{id}', [ClientController::class, 'destroy']);
Route::patch('/edit-client/{id}', [ClientController::class, 'update']);
