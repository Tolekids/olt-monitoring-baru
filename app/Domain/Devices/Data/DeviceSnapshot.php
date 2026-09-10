<?php

namespace App\Domain\Devices\Data;

final class DeviceSnapshot
{
    public function __construct(
        public readonly string $status,
        public readonly array $metrics = [],
        public readonly array $interfaces = [],
        public readonly array $ponPorts = [],
        public readonly array $onus = [],
    ) {
    }
}
