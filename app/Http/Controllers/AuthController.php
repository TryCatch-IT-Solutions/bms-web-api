<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller {
  public function register(Request $request) {
    $formFields = $request->validate([
      'email' => 'email|required',
      'password' => 'required'
    ]);

    $user = User::create($formFields);

    $token = $user->createToken('authToken')->plainTextToken;

    return [
      'user' => $user,
      'token' => $token
    ];
  }
}
