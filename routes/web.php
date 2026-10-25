<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

// Application Installer & Primary Instructor Setup
Route::get('/install', [InstallerController::class, 'show'])->name('installer.show');
Route::post('/install', [InstallerController::class, 'install'])->name('installer.install');
Route::get('/install/requirements', [InstallerController::class, 'requirements'])->name('installer.requirements');

// Web Application Views
Route::get('/', [TerminalController::class, 'index'])->name('terminal.index');
Route::post('/terminal/execute', [TerminalController::class, 'execute'])->name('terminal.execute')->middleware('throttle:60,1,terminal');

// Dual-Role Dashboard & Demo Role Switcher
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/switch-role', [DashboardController::class, 'switchRole'])->name('dashboard.switch-role');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1,login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1,login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/instructor', [InstructorController::class, 'index'])->name('instructor.index');

// API Routes (Identical contract to Next.js API for tests & client integrations)
Route::prefix('api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1,login');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1,login');
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/me', [AuthController::class, 'logout']);

    Route::post('/terminal/execute', [TerminalController::class, 'execute'])->middleware('throttle:60,1,terminal');

    Route::get('/training/modules', [TrainingController::class, 'modules']);

    Route::middleware('auth')->group(function () {
        Route::get('/training/progress', [TrainingController::class, 'progress']);
        Route::post('/training/progress', [TrainingController::class, 'saveProgress']);
    });

    Route::get('/instructor/students', [InstructorController::class, 'studentsApi']);

    // Dual-Role Dashboard APIs
    Route::get('/dashboard/active-pnr', [DashboardController::class, 'getActivePnrApi']);
    Route::post('/dashboard/validate-syntax', [DashboardController::class, 'validateSyntaxApi']);
    Route::post('/dashboard/scenarios/{id}/submit', [DashboardController::class, 'submitScenarioApi']);

    // Instructor Hub APIs
    Route::get('/instructor/spectate/{username}', [DashboardController::class, 'spectateStudentApi']);
    Route::post('/instructor/scenarios', [DashboardController::class, 'createScenarioApi']);
    Route::post('/instructor/submissions/{id}/grade', [DashboardController::class, 'gradeSubmissionApi']);
    Route::post('/instructor/queues/broadcast', [DashboardController::class, 'broadcastQueueApi']);
    Route::post('/instructor/sandbox/reset', [DashboardController::class, 'resetSandboxApi']);
});
