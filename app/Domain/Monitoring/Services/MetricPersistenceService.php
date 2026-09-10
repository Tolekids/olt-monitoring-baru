<?php

namespace App\Domain\Monitoring\Services;

use App\Domain\Devices\Data\DeviceSnapshot;
use App\Models\Device;
use App\Models\DeviceMetricSample;
use App\Models\TrafficSample;
use Illuminate\Support\Carbon;

class MetricPersistenceService
{
    public function persist(Device $device, DeviceSnapshot $snapshot): void
    {
        $recordedAt = Carbon::now();

        foreach ($snapshot->metrics as $metricName => $metricValue) {
            if (! is_numeric($metricValue)) {
                continue;
            }

            DeviceMetricSample::create([
                'device_id' => $device->id,
                'metric_name' => (string) $metricName,
                'metric_value' => $metricValue,
                'recorded_at' => $recordedAt,
            ]);
        }

        foreach ($snapshot->interfaces as $interface) {
            $interfaceName = $interface['name'] ?? $interface['default-name'] ?? null;
            $rxBytes = $interface['rx-byte'] ?? null;
            $txBytes = $interface['tx-byte'] ?? null;

            if ($interfaceName === null || ! is_numeric($rxBytes) || ! is_numeric($txBytes)) {
                continue;
            }

            TrafficSample::create([
                'device_id' => $device->id,
                'interface_name' => $interfaceName,
                'rx_bps' => $rxBytes,
                'tx_bps' => $txBytes,
                'recorded_at' => $recordedAt,
            ]);
        }
    }
}
