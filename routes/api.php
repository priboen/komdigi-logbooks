<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\InternshipController;
use App\Http\Controllers\api\ProgressController;
use App\Http\Controllers\api\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/internships/join', function (Request $request) {
    Log::info('Request received:', $request->all());
    return response()->json(['message' => 'Route is working!']);
});


Route::post('/user/update/{id}', [App\Http\Controllers\Api\AuthController::class, 'updateProfile'])->middleware('auth:sanctum');

Route::post('/admin/register', [App\Http\Controllers\Api\AuthController::class, 'adminRegister'])->middleware('auth:sanctum');
Route::post('/student/register', [App\Http\Controllers\Api\AuthController::class, 'studentRegister']);
Route::post('/supervisor/register', [App\Http\Controllers\Api\AuthController::class, 'supervisorRegister']);
Route::get('/supervisor', [App\Http\Controllers\Api\SupervisorController::class, 'getSupervisor'])->middleware('auth:sanctum');
Route::delete('/supervisor/{id}', [App\Http\Controllers\Api\SupervisorController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/grades/upload', [App\Http\Controllers\Api\GradeController::class, 'uploadGrade'])->middleware('auth:sanctum');
Route::get('/grades/{id}', [App\Http\Controllers\Api\GradeController::class, 'getGrade'])->middleware('auth:sanctum');


Route::apiResource('/internships', InternshipController::class)->middleware('auth:sanctum');
Route::post('/internships/join', [InternshipController::class, 'personalRegister'])->middleware('auth:sanctum');
Route::get('/internships/user/{id}', [InternshipController::class, 'getInternshipsByUserId'])->middleware('auth:sanctum');
Route::get('/internships/supervisor/{supervisorId}', [InternshipController::class, 'getInternshipsBySupervisorId'])->middleware('auth:sanctum');

Route::get('/progress/supervisor', [ProgressController::class, 'getUserProgressForSupervisor'])->middleware('auth:sanctum');
Route::get('/progress/supervisor/{id}', [ProgressController::class, 'getProgressDetailsByInternshipId'])->middleware('auth:sanctum');
Route::get('/progress/user/{userId}', [ProgressController::class, 'getUserProgressForUser'])->middleware('auth:sanctum');
Route::post('/progress/store', [ProgressController::class, 'store'])->middleware('auth:sanctum');


Route::get('/projects', [ProjectController::class, 'index'])->middleware('auth:sanctum');
Route::post('/projects/store', [ProjectController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
