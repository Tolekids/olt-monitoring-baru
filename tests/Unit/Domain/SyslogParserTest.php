<?php

namespace Tests\Unit\Domain;

use App\Domain\Syslog\Parsers\SyslogParser;
use Tests\TestCase;

class SyslogParserTest extends TestCase
{
    public function test_it_parses_priority_and_source_ip(): void
    {
        $result = app(SyslogParser::class)->parse('<134>OLT link changed', '192.0.2.20');

        $this->assertSame('192.0.2.20', $result['source_ip']);
        $this->assertSame('16', $result['facility']);
        $this->assertSame('informational', $result['severity']);
        $this->assertSame('OLT link changed', $result['message']);
    }
}
