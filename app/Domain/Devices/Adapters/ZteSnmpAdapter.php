<?php

namespace App\Domain\Devices\Adapters;

use App\Domain\Devices\Contracts\OltAdapter;
use App\Domain\Devices\Data\ConnectionResult;
use App\Domain\Devices\Data\DeviceSnapshot;
use App\Domain\Devices\Exceptions\DeviceConnectionException;
use App\Models\Device;
use App\Models\DeviceCredential;
use phpseclib3\Net\SSH2;

class ZteSnmpAdapter implements OltAdapter
{
    public function testConnection(Device $device): ConnectionResult
    {
        $credential = $this->credential($device, ['snmp_v2c', 'snmp_v3']);

        if (! function_exists('snmp2_get')) {
            return ConnectionResult::failure('PHP SNMP extension is not installed.');
        }

        try {
            $value = @snmp2_get(
                $device->management_ip,
                (string) $credential->encrypted_community,
                (string) config('network-devices.zte.system_name_oid'),
                $this->timeoutMicroseconds(),
                1,
            );
        } catch (\Throwable $exception) {
            return ConnectionResult::failure('SNMP connection failed.');
        }

        return $value === false
            ? ConnectionResult::failure('SNMP did not return a system name.')
            : ConnectionResult::success('SNMP connection successful.');
    }

    public function poll(Device $device): DeviceSnapshot
    {
        $result = $this->testConnection($device);

        if (! $result->isSuccessful) {
            throw new DeviceConnectionException($result->message ?? 'SNMP polling failed.');
        }

        return new DeviceSnapshot('online');
    }

    public function executeCommand(string $host, int $port, string $username, string $password, string $command): string
    {
        $ssh = new SSH2($host, $port, (int) config('network-devices.timeout_seconds'));

        if (! $ssh->login($username, $password)) {
            throw new DeviceConnectionException('SSH authentication failed.');
        }

        return (string) $ssh->exec($command);
    }

    private function credential(Device $device, array $protocols): DeviceCredential
    {
        $credential = $device->credentials()->whereIn('protocol', $protocols)->first();

        if (! $credential) {
            throw new DeviceConnectionException('No supported SNMP credential configured.');
        }

        return $credential;
    }

    private function timeoutMicroseconds(): int
    {
        return (int) config('network-devices.timeout_seconds') * 1_000_000;
    }
}
