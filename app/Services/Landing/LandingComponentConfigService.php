<?php

namespace App\Services\Landing;

use App\Services\Landing\Contracts\LandingComponentDefinition;

final class LandingComponentConfigService
{
    public function __construct(
        private LandingComponentRegistry $registry,
        private LandingComponentConfigValidator $validator,
        private LandingComponentConfigResolver $resolver
    ) {
    }

    public function definitionFor(string $componentKey): LandingComponentDefinition
    {
        return $this->registry->get($componentKey);
    }

    public function validateForStorage(string $componentKey, array $config): array
    {
        return $this->validator->validate(
            $this->definitionFor($componentKey),
            $config
        );
    }

    public function resolveForRendering(string $componentKey, array $storedConfig): array
    {
        return $this->resolver->resolve(
            $this->definitionFor($componentKey),
            $storedConfig
        );
    }
}
