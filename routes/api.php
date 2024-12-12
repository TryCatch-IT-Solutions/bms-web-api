<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
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
  Route::post('/users/delete', [UserController::class, 'deleteUsers'])->name('users.deleteUsers');
  Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
  Route::get('/user/{user}', [UserController::class, 'show'])->name('user.show');
  Route::post('/user/{user}', [UserController::class, 'update'])->name('user.update');

  Route::get('/devices', [DeviceController::class, 'list'])->name('devices.list');
  Route::post('/devices/delete', [DeviceController::class, 'deleteDevices'])->name('devices.delete');

  Route::get('/device/{device}', [DeviceController::class, 'show'])->name('devices.show');
  Route::post('/device/{device}', [DeviceController::class, 'edit'])->name('devices.edit');

  Route::get('/verify-token', function(Request $request) {
    return response()->json(true);
  });
});
