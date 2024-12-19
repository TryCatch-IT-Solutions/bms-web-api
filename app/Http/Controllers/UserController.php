<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

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

    $query = User::withTrashed()->whereNot('id', $request->user()->id);

    if($request->has('search')) {
      $query->where('first_name', 'like', '%' . $request->search . '%')
        ->orWhere('last_name', 'like', '%' . $request->search . '%')
        ->orWhere('email', 'like', '%' . $request->search . '%')
        ->orWhere('phone_number', 'like', '%' . $request->search . '%');
    }

    if($request->has('roles')) {
      $query->whereIn('role', $request->input('roles'));
    }

    if($request->has('status')) {
      $query->whereIn('status', $request->input('status'));
    }

    if($request->has('available')) {
      $request->get('available') ?
        $query->doesntHave('group') :
        $query->has('group');
    }

    $userList = match($role) {
      'superadmin' => $query,
      'groupadmin' => $query->where('group_id', $groupId),
      default => $user
    };

    $userList = $userList->orderByDesc('id')->paginate($limit);

    return response()->json([
      'content' => $userList->items(),
      'meta' => [
        'current_page' => $userList->currentPage(),
        'last_page' => $userList->lastPage(),
        'per_page' => $userList->perPage(),
        'from' => $userList->firstItem(),
        'to' => $userList->lastItem(),
        'total' => $userList->total(),
      ]
    ]);
  }

  /**
   * Import users
   */
  public function import(Request $request) {
    if(!in_array($request->user()->role, ['superadmin', 'groupadmin'])) {
      return response()->json('Unauthorized', 401);
    }

    $request->validate(['file' => ['required', 'mimetypes:text/csv,text/plain,application/csv,text/comma-separated-values,text/anytext,application/octet-stream,application/txt', 'max:12288']]);

    $fileContents = file($request->file('file')->getPathname());
    $insertPayload = [];
    foreach($fileContents as $key => $line) {
      if($key === 0) {
        continue;
      }

      $row = str_getcsv($line);

      $insertPayload[] = [
        'email' => $row[0],
        'password' => $row[1],
        'role' => $row[2],
        'first_name' => $row[3],
        'last_name' => $row[4],
        'phone_number' => $row[5],
        'birth_date' => $row[6],
        'gender' => $row[7],
        'emergency_contact_name' => $row[8],
        'emergency_contact_no' => $row[9],
        'address1' => $row[10],
        'barangay' => $row[11],
        'municipality' => $row[12],
        'zip_code' => $row[13],
        'province' => $row[14]
      ];
    }

    try {
      User::insertOrIgnore($insertPayload);
    }
    catch(Exception $e) {
      return response($e->getMessage(), 400);
    }

    return response()->json('Import successful');
  }

  /**
   * Returns the users count per role
   */
  public function usersCount(Request $request): JsonResponse {
    if($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized', 401);
    }

    $statusCounts = DB::table('users')
      ->select('status', DB::raw('COUNT(*) as count'))
      ->whereNot('id', $request->user()->id);

    $statusCounts = match($request->get('page')) {
      'employee' => $statusCounts->where('role', 'employee'),
      default => $statusCounts->whereNot('role', 'employee')
    };

    $statusCounts = $statusCounts->groupBy('status')->get();
    $response = [];
    foreach($statusCounts as $statusCount) {
      $response[$statusCount->status] = $statusCount->count;
    }

    return response()->json($response);
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
  public function show(string $userId): JsonResponse {
    try {
      $user = User::where('id', $userId)->get()->first();
      if ($user->exists) {
        if ($user->role === 'employee') {
          $user = Employee::where('id', $userId)->get()->first();

          $timeEntriesByDate = $user->timeEntries->groupBy(function ($timeEntry) {
            return Carbon::parse($timeEntry->datetime)->format('Y-m-d');
          });
          $user = json_decode($user->toJson(), true);
          $user['time_entries'] = $timeEntriesByDate;

          return response()->json($user);
        }

        return response()->json($user);
      }
    }
    catch(Exception $e) {
      return response()->json(['errors' => 'User not found.'], 404);
    }

    return response()->json(['errors' => 'An unknown error has occurred. Please try again.'], 500);
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

    $superDuperAdminInDelete = in_array(1, $request->get('users'));
    $groupAdminsWithGroup = array_intersect($request->get('users'), User::where('role', 'groupadmin')->whereNotNull('group_id')->get()->pluck('id')->toArray());

    $usersAffected = User::whereIn('id', $request->get('users'))->whereNotIn('id', [1, ...$groupAdminsWithGroup])->whereNull('deleted_at')->update([
      'status' => 'inactive',
      'deleted_at' => Carbon::now(),
    ]);

    $message = [];
    if($superDuperAdminInDelete) {
      $message[] = 'User ID 1 is immune to deletion.';
    }

    if(count($groupAdminsWithGroup) > 0) {
      $message[] = 'Group admins with groups CANNOT be deleted, found ' . count($groupAdminsWithGroup);
    }

    $message = implode(' ', $message);

    if ($usersAffected === 0) {
      return response()->json('No users to delete. ' . $message);
    }

    return response()->json(($usersAffected > 1 ? 'Users' : 'User') . ' has been successfully deleted. ' . $message);
  }

  /**
   * Restore users
   */
  public function restoreUsers(Request $request): JsonResponse
  {
    $request->validate(['users' => 'array|required']);

    if ($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $users = User::onlyTrashed()->whereIn('id', $request->get('users'));
    $usersAffected = $users->count();
    $users->update(['status' => 'active']);

    if(!$users->restore()) {
      return response()->json('An unknown error has occurred while trying to restore users. Please try again.');
    }

    if ($usersAffected === 0) {
      return response()->json('No users to delete');
    }

    return response()->json(($usersAffected > 1 ? 'Users' : 'User') . ' has been successfully restored');
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
