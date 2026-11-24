<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

// Application Installer & Single User Setup
Route::get('/install', [InstallerController::class, 'show'])->name('installer.show');
Route::post('/install', [InstallerController::class, 'install'])->name('installer.install');
Route::get('/install/requirements', [InstallerController::class, 'requirements'])->name('installer.requirements');
Route::post('/install/test-db', [InstallerController::class, 'testDatabase'])->middleware('throttle:10,1')->name('installer.test-db');

// Main Page Redirect (WordPress / external site manages the public landing portal)
Route::redirect('/', '/login')->name('landing');

// Personal Dashboard & Cryptic Terminal Simulator (Requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{section}', [DashboardController::class, 'section'])->name('dashboard.section');
    Route::get('/terminal', [TerminalController::class, 'index'])->name('terminal.index');
    Route::post('/terminal/execute', [TerminalController::class, 'execute'])->name('terminal.execute')->middleware('throttle:60,1,terminal');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1,login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:6,1');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update')->middleware('throttle:6,1');

// API Routes (Identical contract to Next.js API for tests & client integrations)
Route::prefix('api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1,login');
    Route::post('/auth/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:6,1');
    Route::post('/auth/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:6,1');
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('throttle:60,1');
    Route::post('/auth/me', [AuthController::class, 'logout'])->middleware('throttle:60,1');

    Route::post('/terminal/execute', [TerminalController::class, 'execute'])->middleware('throttle:60,1,terminal');

    Route::get('/training/modules', [TrainingController::class, 'modules']);

    Route::post('/dashboard/validate-syntax', [DashboardController::class, 'validateSyntaxApi'])->middleware('throttle:60,1');

    Route::middleware('auth')->group(function () {
        Route::get('/training/progress', [TrainingController::class, 'progress']);
        Route::post('/training/progress', [TrainingController::class, 'saveProgress']);

        // Personal Dashboard APIs (Secured behind auth)
        Route::get('/dashboard/active-pnr', [DashboardController::class, 'getActivePnrApi']);
        Route::post('/dashboard/scenarios', [DashboardController::class, 'createScenarioApi'])->middleware('throttle:15,1');
        Route::delete('/dashboard/scenarios/{id}', [DashboardController::class, 'deleteScenarioApi'])->middleware('throttle:15,1');
        Route::post('/dashboard/scenarios/{id}/submit', [DashboardController::class, 'submitScenarioApi'])->middleware('throttle:15,1');
    });
});
