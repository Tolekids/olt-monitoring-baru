<?php

namespace App\Domain\Devices\Contracts;

use App\Domain\Devices\Data\ConnectionResult;
use App\Domain\Devices\Data\DeviceSnapshot;
use App\Models\Device;

interface DeviceAdapter
{
    public function testConnection(Device $device): ConnectionResult;

    public function poll(Device $device): DeviceSnapshot;
}
