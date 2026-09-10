<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SyslogEventResource;
use App\Models\SyslogEvent;
use Illuminate\Http\Request;

class SyslogController extends Controller
{
    public function index(Request $request)
    {
        $events = SyslogEvent::query()
            ->when($request->integer('device_id'), fn ($query, $deviceId) => $query->where('device_id', $deviceId))
            ->when($request->string('severity')->trim()->value(), fn ($query, $severity) => $query->where('severity', $severity))
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where('message', 'like', "%{$search}%"))
            ->latest('received_at')
            ->paginate(min($request->integer('per_page', 50), 200));

        return SyslogEventResource::collection($events);
    }
}
