<?php

namespace App\Services\Landing\Components;

final class SeedMobileWhyGrowV1 extends SeedMobileChecklistV1
{
    protected function componentKey(): string
    {
        return 'seed-mobile-why-grow-v1';
    }

    protected function componentName(): string
    {
        return 'Seed Mobile Why Grow Checklist';
    }
}
