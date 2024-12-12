<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller {
  /**
   * Returns the list of users in paginated form (20)
   */
  public function users(Request $request): JsonResponse {
    $user = $request->user();
    $role = $user->role;
    $groupId = $user->group_id ?? null;

    $limit = 20;
    if($request->has('limit')) {
      $limit = (int) $request->input('limit');
      if($limit > 100) {
        $limit = 100;
      }
    }

    $query = User::withTrashed();

    if($request->has('roles')) {
      $query = $query->whereIn('role', $request->input('roles'));
    }

    if($request->has('status')) {
      $query->whereIn('status', $request->input('status'));
    }

    $userList = match($role) {
      'superadmin' => isset($query) ? $query->paginate($limit) : User::paginate($limit),
      'groupadmin' => isset($query) ? $query->where('group_id', $groupId)->paginate($limit) : User::where('group_id', $groupId)->paginate($limit),
      default => $user
    };

    return response()->json([
      'content' => $userList->items(),
      'meta' => [
        'current_page' => $userList->currentPage(),
        'last_page' => $userList->lastPage(),
        'per_page' => $userList->perPage(),
        'from' => $userList->firstItem(),
        'to' => $userList->lastItem(),
        'total' => $userList->total()
      ]
    ]);
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
  public function show(User $user): JsonResponse {
    if($user->exists) {
      return response()->json($user);
    }

    return response()->json(['errors' => 'User not found.'], 404);
  }

  /**
   * Delete Users
   */
  public function deleteUsers(Request $request): JsonResponse
  {
    $request->validate(['users' => 'array|required']);

    if ($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $usersAffected = User::whereIn('id', $request->get('users'))->whereNull('deleted_at')->update([
      'status' => 'inactive',
      'deleted_at' => Carbon::now(),
    ]);

    if ($usersAffected === 0) {
      return response()->json('No users to delete');
    }

    return response()->json(($usersAffected > 1 ? 'Users' : 'User') . ' has been successfully deleted');
  }

  /**
   * Updates a particular user.
   */
  public function update(Request $request, User $user): JsonResponse
  {
    $extraRules = [];

    if ($user->role === 'superadmin') {
      $extraRules = ['role' => 'nullable|in:superadmin,groupadmin,employee'];
    }

    $formFields = $request->validate(array_merge($extraRules, [
      'group_id' => 'nullable',
      'first_name' => 'required',
      'last_name' => 'required',
      'middle_name' => 'nullable',
      'email' => ['email', 'required', Rule::unique('users')->withoutTrashed()->ignore($user)],
      'phone_number' => ['numeric', 'required', Rule::unique('users')->withoutTrashed()->ignore($user)],
      'birth_date' => 'required',
      'gender' => 'required',
      'emergency_contact_name' => 'required',
      'emergency_contact_no' => 'required',
      'address1' => 'required',
      'address2' => 'nullable',
      'barangay' => 'required',
      'municipality' => 'required',
      'zip_code' => 'required',
      'province' => 'required'
    ]));

    $user->update($formFields);

    return response()->json([
      'message' => 'User has been successfully updated.'
    ]);
  }
}
