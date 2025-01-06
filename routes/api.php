<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/user/update/{id}', [App\Http\Controllers\Api\AuthController::class, 'updateProfile'])->middleware('auth:sanctum');

Route::post('/student/register', [App\Http\Controllers\Api\AuthController::class, 'studentRegister']);
Route::post('/supervisor/register', [App\Http\Controllers\Api\AuthController::class, 'supervisorRegister']);
Route::get('/supervisor', [App\Http\Controllers\Api\SupervisorController::class, 'getSupervisor'])->middleware('auth:sanctum');
Route::delete('/supervisor/{id}', [App\Http\Controllers\Api\SupervisorController::class, 'destroy'])->middleware('auth:sanctum');

Route::apiResource('/internships', App\Http\Controllers\Api\InternshipController::class)->middleware('auth:sanctum');
Route::get('/internships/user/{id}', [App\Http\Controllers\Api\InternshipController::class, 'getInternshipsByUserId'])->middleware('auth:sanctum');
Route::get('/internships/supervisor/{supervisorId}', [App\Http\Controllers\Api\InternshipController::class, 'getInternshipsBySupervisorId'])->middleware('auth:sanctum');


Route::get('/projects', [App\Http\Controllers\Api\ProjectController::class, 'index'])->middleware('auth:sanctum');
Route::post('/projects/store', [App\Http\Controllers\Api\ProjectController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/projects/{id}', [App\Http\Controllers\Api\ProjectController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
