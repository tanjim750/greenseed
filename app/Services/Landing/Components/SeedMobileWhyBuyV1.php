<?php

namespace App\Services\Landing\Components;

final class SeedMobileWhyBuyV1 extends SeedMobileChecklistV1
{
    protected function componentKey(): string
    {
        return 'seed-mobile-why-buy-v1';
    }

    protected function componentName(): string
    {
        return 'Seed Mobile Why Buy Checklist';
    }
}
