<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\DiagnosisController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update.put');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::get('/diagnoses', [DiagnosisController::class, 'index'])->name('diagnoses.index');
});
