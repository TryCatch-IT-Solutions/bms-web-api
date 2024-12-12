<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller {
  public function list(Request $request) {
    if($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $devices = Device::withTrashed()->get();

    return response()->json($devices);
  }
}
