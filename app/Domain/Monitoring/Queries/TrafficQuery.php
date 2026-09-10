<?php

namespace App\Domain\Monitoring\Queries;

use App\Models\TrafficSample;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrafficQuery
{
    public function execute(?int $deviceId, ?string $from, ?string $to, int $perPage = 100): LengthAwarePaginator
    {
        return TrafficSample::query()
            ->when($deviceId, fn ($query) => $query->where('device_id', $deviceId))
            ->when($from, fn ($query) => $query->where('recorded_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('recorded_at', '<=', $to))
            ->latest('recorded_at')
            ->paginate(min($perPage, 500));
    }
}
