<?php

namespace App\Domain\Monitoring\Queries;

use App\Models\Device;
use App\Models\Onu;
use App\Models\TrafficSample;

class DashboardSummaryQuery
{
    public function execute(): array
    {
        return [
            'devices_online' => Device::where('status', 'online')->count(),
            'devices_offline' => Device::where('status', 'offline')->count(),
            'onus_online' => Onu::where('status', 'online')->count(),
            'onus_los' => Onu::where('status', 'los')->count(),
            'onus_power_off' => Onu::where('status', 'power_off')->count(),
            'aggregate_rx_bps' => (float) TrafficSample::query()->sum('rx_bps'),
            'aggregate_tx_bps' => (float) TrafficSample::query()->sum('tx_bps'),
        ];
    }
}
