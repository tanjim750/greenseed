<?php

namespace App\Services\Landing\Contracts;

interface LandingComponentConfigNormalizer
{
    public function normalize(array $config): array;
}
