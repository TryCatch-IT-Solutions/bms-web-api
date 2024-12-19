<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller {
  public function index(Request $request): JsonResponse
  {
    $user = $request->user();
    $role = $user->role;

    if(!in_array($role, ['groupadmin', 'superadmin'])) {
      return response()->json('Unauthorized', 401);
    }

    $limit = 20;
    if ($request->has('limit')) {
      $limit = (int)$request->input('limit');
      if ($limit > 100) {
        $limit = 100;
      }
    }

    $timeEntries = match('groupadmin') {
      'groupadmin' => TimeEntry::with('employee')->whereHas('employee', function($query) use ($user) {
        return $query->where('group_id', $user->group_id);
      })->paginate($limit),
      default => TimeEntry::paginate($limit)
    };

    return response()->json([
      'content' => $timeEntries->items(),
      'meta' => [
        'current_page' => $timeEntries->currentPage(),
        'last_page' => $timeEntries->lastPage(),
        'per_page' => $timeEntries->perPage(),
        'from' => $timeEntries->firstItem(),
        'to' => $timeEntries->lastItem(),
        'total' => $timeEntries->total(),
      ]
    ]);
  }
}
