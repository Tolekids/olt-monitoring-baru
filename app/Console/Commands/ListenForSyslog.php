<?php

namespace App\Console\Commands;

use App\Domain\Syslog\Services\SyslogIngestionService;
use Illuminate\Console\Command;

class ListenForSyslog extends Command
{
    protected $signature = 'syslog:listen';

    protected $description = 'Listen for UDP syslog messages from network devices.';

    public function handle(SyslogIngestionService $ingestionService): int
    {
        $address = config('monitoring.syslog.bind_address');
        $port = config('monitoring.syslog.port');
        $socket = @stream_socket_server("udp://{$address}:{$port}", $errorCode, $errorMessage, STREAM_SERVER_BIND);

        if (! is_resource($socket)) {
            $this->error("Unable to bind syslog listener: {$errorMessage} ({$errorCode})");

            return self::FAILURE;
        }

        $this->info("Listening for syslog on {$address}:{$port}");

        while (true) {
            $peer = null;
            $message = stream_socket_recvfrom($socket, 65535, 0, $peer);

            if ($message === false || $peer === null) {
                continue;
            }

            [$sourceIp] = explode(':', $peer, 2);
            $ingestionService->ingest($message, $sourceIp);
        }
    }
}
