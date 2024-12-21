<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Employee;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends Controller {
  /**
   * Returns the list of users in paginated form (20)
   */
  public function list(Request $request): JsonResponse {
    $user = $request->user();
    $role = $user->role;

    if($role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $limit = 20;
    if($request->has('limit')) {
      $limit = (int) $request->input('limit');
      if($limit > 100) {
        $limit = 100;
      }
    }

    $query = Group::withCount('devices')->with('groupAdmin');
    if($request->has('status')) {
      $query = $query->whereIn('status', $request->input('status'));
    }

    $groupList = $query->paginate($limit);

    return response()->json([
      'content' => $groupList->items(),
      'meta' => [
        'current_page' => $groupList->currentPage(),
        'last_page' => $groupList->lastPage(),
        'per_page' => $groupList->perPage(),
        'from' => $groupList->firstItem(),
        'to' => $groupList->lastItem(),
        'total' => $groupList->total()
      ]
    ]);
  }

  /**
   * Create a group
   */
  public function create(Request $request): JsonResponse {
    $formFields = $request->validate([
      'name' => 'required',
      'group_admin' => ['required', Rule::in(User::where('role', 'groupadmin')->whereNull('group_id')->get()->pluck('id')->toArray())],
      'employees' => ['required', 'array', Rule::in(User::where('role', 'employee')->whereNull('group_id')->get()->pluck('id')->toArray())],
    ]);

    $groupId = Group::create($formFields)->id;

    try {
      User::where('id', $formFields['group_admin'])->update(['group_id' => $groupId, 'role' => 'groupadmin']);

      if($request->has('employees')) {
        User::whereIn('id', $formFields['employees'])->update(['group_id' => $groupId]);
      }
    }
    catch(Exception $e) {
      return response()->json(['errors' => [$e->getMessage()]], 400);
    }

    return response()->json('Group has been successfully created');
  }

  /**
   * Delete Groups
   */
  public function deleteGroups(Request $request): JsonResponse
  {
    $request->validate(['groups' => 'array|required']);

    if($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $groups = Group::with(['employees', 'groupadmin', 'devices'])->whereIn('id', $request->get('groups'))->whereNull('deleted_at')->get();
    $groupsAffected = 0;
    foreach($groups as $group) {
      Device::whereIn('id', $group->devices->pluck('id')->toArray())->update(['group_id' => null]);
      Employee::whereIn('id', $group->employees->pluck('id')->toArray())->update(['group_id' => null]);
      User::where('id', $group->groupAdmin->id)->update(['group_id' => null]);
      $group->update(['deleted_at' => Carbon::now()]);
      $groupsAffected++;
    }

    if($groupsAffected === 0) {
      return response()->json('No groups to delete');
    }

    return response()->json(($groupsAffected > 1 ? 'Groups' : 'Group') . ' has been successfully deleted');
  }

  /**
   * Restore groups
   */
  public function restoreGroups(Request $request): JsonResponse {
    $request->validate(['groups' => 'array|required']);

    if($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $groups = Group::onlyTrashed()->whereIn('id', $request->get('groups'));
    $groupsAffected = $groups->count();

    if(!$groups->restore()) {
      return response()->json([
        'errors' => 'One or more of the groups is not archived.'
      ]);
    }

    if ($groupsAffected === 0) {
      return response()->json('No groups to delete');
    }

    return response()->json(($groupsAffected > 1 ? 'Groups' : 'Group') . ' has been successfully restored');
  }

  /**
   * Show group
   */
  public function show(Request $request, string $groupId): JsonResponse {
    try {
      $group = Group::with(['devices', 'groupAdmin', 'employees'])->findOrFail($groupId);
    }
    catch(Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

    return response()->json($group);
  }

  /**
   * Updates a particular group.
   */
  public function update(Request $request, Group $group): JsonResponse {
    $groupAdmins = User::where('role', 'groupadmin')->whereNotNull('group_id');
    if(!is_null($group->groupAdmin)) {
      $groupAdmins->whereNot('id', $group->groupAdmin->id);
    }
    $groupAdmins = $groupAdmins->get()->pluck('id')->toArray();

    $formFields = $request->validate([
      'name' => 'required',
      'group_admin' => ['required', Rule::notIn($groupAdmins)]
    ]);

    if(!isset($group->groupAdmin) || $group->groupAdmin->id !== $formFields['group_admin']) {
      User::where('role', 'groupadmin')->where('group_id', $group->id)->update(['role' => 'employee']);
      User::where('id', $formFields['group_admin'])->update(['role' => 'groupadmin', 'group_id' => $group->id]);
    }

    $group->update($formFields);

    return response()->json([
      'message' => 'Group has been successfully updated.'
    ]);
  }

  public function removeEmployee(Request $request, string $groupId): JsonResponse {
    $request->validate([
      'employees' => 'required|array'
    ]);

    Employee::whereIn('id', $request->post('employees'))->where('group_id', $groupId)->update(['group_id' => null]);

    return response()->json('Employees has been successfully removed');
  }

  public function addEmployee(Request $request, string $groupId): JsonResponse {
    $request->validate([
      'employees' => 'required|array'
    ]);

    Employee::whereIn('id', $request->post('employees'))->update(['group_id' => $groupId]);

    return response()->json('Employees has been successfully added');
  }
}
