<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function(Request $request) {
  return response()->json([
    'status' => 'ok'
  ]);
});

Route::post('/register', [AuthController::class, 'register'])->name('auth.register')->middleware('guest');
