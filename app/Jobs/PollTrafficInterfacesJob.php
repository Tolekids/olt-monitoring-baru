<?php

namespace App\Jobs;

use App\Domain\Monitoring\Services\DevicePollingService;
use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PollTrafficInterfacesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 1;

    public function __construct(public readonly int $deviceId)
    {
    }

    public function handle(DevicePollingService $pollingService): void
    {
        $device = Device::find($this->deviceId);

        if ($device?->is_polling_enabled) {
            $pollingService->poll($device, 'traffic');
        }
    }
}
