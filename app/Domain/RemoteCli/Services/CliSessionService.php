<?php

namespace App\Domain\RemoteCli\Services;

use App\Domain\Devices\Adapters\ZteSnmpAdapter;
use App\Domain\Devices\Exceptions\DeviceConnectionException;
use App\Domain\RemoteCli\Rules\AllowedCommandRule;
use App\Models\CliSession;
use App\Models\CommandAuditLog;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CliSessionService
{
    public function open(User $user, Device $device, string $protocol): CliSession
    {
        if ($device->vendor !== 'zte' || ! in_array($protocol, ['ssh', 'telnet'], true)) {
            throw new DeviceConnectionException('The MVP CLI supports SSH/Telnet sessions to ZTE devices only.');
        }

        return CliSession::create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'protocol' => $protocol,
            'status' => 'active',
            'started_at' => Carbon::now(),
        ]);
    }

    public function execute(CliSession $session, string $command): CommandAuditLog
    {
        Validator::make(['command' => $command], ['command' => [new AllowedCommandRule()]])->validate();

        $device = $session->device;
        $credential = $device->credentials()->where('protocol', $session->protocol)->first();

        if (! $credential) {
            throw new DeviceConnectionException('No CLI credential configured for this device.');
        }

        $success = false;
        $output = null;

        try {
            $output = app(ZteSnmpAdapter::class)->executeCommand(
                $device->management_ip,
                $credential->port,
                (string) $credential->username,
                (string) $credential->encrypted_password,
                trim($command),
            );
            $success = true;
        } catch (\Throwable $exception) {
            $output = 'CLI command failed.';
        }

        return CommandAuditLog::create([
            'cli_session_id' => $session->id,
            'user_id' => $session->user_id,
            'device_id' => $session->device_id,
            'command' => trim($command),
            'output_excerpt' => mb_substr((string) $output, 0, 4000),
            'success' => $success,
            'executed_at' => Carbon::now(),
        ]);
    }

    public function close(CliSession $session): CliSession
    {
        $session->update([
            'status' => 'closed',
            'ended_at' => Carbon::now(),
        ]);

        return $session->refresh();
    }
}
