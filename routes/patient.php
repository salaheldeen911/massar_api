<?php

use App\Http\Controllers\Patient\PatientExerciseController;
use App\Http\Controllers\Patient\PatientNutritionPlanController;
use App\Http\Controllers\Patient\PatientTreatmentPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Patient API Routes
|--------------------------------------------------------------------------
|
| Routes dedicated for Patient operations.
|
*/

Route::get('/', function () {
    return response()->json(['message' => 'Patient API Service']);
});

Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {
    Route::get('/treatment-plan', [PatientTreatmentPlanController::class, 'show'])->name('treatment-plan.show');
    Route::get('/nutrition-plan', [PatientNutritionPlanController::class, 'show'])->name('nutrition-plan.show');

    Route::get('/exercises', [PatientExerciseController::class, 'index'])->name('exercises.index');
    Route::post('/exercises/{patientExercise}/log', [PatientExerciseController::class, 'log'])->name('exercises.log');
    Route::post('/exercises/{patientExercise}/complete', [PatientExerciseController::class, 'complete'])->name('exercises.complete');
});
