<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller {
  public function list(Request $request): JsonResponse {
    if($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $limit = 20;
    if($request->has('limit')) {
      $limit = (int) $request->input('limit');
      if($limit > 100) {
        $limit = 100;
      }
    }

    $devices = Device::withTrashed();

    if($request->has('search')) {
      $devices->where('model', 'like', '%' . $request->search . '%')
        ->orWhere('group_id', 'like', '%' . $request->search . '%')
        ->orWhere('serial_no', 'like', '%' . $request->search . '%');
    }

    if($request->has('available')) {
      $request->get('available') ?
        $devices->doesntHave('group') :
        $devices->has('group');
    }

    $devices = $devices->paginate($limit);

    return response()->json([
      'content' => $devices->items(),
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
      'group_id' => 'nullable|exists:groups,id',
      'model' => 'required',
      'serial_no' => 'required',
      'lat' => 'nullable',
      'lon' => 'nullable'
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

  /**
   * Restore devices
   */
  public function restoreDevices(Request $request): JsonResponse
  {
    $request->validate(['devices' => 'array|required']);

    if ($request->user()->role !== 'superadmin') {
      return response()->json('Unauthorized.', 401);
    }

    $devices = Device::onlyTrashed()->whereIn('id', $request->get('devices'));
    $devicesAffected = $devices->count();

    if(!$devices->restore()) {
      return response()->json('An unknown error has occurred while trying to restore devices. Please try again.');
    }

    if ($devicesAffected === 0) {
      return response()->json('No devices to delete');
    }

    $devices->update(['status' => 'active']);
    return response()->json(($devicesAffected > 1 ? 'Devices' : 'Device') . ' has been successfully restored');
  }
}
