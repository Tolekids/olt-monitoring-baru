<?php

namespace App\Domain\Syslog\Services;

use App\Domain\Syslog\Parsers\SyslogParser;
use App\Models\Device;
use App\Models\SyslogEvent;

class SyslogIngestionService
{
    public function __construct(private readonly SyslogParser $parser)
    {
    }

    public function ingest(string $message, string $sourceIp): SyslogEvent
    {
        $attributes = $this->parser->parse($message, $sourceIp);
        $attributes['device_id'] = Device::where('management_ip', $sourceIp)->value('id');

        return SyslogEvent::create($attributes);
    }
}
