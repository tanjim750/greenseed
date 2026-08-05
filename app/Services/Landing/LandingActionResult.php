<?php

namespace App\Services\Landing;

final class LandingActionResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $payload,
        public readonly ?int $orderId = null,
        public readonly int $statusCode = 200
    ) {
    }

    public static function success(array $payload, ?int $orderId = null, int $statusCode = 200): self
    {
        return new self(true, $payload, $orderId, $statusCode);
    }

    public static function failure(array $payload, int $statusCode = 422): self
    {
        return new self(false, $payload, null, $statusCode);
    }
}
