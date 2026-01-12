<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ClientController;


Route::get('clients', [ClientController::class, 'index']);
Route::post('add-client', [ClientController::class, 'create']);
Route::delete('/client/{id}', [ClientController::class, 'destroy']);
