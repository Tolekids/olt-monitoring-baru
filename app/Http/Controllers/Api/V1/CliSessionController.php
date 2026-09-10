<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\RemoteCli\Services\CliSessionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ExecuteCliCommandRequest;
use App\Http\Requests\Api\V1\StoreCliSessionRequest;
use App\Models\CliSession;
use App\Models\Device;
use Illuminate\Http\Request;

class CliSessionController extends Controller
{
    public function store(StoreCliSessionRequest $request, Device $device, CliSessionService $service)
    {
        return response()->json([
            'data' => $service->open($request->user(), $device, $request->validated('protocol')),
        ], 201);
    }

    public function execute(ExecuteCliCommandRequest $request, CliSession $session, CliSessionService $service)
    {
        abort_unless($session->user_id === $request->user()->id || $request->user()->can('audit.view'), 403);

        return response()->json([
            'data' => $service->execute($session, $request->validated('command')),
        ]);
    }

    public function close(Request $request, CliSession $session, CliSessionService $service)
    {
        abort_unless($session->user_id === $request->user()->id || $request->user()->can('audit.view'), 403);

        return response()->json(['data' => $service->close($session)]);
    }
}
