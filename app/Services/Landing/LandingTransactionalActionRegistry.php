<?php

namespace App\Services\Landing;

use App\Services\Landing\Contracts\LandingTransactionalActionHandler;
use InvalidArgumentException;

final class LandingTransactionalActionRegistry
{
    /** @var array<string, class-string<LandingTransactionalActionHandler>> */
    private array $handlers = [];

    public function register(string $actionKey, string $handlerClass): void
    {
        $this->handlers[$actionKey] = $handlerClass;
    }

    public function get(string $actionKey): LandingTransactionalActionHandler
    {
        $handlerClass = $this->handlers[$actionKey] ?? null;

        if (!$handlerClass) {
            throw new InvalidArgumentException("Unknown landing action [{$actionKey}].");
        }

        return app($handlerClass);
    }
}
