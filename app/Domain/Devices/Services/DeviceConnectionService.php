<?php

namespace App\Domain\Devices\Services;

use App\Domain\Devices\Adapters\MikrotikApiAdapter;
use App\Domain\Devices\Adapters\ZteSnmpAdapter;
use App\Domain\Devices\Contracts\DeviceAdapter;
use App\Models\Device;
use InvalidArgumentException;

class DeviceConnectionService
{
    public function adapterFor(Device $device): DeviceAdapter
    {
        return match ($device->vendor) {
            'zte' => app(ZteSnmpAdapter::class),
            'mikrotik' => app(MikrotikApiAdapter::class),
            default => throw new InvalidArgumentException('Unsupported device vendor.'),
        };
    }
}
