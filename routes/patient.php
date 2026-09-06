<?php

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
