<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Psy\Util\Json;
use function Symfony\Component\String\s;

class UserController extends Controller {
  public function users(Request $request) {
    $user = $request->user();
    $role = $user->role;
    $groupId = $user->group_id ?? null;

    $userList = match($role) {
      'superadmin' => User::all(),
      'groupadmin' => User::where('group_id', $groupId)->get(),
      default => $user
    };

    return response()->json($userList);
  }

  /**
   * Returns the current user object.
   */
  public function profile(Request $request): JsonResponse {
    return response()->json($request->user());
  }

  /**
   * Shows a specific user given a user id
   */
  public function show(Request $request, User $user): JsonResponse {
    if($user->exists) {
      return response()->json($user);
    }

    return response()->json(['errors' => 'User not found.'], 404);
  }

  /**
   * Updates a particular user.
   */
  public function update(Request $request, User $user): JsonResponse {
    $formFields = $request->validate([
      'group_id' => 'nullable',
      'role' => 'required',
      'first_name' => 'required',
      'last_name' => 'required',
      'middle_name' => 'nullable',
      'email' => ['email', 'required', Rule::unique('users')->ignore($user)],
      'phone_number' => ['email', 'required', Rule::unique('users')->ignore($user)],
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

    User::update($formFields);

    return response()->json([
      'message' => 'User has been successfully updated.'
    ]);
  }
}
