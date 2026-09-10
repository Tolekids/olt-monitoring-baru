<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SyslogEventResource;
use App\Models\SyslogEvent;
use Illuminate\Http\Request;

class SyslogStreamController extends Controller
{
    public function __invoke(Request $request)
    {
        $lastEventId = $request->integer('after_id', 0);

        return response()->stream(function () use ($lastEventId): void {
            $cursor = $lastEventId;
            $startedAt = microtime(true);

            while (! connection_aborted() && microtime(true) - $startedAt < 25) {
                $events = SyslogEvent::query()
                    ->where('id', '>', $cursor)
                    ->oldest('id')
                    ->limit(100)
                    ->get();

                foreach ($events as $event) {
                    echo "id: {$event->id}\n";
                    echo 'event: syslog\n';
                    echo 'data: '.json_encode((new SyslogEventResource($event))->resolve())."\n\n";
                    $cursor = $event->id;
                }

                echo ": heartbeat\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
