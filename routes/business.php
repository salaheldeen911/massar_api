<?php

use App\Http\Controllers\Business\CenterDetailsController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\ExerciseController;
use App\Http\Controllers\Business\PatientController;
use App\Http\Controllers\Business\PatientPlanController;
use App\Http\Controllers\Business\SupportTicketController;
use App\Http\Controllers\Business\TherapistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Business API Routes
|--------------------------------------------------------------------------
|
| Unified routes for Center Business Operations (Admin & Therapist roles).
|
*/

Route::get('/', function () {
    return response()->json(['message' => 'Business API Service']);
});

Route::middleware(['auth:sanctum', 'role:admin|therapist'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Exclusive Admin Only Center Details Route
    Route::middleware('role:admin')->group(function () {
        Route::get('/center-details', [CenterDetailsController::class, 'show'])->name('center-details.show');
        Route::post('/center-details', [CenterDetailsController::class, 'update'])->name('center-details.update');
        Route::put('/center-details', [CenterDetailsController::class, 'update'])->name('center-details.update.put');
    });

    // Shared Business Operations (Admin & Therapist)
    Route::post('/patients/{patient}/treatment-plan', [PatientPlanController::class, 'storeTreatmentPlan'])->name('patients.treatment-plan');
    Route::post('/patients/{patient}/nutrition-plan', [PatientPlanController::class, 'storeNutritionPlan'])->name('patients.nutrition-plan');

    Route::post('/patients/{patient}/exercises', [ExerciseController::class, 'assign'])->name('patients.exercises.assign');
    Route::delete('/patients/{patient}/exercises/{patientExercise}', [ExerciseController::class, 'unassign'])->name('patients.exercises.unassign');

    Route::apiResource('patients', PatientController::class);
    Route::apiResource('therapists', TherapistController::class);
    Route::apiResource('exercises', ExerciseController::class);
    Route::apiResource('support-tickets', SupportTicketController::class)->only(['index', 'store', 'show']);
});
