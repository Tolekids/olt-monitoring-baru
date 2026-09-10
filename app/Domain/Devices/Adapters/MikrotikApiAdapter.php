<?php

namespace App\Domain\Devices\Adapters;

use App\Domain\Devices\Contracts\RouterAdapter;
use App\Domain\Devices\Data\ConnectionResult;
use App\Domain\Devices\Data\DeviceSnapshot;
use App\Domain\Devices\Exceptions\DeviceConnectionException;
use App\Models\Device;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

class MikrotikApiAdapter implements RouterAdapter
{
    public function testConnection(Device $device): ConnectionResult
    {
        try {
            $client = $this->client($device);
            $client->query((new Query('/system/identity/print')))->read();

            return ConnectionResult::success('RouterOS API connection successful.');
        } catch (\Throwable $exception) {
            return ConnectionResult::failure('RouterOS API connection failed.');
        }
    }

    public function poll(Device $device): DeviceSnapshot
    {
        $client = $this->client($device);
        $resources = $client->query((new Query('/system/resource/print')))->read();
        $interfaces = $client->query((new Query('/interface/print')))->read();

        return new DeviceSnapshot(
            status: 'online',
            metrics: $resources[0] ?? [],
            interfaces: $interfaces,
        );
    }

    private function client(Device $device): Client
    {
        $credential = $device->credentials()->where('protocol', 'routeros_api')->first();

        if (! $credential) {
            throw new DeviceConnectionException('No RouterOS API credential configured.');
        }

        return new Client(new Config([
            'host' => $device->management_ip,
            'user' => $credential->username,
            'pass' => $credential->encrypted_password,
            'port' => $credential->port,
            'timeout' => (int) config('network-devices.timeout_seconds'),
        ]));
    }
}
