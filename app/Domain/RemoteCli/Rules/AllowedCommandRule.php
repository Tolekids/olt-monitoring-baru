<?php

namespace App\Domain\RemoteCli\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class AllowedCommandRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $command = trim((string) $value);
        $isReadOnlyCommand = preg_match('/^(show|display|get|terminal\s+length|exit|quit)(\s|$)/i', $command) === 1;

        if (! $isReadOnlyCommand) {
            $fail('Only approved read-only CLI commands are allowed in the MVP.');
        }
    }
}
