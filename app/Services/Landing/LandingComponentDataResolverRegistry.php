<?php

namespace App\Services\Landing;

use App\Services\Landing\Contracts\LandingComponentDataResolver;
use InvalidArgumentException;

final class LandingComponentDataResolverRegistry
{
    private array $resolvers = [];

    public function register(string $key, string $resolverClass): void
    {
        if (!is_subclass_of($resolverClass, LandingComponentDataResolver::class)) {
            throw new InvalidArgumentException("Landing data resolver must implement LandingComponentDataResolver: {$resolverClass}");
        }

        $this->resolvers[$key] = $resolverClass;
    }

    public function get(string $key): LandingComponentDataResolver
    {
        $resolverClass = $this->resolvers[$key] ?? null;

        if (!$resolverClass) {
            throw new InvalidArgumentException("Unknown landing data resolver: {$key}");
        }

        return app($resolverClass);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->resolvers);
    }

    public function keys(): array
    {
        return array_keys($this->resolvers);
    }
}
