<?php

namespace App\Services\Landing;

use App\Services\Landing\Contracts\LandingComponentDefinition;
use Illuminate\Support\Facades\Validator;

final class LandingComponentConfigValidator
{
    public function __construct(
        private LandingComponentDefaultConfigNormalizer $normalizer,
        private LandingComponentConfigResolver $resolver
    ) {
    }

    public function validate(
        LandingComponentDefinition $definition,
        array $config
    ): array {
        $normalizedConfig = $this->normalizer->normalize($definition, $config);
        $resolvedConfig = $this->resolver->resolve($definition, $normalizedConfig);

        Validator::make(
            $resolvedConfig,
            $definition->validationRules()
        )->validate();

        return $normalizedConfig;
    }
}
