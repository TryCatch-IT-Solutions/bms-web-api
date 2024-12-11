<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function(Request $request) {
  return response()->json([
    'status' => 'ok'
  ]);
});

Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
Route::post('/reset-password', [AuthController::class, 'resetPasswordSave'])->name('password.reset');

Route::middleware(['auth:sanctum'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

  // TODO: Apply gate / token abilities for superadmin and groupadmin as necessary.
  Route::get('/users', [UserController::class, 'users'])->name('users');

  Route::get('/user', function(Request $request) {
    return response()->json($request->user());
  });

  Route::get('/verify-token', function(Request $request) {
    return response()->json(true);
  });
});
