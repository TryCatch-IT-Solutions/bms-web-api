<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\GroupController;
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
  Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
  Route::get('/users/count', [UserController::class, 'usersCount'])->name('users.count');
  Route::post('/users/delete', [UserController::class, 'deleteUsers'])->name('users.deleteUsers');
  Route::post('/users/restore', [UserController::class, 'restoreUsers'])->name('users.restore');
  Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
  Route::get('/user/{user}', [UserController::class, 'show'])->name('user.show');
  Route::post('/user/{user}', [UserController::class, 'update'])->name('user.update');

  Route::get('/devices', [DeviceController::class, 'list'])->name('devices.list');
  Route::post('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
  Route::post('/devices/delete', [DeviceController::class, 'deleteDevices'])->name('devices.delete');
  Route::post('/devices/restore', [DeviceController::class, 'restoreDevices'])->name('devices.restore');
  Route::get('/device/{device}', [DeviceController::class, 'show'])->name('devices.show');
  Route::post('/device/{device}', [DeviceController::class, 'edit'])->name('devices.edit');

  Route::get('/groups', [GroupController::class, 'list'])->name('groups.list');
  Route::post('/groups/create', [GroupController::class, 'create'])->name('groups.create');
  Route::post('/groups/delete', [GroupController::class, 'deleteGroups'])->name('groups.deleteGroups');
  Route::post('/groups/restore', [GroupController::class, 'restoreGroups'])->name('groups.restore');
  Route::get('/group/{group}', [GroupController::class, 'show'])->name('groups.deleteGroups');
  Route::post('/group/{group}', [GroupController::class, 'update'])->name('groups.update');
  Route::post('/group/{group}/employees/add', [GroupController::class, 'addEmployee'])->name('groups.addEmployee');
  Route::post('/group/{group}/employees/remove', [GroupController::class, 'removeEmployee'])->name('groups.removeEmployee');

  Route::get('/verify-token', function(Request $request) {
    return response()->json(true);
  });
});
