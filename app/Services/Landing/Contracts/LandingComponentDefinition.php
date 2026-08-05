<?php

namespace App\Services\Landing\Contracts;

use App\Models\DynamicLandingPageComponent;

interface LandingComponentDefinition
{
    public function key(): string;

    public function name(): string;

    public function category(): string;

    public function view(): string;

    public function schema(): array;

    public function defaults(): array;

    public function validationRules(): array;

    public function dataResolver(): ?string;

    public function resolveData(
        DynamicLandingPageComponent $component,
        array $config
    ): array;

    public function behaviours(): array;
}
