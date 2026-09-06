<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Routes dedicated for Center Admin operations.
|
*/

Route::get('/', function () {
    return response()->json(['message' => 'Admin API Service']);
});
