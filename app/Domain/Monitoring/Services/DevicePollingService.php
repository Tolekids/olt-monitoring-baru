<?php

namespace App\Domain\Monitoring\Services;

use App\Domain\Devices\Services\DeviceConnectionService;
use App\Models\Device;
use App\Models\DevicePollRun;
use Illuminate\Support\Carbon;
use Throwable;

class DevicePollingService
{
    public function __construct(
        private readonly DeviceConnectionService $connectionService,
        private readonly MetricPersistenceService $metricPersistenceService,
    ) {
    }

    public function poll(Device $device, string $pollType): DevicePollRun
    {
        $pollRun = DevicePollRun::create([
            'device_id' => $device->id,
            'poll_type' => $pollType,
            'status' => 'running',
            'started_at' => Carbon::now(),
        ]);

        try {
            $snapshot = $this->connectionService->adapterFor($device)->poll($device);
            $this->metricPersistenceService->persist($device, $snapshot);

            $pollRun->update([
                'status' => 'completed',
                'finished_at' => Carbon::now(),
            ]);
            $device->update([
                'status' => $snapshot->status,
                'last_seen_at' => Carbon::now(),
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $safeError = $this->safeErrorMessage($exception);
            $pollRun->update([
                'status' => 'failed',
                'finished_at' => Carbon::now(),
                'error_message' => $safeError,
            ]);
            $device->update([
                'status' => 'offline',
                'last_error' => $safeError,
            ]);
        }

        return $pollRun->refresh();
    }

    private function safeErrorMessage(Throwable $exception): string
    {
        $message = preg_replace('/(password|community|secret|token)=?[^\s]+/i', '$1=[redacted]', $exception->getMessage());

        return mb_substr($message ?: 'Device polling failed.', 0, 1000);
    }
}
