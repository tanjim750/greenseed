<?php

namespace App\Services\Landing;

use App\Services\Landing\Contracts\LandingComponentDefinition;

final class LandingComponentConfigResolver
{
    public function __construct(
        private LandingComponentDefaultConfigNormalizer $normalizer
    ) {
    }

    public function resolve(
        LandingComponentDefinition $definition,
        array $storedConfig
    ): array {
        return array_replace_recursive(
            $definition->defaults(),
            $this->normalizer->normalize($definition, $storedConfig)
        );
    }
}
