<?php

namespace App\Services\Landing;

use App\Services\Landing\Contracts\LandingComponentDefinition;
use InvalidArgumentException;

final class LandingComponentRegistry
{
    private array $definitions = [];

    public function register(LandingComponentDefinition $definition): void
    {
        $this->definitions[$definition->key()] = $definition;
    }

    public function get(string $componentKey): LandingComponentDefinition
    {
        $definition = $this->definitions[$componentKey] ?? null;

        if (!$definition) {
            throw new InvalidArgumentException("Unknown landing component: {$componentKey}");
        }

        return $definition;
    }

    public function all(): array
    {
        return array_values($this->definitions);
    }

    public function groupedByCategory(): array
    {
        return collect($this->definitions)
            ->groupBy(fn (LandingComponentDefinition $definition) => $definition->category())
            ->all();
    }
}
