<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Therapist API Routes
|--------------------------------------------------------------------------
|
| Routes dedicated for Therapist operations.
|
*/

Route::get('/', function () {
    return response()->json(['message' => 'Therapist API Service']);
});
