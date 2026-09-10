<?php

namespace Tests\Unit\Domain;

use App\Domain\RemoteCli\Rules\AllowedCommandRule;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AllowedCommandRuleTest extends TestCase
{
    public function test_it_allows_read_only_commands(): void
    {
        $validator = Validator::make(['command' => 'show version'], [
            'command' => [new AllowedCommandRule()],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_it_rejects_state_changing_commands(): void
    {
        $validator = Validator::make(['command' => 'configure terminal'], [
            'command' => [new AllowedCommandRule()],
        ]);

        $this->assertTrue($validator->fails());
    }
}
