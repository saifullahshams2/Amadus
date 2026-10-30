<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Application Installer & Primary Instructor Setup
Route::get('/install', [InstallerController::class, 'show'])->name('installer.show');
Route::post('/install', [InstallerController::class, 'install'])->name('installer.install');
Route::get('/install/requirements', [InstallerController::class, 'requirements'])->name('installer.requirements');
Route::post('/install/test-db', [InstallerController::class, 'testDatabase'])->middleware('throttle:10,1')->name('installer.test-db');

// Main Landing Page (Public 100vh / 100vw Welcome Portal)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Access Dashboard Gateway (Redirects guests to register, authenticated to dashboard)
Route::get('/access-dashboard', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('register');
})->name('access-dashboard');

// Unified Role-Secured Dashboard & Cryptic Terminal Simulator (Requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{section}', [DashboardController::class, 'section'])->name('dashboard.section');
    Route::post('/dashboard/switch-role', [DashboardController::class, 'switchRole'])->name('dashboard.switch-role');
    Route::get('/terminal', [TerminalController::class, 'index'])->name('terminal.index');
    Route::post('/terminal/execute', [TerminalController::class, 'execute'])->name('terminal.execute')->middleware('throttle:60,1,terminal');
    Route::post('/instructor/exit-impersonation', [InstructorController::class, 'exitImpersonation'])->name('impersonation.exit');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1,login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1,login');
Route::get('/pending-approval', [AuthController::class, 'pendingApproval'])->name('auth.pending-approval');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:6,1');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update')->middleware('throttle:6,1');

// Web Instructor Operations (Strictly restricted to instructors)
Route::middleware('instructor')->group(function () {
    Route::get('/instructor', [InstructorController::class, 'index'])->name('instructor.index');
    Route::post('/instructor/students', [InstructorController::class, 'storeStudent'])->name('instructor.students.store');
    Route::delete('/instructor/students/{id}', [InstructorController::class, 'destroyStudent'])->name('instructor.students.destroy');
    Route::post('/instructor/students/{id}/impersonate', [InstructorController::class, 'impersonateStudent'])->name('instructor.students.impersonate');
    Route::post('/instructor/students/{id}/approve', [InstructorController::class, 'approveStudent'])->name('instructor.students.approve');
    Route::post('/instructor/students/{id}/reject', [InstructorController::class, 'rejectStudent'])->name('instructor.students.reject');
    Route::delete('/instructor/scenarios/{id}', [DashboardController::class, 'deleteScenario'])->name('instructor.scenarios.destroy');
});

// API Routes (Identical contract to Next.js API for tests & client integrations)
Route::prefix('api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1,login');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1,login');
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

        // Dual-Role Dashboard APIs (Secured behind auth)
        Route::get('/dashboard/active-pnr', [DashboardController::class, 'getActivePnrApi']);
        Route::post('/dashboard/scenarios/{id}/submit', [DashboardController::class, 'submitScenarioApi'])->middleware('throttle:15,1');
    });

    // Instructor Hub APIs (Strictly restricted to instructors)
    Route::middleware('instructor')->prefix('instructor')->group(function () {
        Route::get('/students', [InstructorController::class, 'studentsApi']);
        Route::post('/students', [InstructorController::class, 'storeStudent']);
        Route::delete('/students/{id}', [InstructorController::class, 'destroyStudent']);
        Route::post('/students/{id}/impersonate', [InstructorController::class, 'impersonateStudent']);
        Route::post('/students/batch', [InstructorController::class, 'batchStoreStudents'])->middleware('throttle:5,1');
        Route::post('/students/{id}/approve', [InstructorController::class, 'approveStudent']);
        Route::post('/students/{id}/reject', [InstructorController::class, 'rejectStudent']);
        Route::get('/spectate/{username}', [DashboardController::class, 'spectateStudentApi']);
        Route::post('/scenarios', [DashboardController::class, 'createScenarioApi']);
        Route::delete('/scenarios/{id}', [DashboardController::class, 'deleteScenario']);
        Route::post('/submissions/{id}/grade', [DashboardController::class, 'gradeSubmissionApi']);
        Route::post('/queues/broadcast', [DashboardController::class, 'broadcastQueueApi'])->middleware('throttle:20,1');
        Route::post('/sandbox/reset', [DashboardController::class, 'resetSandboxApi'])->middleware('throttle:5,1');
    });
});
