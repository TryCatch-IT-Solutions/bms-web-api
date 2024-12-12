<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller {
  public function list(Request $request): JsonResponse {
    if($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $devices = Device::withTrashed()->paginate(20);

    return response()->json([
      'data' => $devices->items(),
      'meta' => [
        'current_page' => $devices->currentPage(),
        'last_page' => $devices->lastPage(),
        'per_page' => $devices->perPage(),
        'from' => $devices->firstItem(),
        'to' => $devices->lastItem(),
        'total' => $devices->total()
      ]
    ]);
  }

  /**
   * Adds a new device
   */
  public function create(Request $request): JsonResponse {
    $formFields = $request->validate([
      'group_id' => 'required|exists:groups,id',
      'model' => 'required',
      'serial_no' => 'required',
      'lat' => 'required',
      'lon' => 'required'
    ]);

    Device::create($formFields);

    return response()->json('Device successfully created');
  }

  /**
   * Delete Devices
   */
  public function deleteDevices(Request $request): JsonResponse {
    $request->validate(['devices' => 'array|required']);

    if($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $devicesAffected = Device::whereIn('id', $request->get('devices'))->whereNull('deleted_at')->update([
      'deleted_at' => Carbon::now(),
    ]);

    if($devicesAffected === 0) {
      return response()->json('No devices to delete');
    }

    return response()->json(($devicesAffected > 1 ? 'Devices' : 'Device') . ' has been successfully deleted');
  }

}
