<?php

namespace App\Domain\Devices\Data;

final class ConnectionResult
{
    public function __construct(
        public readonly bool $isSuccessful,
        public readonly ?string $message = null,
    ) {
    }

    public static function success(string $message = 'Connection successful'): self
    {
        return new self(true, $message);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
