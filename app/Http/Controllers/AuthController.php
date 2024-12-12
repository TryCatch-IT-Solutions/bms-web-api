<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller {
  public function register(Request $request): array {
    $formFields = $request->validate([
      'group_id' => 'nullable',
      'role' => 'required',
      'first_name' => 'required',
      'last_name' => 'required',
      'middle_name' => 'nullable',
      'email' => ['email', 'required', Rule::unique('users')->withoutTrashed()],
      'phone_number' => ['required', Rule::unique('users')->withoutTrashed()],
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

  public function forgotPassword(Request $request): array {
    $request->validate([
      'email' => 'email|required'
    ]);

    $status = Password::sendResetLink(
      $request->only('email')
    );

    return [
      'message' => __($status)
    ];
  }

  public function resetPasswordSave(Request $request): array {
    $request->validate([
      'password' => 'required',
    ]);

    $status = Password::reset(
      array_merge($request->only('email', 'password', 'token')),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    return [
      'message' => __($status)
    ];
  }
}
