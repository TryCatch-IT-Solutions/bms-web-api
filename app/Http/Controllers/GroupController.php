<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    if($request->has('status')) {
      $query = Group::whereIn('status', $request->input('status'));
    }

    $groupList = isset($query) ? $query->paginate($limit) : Group::paginate($limit);

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
      'name' => 'required'
    ]);

    Group::create($formFields);

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

    $groupsAffected = Group::whereIn('id', $request->get('groups'))->whereNull('deleted_at')->update([
      'deleted_at' => Carbon::now(),
    ]);

    if($groupsAffected === 0) {
      return response()->json('No groups to delete');
    }

    return response()->json(($groupsAffected > 1 ? 'Groups' : 'Group') . ' has been successfully deleted');
  }

  /**
   * Show group
   */
  public function show(Request $request, string $groupId): JsonResponse {
    try {
      $group = Group::with(['devices', 'groupAdmin', 'employees'])->findOrFail($groupId);
    }
    catch(Exception $e) {
      return response()->json($e->getMessage(), $e->getCode());
    }

    return response()->json($group);
  }

  /**
   * Updates a particular group.
   */
  public function update(Request $request, Group $group): JsonResponse {
    $formFields = $request->validate([
      'name' => 'required',
    ]);

    $group->update($formFields);

    return response()->json([
      'message' => 'Group has been successfully updated.'
    ]);
  }
}
