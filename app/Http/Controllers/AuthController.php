<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
  public function register(Request $request): array {
    $formFields = $request->validate([
      'group_id' => 'nullable',
      'role' => 'required',
      'first_name' => 'required',
      'last_name' => 'required',
      'middle_name' => 'nullable',
      'email' => 'email|required|unique:users',
      'phone_number' => 'required|unique:users',
      'birth_date' => 'required',
      'gender' => 'required',
      'emergency_contact_name' => 'required',
      'emergency_contact_no' => 'required',
      'address1' => 'required',
      'address2' => 'nullable',
      'barangay' => 'required',
      'municipality' => 'required',
      'zip_code' => 'required',
      'province' => 'required',
      'password' => 'required'
    ]);

    User::create($formFields);

    return [
      'message' => 'User successfully registered.'
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
