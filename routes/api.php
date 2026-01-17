<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\StaffController;


Route::get('clients', [ClientController::class, 'index']);
Route::get('/client/{id}', [ClientController::class, 'show']);
Route::post('add-client', [ClientController::class, 'create']);
Route::delete('/client/{id}', [ClientController::class, 'destroy']);

Route::get('staff', [StaffController::class, 'index']);
Route::post('add-staff', [StaffController::class, 'create']);
Route::delete('/staff/{id}', [StaffController::class, 'destroy']);
Route::delete('/delete-client/{id}', [ClientController::class, 'destroy']);
Route::patch('/edit-client/{id}', [ClientController::class, 'update']);
