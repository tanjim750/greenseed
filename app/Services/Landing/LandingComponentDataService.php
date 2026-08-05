<?php

namespace App\Services\Landing;

use App\Models\DynamicLandingPageComponent;
use App\Services\Landing\Contracts\LandingComponentDefinition;

final class LandingComponentDataService
{
    public function __construct(
        private LandingComponentDataResolverRegistry $registry
    ) {
    }

    public function resolve(
        LandingComponentDefinition $definition,
        DynamicLandingPageComponent $component,
        array $config
    ): array {
        $resolverKey = $definition->dataResolver();

        if (!$resolverKey) {
            return $definition->resolveData($component, $config);
        }

        return $this->registry
            ->get($resolverKey)
            ->resolve($component, $config);
    }
}
