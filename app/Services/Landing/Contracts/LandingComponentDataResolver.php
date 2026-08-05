<?php

namespace App\Services\Landing\Contracts;

use App\Models\DynamicLandingPageComponent;

interface LandingComponentDataResolver
{
    public function resolve(
        DynamicLandingPageComponent $component,
        array $config
    ): array;
}
