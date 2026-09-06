<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'Massar API'),
        'status' => 'online',
        'timestamp' => now()->toIso8601String(),
    ]);
});

