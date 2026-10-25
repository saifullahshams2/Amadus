<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

// Web Application Views
Route::get('/', [TerminalController::class, 'index'])->name('terminal.index');
Route::post('/terminal/execute', [TerminalController::class, 'execute'])->name('terminal.execute');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/instructor', [InstructorController::class, 'index'])->name('instructor.index');

// API Routes (Identical contract to Next.js API for tests & client integrations)
Route::prefix('api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/me', [AuthController::class, 'logout']);

    Route::post('/terminal/execute', [TerminalController::class, 'execute']);

    Route::get('/training/modules', [TrainingController::class, 'modules']);
    Route::get('/training/progress', [TrainingController::class, 'progress']);
    Route::post('/training/progress', [TrainingController::class, 'saveProgress']);

    Route::get('/instructor/students', [InstructorController::class, 'studentsApi']);
});
