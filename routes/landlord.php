<?php

use App\Http\Controllers\Landlord\CenterController;
use App\Http\Controllers\Landlord\CenterSubscriptionController;
use App\Http\Controllers\Landlord\PatientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landlord API Routes
|--------------------------------------------------------------------------
|
| Routes dedicated for Landlord operations (Global management, Centers, system configs).
|
*/

Route::get('/', function () {
    return response()->json(['message' => 'Landlord API Service']);
});

Route::middleware(['auth:sanctum', 'role:landlord'])->group(function () {
    Route::get('/centers/pending', [CenterSubscriptionController::class, 'pending'])->name('centers.pending');
    Route::post('/centers/{center}/approve', [CenterSubscriptionController::class, 'approve'])->name('centers.approve');
    Route::post('/centers/{center}/reject', [CenterSubscriptionController::class, 'reject'])->name('centers.reject');

    Route::get('/centers/{center}/staff', [CenterController::class, 'staff'])->name('centers.staff');
    Route::apiResource('centers', CenterController::class);
    Route::apiResource('patients', PatientController::class);
});
