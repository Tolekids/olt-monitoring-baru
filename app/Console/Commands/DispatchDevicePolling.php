<?php

namespace App\Console\Commands;

use App\Jobs\PollMikrotikJob;
use App\Jobs\PollZteOltJob;
use App\Models\Device;
use Illuminate\Console\Command;

class DispatchDevicePolling extends Command
{
    protected $signature = 'monitoring:poll-devices';

    protected $description = 'Dispatch polling jobs for enabled network devices.';

    public function handle(): int
    {
        Device::query()
            ->where('is_polling_enabled', true)
            ->chunkById(100, function ($devices): void {
                foreach ($devices as $device) {
                    $job = $device->vendor === 'zte'
                        ? new PollZteOltJob($device->id)
                        : new PollMikrotikJob($device->id);

                    dispatch($job)->onQueue('device-polling');
                }
            });

        $this->info('Device polling jobs dispatched.');

        return self::SUCCESS;
    }
}
