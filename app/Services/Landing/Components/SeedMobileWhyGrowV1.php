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

    protected function defaultHeading(): string
    {
        return 'বিশ্ববিখ্যাত বারি বেগুন-১২ কেন চাষ করবেন?';
    }

    protected function defaultItems(): array
    {
        return [
            ['icon' => 'check_circle', 'text' => 'বারি বেগুন-১২ লবণাক্ত জমিতেও চাষ করা যায়।'],
            ['icon' => 'check_circle', 'text' => 'প্রতিটি বেগুনের ওজন সর্বোচ্চ ১ কেজি পর্যন্ত হতে পারে।'],
            ['icon' => 'check_circle', 'text' => 'অল্প যত্ন এবং কম খরচে বারি বেগুনের চাষ করা সম্ভব।'],
        ];
    }
}
