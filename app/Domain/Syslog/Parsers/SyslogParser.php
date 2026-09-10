<?php

namespace App\Domain\Syslog\Parsers;

use Illuminate\Support\Carbon;

class SyslogParser
{
    public function parse(string $message, string $sourceIp): array
    {
        $priority = 13;
        $body = trim($message);

        if (preg_match('/^<(\d+)>(.*)$/', $body, $matches)) {
            $priority = (int) $matches[1];
            $body = trim($matches[2]);
        }

        $severityNames = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'informational', 'debug'];
        $severity = $severityNames[$priority % 8] ?? 'unknown';

        return [
            'source_ip' => $sourceIp,
            'facility' => (string) intdiv($priority, 8),
            'severity' => $severity,
            'message' => $body,
            'received_at' => Carbon::now(),
            'parsed_at' => Carbon::now(),
        ];
    }
}
