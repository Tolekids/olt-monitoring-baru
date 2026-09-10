<?php

namespace App\Domain\Devices\Contracts;

interface OltAdapter extends DeviceAdapter
{
    public function executeCommand(string $host, int $port, string $username, string $password, string $command): string;
}
