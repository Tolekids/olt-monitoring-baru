<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Devices\Services\DeviceConnectionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDeviceRequest;
use App\Http\Requests\Api\V1\UpdateDeviceRequest;
use App\Http\Resources\Api\V1\DeviceResource;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = Device::query()
            ->when($request->string('search')->trim()->value(), function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('management_ip', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(min($request->integer('per_page', 20), 100));

        return DeviceResource::collection($devices);
    }

    public function store(StoreDeviceRequest $request)
    {
        $device = DB::transaction(function () use ($request): Device {
            $device = Device::create($request->safe()->except('credentials'));
            $this->syncCredentials($device, $request->validated('credentials'));

            return $device;
        });

        return (new DeviceResource($device))->response()->setStatusCode(201);
    }

    public function show(Device $device): DeviceResource
    {
        return new DeviceResource($device);
    }

    public function update(UpdateDeviceRequest $request, Device $device): DeviceResource
    {
        DB::transaction(function () use ($request, $device): void {
            $device->update($request->safe()->except('credentials'));

            if ($request->has('credentials')) {
                $this->syncCredentials($device, $request->validated('credentials'));
            }
        });

        return new DeviceResource($device->refresh());
    }

    public function destroy(Device $device): JsonResponse
    {
        $device->delete();

        return response()->json(null, 204);
    }

    public function testConnection(Device $device, DeviceConnectionService $connectionService): JsonResponse
    {
        abort_unless(request()->user()?->can('devices.manage'), 403);
        $result = $connectionService->adapterFor($device)->testConnection($device);

        return response()->json([
            'data' => [
                'is_successful' => $result->isSuccessful,
                'message' => $result->message,
            ],
        ], $result->isSuccessful ? 200 : 422);
    }

    private function syncCredentials(Device $device, ?array $credentials): void
    {
        if (! $credentials) {
            return;
        }

        $device->credentials()->updateOrCreate(
            ['protocol' => $credentials['protocol']],
            [
                'port' => $credentials['port'],
                'username' => $credentials['username'] ?? null,
                'encrypted_password' => $credentials['password'] ?? null,
                'encrypted_community' => $credentials['community'] ?? null,
            ],
        );
    }
}
