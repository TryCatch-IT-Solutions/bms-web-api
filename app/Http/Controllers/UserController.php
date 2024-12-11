<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
}
