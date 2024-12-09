<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
  public function register(Request $request): array {
    $formFields = $request->validate([
      'email' => 'email|required|unique:users',
      'password' => 'required'
    ]);

    $user = User::create($formFields);

    $token = $user->createToken('authToken')->plainTextToken;

    return [
      'user' => $user,
      'token' => $token
    ];
  }

  public function login(Request $request): array {
    $formFields = $request->validate([
      'email' => 'email|required',
      'password' => 'required'
    ]);

    $user = User::where('email', $formFields['email'])->first();

    if(!$user || !Hash::check($formFields['password'], $user->password)) {
      return [
        'errors' => 'Invalid email or password'
      ];
    }

    $token = $user->createToken($user->email);

    return [
      'user' => $user,
      'token' => $token->plainTextToken
    ];
  }

  public function logout(Request $request): array {
    $request->user()->tokens()->delete();

    return [
      'message' => 'You have been successfully logged out.'
    ];
  }
}
